<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $username,
        public bool $isTyping = true
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('chat-channel')];
    }

    public function broadcastAs(): string
    {
        return 'user.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'username' => $this->username,
            'is_typing' => $this->isTyping,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
