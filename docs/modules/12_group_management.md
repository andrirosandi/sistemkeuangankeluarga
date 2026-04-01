# Module: Group / Role Management (Admin)

## Deskripsi
Mengelola peran (Role) atau "Perwujudan Group" di dalam sistem untuk mendefinisikan tingkat akses dari masing-masing jenis anggota keluarga (Contoh: "Kepala Keluarga", "Pencatat Keuangan", "Anak").

## Pages

### 1. List Group
- Tabel semua group/role yang telah dibuat.
- Action: Create, Edit, Delete.

### 2. Create Group
- Form Input: Nama Group (misal: "Anak").
- **Permission Assignment**: Checkbox list berjejer berisi seluruh izin akses (permissions) yang ada di aplikasi per modul. Admin dapat mencentang izin apa saja yang berhak dilakukan oleh grup ini.

### 3. Edit Group
- Edit: Nama Group.
- Edit: Centang/hapus centang dari daftar Permission Assignment.

## Aturan Bisnis
- Hanya user dengan permission pengelola Group yang boleh mengakses.
- Role/Group yang sedang digunakan oleh User tidak boleh dihapus mendadak (harus dipindah dulu user-nya).
- Setiap group bebas memiliki kombinasi permission tanpa batasan (Dynamic Authorization).
