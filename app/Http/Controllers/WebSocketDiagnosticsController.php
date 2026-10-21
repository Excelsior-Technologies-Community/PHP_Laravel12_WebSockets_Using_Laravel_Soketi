<?php

namespace App\Http\Controllers;

use App\Events\DiagnosticsPingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebSocketDiagnosticsController extends Controller
{
    /**
     * Display Soketi Health & Connection Diagnostics Studio
     */
    public function index()
    {
        $config = [
            'broadcaster' => config('broadcasting.default'),
            'driver' => config('broadcasting.connections.pusher.driver', 'pusher'),
            'app_key' => config('broadcasting.connections.pusher.key', 'local'),
            'host' => config('broadcasting.connections.pusher.options.host', '127.0.0.1'),
            'port' => config('broadcasting.connections.pusher.options.port', 6001),
            'scheme' => config('broadcasting.connections.pusher.options.scheme', 'http'),
        ];

        return view('websocket.diagnostics', compact('config'));
    }

    /**
     * Dispatch a Test Ping Event through Soketi WebSocket Server
     */
    public function ping(Request $request)
    {
        $pingId = (string) Str::uuid();
        $serverTime = now()->toIso8601String();
        $port = (int) config('broadcasting.connections.pusher.options.port', 6001);

        $broadcastDelivered = false;
        $broadcastError = null;

        try {
            broadcast(new DiagnosticsPingEvent(
                pingId: $pingId,
                serverTime: $serverTime,
                driver: 'Soketi / Pusher Protocol',
                port: $port
            ));
            $broadcastDelivered = true;
        } catch (\Throwable $e) {
            $broadcastDelivered = false;
            $broadcastError = $e->getMessage();
        }

        return response()->json([
            'success' => true,
            'broadcast_delivered' => $broadcastDelivered,
            'error' => $broadcastError,
            'suggestion' => $broadcastDelivered ? null : "Soketi server is offline on port {$port}. Run 'npx @soketi/soketi start' in a terminal.",
            'ping_id' => $pingId,
            'sent_at' => $serverTime,
            'channel' => 'soketi-diagnostics',
            'event' => 'diagnostics.ping',
            'message' => $broadcastDelivered 
                ? 'Diagnostics Ping broadcast dispatched successfully to Soketi.'
                : "Diagnostics Ping created, but Soketi is not listening on port {$port}.",
        ]);
    }
}
