<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;


    public function __construct(
        public Notification $notification
    ) {}



    public function broadcastOn(): array
    {
        return [
            new Channel('notifications')
        ];
    }



    public function broadcastAs(): string
    {
        return 'notification.sent';
    }



    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id' => $this->notification->id,
                'title' => $this->notification->title,
                'message' => $this->notification->message,
                'type' => $this->notification->type,
                'created_at' => $this->notification->created_at,
            ]
        ];
    }
}