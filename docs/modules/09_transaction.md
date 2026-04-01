# Module: Transaction & Realisasi Kas (Admin)

## Deskripsi
Modul ini adalah jantung finansial aplikasi yang mencatat **Mutasi Aktual (Uang Keluar/Masuk Riil)**. User biasa (Istri/Anak) diizinkan untuk melihat secara *Read-Only* khusus untuk daftar mutasi yang berasal dari pengajuan/request miliknya. Namun aksi tambah/ubah/eksekusi 100% adalah hak mutlak Admin. Semua data di sini harus masuk melalui dua pintu utama:
1. **Dari Request Approval:** Ketika Admin menyetujui (`approve`) pengajuan dan mengeksekusinya.
2. **Dari Penggunaan Template (Shortcut):** Ketika Admin mengaktifkan pembayaran berulang/rutin via Master Template.

## Pages & UI

### 1. List Transaksi (Buku Mutasi Kas)
- Menampilkan seluruh `transaction_header`.
- **Informasi:** Terdapat tag visual membedakan transaksi yang lahir dari *Request Istri/Anak* (menampilkan link `request_id`) dengan transaksi yang murni *Direct Input/Template* (`request_id = NULL`).
- **Search & Filter:** 
  - Rentang Tanggal (`transaction_date`).
  - Tipe Pemasukan/Pengeluaran (`trans_code`).
  - Status Pekerjaan (`draft` atau `completed`).
- **Data Balance Singkat (Widget):** Di atas tabel, tampilkan angka `Saldo Awal`, `Total In`, `Total Out`, `Saldo Akhir` dari bulan yang sedang dilihat di filter, agar Admin tahu posisi uang real-time.

### 2. Form Transaksi (Halaman Eksekusi Realisasi)
Ini adalah halaman yang otomatis terbuka pasca klik *Approve* dari Request, ATAU klik "Gunakan" dari Template. Tidak disarankan membuat transaksi mentah 100% kosong dari 0 tanpa lewat Request/Template.
- **Header:**
  - Tanggal Realisasi Kas (`transaction_date`).
  - Kategori & Deskripsi.
- **Tabel Detail:**
  - Telah terisi otomatis (*pre-filled*) dari baris item yang dibawa dari Request atau Template.
  - Admin berhak/bisa mengubah Nominal Amount dari setiap item pada titik waktu pencairan uang yang diproses saat ini saja.
- **Tombol Penyimpanan:**
  - **Simpan ke Draft:** Transaksi diamankan sebatas coretan. Belum menggeser saldo utama. Status ter-set ke `draft`.
  - **Proses (Selesai):** 
    1. Transaksi paten, menjadi `completed`. 
    2. Jika ini berasal dari *Request*, baris detail terkait berubah menjadi `realized`.

### 3. Halaman Edit Transaksi 
- Mengizinkan revisi kesalahan pengetikan nominal. Namun memiliki proteksi ketat via Database Lock.
- Perubahan pada nominal ketika status sudah `completed` akan memicu trigger.

## Aturan Bisnis & Logika Trigger (Observer Laravel)
- Aplikasi akan memantau segala bentuk *Update/Insert/Delete* pada `transaction_header`.
- **HANYA status `completed` yang dihitung.**
- Jika ada penambahan nominal = Sistem mencari baris di tabel `balance` dengan rentang `YYYY-MM`. Sistem menjumlah/mengurangi field `total_in` & `total_out`.
- `ending balance` bulan tersebut harus digaransi sinkron: `begin` + `total_in` - `total_out`.
