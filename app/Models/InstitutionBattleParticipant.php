<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionBattleParticipant extends Model
{
    protected $fillable = [
        'battle_id', 'user_id', 'institution_id', 'status',
        'score', 'correct', 'wrong', 'streak', 'max_streak',
        'xp_earned', 'time_taken', 'finished_at',
        'tab_switches', 'window_blurs',
        'disqualified', 'disqualify_reason',
    ];

    protected $casts = [
        'disqualified' => 'boolean',
        'finished_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function battle()
    {
        return $this->belongsTo(InstitutionBattle::class, 'battle_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────
    public function getAccuracyAttribute(): int
    {
        $total = $this->correct + $this->wrong;
        if ($total === 0) return 0;
        return (int) round(($this->correct / $total) * 100);
    }

    // ── Helpers ────────────────────────────────────────────────────────
    public function incrementViolation(string $type): void
    {
        if ($type === 'tab_switch') {
            $this->increment('tab_switches');
        } else {
            $this->increment('window_blurs');
        }

        $this->refresh();
        $totalViolations = $this->tab_switches + $this->window_blurs;

        // Penalise score
        $this->decrement('score', 10);

        // Disqualify after 3 violations
        if ($totalViolations >= 3) {
            $this->update([
                'disqualified'      => true,
                'disqualify_reason' => 'Anti-cheat: too many violations',
            ]);
        }
    }
}