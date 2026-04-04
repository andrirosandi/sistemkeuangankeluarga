<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Blade;
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
        Blade::directive('uang', function ($expression) {
            return "<?php echo e(App\Models\Setting::get('currency', 'Rp') . ' ' . number_format((float)($expression), 0, ',', '.')); ?>";
        });
    }
}
