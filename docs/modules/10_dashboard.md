# Module: Dashboard (Halaman Utama)

## Deskripsi
Halaman yang pertama kali terbuka sesaat setelah user berhasil *login*. Tampilan widget ditampilkan berdasarkan **permission** user, sehingga setiap role melihat dashboard yang berbeda sesuai hak aksesnya.

## Widget Layout

Dashboard terdiri dari widget-widget berikut (urut dari atas ke bawah):

### 1. Welcome Bar
- Sapaan pribadi + tombol shortcut "Buat Pengajuan Baru"
- Tampil untuk: Semua user

### 2. Balance Cards (Kartu Saldo)
- Saldo Awal, Total Masuk, Total Keluar, Saldo Akhir bulan ini
- Data dari tabel `balance` (bukan query SUM real-time)
- Permission: `dashboard.system.balance`
- Tampil untuk: Admin only

### 4. Category Breakdown (Donut Chart) — **NEW**
- Distribusi transaksi per kategori (ApexCharts donut)
- Data dari `transaction_header` (completed)
- Permission: `dashboard.widget.category`
- Scope: self/group/all

### 6. Month Comparison (Bar Chart) — **NEW**
- Perbandingan pemasukan/pengeluaran bulan ini vs bulan lalu
- Data dari tabel `balance`
- Permission: `dashboard.widget.month-compare`
- Tampil untuk: Admin only

### 7. Ringkasan Transaksi
- Total pemasukan & pengeluaran (completed transactions)
- Permission: `dashboard.widget.summary`
- Scope: self/group/all, Filter: kategori

### 8. Aktivitas 7 Hari
- Tabel aktivitas debit/kredit 7 hari terakhir
- Permission: `dashboard.widget.activity`
- Scope: self/group/all, Filter: kategori

### 9. Ranking Grup — **NEW**
- Leaderboard grup berdasarkan pengeluaran (paling tinggi → paling rendah)
- Progress bar + info pemasukan/net per grup
- Permission: `dashboard.widget.group-ranking`
- Tampil untuk: Admin, role dengan scope.group

### 10. Ranking Pengguna — **NEW**
- Leaderboard user berdasarkan pengeluaran
- Permission: `dashboard.widget.user-ranking`
- Tampil untuk: Admin, role dengan scope.group

### 11. Statistik Approval — **NEW**
- Khusus approver: total approved/rejected, pending, avg response time
- Alert jika ada request overdue (>3 hari)
- Permission: `dashboard.widget.approval-stats`
- Tampil untuk: User dengan permission `*.request.approve`

### 12. Pengajuan Pending
- List 10 request pending terbaru
- Permission: `dashboard.widget.alerts`
- Scope: self/group/all

### 13. Transaksi Terkini
- List 10 transaksi completed terbaru (full width)
- Permission: `dashboard.widget.recent`
- Scope: self/group/all, Filter: kategori

## Scope System
- **self**: Hanya data milik user sendiri
- **group**: Data user sendiri + user dari role yang di-watch (via `role_visibility`)
- **all**: Semua data (admin only)

## Aturan Bisnis
- Balance cards membaca dari 1 baris tabel `balance` (performa <50ms)
- Chart menggunakan ApexCharts (via CDN)
- Approver dideteksi dari permission `in.request.approve` atau `out.request.approve`
- Outstanding mencakup: requested (belum dijawab), approved belum cair, realisasi parsial
