<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleParticipant extends Model
{
    protected $fillable = [
        'room_id', 'user_id', 'team', 'status',
        'score', 'correct', 'wrong', 'streak', 'max_streak',
        'xp_earned', 'time_taken',
        'tab_switches', 'window_blurs',
        'disqualified', 'disqualify_reason',
        'answer_log',
    ];

    protected $casts = [
        'answer_log'    => 'array',
        'disqualified'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(BattleRoom::class, 'room_id');
    }

    public function getAccuracyAttribute(): int
    {
        $total = $this->correct + $this->wrong;
        if ($total === 0) return 0;
        return (int) round(($this->correct / $total) * 100);
    }
}


class BattleQuestionAnswer extends Model
{
    protected $table = 'battle_question_answers';

    protected $fillable = [
        'room_id', 'user_id', 'question_index',
        'selected_option', 'is_correct', 'time_ms', 'points_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}