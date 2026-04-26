<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionBattleQuestionAnswer extends Model
{
    protected $fillable = [
        'battle_id',
        'user_id',
        'question_index',
        'selected_option',
        'is_correct',
        'time_ms',
        'points_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function battle(): BelongsTo
    {
        return $this->belongsTo(InstitutionBattle::class, 'battle_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}