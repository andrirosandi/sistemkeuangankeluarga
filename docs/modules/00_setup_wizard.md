# Module: Setup Wizard

## Deskripsi
Halaman setup awal yang muncul saat pertama kali aplikasi dijalankan (belum ada user di database). Setelah selesai, halaman ini tidak bisa diakses lagi.

## Pages

### 1. Step 1 — Buat Admin Pertama
- Form: name, email, password, confirm password.
- Otomatis assign role Admin dari database setelah insert.
- Menjadi parameter bahwa aplikasi siap digunakan (jika table users isi > 0).

### 2. Step 2 — Pengaturan Sistem (Aplikasi)
- Form input Currency (cth: "Rp" atau "IDR").
- Dropdown Timezone. Default: `Asia/Jakarta`.
- Field akan disimpan di tabel `settings`.

### 3. Step 3 — Mail SMTP (Opsional)
- Host, Port, Username, Password, Encryption.
- Bisa di-skip, diisi nanti via Settings.

## Aturan Bisnis
- Hanya bisa diakses jika **belum ada user** di database
- Jika sudah ada user, redirect ke login
- Setelah wizard selesai, otomatis login sebagai admin yang baru dibuat
- Data settings disimpan ke tabel `settings`
