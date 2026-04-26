<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class InstitutionAnswerSubmitted implements ShouldBroadcast
{
    use InteractsWithBroadcasting, SerializesModels;

    public string $code;
    public array $scores;
    public int $questionIndex;
    public array $correctInfo; // ['correct_option' => int, 'explanation' => string]

    public function __construct(string $code, array $scores, int $questionIndex, array $correctInfo)
    {
        $this->code = $code;
        $this->scores = $scores;
        $this->questionIndex = $questionIndex;
        $this->correctInfo = $correctInfo;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('institution-battle.' . $this->code);
    }

    public function broadcastAs(): string
    {
        return 'institution-answer.submitted';
    }
}