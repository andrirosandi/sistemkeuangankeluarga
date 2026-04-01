# Module: Settings (Admin)

## Deskripsi
Halaman pengaturan aplikasi. Semua settings disimpan di tabel `settings` (key-value).

## Pages

### 1. Settings Page
Form grouped dalam 1 halaman:

**General**
- Currency (contoh: Rp)
- Timezone (dropdown, list dari API via backend)

**SMTP / Email**
- Host
- Port
- Username
- Password
- Encryption (SSL/TLS/None)
- Tombol Test Kirim Email (opsional)

## Aturan Bisnis
- Hanya Admin yang bisa akses
- Simpan ke tabel `settings` dengan key-value
- Timezone digunakan untuk display tanggal di seluruh aplikasi
- Currency hanya digunakan sebagai caption/label (tidak ada perhitungan rate)
