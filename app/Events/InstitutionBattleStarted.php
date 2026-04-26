<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class InstitutionBattleStarted implements ShouldBroadcast
{
    use InteractsWithBroadcasting, SerializesModels;

    public string $code;
    public array $questions;
    public int $questionTimer;
    public int $currentQuestion;

    public function __construct(string $code, array $questions, int $questionTimer, int $currentQuestion)
    {
        $this->code = $code;
        $this->questions = $questions;
        $this->questionTimer = $questionTimer;
        $this->currentQuestion = $currentQuestion;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('institution-battle.' . $this->code);
    }

    public function broadcastAs(): string
    {
        return 'institution-battle.started';
    }
}