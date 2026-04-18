<?php

namespace App\Http\Controllers;

use App\Events\BattleAnswerSubmitted;
use App\Events\BattleFinished;
use App\Events\BattleLobbyUpdated;
use App\Events\BattleNextQuestion;
use App\Events\BattleStarted;
use App\Events\BattleViolation;
// use App\Http\Controllers\Controller;
use App\Models\BattleParticipant;
use App\Models\BattleQuestionAnswer;
use App\Models\BattleRoom;
use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BattleController extends Controller
{
    // ── 1. Setup page ─────────────────────────────────────────────────────
    public function setup(Request $request)
    {
        $user    = Auth::user();
        $student = $user->getOrCreateStudent();
        $quizId  = $request->query('quizId');

        if (!$quizId) {
            return redirect()->route('student.quiz.index')
                ->with('error', 'No quiz selected. Please generate a quiz first.');
        }

        $quiz = Quiz::where('id', $quizId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$quiz) {
            return redirect()->route('student.quiz.index')
                ->with('error', 'Quiz not found.');
        }

        return view('student.battle.setup', compact('quiz', 'student', 'user'));
    }

    // ── 2. Create Room (POST) ──────────────────────────────────────────────
    public function createRoom(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id'        => 'required|integer|exists:quizzes,id',
            'mode'           => 'required|in:1v1,group,team',
            'team_a_name'    => 'required_if:mode,team|string|max:50',
            'team_b_name'    => 'required_if:mode,team|string|max:50',
            'question_timer' => 'integer|between:10,60',
            'max_per_team'   => 'integer|between:2,20',
            'anti_cheat'     => 'boolean',
            'is_public'      => 'boolean',
            'subject'        => 'nullable|string|max:100',
            'class_level'    => 'nullable|string|max:20',
            'difficulty'     => 'nullable|string|max:20',
        ]);

        $quiz = Quiz::where('id', $request->quiz_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$quiz) {
            return response()->json(['success' => false, 'message' => 'Quiz not found.'], 404);
        }

        DB::beginTransaction();
        try {
            $room = BattleRoom::create([
                'code'            => BattleRoom::generateCode(),
                'host_id'         => Auth::id(),
                'quiz_id'         => $quiz->id,
                'mode'            => $request->mode,
                'status'          => 'waiting',
                'team_a_name'     => $request->team_a_name ?? 'Team A',
                'team_b_name'     => $request->team_b_name ?? 'Team B',
                'max_per_team'    => $request->max_per_team ?? 10,
                'question_timer'  => $request->question_timer ?? 20,
                'total_questions' => count($quiz->questions ?? []),
                'anti_cheat'      => $request->input('anti_cheat', true),
                'is_public'       => $request->input('is_public', false),
                'subject'         => $request->subject ?? $quiz->subject,
                'class_level'     => $request->class_level ?? $quiz->class,
                'difficulty'      => $request->difficulty ?? $quiz->difficulty ?? 'intermediate',
            ]);

            BattleParticipant::create([
                'room_id' => $room->id,
                'user_id' => Auth::id(),
                'team'    => $request->mode === 'team' ? 'a' : null,
                'status'  => 'joined',
            ]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'roomCode'    => $room->code,
                'inviteUrl'   => $room->invite_url,
                'teamAUrl'    => $room->team_a_url,
                'teamBUrl'    => $room->team_b_url,
                'mode'        => $room->mode,
                'redirectUrl' => route('student.battle.lobby', $room->code),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function publicBattles(Request $request): JsonResponse
    {
        $user    = Auth::user();
        $student = $user->student ?? null;

        // Get recent quiz history for subject/class matching
        $recentSubjects = \App\Models\QuizResult::where('user_id', $user->id)
            ->whereNotNull('subject')
            ->latest()
            ->take(20)
            ->pluck('subject')
            ->unique()
            ->values()
            ->all();

        $query = BattleRoom::where('is_public', true)
            ->where('status', 'waiting')
            ->where('host_id', '!=', $user->id)  // don't show own rooms
            ->whereDoesntHave('participants', fn($q) => $q->where('user_id', $user->id)) // not already in
            ->with(['host', 'quiz', 'participants'])
            ->withCount('participants')
            ->latest()
            ->take(10);

        $rooms = $query->get()->map(fn($room) => [
            'id'           => $room->id,
            'code'         => $room->code,
            'mode'         => $room->mode,
            'quizTitle'    => $room->quiz->title ?? 'Quiz Battle',
            'subject'      => $room->subject,
            'classLevel'   => $room->class_level,
            'difficulty'   => $room->difficulty,
            'playerCount'  => $room->participants_count,
            'hostName'     => $room->host->name,
            'inviteUrl'    => $room->invite_url,
            'joinUrl'      => route('student.battle.join.code', $room->code),
            // Relevance score for sorting
            'relevance'    => in_array($room->subject, $recentSubjects) ? 2 : 0,
        ])->sortByDesc('relevance')->values()->all();

        return response()->json(['success' => true, 'rooms' => $rooms]);
    }

    // ── 3. Join page (GET) ─────────────────────────────────────────────────
    public function joinPage(string $code = null, string $team = null)
    {
        $user    = Auth::user();
        $student = $user->getOrCreateStudent();

        // No code → blank join form
        if (!$code) {
            return view('student.battle.join', [
                'room'            => null,
                'alreadyJoined'   => false,
                'preselectedTeam' => null,
                'joinError'       => session('error'),
                'student'         => $student,
                'user'            => $user,
            ]);
        }

        $room = BattleRoom::where('code', strtoupper($code))
            ->with(['host', 'quiz', 'participants.user'])
            ->first();

        // Room not found
        if (!$room) {
            return view('student.battle.join', [
                'room'            => null,
                'alreadyJoined'   => false,
                'preselectedTeam' => null,
                'joinError'       => "Room code \"{$code}\" not found or has already ended.",
                'student'         => $student,
                'user'            => $user,
            ]);
        }

        // Room finished → go to results
        if ($room->status === 'finished') {
            return redirect()->route('student.battle.results', $room->code);
        }

        // Room in progress
        if ($room->status === 'in_progress') {
            if ($room->hasParticipant(Auth::id())) {
                return redirect()->route('student.battle.arena', $room->code);
            }
            return view('student.battle.join', [
                'room'            => null,
                'alreadyJoined'   => false,
                'preselectedTeam' => null,
                'joinError'       => 'This battle has already started.',
                'student'         => $student,
                'user'            => $user,
            ]);
        }

        $alreadyJoined   = $room->hasParticipant(Auth::id());
        $preselectedTeam = $team;

        return view('student.battle.join', compact(
            'room',
            'alreadyJoined',
            'preselectedTeam',
            'student',
            'user'
        ));
    }

    // ── 4. Lookup room by code (AJAX GET) ──────────────────────────────────
    public function lookupRoom(string $code): JsonResponse
    {
        $room = BattleRoom::where('code', strtoupper($code))
            ->where('status', 'waiting')
            ->with(['quiz', 'host', 'participants.user'])
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found or battle has already started.',
            ]);
        }

        return response()->json([
            'success' => true,
            'room'    => [
                'code'        => $room->code,
                'mode'        => $room->mode,
                'quizTitle'   => $room->quiz->title ?? 'Quiz Battle',
                'hostName'    => $room->host->name,
                'playerCount' => $room->participants->count(),
                'maxPerTeam'  => $room->max_per_team,
                'teamAName'   => $room->team_a_name,
                'teamBName'   => $room->team_b_name,
                'teamACount'  => $room->participants->where('team', 'a')->count(),
                'teamBCount'  => $room->participants->where('team', 'b')->count(),
                'players'     => $room->participants->map(fn($p) => [
                    'name'   => $p->user->name,
                    'team'   => $p->team,
                    'isHost' => $p->user_id === $room->host_id,
                ])->values()->all(),
            ],
        ]);
    }

    // ── 5. Join room (AJAX POST) ───────────────────────────────────────────
    public function joinRoom(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|min:4|max:10',
            'team' => 'nullable|in:a,b',
        ]);

        $room = BattleRoom::where('code', strtoupper($request->code))
            ->with('participants')
            ->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found.']);
        }

        if ($room->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'This battle has already ended.']);
        }

        if ($room->status === 'in_progress') {
            if ($room->hasParticipant(Auth::id())) {
                return response()->json([
                    'success'     => true,
                    'redirectUrl' => route('student.battle.arena', $room->code),
                ]);
            }
            return response()->json(['success' => false, 'message' => 'Battle already in progress. Cannot join.']);
        }

        $userId = Auth::id();

        // Already in room
        if ($room->hasParticipant($userId)) {
            return response()->json([
                'success'     => true,
                'redirectUrl' => route('student.battle.lobby', $room->code),
            ]);
        }

        // 1v1 capacity
        if ($room->mode === '1v1' && $room->participants()->count() >= 2) {
            return response()->json(['success' => false, 'message' => 'This 1v1 room is full.']);
        }

        // Team mode: require team selection + capacity check
        if ($room->mode === 'team') {
            if (!$request->team) {
                return response()->json(['success' => false, 'message' => 'Please select a team (A or B) to join.']);
            }
            $teamCount = $room->participants()->where('team', $request->team)->count();
            if ($teamCount >= $room->max_per_team) {
                return response()->json(['success' => false, 'message' => 'This team is full. Please join the other team.']);
            }
        }

        BattleParticipant::create([
            'room_id' => $room->id,
            'user_id' => $userId,
            'team'    => $request->team,
            'status'  => 'joined',
        ]);

        $this->broadcastLobby($room->fresh(['participants.user']));

        return response()->json([
            'success'     => true,
            'redirectUrl' => route('student.battle.lobby', $room->code),
        ]);
    }

    // ── 6. Lobby page (GET) ────────────────────────────────────────────────
    public function lobby(string $code)
    {
        $user    = Auth::user();
        $student = $user->getOrCreateStudent();

        $room = BattleRoom::where('code', $code)
            ->with(['host', 'quiz', 'participants.user'])
            ->first();

        if (!$room) {
            return redirect()->route('student.battle.join.page')
                ->with('error', 'Room not found.');
        }

        if ($room->status === 'finished') {
            return redirect()->route('student.battle.results', $room->code);
        }

        if ($room->status === 'in_progress') {
            return redirect()->route('student.battle.arena', $room->code);
        }

        $userId = Auth::id();

        // Auto-join if somehow not in room (e.g. host navigated back)
        if (!$room->hasParticipant($userId)) {
            BattleParticipant::firstOrCreate(
                ['room_id' => $room->id, 'user_id' => $userId],
                ['team' => null, 'status' => 'joined']
            );
            $room->refresh();
            $this->broadcastLobby($room);
        }

        return view('student.battle.lobby', compact('room', 'student', 'user'));
    }

    // ── 7. Start Battle (POST, host only) ──────────────────────────────────
    public function startBattle(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $room = BattleRoom::where('code', $request->code)
            ->with(['quiz', 'participants'])
            ->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found.']);
        }

        if ($room->host_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Only the host can start the battle.']);
        }

        // Already started → just redirect
        if ($room->status === 'in_progress') {
            return response()->json([
                'success'     => true,
                'redirectUrl' => route('student.battle.arena', $room->code),
            ]);
        }

        if ($room->status !== 'waiting') {
            return response()->json(['success' => false, 'message' => 'Cannot start this battle.']);
        }

        if ($room->participants()->count() < 2) {
            return response()->json(['success' => false, 'message' => 'Need at least 2 players to start.']);
        }

        if (!$room->quiz || empty($room->quiz->questions)) {
            return response()->json(['success' => false, 'message' => 'Quiz has no questions.']);
        }

        $room->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        $room->participants()->update(['status' => 'playing']);

        // Broadcast questions WITHOUT answers
        $questions = collect($room->quiz->questions)->map(fn($q) => [
            'question' => $q['question'],
            'options'  => $q['options'],
            'topic'    => $q['topic'] ?? '',
        ])->values()->all();

        broadcast(new BattleStarted($room->code, $questions, $room->question_timer));

        return response()->json([
            'success'     => true,
            'redirectUrl' => route('student.battle.arena', $room->code),
        ]);
    }

    // ── 8. Arena page (GET) ────────────────────────────────────────────────
    public function arena(string $code)
    {
        $user    = Auth::user();
        $student = $user->getOrCreateStudent();

        $room = BattleRoom::where('code', $code)
            ->with(['quiz', 'participants.user', 'host'])
            ->first();

        if (!$room) {
            return redirect()->route('student.battle.join.page')
                ->with('error', 'Room not found.');
        }

        if ($room->status === 'finished') {
            return redirect()->route('student.battle.results', $room->code);
        }

        if ($room->status === 'waiting') {
            return redirect()->route('student.battle.lobby', $room->code);
        }

        if (!$room->hasParticipant(Auth::id())) {
            return redirect()->route('student.battle.join.page')
                ->with('error', 'You are not in this battle.');
        }

        return view('student.battle.arena', compact('room', 'student', 'user'));
    }

    // ── 9. Submit Answer (AJAX POST) ───────────────────────────────────────
    public function submitAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'room_code'      => 'required|string',
            'question_index' => 'required|integer|min:0',
            'selected'       => 'required|integer|min:-1|max:3',
            'time_ms'        => 'required|integer|min:0',
        ]);

        $room = BattleRoom::where('code', $request->room_code)
            ->where('status', 'in_progress')
            ->with('quiz')
            ->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found or not active.']);
        }

        $userId    = Auth::id();
        $qIdx      = $request->question_index;
        $selected  = $request->selected;
        $timeMsRaw = min($request->time_ms, $room->question_timer * 1000);

        // Already answered?
        if (BattleQuestionAnswer::where('room_id', $room->id)
            ->where('user_id', $userId)
            ->where('question_index', $qIdx)
            ->exists()
        ) {
            return response()->json(['success' => false, 'message' => 'Already answered.']);
        }

        $questions = $room->quiz->questions ?? [];
        $q         = $questions[$qIdx] ?? null;

        if (!$q) {
            return response()->json(['success' => false, 'message' => 'Invalid question index.']);
        }

        $isCorrect = ($selected === (int) $q['answer']);
        $timeRatio = $timeMsRaw > 0 ? (1 - ($timeMsRaw / ($room->question_timer * 1000))) : 1;
        $points    = $isCorrect ? max(20, (int) round(100 * $timeRatio)) : 0;

        BattleQuestionAnswer::create([
            'room_id'         => $room->id,
            'user_id'         => $userId,
            'question_index'  => $qIdx,
            'selected_option' => $selected,
            'is_correct'      => $isCorrect,
            'time_ms'         => $timeMsRaw,
            'points_earned'   => $points,
        ]);

        $participant = BattleParticipant::where('room_id', $room->id)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Participant record not found.']);
        }

        $newStreak = $isCorrect ? $participant->streak + 1 : 0;
        if ($isCorrect && $newStreak >= 3) $points += 20;

        $participant->update([
            'score'      => $participant->score + $points,
            'correct'    => $participant->correct + ($isCorrect ? 1 : 0),
            'wrong'      => $participant->wrong   + ($isCorrect ? 0 : 1),
            'streak'     => $newStreak,
            'max_streak' => max($participant->max_streak, $newStreak),
        ]);

        broadcast(new BattleAnswerSubmitted(
            $room->code,
            $this->getLiveScores($room),
            $qIdx,
            ['correct_option' => (int) $q['answer'], 'explanation' => $q['explanation'] ?? '']
        ));

        $this->checkAdvanceQuestion($room, $qIdx);

        return response()->json([
            'success'     => true,
            'isCorrect'   => $isCorrect,
            'points'      => $points,
            'streak'      => $newStreak,
            'correct'     => (int) $q['answer'],
            'explanation' => $q['explanation'] ?? '',
        ]);
    }

    // ── 10. Anti-cheat violation (AJAX POST) ───────────────────────────────
    public function reportViolation(Request $request): JsonResponse
    {
        $request->validate([
            'room_code' => 'required|string',
            'type'      => 'required|in:tab_switch,window_blur',
        ]);

        $room = BattleRoom::where('code', $request->room_code)
            ->where('status', 'in_progress')
            ->first();

        if (!$room) {
            return response()->json(['success' => true, 'disqualified' => false]);
        }

        $participant = BattleParticipant::where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant || $participant->disqualified) {
            return response()->json([
                'success'      => true,
                'disqualified' => (bool) ($participant->disqualified ?? false),
            ]);
        }

        $field = $request->type === 'tab_switch' ? 'tab_switches' : 'window_blurs';
        $participant->increment($field);
        $participant->refresh();

        $totalViolations = $participant->tab_switches + $participant->window_blurs;
        $disqualified    = $totalViolations >= 3;

        if ($disqualified) {
            $participant->update([
                'disqualified'      => true,
                'disqualify_reason' => 'Too many anti-cheat violations',
                'status'            => 'disqualified',
                'score'             => max(0, $participant->score - 50),
            ]);
        } elseif ($request->type === 'tab_switch') {
            $participant->update(['score' => max(0, $participant->score - 10)]);
        }

        broadcast(new BattleViolation(
            $room->code,
            Auth::id(),
            $request->type,
            $totalViolations,
            $disqualified
        ));

        return response()->json([
            'success'      => true,
            'violations'   => $totalViolations,
            'disqualified' => $disqualified,
        ]);
    }

    // ── 11. Results page (GET) ─────────────────────────────────────────────
    public function results(string $code)
    {
        $user    = Auth::user();
        $student = $user->getOrCreateStudent();

        $room = BattleRoom::where('code', $code)
            ->with(['participants.user', 'quiz', 'host'])
            ->first();

        if (!$room) {
            return redirect()->route('student.battle.join.page')
                ->with('error', 'Room not found.');
        }

        if ($room->status === 'in_progress') {
            $this->finishBattle($room);
            $room->refresh();
        }

        $myParticipant = $room->participants->firstWhere('user_id', Auth::id());

        return view('student.battle.results', compact('room', 'myParticipant', 'student', 'user'));
    }

    // ── 12. Rematch (AJAX POST) ────────────────────────────────────────────
    public function rematch(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $oldRoom = BattleRoom::where('code', $request->code)->first();

        if (!$oldRoom) {
            return response()->json(['success' => false, 'message' => 'Original room not found.']);
        }

        if ($oldRoom->host_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Only the host can start a rematch.']);
        }

        DB::beginTransaction();
        try {
            $newRoom = BattleRoom::create([
                'code'            => BattleRoom::generateCode(),
                'host_id'         => $oldRoom->host_id,
                'quiz_id'         => $oldRoom->quiz_id,
                'mode'            => $oldRoom->mode,
                'status'          => 'waiting',
                'team_a_name'     => $oldRoom->team_a_name,
                'team_b_name'     => $oldRoom->team_b_name,
                'max_per_team'    => $oldRoom->max_per_team,
                'question_timer'  => $oldRoom->question_timer,
                'total_questions' => $oldRoom->total_questions,
            ]);

            $oldRoom->participants->each(fn($p) => BattleParticipant::create([
                'room_id' => $newRoom->id,
                'user_id' => $p->user_id,
                'team'    => $p->team,
                'status'  => 'joined',
            ]));

            DB::commit();

            return response()->json([
                'success'     => true,
                'redirectUrl' => route('student.battle.lobby', $newRoom->code),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── 13. Battle history (GET) ───────────────────────────────────────────
    public function history()
    {
        $user    = Auth::user();
        $student = $user->getOrCreateStudent();

        $rooms = BattleRoom::whereHas('participants', fn($q) => $q->where('user_id', Auth::id()))
            ->where('status', 'finished')
            ->with(['participants.user', 'quiz', 'host'])
            ->latest()
            ->paginate(10);

        return view('student.battle.history', compact('rooms', 'student', 'user'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // Private helpers
    // ══════════════════════════════════════════════════════════════════════

    private function getLiveScores(BattleRoom $room): array
    {
        return BattleParticipant::where('room_id', $room->id)
            ->with('user')
            ->get()
            ->map(fn($p) => [
                'user_id' => $p->user_id,
                'name'    => $p->user->name,
                'score'   => $p->score,
                'correct' => $p->correct,
                'streak'  => $p->streak,
                'team'    => $p->team,
            ])
            ->sortByDesc('score')
            ->values()
            ->all();
    }

    private function checkAdvanceQuestion(BattleRoom $room, int $qIdx): void
    {
        $totalPlayers  = BattleParticipant::where('room_id', $room->id)
            ->where('disqualified', false)
            ->count();
        $answeredCount = BattleQuestionAnswer::where('room_id', $room->id)
            ->where('question_index', $qIdx)
            ->count();

        if ($answeredCount >= $totalPlayers) {
            if ($qIdx + 1 >= $room->total_questions) {
                $this->finishBattle($room);
            } else {
                broadcast(new BattleNextQuestion($room->code, $qIdx + 1));
            }
        }
    }

    private function safeTimeTaken($startTime): int
    {
        if (!$startTime) return 0;

        // Prevent future-time bugs
        if ($startTime->isFuture()) return 0;

        // Ensure non-negative integer
        return (int) max(0, now()->diffInSeconds($startTime, false));
    }

    private function finishBattle(BattleRoom $room): void
    {
        if ($room->status === 'finished') return;

        $participants = $room->participants()->with('user')->get();
        $total        = max(1, $room->total_questions);

        $participants->each(function ($p) use ($room, $total) {

            $accuracy = (int) round(($p->correct / $total) * 100);
            $xp       = $p->score + ($accuracy >= 80 ? 40 : 0);

            // ✅ Safe time calculation
            $timeTaken = $this->safeTimeTaken($room->started_at);

            // ✅ Update participant
            $p->update([
                'xp_earned'  => $xp,
                'status'     => 'finished',
                'time_taken' => $timeTaken,
            ]);

            // ✅ Store result safely
            QuizResult::create([
                'user_id'    => $p->user_id,
                'quiz_id'    => $room->quiz_id,
                'type'       => $room->mode,
                'score'      => $p->correct,
                'total_q'    => $total,
                'accuracy'   => $accuracy,
                'xp_earned'  => $xp,
                'time_taken' => $timeTaken,
                'answer_log' => $p->answer_log ?? [],
            ]);
        });

        // ── Determine winner ──
        $winnerTeam = null;
        $winnerUser = null;

        if ($room->mode === 'team') {
            $scoreA = $participants->where('team', 'a')->sum('score');
            $scoreB = $participants->where('team', 'b')->sum('score');

            $winnerTeam = $scoreA >= $scoreB
                ? $room->team_a_name
                : $room->team_b_name;
        } else {
            $top = $participants->sortByDesc('score')->first();

            if ($top) {
                $winnerUser = [
                    'id'   => $top->user_id,
                    'name' => $top->user->name,
                ];
            }
        }

        // ── Final scoreboard ──
        $finalScores = $participants->map(function ($p) use ($total) {
            return [
                'user_id'  => $p->user_id,
                'name'     => $p->user->name,
                'score'    => $p->score,
                'correct'  => $p->correct,
                'wrong'    => $p->wrong,
                'streak'   => $p->max_streak,
                'xp'       => $p->xp_earned,
                'team'     => $p->team,
                'accuracy' => (int) round(($p->correct / $total) * 100),
                'disq'     => (bool) $p->disqualified,
            ];
        })->sortByDesc('score')->values()->all();

        // ── Update room ──
        $room->update([
            'status'         => 'finished',
            'finished_at'    => now(),
            'final_scores'   => $finalScores,
            'winner_team'    => $winnerTeam,
            'winner_user_id' => $winnerUser['id'] ?? null,
        ]);

        // ── Broadcast result ──
        broadcast(new BattleFinished(
            $room->code,
            $finalScores,
            $winnerTeam,
            $winnerUser,
            $room->mode
        ));
    }

    private function broadcastLobby(BattleRoom $room): void
    {
        $room->loadMissing('participants.user');

        $participants = $room->participants->map(fn($p) => [
            'id'     => $p->user_id,
            'name'   => $p->user->name,
            'team'   => $p->team,
            'status' => $p->status,
        ])->all();

        broadcast(new BattleLobbyUpdated($room->code, $participants, $room->status));
    }

    public function lobbyState(string $code): JsonResponse
    {
        $room = BattleRoom::where('code', $code)
            ->with(['participants.user'])
            ->first();

        if (!$room) {
            return response()->json(['success' => false]);
        }

        $participants = $room->participants->map(fn($p) => [
            'id'     => $p->user_id,
            'name'   => $p->user->name,
            'team'   => $p->team,
            'status' => $p->status,
        ])->values()->all();

        return response()->json([
            'success'      => true,
            'status'       => $room->status,
            'participants' => $participants,
            'count'        => count($participants),
        ]);
    }

    /**
     * Arena state poll (AJAX GET) — fallback for WebSocket.
     * Returns current scores + room status.
     *
     * GET /student/battle/arena-state/{code}
     */
    public function arenaState(string $code): JsonResponse
    {
        $room = BattleRoom::where('code', $code)
            ->with(['participants.user'])
            ->first();

        if (!$room) {
            return response()->json(['success' => false]);
        }

        $scores = BattleParticipant::where('room_id', $room->id)
            ->with('user')
            ->get()
            ->map(fn($p) => [
                'user_id' => $p->user_id,
                'name'    => $p->user->name,
                'score'   => $p->score,
                'correct' => $p->correct,
                'streak'  => $p->streak,
                'team'    => $p->team,
            ])
            ->sortByDesc('score')
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'status'  => $room->status,
            'scores'  => $scores,
        ]);
    }
}
