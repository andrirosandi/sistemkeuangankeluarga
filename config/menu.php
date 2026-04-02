<?php

return [
    'sidebar' => [
        [
            'label'      => 'Dashboard',
            'icon'       => 'home',
            'route'      => 'dashboard',
            'permission' => 'dashboard.view',
        ],
        [
            'label' => 'Kas Masuk',
            'icon'  => 'trending-up',
            'children' => [
                [
                    'label'      => 'Pengajuan',
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
            'label' => 'Kas Keluar',
            'icon'  => 'trending-down',
            'children' => [
                [
                    'label'      => 'Pengajuan',
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
            'label'      => 'Mutasi',
            'icon'       => 'arrows-exchange',
            'route'      => 'mutation.index',
            'permission' => 'mutation.view',
        ],
        [
            'label'      => 'Laporan & Analitik',
            'icon'       => 'chart-bar',
            'route'      => 'report.index',
            'permission' => 'report.view',
        ],
        [
            'label' => 'Master Data & Pengaturan',
            'icon'  => 'settings',
            'children' => [
                [
                    'label'      => 'Kategori Kas',
                    'route'      => 'master.category.index',
                    'permission' => 'category.view',
                ],
                [
                    'label'      => 'Template Rutin',
                    'route'      => 'master.template.index',
                    'permission' => 'template.view',
                ],
                [
                    'label'      => 'Manajemen Pengguna',
                    'route'      => 'master.user.index',
                    'permission' => 'user.view',
                ],
                [
                    'label'      => 'Group & Akses',
                    'route'      => 'master.group.index',
                    'permission' => 'role.view',
                ],
                [
                    'label'      => 'Pengaturan Sistem',
                    'route'      => 'settings.index',
                    'permission' => 'setting.view',
                ],
            ],
        ],
    ],
];
