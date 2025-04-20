<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use Illuminate\Http\Request;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use lluminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;


class UserController extends Controller
{

    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar User',
            'list' => ['Home', 'User']
        ];

        $page = (object) [
            'title' => 'Daftar user yang terdaftar dalam sistem'
        ];

        $activeMenu = 'user';

        $level = LevelModel::all();

        return view('user.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'level' => $level, 'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        $users = UserModel::select('user_id', 'username', 'nama', 'level_id')->with('level');

        if ($request->level_id) {
            $users->where('level_id', $request->level_id);
        }

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('aksi', function ($user) {
                $btn = '<a href="' . url('/user/' . $user->user_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                // $btn .= '<a href="' . url('/user/' . $user->user_id . '/edit') . '" class="btn btn-warning btn-sm">Edit</a> ';
                // $btn .= '<form class="d-inline-block" method="POST" action="' . url('/user/' . $user->user_id) . '">'
                //     . csrf_field() . method_field('DELETE') . 
                //     '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin menghapus data ini?\');">Hapus</button>
                // </form>';

                // $btn = '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(string $id)
    {
        $user = UserModel::find($id);

        $breadcrumb = (object) [
            'title' => 'Detail User',
            'list' => ['Home', 'User', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail user'
        ];

        $activeMenu = 'user';

        return view('user.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'user' => $user, 'activeMenu' => $activeMenu]);
    }

    public function import(){
        return view('user.import');
    }

    public function import_excel(Request $request) {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_user' => ['required', 'mimes:xlsx', 'max:1024']
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_user');
            
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            
            $data = $sheet->toArray(null, false, true, true);

            $insert = [];
            if (count($data) > 1) {
                foreach ($data as $baris => $value) {
                    if ($baris > 1) {
                        $insert[] = [
                            'level_id' => $value['A'],
                            'username' => $value['B'],
                            'nama' => $value['C'],
                            'password' => ($value['D'])
                        ];
                    }
                }

                if (count($insert) > 0) {
                    UserModel::insertOrIgnore($insert);
                    return response()->json([
                        'status' => true,
                        'message' => 'Data user berhasil diimport'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Tidak ada data yang diimport'
                    ]);
                }
            }
        }
        redirect('/');
    }

    public function export_excel(Request $request) {
        $user = UserModel::select('username', 'nama', 'level_id')
            ->orderBy('user_id', 'asc')
            ->with('level')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Username');
        $sheet->setCellValue('C1', 'Nama User');
        $sheet->setCellValue('D1', 'Level');

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $no = 1;
        $baris = 2;
        foreach ($user as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->username);
            $sheet->setCellValue('C' . $baris, $value->nama);
            $sheet->setCellValue('D' . $baris, $value->level->level_kode);
            $baris++;
            $no++;
        }
        
        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $sheet->setTitle('Data User');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = 'Data User ' . date('Y-m-d H:i:s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
    }

    public function export_pdf(Request $request) {
        $user = UserModel::select('username', 'nama', 'level_id') 
            ->orderBy('level_id', 'asc')
            ->with('level')
            ->get();

        $pdf = PDF::loadView('/user/export_pdf', ['user' => $user]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Data User ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function create_ajax()
    {
        $level = LevelModel::all();

        return view('user.create_ajax')->with('level', $level);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_id' => 'required|integer',
                'username' => 'required|string|min:3|unique:m_user,username',
                'nama' => 'required|string|max:100',
                'password' => 'required|string|min:6'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            // UserModel::create($request->all());
            $user = new UserModel();
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->nama = $request->nama;
            $user->level_id = $request->level_id;
            $user->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil disimpan'
            ]);
        }
        redirect('/');
    }

    public function edit_ajax(string $id)
    {
        $user = UserModel::find($id);
        $level = LevelModel::all();

        return view('user.edit_ajax', ['user' => $user, 'level' => $level]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_id' => 'required|integer',
                'username' => 'required|max:20|unique:m_user,username,' . $id . ',user_id',
                'nama' => 'required|max:100',
                'password' => 'nullable|min:6|max:20'
            ];
            // use Illuminate\Support\Facades\Validator;
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false, // respon json, true: berhasil, false: gagal
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors() // menunjukkan field mana yang error
                ]);
            }
            $check = UserModel::find($id);
            if ($check) {
                if (!$request->filled('password')) { // jika password tidak diisi, maka hapus dari request
                    $request->request->remove('password');
                }
                $check->update($request->all());
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil diupdate'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }
        redirect('./user');
    }

    public function delete_ajax(Request $request, $id) {
        try {
            if($request->ajax() || $request->wantsJson()) {
                $user = UserModel::find($id);
                if ($user) {
                    $user->delete();
                    return response()->json([
                        'status' => true,
                        'message' => 'Data berhasil dihapus'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data tidak ditemukan'
                    ]);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
            ]);
        }
    }

    public function confirm_ajax(string $id) {
        $user = UserModel::find($id);

        return view('user.confirm_ajax', ['user' => $user]);
    }

    // public function create()
    // {
    //     $breadcrumb = (object) [
    //         'title' => 'Tambah User',
    //         'list' => ['Home', 'User']
    //     ];

    //     $page = (object) [
    //         'title' => 'Tambah user baru'
    //     ];

    //     $level = LevelModel::all();
    //     $activeMenu = 'user';

    //     return view('user.create', ['breadcrumb' => $breadcrumb, 'page' => $page, 'level' => $level, 'activeMenu' => $activeMenu]);
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'username' => 'required',
    //         'password' => 'required',
    //         'nama' => 'required',
    //         'level_id' => 'required'
    //     ]);

    //     $user = new UserModel();
    //     $user->username = $request->username;
    //     $user->password = Hash::make($request->password);
    //     $user->nama = $request->nama;
    //     $user->level_id = $request->level_id;
    //     $user->save();

    //     return redirect('/user')->with('success', 'Data user berhasil disimpan');
    // }

    // public function show_ajax(string $id)
    // {
    //     $breadcrumb = (object) [
    //         'title' => 'Detail User',
    //         'list' => ['Home', 'User', 'Detail']
    //     ];
    
    //     $page = (object) [
    //         'title' => 'Detail user'
    //     ];
    
    //     $activeMenu = 'user';
    
    //     return view('user.show_ajax', [
    //         'breadcrumb' => $breadcrumb,
    //         'page' => $page,
    //         'id' => $id,
    //         'activeMenu' => $activeMenu
    //     ]);
    // }

    // public function edit(string $id)
    // {
    //     $user = UserModel::find($id);

    //     $breadcrumb = (object) [
    //         'title' => 'Edit User',
    //         'list' => ['Home', 'User', 'Edit']
    //     ];

    //     $page = (object) [
    //         'title' => 'Edit user'
    //     ];

    //     $level = LevelModel::all();
    //     $activeMenu = 'user';

    //     return view('user.edit', ['breadcrumb' => $breadcrumb, 'page' => $page, 'user' => $user, 'level' => $level, 'activeMenu' => $activeMenu]);
    // }
    
    // public function update(Request $request, string $id)
    // {
    //     $request->validate([
    //         'username' => 'required|string|min:3|unique:m_user,username,' . $id . ',user_id',
    //         'nama' => 'required|string|max:100',
    //         'password' => 'required|min:5',
    //         'level_id' => 'required|integer'
    //     ]);

    //     $user = UserModel::find($id);
    //     $user->username = $request->username;
    //     $user->nama = $request->nama;
    //     $user->password = $request->password ? bcrypt($request->password) : UserModel::find($id)->password;
    //     $user->level_id = $request->level_id;
    //     $user->save();

    //     return redirect('/user')->with('success', 'Data user berhasil disimpan');
    // }
    
    // public function destroy(string $id)
    // {
    //     $check = UserModel::find($id);
    //     if (!$check) {
    //         return redirect('/user')->with('error', 'Data user tidak ditemukan');
    //     }

    //     try {
    //         UserModel::destroy($id); // Hapus data level
    //         return redirect('/user')->with('success', 'Data user berhasil dihapus');
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         // Jika terjadi error ketika menghapus data, redirect kembali ke halaman dengan membawa pesan error
    //         return redirect('/user')->with('error', 'Data user gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini');
    //     }
    // }
}