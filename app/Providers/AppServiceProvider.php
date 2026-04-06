<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @uang($amount) — formats number with currency symbol from settings
        // Usage: @uang(150000) → "Rp 150.000"
        //        @uang($item->amount)
        //        @uang(-5000) → "Rp -5.000"
        //
        // @bulan($date) — formats date to Indonesian month name
        // Usage: @bulan('2026-04-06') → "April 2026"
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Apply SMTP settings from DB to runtime config
        try {
            $mailHost = Setting::get('mail_host');
            if ($mailHost) {
                $password = Setting::get('mail_password');
                $decryptedPassword = '';
                if ($password) {
                    try {
                        $decryptedPassword = Crypt::decryptString($password);
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
            }
        } catch (\Exception $e) {
            // fail silently during initial setup/migration
        }
        Blade::directive('uang', function ($expression) {
            return "<?php echo e(App\Models\Setting::get('currency', 'Rp') . ' ' . number_format((float)($expression), 0, ',', '.')); ?>";
        });
        Blade::directive('bulan', function ($expression) {
            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            return "<?php \$d = new DateTime($expression); echo \$months[(int)\$d->format('n') - 1] . ' ' . \$d->format('Y'); ?>";
        });
    }
}
