<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): Response
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications->through(fn ($n) => $this->formatNotification($n)),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Get notifications for the dropdown panel.
     */
    public function dropdown(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->take(10)
            ->get()
            ->map(fn ($n) => $this->formatNotification($n));

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
            'unreadCount' => 0,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted.',
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json([
            'message' => 'All notifications cleared.',
            'unreadCount' => 0,
        ]);
    }

    /**
     * Format a notification for the frontend.
     */
    private function formatNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $type = $data['type'] ?? class_basename($notification->type);

        // Map notification types to icons and colors
        $typeConfig = $this->getNotificationTypeConfig($type);

        return [
            'id' => $notification->id,
            'type' => $type,
            'title' => $data['title'] ?? $typeConfig['defaultTitle'],
            'message' => $data['message'] ?? $data['body'] ?? '',
            'icon' => $data['icon'] ?? $typeConfig['icon'],
            'color' => $data['color'] ?? $typeConfig['color'],
            'href' => $data['href'] ?? $data['action_url'] ?? null,
            'read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }

    /**
     * Get configuration for notification type.
     */
    private function getNotificationTypeConfig(string $type): array
    {
        $configs = [
            'transfer' => [
                'icon' => 'pi-send',
                'color' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
                'defaultTitle' => 'Transfer Update',
            ],
            'transfer_received' => [
                'icon' => 'pi-download',
                'color' => 'text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400',
                'defaultTitle' => 'Transfer Received',
            ],
            'deposit' => [
                'icon' => 'pi-download',
                'color' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
                'defaultTitle' => 'Deposit Update',
            ],
            'withdrawal' => [
                'icon' => 'pi-upload',
                'color' => 'text-orange-600 bg-orange-100 dark:bg-orange-900/30 dark:text-orange-400',
                'defaultTitle' => 'Withdrawal Update',
            ],
            'loan' => [
                'icon' => 'pi-wallet',
                'color' => 'text-violet-600 bg-violet-100 dark:bg-violet-900/30 dark:text-violet-400',
                'defaultTitle' => 'Loan Update',
            ],
            'card' => [
                'icon' => 'pi-credit-card',
                'color' => 'text-purple-600 bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400',
                'defaultTitle' => 'Card Update',
            ],
            'grant' => [
                'icon' => 'pi-dollar',
                'color' => 'text-teal-600 bg-teal-100 dark:bg-teal-900/30 dark:text-teal-400',
                'defaultTitle' => 'Grant Update',
            ],
            'security' => [
                'icon' => 'pi-shield',
                'color' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
                'defaultTitle' => 'Security Alert',
            ],
            'support' => [
                'icon' => 'pi-comments',
                'color' => 'text-cyan-600 bg-cyan-100 dark:bg-cyan-900/30 dark:text-cyan-400',
                'defaultTitle' => 'Support Update',
            ],
            'kyc' => [
                'icon' => 'pi-id-card',
                'color' => 'text-amber-600 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400',
                'defaultTitle' => 'KYC Update',
            ],
            'reward' => [
                'icon' => 'pi-star-fill',
                'color' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400',
                'defaultTitle' => 'Reward Update',
            ],
            'promo' => [
                'icon' => 'pi-gift',
                'color' => 'text-rose-600 bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400',
                'defaultTitle' => 'Special Offer',
            ],
            'admin' => [
                'icon' => 'pi-megaphone',
                'color' => 'text-indigo-600 bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400',
                'defaultTitle' => 'Admin Message',
            ],
            'system' => [
                'icon' => 'pi-info-circle',
                'color' => 'text-gray-600 bg-gray-100 dark:bg-gray-700/50 dark:text-gray-400',
                'defaultTitle' => 'System Update',
            ],
        ];

        return $configs[strtolower($type)] ?? [
            'icon' => 'pi-bell',
            'color' => 'text-indigo-600 bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400',
            'defaultTitle' => 'Notification',
        ];
    }
}
