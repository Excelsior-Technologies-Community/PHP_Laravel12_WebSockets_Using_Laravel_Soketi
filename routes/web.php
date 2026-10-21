<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WebSocketDiagnosticsController;
use App\Http\Controllers\PresenceRoomController;
use App\Http\Controllers\LivePollController;
use App\Http\Controllers\ServerTelemetryController;

// Home redirect to diagnostics studio
Route::get('/', function () {
    return redirect()->route('websocket.diagnostics');
})->name('home');

// 1. Soketi Server Health & Real-Time Connection Diagnostics Studio
Route::get('/websocket/diagnostics', [WebSocketDiagnosticsController::class, 'index'])->name('websocket.diagnostics');
Route::post('/websocket/diagnostics/ping', [WebSocketDiagnosticsController::class, 'ping'])->name('websocket.diagnostics.ping');

// 2. Presence Channels & Live Active Users Radar
Route::get('/websocket/presence', [PresenceRoomController::class, 'index'])->name('websocket.presence');
Route::post('/websocket/presence/switch', [PresenceRoomController::class, 'switchIdentity'])->name('websocket.presence.switch');

// 4. Real-Time Chat & Typing Indicators
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/send-message', [ChatController::class, 'send'])->name('chat.send');
Route::post('/chat/typing', [ChatController::class, 'typing'])->name('chat.typing');
Route::post('/chat/clear', [ChatController::class, 'clear'])->name('chat.clear');

// 5. Live Interactive Polling & Instant Voting Room
Route::get('/websocket/polls', [LivePollController::class, 'index'])->name('websocket.polls');
Route::post('/websocket/polls/vote', [LivePollController::class, 'vote'])->name('websocket.polls.vote');
Route::post('/websocket/polls/reset', [LivePollController::class, 'reset'])->name('websocket.polls.reset');

// 6. Categorized Notification Toast Center with Audio Chimes
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
Route::post('/notifications/clear', [NotificationController::class, 'clear'])->name('notifications.clear');

// 7. Real-Time Server Heartbeat & Live Telemetry Streamer
Route::get('/websocket/telemetry', [ServerTelemetryController::class, 'index'])->name('websocket.telemetry');
Route::post('/websocket/telemetry/stream', [ServerTelemetryController::class, 'stream'])->name('websocket.telemetry.stream');
