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
            ['kategori_id' => 1, 'barang_kode' => 'BRG001', 'barang_nama' => 'Laptop ASUS', 'harga_beli' => 5000000, 'harga_jual' => 6000000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 1, 'barang_kode' => 'BRG002', 'barang_nama' => 'Smartphone Samsung', 'harga_beli' => 3000000, 'harga_jual' => 3500000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 1, 'barang_kode' => 'BRG011', 'barang_nama' => 'Tablet Lenovo', 'harga_beli' => 2500000, 'harga_jual' => 3000000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 2, 'barang_kode' => 'BRG003', 'barang_nama' => 'Jaket Kulit', 'harga_beli' => 200000, 'harga_jual' => 300000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 2, 'barang_kode' => 'BRG004', 'barang_nama' => 'Sepatu Sneakers', 'harga_beli' => 400000, 'harga_jual' => 500000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 2, 'barang_kode' => 'BRG012', 'barang_nama' => 'Kemeja Batik', 'harga_beli' => 150000, 'harga_jual' => 200000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 3, 'barang_kode' => 'BRG005', 'barang_nama' => 'Mie Instan', 'harga_beli' => 2500, 'harga_jual' => 3500, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 3, 'barang_kode' => 'BRG006', 'barang_nama' => 'Biskuit Coklat', 'harga_beli' => 5000, 'harga_jual' => 7000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 3, 'barang_kode' => 'BRG013', 'barang_nama' => 'Susu UHT', 'harga_beli' => 10000, 'harga_jual' => 15000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 4, 'barang_kode' => 'BRG007', 'barang_nama' => 'Mouse Logitech', 'harga_beli' => 150000, 'harga_jual' => 200000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 4, 'barang_kode' => 'BRG008', 'barang_nama' => 'Keyboard Mechanical', 'harga_beli' => 300000, 'harga_jual' => 400000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 4, 'barang_kode' => 'BRG014', 'barang_nama' => 'Flashdisk 32GB', 'harga_beli' => 50000, 'harga_jual' => 70000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 5, 'barang_kode' => 'BRG009', 'barang_nama' => 'Oli Motor', 'harga_beli' => 50000, 'harga_jual' => 70000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 5, 'barang_kode' => 'BRG010', 'barang_nama' => 'Ban Mobil', 'harga_beli' => 800000, 'harga_jual' => 1000000, 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => 5, 'barang_kode' => 'BRG015', 'barang_nama' => 'Aki Mobil', 'harga_beli' => 900000, 'harga_jual' => 1100000, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
