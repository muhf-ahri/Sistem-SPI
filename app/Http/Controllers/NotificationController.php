<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(15);
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(Request $request)
    {
        $user = auth()->user();

        if ($request->filled('id')) {
            $notification = $user->notifications()->where('id', $request->id)->first();
            if ($notification && !$notification->read_at) {
                $notification->markAsRead();
            }
        } else {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }
}
