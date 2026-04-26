<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\TutorChat;
use App\Services\AIQuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{

    public function __construct(private AIQuizService $ai) {}

    // ── Main page ────────────────────────────────────────────────────────────
    public function index()
    {
        $user    = Auth::user();
        $student = $user->getOrCreateStudent();

        $myQuizzes = Quiz::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Load tutor chat sessions for the sidebar
        $chatSessions = TutorChat::where('user_id', $user->id)
            ->select('id', 'title', 'subject', 'updated_at')
            ->orderByDesc('updated_at')
            ->take(20)
            ->get();

        return view('student.quiz.aiquiz', compact('user', 'student', 'myQuizzes', 'chatSessions'));
    }

    // ── Generate from Topic ──────────────────────────────────────────────────
    public function generateTopic(Request $request): JsonResponse
    {
        $request->validate([
            'topic'      => 'required|string|max:200',
            'count'      => 'integer|min:3|max:20',
            'difficulty' => 'in:beginner,intermediate,advanced',
        ]);

        try {
            $questions = $this->ai->generateFromTopic(
                $request->topic,
                'General',
                (int) ($request->count ?? 10),
                $request->difficulty ?? 'intermediate'
            );

            $quiz = Quiz::create([
                'user_id'    => Auth::id(),
                'title'      => $request->topic,
                'topic'      => $request->topic,
                'difficulty' => $request->difficulty ?? 'intermediate',
                'source'     => 'topic',
                'questions'  => $questions,
            ]);

            return response()->json([
                'success'   => true,
                'questions' => $questions,
                'quizId'    => $quiz->id,
                'count'     => count($questions),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Generate from PDF ────────────────────────────────────────────────────
    public function generatePdf(Request $request): JsonResponse
    {
        $request->validate([
            'pdf'        => 'required|file|mimes:pdf|max:10240',
            'count'      => 'integer|min:3|max:20',
            'difficulty' => 'in:beginner,intermediate,advanced',
        ]);

        try {
            $pdfPath = $request->file('pdf')->getRealPath();
            $parser  = new \Smalot\PdfParser\Parser();
            $pdf     = $parser->parseFile($pdfPath);
            $text    = $pdf->getText();

            if (strlen(trim($text)) < 50) {
                return response()->json(['success' => false, 'message' => 'PDF appears empty or unreadable. Try a text-based PDF.'], 400);
            }

            $questions = $this->ai->generateFromPdfText(
                $text,
                (int) ($request->count ?? 10),
                $request->difficulty ?? 'intermediate'
            );

            $quiz = Quiz::create([
                'user_id'    => Auth::id(),
                'title'      => pathinfo($request->file('pdf')->getClientOriginalName(), PATHINFO_FILENAME),
                'difficulty' => $request->difficulty ?? 'intermediate',
                'source'     => 'pdf',
                'questions'  => $questions,
            ]);

            return response()->json([
                'success'   => true,
                'questions' => $questions,
                'quizId'    => $quiz->id,
                'count'     => count($questions),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Generate from Image ──────────────────────────────────────────────────
    public function generateImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'count' => 'integer|min:3|max:10',
        ]);

        try {
            $base64   = base64_encode(file_get_contents($request->file('image')->getRealPath()));
            $mimeType = $request->file('image')->getMimeType();
            $result   = $this->ai->generateFromImage($base64, $mimeType, (int) ($request->count ?? 5));

            $quiz = Quiz::create([
                'user_id'    => Auth::id(),
                'title'      => "Image Quiz: {$result['label']}",
                'difficulty' => 'intermediate',
                'source'     => 'image',
                'questions'  => $result['questions'],
            ]);

            return response()->json([
                'success'       => true,
                'questions'     => $result['questions'],
                'quizId'        => $quiz->id,
                'count'         => count($result['questions']),
                'detectedLabel' => $result['label'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // ── Generate Standard ────────────────────────────────────────────────────
    public function generateStandard(Request $request): JsonResponse
    {
        $request->validate([
            'subject'    => 'required|string|max:100',
            'class'      => 'required|string|max:20',
            'count'      => 'integer|min:3|max:20',
            'difficulty' => 'in:beginner,intermediate,advanced',
        ]);

        try {
            $questions = $this->ai->generateStandard(
                $request->subject,
                $request->class,
                (int) ($request->count ?? 10),
                $request->difficulty ?? 'intermediate'
            );

            $quiz = Quiz::create([
                'user_id'    => Auth::id(),
                'title'      => "Class {$request->class} — {$request->subject}",
                'subject'    => $request->subject,
                'class'      => $request->class,
                'difficulty' => $request->difficulty ?? 'intermediate',
                'source'     => 'standard',
                'questions'  => $questions,
            ]);

            return response()->json([
                'success'   => true,
                'questions' => $questions,
                'quizId'    => $quiz->id,
                'count'     => count($questions),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Save Manual MCQ ──────────────────────────────────────────────────────
    public function saveManual(Request $request): JsonResponse
    {
        $request->validate([
            'title'                   => 'required|string|max:200',
            'subject'                 => 'nullable|string|max:100',
            'difficulty'              => 'in:beginner,intermediate,advanced',
            'questions'               => 'required|array|min:1',
            'questions.*.question'    => 'required|string',
            'questions.*.options'     => 'required|array|size:4',
            'questions.*.options.*'   => 'required|string',
            'questions.*.answer'      => 'required|integer|min:0|max:3',
            'questions.*.explanation' => 'nullable|string',
        ]);

        $questions = array_map(fn($q) => array_merge($q, ['source' => 'manual']), $request->questions);

        $quiz = Quiz::create([
            'user_id'    => Auth::id(),
            'title'      => $request->title,
            'subject'    => $request->subject,
            'difficulty' => $request->difficulty ?? 'intermediate',
            'source'     => 'manual',
            'questions'  => $questions,
        ]);

        return response()->json([
            'success' => true,
            'quizId'  => $quiz->id,
            'count'   => count($questions),
            'message' => 'Quiz saved successfully!',
        ]);
    }

    // ── Submit Solo Result ────────────────────────────────────────────────────
    public function submitSolo(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id'    => 'nullable|integer|exists:quizzes,id',
            'score'      => 'required|integer|min:0',
            'total_q'    => 'required|integer|min:1',
            'accuracy'   => 'required|integer|min:0|max:100',
            'xp_earned'  => 'required|integer|min:0',
            'subject'    => 'nullable|string',
            'topic'      => 'nullable|string',
            'difficulty' => 'nullable|string',
            'time_taken' => 'nullable|integer',
            'answer_log' => 'nullable|array',
        ]);

        $user    = Auth::user();
        $student = $user->student ?? null;

        QuizResult::create([
            'user_id'    => $user->id,
            'quiz_id'    => $request->quiz_id,
            'type'       => 'solo',
            'score'      => $request->score,
            'total_q'    => $request->total_q,
            'accuracy'   => $request->accuracy,
            'xp_earned'  => $request->xp_earned,
            'subject'    => $request->subject,
            'topic'      => $request->topic,
            'difficulty' => $request->difficulty,
            'time_taken' => $request->time_taken,
            'answer_log' => $request->answer_log,
        ]);

        if ($request->quiz_id) {
            Quiz::where('id', $request->quiz_id)->increment('play_count');
        }

        if ($student) {
            $student->addXp($request->xp_earned);
            $student->increment('total_quizzes');
        }

        return response()->json(['success' => true]);
    }

    // ── Tutor Chat: Send message ──────────────────────────────────────────────
    // POST /student/quiz/tutor/chat
    public function tutorIndex()
    {
        $user = Auth::user();
        $student = $user->getOrCreateStudent();
        $chatSessions = TutorChat::where('user_id', $user->id)
            ->select('id', 'title', 'subject', 'updated_at')
            ->orderByDesc('updated_at')
            ->take(30)
            ->get();

        return view('student.quiz.TutorChat', compact('user', 'chatSessions', 'student'));
    }
    public function tutorChat(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => 'required|string|max:2000',
            'subject'    => 'nullable|string|max:100',
            'session_id' => 'nullable|integer|exists:tutor_chats,id',
        ]);

        $userId  = Auth::id();
        $subject = $request->subject ?? 'General';
        $message = trim($request->message);

        // Load or create session
        if ($request->session_id) {
            $session = TutorChat::where('id', $request->session_id)
                ->where('user_id', $userId)
                ->firstOrFail();
        } else {
            $session = TutorChat::create([
                'user_id' => $userId,
                'subject' => $subject,
                'title'   => $this->generateChatTitle($message),
                'messages' => [],
            ]);
        }

        $history = $session->messages ?? [];

        try {
            $response = $this->ai->tutorChat($message, $subject, $history);

            // Append to messages
            $history[] = ['role' => 'user',      'content' => $message,  'time' => now()->toISOString()];
            $history[] = ['role' => 'assistant',  'content' => $response, 'time' => now()->toISOString()];

            $session->update([
                'messages'   => $history,
                'subject'    => $subject,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success'    => true,
                'response'   => $response,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Tutor Chat: Load session ──────────────────────────────────────────────
    // GET /student/quiz/tutor/session/{id}
    public function tutorSession(int $id): JsonResponse
    {
        $session = TutorChat::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success'  => true,
            'session'  => [
                'id'       => $session->id,
                'title'    => $session->title,
                'subject'  => $session->subject,
                'messages' => $session->messages ?? [],
            ],
        ]);
    }

    // ── Tutor Chat: List sessions ─────────────────────────────────────────────
    // GET /student/quiz/tutor/sessions
    public function tutorSessions(): JsonResponse
    {
        $sessions = TutorChat::where('user_id', Auth::id())
            ->select('id', 'title', 'subject', 'updated_at')
            ->orderByDesc('updated_at')
            ->take(30)
            ->get();

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    // ── Tutor Chat: Delete session ─────────────────────────────────────────────
    // DELETE /student/quiz/tutor/session/{id}
    public function tutorDeleteSession(int $id): JsonResponse
    {
        TutorChat::where('id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['success' => true]);
    }

    // ── Tutor Chat: New session ────────────────────────────────────────────────
    // POST /student/quiz/tutor/new
    public function tutorNewSession(Request $request): JsonResponse
    {
        $session = TutorChat::create([
            'user_id'  => Auth::id(),
            'subject'  => $request->subject ?? 'General',
            'title'    => 'New Chat',
            'messages' => [],
        ]);

        return response()->json(['success' => true, 'session_id' => $session->id]);
    }

    // ── AI Suggestions ────────────────────────────────────────────────────────
    public function suggestions(): JsonResponse
    {
        $results = QuizResult::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        if ($results->isEmpty()) {
            return response()->json([
                'suggestion'   => 'Start your first quiz to get personalized suggestions! 🚀',
                'weakSubjects' => [],
            ]);
        }

        $subjectMap = [];
        foreach ($results as $r) {
            if ($r->subject) {
                $subjectMap[$r->subject][] = $r->accuracy;
            }
        }

        $weakList = [];
        foreach ($subjectMap as $subj => $accuracies) {
            $avg = round(array_sum($accuracies) / count($accuracies));
            if ($avg < 70) {
                $weakList[] = "{$subj} ({$avg}% average accuracy)";
            }
        }

        try {
            $suggestion = $this->ai->suggestions($weakList);
            return response()->json(['success' => true, 'suggestion' => $suggestion, 'weakSubjects' => $weakList]);
        } catch (\Exception $e) {
            return response()->json([
                'suggestion'   => 'Keep practicing to unlock personalized tips! 💪',
                'weakSubjects' => $weakList,
            ]);
        }
    }

    // ── Delete Quiz ───────────────────────────────────────────────────────────
    public function destroy(Quiz $quiz): JsonResponse
    {
        if ($quiz->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $quiz->delete();
        return response()->json(['success' => true]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function generateChatTitle(string $message): string
    {
        $title = substr($message, 0, 50);
        return strlen($message) > 50 ? $title . '…' : $title;
    }

    public function tutorGenQuiz(Request $request): JsonResponse
    {
        $request->validate([
            'messages' => 'required|array|min:2',
            'subject'  => 'nullable|string|max:100',
        ]);

        $subject = $request->subject ?? 'General';

        // ✅ FORCE CLEAN HISTORY (CRITICAL FIX)
        $history = array_map(function ($msg) {

            $content = $msg['content'] ?? '';

            // 🔥 ALWAYS STRING (NO ERROR POSSIBLE)
            if (!is_string($content)) {
                $content = json_encode($content, JSON_UNESCAPED_UNICODE);
            }

            return [
                'role' => $msg['role'] ?? 'user',
                'content' => $content,
            ];
        }, $request->messages);

        // Ensure enough content
        $chatLength = implode(' ', array_column($history, 'content'));

        if (strlen($chatLength) < 120) {
            return response()->json([
                'success' => false,
                'message' => 'Chat too short. Continue learning first.',
            ]);
        }

        try {
            $questions = $this->ai->generateFromChat($history, $subject, 5, 'mixed');

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate quiz.',
                ]);
            }

            return response()->json([
                'success'   => true,
                'questions' => $questions,
                'count'     => count($questions),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}

