# Module: Request (Pengajuan)

## Deskripsi
Fitur bagi user (Istri/Anak) untuk membuat pengajuan. Sesuai kesepakatan, UI dipisah menjadi dua menu utama agar lebih intuitif:
1. **Pengajuan Kas Masuk** (Melaporkan pemasukan, `trans_code = 1`)
2. **Pengajuan Kas Keluar** (Meminta uang pencairan, `trans_code = 2`)

## Pages & UI

### 1. List Request (Berlaku untuk Masuk & Keluar)
- Menampilkan daftar request pribadi user tersebut sesuai dengan menu jenis yang diklik.
- **Search & Filter Explorer:**
  - **Search:** Pencarian bebas berdasarkan keterangan/deskripsi pengajuan.
  - **Filter Status:** (Draft, Requested, Approved, Rejected, Canceled).
  - **Filter Prioritas:** (Low, Normal, High).
  - **Filter Tanggal:** (Bulan tertentu / Rentang tanggal).
- Dropdown Action:
  - **View:** Melihat draf/progress.
  - **Edit:** (Hanya jika masih `draft`).
  - **Submit:** (Mengubah `draft` menjadi `requested`).
  - **Cancel:** (Bisa mem-batalkan yang belum direalisasi Admin).

### 2. Form Tambah & Edit Request (Master-Detail)
Halaman form dibagi menjadi 2 area fungsional:

**Area Atas - Header:**
- Kategori (`category_id`)
- Tanggal Pengajuan (`request_date`)
- Jenis Transaksi otomatis terkunci sesuai Menu (Masuk/Keluar).
- Prioritas / Urgensi (`priority`: Low, Normal, High)
- Deskripsi Umum (`description`: Judul singkat)
- Catatan Tambahan (`notes`: Penjelasan panjang/alasan opsional)
- **Attach Bukti:** Fitur upload resi/struk. **Bisa mengunggah BANYAK FOTO (multiple files) sekaligus** dalam 1 pengajuan (dikelola via Spatie MediaLibrary).
- Menampilkan **Total Amount** (Read-only, ter-kalkulasi dari detail).

**Area Bawah - Detail Item:**
- Tabel yang memuat daftar detail dari pengajuan ini.
- **Action per baris & tabel:**
  - **Tambah Item:** Membuka form/dialog untuk isi Deskripsi dan Amount, lalu nambah baris baru di tabel.
  - **Edit Item:** Mengubah baris yang sudah ditambahkan (sebelum di-save ke database).
  - **Hapus Item:** Menghilangkan baris dari tabel.

### 3. Tombol Penyimpanan
- **Simpan ke Draft:** Menyimpan ke database dengan status `draft`. Data belum masuk ke Inbox Admin, sehingga user masih bebas melakukan *Edit* atau *Cancel*.
- **Ajukan (Submit):** Menyimpan keseluruhan form dan *langsung* mengubah status header menjadi `requested`.
  - Form terkunci dan tidak bisa diedit lagi oleh User (Istri/Anak).
  - **Logika Notifikasi:** Sistem akan otomatis mengirim Alert/Notifikasi (tersimpan di tabel `notifications`) kepada Admin bahwa ada "Pengajuan Baru dari [Nama User]" yang menunggu persetujuan.

### 4. Detail Request & Approval History (View)
Halaman bersifat *read-only* untuk memantau progress:
- Menampilkan ulang data Header dan tabel Detail Item.
- Menampilkan lampiran foto bukti struk (klik untuk perbesar).
- **Approval History / Log:** Menampilkan jejak waktu (timeline) dari request tersebut:
  - Waktu Dibuat (Kapan di-draft).
  - Waktu Diajukan (Ketika disubmit menjadi `requested`).
  - Waktu Diproses Admin (`approved_at`) beserta informasi siapa Admin-nya.
  - Status Akhir (`approved`, `rejected`, atau `canceled`).
  - **Catatan Admin:** Memuat alasan penolakan (`rejection_reason`) atau informasi lain jika digagalkan penuh.

## Aturan Bisnis
- Header.amount murni di-update setiap kali ada Tambah/Edit/Hapus pada Item di bawahnya.
