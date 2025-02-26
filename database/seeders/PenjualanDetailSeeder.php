<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PenjualanDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $details = [];
        for ($i = 1; $i <= 10; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                $details[] = [
                    'penjualan_id' => $i,
                    'barang_id' => $j,
                    'detail_harga' => rand(5000, 50000),
                    'detail_jumlah' => rand(1, 5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('t_penjualan_detail')->insert($details);
    }
}