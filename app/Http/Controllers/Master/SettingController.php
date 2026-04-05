<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UpdateSettingRequest;
use App\Http\Requests\Master\VerifyOtpRequest;
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

            // Jika SMTP berubah, kirim OTP
            if ($smtpChanged) {
                $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                Session::put('smtp_verification_otp', $otp);
                Session::put('smtp_verification_expires', now()->addMinutes(10));

                // Coba kirim email
                try {
                    Mail::to($request->mail_from)->send(new SmtpTestMail($otp));

                    return redirect()->back()->with([
                        'success' => 'Pengaturan disimpan! Silakan cek email '.$request->mail_from.' untuk kode verifikasi.',
                        'show_otp_modal' => true,
                    ]);
                } catch (\Exception $e) {
                    // Jika gagal kirim, beri peringatan tapi data tetap tersimpan
                    return redirect()->back()->with([
                        'warning' => 'Pengaturan disimpan, tapi GAGAL mengirim email percobaan: '.$e->getMessage(),
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengaturan! '.$e->getMessage());
        }
    }

    /**
     * Verifikasi kode OTP SMTP.
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {

        $storedOtp = Session::get('smtp_verification_otp');
        $expiresAt = Session::get('smtp_verification_expires');

        if (! $storedOtp || now()->isAfter($expiresAt)) {
            return redirect()->back()->with([
                'error' => 'Kode verifikasi kedaluwarsa atau tidak ditemukan. Silakan simpan ulang pengaturan.',
                'show_otp_modal' => true,
            ]);
        }

        if ($request->otp === $storedOtp) {
            Session::forget(['smtp_verification_otp', 'smtp_verification_expires']);
            Setting::set('smtp_verified_at', now());

            return redirect()->route('settings.index')->with('success', 'Email berhasil diverifikasi! Sistem kini siap mengirim notifikasi.');
        }

        return redirect()->back()->with([
            'error' => 'Kode verifikasi salah! Silakan periksa kembali email Anda.',
            'show_otp_modal' => true,
        ]);
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
}
