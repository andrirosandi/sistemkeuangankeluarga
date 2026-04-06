<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\RoleVisibility;
use App\Models\User;
use App\Models\Setting;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Cek apakah SMTP sudah disetup dan siap mengirim email.
     */
    private static function isMailReady(): bool
    {
        return !empty(Setting::get('smtp_verified_at')) && !empty(Setting::get('mail_host'));
    }

    /**
     * Dispatch email notification helper
     */
    private static function sendEmailIfReady(User $user, string $message, ?string $routeName, array $routeParams = []): void
    {
        if (self::isMailReady() && !empty($user->email)) {
            try {
                $actionUrl = $routeName ? route($routeName, $routeParams) : null;
                Mail::to($user->email)->send(new NotificationMail($message, $actionUrl));
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email notifikasi: ' . $e->getMessage());
            }
        }
    }

    /**
     * Kirim notifikasi ke satu user.
     */
    public static function notifyUser($userId, $message, $routeName = null, $routeParams = []): void
    {
        Notification::create([
            'user_id' => $userId,
            'message' => $message,
            'route_name' => $routeName,
            'route_params' => $routeParams,
            'is_read' => false,
        ]);

        $user = User::find($userId);
        if ($user) {
            self::sendEmailIfReady($user, $message, $routeName, $routeParams);
        }
    }

    /**
     * Kirim notifikasi ke semua user yang punya permission approve.
     * Anti-duplikat: cek apakah ada notifikasi belum dibaca dengan deskripsi yang sama.
     */
    public static function notifyApprovers($req, $type, $routeName = null, $routeParams = []): void
    {
        $permissionName = $type == 'in' ? 'in.request.approve' : 'out.request.approve';
        $approvers = User::permission($permissionName)->get();

        $keyword = 'Pengajuan baru menunggu persetujuan: <strong>' . htmlspecialchars($req->description) . '</strong>';

        foreach ($approvers as $approver) {
            $visibleUserIds = RoleVisibility::getVisibleUserIds($approver);
            if (!$visibleUserIds->contains($req->created_by)) {
                continue;
            }

            $exists = Notification::where('user_id', $approver->id)
                ->where('message', 'like', '%' . addcslashes($req->description, '%_') . '%')
                ->where('is_read', false)
                ->exists();

            if (!$exists) {
                $messageLabel = $keyword . ' dari ' . (auth()->user()->name ?? 'Sistem');
                Notification::create([
                    'user_id' => $approver->id,
                    'message' => $messageLabel,
                    'route_name' => $routeName,
                    'route_params' => $routeParams,
                    'is_read' => false,
                ]);

                self::sendEmailIfReady($approver, $messageLabel, $routeName, $routeParams);
            }
        }
    }
}
