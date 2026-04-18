<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
 
class InstitutionBattle extends Model
{
    protected $fillable = [
        'code', 'host_institution_id', 'quiz_id', 'status',
        'institution_count', 'student_limit', 'question_timer',
        'total_questions', 'current_question', 'anti_cheat',
        'scheduled_at', 'started_at', 'finished_at',
        'final_scores', 'winner_inst_id',
    ];
 
    protected $casts = [
        'anti_cheat'   => 'boolean',
        'final_scores' => 'array',
        'scheduled_at' => 'datetime',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
    ];
 
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
 
    public function hostInstitution()
    {
        return $this->belongsTo(Institution::class, 'host_institution_id');
    }
 
    public function institutionParticipants()
    {
        return $this->hasMany(InstitutionBattleParticipant::class, 'battle_id');
    }
 
    public function participants()
    {
        return $this->hasMany(BattleParticipant::class, 'inst_battle_id');
    }
 
    public function answers()
    {
        return $this->hasMany(BattleQuestionAnswer::class, 'inst_battle_id');
    }
 
    public static function generateCode(): string
    {
        do { $code = 'IB' . strtoupper(Str::random(6)); }
        while (static::where('code', $code)->exists());
        return $code;
    }
}