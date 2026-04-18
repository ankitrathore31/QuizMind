<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BattleStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomCode,
        public array  $questions,
        public int    $questionTimer,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("battle.{$this->roomCode}")];
    }

    public function broadcastAs(): string
    {
        return 'battle.started';
    }
}