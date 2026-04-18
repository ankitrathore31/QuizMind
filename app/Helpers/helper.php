<?php

use Carbon\Carbon;
use App\Models\User;

if (!function_exists('update_login_streak')) {

    function update_login_streak(User $user)
    {
        // Only apply to students
        if ($user->role !== 'student' || !$user->student) {
            return;
        }

        $student = $user->student;

        $today = Carbon::today();
        $lastDate = $student->streak_last_date
            ? Carbon::parse($student->streak_last_date)->startOfDay()
            : null;

        if ($lastDate) {
            $diff = $lastDate->diffInDays($today);

            if ($diff === 1) {
                // ✅ Continue streak
                $student->streak += 1;
            } elseif ($diff > 1) {
                // ❌ Missed → reset
                $student->streak = 1;
            }
            // same day → do nothing
        } else {
            // First login
            $student->streak = 1;
        }

        $student->streak_last_date = $today;
        $student->save();
    }
}