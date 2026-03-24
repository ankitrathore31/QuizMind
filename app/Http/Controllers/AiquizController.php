<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Services\AIQuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AIQuizController extends Controller
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

        return view('student.quiz.aiquiz', compact('user', 'student', 'myQuizzes'));
    }

    // ── Generate from Topic ──────────────────────────────────────────────────
    public function generateTopic(Request $request)
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
                (int)($request->count ?? 10),
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
    public function generatePdf(Request $request)
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
                return response()->json(['success' => false, 'message' => 'PDF appears empty or unreadable'], 400);
            }

            $questions = $this->ai->generateFromPdfText(
                $text,
                (int)($request->count ?? 10),
                $request->difficulty ?? 'intermediate'
            );

            $quiz = Quiz::create([
                'user_id'    => Auth::id(),
                'title'      => $request->file('pdf')->getClientOriginalName(),
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
    public function generateImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'count' => 'integer|min:3|max:10',
        ]);

        try {
            $base64   = base64_encode(file_get_contents($request->file('image')->getRealPath()));
            $mimeType = $request->file('image')->getMimeType();

            $result    = $this->ai->generateFromImage($base64, $mimeType, (int)($request->count ?? 5));
            $questions = $result['questions'];
            $label     = $result['label'];

            $quiz = Quiz::create([
                'user_id'    => Auth::id(),
                'title'      => "Image Quiz: {$label}",
                'difficulty' => 'intermediate',
                'source'     => 'image',
                'questions'  => $questions,
            ]);

            return response()->json([
                'success'       => true,
                'questions'     => $questions,
                'quizId'        => $quiz->id,
                'count'         => count($questions),
                'detectedLabel' => $label,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // ── Generate Standard (class + subject) ──────────────────────────────────
    public function generateStandard(Request $request)
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
                (int)($request->count ?? 10),
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
    public function saveManual(Request $request)
    {
        $request->validate([
            'title'                    => 'required|string|max:200',
            'subject'                  => 'nullable|string|max:100',
            'difficulty'               => 'in:beginner,intermediate,advanced',
            'questions'                => 'required|array|min:1',
            'questions.*.question'     => 'required|string',
            'questions.*.options'      => 'required|array|size:4',
            'questions.*.options.*'    => 'required|string',
            'questions.*.answer'       => 'required|integer|min:0|max:3',
            'questions.*.explanation'  => 'nullable|string',
            'questions.*.topic'        => 'nullable|string',
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
    public function submitSolo(Request $request)
    {
        $request->validate([
            'quiz_id'    => 'nullable|integer|exists:quizzes,id',
            'score'      => 'required|integer|min:0',
            'total_q'    => 'required|integer|min:1',
            'accuracy'   => 'required|integer|min:0|max:100',
            'xp_earned'  => 'required|integer|min:0',
            'difficulty' => 'nullable|string',
            'subject'    => 'nullable|string',
            'topic'      => 'nullable|string',
            'time_taken' => 'nullable|integer',
            'answer_log' => 'nullable|array',
        ]);

        $user    = Auth::user();
        $student = $user->student;

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
            $student->total_quizzes++;
            $student->save();
        }

        return response()->json(['success' => true, 'message' => 'Result saved!']);
    }

    // ── Tutor Chat ────────────────────────────────────────────────────────────
    public function tutorChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'subject' => 'nullable|string',
            'history' => 'nullable|array',
        ]);

        try {
            $response = $this->ai->tutorChat(
                $request->message,
                $request->subject ?? 'General',
                $request->history ?? []
            );
            return response()->json(['success' => true, 'response' => $response]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AI Suggestions ─────────────────────────────────────────────────────
    public function suggestions()
    {
        $user    = Auth::user();
        $results = QuizResult::where('user_id', $user->id)
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
                $weakList[] = "{$subj} ({$avg}%)";
            }
        }

        try {
            $suggestion = $this->ai->suggestions($weakList);
            return response()->json(['suggestion' => $suggestion, 'weakSubjects' => $weakList]);
        } catch (\Exception $e) {
            return response()->json([
                'suggestion'   => 'Keep practicing to unlock personalized tips! 💪',
                'weakSubjects' => $weakList,
            ]);
        }
    }

    // ── Delete a Quiz ─────────────────────────────────────────────────────
    public function destroy(Quiz $quiz)
    {
        if ($quiz->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $quiz->delete();
        return response()->json(['success' => true, 'message' => 'Quiz deleted.']);
    }
}