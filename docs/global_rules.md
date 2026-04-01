# Aturan & Standar Global (Global Guidelines)

Dokumen ini berisi aturan main (standar) penulisan kode, penanganan file, dan standar sistem secara keseluruhan untuk proyek **Sistem Keuangan Keluarga**. Seluruh proses pengkodingan wajib berpedoman pada aturan ini.

## 1. Standar Penanganan Upload (File & Media)
- **Alur Upload (Dedicated API):** 
  1. Frontend (Alpine.js dll) melakukan kompresi (resize gambar dll) via JavaScript sebelum mengunggah.
  2. Gambar dikirim murni secara AJAX ke endpoint API Upload Khusus *sebelum* form utama (Request Form) di-submit penuh.
  3. API menerima dan menyimpan gambar sebagai status sementara (*temporary*), lalu mengembalikan `media_id` dari Spatie MediaLibrary.
  4. Form utama saat di-submit hanya membawa array ID tersebut (contoh: `<input type="hidden" name="media_ids[]" value="10">`).
  5. Backend memproses data utama, dapet ID transaksi, lalu meng-attach *temporary media_id* tersebut ke Transaksi yang sah.
- **UX Proteksi Submit:** Selama proses kompresi dan *background upload API* berlangsung, tombol *Submit/Ajukan* utama form WAJIB di-*disabled* agar user tidak terlanjur ngirim data kosong/tidak komplit.
- **Penanganan Orphaned File:** Tidak perlu sibuk memikirkan Job Scheduler untuk menghapus foto "tak bertuan" (batal disubmit). Biarkan menumpuk dahulu dan anggap *expired* secara nalar setelah 1 hari. Fokus pada flow fungsional PRD.
- **Library Utama:** Wajib menggunakan **Spatie Laravel MediaLibrary**. Tidak diperkenankan membuat kolom manual `foto`/`image` di tabel transaksi.

## 2. Standar Penulisan & Database (Naming Convention)
- **Database Tables & Columns:** Selalu gunakan format *snake_case* (`request_header`, `transaction_date`).
- **Data Uang (Currency):** Semua nilai uang disimpan dalam struktur *schema* `DECIMAL(15,4)`. 
- **PHP Classes & Models:** Selalu gunakan format *PascalCase* (contoh: `RequestHeader`, `TransactionDetail`).
- **Methods & Variables:** Menggunakan *camelCase* (`calculateTotal()`, `$requestData`).
- **Relationship:** Penamaan relasi method di Laravel Model harus deskriptif. (Misal `public function details()` pada RequestHeader untuk memanggil RequestDetail).

## 3. UI / UX & Frontend
- **Interaksi Dynamic (Form Master-Detail, Modal, Filter):** Segala interaksi DOM tanpa reload seperti penambahan *dynamic row* item di tabel wajin menggunakan **HTMX** atau **Alpine.js**. Hal ini untuk menjaga kode tetap *lightweight* tanpa perlu setup Vue/React.
- **Styling:** Menggunakan **Bootstrap 5** (Sesuai tech-stack PRD) yang di-*bundle* via Laravel Vite. 
- **Format Tampilan Uang:** Saat di tampilan Blade, nominal dikaver dengan fungsi helper format atau number_format agar mudah dibaca pengguna (misal dari `100000.0000` tampil `100.000`). Simbol mata uang selalu merujuk dari konfigurasi di tabel `settings`.

## 4. Keamanan, Role & Intergritas Data
- **Rate Limiting:** Terapkan pembatasan *Request/Rate Limiting* (fitur bawaan Laravel `ThrottleRequests`) pada rute krusial seperti endpoint API Upload Gambar (mencegah spam *storage*) dan aksi Submit Pengajuan/Transaksi (mencegah interaksi berlebih lintas detik yang tidak masuk akal).
- **Validasi Input (Mutlak):** SELALU validasi seluruh *request input* pengunjung via Controller Validation atau Form Request Laravel. Dilarang keras melakukan proses simpan/update ke database tanpa di-filter untuk menghindari celah keamanan (XSS, SQL Injection, *Mass Assignment*).
- **Database Concurrency Lock (Mencegah Double Input Utama):** Seluruh aksi mutasi (INSERT/UPDATE) pada tabel krusial seperti `request_header`, `request_detail`, `transaction_header`, `transaction_detail`, dan `balance` WAJIB menggunakan `DB::transaction()`. Penggunaan fungsi *Pessimistic Locking* Laravel (`lockForUpdate()` atau implementasi sejenis) harus diutamakan guna menghindari insiden dobel potong saldo ketike internet tidak stabil/tekan submit 2x beruntun.
- **Hak Akses Halaman:** Dibatasi ketat dengan _Middleware_ dari **Spatie Laravel Permission** (`role:Admin|User`).
- **Pengecekan Kepemilikan (Ownership):** User hanya bisa membuka Request yang miliknya sendiri (`created_by === auth()->id()`). Admin kebal dari aturan ini dan bisa melihat semua data lintas-user.
