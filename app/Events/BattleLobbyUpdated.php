<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BattleLobbyUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomCode,
        public array  $participants,
        public string $roomStatus,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("battle.{$this->roomCode}")];
    }

    public function broadcastAs(): string
    {
        return 'lobby.updated';
    }
}