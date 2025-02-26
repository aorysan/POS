<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('m_barang')->insert([
            ['kategori_id' => 1, 'barang_kode' => 'BRG001', 'barang_nama' => 'Laptop ASUS', 'barang_beli' => 5000000, 'barang_jual' => 6000000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 1, 'barang_kode' => 'BRG002', 'barang_nama' => 'Smartphone Samsung', 'barang_beli' => 3000000, 'barang_jual' => 3500000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 1, 'barang_kode' => 'BRG011', 'barang_nama' => 'Tablet Lenovo', 'barang_beli' => 2500000, 'barang_jual' => 3000000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 2, 'barang_kode' => 'BRG003', 'barang_nama' => 'Jaket Kulit', 'barang_beli' => 200000, 'barang_jual' => 300000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 2, 'barang_kode' => 'BRG004', 'barang_nama' => 'Sepatu Sneakers', 'barang_beli' => 400000, 'barang_jual' => 500000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 2, 'barang_kode' => 'BRG012', 'barang_nama' => 'Kemeja Batik', 'barang_beli' => 150000, 'barang_jual' => 200000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 3, 'barang_kode' => 'BRG005', 'barang_nama' => 'Mie Instan', 'barang_beli' => 2500, 'barang_jual' => 3500, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 3, 'barang_kode' => 'BRG006', 'barang_nama' => 'Biskuit Coklat', 'barang_beli' => 5000, 'barang_jual' => 7000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 3, 'barang_kode' => 'BRG013', 'barang_nama' => 'Susu UHT', 'barang_beli' => 10000, 'barang_jual' => 15000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 4, 'barang_kode' => 'BRG007', 'barang_nama' => 'Mouse Logitech', 'barang_beli' => 150000, 'barang_jual' => 200000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 4, 'barang_kode' => 'BRG008', 'barang_nama' => 'Keyboard Mechanical', 'barang_beli' => 300000, 'barang_jual' => 400000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 4, 'barang_kode' => 'BRG014', 'barang_nama' => 'Flashdisk 32GB', 'barang_beli' => 50000, 'barang_jual' => 70000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 5, 'barang_kode' => 'BRG009', 'barang_nama' => 'Oli Motor', 'barang_beli' => 50000, 'barang_jual' => 70000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 5, 'barang_kode' => 'BRG010', 'barang_nama' => 'Ban Mobil', 'barang_beli' => 800000, 'barang_jual' => 1000000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 5, 'barang_kode' => 'BRG015', 'barang_nama' => 'Aki Mobil', 'barang_beli' => 900000, 'barang_jual' => 1100000, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}