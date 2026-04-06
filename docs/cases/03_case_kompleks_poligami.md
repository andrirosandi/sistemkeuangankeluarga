# Case 3: Dinamika Kompleks & Privasi Data (Poligami/Isu Skala Besar)

Skenario tahapan ini dibuat untuk melakukan **Stress Test** (Uji Beban Logika) pada sistem guna melihat sebaik apa sistem menyekat/meng-isolasi data sensitif seperti finansial antar grup yang secara teknis terhubung pada satu "Admin", namun dilarang saling melihat rekapitulasi keuangannya.

## A. Setup Struktur Beban
1. Memastikan di tahap sebelumnya sudah dibuat:
   - **Suami (Admin/Budi)**
   - **Istri 1 (Ani)**
   - **Anak 1 (Caca)**
2. Kini Suami menambahkan entitas baru:
   - Ke menu **Master Data > Pengguna**, tambahkan **Istri 2 (Dewi)** (Role = `istri`).
   - Tambahkan **Anak 2 (Dino)** (Role = `anak`).

---

## B. Skenario 1: Segregasi Data & Zero Visibility (Poin Kritis)
**Tujuan:** Menguji aturan visibilitas data agar Istri 1 tidak bisa mengintip urusan dapur Istri 2.
1. Login sebagai **Istri 2 (Dewi)**.
2. Buat Pengajuan Dana:
   - Nominal: **Rp 10.000.000** ("Dana Pembukaan Toko Kue")
   - Submit pengajuan.
3. Login sebagai **Istri 1 (Ani)**.
   - Buka menu **Pengajuan** atau **Dashboard**.
   - **Test Validation:** Ani sama sekali **TIDAK BOLEH** melihat ada notifikasi masuk terkait dana 10 Juta. Saldo akhir yang dilihat Ani di Dashboard juga hanya merupakan pengeluarannya sendiri (atau pengeluaran spesifik grup pertamanya) - bergantung bagaimana Suami mempresentasikan limit saldo untuk setiap pengguna. 
   - _Catatan Implementasi: Di sistem default (1 wallet admin), balance system biasanya akumulasi total, sehingga perlu dicek apakah Dashboard Istri_1 juga bisa mengintip saldo global keluarga atau hanya mutasi dia saja._ 

---

## C. Skenario 2: Approval Paralel Lintas Istri Kandung
1. **Anak 2 (Dino)** login. 
   - Ia mengajukan dana Rp 500.000 untuk "Kegiatan Ekstrakurikuler".
2. **Suami (Admin)** login.
   - Suami melihat 2 Pengajuan Masuk: Rp 10 Juta dari Istri 2, dan Rp 500 Ribu dari Anak 2.
   - Suami melakukan **Approve** pada keduanya.
3. Apakah **Istri 1 (Ani)** bisa ikut berinteraksi?
   - **Test Validation:** Buka browser Istri 1. Akses halaman apa saja. Pastikan Istri 1 tidak bisa men-tracking riwayat pengajuan milik Dino (Anak 2) maupun Dewi (Istri 2) yang baru di-approve pak Suami.

---

## D. Skenario 3: Outstanding Menumpuk di Lintas Bulan
**Tujuan:** Menguji konsistensi sistem pencatatan jika Realisasi dilakukan secara dicicil dengan sisa uang (Outstanding) yang sangat lama tidak digunakan.
1. **Istri 2 (Dewi)** mendapat notifikasi Approval Rp 10.000.000.
2. Di bulan pertama, Istri 2 menjalankan **Realisasi 1**: 
   - Input: Rp 3.000.000 ("Beli Oven").
   - Status Outstanding sisa Rp 7.000.000.
3. Masuk ke simulasi bulan kedua. Istri 2 tidak ada progress. Outstanding harus tetap "menggantung" tanpa terganggu reset bulanan.
4. Di bulan ketiga, Istri 2 menjalankan **Realisasi 2**:
   - Input: Rp 2.000.000 ("Beli Etalase Kaca").
   - Status Outstanding kini merosot jadi Rp 5.000.000.

---

## E. Skenario 4: Write-Off (Pemutihan Hutang)
1. Setelah sekian lama (misalnya toko kue sudah jalan), sisa plafon Rp 5 Juta dari Suami tersebut direlakan atau dianggap "sudah habis" tanpa pelaporan bon. 
2. **Suami (Admin)** login. 
3. Buka menu **Perlu Tindakan / Outstanding**. 
4. Suami menekan tombol **Batal / Tutup Sisa / Write-Off** pada outstanding Istri 2 yang tersisa 5 Juta tersebut.
5. **Test Validation:** Saldo/Limit Istri 2 menyesuaikan. Status Pengajuan menjadi _Completed (Closed)_, menutup alokasi gantung tanpa harus memaksa Istri 2 mencari struk fiktif senilai 5 Juta tersebut. 
