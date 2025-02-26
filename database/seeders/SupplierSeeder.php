<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('m_supplier')->insert([
            [
                'supplier_kode' => 'SUP001',
                'supplier_nama' => 'PT. Sumber Berkah',
                'supplier_alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_kode' => 'SUP002',
                'supplier_nama' => 'CV. Makmur Jaya',
                'supplier_alamat' => 'Jl. Sudirman No. 25, Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_kode' => 'SUP003',
                'supplier_nama' => 'UD. Sejahtera',
                'supplier_alamat' => 'Jl. Diponegoro No. 5, Surabaya',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}