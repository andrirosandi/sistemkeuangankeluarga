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
     * Apply SMTP config dari database ke Laravel runtime config,
     * lalu purge cached mailer agar transport baru dibuat.
     */
    public static function applySmtpConfig(): void
    {
        $mailHost = Setting::get('mail_host');
        if ($mailHost) {
            $password = Setting::get('mail_password');
            $decryptedPassword = '';
            if ($password) {
                try {
                    $decryptedPassword = \Illuminate\Support\Facades\Crypt::decryptString($password);
                } catch (\Exception $e) {
                    // ignore decrypt error
                }
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $mailHost,
                'mail.mailers.smtp.port' => Setting::get('mail_port', 587),
                'mail.mailers.smtp.username' => Setting::get('mail_username'),
                'mail.mailers.smtp.password' => $decryptedPassword,
                'mail.mailers.smtp.encryption' => Setting::get('mail_encryption', 'tls'),
                'mail.from.address' => Setting::get('mail_from'),
                'mail.from.name' => Setting::get('app_name', config('app.name')),
            ]);

            // Purge cached SMTP transport agar config baru dipakai
            Mail::purge('smtp');
        }
    }

    /**
     * Public helper: apply SMTP config dari DB (dipanggil dari SettingController dsb.)
     */
    public static function refreshSmtpConfig(): void
    {
        self::applySmtpConfig();
    }

    /**
     * Dispatch email notification helper
     */
    private static function sendEmailIfReady(User $user, string $message, ?string $routeName, array $routeParams = []): void
    {
        if (self::isMailReady() && !empty($user->email)) {
            self::applySmtpConfig();
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

        $createdBy = $req->created_by;

        foreach ($approvers as $approver) {
            // Jangan kirim notifikasi ke pembuat request itu sendiri
            if ($approver->id == $createdBy) {
                continue;
            }

            $visibleUserIds = RoleVisibility::getVisibleUserIds($approver);
            if (!$visibleUserIds->contains($createdBy)) {
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
