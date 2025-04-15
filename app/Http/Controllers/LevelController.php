<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use Illuminate\Http\Request;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;

class LevelController extends Controller
{

    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Level',
            'list' => ['Home', 'Level']
        ];

        $page = (object) [
            'title' => 'Daftar level yang terdaftar dalam sistem'
        ];

        $activeMenu = 'level';

        // Ambil data level dari tabel level
        $level = LevelModel::select('level_id', 'level_kode', 'level_nama')->get();

        return view('level.index', compact('breadcrumb', 'page', 'level', 'activeMenu'));
    }

    public function list(Request $request)
    {
        $levels = LevelModel::select('level_id', 'level_kode', 'level_nama');

        return DataTables::of($levels)
            ->addIndexColumn()
            ->addColumn('aksi', function ($level) {
                // $btn = '<a href="' . url('/level/' . $level->level_id . '/edit') . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Edit</a>';
                $btn = '<a href="' . url('/level/' . $level->level_id) . '" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Detail</a>';
                // $btn .= '<form class="d-inline-block" method="POST" action="' . url('/level/' . $level->level_id) . '">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin menghapus data ini?\');"><i class="fa fa-trash"></i> Hapus</button></form>';
                // $btn = '<button onclick="modalAction(\'' . url('/level/' . $level->level_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/level/' . $level->level_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/level/' . $level->level_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;

            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(string $id)
    {
        $level = LevelModel::find($id);

        $breadcrumb = (object) [
            'title' => 'Detail Level',
            'list' => ['Home', 'Level', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail Level'
        ];

        $activeMenu = 'level';

        return view('level.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'level' => $level, 'activeMenu' => $activeMenu]);
    }

    public function create_ajax()
    {
        $level = LevelModel::all();

        return view('level.create_ajax')->with('level', $level);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_id' => 'required',
                'level_kode' => 'required',
                'level_nama' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            LevelModel::create($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Data level berhasil disimpan'
            ]);
        }
        redirect('/');
    }

    public function import() {
        return view('level.import');
    }

    public function import_excel(Request $request) {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_level' => ['required', 'mimes:xlsx', 'max:1024']
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_level');
            
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
                            'level_kode' => $value['B'],
                            'level_nama' => $value['C']
                        ];
                    }
                }

                if (count($insert) > 0) {
                    LevelModel::insertOrIgnore($insert);
                    return response()->json([
                        'status' => true,
                        'message' => 'Data level berhasil diimport'
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
        $level = LevelModel::select('level_id', 'level_kode', 'level_nama')
            ->orderBy('level_id', 'asc')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'ID Level');
        $sheet->setCellValue('C1', 'Kode Level');
        $sheet->setCellValue('D1', 'Nama Level');

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $no = 1;
        $baris = 2;
        foreach ($level as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->level_id);
            $sheet->setCellValue('C' . $baris, $value->level_kode);
            $sheet->setCellValue('D' . $baris, $value->level_nama);
            $baris++;
            $no++;
        }
        
        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $sheet->setTitle('Data Level');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = 'Data Level ' . date('Y-m-d H:i:s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
    }

    public function export_pdf(Request $request) {
        $level = LevelModel::select('level_id', 'level_kode', 'level_nama')
            ->orderBy('level_id', 'asc')
            ->get();

        $pdf = PDF::loadView('/level/export_pdf', ['level' => $level]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Data Level ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function edit_ajax(string $id)
    {
        $level = LevelModel::find($id);

        return view('level.edit_ajax', ['level' => $level]);
    }

    public function delete_ajax(Request $request, $id) {
        try {
            if($request->ajax() || $request->wantsJson()) {
                $level = LevelModel::find($id);
                if ($level) {
                    $level->delete();
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
        } catch(\Illuminate\Database\QueryException $e) {
            return response()->json([    
                'status' => false,
                'message' => 'Data gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
            ]);
        }
    }

    public function confirm_ajax(string $id) {
        $level = LevelModel::find($id);
        return view('level.confirm_ajax', ['level' => $level]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_id' => 'required',
                'level_kode' => 'required',
                'level_nama' => 'required'
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
            
            $check = LevelModel::find($id);
            
            if ($check) {
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
        redirect('/');
    }

        // public function edit(string $id)
    // {
    //     $level = LevelModel::find($id);

    //     // Cek jika data tidak ditemukan
    //     if (!$level) {
    //         return redirect()->route('/level')->with('error', 'Level tidak ditemukan.');
    //     }

    //     $breadcrumb = (object) [
    //         'title' => 'Edit Level',
    //         'list' => ['Home', 'Level', 'Edit']
    //     ];

    //     $page = (object) [
    //         'title' => 'Edit Level'
    //     ];

    //     $activeMenu = 'level';

    //     return view('level.edit', ['breadcrumb' => $breadcrumb, 'page' => $page, 'level' => $level, 'activeMenu' => $activeMenu]);
    // }

    // public function update(Request $request, string $id) {
    //     $request->validate([
    //         'level_id' => 'required|integer',
    //         'level_nama' => 'required|string|max:100',
    //         'level_kode' => 'required'
    //     ]);

    //     $level = LevelModel::find($id);
    //     $level->level_nama = $request->level_nama;
    //     $level->level_kode = $request->level_kode;
    //     $level->level_id = $request->level_id;
    //     $level->save();

    //     return redirect('/level')->with('success', 'Data level berhasil disimpan');    
    // }

    // public function destroy(string $id)
    // {
    //     $check = LevelModel::find($id);
    //     if (!$check) {
    //         return redirect('/level')->with('error', 'Data level tidak ditemukan');
    //     }

    //     try {
    //         LevelModel::destroy($id); // Hapus data level
    //         return redirect('/level')->with('success', 'Data level berhasil dihapus');
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         // Jika terjadi error ketika menghapus data, redirect kembali ke halaman dengan membawa pesan error
    //         return redirect('/level')->with('error', 'Data level gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini');
    //     }
    // }

    
    // public function create()
    // {
    //     $breadcrumb = (object) [
    //         'title' => 'Tambah User',
    //         'list' => ['Home', 'level']
    //     ];

    //     $page = (object) [
    //         'title' => 'Tambah level baru'
    //     ];

    //     $level = LevelModel::all();
    //     $activeMenu = 'level';

    //     return view('level.create', ['breadcrumb' => $breadcrumb, 'page' => $page, 'level' => $level, 'activeMenu' => $activeMenu]);
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'level_id' => 'required|integer',
    //         'level_nama' => 'required',
    //         'level_kode' => 'required'
    //     ]);

    //     $level = new LevelModel();
    //     $level->level_nama = $request->level_nama;
    //     $level->level_kode = $request->level_kode;
    //     $level->level_id = $request->level_id;
    //     $level->save();

    //     return redirect('/level')->with('success', 'Data level berhasil disimpan');
    // }
}