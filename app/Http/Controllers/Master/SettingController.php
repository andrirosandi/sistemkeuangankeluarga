<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UpdateSettingRequest;
use App\Mail\SmtpTestMail;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan sistem.
     */
    public function index()
    {
        $keys = [
            'app_name', 'timezone', 'currency', 'mail_host', 'mail_port', 
            'mail_username', 'mail_encryption', 'mail_from', 'mail_password'
        ];

        // Ambil semua data setting dalam 1 query saja (Optimasi)
        $settingsData = Setting::whereIn('key', $keys)->get()->pluck('value', 'key');

        $settings = [
            'app_name' => $settingsData['app_name'] ?? config('app.name'),
            'timezone' => $settingsData['timezone'] ?? config('app.timezone'),
            'currency' => $settingsData['currency'] ?? 'Rp',
            'mail_host' => $settingsData['mail_host'] ?? '',
            'mail_port' => $settingsData['mail_port'] ?? '',
            'mail_username' => $settingsData['mail_username'] ?? '',
            'mail_encryption' => $settingsData['mail_encryption'] ?? 'tls',
            'mail_from' => $settingsData['mail_from'] ?? '',
        ];

        // Dekripsi password SMTP jika ada
        $smtpPasswordEncrypted = $settingsData['mail_password'] ?? null;
        $settings['mail_password'] = '';
        if ($smtpPasswordEncrypted) {
            try {
                $settings['mail_password'] = Crypt::decryptString($smtpPasswordEncrypted);
            } catch (\Exception $e) {
                // Jika gagal dekripsi (misal key .env berubah), biarkan kosong
            }
        }

        // Get current logo and favicon media
        $logoMedia = Setting::where('key', 'app_logo')->first()?->getFirstMedia('app_logo');
        $faviconMedia = Setting::where('key', 'app_favicon')->first()?->getFirstMedia('app_favicon');

        return view('master.setting.index', compact('settings', 'logoMedia', 'faviconMedia'));
    }

    /**
     * Update pengaturan sistem secara massal.
     */
    public function update(UpdateSettingRequest $request)
    {
        $validated = $request->validated();

        try {
            $smtpFields = ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from'];
            $smtpChanged = false;

            foreach ($validated as $key => $value) {
                if (in_array($key, ['logo_media_id', 'favicon_media_id'])) {
                    continue;
                }

                $oldValue = Setting::get($key);

                if (in_array($key, $smtpFields) && $value != $oldValue) {
                    $smtpChanged = true;
                }

                if ($key === 'mail_password' && ! empty($value)) {
                    // Encrypt SMTP password (2-way)
                    Setting::set($key, Crypt::encryptString($value));
                } else {
                    Setting::set($key, $value);
                }
            }

            // Handle Logo & Favicon via MediaLibrary
            if ($request->logo_media_id) {
                $this->attachSettingsMedia($request->logo_media_id, 'app_logo');
            }
            if ($request->favicon_media_id) {
                $this->attachSettingsMedia($request->favicon_media_id, 'app_favicon');
            }

            // Jika SMTP berubah, reset verifikasi
            if ($smtpChanged) {
                Setting::set('smtp_verified_at', null);
            }

            return redirect()->back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengaturan! '.$e->getMessage());
        }
    }

    /**
     * Kirim kode OTP untuk verifikasi SMTP (AJAX).
     */
    public function sendOtp(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'mail_host' => 'required',
            'mail_port' => 'required',
            'mail_username' => 'required',
            'mail_password' => 'required',
            'mail_encryption' => 'required',
            'mail_from' => 'required|email',
        ]);

        try {
            // Set config sementara untuk kirim email
            config([
                'mail.mailers.smtp.host' => $request->mail_host,
                'mail.mailers.smtp.port' => $request->mail_port,
                'mail.mailers.smtp.username' => $request->mail_username,
                'mail.mailers.smtp.password' => $request->mail_password,
                'mail.mailers.smtp.encryption' => $request->mail_encryption,
                'mail.from.address' => $request->mail_from,
            ]);

            // Generate dan simpan OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            Session::put('smtp_verification_otp', $otp);
            Session::put('smtp_verification_expires', now()->addMinutes(10));

            Mail::to($request->mail_from)->send(new SmtpTestMail($otp));

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP berhasil dikirim ke ' . $request->mail_from,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verifikasi kode OTP SMTP (AJAX).
     */
    public function verifyOtp(\Illuminate\Http\Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $storedOtp = Session::get('smtp_verification_otp');
        $expiresAt = Session::get('smtp_verification_expires');

        if (! $storedOtp || now()->isAfter($expiresAt)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode verifikasi kedaluwarsa atau tidak ditemukan. Silakan kirim ulang kode OTP.',
            ], 422);
        }

        if ($request->otp === $storedOtp) {
            Session::forget(['smtp_verification_otp', 'smtp_verification_expires']);
            Setting::set('smtp_verified_at', now());

            return response()->json([
                'success' => true,
                'message' => 'Email berhasil diverifikasi! Sistem kini siap mengirim notifikasi.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode verifikasi salah! Silakan periksa kembali email Anda.',
        ], 422);
    }

    /**
     * Helper to attach media from temporary model to setting model
     */
    private function attachSettingsMedia($mediaId, $settingKey)
    {
        $setting = Setting::where('key', $settingKey)->first();
        if (! $setting) {
            $setting = Setting::create(['key' => $settingKey, 'value' => '']);
        }

        $media = Media::find($mediaId);
        if ($media) {
            // Delete old media in the same collection
            $setting->clearMediaCollection($settingKey);

            // Move from TemporaryMedia to Setting model
            $newMedia = $media->move($setting, $settingKey);

            // Update the setting value with the media filename
            $setting->update(['value' => $newMedia->file_name]);
        }
    }

    /**
     * Reset aplikasi ke kondisi awal (hapus semua data & user).
     * Dibuatkan khusus untuk interview.
     * Setelah reset, akan redirect ke /setup untuk setup ulang.
     */
    public function reset()
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        try {
            auth()->logout();
            Session::flush();

            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
            
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            return redirect()->route('setup.index')->with('info', 'Aplikasi telah direset. Silakan lakukan setup ulang.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mereset aplikasi! ' . $e->getMessage());
        }
    }
}
