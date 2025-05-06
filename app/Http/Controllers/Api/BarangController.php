<?php

namespace App\Http\Controllers\Api;

use App\Models\BarangModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    // Menampilkan semua data barang
    public function index()
    {
        $barangs = BarangModel::all();
        return response()->json($barangs);
    }

    // Menyimpan data barang baru
    public function store(Request $request)
    {
        $barang = BarangModel::create($request->all());
        return response()->json($barang, 201);
    }
    
    // Menampilkan detail data barang berdasarkan ID
    public function show(BarangModel $barang)
    {
        return $barang;
    }
    
    // Mengupdate data barang berdasarkan ID
    public function update(Request $request, BarangModel $barang)
    {
        $barang->update($request->all());
        return $barang;
    }
    
    // Menghapus data barang berdasarkan ID
    public function destroy(BarangModel $barang)
    {
        $barang->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'Data terhapus',
        ]);
    }
}