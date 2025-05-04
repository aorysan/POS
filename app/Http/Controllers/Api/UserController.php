<?php

namespace App\Http\Controllers\Api;

use App\Models\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    // Menampilkan semua data user
    public function index()
    {
        $users = UserModel::all();
        return response()->json($users);
    }

    // Menyimpan data user baru
    public function store(Request $request)
    {
        $user = UserModel::create($request->all());
        return response()->json($user, 201);
    }
    
    // Menampilkan detail data user berdasarkan ID
    public function show(UserModel $user)
    {
        return $user;
    }
    
    // Mengupdate data user berdasarkan ID
    public function update(Request $request, UserModel $user)
    {
        $user->update($request->all());
        return $user;
    }
    
    // Menghapus data user berdasarkan ID
    public function destroy(UserModel $user)
    {
        $user->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'Data terhapus',
        ]);
    }
}