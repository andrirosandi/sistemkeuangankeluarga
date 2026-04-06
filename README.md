# Sistem Keuangan Keluarga

Sistem manajemen keuangan keluarga berbasis web yang memudahkan pencatatan, pengajuan, approval, realisasi, dan pelaporan keuangan rumah tangga.

![Laravel](https://img.shields.io/badge/Laravel-12-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## Fitur Utama

### Dashboard
- 📊 Ringkasan keuangan: total pemasukan & pengeluaran
- 💰 Saldo akhir per bulan
- 📈 Widget interaktif untuk quick view
- 📱 Tampilan responsif (mobile-friendly)

### Pengajuan Dana (Kas Masuk & Keluar)
- ✍️ Form pengajuan dengan rincian item
- 📎 Upload bukti/bukti (gambar/PDF)
- 👤 Approval workflow dari Admin
- 📝 Status tracking: draft, pending, approved, rejected
- 💬 Catatan tambahan & prioritas

### Realisasi Transaksi
- ✅ Realisasi dari pengajuan yang disetujui
- 📤 Automatic balance update saat completed
- ✅ Pembatalan realisasi (restore saldo)
- 📎 Upload bukti transaksi

### Approval System
- ✅ Admin melihat semua pengajuan masuk
- 👍 Approve/Reject dengan alasan
- 📧 Edit pengajuan jika ada kesalahan
- 📢 Notifikasi real-time ke pengaju

### Master Data
- 📂 **Kategori**: Daftar kategori pemasukan/pengeluaran
- 👥 **Template**: Template pengajuan berulang
- 👤 **Role Management**: Atur hak akses Admin/User
- 👥 **User Management**: Tambah/Edit user keluarga

### Laporan
- 📊 Laporan bulanan (kas masuk/keluar)
- 📈 Laporan kategori
- 👨 Laporan per anggota keluarga
- 📄 Export Excel (via Maatwebsite Excel)
- 🖨️ Generate PDF (via barryvdh/laravel-dompdf)

### Notifikasi
- 🔔 Notifikasi in-app untuk pengajuan & approval
- 📧 Notifikasi email (opsional)

## Teknologi

### Backend
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: MySQL 8.0
- **Authentication**: Laravel Breeze
- **Authorization**: Spatie Laravel Permission
- **File Upload**: Spatie MediaLibrary
- **PDF**: barryvdh/laravel-dompdf
- **Excel**: Maatwebsite/Laravel-Excel

### Frontend
- **UI Framework**: Bootstrap 5 (Tabler Admin Template)
- **JavaScript**: Alpine.js + HTMX
- **Icons**: Tabler Icons
- **Charts**: ApexCharts

## Persyaratan Sistem

- PHP 8.2 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Composer 2.x
- Node.js 18+ (untuk development)
- Nginx/Apache (untuk production)

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/andrirosandi/sistemkeuangankeluarga.git
cd sistemkeuangankeluarga
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install npm dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database connection in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=sistem_keuangan_keluarga
# DB_USERNAME=your_username
# DB_PASSWORD=your_password
```

### 4. Run Migrations

```bash
# Buat tabel database
php artisan migrate --force

# Seed data awal (roles, permissions, kategori)
php artisan db:seed
```

### 5. Link Storage (Opsional)

```bash
# Untuk file uploads
php artisan storage:link
```

### 6. Build Assets

```bash
# Build CSS & JS untuk production
npm run build
```

### 7. Jalankan Aplikasi

```bash
# Development server
composer run dev

# Atau jalankan terpisah:
php artisan serve           # Start Laravel (port 8000)
php artisan queue:listen    # Process background jobs
npm run dev                 # Start Vite dev server (HMR)
```

## Pengaturan Awal (Setup Wizard)

Aplikasi memiliki **Setup Wizard** di `/setup` untuk membuat admin user pertama:

1. Buka aplikasi di browser
2. Jika belum ada user, akan otomatis redirect ke `/setup`
3. Isi form setup:
   - Nama Admin
   - Email
   - Password
   - Konfirmasi Password
4. Klik "Buat Admin"
5. Login dengan akun admin yang baru dibuat

Setelah ada user, halaman setup akan otomatis dinonaktifkan.

## Struktur Role & Permission

### Role Default

| Role | Deskripsi |
|------|-----------|
| **Admin** | Akses penuh: melihat semua data, approval, master data |
| **User** | Hanya data sendiri: pengajuan, realisasi, saldo |

### Permissions

33 permission granular (dot-notation) untuk kontrol akses fitur:
- `request.view_all` / `request.view_own`
- `request.create` / `request.edit` / `request.delete`
- `transaction.view_all` / `transaction.view_own`
- `request.approve` / `request.reject`
- Dan lainnya...

## Panduan Penggunaan

### Untuk Admin

1. **Dashboard**: Monitor overview keuangan keluarga
2. **Approval**: Review pengajuan dana yang masuk
3. **Master Data**: Atur kategori & template
4. **User Management**: Kelola user keluarga
5. **Laporan**: Generate & download laporan

### Untuk User

1. **Dashboard**: Lihat saldo & overview
2. **Buat Pengajuan**:
   - Pilih jenis: Kas Masuk atau Kas Keluar
   - Isi info utama (kategori, tanggal, deskripsi)
   - Tambah rincian item (nama & nominal)
   - Upload bukti (opsional)
   - Simpan draft atau kirim langsung
3. **Lihat Status**: Pantau status pengajuan (pending/approved/rejected)
4. **Realisasi**: Realisasikan pengajuan yang disetujui
5. **Laporan**: Lihat laporan keuangan

## Struktur Database

Tabel utama:
- `users` - Data pengguna
- `roles` - Role sistem
- `permissions` - Permission detail
- `model_has_roles` - User-Role mapping
- `role_has_permissions` - Role-Permission mapping
- `categories` - Kategori transaksi
- `request_header` - Header pengajuan
- `request_detail` - Detail item pengajuan
- `transaction_header` - Header realisasi
- `transaction_detail` - Detail item realisasi
- `balance` - Saldo bulanan
- `media` - File uploads (Spatie MediaLibrary)
- `notifications` - Notifikasi
- `settings` - Pengaturan aplikasi

## Development

### Jalankan Environment Development

```bash
# Server Laravel + Queue + Vite dev server
composer run dev
```

### Testing

```bash
# Jalankan semua test
vendor/bin/phpunit

# Jalankan test spesifik
vendor/bin/phpunit --filter RequestTest
```

### Coding Standards

- **PSR-12**: PHP coding standard
- **Frontend**: Gunakan Tabler Icons (bukan inline SVG)
- **Alpine.js**: Gunakan pattern global data untuk form (lihat `CLAUDE.md`)
- **Validation**: Selalu gunakan Form Request classes
- **Naming**:
  - Database: `snake_case`
  - PHP Classes/Models: `PascalCase`
  - Methods/Variables: `camelCase`

## Deployment

### Via Docker (Production)

```bash
# Build dan jalankan dengan Docker
docker compose up -d

# Untuk berhenti
docker compose down
```

### Via Server

1. Upload file ke server
2. Jalankan `composer install --no-dev`
3. Jalankan `php artisan key:generate`
4. Setup `.env` untuk production
5. Jalankan `php artisan migrate --force`
6. Jalankan `php artisan db:seed`
7. Jalankan `php artisan storage:link`
8. Jalankan `php artisan config:cache:clear`
9. Build assets: `npm run build`
10. Configure web server (Nginx/Apache)

## Alur Workflow Pengajuan

```
User → Buat Pengajuan (draft)
    ↓
Admin → Review (pending)
    ↓
Admin → Approve → Status: approved
    ↓
User → Realisasi → Status: completed
    ↓
Balance → Saldo otomatis update
```

## Dokumentasi Lengkap

Dokumentasi teknis lebih detail tersedia di folder `docs/`:

- `docs/global_rules.md` - Aturan pengembangan
- `docs/techstack.md` - Teknologi yang digunakan
- `docs/tugas.md` - Spesifikasi tugas awal
- `docs/sidebar_menu.yml` - Struktur menu sidebar
- `docs/modules/` - Dokumentasi modul spesifik
- `CLAUDE.md` - Panduan untuk Claude Code

## License

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## Kontak

- **Developer**: Andri Rosandi
- **Email**: me@andrirosandi.my.id

