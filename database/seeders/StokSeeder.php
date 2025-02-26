<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('t_stok')->insert([
            ['barang_id' => 1, 'supplier_id' => 1, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 2, 'supplier_id' => 2, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 3, 'supplier_id' => 3, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 4, 'supplier_id' => 1, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 5, 'supplier_id' => 2, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 6, 'supplier_id' => 3, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 7, 'supplier_id' => 1, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 8, 'supplier_id' => 2, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 9, 'supplier_id' => 3, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 10, 'supplier_id' => 1, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 16, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 11, 'supplier_id' => 2, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 28, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 12, 'supplier_id' => 3, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 19, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 13, 'supplier_id' => 1, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 14, 'supplier_id' => 2, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['barang_id' => 15, 'supplier_id' => 3, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 27, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}