<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Mail\SmtpTestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan sistem.
     */
    public function index()
    {
        $settings = [
            'app_name'        => Setting::get('app_name', config('app.name')),
            'timezone'        => Setting::get('timezone', config('app.timezone')),
            'currency'        => Setting::get('currency', 'Rp'),
            'mail_host'       => Setting::get('mail_host'),
            'mail_port'       => Setting::get('mail_port'),
            'mail_username'   => Setting::get('mail_username'),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from'       => Setting::get('mail_from'),
        ];

        // Dekripsi password SMTP jika ada
        $smtpPasswordEncrypted = Setting::get('mail_password');
        $settings['mail_password'] = '';
        if ($smtpPasswordEncrypted) {
            try {
                $settings['mail_password'] = Crypt::decryptString($smtpPasswordEncrypted);
            } catch (\Exception $e) {
                // Jika gagal dekripsi (misal key .env berubah), biarkan kosong
            }
        }

        return view('master.setting.index', compact('settings'));
    }

    /**
     * Update pengaturan sistem secara massal.
     */
    public function update(Request $request)
    {
        $rules = [
            'app_name'        => 'required|string|max:255',
            'logo_media_id'   => 'nullable|integer',
            'favicon_media_id'=> 'nullable|integer',
            'timezone'        => 'required|string',
            'currency'        => 'required|string|max:10',
            'mail_host'       => 'nullable|string',
            'mail_port'       => 'nullable|numeric',
            'mail_username'   => 'nullable|string',
            'mail_password'   => 'nullable|string',
            'mail_encryption' => 'nullable|in:ssl,tls',
            'mail_from'       => 'nullable|email',
        ];

        $validated = $request->validate($rules);

        try {
            $smtpFields = ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from'];
            $smtpChanged = false;

            foreach ($validated as $key => $value) {
                if (in_array($key, ['logo_media_id', 'favicon_media_id'])) continue;
                
                $oldValue = Setting::get($key);
                
                if (in_array($key, $smtpFields) && $value != $oldValue) {
                    $smtpChanged = true;
                }

                if ($key === 'mail_password' && !empty($value)) {
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
                        'success' => 'Pengaturan disimpan! Silakan cek email ' . $request->mail_from . ' untuk kode verifikasi.',
                        'show_otp_modal' => true
                    ]);
                } catch (\Exception $e) {
                    // Jika gagal kirim, beri peringatan tapi data tetap tersimpan
                    return redirect()->back()->with([
                        'warning' => 'Pengaturan disimpan, tapi GAGAL mengirim email percobaan: ' . $e->getMessage(),
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengaturan! ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi kode OTP SMTP.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $storedOtp = Session::get('smtp_verification_otp');
        $expiresAt = Session::get('smtp_verification_expires');

        if (!$storedOtp || now()->isAfter($expiresAt)) {
            return redirect()->back()->with([
                'error' => 'Kode verifikasi kedaluwarsa atau tidak ditemukan. Silakan simpan ulang pengaturan.',
                'show_otp_modal' => true
            ]);
        }

        if ($request->otp === $storedOtp) {
            Session::forget(['smtp_verification_otp', 'smtp_verification_expires']);
            Setting::set('smtp_verified_at', now());
            
            return redirect()->route('settings.index')->with('success', 'Email berhasil diverifikasi! Sistem kini siap mengirim notifikasi.');
        }

        return redirect()->back()->with([
            'error' => 'Kode verifikasi salah! Silakan periksa kembali email Anda.',
            'show_otp_modal' => true
        ]);
    }
    /**
     * Helper to attach media from temporary model to setting model
     */
    private function attachSettingsMedia($mediaId, $settingKey)
    {
        $setting = Setting::where('key', $settingKey)->first();
        if (!$setting) {
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
