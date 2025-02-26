<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('t_penjualan')->insert([
            ['user_id' => 1, 'penjualan_kode' => 'PJ001', 'pembeli' => 'Andi', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'penjualan_kode' => 'PJ002', 'pembeli' => 'Budi', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'penjualan_kode' => 'PJ003', 'pembeli' => 'Citra', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 1, 'penjualan_kode' => 'PJ004', 'pembeli' => 'Dewi', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'penjualan_kode' => 'PJ005', 'pembeli' => 'Eko', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'penjualan_kode' => 'PJ006', 'pembeli' => 'Fajar', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 1, 'penjualan_kode' => 'PJ007', 'pembeli' => 'Gina', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'penjualan_kode' => 'PJ008', 'pembeli' => 'Hadi', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'penjualan_kode' => 'PJ009', 'pembeli' => 'Indah', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 1, 'penjualan_kode' => 'PJ010', 'pembeli' => 'Joko', 'penjualan_tanggal' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}