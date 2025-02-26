<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile($id, $name)
    {
        return view('user', compact('id', 'name'));
    }

    public function index() {
        // $data = [
        //     'nama' => 'Pelanggan Pertama',
        // ];
        // UserModel::where('username', 'customer-1') -> update($data);

        $user = UserModel::all();
        return view('user', ['data' => $user]);
    }
}