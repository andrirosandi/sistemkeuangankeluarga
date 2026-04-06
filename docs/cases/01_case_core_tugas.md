# Case 1: Core Flow (Memenuhi Kebutuhan tugas.md)

Skenario ini secara khusus didesain untuk menjawab persyaratan pada `tugas.md`, yaitu mengenai:
- Role Suami (Admin) dan Istri (User).
- Admin dapat melihat semua data dan melakukan approval.
- User (Istri) mengelola datanya sendiri, membuat Tambah Pengeluaran dan Pengajuan Dana.
- Dashboard dan Notifikasi.

## A. Setup Data Awal
1. **Lakukan Reset Sistem:** Masuk ke menu **Pengaturan > Umum**, lalu klik tombol **Kosongkan DB & Setup Ulang**.
2. **Buat Akun Suami (Admin):** 
   - Lewati wizard Setup awal, beri nama **Suami (Budi)**, daftarkan email dan password. 
   - Role otomatis menjadi `admin`.
3. **Buat Akun Istri (User):**
   - Dari dashboard Suami, masuk ke menu **Master Data > Pengguna** dan buat user baru.
   - Nama: **Istri (Ani)**
   - Role: `istri` (termasuk sebagai `user` pengaju).

---

## B. Skenario 1: Master Uang Masuk oleh Admin (Poin 5.2 Tugas)
**Tujuan:** Menguji fungsi Admin dalam menginput kas masuk keluarga.
1. Login sebagai **Suami**.
2. Buka menu **Kas Masuk > Realisasi** (atau langsung Mutasi jika input langsung tersedia).
3. Buat Transaksi Baru ("Gaji Suami - April 2026") dengan nominal **Rp 20.000.000**.
4. Cek **Dashboard** untuk memastikan Saldo bertambah menjadi Rp 20 Juta dan grafik Pemasukan Terisi.

---

## C. Skenario 2: Pengajuan Dana oleh User (Poin 5.5 Tugas)
**Tujuan:** Menguji fitur User meminta kucuran dana yang jika di-approve akan dicatat penggunaannya.
1. Buka browser lain / mode Incognito, login sebagai **Istri (Ani)**.
2. Buka menu **Kas Keluar > Pengajuan**.
3. Buat Pengajuan baru:
   - Kategori: Pendidikan / Biaya Rumah Tangga
   - Nominal: **Rp 5.000.000**
   - Alasan: "Biaya SPP & Belanja Dapur Bulan Ini"
4. Status saat ini adalah **Pending**. Istri (Ani) tidak bisa melihat pengajuan dari pihak lain jika ada.

---

## D. Skenario 3: Approval Admin & Notifikasi (Poin 5.6 & 5.7 Tugas)
**Tujuan:** Menguji fitur Opsional Nilai Tambah (Box Notifikasi & Email Notifikasi).
1. Kembali ke browser **Suami**.
2. **Uji Box Notifikasi:** Cek Lonceng **Notifikasi** di pojok kanan atas layar. Pastikan ada notifikasi pesan masuk: _"Ani mengajukan dana Rp 5.000.000"_.
3. **Uji Email Notifikasi:** Buka Email Admin (atau Mailtrap.io jika sedang lokal). Tunjukkan kepada penguji bahwa Admin menerima Email pemberitahuan masuk tentang pengajuan baru dari User (Sesuai Poin 5.7 opsional kedua).
4. Klik notifikasi lonceng tersebut untuk dialihkan ke halaman persetujuan (atau masuk ke **Kas Keluar > Pengajuan**).
5. Suami memastikan data pengajuan Istri, lalu klik **Approve**. (Status berubah menjadi _Approved_).
6. Pada titik ini, uang sebesar Rp 5.000.000 dipegang oleh Istri dan siap dihabiskan (masuk sebagai tiket alokasi di menu "Perlu Tindakan").

---

## E. Skenario 4: Management Pengeluaran oleh User (Poin 5.3 Tugas)
**Tujuan:** Mencatat pemakaian rill (Realisasi) dari dana yang sudah disetujui, lengkap dengan upload struk/bukti.
1. Kembali ke browser **Istri**.
2. Istri mendapat notifikasi bahwa pengajuannya telah Disetujui.
3. Buka menu **Perlu Tindakan**. Klik tombol **Realisasi** pada tiket Rp 5.000.000 tersebut.
4. Isi form Realisasi Pengeluaran:
   - Jumlah: **Rp 4.500.000** (Kenyataannya hanya terpakai 4.5 Jt).
   - Deskripsi: "Belanja bulanan dan SPP lunas"
   - Upload Bukti: _Upload gambar struk belanja_. (Sistem sudah memvalidasi file upload).
5. Simpan. Karena ada sisa Rp 500.000, Outstanding belum sepenuhnya tertutup. 

---

## F. Skenario 5: Penggunaan Template Transaksi Khusus
**Tujuan:** Menunjukkan poin *plus* di luar tugas di mana user bisa otomatisasi transaksi berulang.
1. Masih di akun **Istri**.
2. Buka menu **Master Data > Template Transaksi**.
3. Buat Template baru misalnya "Bayar Tukang Sampah" sebesar Rp 50.000.
4. Saat membuat "Realisasi" atau "Kas Keluar" baru, Istri cukup mengklik template tersebut, dan form otomatis terisi.

---

## G. Skenario 6: Pengecekan Dashboard & Kesesuaian Template UI (Poin 5.1 & 6 Tugas)
**Tujuan:** Memvalidasi rekapitulasi data pada Dashboard dan memastikan keseluruhan UI mematuhi syarat framework template.
1. Keduanya (Suami dan Istri) kembali ke tampilan **Dashboard**.
2. **Review Data Dashboard (Sesuai Poin 5.1 Tugas):**
   - Buktikan bahwa UI menampilkan **Total Pemasukan** (Keseluruhan terisi Rp 20.000.000).
   - Buktikan ada card/widget **Total Pengeluaran** yang menampilkan Rp 4.500.000 (sesuai yang direalisasikan istri).
   - Buktikan **Saldo Akhir** dihitung dengan tepat: 20 Juta - 4.5 Jt = Rp 15.500.000.
3. **Review Teknis (Sesuai Poin 6 Tugas):**
   - Tunjukkan bahwa interface web ini sepenuhnya di-desain menggunakan **Bootstrap 5** _(Admin Template Tabler)_.
   - Sempatkan menginspeksi file upload (bukti struk) tadi terbuka dengan sempurna.
