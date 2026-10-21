<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('chat-channel')];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'name' => $this->message->name,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at->format('h:i A'),
        ];
    }
}
