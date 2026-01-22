<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()));

        if ($request->unread_only) {
            $query->whereNull('read_at');
        }

        $notifications = $query->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => collect($notifications->items())->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => class_basename($n->type),
                    'title' => $n->data['title'] ?? 'Notification',
                    'message' => $n->data['message'] ?? '',
                    'data' => $n->data,
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at' => $n->created_at->toIso8601String(),
                ]),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $notif = Notification::where('id', $notification)
            ->where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->first();

        if (! $notif) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notif->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
}
