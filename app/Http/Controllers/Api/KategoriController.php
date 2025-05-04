<?php

namespace App\Http\Controllers\Api;

use App\Models\KategoriModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KategoriController extends Controller
{
    // Menampilkan semua data kategori
    public function index()
    {
        $kategoris = KategoriModel::all();
        return response()->json($kategoris);
    }

    // Menyimpan data kategori baru
    public function store(Request $request)
    {
        $kategori = KategoriModel::create($request->all());
        return response()->json($kategori, 201);
    }
    
    // Menampilkan detail data kategori berdasarkan ID
    public function show(KategoriModel $kategori)
    {
        return $kategori;
    }
    
    // Mengupdate data kategori berdasarkan ID
    public function update(Request $request, KategoriModel $kategori)
    {
        $kategori->update($request->all());
        return $kategori;
    }
    
    // Menghapus data kategori berdasarkan ID
    public function destroy(KategoriModel $kategori)
    {
        $kategori->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'Data terhapus',
        ]);
    }
}