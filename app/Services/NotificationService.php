<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send notification to specific users by IDs or Models
     */
    public static function sendToUsers($users, string $title, string $message, string $url = '#', string $type = 'info'): void
    {
        if (empty($users)) return;

        if (is_numeric($users) || is_string($users)) {
            $users = User::where('id', $users)->get();
        } elseif (is_array($users)) {
            $users = User::whereIn('id', $users)->get();
        }

        Notification::send($users, new SystemNotification($title, $message, $url, $type));
    }

    /**
     * Send notification to users by role(s)
     */
    public static function sendToRoles(array|string $roles, string $title, string $message, string $url = '#', string $type = 'info'): void
    {
        $roles = (array) $roles;
        $users = User::whereIn('role', $roles)->where('is_active', true)->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new SystemNotification($title, $message, $url, $type));
        }
    }

    /**
     * Send notification to users in specific division, optionally filtered by role
     */
    public static function sendToDivision(int $divisionId, string $title, string $message, string $url = '#', string $type = 'info', array|string|null $roles = null): void
    {
        $query = User::where('division_id', $divisionId)->where('is_active', true);

        if ($roles) {
            $query->whereIn('role', (array) $roles);
        }

        $users = $query->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new SystemNotification($title, $message, $url, $type));
        }
    }
}
