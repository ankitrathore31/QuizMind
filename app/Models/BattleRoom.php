<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BattleRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'host_id', 'quiz_id', 'mode', 'status',
        'team_a_name', 'team_b_name', 'max_per_team',
        'question_timer', 'total_questions',
        'final_scores', 'winner_team', 'winner_user_id',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'final_scores' => 'array',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────
    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function participants()
    {
        return $this->hasMany(BattleParticipant::class, 'room_id');
    }

    public function answers()
    {
        return $this->hasMany(BattleQuestionAnswer::class, 'room_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    // ── Helpers ───────────────────────────────────
    public static function generateCode(): string
    {
        do {
            $code = 'QM-' . strtoupper(Str::random(4));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function getInviteUrlAttribute(): string
    {
        return url("/student/battle/join/{$this->code}");
    }

    public function getTeamAUrlAttribute(): string
    {
        return url("/student/battle/team/{$this->code}/a");
    }

    public function getTeamBUrlAttribute(): string
    {
        return url("/student/battle/team/{$this->code}/b");
    }

    public function isHost(int $userId): bool
    {
        return $this->host_id === $userId;
    }

    public function hasParticipant(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    public function teamScore(string $team): int
    {
        return $this->participants()->where('team', $team)->sum('score');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'in_progress']);
    }
}