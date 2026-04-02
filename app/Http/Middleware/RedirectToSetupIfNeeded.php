<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware global.
 * Jika belum ada user di database (fresh install), paksa redirect ke /setup.
 */
class RedirectToSetupIfNeeded
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jangan intercept asset Vite, setup, dan auth routes
        $excluded = [
            'setup',
            'login',
            'logout',
            'register',
            'password',
            'forgot-password',
            '_debugbar',
        ];

        foreach ($excluded as $path) {
            if ($request->is($path) || $request->is($path . '/*')) {
                return $next($request);
            }
        }

        // Cek apakah setup sudah selesai via tabel Setting
        // Jika tabel belum ada (fresh migration) atau flag setup_completed belum true
        try {
            $isSetupDone = \Illuminate\Support\Facades\Schema::hasTable('settings') 
                           && \App\Models\Setting::get('setup_completed') === '1';
            
            if (!$isSetupDone) {
                return redirect()->to('/setup');
            }
        } catch (\Exception $e) {
            // Jika ada error DB (tabel belum ada), arahkan ke setup
            return redirect()->to('/setup');
        }

        return $next($request);
    }
}
