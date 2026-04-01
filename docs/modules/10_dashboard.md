# Module: Dashboard (Halaman Utama)

## Deskripsi
Halaman yang pertama kali terbuka sesaat setelah user berhasil *login*. Tampilan (`View`) akan sangat berbeda secara konsep antara Admin (Suami) dan User (Istri/Anak), meskipun berada pada URL yang sama (`/dashboard`).

## Pages & UI

### 1. View Dashboard Admin (Suami)
- **Kartu Ringkasan (Cards):** Mengambil data dari tabel `balance` untuk bulan berjalan (`kini`).
  - Saldo Awal Bulan Transaksi
  - Total Uang Masuk (Bulan Ini)
  - Total Pengeluaran (Bulan Ini)
  - **Saldo Riil Utama (Ending Balance)**
- **Widget Alert Action:** Block peringatan berwarna merah/kuning jika ada pengajuan (dari tabel `request_header`) berstatus `requested` yang belum ditindaklanjuti. Diberi tombol shortcut menuju `Inbox Approval`.
- **Tabel Mini (Mutasi Terkini):** Menampilkan 5 sampai 10 baris terakhir dari `transaction_header` berstatus `completed`. Memberi tahu suami letak arus kas terakhir.

### 2. View Dashboard User (Istri/Anak)
- **Ringkasan Aktivitas Pribadi:**
  - Total Nominal Pengajuan Bulan Ini (yang sudah diapprove/realized).
  - Info singkat pengajuan terakhirnya (Misal: "Pengajuan Belanja Dapur - *Menunggu*").
- **Widget Shortcut:** Tombol besar "Buat Pengajuan Baru".
- **Tabel Mini:** 5 List pengajuan terakhir miliknya beserta statusnya tanpa harus masuk ke menu List Request.

## Aturan Bisnis
- Perhitungan saldo di dashboard Admin **TIDAK MENGGUNAKAN** query `SUM()` berat pada runtime. Melainkan 100% membaca field yang sudah ada di 1 baris tabel `balance` pada bulan berjalan. Konsep ini menjamin Dashboard di-load sangat cepat (< 50ms).
- Istri/Anak **DILARANG KERAS** melihat panel Saldo Utama Suami, mereka hanya fokus melihat laporan pengajuan pribadinya.
