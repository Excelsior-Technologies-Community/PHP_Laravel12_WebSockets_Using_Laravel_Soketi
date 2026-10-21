<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Presence Channel for Live Active Users Radar
Broadcast::channel('presence-room.{roomId}', function ($user, $roomId) {
    if ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => '👤',
        ];
    }

    $guestId = crc32(request()->ip() . session()->getId());
    $guestName = session('guest_presence_name', 'Visitor_' . substr(abs($guestId), 0, 4));

    return [
        'id' => abs($guestId),
        'name' => $guestName,
        'email' => 'visitor@soketi.local',
        'avatar' => '🌐',
    ];
});
