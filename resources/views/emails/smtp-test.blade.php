<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode Verifikasi Pengaturan Email</title>
    <style>
        body { font-family: sans-serif; line-height: 1.5; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px; }
        .header { text-align: center; margin-bottom: 30px; }
        .otp { display: block; font-size: 32px; font-weight: bold; text-align: center; color: #206bc4; margin: 20px 0; letter-spacing: 1px; }
        .footer { font-size: 12px; color: #999; margin-top: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Konfigurasi Email Berhasil Terhubung!</h2>
        </div>
        <p>Halo, Admin!</p>
        <p>Anda menerima email ini karena Anda baru saja memperbarui pengaturan SMTP pada sistem <strong>{{ config('app.name') }}</strong>.</p>
        <p>Gunakan kode verifikasi berikut untuk mengonfirmasi bahwa email ini benar-benar bisa digunakan:</p>
        
        <span class="otp">{{ $otp }}</span>
        
        <p>Masukkan kode ini pada halaman pengaturan sistem untuk menyelesaikan proses verifikasi.</p>
        <p>Abaikan email ini jika Anda tidak merasa memperbarui pengaturan sistem.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Dikirim otomatis oleh sistem.
        </div>
    </div>
</body>
</html>
