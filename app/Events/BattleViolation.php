<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BattleViolation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomCode,
        public int    $userId,
        public string $violationType,
        public int    $count,
        public bool   $disqualified,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("battle.{$this->roomCode}")];
    }

    public function broadcastAs(): string
    {
        return 'violation';
    }
}