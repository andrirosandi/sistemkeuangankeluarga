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
                    'label'      => 'Realisasi',
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
                    'label'      => 'Realisasi',
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
            'label'      => 'Perlu Tindakan',
            'icon'       => 'alert-circle',
            'route'      => 'outstanding.index',
            'permission' => 'outstanding.view',
        ],
        [
            'label'      => 'Laporan',
            'icon'       => 'report-analytics',
            'route'      => 'report.index',
            'permission' => 'report.view.self',
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
                    'route'      => 'master.templates.index',
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
                    'label'      => 'Peran & Akses',
                    'route'      => 'master.roles.index',
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
