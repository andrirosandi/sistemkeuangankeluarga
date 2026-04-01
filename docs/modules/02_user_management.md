# Module: User Management (Admin)

## Deskripsi
Admin mendaftarkan dan mengelola anggota keluarga. Register publik dinonaktifkan. Boleh ada lebih dari 1 admin.

## Pages

### 1. List User
- Tabel semua user: nama, email, role, status (aktif/nonaktif)
- Action: edit, reset password, toggle aktif/nonaktif

### 2. Create User
- Form: name, email, password, assign role/group (Dropdown)

### 3. Edit User
- Edit: name, email, assign role/group (Dropdown)
- Reset password
- Toggle aktif/nonaktif

## Aturan Bisnis
- Hanya User dengan permission tertentu yang sanggup mengelola module ini
- Admin bisa edit nama user (user sendiri tidak bisa ganti nama)
- User nonaktif tidak bisa login, data historisnya tetap ada
- User tidak bisa menonaktifkan dirinya sendiri
- Setiap user hanya bisa di-assign ke 1 Role/Group (relasi One-to-One)
