# Module: Auth

## Deskripsi
Autentikasi menggunakan Laravel Breeze (session-based). Register publik dinonaktifkan — hanya Admin yang bisa mendaftarkan user baru via User Management.

## Pages

### 1. Setup Wizard (First-Time Only)
- Muncul saat pertama kali aplikasi dijalankan (belum ada user di database)
- Form: name, email, password → otomatis jadi Admin pertama
- Setelah selesai, halaman ini tidak bisa diakses lagi

### 2. Login
- Email + Password
- Remember me
- Link forgot password
- User nonaktif tidak bisa login

### 3. Forgot Password
- Input email → kirim link reset via email (butuh SMTP di settings)

### 4. Reset Password
- Set password baru dari link email

### 5. Profile
- User bisa edit: email, password
- User TIDAK bisa edit: name (hanya admin yang bisa ubah nama)

## Aturan Bisnis
- Halaman register publik **dinonaktifkan**
- Admin mendaftarkan user baru via module User Management
- Setup wizard hanya muncul 1x saat belum ada user
- Setelah login, redirect ke Dashboard
- Role dicek via Spatie Permission (Admin / User)
