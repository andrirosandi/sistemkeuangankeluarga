## Tech Stack

| Layer | Technology | Keterangan |
|---|---|---|
| Backend | Laravel 12 | Framework utama (sesuai ketentuan tugas) |
| Templating | Blade | Laravel Blade Templating Engine |
| UI Template | **Tabler** (Bootstrap 5-based) | Admin template modern, open-source, kompatibel Bootstrap 5 |
| Styling | Bootstrap 5 | Bundled via Tabler, di-build dengan Laravel Vite |
| Interaktivitas | HTMX + Alpine.js | Pengganti Livewire (lebih ringan, tanpa WebSocket) |
| Database | MySQL | Sesuai ketentuan tugas |
| Auth | Laravel Breeze | Scaffolding authentication (session-based). Register publik dinonaktifkan. |
| Authorization | Spatie Laravel Permission | Role & Permission (Admin / User) |
| Balance Engine | Laravel Observer + BalanceService | Hitung ulang saldo otomatis saat transaksi berubah. Dibungkus `DB::transaction()`. |
| File Upload | Spatie Laravel MediaLibrary | Upload bukti pengeluaran (gambar / PDF) |
| PDF Generator | barryvdh/laravel-dompdf | Generate laporan PDF (nilai tambah) |
| Charts | ApexCharts (via CDN) | Visualisasi laporan, diinisiasi via Alpine.js |

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

# Excel Export
composer require maatwebsite/excel
```

> **Tabler UI Template:** Diintegrasikan secara manual.
> Download dari https://github.com/tabler/tabler → copy aset ke `public/vendor/tabler/`.
> Layout Tabler digunakan untuk **Admin Layout** saja (`resources/views/layouts/app.blade.php`).
> Auth views Breeze (`layouts/guest.blade.php`) **tidak disentuh**.

---

## Catatan Compatibility

| Package | Versi Target | PHP | Laravel |
|---|---|---|---|
| laravel/breeze | ^2.x | ^8.2 | 12.x |
| spatie/laravel-permission | ^6.x | ^8.1 | 12.x |
| spatie/laravel-medialibrary | ^11.x | ^8.2 | 12.x |
| barryvdh/laravel-dompdf | ^3.x | ^8.0 | 12.x |
| maatwebsite/excel | ^3.1 | ^8.0 | 12.x |

> **Penting**: Spatie MediaLibrary akan membuat tabel `media` otomatis via migration-nya sendiri. **Jangan buat tabel `media` secara manual** di migration kustom.