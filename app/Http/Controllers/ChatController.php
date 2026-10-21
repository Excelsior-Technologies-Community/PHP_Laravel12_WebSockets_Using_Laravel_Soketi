<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display Real-Time Chat Studio with Typing Indicators
     */
    public function index()
    {
        $messages = Message::latest()
            ->take(30)
            ->get()
            ->reverse()
            ->values();

        return view('websocket.chat', compact('messages'));
    }

    /**
     * Send chat message & broadcast to Soketi WebSocket
     */
    public function send(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'message' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'name' => trim($request->name),
            'message' => trim($request->message),
        ]);

        try {
            broadcast(new MessageSent($message));
        } catch (\Throwable $e) {
            // Log broadcast error if Soketi is down, but still save message to DB
            \Log::warning('Broadcast failed (Soketi offline?): ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $message->id,
                'name' => $message->name,
                'message' => $message->message,
                'created_at' => $message->created_at->format('h:i A'),
            ],
        ]);
    }

    /**
     * Broadcast User Typing event to Soketi
     */
    public function typing(Request $request)
    {
        $username = trim($request->input('username', 'Anonymous'));
        $isTyping = (bool) $request->input('is_typing', true);

        try {
            broadcast(new UserTyping($username, $isTyping));
        } catch (\Throwable $e) {
            \Log::warning('UserTyping broadcast failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /**
     * Clear all chat history
     */
    public function clear()
    {
        Message::truncate();
        return back()->with('success', 'Chat message history cleared.');
    }
}
