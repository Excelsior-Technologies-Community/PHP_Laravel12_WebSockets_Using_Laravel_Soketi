<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;


class UserTyping implements ShouldBroadcast
{

    use SerializesModels;


    public $username;


    public function __construct($username)
    {
        $this->username = $username;
    }


    public function broadcastOn()
    {
        return new Channel('chat-channel');
    }


    public function broadcastAs()
    {
        return 'user.typing';
    }
}
