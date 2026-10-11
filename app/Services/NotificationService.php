<?php

namespace App\Services;

use App\Models\InAppNotification;
use App\Models\User;

class NotificationService
{
    /**
     * Kirim notifikasi internal dalam aplikasi ke pengguna tertentu.
     */
    public function notify(User|int $user, string $title, string $message, ?string $link = null): InAppNotification
    {
        $userId = $user instanceof User ? $user->id : $user;

        return InAppNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
        ]);
    }
}
