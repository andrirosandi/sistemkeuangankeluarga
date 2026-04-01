# Module: Notifications

## Deskripsi
Fitur in-app notification (sesuai tugas 5.7). Notifikasi dikirim otomatis oleh sistem berdasarkan event tertentu (contoh: request di-approve, di-reject, atau ada request baru untuk admin).

## Pages & UI

### 1. Notification Dropdown / Widget (Header)
- Ikon lonceng (bell) di header menampilkan jumlah notifikasi "unread" (badge counter).
- Dropdown menampilkan 5-10 notifikasi terbaru yang belum dibaca.
- Menu "Mark All As Read" dan "Lihat Semua".

### 2. List Notification Page
- Halaman tabel/list seluruh notifikasi (history).
- Filter: All, Unread.
- Menampilkan rentang waktu (contoh: "2 jam yang lalu", "Kemarin").
- Isi notifikasi (pesan berupa HTML) bisa memiliki CTA/link ke halaman terkait.

## Aturan Bisnis
- User hanya bisa melihat datanya masing-masing (`user_id`).
- Jika pesan di-klik, maka:
  - Field `is_read` diubah menjadi `1`.
  - Field `read_at` diisi timestamp.
  - Redirect menuju link yang disematkan dalam isi HTML pesannya.
- Event Generator Notifikasi:
  - **Istri/Anak ajukan dana** ➔ Admin terima notif ("Ada pengajuan baru dari [Nama]").
  - **Admin approve/reject** ➔ Istri/Anak pembuat request terima notif status ("Pengajuan [#...] Anda disetujui / ditolak").
