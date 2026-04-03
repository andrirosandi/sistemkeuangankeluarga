<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Pemasukan
            ['name' => 'Gaji', 'color' => '#10B981'],
            ['name' => 'Bonus / THR', 'color' => '#34D399'],
            ['name' => 'Hasil Usaha', 'color' => '#059669'],

            // Pengeluaran Rutin
            ['name' => 'Makan & Minum', 'color' => '#F59E0B'],
            ['name' => 'Belanja Bulanan', 'color' => '#F97316'],
            ['name' => 'Listrik & Air', 'color' => '#3B82F6'],
            ['name' => 'Internet & Pulsa', 'color' => '#6366F1'],

            // Pengeluaran Lainnya
            ['name' => 'Transportasi', 'color' => '#EF4444'],
            ['name' => 'Kesehatan', 'color' => '#EC4899'],
            ['name' => 'Hiburan / Self Reward', 'color' => '#8B5CF6'],
            ['name' => 'Pendidikan', 'color' => '#14B8A6'],
            ['name' => 'Cicilan / Hutang', 'color' => '#DC2626'],

            // Simpanan
            ['name' => 'Tabungan / Investasi', 'color' => '#0EA5E9'],
            ['name' => 'Dana Darurat', 'color' => '#64748B'],
            ['name' => 'Sedekah / Zakat', 'color' => '#84CC16'],

            // Lain-lain
            ['name' => 'Lain-lain', 'color' => '#616876'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], ['color' => $cat['color']]);
        }
    }
}
