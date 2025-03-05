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

    public function index()
    {
        // $data = [
        //     'nama' => 'Pelanggan Pertama',
        // ];
        // UserModel::where('username', 'customer-1') -> update($data);

        // $data = [
        //     'level_id' => 2,
        //     'username' => 'manager_tiga',
        //     'nama' => 'Manager 3',
        //     'password' => Hash::make('12345'),
        // ];

        // UserModel::create($data);

        // $user = UserModel::all();
        // $user = UserModel::find(1);
        // $user = UserModel::where('level_id', 1)->first();
        // $user = UserModel::firstWhere('level_id', 1);
        // $user = UserModel::findOr(1, ['username', 'nama'], function() {
            //     abort(404);
            // });
            // $user = UserModel::findOr(20, ['username', 'nama'], function() {
                //     abort(404);
                // });
                // $user = UserModel::findOrFail(1);
                // $user = UserModel::where('username', 'manager9')->firstOrFail();
                // $user = UserModel::where('level_id', 2)->count();
                // return view('user', ['user' => $user]);
                // dd($user);
                // $user = UserModel::firstOrCreate(
        //     [
        //         'username' => 'manager',
        //         'nama' => 'Manager'
        //     ]
        // );
        // $user = UserModel::firstOrCreate(
        //     [
        //     'username' => 'manager22',
        //     'nama' => 'Manager dua dua',
        //     'password' => Hash::make('12345'),
        //     'level_id' => 2
        //     ]
        // );
        // $user = UserModel::firstOrNew(
        //     [
            //         'username' => 'manager',
            //         'nama' => 'Manager'
            //     ]
            // );
            // $user = UserModel::firstOrNew(
        //     [
            //     'username' => 'manager33',
            //     'nama' => 'Manager tiga tiga',
            //     'password' => Hash::make('12345'),
            //     'level_id' => 2
        //     ]
        // );
        // $user->save();
        // $user = UserModel::create(
            //     [
        //         'username' => 'manager55',
        //         'nama' => 'Manager lima lima',
        //         'password' => Hash::make('12345'),
        //         'level_id' => 2
        //     ]
        // );

        // $user -> username = 'manager56';

        // $user->isDirty();
        // $user->isDirty('username');
        // $user->isDirty('nama');
        // $user->isDirty('nama', 'username');
        
        // $user->isClean();
        // $user->isClean('username');
        // $user->isClean('nama');
        // $user->isClean('nama', 'username');

        // $user -> save();
        
        // $user -> isDirty();
        // $user -> isClean();
        // dd($user)->isDirty();
        
        // $user = UserModel::create(
        //     [
            //         'username' => 'manager11',
            //         'nama' => 'Manager satu satu',
            //         'password' => Hash::make('12345'),
            //         'level_id' => 2
        //     ]
        // );

        // $user -> username = 'manager12';
        
        // $user -> save();
        
        // $user -> wasChanged();
        // $user -> wasChanged('username');
        // $user -> wasChanged(['username', 'level_id']);
        // $user -> wasChanged('nama');
        // dd($user)->wasChanged(['nama', 'username']);
        
        // $user = UserModel::all();
        $user = UserModel::with('level') -> get();
        // dd($user);
        return view('user', ['data' => $user]);
    }

    public function tambah() {
        return view('user_tambah');
    }

    public function tambah_simpan(Request $request) {
        UserModel::create([
            'username' => $request->username,
            'nama' => $request->nama,
            'password' => Hash::make($request->password),
            'level_id' => $request->level_id
        ]);
        return redirect('/user');
    }

    public function ubah($id) {
        $user = UserModel::find($id);
        return view('user_ubah', ['data' => $user]);
    }

    public function ubah_simpan($id, Request $request) {
        $user = UserModel::find($id);

        $user -> username = $request->username;
        $user -> nama = $request->nama;
        $user -> password = Hash::make($request->password);
        $user -> level_id = $request->level_id;

        $user -> save();

        return redirect('/user');
    }

    public function hapus($id) {
        $user = UserModel::find($id);
        $user -> delete();
        return redirect('/user');
    }
}