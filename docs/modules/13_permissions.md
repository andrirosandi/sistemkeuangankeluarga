# Struktur Menu & Permissions (RBAC)

Dokumen ini memetakan hubungan antara struktur menu aplikasi dengan **Roles** (Akses Grup) dan **Permissions** (Hak Akses Spesifik) menggunakan standar Spatie Laravel Permission.

## 1. Definisi Roles (Peran)
Sistem memiliki 2 level akses utama (berdasarkan `tugas.md`):
1. **`admin`**: Memiliki **semua** hak akses (Super Akses). Bisa melihat data milik siapa pun, melakukan tindakan *approval/rejection*, dan mengelola *master data*.
2. **`user`**: Memiliki hak akses **terbatas**. Hanya dapat menambah, melihat, mengedit, dan menghapus transaksinya **sendiri** (selama masih berstatus *draft/pending*).

---

## 2. Standar Penamaan Permissions
Permissions atau modul akses yang akan disimpan ke dalam database diberi pola nama:
`[modul].[action]`
*Action* umumnya terdiri dari: `view`, `create`, `edit`, `delete`, ditambah aksi khusus seperti `approve`.

---

## 3. Pemetaan Menu ke Permissions

### A. Dashboard Menu
- **Nama Menu:** Dashboard
- **Permission Required:** `dashboard.view`
- **Diizinkan (Roles):** `admin`, `user`

**Widget Permissions:**
- `dashboard.scope.self` — Melihat data diri sendiri
- `dashboard.scope.group` — Melihat data grup (via role visibility)
- `dashboard.scope.all` — Melihat semua data
- `dashboard.system.balance` — Kartu saldo sistem
- `dashboard.widget.summary` — Ringkasan transaksi
- `dashboard.widget.activity` — Aktivitas 7 hari
- `dashboard.widget.alerts` — Pengajuan pending
- `dashboard.widget.recent` — Transaksi terkini
- `dashboard.widget.request-summary` — Ringkasan pengajuan *(NEW)*
- `dashboard.widget.category` — Breakdown per kategori *(NEW)*
- `dashboard.widget.group-ranking` — Ranking grup *(NEW)*
- `dashboard.widget.user-ranking` — Ranking pengguna *(NEW)*
- `dashboard.widget.outstanding` — Outstanding board *(NEW)*
- `dashboard.widget.month-compare` — Bulan ini vs bulan lalu *(NEW)*
- `dashboard.widget.approval-stats` — Statistik approval *(NEW)*

### B. Modul Kas Masuk
**1. Pengajuan (Request Dana dari Anak/Istri)**
- **Permissions:**
  - `in.request.view` (Admin melihat semua, User melihat miliknya sendiri)
  - `in.request.create` (Mengklaim/meminta uang)
  - `in.request.edit` (Hanya bisa selama status belum diajukan/diproses)
  - `in.request.delete` (Hapus/Cancel pengajuan)
  - `in.request.approve` **[HANYA ADMIN]** (Tombol Acc / Reject)
- **Diizinkan (Roles):** `admin`, `user` (selain aksi approve)

**2. Realisasi (Master Pemasukan)**
- **Permissions:**
  - `in.transaction.view`
  - `in.transaction.create` (Memasukkan gaji/pemasukan di luar pengajuan)
  - `in.transaction.edit`
  - `in.transaction.delete`
- **Diizinkan (Roles):** **`admin` ONLY**

### C. Modul Kas Keluar
**1. Pengajuan (Pencatatan Belanja dari Anak/Istri)**
- **Permissions:**
  - `out.request.view`
  - `out.request.create`
  - `out.request.edit`
  - `out.request.delete`
  - `out.request.approve` **[HANYA ADMIN]**
- **Diizinkan (Roles):** `admin`, `user` (selain aksi approve)

**2. Realisasi (Pencatatan Keluar Final)**
- **Permissions:**
  - `out.transaction.view`
  - `out.transaction.create` 
  - `out.transaction.edit`
  - `out.transaction.delete`
- **Diizinkan (Roles):** **`admin` ONLY**

### D. Modul Mutasi Kas (Root Menu)
- **Nama Menu:** Mutasi
- **Permissions:**
  - `mutation.view` (Melihat jejak ledger kas masuk & kas keluar saling bersanding)
- **Diizinkan (Roles):** `admin`, *(`user` opsional jika mau diizinkan baca)*

### E. Modul Laporan & Analitik
- **Nama Menu:** Laporan & Analitik
- **Permissions:**
  - `report.view` (Akses semua laporan + semua scope)
  - `report.view.self` (Akses laporan terbatas — data diri sendiri) *(NEW)*
  - `report.export` (Download PDF/Excel)
- **Diizinkan (Roles):** `admin` (full), `user` (self-only via `report.view.self`)

### F. Modul Master Data & Pengaturan
Menu ini murni wilayah otoritas Suami / Admin.
- **Kategori Kas**
  - Permissions: `category.view`, `category.create`, `category.edit`, `category.delete`
- **Template Rutin** 
  - Permissions: `template.view`, `template.create`, `template.edit`, `template.delete`
- **Manajemen Pengguna**
  - Permissions: `user.view`, `user.create`, `user.edit`, `user.delete`, `user.reset-password`
- **Group & Akses (Manajemen Role)**
  - Permissions: `role.view`, `role.create`, `role.edit`, `role.delete`
- **Pengaturan Sistem**
  - Permissions: `setting.view`, `setting.edit`
- **Diizinkan (Roles) untuk semua modul F:** **`admin` ONLY**

---

## 4. Implementasi Seeder (Rencana Eksekusi)
Berdasarkan dokumen ini, nanti di file `database/seeders/RolePermissionSeeder.php` kita akan lakukan:
1. *Create* seluruh **permissions** di atas.
2. *Create* role **Admin**, lalu beri perintah: `$roleAdmin->givePermissionTo(Permission::all());` (Super Akses).
3. *Create* role **User**, lalu berikan secara selektif spesifik permissions yang nempel (misal: `dashboard.view`, `in.request.view`, dll).
