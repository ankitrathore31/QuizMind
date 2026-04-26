<?php

namespace App\Http\Controllers;

use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;

class StudentController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────

    public function dashboard()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            abort(403, 'Unauthorized');
        }

        $student = $user->getOrCreateStudent();
        $student->updateStreak();

        // First-time profile setup → redirect to wizard page
        if (!$student->is_profile_complete) {
            return redirect()->route('student.profile.setup')
                ->with('info', 'Please complete your profile to continue.');
        }

        $recentActivity = $this->getRecentActivity($student);
        $leaderboard    = $this->getLeaderboard();

        return view('student.dashboard', compact(
            'user',
            'student',
            'recentActivity',
            'leaderboard'
        ));
    }

    // ── Profile Setup Wizard (GET — first-time only) ───────

    public function profileSetupPage()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            abort(403, 'Unauthorized');
        }

        $student = $user->getOrCreateStudent();

        // Already done → go to dashboard
        if ($student->is_profile_complete) {
            return redirect()->route('student.dashboard');
        }

        return view('student.profile.profile-setup', compact('user', 'student'));
    }

    // ── Profile Page (GET — view + edit for existing users) ──

    public function profilePage()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            abort(403, 'Unauthorized');
        }

        $student = $user->getOrCreateStudent();

        // If not set up yet, send to wizard
        if (!$student->is_profile_complete) {
            return redirect()->route('student.profile.setup');
        }

        return view('student.profile.profile-setup', compact('user', 'student'));
    }

    // ── Save / Update Profile (POST — AJAX or redirect) ───

    public function saveProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $student = $user->getOrCreateStudent();

        $data = $request->validate([
            'display_name'        => ['required', 'string', 'max:255'],
            'age'                 => ['required', 'integer', 'min:5', 'max:30'],
            'class'               => ['required', 'string', 'max:50'],
            'school_name'         => ['required', 'string', 'max:255'],
            'bio'                 => ['nullable', 'string', 'max:300'],
            'subjects_interest'   => ['nullable', 'array'],
            'subjects_interest.*' => ['string'],
            'avatar'              => ['nullable', 'string', 'max:10'],
        ]);

        $data['subjects_interest']   = $data['subjects_interest'] ?? [];
        $data['is_profile_complete'] = true;

        $student->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile saved!',
                'student' => [
                    'display_name' => $student->display_name,
                    'avatar'       => $student->avatar,
                    'level'        => $student->level,
                    'level_title'  => $student->level_title,
                ],
            ]);
        }

        // Non-AJAX (wizard form fallback)
        return redirect()->route('student.dashboard')
            ->with('success', '🎉 Profile saved! Welcome to QuizMind!');
    }

    // ── Stats (AJAX) ──────────────────────────────────────

    public function stats()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'No profile'], 404);
        }

        return response()->json([
            'level'       => $student->level,
            'level_title' => $student->level_title,
            'xp'          => $student->xp,
            'xp_progress' => $student->xp_progress,
            'streak'      => $student->streak,
            'accuracy'    => $student->accuracy,
            'win_rate'    => $student->win_rate,
        ]);
    }

    // ── Delete Account Page (GET) ─────────────────────────

    public function deleteAccountPage()
    {
        $user = Auth::user();
        $student = $user->getOrCreateStudent();
        return view('student.profile.delete-account', compact('student'));
    }

    // ── Delete Account (DELETE) ───────────────────────────

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The password you entered is incorrect.']);
        }

        $user->student?->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect('/')->with('success', 'Your account has been permanently deleted.');
    }

    // ── Private helpers ───────────────────────────────────

    private function getRecentActivity(Student $student): array
    {
        $results = QuizResult::where('user_id', $student->user_id)
            ->latest()
            ->take(5)
            ->get();

        $iconMap = [
            'Mathematics'  => '🧮',
            'Physics'      => '⚛️',
            'Chemistry'    => '🧪',
            'Biology'      => '🧬',
            'History'      => '📜',
            'Geography'    => '🌍',
            'English'      => '📖',
            'Computer Sci' => '💻',
            'Economics'    => '📊',
            'default'      => '📝',
        ];

        return $results->map(function ($r) use ($iconMap) {
            $icon = $iconMap[$r->subject] ?? $iconMap['default'];
            if ($r->type !== 'solo') $icon = '⚔️';

            return [
                'type'    => $r->type,
                'subject' => $r->subject . ($r->topic ? " · {$r->topic}" : ''),
                'score'   => (int) $r->accuracy,
                'time'    => $r->created_at->diffForHumans(),
                'xp'      => $r->xp_earned,
                'icon'    => $icon,
            ];
        })->toArray();
    }

    private function getLeaderboard(): array
    {
        return Student::with('user')
            ->where('is_profile_complete', true)
            ->orderByDesc('xp')
            ->orderByDesc('level')
            ->take(5)
            ->get()
            ->map(fn($s) => [
                'name'   => $s->display_name ?: ($s->user->name ?? 'Unknown'),
                'level'  => $s->level,
                'xp'     => $s->xp,
                'streak' => $s->streak,
                'avatar' => $s->avatar,
            ])
            ->toArray();
    }
}
