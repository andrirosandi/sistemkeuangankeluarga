<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk route /setup.
 * Jika sudah ada user di database, redirect ke login (setup sudah selesai).
 */
class RedirectIfSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        // Sejauh wizard session masih ada, izinkan akses ke /setup
        if (session()->has('setup_step')) {
            return $next($request);
        }

        // Cek apakah setup sudah benar-benar selesai
        try {
            $isSetupDone = \Illuminate\Support\Facades\Schema::hasTable('settings') 
                           && \App\Models\Setting::get('setup_completed') === '1';

            if ($isSetupDone) {
                return redirect()->route('login');
            }
        } catch (\Exception $e) {
            // Abaikan error DB
        }

        return $next($request);
    }
}
