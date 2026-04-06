# Flowchart Sistem Keuangan Keluarga

Dokumen ini berisi standar Flowchart menggunakan sintaks **Mermaid**. Anda bisa me-*render* kode di bawah ini menjadi gambar di [Mermaid Live Editor](https://mermaid.live/) atau ekstensi Markdown VS Code Anda, lalu menyimpannya dalam format **PDF** untuk dikumpulkan ke Perusahaan.

## 1. Flowchart Arus Kas Keluar & Siklus Pengajuan (Core Approval Workflow)

Diagram ini mengilustrasikan perjalanan dana dari sejak diminta (Pengajuan) hingga dana tersebut dibelanjakan dengan bukti (Realisasi/Pengeluaran).

```mermaid
graph TD
    %% Nodes
    Start([Mulai: User Butuh Dana]) --> A
    A[User Mengisi Form Pengajuan] --> B{Pilih Action}
    
    B -- Simpan Draft --> C[Status Header: DRAFT]
    B -- Submit --> D[Status Header: REQUESTED]
    
    C --> |User Edit/Submit| D
    
    D --> E[System: Kirim Notifikasi ke Admin]
    E --> F[Admin Review Pengajuan]
    
    F --> G{Keputusan Admin}
    
    G -- Tolak --> H[Admin Beri Alasan]
    H --> I[Status Header: REJECTED]
    I --> |User Edit & Resubmit| D
    
    G -- Batal (User) --> J[Status Header: CANCELED]
    
    G -- Setujui --> K[Status Header: APPROVED]
    K --> L[Sistem: Auto-Create Transaction DRAFT]
    L --> M[Status Detail: PENDING]
    
    M --> N[User/Admin Lengkapi Bukti & Realisasi]
    N --> O{Action Realisasi}
    
    O -- Simpan Draft --> P[Status Transaksi: DRAFT]
    P --> |Update/Selesaikan| Q[Status Transaksi: COMPLETED]
    
    O -- Selesaikan --> Q
    
    Q --> R[Status Detail: REALIZED]
    R --> S{Cek Sisa Outstanding}
    
    S -- Masih Ada Sisa --> T[Kembali ke Siklus Realisasi]
    T --> N
    
    S -- Habis / Write-Off --> U[Status Detail: CLOSED]
    U --> V([Selesai: Siklus Selesai])
```

---

## 2. Flowchart Otentikasi, Hak Akses (Privasi), dan Kas Masuk

Diagram ini memvisualisasikan bagaimana sistem secara ketat membelah akses antara `Admin` dan `User` biasa, serta bagaimana Admin mengontrol suntikan Kas Masuk (Income) yang jadi *trigger* awal saldo Dashboard.

```mermaid
graph TD
    %% Nodes
    Start([Mulai: Login Aplikasi]) --> A
    A[Email & Password di-submit] --> B{Validasi Autentikasi}
    
    B -- Gagal --> C[Kembali ke Halaman Login]
    B -- Valid --> D{Cek Role Middleware}
    
    %% Alur User Biasa
    D -- Role: USER --> E[Menerapkan Scoped Visibility: user_id]
    E --> F[Memuat Dashboard Personal]
    F --> |Hanya Boleh| G(Mengakses Data Miliknya Sendiri)
    
    %% Alur Admin Utama
    D -- Role: ADMIN --> H[Menerapkan Global Visibility]
    H --> I[Memuat Dashboard Global Keluarga]
    I --> J{Pilih Task Utama Admin}
    
    J -- Approver --> K[Daftar Seluruh Pengajuan Menunggu]
    J -- Controller --> L[Input Kas Masuk Baru]
    
    %% Alur Kas Masuk
    L --> M[Form Pendapatan/Bonus/Gaji]
    M --> N{Submit Form}
    N --> O[Sistem: Tambah Saldo Akhir]
    O --> P[Update Diagram Pemasukan Dashboard]
    P --> ([Selesai])
```
