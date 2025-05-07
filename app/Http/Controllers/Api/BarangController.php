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
        // Validate image
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'required',
            'barang_kode' => 'required',
            'barang_nama' => 'required',
            'harga_beli' => 'required',
            'harga_jual' => 'required',
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
        // Validate image
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'required',
            'barang_kode' => 'required',
            'barang_nama' => 'required',
            'harga_beli' => 'required',
            'harga_jual' => 'required',
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