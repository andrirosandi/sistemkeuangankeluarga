-- ==========================================================
-- SISTEM KEUANGAN KELUARGA - Database Schema
-- ==========================================================
-- trans_code : 1 = IN (pemasukan), 2 = OUT (pengeluaran)
-- Tabel role/permission dihandle oleh Spatie Laravel Permission
-- Tabel media dihandle oleh Spatie Laravel MediaLibrary
-- ==========================================================

-- Pengajuan oleh User (Istri/Anak)
-- trans_code=1 : Pengajuan Dana (fitur 5.5)
-- trans_code=2 : Pengeluaran (fitur 5.3)
-- Partial approve: admin bisa pilih detail mana yang disetujui
-- Upload bukti (gambar/PDF) di-attach via Spatie MediaLibrary (polymorphic)
CREATE TABLE `request_header`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `notes` TEXT NULL COMMENT 'Catatan tambahan/penjelasan opsional dari user',
    `amount` DECIMAL(15,4) NOT NULL COMMENT 'Auto-sum dari request_detail',
    `trans_code` TINYINT UNSIGNED NOT NULL COMMENT '1=IN, 2=OUT',
    `request_date` DATE NOT NULL COMMENT 'Tanggal pengajuan/pengeluaran',
    `created_by` BIGINT UNSIGNED NOT NULL COMMENT 'FK users: user yang mengajukan',
    `priority` ENUM('low','normal','high') NOT NULL DEFAULT 'normal' COMMENT 'Tingkat urgensi pengajuan',
    `status` ENUM('draft','requested','approved','rejected','canceled') NOT NULL DEFAULT 'draft',
    `approved_by` BIGINT UNSIGNED NULL COMMENT 'FK users: admin yang approve/reject',
    `approved_at` TIMESTAMP NULL COMMENT 'Waktu approve/reject',
    `rejection_reason` TEXT NULL COMMENT 'Alasan jika di-reject',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);
-- Transaksi aktual (realisasi)
-- Terbentuk otomatis saat request di-approve, atau input langsung oleh Admin dari template
-- request_id NULL = input langsung (bukan dari request)
CREATE TABLE `transaction_header`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `notes` TEXT NULL COMMENT 'Catatan tambahan admin untuk transaksi riil',
    `amount` DECIMAL(15,4) NOT NULL COMMENT 'Auto-sum dari transaction_detail',
    `request_id` BIGINT UNSIGNED NULL COMMENT 'FK request_header: NULL jika input langsung dari template',
    `trans_code` TINYINT UNSIGNED NOT NULL COMMENT '1=IN, 2=OUT',
    `transaction_date` DATE NOT NULL COMMENT 'Tanggal realisasi transaksi',
    `created_by` BIGINT UNSIGNED NOT NULL COMMENT 'FK users: admin yang membuat',
    `status` ENUM('draft','completed','canceled') NOT NULL DEFAULT 'draft',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);
-- Line item dari request (misal: celana, baju, dll)
CREATE TABLE `request_detail`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `header_id` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(15,4) NOT NULL,
    `status` ENUM('pending','realized','closed') NULL DEFAULT NULL COMMENT 'NULL=ikut header, pending=menunggu realisasi, realized=sudah jadi transaction, closed=write off',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);
-- Line item dari transaction
-- request_detail_id NULL = input langsung dari template, bukan dari request
-- amount bisa beda dari request_detail (admin bisa edit)
CREATE TABLE `transaction_detail`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `header_id` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(15,4) NOT NULL COMMENT 'Amount aktual (bisa beda dari request)',
    `request_detail_id` BIGINT UNSIGNED NULL COMMENT 'FK request_detail: NULL jika dari template',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);
-- User authentication (kompatibel Laravel Breeze)
CREATE TABLE `users`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=nonaktif, 1=aktif',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);
-- Template / preset transaksi berulang (fitur 5.2 Master Uang Masuk + pengeluaran rutin)
-- trans_code=1 : Template pemasukan (gaji, bonus)
-- trans_code=2 : Template pengeluaran (sewa kontrakan, listrik)
CREATE TABLE `template_header`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(15,4) NOT NULL,
    `trans_code` TINYINT UNSIGNED NOT NULL COMMENT '1=IN, 2=OUT',
    `created_by` BIGINT UNSIGNED NOT NULL COMMENT 'FK users: admin yang membuat template',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);
