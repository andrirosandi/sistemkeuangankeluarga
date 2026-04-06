# Case 2: Hierarki & Workflow Anak (Tambah Anak)

Skenario ini didesain untuk memeriksa keluwesan sistem saat anggota keluarga bertambah. Secara spesifik, menguji apakah proses pengajuan dari tingkat terbawah bisa ditangani secara prosedural.

## A. Setup Data Tambahan
1. Login menggunakan akun **Suami (Admin)**.
2. Ke menu **Master Data > Pengguna**, klik Tambah Pengguna.
3. Buat akun: 
   - Nama: **Anak (Caca)**
   - Role: `anak`
4. Berikan Role tambahan (Delegasi Approval) ke **Istri (Ani)**:
   - Misal di sistem ada kapabilitas untuk memberikan menu "Approve" (Role: `admin` parsial, atau permission `approve-request` ke Istri 1).
   - _Catatan: Jika di sistem saat ini hanya Admin yang bisa memberi Approval, maka Suami yang akan turun tangan langsung._

---

## B. Skenario 1: Anak Melakukan Pengajuan Pengeluaran
1. Buka browser / mode Incongito, login sebagai **Anak (Caca)**.
2. Buka menu **Kas Keluar > Pengajuan**.
3. Klik tombol **Tambah Pengajuan**:
   - Nominal: **Rp 2.000.000**
   - Deskripsi: "Uang Saku Lebaran & Pembelian Game" 
   - Tanggal: Bulan ini
4. Status pengajuan Caca adalah **Pending**.

---

## C. Skenario 2: Proses Review dan Penolakan (Reject & Edit)
1. Login sebagai **Suami** (atau Istri jika Istri diberi hak akses Approve).
2. Terdapat notifikasi bahwa _Anak (Caca) mengajukan dana Rp 2.000.000_.
3. Suami membuka pengajuan tersebut. Budi merasa Rp 2 Juta terlalu besar untuk Uang Jajan.
4. Suami klik tombol **Tolak (Reject)** dengan menambahkan Note/Alasan: _"Maksimal 1 Juta ya, sisanya ditabung"_.

---

## D. Skenario 3: Revisi Pengajuan Anak
1. **Anak (Caca)** mendapat Notifikasi bahwa pengajuannya ditolak.
2. Caca membuka detail pengajuannya. Di sana tertera Note dari sang ayah.
3. Caca melakukan Edit / Merevisi nominal Pengajuan menjadi **Rp 1.000.000**.
4. Caca men-submit ulang pengajuan (Status kembali dari Rejected/Draft menjadi **Pending**).

---

## E. Skenario 4: Skema Approval & Realisasi Penuh
1. **Suami** kembali melihat Notifikasi pengajuan ulang Caca.
2. Suami mengklik **Approve**.
3. **Anak (Caca)** membuka menu **Perlu Tindakan**, melakukan **Realisasi** untuk Kas Keluar senilai **Rp 1.000.000** (Full Realization). Caca mengupload bukti Screenshot transfer atau pembelian game.
4. Karena uang dihabiskan sepenuhnya sesuai nilai pengajuan, status `Outstanding` Anak untuk pengajuan ini menjadi Rp 0 dan *Completed/Closed*.
