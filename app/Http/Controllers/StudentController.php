<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{

    public function dashboard()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            abort(403, 'Unauthorized');
        }

        $student = $user->getOrCreateStudent();

        // Update streak on daily visit
        if (method_exists($student, 'updateStreak')) {
            $student->updateStreak();
        }

        // Show profile modal if not completed
        $showProfileModal = !$user->hasStudentProfile();

        // Data (replace later with DB queries)
        $recentActivity = $this->getRecentActivity($student);
        $leaderboard    = $this->getLeaderboard();
        $upcoming       = $this->getUpcoming();

        return view('student.dashboard', compact(
            'user',
            'student',
            'recentActivity',
            'leaderboard',
            'upcoming',
            'showProfileModal'
        ));
    }

    /**
     * Save student profile (AJAX)
     */
    public function saveProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $student = $user->getOrCreateStudent();

        // ✅ FULL validation (match modal fields)
        $data = $request->validate([
            'display_name'      => ['required', 'string', 'max:255'],
            'age'               => ['required', 'integer', 'min:5', 'max:30'],
            'class'             => ['required', 'string', 'max:50'],
            'school_name'       => ['required', 'string', 'max:255'],
            'bio'               => ['nullable', 'string', 'max:300'],
            'subjects_interest' => ['nullable', 'array'],
            'subjects_interest.*' => ['string'],
            'avatar'            => ['nullable', 'image', 'max:2048'],
        ]);

        // ✅ Avatar upload (file-based if used later)
        if ($request->hasFile('avatar')) {
            if (!empty($student->avatar)) {
                Storage::disk('public')->delete($student->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // ✅ Store JSON properly
        $data['subjects_interest'] = $data['subjects_interest'] ?? [];

        // ✅ Mark profile complete
        $data['is_profile_complete'] = true;

        $student->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile saved! Welcome to QuizMind 🎉',
            'student' => [
                'display_name' => $student->display_name,
                'avatar' => $student->avatar
                    ? asset('storage/' . $student->avatar)
                    : null,
                'level' => $student->level,
                'level_title' => $student->level_title,
            ],
        ]);
    }

    /**
     * Get student stats (AJAX)
     */
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

    /**
     * Dummy Recent Activity (replace with DB)
     */
    private function getRecentActivity(Student $student): array
    {
        return [
            [
                'type'    => 'quiz',
                'subject' => 'Mathematics',
                'score'   => 92,
                'time'    => '2 hours ago',
                'xp'      => 120,
                'icon'    => '🧮',
            ],
            [
                'type'    => 'battle',
                'subject' => 'Science vs @alice',
                'score'   => 78,
                'time'    => 'Yesterday',
                'xp'      => 85,
                'icon'    => '⚔️',
            ],
            [
                'type'    => 'quiz',
                'subject' => 'History',
                'score'   => 65,
                'time'    => '2 days ago',
                'xp'      => 60,
                'icon'    => '📜',
            ],
        ];
    }

    /**
     * Dummy Leaderboard (replace with DB)
     */
    private function getLeaderboard(): array
    {
        return [
            ['name' => 'Priya S.',  'level' => 24, 'xp' => 4820, 'streak' => 18, 'avatar' => null],
            ['name' => 'Arjun K.',  'level' => 22, 'xp' => 4410, 'streak' => 12, 'avatar' => null],
            ['name' => 'Meera R.',  'level' => 21, 'xp' => 4200, 'streak' => 9,  'avatar' => null],
            ['name' => 'Dev P.',    'level' => 19, 'xp' => 3900, 'streak' => 22, 'avatar' => null],
            ['name' => 'Sneha T.',  'level' => 18, 'xp' => 3650, 'streak' => 7,  'avatar' => null],
        ];
    }

    /**
     * Dummy Upcoming Events
     */
    private function getUpcoming(): array
    {
        return [
            ['type' => 'quiz',   'name' => 'Physics Chapter 5',   'time' => 'Today 4:00 PM',  'icon' => '⚡'],
            ['type' => 'battle', 'name' => 'Math Battle — Open', 'time' => 'Today 6:00 PM',  'icon' => '⚔️'],
            ['type' => 'quiz',   'name' => 'Chemistry Mock Test', 'time' => 'Tomorrow 10 AM', 'icon' => '🧪'],
        ];
    }
}
