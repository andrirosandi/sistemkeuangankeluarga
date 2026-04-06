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

---

## 3. Sistem Kalkulasi Saldo (Balance Engine)

Model: `balance` | Service: `BalanceService`

Diagram ini menggambarkan bagaimana saldo bulanan dihitung dan dipropagasi ke bulan-bulan berikutnya. Balance Engine dipanggil secara otomatis setiap kali transaksi direalisasikan (`add`) atau dibatalkan (`remove`).

**Rumus inti:**
```
Ending = Begin + Total_In - Total_Out
Begin[bulan N+1] = Ending[bulan N]
```

```mermaid
graph TD
    %% Styling
    classDef startEnd fill:#1A1A1A,color:#fff,stroke:#333,stroke-width:2px;
    classDef process fill:#E3F2FD,color:#0D47A1,stroke:#1E88E5,stroke-width:2px;
    classDef decision fill:#FFF3E0,color:#E65100,stroke:#FF9800,stroke-width:2px;
    classDef system fill:#E8F5E9,color:#1B5E20,stroke:#4CAF50,stroke-width:1px;
    classDef lock fill:#FFEBEE,color:#B71C1C,stroke:#E53935,stroke-width:2px;
    classDef propagate fill:#F3E5F5,color:#6A1B9A,stroke:#AB47BC,stroke-width:1px;

    %% Trigger
    START(["Trigger: Realisasikan / Batalkan Realisasi"]):::startEnd --> INPUT

    INPUT["Input: tanggal transaksi, trans_code, amount, operation"]:::process --> PARSE
    PARSE["Parse bulan dari tanggal transaksi (Y-m)"]:::process --> LOCK
    LOCK["DB Transaction + Row-Level Lock (lockForUpdate)"]:::lock --> CHK_EXIST

    %% Check existing balance
    CHK_EXIST{Record Balance Bulan Ini Sudah Ada?}:::decision

    CHK_EXIST -- "Sudah Ada" --> UPDATE_BAL
    CHK_EXIST -- "Belum Ada" --> CREATE_BAL

    %% Create new balance record
    CREATE_BAL["Ambil Ending dari Bulan Sebelumnya"]:::system --> CHK_PREV
    CHK_PREV{Ada record bulan sebelumnya?}:::decision
    CHK_PREV -- "Ada" --> USE_PREV["Begin = Ending bulan lalu"]:::process
    CHK_PREV -- "Tidak Ada" --> USE_ZERO["Begin = 0"]:::process
    USE_PREV --> NEW_REC
    USE_ZERO --> NEW_REC
    NEW_REC["Buat Record: begin, total_in=0, total_out=0, ending=begin"]:::system --> UPDATE_BAL

    %% Update balance
    UPDATE_BAL{trans_code?}:::decision
    UPDATE_BAL -- "1 (Kas Masuk)" --> ADD_IN["total_in += sign * amount"]:::process
    UPDATE_BAL -- "2 (Kas Keluar)" --> ADD_OUT["total_out += sign * amount"]:::process

    ADD_IN --> CALC
    ADD_OUT --> CALC
    CALC["ending = begin + total_in - total_out"]:::system --> SAVE
    SAVE["Simpan Record Balance"]:::system --> PROPAGATE

    %% Propagation
    PROPAGATE["Propagasi ke Bulan Berikutnya"]:::propagate --> CHK_NEXT
    CHK_NEXT{Ada record bulan berikutnya?}:::decision

    CHK_NEXT -- "Tidak Ada" --> DONE([Selesai]):::startEnd
    CHK_NEXT -- "Ada" --> LOOP

    LOOP["Untuk setiap bulan berikutnya:"]:::propagate --> LOOP_CALC
    LOOP_CALC["begin = ending bulan sebelumnya\nending = begin + total_in - total_out"]:::system --> LOOP_SAVE
    LOOP_SAVE["Simpan & lanjut ke bulan berikutnya"]:::propagate --> CHK_NEXT

    %% Sign note
    NOTE["Catatan: sign = +1 (operation: add) atau -1 (operation: remove)"]
    style NOTE fill:#FFFDE7,color:#F57F17,stroke:#FBC02D,stroke-width:1px,stroke-dasharray: 5 5
```

### Kapan Balance Engine Dipanggil?

| Aksi | Operation | Efek |
|------|-----------|------|
| Realisasikan transaksi | `add` | `total_in` atau `total_out` bertambah |
| Batalkan realisasi | `remove` | `total_in` atau `total_out` berkurang (revert) |
| Recalculate manual | - | Hitung ulang dari bulan tertentu ke depan |


