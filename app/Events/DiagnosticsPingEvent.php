<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiagnosticsPingEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $pingId,
        public string $serverTime,
        public string $driver,
        public int $port
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('soketi-diagnostics')];
    }

    public function broadcastAs(): string
    {
        return 'diagnostics.ping';
    }

    public function broadcastWith(): array
    {
        return [
            'ping_id' => $this->pingId,
            'server_time' => $this->serverTime,
            'driver' => $this->driver,
            'port' => $this->port,
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'timestamp_ms' => round(microtime(true) * 1000),
        ];
    }
}
