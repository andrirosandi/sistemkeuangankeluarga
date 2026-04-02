# Module: Realisasi (Approval & Transaksi Admin)

## Deskripsi
Sesuai struktur sidebar yang disepakati, menu ini adalah gabungan fitur "Inbox Approval" dan "Buku Mutasi" yang diakses oleh Admin. Menu dipisah berdasarkan jenis kas:
1. **Kas Masuk > Realisasi**
2. **Kas Keluar > Realisasi**

Halaman Realisasi ini akan berfungsi ganda:
- Menampilkan daftar antrean pengajuan (menunggu persetujuan).
- Menampilkan riwayat transaksi (mutasi kas riil) yang sudah disetujui dan diselesaikan.

## Pages & UI

### 1. Halaman Realisasi (Diberi Tabs/Filter)
- Halaman utama "Realisasi" (baik Masuk/Keluar) sebaiknya memuat 2 bagian/tab utama:
  1. **Tab Antrean (Inbox):** Menampilkan daftar dari tabel `request_header` yang statusnya `requested`.
  2. **Tab Selesai (Mutasi Kas):** Menampilkan buku mutasi dari tabel `transaction_header` yang statusnya `completed`.
- Data List secara default diurutkan berdasarkan `priority` High di urutan teratas, lalu berdasar tanggal terbaru.
- Action Utama: Tombol **Review**.

### 2. Review Request & Beri Keputusan (Read-Only)
Pas Admin klik salah satu Request dari Inbox, Admin diarahkan ke halaman ini.
- **Tampilan Utama (Read-only):** Menampilkan Detail Header (kategori, nominal diajukan, prioritas, catatan tambahan dari user) dan Tabel Detail Item persis seperti yang diisi user.
- **Tampilan Bukti:** Foto diletakkan dengan jelas dan bisa diperbesar (*lightbox*).
- **Form Keputusan:**
  - **Tolak (Reject):** Membuka input *Alasan Penolakan* (`rejection_reason`). Saat dikonfirmasi, Pengajuan langsung berubah statusnya menjadi `rejected`. Selesai.
  - **Setujui (Approve):** Merubah status Pengajuan menjadi `approved`.
    - ➔ *Logika Otomatis:* Ketika tombol Setuju diklik, sistem **secara otomatis membuat entri di tabel `transaction_header` beserta detailnya dengan status `draft`**, lalu **me-redirect** Admin menuju halaman **Form Transaction (Realisasi)** tersebut untuk diedit.

### 3. Redirect ke Form Transaction (Realisasi)
Form ini adalah wujud nyata eksekusi pencairan uang / aktual kas (terhubung ke modul Transaction).
- Secara otomatis *pre-filled* (sudah terbentuk di database berstatus `draft`) dari Item-item pengajuan yang baru saja di-approve.
- Di sinilah Admin bisa memilah/mengedit mana item yang di Write-off/Closed, dan mengubah nominal aktual (jika realisasi uang yang cair berbeda dengan angka pengajuan).
- Terdapat 2 pilihan penyimpanan transaksi realisasi ini:
  - **Simpan ke Draft:** Menyimpan progres ketikan admin. Uang riil belum memotong dari tabel `balance` bulanan.
  - **Proses (Selesai):** Transaksi difinalisasi menjadi `completed`, uang langsung terpotong/bertambah pada rekap `balance`, dan field `status` pada `request_detail` yang bersangkutan ditarik menjadi `realized`.

## Aturan Bisnis
- Jika Pengajuan ditolak, dikirimkan Notifikasi ke Pembuat Request berisi *Rejection Reason*.
- Jika Pengajuan disetujui dan langsung di **Proses** di halaman Transaksi, Notifikasi juga dikirim ke user bahwa "Dana sudah cair / terealisasi".
