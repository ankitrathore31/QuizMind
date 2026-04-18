<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MyQuizHistoryController extends Controller
{
    // ── Main History Page ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user = Auth::user();
         $student = $user->getOrCreateStudent();
        // Solo quiz results with quiz relationship, paginated
        $soloResults = QuizResult::with('quiz')
            ->where('user_id', $user->id)
            ->where('type', 'solo')
            ->orderByDesc('created_at')
            ->paginate(12, ['*'], 'solo_page')
            ->withQueryString();

        // Battle rooms the user participated in
        $battleRooms = BattleRoom::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->with(['participants' => fn($q) => $q->where('user_id', $user->id)])
            ->whereIn('status', ['finished', 'completed'])
            ->orderByDesc('finished_at')
            ->paginate(10, ['*'], 'battle_page')
            ->withQueryString();

        // Summary stats for the header
        // $stats = $this->getOrCreateStudent($user);

        return view('student.quiz.my-quizzes', compact('soloResults', 'battleRooms','student','user'));
    }

    // ── Delete a Quiz Result ──────────────────────────────────────────────
    public function deleteResult(int $id): JsonResponse
    {
        $result = QuizResult::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $result->delete();

        return response()->json(['success' => true, 'message' => 'Result deleted']);
    }

    // ── Delete a Saved Quiz ───────────────────────────────────────────────
    public function deleteQuiz(int $id): JsonResponse
    {
        $quiz = Quiz::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Also delete all results for this quiz belonging to this user
        QuizResult::where('quiz_id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        $quiz->delete();

        return response()->json(['success' => true, 'message' => 'Quiz deleted']);
    }

    // ── Get Full Quiz Detail (for review modal via AJAX) ──────────────────
    public function getResultDetail(int $id): JsonResponse
    {
        $result = QuizResult::with('quiz')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $quiz = $result->quiz;

        return response()->json([
            'success'   => true,
            'result'    => [
                'id'        => $result->id,
                'title'     => $quiz?->title ?? 'Untitled Quiz',
                'score'     => $result->score,
                'total'     => $result->total_q,
                'accuracy'  => $result->accuracy,
                'xp'        => $result->xp_earned,
                'time'      => $result->time_taken,
                'subject'   => $result->subject,
                'difficulty'=> $result->difficulty,
                'created_at'=> $result->created_at->format('d M Y, h:i A'),
                'log'       => $result->answer_log ?? [],
                'questions' => $quiz?->questions ?? [],
                'source'    => $quiz?->source ?? 'topic',
            ],
        ]);
    }

    // ── Stats summary for the page header ────────────────────────────────
    private function getUserStats(int $user): array
    {
        $results = QuizResult::where('user_id', $user)->where('type', 'solo')->get();

        if ($results->isEmpty()) {
            return [
                'total_quizzes' => 0,
                'avg_accuracy'  => 0,
                'total_xp'      => 0,
                'best_accuracy' => 0,
                'total_correct' => 0,
                'total_answered'=> 0,
            ];
        }

        return [
            'total_quizzes'  => $results->count(),
            'avg_accuracy'   => round($results->avg('accuracy')),
            'total_xp'       => $results->sum('xp_earned'),
            'best_accuracy'  => $results->max('accuracy'),
            'total_correct'  => $results->sum('score'),
            'total_answered' => $results->sum('total_q'),
        ];
    }
}