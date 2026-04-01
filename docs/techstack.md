## Tech Stack

| Layer | Technology | Keterangan |
|---|---|---|
| Backend | Laravel 12 | Framework utama (sesuai ketentuan tugas) |
| Templating | Blade | Laravel Blade Templating Engine |
| Styling | Bootstrap 5 | Wajib sesuai ketentuan tugas |
| Interaktivitas | HTMX + Alpine.js | Pengganti Livewire (lebih ringan, tanpa WebSocket) |
| Database | MySQL | Sesuai ketentuan tugas |
| Auth | Laravel Breeze | Scaffolding authentication (session-based) |
| Authorization | Spatie Laravel Permission | Role & Permission (Admin / User) |
| File Upload | Spatie Laravel MediaLibrary | Upload bukti pengeluaran (gambar / PDF) |
| PDF Generator | barryvdh/laravel-dompdf | Generate laporan PDF (nilai tambah) |

> **Catatan Interaktivitas**: Tugas menyebut Livewire sebagai opsional. Proyek ini menggunakan **HTMX + Alpine.js** sebagai solusi interaktivitas yang lebih ringan dan kompatibel penuh dengan stack Blade + Bootstrap 5, tanpa perlu kompilasi JavaScript tambahan.

---

## Package

```bash
# Wajib
composer require laravel/breeze
php artisan breeze:install blade

# Authorization
composer require spatie/laravel-permission

# File Upload (auto-generate tabel `media` via migration)
composer require spatie/laravel-medialibrary

# PDF Generator
composer require barryvdh/laravel-dompdf
```

---

## Catatan Compatibility

| Package | Versi Target | PHP | Laravel |
|---|---|---|---|
| laravel/breeze | ^2.x | ^8.2 | 12.x |
| spatie/laravel-permission | ^6.x | ^8.1 | 12.x |
| spatie/laravel-medialibrary | ^11.x | ^8.2 | 12.x |
| barryvdh/laravel-dompdf | ^3.x | ^8.0 | 12.x |

> **Penting**: Spatie MediaLibrary akan membuat tabel `media` otomatis via migration-nya sendiri. **Jangan buat tabel `media` secara manual** di migration kustom.