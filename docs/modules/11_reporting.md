# Module: Reporting & Analitik (Admin)

## Deskripsi
Modul khusus yang merangkum kesehatan finansial keluarga dalam bentuk grafik (Visual/Chart) dan tabel data (Eksport Excel/PDF). Fitur ini eksklusif bagi Admin.

## Pages / Jenis Laporan

### 1. Laporan Tahunan (Annual Report)
- **Data Source:** Agregasi dari tabel `balance` (akumulasi *begin*, *total_in*, *total_out*, *ending* per bulan).
- **Tampilan:** Grafik Tren Garis (Line Chart) 12 Bulan. Menunjukkan pergerakan naiknya Pemasukan (Garis Biru) versus Pengeluaran (Garis Merah).
- **Tujuan:** Evaluasi besar seberapa sehat sisa/tabungan uang rumah tangga dalam 1 tahun terakhir.

### 2. Laporan Pengeluaran per-Kategori
- **Data Source:** Tabel `transaction_header` difilter dengan `trans_code = 2` (Out/Pengeluaran). Di-grup berdasarkan `category_id`.
- **Tampilan:** Diagram Lingkaran (Pie Chart / Donut Chart).
- **Tujuan:** Admin dapat melihat porsi kebocoran dana terbesar, misal: "Bulan ini 60% habis di Kategori Baju Anak, cuma 20% di Dapur".
- **Filter Bar:** Periode Bulanan / Custom Rentang Hari.

### 3. Laporan Mutasi Detail (Ekspor Arsip)
- **Data Source:** Tabel `transaction_header` & `transaction_detail`.
- **Tampilan:** Data berbentuk Grid Tabel riil layaknya rekening koran bank.
- **Tujuan:** Bukti konkret kas riil yang bisa difilter tanggalnya, lalu terdapat tombol aksi:
  - **Export to PDF:** Untuk dicetak.
  - **Export to Excel/CSV:** Untuk dicatat/dikalkulasi ulang secara manual jika suami membutuhkannya.

### 4. Laporan Realisasi vs Pengajuan (Efisiensi)
- **Data Source:** Membandingkan nilai sum `request_header.amount` (Angka Mentah yang Diminta) berhadapan dengan `transaction_header.amount` (Angka Realisasi yang Disetujui Suami).
- **Tujuan:** Menunjukkan rasio "Penyelamatan/Penghematan Budget". Misal: Total Istri request Rp5.000.000, tapi Admin cuma mengeksekusi cair Rp3.500.000. Tersisa penghematan/rejected Rp1.500.000.

### 5. Laporan Outstanding (Tanggungan Belum Cair)
- **Data Source:** Tabel `request_header` yang berstatus `requested` (belum dijawab) ditambah tabel `transaction_header` yang masih nyangkut berstatus `draft` (disetujui tapi uangnya belum ditransfer/kasikan).
- **Tujuan:** Evaluasi tanggungan hutang internal keluarga. Menunjukkan total Nominal Rupiah dari pengajuan-pengajuan milik Istri/Anak yang belum direalisasikan oleh Suami sampai detik ini. Membantu *monitoring* agar tidak ada request yang kadaluarsa atau terlupakan.

## Aturan Bisnis
- Penggunaan package seperti `Maatwebsite/Laravel-Excel` atau `barryvdh/laravel-dompdf` digunakan pada modul ke-3 untuk mengekspor data transaksi.
- Visualisasi Chart (poin 1 dan 2) disarankan menggunakan library JavaScript modern paling ringan (misal *Chart.js* atau *ApexCharts*) yang diinisialisasi melalui Alpine.js.
