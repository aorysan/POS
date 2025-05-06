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
        // Validate image
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'penjualan_kode' => 'required',
            'pembeli' => 'required',
            'penjualan_tanggal' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->hashName();
            $request->file('image')->store('public/posts');
        }

        $penjualan = PenjualanModel::create($data);
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
        // Validate image
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'penjualan_kode' => 'required',
            'pembeli' => 'required',
            'penjualan_tanggal' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->hashName();
            $request->file('image')->store('public/posts');
        }

        $penjualan->update($data);
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