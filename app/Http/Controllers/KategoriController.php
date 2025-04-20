<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\KategoriModel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;

class KategoriController extends Controller
{
    public function index()
    {
        $breadcrumb = (object)[
            'title' => 'Daftar Kategori',
            'list' => ['Home', 'Kategori']
        ];

        $page = (object)[
            'title' => 'Daftar Kategori yang ada'
        ];

        $activeMenu = 'kategori'; // Set menu yang sedang aktif

        $kategori = KategoriModel::all(); // ambil data kategori untuk filter kategori
        return view('kategori.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        $kategoris = KategoriModel::select('kategori_id', 'kategori_kode', 'kategori_nama');

        // 
        if ($request->kategori_id) {
            $kategoris->where('kategori_id', $request->kategori_id);
        }

        return DataTables::of($kategoris)
            ->addIndexColumn() // Menambahkan kolom index / no urut (default nmaa kolom: DT_RowINdex)
            ->addColumn('aksi', function ($kategori) {
                $btn = '<a href="' . url('/kategori/' . $kategori->kategori_id) . '" class="btn btn-info btn-sm">Detail</a>';
                // $btn .= '<a href="' . url('/kategori/' . $kategori->kategori_id . '/edit') . '" class="btn btn-warning btn-sm">Edit</a> ';
                // $btn .= '<form class="d-inline-block" method="POST" action="' . url('/kategori/' . $kategori->kategori_id) . '">' . csrf_field() . method_field('DELETE')
                //     . '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakit menghapus data 
                // ini?\');">Hapus</button></form>';

                // $btn = '<button onclick="modalAction(\'' . url('/kategori/' . $kategori->kategori_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/kategori/' . $kategori->kategori_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/kategori/' . $kategori->kategori_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })

            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(string $id)
    {
        $kategori = KategoriModel::find($id);

        $breadcrumb = (object)[
            'title' => 'Detail kategori',
            'list' => ['Home', 'kategori', 'Detail']
        ];

        $page = (object)[
            'title' => 'Detail Kategori'
        ];

        $activeMenu = 'kategori';
        return view('kategori.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }


        public function create_ajax()
    {
        $kategori = KategoriModel::all();

        return view('kategori.create_ajax')->with('kategori', $kategori);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_kode' => 'required',
                'kategori_nama' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            KategoriModel::create($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Data kategori berhasil disimpan'
            ]);
        }
        redirect('/');
    }

    public function import()
    {
        return view('kategori.import');
    }

    public function import_excel(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_kategori' => ['required', 'mimes:xlsx', 'max:1024']
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_kategori');
            
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
                            'kategori_kode' => $value['A'],
                            'kategori_nama' => $value['B'],
                            'created_at' => now(),
                        ];
                    }
                }

                if (count($insert) > 0) {
                    KategoriModel::insertOrIgnore($insert);
                    return response()->json([
                        'status' => true,
                        'message' => 'Data kategori berhasil diimport'
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
        $kategori = KategoriModel::select('kategori_id', 'kategori_kode', 'kategori_nama')
            ->orderBy('kategori_id', 'asc')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Kategori');
        $sheet->setCellValue('C1', 'Nama Kategori');

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $no = 1;
        $baris = 2;
        foreach ($kategori as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->kategori_kode);
            $sheet->setCellValue('C' . $baris, $value->kategori_nama);
            $baris++;
            $no++;
        }
        
        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $sheet->setTitle('Data Kategori');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = 'Data Kategori ' . date('Y-m-d H:i:s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
    }

    public function export_pdf(Request $request) {
        $kategori = KategoriModel::select('kategori_id', 'kategori_kode', 'kategori_nama')
            ->orderBy('kategori_id', 'asc')
            ->get();

        $pdf = PDF::loadView('/kategori/export_pdf', ['kategori' => $kategori]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Data Kategori ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function edit_ajax(string $id)
    {
        $kategori = KategoriModel::find($id);

        return view('kategori.edit_ajax', ['kategori' => $kategori]);
    }

    public function delete_ajax(Request $request, $id) {
        try {
            if($request->ajax() || $request->wantsJson()) {
                $kategori = KategoriModel::find($id);
                if ($kategori) {
                    $kategori->delete();
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
        $kategori = KategoriModel::find($id);
        return view('kategori.confirm_ajax', ['kategori' => $kategori]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_kode' => 'required',
                'kategori_nama' => 'required'
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
            
            $check = KategoriModel::find($id);
            
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
    
    // public function create()
    // {
    //     $breadcrumb = (object)[
    //         'title' => 'Tambah Kategori',
    //         'list' => ['Home', 'Kategori', 'Tambah']
    //     ];
    //     $page = (object)[
    //         'title' => 'Tambah Kategori baru'
    //     ];

    //     $kategori = KategoriModel::all(); // ambil data kategori untuk ditampilkan di form
    //     $activeMenu = 'kategori'; //set menu yang sedang aktif

    //     return view('kategori.create', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    // }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'kategori_kode'   => 'required|string|min:3|unique:m_kategori,kategori_kode',
    //         'kategori_nama'   => 'required|string|max:100',

    //     ]);

    //     KategoriModel::create([
    //         'kategori_kode'    => $request->kategori_kode,
    //         'kategori_nama'    => $request->kategori_nama,

    //     ]);

    //     return redirect('/kategori')->with('success', 'Data Kategori berhasil disimpan');
    // }
    

    // public function edit(string $id)
    // {
    //     $kategori = KategoriModel::find($id);

    //     $breadcrumb = (object)[
    //         'title' => 'Edit Kategori',
    //         'list' => ['Home', 'Kategori', 'Edit']
    //     ];

    //     $page = (object)[
    //         'title' => 'Edit Kategori'
    //     ];

    //     $activeMenu = 'kategori';
    //     return view('kategori.edit', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    // }

    // public function update(Request $request, string $id)
    // {
    //     $request->validate([
    //         'kategori_kode'    => 'required|string|min:3|unique:m_kategori,kategori_kode,' . $id . ',kategori_id',
    //         'kategori_nama'    => 'required|string|max:100',

    //     ]);

    //     KategoriModel::find($id)->update([
    //         'kategori_kode'    => $request->kategori_kode,
    //         'kategori_nama'    => $request->kategori_nama,

    //     ]);

    //     return redirect('/kategori')->with('success', 'Data kategori berhasil diubah');
    // }

    // public function destroy(string $id)
    // {
    //     $check = KategoriModel::find($id);
    //     if (!$check) {
    //         return redirect('/kategori')->with('error', 'Data Kategori tidak ditemukan');
    //     }

    //     try {
    //         KategoriModel::destroy($id); // Hapus data kategori

    //         return redirect('/kategori')->with('success', 'Data Kategori berhasil dihapus');
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         // Jika terjadi error ketika menghapus data , redirect kemabli ke halaman dengan membawa pesan error
    //         return redirect('/kategori')->with('errror', 'Data kategori gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini');
    //     }
    // }

}