-- Line item dari template
CREATE TABLE `template_detail`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `header_id` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(15,4) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);
-- Notifikasi in-app (fitur 5.7)
-- message berisi HTML, bisa include link
CREATE TABLE `notifications`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL COMMENT 'Isi notifikasi, bisa berisi HTML dengan link',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=belum dibaca, 1=sudah dibaca',
    `read_at` TIMESTAMP NULL COMMENT 'Waktu notifikasi dibaca',
    `created_at` TIMESTAMP NULL
);
-- Kategori transaksi (shared untuk IN dan OUT)
CREATE TABLE `categories`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL
);
-- Tabel `media` TIDAK dibuat manual
-- Dihandle otomatis oleh Spatie Laravel MediaLibrary via migration bawaan library

-- Settings (key-value)
-- Contoh isi:
--   smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption
--   currency (Rp / IDR)
--   timezone (Asia/Jakarta)
CREATE TABLE `settings`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    `value` TEXT NULL
);
-- Rekap saldo bulanan (fitur 5.1 Dashboard)
-- Dikalkulasi otomatis via Laravel observer saat transaction berubah
CREATE TABLE `balance`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `month` VARCHAR(7) NOT NULL UNIQUE COMMENT 'Format: YYYY-MM',
    `begin` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Saldo awal bulan',
    `total_in` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Total pemasukan bulan ini',
    `total_out` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Total pengeluaran bulan ini',
    `ending` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Saldo akhir bulan (begin + total_in - total_out)',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);
-- ==========================================================
-- FOREIGN KEY CONSTRAINTS
-- ==========================================================
ALTER TABLE
    `transaction_detail` ADD CONSTRAINT `transaction_detail_header_id_foreign` FOREIGN KEY(`header_id`) REFERENCES `transaction_header`(`id`);
ALTER TABLE
    `request_header` ADD CONSTRAINT `request_header_category_id_foreign` FOREIGN KEY(`category_id`) REFERENCES `categories`(`id`);
ALTER TABLE
    `request_header` ADD CONSTRAINT `request_header_created_by_foreign` FOREIGN KEY(`created_by`) REFERENCES `users`(`id`);
ALTER TABLE
    `request_header` ADD CONSTRAINT `request_header_approved_by_foreign` FOREIGN KEY(`approved_by`) REFERENCES `users`(`id`);
ALTER TABLE
    `request_detail` ADD CONSTRAINT `request_detail_header_id_foreign` FOREIGN KEY(`header_id`) REFERENCES `request_header`(`id`);
ALTER TABLE
    `transaction_detail` ADD CONSTRAINT `transaction_detail_request_detail_id_foreign` FOREIGN KEY(`request_detail_id`) REFERENCES `request_detail`(`id`);
ALTER TABLE
    `notifications` ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `template_detail` ADD CONSTRAINT `template_detail_header_id_foreign` FOREIGN KEY(`header_id`) REFERENCES `template_header`(`id`);
ALTER TABLE
    `transaction_header` ADD CONSTRAINT `transaction_header_request_id_foreign` FOREIGN KEY(`request_id`) REFERENCES `request_header`(`id`);
ALTER TABLE
    `transaction_header` ADD CONSTRAINT `transaction_header_category_id_foreign` FOREIGN KEY(`category_id`) REFERENCES `categories`(`id`);
ALTER TABLE
    `transaction_header` ADD CONSTRAINT `transaction_header_created_by_foreign` FOREIGN KEY(`created_by`) REFERENCES `users`(`id`);
ALTER TABLE
    `template_header` ADD CONSTRAINT `template_header_category_id_foreign` FOREIGN KEY(`category_id`) REFERENCES `categories`(`id`);
ALTER TABLE
    `template_header` ADD CONSTRAINT `template_header_created_by_foreign` FOREIGN KEY(`created_by`) REFERENCES `users`(`id`);