<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'display_name',
        'avatar',
        'class',
        'age',
        'school_name',
        'subjects_interest',
        'bio',
        'level',
        'xp',
        'xp_to_next_level',
        'streak',
        'streak_last_date',
        'total_quizzes',
        'total_correct',
        'total_wrong',
        'total_battles_won',
        'total_battles_lost',
        'badges',
        'rank',
        'is_profile_complete',
    ];

    protected $casts = [
        'subjects_interest' => 'array',
        'badges' => 'array',
        'streak_last_date' => 'date',
        'is_profile_complete' => 'boolean',
    ];

    // Relationship: belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // XP needed to reach next level (100 * level^1.5)
    public function getXpToNextLevelAttribute($value)
    {
        return (int) (100 * pow($this->level, 1.5));
    }

    // XP progress percentage
    public function getXpProgressAttribute()
    {
        $needed = (int) (100 * pow($this->level, 1.5));
        return $needed > 0 ? min(100, round(($this->xp / $needed) * 100)) : 0;
    }

    // Level title based on level
    public function getLevelTitleAttribute()
    {
        $titles = [
            1  => 'Novice',
            5  => 'Apprentice',
            10 => 'Scholar',
            20 => 'Expert',
            30 => 'Master',
            50 => 'Grandmaster',
            75 => 'Legend',
            100=> 'Mythic',
        ];
        $title = 'Novice';
        foreach ($titles as $lvl => $t) {
            if ($this->level >= $lvl) $title = $t;
        }
        return $title;
    }

    // Accuracy percentage
    public function getAccuracyAttribute()
    {
        $total = $this->total_correct + $this->total_wrong;
        return $total > 0 ? round(($this->total_correct / $total) * 100) : 0;
    }

    // Win rate percentage
    public function getWinRateAttribute()
    {
        $battles = $this->total_battles_won + $this->total_battles_lost;
        return $battles > 0 ? round(($this->total_battles_won / $battles) * 100) : 0;
    }

    // Add XP and handle level up
    public function addXp(int $amount)
    {
        $this->xp += $amount;
        while ($this->xp >= $this->xpToNextLevel) {
            $this->xp -= $this->xpToNextLevel;
            $this->level++;
        }
        $this->save();
    }

    // Update streak
    public function updateStreak()
    {
        $today = now()->toDateString();
        $last  = $this->streak_last_date?->toDateString();

        if ($last === $today) return; // already updated today

        if ($last === now()->subDay()->toDateString()) {
            $this->streak++;
        } else {
            $this->streak = 1;
        }
        $this->streak_last_date = $today;
        $this->save();
    }
}