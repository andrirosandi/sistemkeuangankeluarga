<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seluruh daftar permissions sistem.
     * Pola penamaan: [modul].[action]
     */
    private array $permissions = [
        // Dashboard
        'dashboard.view',

        // Kas Masuk - Pengajuan
        'in.request.view',
        'in.request.create',
        'in.request.edit',
        'in.request.delete',
        'in.request.approve',

        // Kas Masuk - Realisasi / Transaksi
        'in.transaction.view',
        'in.transaction.create',
        'in.transaction.edit',
        'in.transaction.delete',

        // Kas Keluar - Pengajuan
        'out.request.view',
        'out.request.create',
        'out.request.edit',
        'out.request.delete',
        'out.request.approve',

        // Kas Keluar - Realisasi / Transaksi
        'out.transaction.view',
        'out.transaction.create',
        'out.transaction.edit',
        'out.transaction.delete',

        // Mutasi Kas
        'mutation.view',

        // Laporan & Analitik
        'report.view',
        'report.export',

        // Master Data: Kategori
        'category.view',
        'category.create',
        'category.edit',
        'category.delete',

        // Master Data: Template Rutin
        'template.view',
        'template.create',
        'template.edit',
        'template.delete',

        // Master Data: Manajemen Pengguna
        'user.view',
        'user.create',
        'user.edit',
        'user.delete',
        'user.reset-password',

        // Master Data: Role & Akses
        'role.view',
        'role.create',
        'role.edit',
        'role.delete',

        // Pengaturan Sistem
        'setting.view',
        'setting.edit',

        // Notifikasi
        'notification.view',
    ];

    /**
     * Permissions yang boleh dimiliki role 'user' (Istri/Anak).
     */
    private array $userPermissions = [
        'dashboard.view',
        // Kas Masuk - Pengajuan (user hanya bisa CRUD milik sendiri, bukan approve)
        'in.request.view',
        'in.request.create',
        'in.request.edit',
        'in.request.delete',
        // Kas Keluar - Pengajuan
        'out.request.view',
        'out.request.create',
        'out.request.edit',
        'out.request.delete',
        // Mutasi (read-only)
        'mutation.view',
        // Notifikasi milik sendiri
        'notification.view',
    ];

    public function run(): void
    {
        // Reset cache Spatie untuk mencegah konflik
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create semua permissions
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('✅ ' . count($this->permissions) . ' permissions created.');

        // 2. Create role Admin → berikan SEMUA permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        $this->command->info('✅ Role [admin] created & assigned all permissions.');

        // 3. Create role User → berikan subset permissions terbatas
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions($this->userPermissions);

        $this->command->info('✅ Role [user] created & assigned ' . count($this->userPermissions) . ' permissions.');
    }
}
