<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class InstitutionNextQuestion implements ShouldBroadcast
{
    use InteractsWithBroadcasting, SerializesModels;

    public string $code;
    public int $questionIndex;

    public function __construct(string $code, int $questionIndex)
    {
        $this->code = $code;
        $this->questionIndex = $questionIndex;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('institution-battle.' . $this->code);
    }

    public function broadcastAs(): string
    {
        return 'institution-next.question';
    }
}