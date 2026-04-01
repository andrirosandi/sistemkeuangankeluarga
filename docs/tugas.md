TUGAS INTERVIEW
Sistem Keuangan Keluarga (Laravel)
1. Tujuan
Tugas ini bertujuan untuk menguji kemampuan kandidat dalam:
•	CRUD (Create, Read, Update, Delete) 
•	Relasi database 
•	Authentication & Authorization 
•	Approval workflow 
•	Validasi dan struktur kode Laravel
•	Analisa Kebutuhan Sistem
2. Deskripsi Umum
Buat sebuah aplikasi berbasis Laravel untuk pencatatan keuangan keluarga dengan sistem role dan approval.
3. Role User
Aplikasi memiliki 3 role:
1.	Admin (Suami) 
2.	User (Istri, Anak)
4. Hak Akses
User:
•	Hanya dapat melihat dan mengelola data miliknya sendiri 
•	Tidak dapat melihat data user lain 
Admin:
•	Dapat melihat semua data user 
•	Dapat melakukan approval 
5. Fitur Sistem
5.1 Dashboard (Semua Role)
Menampilkan:
•	Total pemasukan (keseluruhan dan per bulan)
•	Total pengeluaran (keseluruhan dan per bulan)
•	Saldo akhir (saat ini)


5.2 Master Uang Masuk / Pemasukan(Admin)
Fitur:
•	CRUD data pemasukan per bulan
•	Contoh: gaji, bonus, dll
5.3 Management Pengeluaran (User)
User dapat:
•	Menambahkan data pengeluaran 
Field minimal:
•	Jumlah 
•	Deskripsi 
•	Tanggal 
•	Upload bukti (gambar / PDF) 
Status:
•	pending (ketika baru di buat oleh user) 
•	approved 
•	rejected
5.4 Approval Pengeluaran (Admin)
Admin dapat:
•	Melihat semua data pengeluaran 
•	Melakukan: 
o	Approve
o	Reject
5.5 Pengajuan Dana (User)
User dapat:
•	Mengajukan dana 
Field minimal:
•	Jumlah 
•	Alasan 
•	Bulan pengajuan 
Status:
•	pending 
•	approved 
•	rejected
5.6 Approval Pengajuan (Admin )
Admin dapat:
•	Approve / Reject pengajuan 
Jika disetujui:
•	Nominal pengajuan otomatis masuk ke Master Uang Masuk sesuai bulan pengajuan
5.7 Notifikasi (Opsional / Nilai Tambah)
•	Ada box notifikasi yang akan terisi jika ada pengajuan dana yang di ajukan user, informasi pengeluaran yang di ajukan user, dllnya sesuai kebutuhan
•	Ada email notifikasi kepada admin jika ada pengajuan / pengeluaran yang di buat oleh user
6. Ketentuan Teknis
•	Menggunakan Laravel (versi 12) 
•	Database mysql
•	Wajib menggunakan bootstrap 5 (boleh menggunakan tamplate dashboard admin bootstrap)
•	Menggunakan migration & relasi database (optional)
•	Menggunakan authentication (Breeze / manual) 
•	Menggunakan authorization (middleware / policy) 
•	Menggunakan validasi pada form input 
•	Menggunakan file upload untuk bukti pengeluaran
•	Menggunakan Blade Tamplating Enggine
•	Menggunakan Live Wire (Optional)
•	Menggunakan Eloquent ORM (Optional)
7. Perancangan Sistem dan pengumpulan tugas
Kandidat diminta untuk:
•	Membuat flowchart dalam bentuk PDF
•	Membuat vidio tutorial penggunakaan aplikasi 
•	Source code disimpan di github, dan kirimkan ke email agus.yogandi@inacofood.com dan cc alberto.simon@inacofood.com. sertakan dokumen lainya yang sudah di buat.

