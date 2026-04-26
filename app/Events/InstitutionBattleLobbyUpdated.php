<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class InstitutionBattleLobbyUpdated implements ShouldBroadcast
{
    use InteractsWithBroadcasting, SerializesModels;

    public string $code;
    public array $participants;
    public string $status;
    public int $totalParticipants;

    public function __construct(string $code, array $participants, string $status, int $totalParticipants)
    {
        $this->code = $code;
        $this->participants = $participants;
        $this->status = $status;
        $this->totalParticipants = $totalParticipants;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('institution-battle.' . $this->code);
    }

    public function broadcastAs(): string
    {
        return 'institution-battle.lobby-updated';
    }
}