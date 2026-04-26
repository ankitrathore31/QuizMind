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
        'subjects_interest'  => 'array',
        'badges'             => 'array',
        'streak_last_date'   => 'date:Y-m-d',   // store as plain date string — no timezone drift
        'is_profile_complete' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getXpToNextLevelAttribute(): int
    {
        return 100 + (150 * ($this->level - 1));
        // L1=100, L2=250, L3=400, L4=550, L5=700 …
    }

    /** XP progress percentage within current level */
    public function getXpProgressAttribute(): int
    {
        $needed = $this->xpToNextLevel;
        return $needed > 0 ? min(100, (int) round(($this->xp / $needed) * 100)) : 0;
    }

    /** Level title */
    public function getLevelTitleAttribute(): string
    {
        $titles = [
            1   => 'Novice',
            5   => 'Apprentice',
            10  => 'Scholar',
            20  => 'Expert',
            30  => 'Master',
            50  => 'Grandmaster',
            75  => 'Legend',
            100 => 'Mythic',
        ];
        $title = 'Novice';
        foreach ($titles as $lvl => $t) {
            if ($this->level >= $lvl) $title = $t;
        }
        return $title;
    }

    /** Accuracy percentage */
    public function getAccuracyAttribute(): int
    {
        $total = $this->total_correct + $this->total_wrong;
        return $total > 0 ? (int) round(($this->total_correct / $total) * 100) : 0;
    }

    /** Win rate percentage */
    public function getWinRateAttribute(): int
    {
        $battles = $this->total_battles_won + $this->total_battles_lost;
        return $battles > 0 ? (int) round(($this->total_battles_won / $battles) * 100) : 0;
    }

    
    public function addXp(int $amount): void
    {
        $this->xp += $amount;

        while (true) {
            $needed = 100 + (150 * ($this->level - 1));
            if ($this->xp < $needed) break;
            $this->xp -= $needed;
            $this->level++;
        }

        $this->save();
    }

  
    public function updateStreak(): void
    {
        // Use app timezone-aware "today" string
        $today     = now()->toDateString();                       // e.g. "2025-04-25"
        $yesterday = now()->subDay()->toDateString();             // e.g. "2025-04-24"

        // streak_last_date is cast as date — format it back to a plain string
        $last = $this->streak_last_date
            ? $this->streak_last_date->format('Y-m-d')
            : null;

        // Already logged today — do nothing
        if ($last === $today) {
            return;
        }

        if ($last === $yesterday) {
            // Consecutive day — extend streak
            $this->streak++;
        } else {
            // Gap or first login — reset to 1
            $this->streak = 1;
        }

        $this->streak_last_date = $today;
        $this->save();
    }
}
