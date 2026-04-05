# Operasi Database & Artisan di Lingkungan Docker

Dokumentasi ini menjelaskan cara berinteraksi dengan basis data (MySQL) dan menjalankan perintah Laravel Artisan saat aplikasi sudah berjalan di dalam kontainer Docker pada peladen (VPS).

## 1. Konsep Pemanggilan Perintah
Karena aplikasi Laravel Mas berada di dalam perisai kontainer, perintah standar `php artisan` tidak bisa dijalankan langsung dari terminal VPS. Kita harus menggunakan "jembatan" bernama `docker exec`.

**Logika Alurnya:**
`Mas (Terminal VPS)` -> `Docker Engine` -> `Kontainer (skk_app)` -> `PHP Artisan`

## 2. Perintah Database yang Sering Digunakan

### A. Database Seeding (Mengisi Data Awal/Testing)
Gunakan ini untuk memasukkan data yang sudah didefinisikan di folder `database/seeders`.

```bash
# Menjalankan semua seeder utama (DatabaseSeeder)
docker exec -it skk_app php artisan db:seed

# Menjalankan kelas seeder tertentu saja
docker exec -it skk_app php artisan db:seed --class=NamaKelasSeeder
```

### B. Database Migration (Memperbarui Struktur Tabel)
Jika Mas menambah kolom atau tabel baru di kode program:

```bash
# Menjalankan migrasi yang belum dieksekusi
docker exec -it skk_app php artisan migrate

# Jika di server produksi, tambahkan flag --force agar tidak bertanya konfirmasi
docker exec -it skk_app php artisan migrate --force

# Melihat status migrasi (mana yang sudah, mana yang belum)
docker exec -it skk_app php artisan migrate:status
```

### C. Pembatalan Migrasi (Rollback)
**Hati-hati!** Ini akan menghapus data pada tabel yang di-*rollback*.

```bash
# Membatalkan 1 langkah migrasi terakhir
docker exec -it skk_app php artisan migrate:rollback
```

## 3. Perintah Artisan Pendukung Lainnya

| Tujuan | Perintah Docker |
| :--- | :--- |
| **Membersihkan Cache** | `docker exec skk_app php artisan optimize:clear` |
| **Masuk ke Mode Shell** | `docker exec -it skk_app sh` (untuk masuk ke dalam terminal kontainer) |
| **Tinker (REPL PHP)** | `docker exec -it skk_app php artisan tinker` |
| **Daftar Route** | `docker exec skk_app php artisan route:list` |

## 4. Tips Keamanan di Server
1.  **Gunakan `-it` (Interactive TTY)**: Selalu gunakan ini jika perintah membutuhkan masukan (input) dari Mas atau memberikan output teks yang panjang.
2.  **Container Name**: Pastikan nama kontainer benar. Mas bisa mengeceknya dengan `docker ps`. Dalam konfigurasi kita, namanya adalah `skk_app`.
3.  **Jangan di-Root**: Pastikan Mas menjalankan ini sebagai user yang punya izin docker (biasanya user standar yang masuk grup docker).
