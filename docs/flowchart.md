# Core Flowcharts - Sistem Keuangan Keluarga

Dokumen ini berisi standar Flowchart *Core Business Logic* menggunakan sintaks **Mermaid**, yang disusun secara **100% akurat** berdasarkan kode di `app/Services/`, `app/Http/Controllers/Transaction/`, dan skema database (`database/migrations/`).

> Render diagram di [Mermaid Live Editor](https://mermaid.live/) atau ekstensi Markdown VS Code, lalu export ke **PDF** untuk dikumpulkan.

---

## 1. Siklus Pengajuan & Approval

Model: `request_header` | Service: `FinanceRequestService`

Diagram ini memvisualisasikan perjalanan siklus hidup Pengajuan (`request_header`), mulai dari pembuatan form hingga keputusan final (Approved / Rejected). User bisa menarik kembali pengajuan ke Draft kapan saja sebelum diproses Admin.

```mermaid
graph TD
    %% Styling
    classDef startEnd fill:#1A1A1A,color:#fff,stroke:#333,stroke-width:2px;
    classDef status fill:#E3F2FD,color:#0D47A1,stroke:#1E88E5,stroke-width:2px;
    classDef action fill:#FFF3E0,color:#E65100,stroke:#FF9800,stroke-width:1px;
    classDef system fill:#E8F5E9,color:#1B5E20,stroke:#4CAF50,stroke-width:1px;
    classDef notif fill:#F3E5F5,color:#6A1B9A,stroke:#AB47BC,stroke-width:1px;

    %% Entry
    Start([Mulai: User Buat Form Pengajuan]):::startEnd --> B{Aksi Simpan}

    B -- "Simpan sebagai Draf" --> S_DRAFT[Status: DRAFT]:::status
    B -- "Langsung Submit" --> S_REQ[Status: REQUESTED]:::status

    %% Alur DRAFT
    S_DRAFT --> C{Aksi User di Draf}
    C -- "Edit & Simpan" --> S_DRAFT
    C -- "Hapus" --> Z_DEL([Dihapus dari Sistem]):::startEnd
    C -- "Submit ke Admin" --> S_REQ

    %% Alur REQUESTED
    S_REQ --> NOTIF_SUBMIT["Notif: Kirim ke Approver/Admin"]:::notif
    NOTIF_SUBMIT --> EVAL{Keputusan}

    EVAL -- "User Tarik Kembali" --> S_DRAFT
    EVAL -- "Admin Tolak + Alasan" --> S_REJ[Status: REJECTED]:::status
    EVAL -- "Admin Setujui" --> S_APP[Status: APPROVED]:::status

    %% Reject notification
    S_REJ --> NOTIF_REJ["Notif: Beritahu User Ditolak + Alasan"]:::notif
    NOTIF_REJ --> FIN([Siklus Selesai]):::startEnd

    %% Approved triggers
    S_APP --> NOTIF_APP["Notif: Beritahu User Disetujui"]:::notif
    NOTIF_APP --> AUTO[Sistem: Auto-Create Transaction DRAFT + Set Detail PENDING]:::system
    AUTO --> NEXT([Lanjut ke Siklus Realisasi]):::startEnd
```

---

## 2. Siklus Realisasi, Saldo & Outstanding

Model: `transaction_header` + `request_detail` | Service: `TransactionService` + `BalanceService`

Diagram ini menggambarkan alur realisasi transaksi, update saldo, dan penanganan outstanding (realisasi parsial) termasuk write-off.

```mermaid
graph TD
    %% Styling
    classDef startEnd fill:#1A1A1A,color:#fff,stroke:#333,stroke-width:2px;
    classDef status fill:#E3F2FD,color:#0D47A1,stroke:#1E88E5,stroke-width:2px;
    classDef action fill:#FFF3E0,color:#E65100,stroke:#FF9800,stroke-width:1px;
    classDef system fill:#FFF8E1,color:#F57F17,stroke:#FBC02D,stroke-width:2px;
    classDef detail fill:#FCE4EC,color:#880E4F,stroke:#D81B60,stroke-width:1px;
    classDef notif fill:#F3E5F5,color:#6A1B9A,stroke:#AB47BC,stroke-width:1px;

    %% 3 Entry Points
    IN1([Via Approve Pengajuan - Auto Create]):::startEnd --> S_DRAFT
    IN2([Via Input Kas Manual - Tanpa Pengajuan]):::startEnd --> S_DRAFT
    IN3([Via Realisasi Sisa - Partial Outstanding]):::startEnd --> S_DRAFT

    %% DRAFT
    S_DRAFT[Status Transaksi: DRAFT]:::status --> ACT{Aksi di Draft}

    ACT -- "Edit Draft" --> S_DRAFT
    ACT -- "Hapus Draft" --> DEL_CHK{Ada Transaksi Lain untuk Pengajuan Ini?}
    ACT -- "Realisasikan" --> COMP_FLOW

    %% Delete logic
    DEL_CHK -- "Tidak Ada" --> DEL_RESET[Sistem: Reset Pengajuan ke REQUESTED]:::system
    DEL_CHK -- "Ada Transaksi Lain" --> DEL_KEEP[Sistem: Hapus Draft Saja, Pengajuan Tetap APPROVED]:::system
    DEL_RESET --> DEL_END([Draft Dihapus]):::startEnd
    DEL_KEEP --> DEL_END

    %% Complete flow
    COMP_FLOW[Sistem: Set Status COMPLETED]:::system --> COMP_DET
    COMP_DET[Set Detail Pengajuan: PENDING -> REALIZED]:::detail --> COMP_BAL
    COMP_BAL[Engine: Update Saldo Bulan Ini]:::system --> NOTIF_COMP
    NOTIF_COMP["Notif: Beritahu User Telah Direalisasikan"]:::notif --> S_COMP
    S_COMP[Status Transaksi: COMPLETED]:::status --> CHK{Cek Sisa Outstanding}

    %% Outstanding check
    CHK -- "Semua Item Terealisasi" --> FIN([Siklus Selesai]):::startEnd
    CHK -- "Masih Ada Item Pending" --> PARTIAL{Aksi Admin untuk Sisa}

    PARTIAL -- "Realisasikan Sisa" --> IN3
    PARTIAL -- "Write-off Sisa" --> WOFF[Sistem: Set Detail PENDING -> CLOSED]:::detail
    WOFF --> FIN

    %% Revert / Batalkan Realisasi
    S_COMP -.-> |Batalkan Realisasi| REV_FLOW
    REV_FLOW[Sistem: Revert Status ke DRAFT]:::system -.-> REV_DET
    REV_DET[Revert Detail: REALIZED -> PENDING]:::detail -.-> REV_BAL
    REV_BAL[Engine: Kembalikan Saldo]:::system -.-> NOTIF_REV
    NOTIF_REV["Notif: Beritahu User Realisasi Dibatalkan"]:::notif -.-> S_DRAFT
```
