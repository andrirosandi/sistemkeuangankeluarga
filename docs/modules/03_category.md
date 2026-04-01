# Module: Category Management (Admin)

## Deskripsi
CRUD kategori transaksi. Kategori dipakai bersama untuk IN dan OUT (tidak dibedakan per trans_code).

## Pages

### 1. List Category
- Tabel: nama kategori
- Action: edit, delete

### 2. Create Category
- Form: name

### 3. Edit Category
- Form: name

## Aturan Bisnis
- Hanya Admin yang bisa akses
- Delete: hard delete, tapi **gagal jika kategori sudah dipakai** di request/transaction/template (proteksi FK)
