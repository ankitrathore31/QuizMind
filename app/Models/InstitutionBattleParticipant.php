<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class InstitutionBattleParticipant extends Model
{
    protected $fillable = [
        'battle_id', 'institution_id', 'name', 'student_code',
        'is_host', 'total_score',
    ];
 
    protected $casts = [
        'is_host' => 'boolean',
    ];
 
    public function battle()
    {
        return $this->belongsTo(InstitutionBattle::class, 'battle_id');
    }
 
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
 
    public function participants()
    {
        return $this->hasMany(BattleParticipant::class, 'institution_battle_participant_id');
    }
}
