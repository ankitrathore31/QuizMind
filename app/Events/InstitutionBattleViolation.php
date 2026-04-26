<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class InstitutionBattleViolation implements ShouldBroadcast
{
    use InteractsWithBroadcasting, SerializesModels;

    public string $code;
    public int $userId;
    public string $type; // 'tab_switch' or 'window_blur'
    public bool $disqualified;

    public function __construct(string $code, int $userId, string $type, bool $disqualified)
    {
        $this->code = $code;
        $this->userId = $userId;
        $this->type = $type;
        $this->disqualified = $disqualified;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('institution-battle.' . $this->code);
    }

    public function broadcastAs(): string
    {
        return 'institution-battle.violation';
    }
}