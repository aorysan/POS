<?php

namespace App\Http\Controllers\Api;

use App\Models\PenjualanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class PenjualanController extends Controller
{
    // Menampilkan semua data penjualan
    public function index()
    {
        $penjualans = PenjualanModel::all();
        return response()->json($penjualans);
    }

    // Menyimpan data penjualan baru
    public function store(Request $request)
    {
        $penjualan = PenjualanModel::create($request->all());
        return response()->json($penjualan, 201);
    }
    
    // Menampilkan detail data penjualan berdasarkan ID
    public function show(PenjualanModel $penjualan)
    {
        return $penjualan;
    }
    
    // Mengupdate data penjualan berdasarkan ID
    public function update(Request $request, PenjualanModel $penjualan)
    {
        $penjualan->update($request->all());
        return $penjualan;
    }
    
    // Menghapus data penjualan berdasarkan ID
    public function destroy(PenjualanModel $penjualan)
    {
        $penjualan->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'Data terhapus',
        ]);
    }
}