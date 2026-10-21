<?php

namespace App\Http\Controllers;

use App\Events\NotificationSent;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display Categorized Notification Toast Center
     */
    public function index(Request $request)
    {
        $filterType = $request->input('type');
        $notifications = Notification::when($filterType, function ($q) use ($filterType) {
            $q->where('type', $filterType);
        })
            ->latest()
            ->take(30)
            ->get();

        $stats = [
            'total' => Notification::count(),
            'info' => Notification::where('type', 'info')->count(),
            'success' => Notification::where('type', 'success')->count(),
            'warning' => Notification::where('type', 'warning')->count(),
            'urgent' => Notification::where('type', 'urgent')->orWhere('type', 'danger')->count(),
        ];

        return view('websocket.notifications', compact('notifications', 'stats', 'filterType'));
    }

    /**
     * Create & broadcast new notification to Soketi
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|string|in:info,success,warning,urgent,danger',
        ]);

        $notification = Notification::create([
            'title' => trim($request->title),
            'message' => trim($request->message),
            'type' => $request->type,
        ]);

        try {
            broadcast(new NotificationSent($notification));
        } catch (\Throwable $e) {
            \Log::warning('Notification broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'notification' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'created_at' => $notification->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Clear all notifications
     */
    public function clear()
    {
        Notification::truncate();
        return back()->with('success', 'All notifications cleared.');
    }
}
