<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstitutionController extends Controller
{


    public function index()
    {
        $user        = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $students    = $this->getStudentsWithStats($institution);

        // Pull battle history for this institution
        $battleHistory = \App\Models\InstitutionBattleHistory::where('institution_id', $institution->id)
            ->with(['battle.quiz', 'battle'])
            ->latest()
            ->get();

        return view('institution.dashboard', [
            'user'          => $user,
            'institution'   => $institution,
            'students'      => $students,
            'topStudents'   => $students->sortByDesc('total_quizzes')->take(5)->values(),
            'stats'         => $this->getStats($institution, $students, $battleHistory),
            'battleHistory' => $battleHistory,
        ]);
    }

    private function getStats(Institution $institution, $students, $battleHistory = null): array
    {
        // Load battle history if not passed
        if ($battleHistory === null) {
            $battleHistory = \App\Models\InstitutionBattleHistory::where('institution_id', $institution->id)->get();
        }

        $totalBattles = $battleHistory->count();
        $battleWins   = $battleHistory->where('rank', 1)->count();
        $battleLosses = $totalBattles - $battleWins;

        return [
            'total_students' => $students->count(),
            'total_quizzes'  => $students->sum('total_quizzes'),
            'total_correct'  => $students->sum('total_correct'),
            'total_wrong'    => $students->sum('total_wrong'),

            // Real battle data from InstitutionBattleHistory
            'total_battle'   => $totalBattles,
            'battle_wins'    => $battleWins,
            'battle_losses'  => $battleLosses,

            'avg_accuracy'   => $students->count()
                ? round($students->avg('accuracy'))
                : 0,

            'top_student_name' => $students->first()?->display_name
                ?? $students->first()?->user->name
                ?? '—',
        ];
    }

    // ── STUDENTS PAGE (NEW) ───────────────────────────────────────
    public function studentsPage()
    {
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();
        $students    = $this->getStudentsWithStats($institution);

        return view('institution.student.list', [
            'institution' => $institution,
            'students'    => $students,
        ]);
    }

    // ── SETTINGS PAGE (NEW) ───────────────────────────────────────
    public function settingsPage()
    {
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();

        return view('institution.setting.view', [
            'institution' => $institution,
        ]);
    }

    // ── AJAX: STUDENTS DATA (NO CHANGE) ───────────────────────────
    public function students(Request $request): JsonResponse
    {
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();
        $students    = $this->getStudentsWithStats($institution);

        $query = strtolower($request->input('q', ''));
        if ($query) {
            $students = $students->filter(function ($s) use ($query) {
                $name = strtolower($s->display_name ?? $s->user->name ?? '');
                return str_contains($name, $query);
            })->values();
        }

        return response()->json([
            'success'  => true,
            'students' => $students->map(fn($s) => [
                'id'             => $s->id,
                'name'           => $s->display_name ?? $s->user->name,
                'avatar_initial' => strtoupper(substr($s->display_name ?? $s->user->name ?? '?', 0, 1)),
                'total_quizzes'  => $s->total_quizzes ?? 0,
                'total_correct'  => $s->total_correct ?? 0,
                'total_wrong'    => $s->total_wrong ?? 0,
                'accuracy'       => $s->accuracy,
                'xp'             => $s->xp ?? 0,
                'level'          => $s->level ?? 1,
                'streak'         => $s->streak ?? 0,
                'battles_won'    => $s->total_battles_won ?? 0,
                'battles_lost'   => $s->total_battles_lost ?? 0,
                'badges'         => $s->badges ?? [],
                'joined_at'      => $s->created_at?->format('M d, Y'),
            ])->values(),
        ]);
    }

    // ── UPDATE SETTINGS (LOCKED FIELDS FIXED) ─────────────────────
    public function updateSettings(Request $request)
    {
        $request->validate([
            'principal_name' => 'nullable|string|max:100',
            'address'        => 'nullable|string|max:500',
            'phone'          => 'nullable|string|max:20',
            'city'           => 'nullable|string|max:80',
            'state'          => 'nullable|string|max:80',
            'type'           => 'nullable|string|max:50',
            'is_active'      => 'nullable|boolean',
        ]);

        $institution = Institution::where('user_id', Auth::id())->firstOrFail();

        $institution->update($request->only([
            'principal_name',
            'address',
            'phone',
            'city',
            'state',
            'type',
            'is_active',
        ]));

        return back()->with('success', 'Settings updated successfully');
    }

    // ── HELPERS (NO CHANGE) ───────────────────────────────────────
    private function getStudentsWithStats(Institution $institution)
    {
        return Student::whereHas('user', function ($q) use ($institution) {
            $q->where('ref_code', $institution->code)
                ->where('role', 'student');
        })
            ->with('user')
            ->orderByDesc('xp')
            ->get()
            ->map(function ($student, $index) {
                $total    = ($student->total_correct ?? 0) + ($student->total_wrong ?? 0);
                $accuracy = $total > 0
                    ? round(($student->total_correct / $total) * 100)
                    : 0;

                $student->accuracy = $accuracy;
                $student->rank     = $index + 1;

                return $student;
            });
    }

    private function getStatsOld(Institution $institution, $students): array
    {
        return [
            'total_students' => $students->count(),

            'total_quizzes' => $students->sum('total_quizzes'),

            // ✅ ADD THIS
            'total_correct' => $students->sum('total_correct'),
            'total_wrong'   => $students->sum('total_wrong'),

            // ✅ BATTLES
            'battle_wins'   => $students->sum('total_battles_won'),
            'battle_losses' => $students->sum('total_battles_lost'),

            'avg_accuracy' => $students->count()
                ? round($students->avg('accuracy'))
                : 0,

            'top_student_name' => $students->first()?->display_name
                ?? $students->first()?->user->name
                ?? '—',
        ];
    }
}
