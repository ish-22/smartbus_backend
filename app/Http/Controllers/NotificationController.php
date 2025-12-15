<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List notifications for the authenticated user (e.g. driver).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Optional filter: unread / all
        if ($request->has('only_unread') && $request->only_unread) {
            $query->whereNull('read_at');
        }

        $notifications = $query->get();

        return response()->json([
            'data' => $notifications,
            'unread_count' => $notifications->whereNull('read_at')->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    /**
     * Mark all notifications for the user as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Admin: create notifications for one user or a role.
     */
    public function adminStore(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate request data
        $data = $request->validate([
            'target_role' => 'nullable|in:driver,passenger,owner,admin',
            'title'       => 'required|string|max:255',
            'message'     => 'required|string',
            'type'        => 'required|in:info,warning,success',
        ]);

        // Build recipients query
        $query = \App\Models\User::query();

        if (!empty($data['target_role'])) {
            // Specific role targeted - match case-insensitively in case existing users have uppercase roles
            $role = strtolower($data['target_role']);
            $query->whereRaw('LOWER(role) = ?', [$role]);
        } else {
            // No target specified - notify all users
            // This handles the "all" case from frontend
        }

        $recipients = $query->get(['id', 'role', 'name', 'email']);

        if ($recipients->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients found for the specified criteria',
            ], 400);
        }

        $created = 0;
        foreach ($recipients as $recipient) {
            try {
                Notification::create([
                    'user_id' => $recipient->id,
                    'title'   => $data['title'],
                    'message' => $data['message'],
                    'type'    => $data['type'],
                ]);
                $created++;
            } catch (\Exception $e) {
                \Log::error('Failed to create notification for user ' . $recipient->id . ': ' . $e->getMessage());
            }
        }

        // Also create a copy for the admin who sent it, so they can see it in their own notifications page
        try {
            Notification::create([
                'user_id' => $user->id,
                'title'   => '[Sent] ' . $data['title'],
                'message' => $data['message'],
                'type'    => 'info',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create admin self-notification: ' . $e->getMessage());
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Notification(s) created successfully',
            'recipients'  => $created,
            'total_found' => $recipients->count(),
        ]);
    }
}


