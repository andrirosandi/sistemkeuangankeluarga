# Temuan Masalah pada Dokumen Tugas

## 1. Fitur 5.6 — "Otomatis masuk ke Master Uang Masuk sesuai bulan pengajuan"

**Kutipan tugas:**
> Jika disetujui: Nominal pengajuan otomatis masuk ke Master Uang Masuk sesuai bulan pengajuan

**Masalah:**
Pencatatan keuangan seharusnya berdasarkan **tanggal realisasi** (kapan uang benar-benar keluar/masuk), bukan berdasarkan bulan pengajuan. Jika dicatat sesuai bulan pengajuan, maka laporan keuangan tidak mencerminkan kondisi keuangan yang sebenarnya.

**Contoh kasus:**
- Istri ajukan dana di 25 Maret untuk keperluan bulan April
- Admin approve di 28 Maret, uang keluar 28 Maret
- Kalau dicatat di bulan April → saldo Maret salah, saldo April juga salah

**Keputusan desain:**
Transaksi dicatat berdasarkan `transaction_date` (tanggal realisasi). Field "bulan pengajuan" tidak digunakan. Balance dihitung dari tanggal aktual transaksi.
