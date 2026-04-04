# Module: Role Visibility (Cross-Role Data Access)

## Deskripsi
Fitur untuk mengatur **role mana yang boleh melihat data milik role lain**. Secara default, setiap user hanya bisa melihat data miliknya sendiri. Dengan fitur ini, Admin dapat mengonfigurasi agar suatu role (contoh: "Istri") bisa melihat data pengajuan/transaksi milik role lain (contoh: "Anak") — tanpa harus menjadi Admin.

Admin secara otomatis bisa melihat semua data tanpa perlu konfigurasi di tabel ini.

## Tabel Database
**`role_visibility`** — Relasi many-to-many antar roles (self-referencing via Spatie `roles` table).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto-increment |
| `watcher_role_id` | BIGINT FK→roles | Role yang **melihat** data |
| `watched_role_id` | BIGINT FK→roles | Role yang **dilihat** datanya |
| `created_by` | BIGINT FK→users | Admin yang mengatur visibility ini |
| `updated_by` | BIGINT FK→users | Admin yang terakhir mengubah |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Constraint:** `UNIQUE(watcher_role_id, watched_role_id)` — mencegah duplikasi pasangan.

## Contoh Data

| watcher_role_id | watched_role_id | Artinya |
|---|---|---|
| 2 (Istri) | 3 (Anak) | Istri bisa lihat data Anak |
| 3 (Anak) | 2 (Istri) | Anak bisa lihat data Istri |

> **Catatan:** Relasi ini **tidak otomatis dua arah**. Jika hanya baris pertama yang ada, Istri bisa lihat data Anak tapi Anak TIDAK bisa lihat data Istri.

## Pages & UI

### Integrasi ke Group Management (Modul 12)
Fitur ini **bukan halaman terpisah**. Konfigurasi visibility ditanamkan di dalam halaman **Create/Edit Group** (`master/role/index.blade.php`).

**Tambahan UI di Form Group:**
- Section baru: **"Visibilitas Data"** atau **"Bisa Melihat Data Role Lain"**
- Berisi **checkbox list** dari semua role yang ada (kecuali role yang sedang diedit sendiri)
- Admin mencentang role mana saja yang datanya boleh dilihat oleh group ini

### Contoh Tampilan
```
┌─────────────────────────────────────────────┐
│ Edit Group: Istri                           │
├─────────────────────────────────────────────┤
│ Nama Group: [Istri          ]               │
│                                             │
│ ── Hak Akses (Permissions) ──               │
│ ☑ dashboard.view                            │
│ ☑ in.request.view                           │
│ ☑ in.request.create                         │
│ ...                                         │
│                                             │
│ ── Visibilitas Data ──                      │
│ Bisa melihat data milik role:               │
│ ☐ Admin                                     │
│ ☑ Anak                                      │
│ ☐ Pencatat Keuangan                         │
│                                             │
│          [Simpan]  [Batal]                   │
└─────────────────────────────────────────────┘
```

## Logika di Controller (Query Filter)

### Untuk User Non-Admin
```php
// 1. Ambil role ID user saat ini
$myRoleId = auth()->user()->roles->first()->id;

// 2. Ambil daftar role yang boleh dilihat
$watchedRoleIds = RoleVisibility::where('watcher_role_id', $myRoleId)
    ->pluck('watched_role_id');

// 3. Gabungkan dengan role sendiri (selalu bisa lihat data sendiri)
$allVisibleRoleIds = $watchedRoleIds->push($myRoleId);

// 4. Ambil semua user_id dari role-role yang visible
$visibleUserIds = User::role($allVisibleRoleIds->toArray())
    ->pluck('id');

// 5. Query data (request/transaction/dll)
$requests = RequestHeader::whereIn('created_by', $visibleUserIds)->get();
```

### Untuk Admin
```php
// Admin bypass — langsung lihat semua
if (auth()->user()->hasRole('Admin')) {
    $requests = RequestHeader::all();
}
```

## Aturan Bisnis
- **Admin** selalu bisa melihat semua data tanpa perlu dimasukkan ke tabel ini.
- Hanya user dengan permission `role.edit` yang bisa mengonfigurasi visibility (via Group Management).
- Visibility bersifat **satu arah** (non-reciprocal). Istri bisa lihat Anak ≠ Anak bisa lihat Istri. Keduanya harus dikonfigurasi terpisah.
- Visibility **hanya berlaku untuk READ** (view/list). Tidak memberikan hak edit/delete/approve terhadap data milik role lain.
- Jika sebuah role dihapus, seluruh baris visibility terkait **otomatis terhapus** (ON DELETE CASCADE).

## Modul yang Terpengaruh
Fitur ini mempengaruhi query filter di modul-modul berikut:
1. **Request / Pengajuan** — List request (Kas Masuk & Keluar)
2. **Realisasi** — Tab Antrean (Inbox Approval)
3. **Transaction** — Read-only view untuk non-admin
4. **Mutasi Kas** — Ledger view
5. **Dashboard User** — Ringkasan aktivitas (opsional: bisa lihat aktivitas role lain)

## Catatan untuk Fitur Approval Multi-Role
Fitur **"role lain selain Admin bisa melakukan approval"** tidak memerlukan tabel baru. Cukup assign permission `in.request.approve` dan/atau `out.request.approve` ke role yang diinginkan melalui **Group Management** (modul 12). Sistem RBAC Spatie sudah mendukung ini secara native.

Namun, agar user dengan hak approve bisa **melihat** request milik role lain di inbox antrean, role tersebut harus dikonfigurasi visibility-nya terlebih dahulu di tabel `role_visibility`.
