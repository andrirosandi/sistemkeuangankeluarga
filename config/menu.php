<?php

return [
    'sidebar' => [
        [
            'label'      => 'Dashboard',
            'icon'       => 'layout-dashboard',
            'route'      => 'dashboard',
            'permission' => 'dashboard.view',
        ],
        [
            'label' => 'Pemasukan',
            'icon'  => 'trending-up',
            'children' => [
                [
                    'label'      => 'Pengajuan Dana',
                    'route'      => 'in.request.index',
                    'permission' => 'in.request.view',
                ],
                [
                    'label'      => 'Riwayat Masuk',
                    'route'      => 'in.transaction.index',
                    'permission' => 'in.transaction.view',
                ],
            ],
        ],
        [
            'label' => 'Pengeluaran',
            'icon'  => 'trending-down',
            'children' => [
                [
                    'label'      => 'Pengajuan Belanja',
                    'route'      => 'out.request.index',
                    'permission' => 'out.request.view',
                ],
                [
                    'label'      => 'Riwayat Keluar',
                    'route'      => 'out.transaction.index',
                    'permission' => 'out.transaction.view',
                ],
            ],
        ],
        [
            'label'      => 'Mutasi Kas',
            'icon'       => 'arrows-exchange',
            'route'      => 'mutation.index',
            'permission' => 'mutation.view',
        ],
        [
            'label'      => 'Laporan',
            'icon'       => 'report-analytics',
            'route'      => 'report.index',
            'permission' => 'report.view',
        ],
        [
            'label' => 'Master',
            'icon'  => 'database',
            'children' => [
                [
                    'label'      => 'Kategori Kas',
                    'route'      => 'master.categories.index',
                    'permission' => 'category.view',
                ],
                [
                    'label'      => 'Template Rutin',
                    'route'      => 'master.template.index',
                    'permission' => 'template.view',
                ],
            ],
        ],
        [
            'label' => 'Pengaturan',
            'icon'  => 'settings',
            'children' => [
                [
                    'label'      => 'Anggota Keluarga',
                    'route'      => 'master.users.index',
                    'permission' => 'user.view',
                ],
                [
                    'label'      => 'Hak Akses (RBAC)',
                    'route'      => 'master.group.index',
                    'permission' => 'role.view',
                ],
                [
                    'label'      => 'Sistem',
                    'route'      => 'settings.index',
                    'permission' => 'setting.view',
                ],
            ],
        ],
    ],
];
