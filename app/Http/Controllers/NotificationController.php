<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Events\NotificationSent;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function index()
    {

        $notifications = Notification::latest()->get();

        return view('notifications.index', compact('notifications'));

    }

    public function store(Request $request)
    {

        $request->validate([

            'title' => 'required|max:255',

            'message' => 'required',

            'type' => 'required'

        ]);

        $notification = Notification::create([

            'title' => $request->title,

            'message' => $request->message,

            'type' => $request->type

        ]);

        broadcast(new NotificationSent($notification));

        return response()->json([

            'status' => true,

            'notification' => $notification

        ]);

    }

}