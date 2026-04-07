<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setup\StoreSetupAdminRequest;
use App\Http\Requests\Setup\StoreSetupSettingsRequest;
use App\Http\Requests\Setup\StoreSetupMailRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;

class SetupController extends Controller
{
    /**
     * Tampilkan halaman setup wizard.
     * Step aktif diambil dari session.
     */
    public function index()
    {
        // 1. Auto-Migrate & Seed jika tabel dasar belum ada
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
            
            // Cek jika permissions belum di-seed (cek tabel roles)
            if (\Illuminate\Support\Facades\Schema::hasTable('roles') && \Spatie\Permission\Models\Role::count() === 0) {
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Exception $e) {
            // Jika ada error (misal koneksi DB belum siap), biarkan user melihat pesan error Laravel yang standar
            // atau beri notifikasi di view.
        }

        // 2. Tampilkan halaman setup wizard
        $currentStep = session('setup_step', 1);
        return view('setup.index', compact('currentStep'));
    }

    /**
     * Step 1: Simpan Admin pertama & assign role admin.
     */
    public function storeAdmin(StoreSetupAdminRequest $request)
    {

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign role admin (sudah dibuat oleh RolePermissionSeeder)
        $user->assignRole('admin');

        // Simpan user ID ke session untuk langkah berikutnya
        session(['setup_admin_id' => $user->id, 'setup_step' => 2]);

        return redirect()->route('setup.index');
    }

    /**
     * Step 2: Simpan pengaturan aplikasi (currency & timezone).
     */
    public function storeSettings(StoreSetupSettingsRequest $request)
    {

        Setting::set('currency', $request->currency);
        Setting::set('app_name', config('app.name'));

        session(['setup_step' => 3]);

        return redirect()->route('setup.index');
    }

    /**
     * Step 3: Simpan konfigurasi SMTP (atau skip).
     */
    public function storeMail(StoreSetupMailRequest $request)
    {
        if (!$request->boolean('skip')) {

            Setting::set('mail_host',       $request->mail_host);
            Setting::set('mail_port',       $request->mail_port);
            Setting::set('mail_username',   $request->mail_username);
            Setting::set('mail_password',   Crypt::encryptString($request->mail_password));
            Setting::set('mail_encryption', $request->mail_encryption);
            Setting::set('mail_from',       $request->mail_from);
        }

        // Tandai setup sudah selesai
        Setting::set('setup_completed', '1');

        // Auto-login user admin yang baru dibuat
        $adminId = session('setup_admin_id');
        if ($adminId) {
            Auth::loginUsingId($adminId);
        }

        // Bersihkan session wizard
        session()->forget(['setup_step', 'setup_admin_id']);

        return redirect()->route('dashboard')->with('success', 'Setup selesai! Selamat datang di Sistem Keuangan Keluarga.');
    }
}
