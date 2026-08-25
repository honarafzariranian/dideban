<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Create a new notification
     */
    public function create(array $data): Notification
    {
        return DB::transaction(function () use ($data) {
            return Notification::create([
                'organization_id' => $data['organization_id'],
                'user_id' => $data['user_id'] ?? null,
                'type' => $data['type'],
                'title' => $data['title'],
                'message' => $data['message'] ?? null,
                'data' => $data['data'] ?? null,
                'action_url' => $data['action_url'] ?? null,
                'action_text' => $data['action_text'] ?? null,
                'channel' => $data['channel'] ?? 'database',
                'priority' => $data['priority'] ?? 'normal',
                'group' => $data['group'] ?? null,
            ]);
        });
    }

    /**
     * Send notification to a user
     */
    public function sendToUser(User $user, array $data): Notification
    {
        $data['user_id'] = $user->id;
        $data['organization_id'] = $user->organization_id;

        return $this->create($data);
    }

    /**
     * Send notification to multiple users
     */
    public function sendToUsers($users, array $data): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $data);
        }
    }

    /**
     * Send notification to all users in an organization
     */
    public function sendToOrganization(int $organizationId, array $data): void
    {
        $users = User::where('organization_id', $organizationId)
                     ->where('is_active', true)
                     ->get();

        $this->sendToUsers($users, $data);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId, bool $unreadOnly = false, int $limit = 50)
    {
        $query = Notification::where('user_id', $userId);

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
                          ->unread()
                          ->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
                                    ->where('user_id', $userId)
                                    ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)
                    ->unread()
                    ->update([
                        'is_read' => true,
                        'read_at' => now(),
                    ]);
    }

    /**
     * Delete old notifications
     */
    public function cleanOldNotifications(int $days = 90): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
                          ->delete();
    }
}
