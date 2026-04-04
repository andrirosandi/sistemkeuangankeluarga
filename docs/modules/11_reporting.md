# Module: Reporting & Analitik

## Deskripsi
Modul yang merangkum kesehatan finansial keluarga dalam bentuk grafik (ApexCharts) dan tabel data. **Admin** bisa mengakses semua laporan dengan scope all/group/self. **User biasa** dengan permission `report.view.self` bisa mengakses laporan terbatas (hanya data miliknya sendiri).

## Akses & Permission

| Permission | Admin | User |
|---|---|---|
| `report.view` | ✅ | ❌ |
| `report.view.self` | ✅ | ✅ |
| `report.export` | ✅ | ❌ |

User dengan `report.view.self` hanya bisa melihat data `created_by = auth()->id()`. Scope dropdown tidak ditampilkan.

## Pages / Jenis Laporan

### Landing Page (`/laporan`)
Grid card berisi link ke setiap jenis laporan. User biasa hanya melihat laporan yang boleh diakses.

### R1. Laporan Tahunan (`/laporan/tahunan`)
- **Data Source:** Tabel `balance` (12 bulan)
- **Tampilan:** Area Chart (ApexCharts) — 3 garis: Pemasukan, Pengeluaran, Saldo Akhir
- **Tabel:** Ringkasan bulanan 12 bulan
- **Filter:** Tahun
- **Akses:** Admin only (butuh `report.view`)

### R2. Laporan per Kategori (`/laporan/kategori`)
- **Data Source:** `transaction_header` GROUP BY `category_id`
- **Tampilan:** Donut Chart + tabel detail per kategori
- **Filter:** Bulan, Scope
- **Akses:** Admin + User (self)

### R3. Laporan Mutasi Detail (`/laporan/mutasi`)
- **Data Source:** `transaction_header` + `transaction_detail`
- **Tampilan:** Paginated table (rekening koran)
- **Filter:** Bulan, Jenis (Masuk/Keluar/Semua), Scope
- **Export:** PDF (`barryvdh/laravel-dompdf`), Excel (`maatwebsite/excel`)
- **Akses:** Admin + User (self), Export admin only

### R4. Realisasi vs Pengajuan / Efisiensi (`/laporan/efisiensi`)
- **Data Source:** `request_header.amount` vs `transaction_header.amount`
- **Tampilan:** Bar chart requested vs realized per kategori + summary cards
- **Metrics:** Total diajukan, terealisasi, penghematan, rasio efisiensi %
- **Filter:** Bulan, Scope
- **Akses:** Admin + User (self)

### R5. Laporan Outstanding (`/laporan/outstanding`)
- **Data Source:** `request_header` (requested/approved) + `transaction_header` (draft)
- **Tampilan:** 3 section tabel terpisah: Menunggu Approval, Approved Belum Cair, Realisasi Parsial
- **Fitur:** Aging indicator per request (warna hijau/kuning/merah)
- **Filter:** Scope
- **Akses:** Admin + User (self)

### R6. Laporan per Anggota (`/laporan/per-anggota`) — **NEW**
- **Data Source:** `transaction_header` GROUP BY `created_by`
- **Tampilan:** Horizontal bar chart + ranking table
- **Metrics:** Request count, pemasukan, pengeluaran, net per user
- **Filter:** Bulan
- **Akses:** Admin only (butuh `report.view`)

### R7. Laporan Pemasukan (`/laporan/pemasukan`) — **NEW**
- **Data Source:** `transaction_header` WHERE `trans_code = 1`
- **Tampilan:** Donut chart by category + detail table pemasukan
- **Filter:** Bulan, Scope
- **Akses:** Admin + User (self)

## Library & Package
- **Chart:** ApexCharts (CDN: `cdn.jsdelivr.net/npm/apexcharts`)
- **Export PDF:** `barryvdh/laravel-dompdf`
- **Export Excel:** `maatwebsite/excel` (Maatwebsite\Laravel-Excel)
- Chart diinisialisasi via Alpine.js component

## Aturan Bisnis
- User self-only tidak bisa melihat data orang lain walau manipulasi URL
- Export hanya tersedia untuk user dengan permission `report.export`
- Semua query menggunakan scope enforcement server-side (`resolveScope()`)
