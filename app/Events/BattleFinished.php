<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BattleFinished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string  $roomCode,
        public array   $finalScores,
        public ?string $winnerTeam,
        public ?array  $winnerUser,
        public string  $mode,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("battle.{$this->roomCode}")];
    }

    public function broadcastAs(): string
    {
        return 'battle.finished';
    }
}