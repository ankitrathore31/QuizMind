<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionBattleHistory extends Model
{
    protected $fillable = [
        'institution_id',
        'battle_id',
        'total_participants',
        'total_correct',
        'total_wrong',
        'total_score',
        'average_accuracy',
        'average_time',
        'rank',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(InstitutionBattle::class);
    }
}