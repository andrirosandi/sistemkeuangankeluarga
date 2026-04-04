<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Kirim notifikasi ke satu user.
     */
    public static function notifyUser($userId, $message): void
    {
        Notification::create([
            'user_id' => $userId,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    /**
     * Kirim notifikasi ke semua user yang punya permission approve.
     * Anti-duplikat: cek apakah ada notifikasi belum dibaca dengan deskripsi yang sama.
     */
    public static function notifyApprovers($req, $type): void
    {
        $permissionName = $type == 'in' ? 'in.request.approve' : 'out.request.approve';
        $approvers = User::permission($permissionName)->get();

        $keyword = 'Pengajuan baru menunggu persetujuan: <strong>' . htmlspecialchars($req->description) . '</strong>';

        foreach ($approvers as $approver) {
            $exists = Notification::where('user_id', $approver->id)
                ->where('message', 'like', '%' . addcslashes($req->description, '%_') . '%')
                ->where('is_read', false)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $approver->id,
                    'message' => $keyword . ' dari ' . auth()->user()->name,
                    'is_read' => false,
                ]);
            }
        }
    }
}
