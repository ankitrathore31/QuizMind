<?php

namespace App\Http\Controllers;

use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use App\Models\BattleQuestionAnswer;
use App\Models\InstitutionBattle;
use App\Models\InstitutionBattleParticipant;
use App\Models\Institution;
use App\Models\Quiz;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstitutionBattleController extends Controller
{
    // ── Institution Dashboard ──────────────────────────────────────────────
    public function dashboard()
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return redirect()->route('institution.register');
        }

        // Students linked to institution
        $students = Student::whereHas('user', fn($q) => $q->where('institution_id', $institution->id))
            ->with('user')
            ->get();

        $topStudents = $students->sortByDesc('xp')->take(5);

        $stats = [
            'total_students'  => $students->count(),
            'total_quizzes'   => $students->sum('total_quizzes'),
            'avg_accuracy'    => $students->count() > 0 ? round($students->avg('accuracy')) : 0,
            'top_student_name'=> $topStudents->first()?->display_name ?? $topStudents->first()?->user->name ?? '—',
        ];

        $activeBattles = InstitutionBattle::where('host_institution_id', $institution->id)
            ->whereIn('status', ['waiting', 'in_progress'])
            ->withCount('participants')
            ->latest()
            ->take(5)
            ->get();

        $savedQuizzes = Quiz::where('user_id', $user->id)->latest()->take(20)->get();

        return view('institution.dashboard', compact(
            'institution', 'students', 'topStudents', 'stats', 'activeBattles', 'savedQuizzes'
        ));
    }

    // ── Create Institution Battle ──────────────────────────────────────────
    public function createBattle(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id'           => 'required|integer|exists:quizzes,id',
            'institution_count' => 'required|integer|between:2,4',
            'institution_names' => 'required|array|min:1|max:3',
            'institution_names.*' => 'required|string|max:100',
            'student_limit'     => 'integer|min:5|max:500',
            'question_timer'    => 'integer|between:10,60',
            'anti_cheat'        => 'boolean',
            'scheduled_at'      => 'nullable|date|after:now',
        ]);

        $user = Auth::user();
        $institution = $user->institution;
        $quiz = Quiz::find($request->quiz_id);

        if (!$quiz || empty($quiz->questions)) {
            return response()->json(['success' => false, 'message' => 'Quiz not found or has no questions.']);
        }

        DB::beginTransaction();
        try {
            $battle = InstitutionBattle::create([
                'code'                  => $this->generateCode(),
                'host_institution_id'   => $institution->id,
                'quiz_id'               => $quiz->id,
                'status'                => 'waiting',
                'institution_count'     => $request->institution_count,
                'student_limit'         => $request->student_limit ?? 50,
                'question_timer'        => $request->question_timer ?? 20,
                'total_questions'       => count($quiz->questions),
                'anti_cheat'            => $request->input('anti_cheat', true),
                'scheduled_at'          => $request->scheduled_at,
            ]);

            // Create host institution entry
            $hostEntry = InstitutionBattleParticipant::create([
                'battle_id'    => $battle->id,
                'institution_id' => $institution->id,
                'name'         => $institution->name,
                'student_code' => $this->generateStudentCode(),
                'is_host'      => true,
                'total_score'  => 0,
            ]);

            $institutionCodes = [[
                'name' => $institution->name . ' (Your Institution)',
                'code' => $hostEntry->student_code,
            ]];

            // Create guest institution entries
            foreach ($request->institution_names as $name) {
                $entry = InstitutionBattleParticipant::create([
                    'battle_id'    => $battle->id,
                    'institution_id' => null,
                    'name'         => $name,
                    'student_code' => $this->generateStudentCode(),
                    'is_host'      => false,
                    'total_score'  => 0,
                ]);
                $institutionCodes[] = ['name' => $name, 'code' => $entry->student_code];
            }

            DB::commit();

            return response()->json([
                'success'          => true,
                'roomCode'         => $battle->code,
                'manageUrl'        => route('institution.battle.manage', $battle->code),
                'institutionCodes' => $institutionCodes,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Manage page ────────────────────────────────────────────────────────
    public function manage(string $code)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        $room = InstitutionBattle::where('code', $code)
            ->with(['institutionParticipants', 'quiz', 'participants.user'])
            ->first();

        if (!$room || $room->host_institution_id !== $institution->id) {
            return redirect()->route('institution.dashboard')->with('error', 'Battle not found.');
        }

        if ($room->status === 'finished') {
            return redirect()->route('institution.battle.results', $room->code);
        }

        $violations = DB::table('battle_violations')
            ->where('battle_id', $room->id)
            ->join('users', 'battle_violations.user_id', '=', 'users.id')
            ->select('users.name', 'battle_violations.type', 'battle_violations.disqualified')
            ->latest()
            ->take(20)
            ->get()
            ->toArray();

        $room->violations = $violations;

        return view('institution.battle.manage', compact('room'));
    }

    // ── Student joins via institution code ────────────────────────────────
    public function joinViaCode(Request $request): JsonResponse
    {
        $request->validate(['student_code' => 'required|string']);

        $code = strtoupper($request->student_code);
        $instParticipant = InstitutionBattleParticipant::where('student_code', $code)->first();

        if (!$instParticipant) {
            return response()->json(['success' => false, 'message' => 'Invalid student code.']);
        }

        $battle = $instParticipant->battle;

        if (!$battle || $battle->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'Battle not found or already finished.']);
        }

        if ($battle->status === 'in_progress') {
            return response()->json(['success' => false, 'message' => 'Battle has already started.']);
        }

        $userId = Auth::id();

        // Check capacity
        $currentCount = $battle->participants()
            ->where('institution_battle_participant_id', $instParticipant->id)
            ->count();

        if ($currentCount >= $battle->student_limit) {
            return response()->json(['success' => false, 'message' => 'This institution\'s slots are full.']);
        }

        // Already joined?
        $existing = $battle->participants()->where('user_id', $userId)->first();
        if ($existing) {
            return response()->json([
                'success'     => true,
                'redirectUrl' => route('institution.battle.arena', $battle->code),
            ]);
        }

        BattleParticipant::create([
            'room_id'                          => null,
            'inst_battle_id'                   => $battle->id,
            'institution_id'                   => $instParticipant->institution_id,
            'institution_battle_participant_id' => $instParticipant->id,
            'user_id'                          => $userId,
            'status'                           => 'joined',
            'score'                            => 0,
        ]);

        // Broadcast student joined
        try {
            broadcast(new \App\Events\InstitutionBattleStudentJoined(
                $battle->code,
                $battle->participants()->count(),
                $instParticipant->id,
                $instParticipant->participants()->count()
            ));
        } catch (\Throwable $e) {}

        return response()->json([
            'success'     => true,
            'redirectUrl' => route('institution.battle.arena', $battle->code),
        ]);
    }

    // ── Student joins page (GET) ───────────────────────────────────────────
    public function joinPage(string $code = null)
    {
        $battle = null;
        $myInstitution = null;
        $joinError = null;

        if ($code) {
            $instParticipant = InstitutionBattleParticipant::where('student_code', strtoupper($code))->first();
            if ($instParticipant) {
                $battle = $instParticipant->battle;
                $myInstitution = $instParticipant;
                if ($battle && $battle->status === 'in_progress' && $battle->participants()->where('user_id', Auth::id())->exists()) {
                    return redirect()->route('institution.battle.arena', $battle->code);
                }
            } else {
                $joinError = 'Invalid institution code.';
            }
        }

        return view('institution.battle.join', compact('battle', 'myInstitution', 'joinError'));
    }

    // ── Arena page ────────────────────────────────────────────────────────
    public function arena(string $code)
    {
        $user = Auth::user();
        $room = InstitutionBattle::where('code', $code)
            ->with(['quiz', 'institutionParticipants', 'participants.user'])
            ->first();

        if (!$room) {
            return redirect()->route('institution.battle.join.page')->with('error', 'Battle not found.');
        }

        if ($room->status === 'finished') {
            return redirect()->route('institution.battle.results', $room->code);
        }

        $myParticipant = $room->participants()->where('user_id', Auth::id())->first();
        $myInstitution = $myParticipant
            ? $room->institutionParticipants->find($myParticipant->institution_battle_participant_id)
            : null;

        if (!$myParticipant) {
            return redirect()->route('institution.battle.join.page')->with('error', 'You are not in this battle.');
        }

        return view('institution.battle.arena', compact('room', 'myInstitution', 'myParticipant'));
    }

    // ── Start Battle ──────────────────────────────────────────────────────
    public function startBattle(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $user        = Auth::user();
        $institution = $user->institution;
        $room = InstitutionBattle::where('code', $request->code)->first();

        if (!$room || $room->host_institution_id !== $institution->id) {
            return response()->json(['success' => false, 'message' => 'Not authorized.']);
        }

        if ($room->status !== 'waiting') {
            return response()->json(['success' => false, 'message' => 'Battle cannot be started.']);
        }

        if ($room->participants()->count() < 2) {
            return response()->json(['success' => false, 'message' => 'Need at least 2 students to start.']);
        }

        $room->update(['status' => 'in_progress', 'started_at' => now()]);
        $room->participants()->update(['status' => 'playing']);

        $questions = collect($room->quiz->questions)->map(fn($q) => [
            'question' => $q['question'],
            'options'  => $q['options'],
            'topic'    => $q['topic'] ?? '',
        ])->values()->all();

        try {
            broadcast(new \App\Events\InstitutionBattleStarted($room->code, $questions, $room->question_timer));
        } catch (\Throwable $e) {}

        return response()->json(['success' => true]);
    }

    // ── Submit Answer ─────────────────────────────────────────────────────
    public function submitAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'room_code'      => 'required|string',
            'question_index' => 'required|integer|min:0',
            'selected'       => 'required|integer|min:-1|max:3',
            'time_ms'        => 'required|integer|min:0',
        ]);

        $room = InstitutionBattle::where('code', $request->room_code)
            ->where('status', 'in_progress')
            ->with('quiz')
            ->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Battle not found.']);
        }

        $userId   = Auth::id();
        $qIdx     = $request->question_index;
        $selected = $request->selected;
        $timeMs   = min($request->time_ms, $room->question_timer * 1000);

        // Already answered?
        if (BattleQuestionAnswer::where('inst_battle_id', $room->id)
            ->where('user_id', $userId)
            ->where('question_index', $qIdx)
            ->exists()
        ) {
            return response()->json(['success' => false, 'message' => 'Already answered.']);
        }

        $questions = $room->quiz->questions ?? [];
        $q         = $questions[$qIdx] ?? null;
        if (!$q) return response()->json(['success' => false, 'message' => 'Invalid question.']);

        $isCorrect = ($selected === (int) $q['answer']);
        $timeRatio = $timeMs > 0 ? (1 - ($timeMs / ($room->question_timer * 1000))) : 1;
        $points    = $isCorrect ? max(20, (int) round(100 * $timeRatio)) : 0;

        BattleQuestionAnswer::create([
            'inst_battle_id'   => $room->id,
            'room_id'          => null,
            'user_id'          => $userId,
            'question_index'   => $qIdx,
            'selected_option'  => $selected,
            'is_correct'       => $isCorrect,
            'time_ms'          => $timeMs,
            'points_earned'    => $points,
        ]);

        $participant = $room->participants()->where('user_id', $userId)->lockForUpdate()->first();
        if (!$participant) return response()->json(['success' => false, 'message' => 'Participant not found.']);

        $newStreak = $isCorrect ? $participant->streak + 1 : 0;
        if ($isCorrect && $newStreak >= 3) $points += 20;

        $participant->update([
            'score'      => $participant->score + $points,
            'correct'    => $participant->correct + ($isCorrect ? 1 : 0),
            'wrong'      => $participant->wrong   + ($isCorrect ? 0 : 1),
            'streak'     => $newStreak,
            'max_streak' => max($participant->max_streak ?? 0, $newStreak),
        ]);

        // Update institution total
        $instParticipant = $room->institutionParticipants->find($participant->institution_battle_participant_id);
        if ($instParticipant) {
            $instTotal = $room->participants()
                ->where('institution_battle_participant_id', $instParticipant->id)
                ->sum('score');
            $instParticipant->update(['total_score' => $instTotal]);
        }

        // Broadcast live scores
        $scores = $this->getLiveScores($room);
        try {
            broadcast(new \App\Events\InstitutionBattleScoresUpdated($room->code, $scores, $qIdx, [
                'correct_option' => (int) $q['answer'],
                'explanation'    => $q['explanation'] ?? '',
            ]));
        } catch (\Throwable $e) {}

        // Check if all answered
        $this->checkAdvance($room, $qIdx);

        return response()->json([
            'success'     => true,
            'isCorrect'   => $isCorrect,
            'points'      => $points,
            'streak'      => $newStreak,
            'correct'     => (int) $q['answer'],
            'explanation' => $q['explanation'] ?? '',
        ]);
    }

    // ── Report Violation ──────────────────────────────────────────────────
    public function reportViolation(Request $request): JsonResponse
    {
        $request->validate(['room_code' => 'required|string', 'type' => 'required|in:tab_switch,window_blur']);

        $room = InstitutionBattle::where('code', $request->room_code)->where('status','in_progress')->first();
        if (!$room) return response()->json(['success' => true, 'disqualified' => false]);

        $participant = $room->participants()->where('user_id', Auth::id())->first();
        if (!$participant || $participant->disqualified) {
            return response()->json(['success' => true, 'disqualified' => (bool)($participant->disqualified ?? false)]);
        }

        $field = $request->type === 'tab_switch' ? 'tab_switches' : 'window_blurs';
        $participant->increment($field);
        $participant->refresh();

        $total = $participant->tab_switches + $participant->window_blurs;
        $disq  = $total >= 3;

        if ($disq) {
            $participant->update(['disqualified' => true, 'status' => 'disqualified', 'score' => max(0, $participant->score - 50)]);
        } elseif ($request->type === 'tab_switch') {
            $participant->update(['score' => max(0, $participant->score - 10)]);
        }

        // Log violation
        DB::table('battle_violations')->insert([
            'battle_id'     => $room->id,
            'user_id'       => Auth::id(),
            'type'          => $request->type,
            'violations'    => $total,
            'disqualified'  => $disq,
            'created_at'    => now(),
        ]);

        return response()->json(['success' => true, 'violations' => $total, 'disqualified' => $disq]);
    }

    // ── Force Next Question ───────────────────────────────────────────────
    public function nextQuestion(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $user        = Auth::user();
        $institution = $user->institution;
        $room = InstitutionBattle::where('code', $request->code)->first();

        if (!$room || $room->host_institution_id !== $institution->id) {
            return response()->json(['success' => false, 'message' => 'Not authorized.']);
        }

        $nextIdx = ($room->current_question ?? 0) + 1;
        $room->update(['current_question' => $nextIdx]);

        if ($nextIdx >= $room->total_questions) {
            $this->finishBattle($room);
        } else {
            try {
                broadcast(new \App\Events\InstitutionBattleNextQuestion($room->code, $nextIdx));
            } catch (\Throwable $e) {}
        }

        return response()->json(['success' => true]);
    }

    // ── End Battle ────────────────────────────────────────────────────────
    public function endBattle(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $room = InstitutionBattle::where('code', $request->code)->first();
        if (!$room) return response()->json(['success' => false, 'message' => 'Not found.']);

        $this->finishBattle($room);

        return response()->json([
            'success'    => true,
            'resultsUrl' => route('institution.battle.results', $room->code),
        ]);
    }

    // ── Results ───────────────────────────────────────────────────────────
    public function results(string $code)
    {
        $room = InstitutionBattle::where('code', $code)
            ->with(['institutionParticipants', 'participants.user', 'quiz'])
            ->first();

        if (!$room) {
            return redirect()->route('institution.dashboard');
        }

        if ($room->status === 'in_progress') {
            $this->finishBattle($room);
            $room->refresh();
        }

        $room->winner_institution = $room->institutionParticipants
            ->sortByDesc('total_score')
            ->first();

        return view('institution.battle.results', compact('room'));
    }

    // ── Arena State Poll ──────────────────────────────────────────────────
    public function arenaState(string $code): JsonResponse
    {
        $room = InstitutionBattle::where('code', $code)
            ->with(['institutionParticipants', 'participants.user'])
            ->first();

        if (!$room) return response()->json(['success' => false]);

        return response()->json([
            'success'          => true,
            'status'           => $room->status,
            'scores'           => $this->getLiveScores($room),
            'current_question' => $room->current_question ?? 0,
            'active_count'     => $room->participants()->where('status','playing')->count(),
            'total_count'      => $room->participants()->count(),
            'answered_count'   => BattleQuestionAnswer::where('inst_battle_id',$room->id)->where('question_index',$room->current_question??0)->count(),
        ]);
    }

    // ── Manage State Poll ─────────────────────────────────────────────────
    public function manageState(string $code): JsonResponse
    {
        $room = InstitutionBattle::where('code', $code)->with(['institutionParticipants','participants'])->first();
        if (!$room) return response()->json(['success' => false]);

        return response()->json([
            'success'          => true,
            'status'           => $room->status,
            'scores'           => $this->getLiveScores($room),
            'active_count'     => $room->participants()->where('status','playing')->count(),
            'total_count'      => $room->participants()->count(),
            'current_question' => $room->current_question ?? 0,
            'answered_count'   => BattleQuestionAnswer::where('inst_battle_id',$room->id)->where('question_index',$room->current_question??0)->count(),
        ]);
    }

    // ── Rematch ───────────────────────────────────────────────────────────
    public function rematch(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $old  = InstitutionBattle::where('code', $request->code)->first();
        if (!$old) return response()->json(['success' => false, 'message' => 'Not found.']);

        DB::beginTransaction();
        try {
            $new = InstitutionBattle::create([
                'code'                => $this->generateCode(),
                'host_institution_id' => $old->host_institution_id,
                'quiz_id'             => $old->quiz_id,
                'status'              => 'waiting',
                'institution_count'   => $old->institution_count,
                'student_limit'       => $old->student_limit,
                'question_timer'      => $old->question_timer,
                'total_questions'     => $old->total_questions,
                'anti_cheat'          => $old->anti_cheat,
            ]);

            foreach ($old->institutionParticipants as $ip) {
                InstitutionBattleParticipant::create([
                    'battle_id'      => $new->id,
                    'institution_id' => $ip->institution_id,
                    'name'           => $ip->name,
                    'student_code'   => $this->generateStudentCode(),
                    'is_host'        => $ip->is_host,
                    'total_score'    => 0,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'redirectUrl' => route('institution.battle.manage', $new->code)]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // Private Helpers
    // ══════════════════════════════════════════════════════════════════════

    private function getLiveScores(InstitutionBattle $room): array
    {
        return $room->institutionParticipants->map(function ($ip) use ($room) {
            $students = $room->participants()
                ->where('institution_battle_participant_id', $ip->id)
                ->with('user')
                ->get()
                ->sortByDesc('score')
                ->values();

            return [
                'institution_id' => $ip->id,
                'name'           => $ip->name,
                'total_score'    => $students->sum('score'),
                'student_count'  => $students->count(),
                'students'       => $students->take(10)->map(fn($p) => [
                    'user_id' => $p->user_id,
                    'name'    => $p->user->name,
                    'score'   => $p->score,
                    'correct' => $p->correct,
                    'streak'  => $p->streak,
                ])->values()->all(),
            ];
        })->sortByDesc('total_score')->values()->all();
    }

    private function checkAdvance(InstitutionBattle $room, int $qIdx): void
    {
        $total    = $room->participants()->where('disqualified', false)->count();
        $answered = BattleQuestionAnswer::where('inst_battle_id', $room->id)->where('question_index', $qIdx)->count();

        if ($answered >= $total) {
            $nextIdx = $qIdx + 1;
            $room->update(['current_question' => $nextIdx]);
            if ($nextIdx >= $room->total_questions) {
                $this->finishBattle($room);
            } else {
                try {
                    broadcast(new \App\Events\InstitutionBattleNextQuestion($room->code, $nextIdx));
                } catch (\Throwable $e) {}
            }
        }
    }

    private function finishBattle(InstitutionBattle $room): void
    {
        if ($room->status === 'finished') return;

        $total = max(1, $room->total_questions);
        $participants = $room->participants()->with('user')->get();

        foreach ($participants as $p) {
            $acc = (int) round(($p->correct / $total) * 100);
            $xp  = $p->score + ($acc >= 80 ? 40 : 0);
            $p->update(['xp_earned' => $xp, 'status' => 'finished', 'time_taken' => max(0, (int) now()->diffInSeconds($room->started_at))]);
        }

        // Update institution totals
        foreach ($room->institutionParticipants as $ip) {
            $total_score = $room->participants()->where('institution_battle_participant_id', $ip->id)->sum('score');
            $ip->update(['total_score' => $total_score]);
        }

        $winner = $room->institutionParticipants->sortByDesc('total_score')->first();

        $finalScores = $this->getLiveScores($room);
        $room->update([
            'status'         => 'finished',
            'finished_at'    => now(),
            'final_scores'   => $finalScores,
            'winner_inst_id' => $winner?->id,
        ]);

        try {
            broadcast(new \App\Events\InstitutionBattleFinished($room->code, $finalScores, $winner?->name));
        } catch (\Throwable $e) {}
    }

    private function generateCode(): string
    {
        do { $code = 'IB' . strtoupper(Str::random(6)); }
        while (InstitutionBattle::where('code', $code)->exists());
        return $code;
    }

    private function generateStudentCode(): string
    {
        do { $code = strtoupper(Str::random(8)); }
        while (InstitutionBattleParticipant::where('student_code', $code)->exists());
        return $code;
    }
}