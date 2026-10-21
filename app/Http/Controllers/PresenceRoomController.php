<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresenceRoomController extends Controller
{
    /**
     * Display Presence Channel & Active Users Radar
     */
    public function index(Request $request)
    {
        $roomId = 'general';
        $users = User::take(6)->get();
        $currentUser = Auth::user();

        // If not logged in, generate a persistent guest name for this session
        if (!$currentUser && !session()->has('guest_presence_name')) {
            session(['guest_presence_name' => 'Guest_' . substr(md5(session()->getId() ?: (string) rand()), 0, 4)]);
        }

        $activeIdentity = $currentUser ? $currentUser->name : session('guest_presence_name');

        return view('websocket.presence', compact('roomId', 'users', 'currentUser', 'activeIdentity'));
    }

    /**
     * Quick identity switch for presence testing
     */
    public function switchIdentity(Request $request)
    {
        $userId = $request->input('user_id');
        if ($userId) {
            $user = User::findOrFail($userId);
            Auth::login($user);
            return back()->with('success', "Logged in as {$user->name} for Presence radar.");
        }

        Auth::logout();
        session(['guest_presence_name' => $request->input('guest_name', 'Guest_' . rand(100, 999))]);
        return back()->with('success', "Switched identity to Guest.");
    }
}
