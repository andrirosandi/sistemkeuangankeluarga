# Pengerahan Aplikasi (Deployment) & CI/CD dengan Docker + GitHub Actions

Dokumentasi ini merangkum proses dan arsitektur untuk mendeploy "Sistem Keuangan Keluarga" ke peladen produksi (VPS) menggunakan Docker dan diotomatiskan dengan GitHub Actions.

## 1. Konsep Inti yang Dipelajari

- **Docker & Docker Compose**: Menjalankan aplikasi di dalam ruang (*container*) yang terisolasi. Ini memastikan lingkungan aplikasi sama persis antara komputer pengembang dan peladen sesungguhnya, menghindari masalah *"it works on my machine"*.
- **FrankenPHP (Web Server & PHP Engine)**: Server web modern yang memiliki performa sangat tangguh. Digunakan untuk menampung (`host`) kode aplikasi Laravel agar berjalan lebih optimal dibanding Apache biasa.
- **Reverse Proxy (Caddy)**: Pintu gerbang utama. Caddy yang di-*install* di host berfungsi menangkap semua arus masuk (web) dan memberikan sertifikat SSL otomatis `https://`, sebelum dilanjutkan ke peladen Docker.
- **CI/CD (Continuous Integration / Continuous Deployment)**: Otomatisasi integrasi. Ketika developer memperbarui kode, mesin tambahan (GitHub Actions) bekerja secara langsung tanpa campur tangan manusia untuk menerapkan pembaruan tersebut di *Production*.

## 2. Arsitektur & Pelaksanaan (*Workflow*)
*(Silakan lihat visualisasi pada file `01_Docker_Deploy_CICD.drawio`)*

1. **Commit & Push**: Developer merevisi kode dan mendorongnya (`push`) ke repositori GitHub pada *branch* `main`.
2. **Trigger (Eksekusi Otomatis)**: GitHub mendeteksi perubahan lalu memicu `deploy.yml`.
3. **Tunneling (Koneksi Aman)**: GitHub Runner membuka saluran terenkripsi SSH ke Server VPS menggunakan kredensial yang disematkan dalam **GitHub Secrets**.
4. **Pembaharuan Kode**: Di dalam peladen, perintah `git pull origin main` dijalankan.
5. **Rebuild Container**: Skrip menjalankan `docker compose down && docker compose up -d --build` untuk menciptakan ulang ruang aplikasi dengan paket dan kode termutakhir.
6. **Migrasi Database**: Secara otomatis mengeksekusi migrasi tabel via perintah `artisan migrate`.

## 3. Komando Utama (Daftar Perintah)

> **Peringatan**: Perintah-perintah ini digunakan secara mandiri *(manual)* jika Mas sedang meninjau peladen secara langsung, AI Agent dilarang mengeksekusinya tanpa suruhan eksplisit.

```bash
# === DOCKER ===
# Menjalankan kontainer beserta layanannya dari balik layar
docker compose up -d --build

# Menjatuhkan kontainer (Mematikannya)
docker compose down

# Meninjau status kontainer yang sedang hidup
docker ps

# === KUBECTL / ARTISAN EXEC (Via Docker) ===
# Memaksa agar perpindahan versi kerangka penyimpanan MySQL dilakukan
docker exec -it skk_app php artisan migrate --force

# Mengoptimasi dan memurnikan memori Cache laravel
docker exec skk_app php artisan optimize:clear
```

## 4. Prasyarat Pertama Kali di VPS

Agar CI/CD tidak kebingungan saat pertama kali mengeksekusi otomatisasi, pengguna harus "menanam" pijakan pertama dalam peladennya:
```bash
mkdir -p /var/www/sistemkeuangankeluarga
cd /var/www/sistemkeuangankeluarga
git clone https://github.com/USERNAME/repo_sistemkeuangan.git .
cp .env.example .env
nano .env # (Isi kata sandi db_password dan rahasia lainnya)
```
