<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class InstitutionBattleFinished implements ShouldBroadcast
{
    use InteractsWithBroadcasting, SerializesModels;

    public string $code;
    public array $finalScores;
    public array $institutionRankings;
    public array $topStudents;

    public function __construct(string $code, array $finalScores, array $institutionRankings, array $topStudents)
    {
        $this->code = $code;
        $this->finalScores = $finalScores;
        $this->institutionRankings = $institutionRankings;
        $this->topStudents = $topStudents;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('institution-battle.' . $this->code);
    }

    public function broadcastAs(): string
    {
        return 'institution-battle.finished';
    }
}