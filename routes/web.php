<?php

use App\Events\MessageSent;
use Illuminate\Support\Facades\Route;

Route::get('/send-message', function () {
    broadcast(new MessageSent('Hello from Laravel WebSocket!'));
    return 'Message Sent';
});

Route::get('/', function () {
    return view('chat');
});