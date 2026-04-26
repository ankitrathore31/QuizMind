<?php

namespace App\Http\Controllers;

use App\Events\InstitutionAnswerSubmitted;
use App\Events\InstitutionBattleCountdownUpdated;
use App\Events\InstitutionBattleFinished;
use App\Events\InstitutionBattleLobbyUpdated;
use App\Events\InstitutionBattleRegistrationClosed;
use App\Events\InstitutionBattleStarted;
use App\Events\InstitutionBattleViolation;
use App\Events\InstitutionNextQuestion;
use App\Models\Institution;
use App\Models\InstitutionBattle;
use App\Models\InstitutionBattleHistory;
use App\Models\InstitutionBattleParticipant;
use App\Models\InstitutionBattleQuestionAnswer;
use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstitutionBattleController extends Controller
{

    public function setup(Request $request)
    {
        $user        = Auth::user();
        $quizId      = $request->query('quizId');
        $institution = $this->resolveInstitution($user);
        $quizzes     = Quiz::where('user_id', $user->id)->latest()->get();
        $quiz        = $quizId ? Quiz::find($quizId) : null;

        return view('institution.battle.setup', compact('quiz', 'quizzes', 'institution', 'user'));
    }

    public function createBattle(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id'                  => 'required|integer|exists:quizzes,id',
            'battle_type'              => 'required|in:2school,3school',
            'question_timer'           => 'nullable|integer|between:10,60',
            'anti_cheat'               => 'nullable|boolean',
            // 'students_per_institution' => 'nullable|integer|in:20,50,70,100,150,250',
        ]);

        $quiz = Quiz::where('id', $request->quiz_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$quiz) {
            return response()->json(['success' => false, 'message' => 'Quiz not found.'], 404);
        }

        $user        = Auth::user();
        $hostInst    = $this->resolveInstitution($user);
        $hostInstId  = $hostInst?->id;

        $participatingIds = $hostInstId ? [$hostInstId] : [];

        DB::beginTransaction();
        try {
            $battle = InstitutionBattle::create([
                'institution_id'             => $hostInstId,
                'created_by'                 => Auth::id(),
                'quiz_id'                    => $quiz->id,
                'code'                       => InstitutionBattle::generateCode(),
                'status'                     => 'setup',
                'battle_type'                => $request->battle_type,
                'participating_institutions' => $participatingIds,
                'question_timer'             => $request->question_timer ?? 20,
                'total_questions'            => count($quiz->questions ?? []),
                'anti_cheat'                 => $request->input('anti_cheat', true),
            ]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'battleCode'  => $battle->code,
                'redirectUrl' => route('institution.battle.setup-page', $battle->code),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function setupPage(string $code)
    {
        $user   = Auth::user();
        $battle = InstitutionBattle::where('code', $code)
            ->with(['quiz', 'creator', 'institution', 'participants.user', 'participants.institution'])
            ->first();

        if (!$battle) {
            return redirect()->route('institution.battle.join.page')->with('error', 'Battle not found.');
        }

        if ($battle->created_by !== Auth::id()) {
            $institution = $this->resolveInstitution($user);
            if (
                $institution &&
                in_array($institution->id, array_filter($battle->participating_institutions ?? []))
            ) {
                return view('institution.battle.setup-page', compact('battle', 'institution', 'user'));
            }
            return redirect()->route('institution.battle.arena', $battle->code);
        }

        if ($battle->isFinished()) {
            return redirect()->route('institution.battle.results', $battle->code);
        }

        if (in_array($battle->status, ['in_progress', 'countdown'])) {
            return redirect()->route('institution.battle.arena', $battle->code);
        }

        $institution = $this->resolveInstitution($user) ?? $battle->institution;

        return view('institution.battle.setup-page', compact(
            'battle',
            'institution',
            'user'
        ));
    }

    public function lookupBattle(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|min:4|max:30']);

        $raw        = strtoupper(trim($request->code));
        $masterCode = InstitutionBattle::resolveMasterCode($raw);

        $battle = InstitutionBattle::where('code', $masterCode)
            ->with(['quiz', 'creator', 'institution'])
            ->first();

        if (!$battle) {
            return response()->json([
                'success' => false,
                'message' => 'Battle not found. Please check the code and try again.',
            ]);
        }

        if ($battle->isFinished()) {
            return response()->json(['success' => false, 'message' => 'This battle has already ended.']);
        }

        if (in_array($battle->status, ['in_progress', 'countdown'])) {
            return response()->json(['success' => false, 'message' => 'This battle has already started.']);
        }

        if ($raw === $masterCode) {
            return response()->json([
                'success' => false,
                'message' => 'That is the host code. Use the institution invite code (e.g. ' . $masterCode . '-S2X9).',
            ]);
        }

        $suffixes  = InstitutionBattle::institutionSubSuffixes();
        $validCode = false;
        foreach ($suffixes as $suffix) {
            if ($raw === $masterCode . '-' . $suffix) {
                $validCode = true;
                break;
            }
        }

        if (!$validCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid institution code. Make sure you are using the code ending in -S2X9 or -S3Y8, not a student code.',
            ]);
        }

        $participating = $battle->participating_institutions ?? [];
        $totalSlots    = $battle->battle_type === '3school' ? 3 : 2;
        $slots         = [];

        for ($i = 0; $i < $totalSlots; $i++) {
            $instId      = $participating[$i] ?? null;
            $slots[$i + 1] = $instId ? (Institution::find($instId)?->name ?? 'Unknown') : null;
        }

        return response()->json([
            'success'         => true,
            'quizTitle'       => $battle->quiz?->title ?? 'Untitled Quiz',
            'subject'         => $battle->quiz?->subject ?? null,
            'totalQuestions'  => $battle->total_questions,
            'questionTimer'   => $battle->question_timer,
            'battleType'      => $battle->battle_type,
            'status'          => $battle->status,
            'hostInstitution' => optional($battle->institution)->name
                ?? optional($battle->creator)->name
                ?? 'Host',
            'slots'           => $slots,
        ]);
    }

    public function institutionJoinPage(Request $request)
    {
        $code = $request->query('code');
        return view('institution.battle.join', compact('code'));
    }

    public function institutionJoin(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|min:4|max:30']);

        $raw        = strtoupper(trim($request->code));
        $masterCode = InstitutionBattle::resolveMasterCode($raw);

        $battle = InstitutionBattle::where('code', $masterCode)->first();

        if (!$battle) {
            return response()->json([
                'success' => false,
                'message' => 'Battle not found. Please check the code and try again.',
            ]);
        }

        if ($battle->isFinished()) {
            return response()->json(['success' => false, 'message' => 'This battle has already ended.']);
        }

        if (in_array($battle->status, ['in_progress', 'countdown'])) {
            return response()->json(['success' => false, 'message' => 'This battle has already started.']);
        }

        $suffixes   = InstitutionBattle::institutionSubSuffixes();
        $schoolSlot = null;

        foreach ($suffixes as $idx => $suffix) {
            if ($raw === $masterCode . '-' . $suffix) {
                $schoolSlot = $idx + 2;
                break;
            }
        }

        if ($raw === $masterCode) {
            return response()->json([
                'success' => false,
                'message' => 'That is the host code. Please use the institution invite code (e.g. ' . $masterCode . '-S2X9).',
            ]);
        }

        if ($schoolSlot === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid institution code. Make sure you are using the code that ends in -S2X9 or -S3Y8, not a student code.',
            ]);
        }

        $user      = Auth::user();
        $adminInst = $this->resolveInstitution($user);

        if (!$adminInst) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not linked to an institution. Please contact support.',
            ]);
        }

        $slotIndex     = $schoolSlot - 1;
        $participating = $battle->participating_institutions ?? [];

        if (in_array($adminInst->id, $participating)) {
            $existingSlot = array_search($adminInst->id, $participating) + 1;
            return response()->json([
                'success'         => true,
                'alreadyJoined'   => true,
                'institutionName' => $adminInst->name,
                'studentCode'     => $battle->studentCodeForSlot($existingSlot),
                'lobbyUrl'        => route('institution.battle.setup-page', $battle->code),
            ]);
        }

        if (!empty($participating[$slotIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'School slot ' . $schoolSlot . ' is already taken by another institution.',
            ]);
        }

        $totalSlots = $battle->battle_type === '3school' ? 3 : 2;
        while (count($participating) < $totalSlots) {
            $participating[] = null;
        }
        $participating[$slotIndex] = $adminInst->id;

        $battle->update(['participating_institutions' => $participating]);

        $studentCode = $battle->studentCodeForSlot($schoolSlot);

        $this->broadcastLobby($battle->fresh(['participants.user', 'participants.institution']));

        return response()->json([
            'success'         => true,
            'institutionName' => $adminInst->name,
            'studentCode'     => $studentCode,
            'schoolSlot'      => $schoolSlot,
            'lobbyUrl'        => route('institution.battle.setup-page', $battle->code),
        ]);
    }

    public function joinBattle(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|min:4|max:30']);

        $raw        = strtoupper(trim($request->code));
        $masterCode = InstitutionBattle::resolveMasterCode($raw);

        $battle = InstitutionBattle::where('code', $masterCode)
            ->with('participants')
            ->first();

        if (!$battle) {
            return response()->json(['success' => false, 'message' => 'Battle not found.']);
        }

        if ($battle->created_by === Auth::id()) {
            return response()->json([
                'success'     => true,
                'redirectUrl' => route('institution.battle.setup-page', $battle->code),
            ]);
        }

        if ($battle->isFinished()) {
            return response()->json(['success' => false, 'message' => 'This battle has already ended.']);
        }

        $schoolSlot = $battle->resolveStudentSlot($raw);

        if ($schoolSlot === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student code. Make sure you use the code your admin shared (e.g. JICR9XHV-STU2).',
            ]);
        }

        $participating = $battle->participating_institutions ?? [];
        $instId        = $participating[$schoolSlot - 1] ?? null;

        if (!$instId) {
            return response()->json([
                'success' => false,
                'message' => "School {$schoolSlot} hasn't joined yet. Ask your admin to join first.",
            ]);
        }

        $user = Auth::user();

        if ($battle->hasParticipant($user->id)) {
            return response()->json([
                'success'     => true,
                'redirectUrl' => route('institution.battle.arena', $battle->code),
                'battleInfo'  => $this->buildBattleInfo($battle, $instId),
            ]);
        }

        if ($battle->students_per_institution) {
            $currentCount = InstitutionBattleParticipant::where('battle_id', $battle->id)
                ->where('institution_id', $instId)
                ->count();

            if ($currentCount >= $battle->students_per_institution) {
                return response()->json([
                    'success' => false,
                    'message' => "School {$schoolSlot} has reached its student limit ({$battle->students_per_institution}).",
                ]);
            }
        }

        InstitutionBattleParticipant::create([
            'battle_id'      => $battle->id,
            'user_id'        => $user->id,
            'institution_id' => $instId,
            'status'         => 'registered',
        ]);

        $this->broadcastLobby($battle->fresh(['participants.user', 'participants.institution']));

        return response()->json([
            'success'     => true,
            'redirectUrl' => route('institution.battle.arena', $battle->code),
            'battleInfo'  => $this->buildBattleInfo($battle, $instId),
        ]);
    }

    private function buildBattleInfo(InstitutionBattle $battle, int $instId): array
    {
        return [
            'quizTitle'       => $battle->quiz?->title ?? 'Untitled Quiz',
            'subject'         => $battle->quiz?->subject ?? null,
            'totalQuestions'  => $battle->total_questions,
            'questionTimer'   => $battle->question_timer,
            'playerCount'     => $battle->participants()->count(),
            'hostInstitution' => optional($battle->institution)->name ?? 'Host',
            'yourSchool'      => optional(Institution::find($instId))->name,
        ];
    }

    public function startBattle(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $battle = InstitutionBattle::where('code', $request->code)
            ->where('created_by', Auth::id())
            ->with(['quiz', 'participants'])
            ->first();

        if (!$battle) {
            return response()->json(['success' => false, 'message' => 'Battle not found or not authorized.']);
        }

        if ($battle->status === 'in_progress') {
            return response()->json([
                'success'     => true,
                'redirectUrl' => route('institution.battle.arena', $battle->code),
            ]);
        }

        if (!in_array($battle->status, ['setup', 'registration'])) {
            return response()->json(['success' => false, 'message' => 'Cannot start this battle.']);
        }

        if ($battle->participants()->count() < 2) {
            return response()->json(['success' => false, 'message' => 'Need at least 2 students to start.']);
        }

        if (!$battle->quiz || empty($battle->quiz->questions)) {
            return response()->json(['success' => false, 'message' => 'Quiz has no questions.']);
        }

        $battle->update([
            'status'              => 'countdown',
            'countdown_starts_at' => now(),
        ]);

        $this->startCountdown($battle);

        return response()->json([
            'success'     => true,
            'redirectUrl' => route('institution.battle.arena', $battle->code),
        ]);
    }

    /**
     * Arena — routes to the correct view based on user role:
     *   - Creator (host)         → institution.battle.arena  (isCreator=true, split-screen watcher)
     *   - Institution admin 2/3  → institution.battle.arena  (isCreator=false, isObserverAdmin=true)
     *   - Student participant     → student.battle.arena      (their own dashboard layout)
     */
    public function arena(string $code)
    {
        $user   = Auth::user();
        $student = $user->student ?? null;

        $battle = InstitutionBattle::where('code', $code)
            ->with(['quiz', 'participants.user', 'participants.institution', 'creator', 'institution'])
            ->first();

        if (!$battle) {
            return redirect()->route('institution.battle.join.page')->with('error', 'Battle not found.');
        }

        if ($battle->isFinished()) {
            return redirect()->route('institution.battle.results', $battle->code);
        }

        $isCreator       = ($battle->created_by === Auth::id());
        $institution     = $this->resolveInstitution($user);
        $isObserverAdmin = !$isCreator
            && $institution
            && in_array($institution->id, array_filter($battle->participating_institutions ?? []));

        // ── HOST: the user who created the battle
        if ($isCreator) {
            return view('institution.battle.arena', [
                'battle'          => $battle,
                'student'         => $student,
                'user'            => $user,
                'isCreator'       => true,
                'isObserverAdmin' => false,
            ]);
        }

        // ── OBSERVER ADMIN: institution admin for school 2 or 3
        if ($isObserverAdmin) {
            return view('institution.battle.arena', [
                'battle'          => $battle,
                'student'         => $student,
                'user'            => $user,
                'isCreator'       => false,
                'isObserverAdmin' => true,
            ]);
        }

        // ── STUDENT: must be a registered participant
        if (!$battle->hasParticipant(Auth::id())) {
            // Last-chance auto-join if battle still accepting
            if (
                $institution &&
                in_array($institution->id, array_filter($battle->participating_institutions ?? [])) &&
                in_array($battle->status, ['registration', 'countdown', 'in_progress'])
            ) {
                InstitutionBattleParticipant::firstOrCreate(
                    ['battle_id' => $battle->id, 'user_id' => $user->id],
                    ['institution_id' => $institution->id, 'status' => 'registered']
                );
                $battle->refresh();
            } else {
                return redirect()->route('institution.battle.join.page')
                    ->with('error', 'You are not part of this battle. Please use your student code to join.');
            }
        }

        // Route student to their own student arena view
        return view('student.battle.instituionarena', [
            'battle'          => $battle,
            'student'         => $student,
            'user'            => $user,
            'isCreator'       => false,
            'isObserverAdmin' => false,
        ]);
    }

    public function submitAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'code'           => 'required|string',
            'question_index' => 'required|integer|min:0',
            'selected'       => 'required|integer|min:-1|max:3',
            'time_ms'        => 'required|integer|min:0',
        ]);

        $battle = InstitutionBattle::where('code', $request->code)
            ->where('status', 'in_progress')
            ->with('quiz')
            ->first();

        if (!$battle) {
            return response()->json(['success' => false, 'message' => 'Battle not active.']);
        }

        $userId   = Auth::id();
        $qIdx     = $request->question_index;
        $selected = $request->selected;
        $timeMs   = min($request->time_ms, $battle->question_timer * 1000);

        if (InstitutionBattleQuestionAnswer::where('battle_id', $battle->id)
            ->where('user_id', $userId)
            ->where('question_index', $qIdx)
            ->exists()
        ) {
            return response()->json(['success' => false, 'message' => 'Already answered.']);
        }

        $questions = $battle->quiz->questions ?? [];
        $q         = $questions[$qIdx] ?? null;

        if (!$q) {
            return response()->json(['success' => false, 'message' => 'Invalid question.']);
        }

        $correctAnswer = (int) $q['answer'];
        $isCorrect     = ($selected === $correctAnswer);
        $timeRatio     = $timeMs > 0 ? (1 - ($timeMs / ($battle->question_timer * 1000))) : 1;
        $points        = $isCorrect ? max(20, (int) round(100 * $timeRatio)) : 0;

        InstitutionBattleQuestionAnswer::create([
            'battle_id'       => $battle->id,
            'user_id'         => $userId,
            'question_index'  => $qIdx,
            'selected_option' => $selected,
            'is_correct'      => $isCorrect,
            'time_ms'         => $timeMs,
            'points_earned'   => $points,
        ]);

        $participant = InstitutionBattleParticipant::where('battle_id', $battle->id)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Participant not found.']);
        }

        $newStreak = $isCorrect ? $participant->streak + 1 : 0;
        if ($isCorrect && $newStreak >= 3) $points += 20;

        $participant->update([
            'score'      => $participant->score + $points,
            'correct'    => $participant->correct + ($isCorrect ? 1 : 0),
            'wrong'      => $participant->wrong + ($isCorrect ? 0 : 1),
            'streak'     => $newStreak,
            'max_streak' => max($participant->max_streak, $newStreak),
        ]);

        broadcast(new InstitutionAnswerSubmitted(
            $battle->code,
            $this->getLiveScores($battle),
            $qIdx,
            ['correct_option' => $correctAnswer, 'explanation' => $q['explanation'] ?? '']
        ))->toOthers();

        $this->checkAdvanceQuestion($battle, $qIdx);

        return response()->json([
            'success'     => true,
            'isCorrect'   => $isCorrect,
            'points'      => $points,
            'streak'      => $newStreak,
            'correct'     => $correctAnswer,
            'explanation' => $q['explanation'] ?? '',
        ]);
    }

    public function reportViolation(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'type' => 'required|in:tab_switch,window_blur',
        ]);

        $battle = InstitutionBattle::where('code', $request->code)
            ->where('status', 'in_progress')
            ->first();

        if (!$battle) {
            return response()->json(['success' => true, 'disqualified' => false]);
        }

        $participant = InstitutionBattleParticipant::where('battle_id', $battle->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant || $participant->disqualified) {
            return response()->json([
                'success'      => true,
                'disqualified' => (bool) ($participant->disqualified ?? false),
            ]);
        }

        $participant->incrementViolation($request->type);
        $participant->refresh();

        $totalViolations = $participant->tab_switches + $participant->window_blurs;

        broadcast(new InstitutionBattleViolation(
            $battle->code,
            Auth::id(),
            $request->type,
            $participant->disqualified
        ));

        return response()->json([
            'success'      => true,
            'violations'   => $totalViolations,
            'disqualified' => (bool) $participant->disqualified,
        ]);
    }

    public function results(string $code)
    {
        $user    = Auth::user();
        $student = $user->student ?? null;

        $battle = InstitutionBattle::where('code', $code)
            ->with(['participants.user', 'participants.institution', 'institution', 'quiz', 'creator'])
            ->first();

        if (!$battle) {
            return redirect()->route('institution.battle.join.page')->with('error', 'Battle not found.');
        }

        if ($battle->status === 'in_progress') {
            $this->finishBattle($battle);
            $battle->refresh();
        }

        $myParticipant       = $battle->participants->firstWhere('user_id', Auth::id());
        $institutionRankings = $battle->institution_rankings ?? $battle->getInstitutionRankings();

        $isCreator       = ($battle->created_by === Auth::id());
        $institution     = $this->resolveInstitution($user);
        $isObserverAdmin = !$isCreator
            && $institution
            && in_array($institution->id, array_filter($battle->participating_institutions ?? []));

        // HOST or OBSERVER ADMIN → institution results page
        if ($isCreator || $isObserverAdmin) {
            $myInst = $this->resolveInstitution(Auth::user());

            return view('institution.battle.result', compact(
                'battle',
                'myParticipant',
                'institutionRankings',
                'student',
                'user',
                'myInst'
            ));
        }

        // STUDENT → their own results page (student.battle.results untouched)
        return view('student.battle.instituion-result', compact(
            'battle',
            'myParticipant',
            'institutionRankings',
            'student',
            'user'
        ));
    }

    public function arenaState(string $code): JsonResponse
    {
        $battle = InstitutionBattle::where('code', $code)
            ->with(['participants.user', 'participants.institution'])
            ->first();

        if (!$battle) {
            return response()->json(['success' => false]);
        }

        return response()->json([
            'success'           => true,
            'status'            => $battle->status,
            'scores'            => $this->getLiveScores($battle),
            'institutionScores' => $battle->getInstitutionRankings(),
        ]);
    }

    public function lobbyState(string $code): JsonResponse
    {
        $battle = InstitutionBattle::where('code', $code)
            ->with(['participants.user', 'participants.institution', 'institution', 'quiz'])
            ->first();

        if (!$battle) {
            return response()->json(['success' => false]);
        }

        $participating = $battle->participating_institutions ?? [];
        $totalSlots    = $battle->battle_type === '3school' ? 3 : 2;

        $schools = [];
        for ($i = 0; $i < $totalSlots; $i++) {
            $instId   = $participating[$i] ?? null;
            $inst     = $instId ? Institution::find($instId) : null;
            $students = $battle->participants
                ->where('institution_id', $instId)
                ->values()
                ->map(fn($p) => [
                    'user_id' => $p->user_id,
                    'name'    => $p->user->name,
                    'status'  => $p->status,
                    'avatar'  => strtoupper(substr($p->user->name, 0, 1)),
                ])->all();

            $schools[] = [
                'slot'         => $i + 1,
                'institution'  => $inst ? ['id' => $inst->id, 'name' => $inst->name] : null,
                'joined'       => (bool) $instId,
                'studentCode'  => $instId ? $battle->studentCodeForSlot($i + 1) : null,
                'students'     => $students,
                'studentCount' => count($students),
            ];
        }

        return response()->json([
            'success'        => true,
            'status'         => $battle->status,
            'totalStudents'  => $battle->participants->count(),
            'battleType'     => $battle->battle_type,
            'hostInst'       => optional($battle->institution)->name,
            'schools'        => $schools,
            'inviteCodes'    => $this->buildInviteCodes($battle),
        ]);
    }

    private function buildInviteCodes(InstitutionBattle $battle): array
    {
        $codes      = [];
        $suffixes   = InstitutionBattle::institutionSubSuffixes();
        $totalSlots = $battle->battle_type === '3school' ? 3 : 2;

        for ($i = 0; $i < $totalSlots - 1; $i++) {
            $codes[] = [
                'slot'   => $i + 2,
                'code'   => $battle->code . '-' . $suffixes[$i],
                'suffix' => $suffixes[$i],
            ];
        }

        return $codes;
    }

    public function history()
    {
        $user        = Auth::user();
        $institution = $this->resolveInstitution($user);

        if (!$institution) {
            return redirect()->route('institution.dashboard')
                ->with('error', 'No institution found for your account.');
        }

        $histories = \App\Models\InstitutionBattleHistory::where('institution_id', $institution->id)
            ->with(['battle.quiz', 'battle.institution'])
            ->latest()
            ->paginate(15);

        // Summary stats
        $allHistory  = \App\Models\InstitutionBattleHistory::where('institution_id', $institution->id)->get();
        $totalPlayed = $allHistory->count();
        $totalWins   = $allHistory->where('rank', 1)->count();
        $totalLosses = $totalPlayed - $totalWins;
        $winRate     = $totalPlayed > 0 ? round(($totalWins / $totalPlayed) * 100) : 0;
        $avgAccuracy = $totalPlayed > 0 ? round($allHistory->avg('average_accuracy')) : 0;
        $avgScore    = $totalPlayed > 0 ? round($allHistory->avg('total_score')) : 0;
        $bestRank1   = $allHistory->where('rank', 1)->count();

        // Best battle (highest total_score)
        $bestBattle = $allHistory->sortByDesc('total_score')->first();

        $summaryStats = [
            'total_played' => $totalPlayed,
            'total_wins'   => $totalWins,
            'total_losses' => $totalLosses,
            'win_rate'     => $winRate,
            'avg_accuracy' => $avgAccuracy,
            'avg_score'    => $avgScore,
            'best_score'   => $allHistory->max('total_score') ?? 0,
        ];

        return view('institution.battle.history', compact(
            'user',
            'institution',
            'histories',
            'summaryStats',
            'bestBattle'
        ));
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function resolveInstitution($user): ?Institution
    {
        return $user->institution
            ?? ($user->institution_id ? Institution::find($user->institution_id) : null)
            ?? Institution::where('user_id', $user->id)->first();
    }

    private function getLiveScores(InstitutionBattle $battle): array
    {
        return InstitutionBattleParticipant::where('battle_id', $battle->id)
            ->with(['user', 'institution'])
            ->get()
            ->map(fn($p) => [
                'user_id'          => $p->user_id,
                'name'             => $p->user->name,
                'institution_id'   => $p->institution_id,
                'institution_name' => optional($p->institution)->name ?? 'Unknown',
                'score'            => $p->score,
                'correct'          => $p->correct,
                'streak'           => $p->streak,
                'disqualified'     => (bool) $p->disqualified,
                'avatar'           => strtoupper(substr($p->user->name, 0, 1)),
            ])
            ->sortByDesc('score')
            ->values()
            ->all();
    }

    private function startCountdown(InstitutionBattle $battle): void
    {
        $countdownSeconds = 5;

        for ($i = $countdownSeconds; $i > 0; $i--) {
            broadcast(new InstitutionBattleCountdownUpdated($battle->code, $i));
            sleep(1);
        }

        $battle->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        $battle->participants()->update(['status' => 'playing']);

        $questions = collect($battle->quiz->questions)->map(fn($q) => [
            'question' => $q['question'],
            'options'  => $q['options'],
            'topic'    => $q['topic'] ?? '',
        ])->values()->all();

        broadcast(new InstitutionBattleStarted(
            $battle->code,
            $questions,
            $battle->question_timer,
            0
        ));
    }

    private function checkAdvanceQuestion(InstitutionBattle $battle, int $qIdx): void
    {
        $totalPlayers = InstitutionBattleParticipant::where('battle_id', $battle->id)
            ->where('disqualified', false)
            ->count();

        $answeredCount = InstitutionBattleQuestionAnswer::where('battle_id', $battle->id)
            ->where('question_index', $qIdx)
            ->count();

        if ($answeredCount >= $totalPlayers) {
            if ($qIdx + 1 >= $battle->total_questions) {
                $this->finishBattle($battle);
            } else {
                broadcast(new InstitutionNextQuestion($battle->code, $qIdx + 1));
            }
        }
    }

    private function finishBattle(InstitutionBattle $battle): void
    {
        if ($battle->isFinished()) return;

        $participants = $battle->participants()->with(['user', 'institution'])->get();
        $total        = max(1, $battle->total_questions);

        $participants->each(function ($p) use ($battle, $total) {
            $accuracy  = (int) round(($p->correct / $total) * 100);
            $xp        = $p->score + ($accuracy >= 80 ? 40 : 0);
            $timeTaken = $battle->started_at
                ? (int) max(0, now()->diffInSeconds($battle->started_at, false))
                : 0;

            $p->update([
                'xp_earned'   => $xp,
                'status'      => 'finished',
                'time_taken'  => $timeTaken,
                'finished_at' => now(),
            ]);

            QuizResult::create([
                'user_id'    => $p->user_id,
                'quiz_id'    => $battle->quiz_id,
                'type'       => 'institution_battle',
                'score'      => $p->correct,
                'total_q'    => $total,
                'accuracy'   => $accuracy,
                'xp_earned'  => $xp,
                'time_taken' => $timeTaken,
                'subject'    => $battle->quiz->subject ?? null,
                'metadata'   => json_encode([
                    'battle_code'      => $battle->code,
                    'institution_name' => optional($p->institution)->name,
                    'rank'             => null,
                ]),
            ]);
        });

        $finalScores = $participants->sortByDesc('score')
            ->map(fn($p) => [
                'user_id'          => $p->user_id,
                'name'             => $p->user->name,
                'institution_id'   => $p->institution_id,
                'institution_name' => optional($p->institution)->name,
                'score'            => $p->score,
                'correct'          => $p->correct,
                'wrong'            => $p->wrong,
                'streak'           => $p->max_streak,
                'xp'               => $p->xp_earned,
                'accuracy'         => (int) round(($p->correct / $total) * 100),
                'disqualified'     => (bool) $p->disqualified,
            ])
            ->values()
            ->all();

        $institutionRankings = $battle->getInstitutionRankings();

        $battle->update([
            'status'               => 'finished',
            'finished_at'          => now(),
            'final_scores'         => $finalScores,
            'institution_rankings' => $institutionRankings,
            'top_students'         => array_slice($finalScores, 0, 10),
        ]);

        foreach (collect($battle->participating_institutions)->filter() as $instId) {
            $instParticipants = $participants->where('institution_id', $instId);
            $rank = collect($institutionRankings)->search(fn($r) => $r['institution_id'] == $instId);

            InstitutionBattleHistory::create([
                'institution_id'     => $instId,
                'battle_id'          => $battle->id,
                'total_participants' => $instParticipants->count(),
                'total_correct'      => $instParticipants->sum('correct'),
                'total_wrong'        => $instParticipants->sum('wrong'),
                'total_score'        => $instParticipants->sum('score'),
                'average_accuracy'   => $instParticipants->count()
                    ? (int) round($instParticipants->avg(fn($p) => ($p->correct + $p->wrong) > 0
                        ? ($p->correct / ($p->correct + $p->wrong)) * 100
                        : 0))
                    : 0,
                'average_time'       => (int) ($instParticipants->avg('time_taken') ?? 0),
                'rank'               => $rank !== false ? $rank + 1 : null,
            ]);
        }

        broadcast(new InstitutionBattleFinished(
            $battle->code,
            $finalScores,
            $institutionRankings,
            array_slice($finalScores, 0, 10)
        ));
    }

    private function broadcastLobby(InstitutionBattle $battle): void
    {
        $battle->loadMissing(['participants.user', 'participants.institution']);

        $participants = $battle->participants->map(fn($p) => [
            'id'               => $p->user_id,
            'user_id'          => $p->user_id,
            'name'             => $p->user->name,
            'institution_id'   => $p->institution_id,
            'institution_name' => optional($p->institution)->name,
            'status'           => $p->status,
        ])->all();

        broadcast(new InstitutionBattleLobbyUpdated(
            $battle->code,
            $participants,
            $battle->status,
            count($participants)
        ));
    }
}
