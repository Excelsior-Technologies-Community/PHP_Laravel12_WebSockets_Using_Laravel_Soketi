<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerHeartbeatEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public array $telemetryData
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('server-telemetry')];
    }

    public function broadcastAs(): string
    {
        return 'server.heartbeat';
    }

    public function broadcastWith(): array
    {
        return $this->telemetryData;
    }
}
