# Module: User Management (Admin)

## Deskripsi
Admin mendaftarkan dan mengelola anggota keluarga. Register publik dinonaktifkan. Boleh ada lebih dari 1 admin.

## Pages

### 1. List User
- Tabel semua user: nama, email, role, status (aktif/nonaktif)
- Action: edit, reset password, toggle aktif/nonaktif

### 2. Create User
- Form: name, email, password, role (Admin/User)

### 3. Edit User
- Edit: name, email, role
- Reset password
- Toggle aktif/nonaktif

## Aturan Bisnis
- Hanya Admin yang bisa akses module ini
- Admin bisa edit nama user (user sendiri tidak bisa ganti nama)
- User nonaktif tidak bisa login, data historisnya tetap ada
- Admin tidak bisa menonaktifkan dirinya sendiri
- Boleh lebih dari 1 Admin
