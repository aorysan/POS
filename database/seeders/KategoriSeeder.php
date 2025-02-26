<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('m_kategori')->insert([
            [
                'kategori_kode' => 'ELK001',
                'kategori_nama' => 'Elektronik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kategori_kode' => 'PHS002',
                'kategori_nama' => 'Pakaian & Fashion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kategori_kode' => 'MKN003',
                'kategori_nama' => 'Makanan & Minuman',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kategori_kode' => 'KMP004',
                'kategori_nama' => 'Komputer & Aksesori',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kategori_kode' => 'OTM005',
                'kategori_nama' => 'Otomotif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}