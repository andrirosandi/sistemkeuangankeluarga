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
            'Gaji',
            'Bonus / THR',
            'Hasil Usaha',
            
            // Pengeluaran Rutin
            'Makan & Minum',
            'Belanja Bulanan',
            'Listrik & Air',
            'Internet & Pulsa',
            
            // Pengeluaran Lainnya
            'Transportasi',
            'Kesehatan',
            'Hiburan / Self Reward',
            'Pendidikan',
            'Cicilan / Hutang',
            
            // Simpanan
            'Tabungan / Investasi',
            'Dana Darurat',
            'Sedekah / Zakat',
            
            // Lain-lain
            'Lain-lain',
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }
    }
}
