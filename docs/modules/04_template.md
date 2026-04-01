# Module: Template Management (Admin)

## Deskripsi
CRUD template untuk transaksi berulang. Bisa digunakan sebagai shortcut untuk membuat transaction langsung tanpa melalui request. Fitur 5.2 (Master Uang Masuk) menggunakan template trans_code=1.

## Pages

### 1. List Template
- Tabel: description, category, trans_code (IN/OUT), amount
- Filter: trans_code (IN/OUT)
- Action: view, edit, delete, **"Gunakan"**

### 2. Create Template
- Form header: category, description, trans_code
- Form detail: tabel inline (add/remove row) — description, amount per item
- Amount header auto-sum dari detail

### 3. Edit Template
- Sama seperti create

### 4. Gunakan Template
- Klik "Gunakan" → buka form transaction yang sudah pre-filled dari template
- Admin bisa edit: transaction_date, amount per detail
- 2 tombol:
  - **Simpan Draft** → transaction status `draft`
  - **Selesai** → transaction status `completed`

## Aturan Bisnis
- Hanya Admin yang bisa akses
- Delete: hard delete, gagal jika template sedang dipakai (proteksi)
- Saat "Gunakan", transaction yang terbentuk punya `request_id = NULL` (bukan dari request)
- Template trans_code=1: pemasukan rutin (gaji, bonus, dll)
- Template trans_code=2: pengeluaran rutin (sewa kontrakan, listrik, dll)
