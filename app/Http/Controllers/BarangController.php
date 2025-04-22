<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller
{
    public function index()
    {
        $breadcrumb = (object)[
            'title' => 'Daftar Barang',
            'list' => ['Home', 'Barang']
        ];

        $page = (object)[
            'title' => 'Daftar Barang yang sudah dimasukkan'
        ];

        $activeMenu = 'barang'; // Set menu yang sedang aktif

        $kategori = KategoriModel::all(); // ambil data barang untuk filter barang
        return view('barang.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        $barangs = BarangModel::select('barang_id', 'barang_kode', 'barang_nama', 'kategori_id', 'harga_beli', 'harga_jual')
            ->with('kategori');
        // 
        if ($request->kategori_id) {
            $barangs->where('kategori_id', $request->kategori_id);
        }

        return DataTables::of($barangs)
            ->addIndexColumn() // Menambahkan kolom index / no urut (default nmaa kolom: DT_RowINdex)
            ->addColumn('aksi', function ($barang) {
                $btn  = '<a href="' . url('/barang/' . $barang->barang_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                // $btn .= '<a href="' . url('/barang/' . $barang->barang_id . '/edit') . '" class="btn btn-warning btn-sm">Edit</a> ';
                // $btn .= '<form class="d-inline-block" method="POST" action="'. url('/barang/'.$barang->barang_id).'">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah anda yakin menghapus data ini?\');">Hapus</button></form>';
                $btn .= '<button onclick="modalAction(\'' . url('/barang/' . $barang->barang_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/barang/' . $barang->barang_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })

            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(string $id)
    {
        $barang = BarangModel::with('kategori')->find($id);

        $breadcrumb = (object)[
            'title' => 'Detail barang',
            'list' => ['Home', 'barang', 'Detail']
        ];

        $page = (object)[
            'title' => 'Detail Barang'
        ];

        $activeMenu = 'barang';
        return view('barang.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'barang' => $barang, 'activeMenu' => $activeMenu]);
    }

    public function create_ajax()
    {
        $barang = BarangModel::all();

        return view('barang.create_ajax')->with('barang', $barang);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_id'   => 'required',
                'barang_kode'   => 'required',
                'barang_nama'   => 'required',
                'harga_beli'    => 'required',
                'harga_jual'    => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            BarangModel::create($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Data barang berhasil disimpan'
            ]);
        }
        redirect('/');
    }

    public function import()
    {
        return view('barang.import');
    }

    public function import_excel(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_barang' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }
            $file = $request->file('file_barang'); // ambil file dari request
            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($file->getRealPath()); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
            $data = $sheet->toArray(null, false, true, true); // ambil data excel
            $insert = [];
            if (count($data) > 1) { // jika data lebih dari 1 baris
                foreach ($data as $baris => $value) {
                    if ($baris > 1) { // baris ke 1 adalah header, maka lewati
                        $insert[] = [
                            'kategori_id'=> $value['A'],
                            'barang_kode'=> $value['B'],
                            'barang_nama'=> $value['C'],
                            'harga_beli' => $value['D'],
                            'harga_jual' => $value['E'],
                            'created_at' => now(),
                        ];
                    }
                }
                if (count($insert) > 0) {
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    BarangModel::insertOrIgnore($insert);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil diimport'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data yang diimport'
                ]);
            }
        }
        redirect('/');
    }

    public function export_excel(Request $request)
    {
        $barang = BarangModel::select('kategori_id', 'barang_kode', 'barang_nama', 'harga_beli', 'harga_jual')
            ->orderBy('barang_id', 'asc')
            ->with('kategori')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kategori');
        $sheet->setCellValue('C1', 'Kode Barang');
        $sheet->setCellValue('D1', 'Nama Barang');
        $sheet->setCellValue('E1', 'Harga Beli');
        $sheet->setCellValue('F1', 'Harga Jual');

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $no = 1;
        $baris = 2;
        foreach ($barang as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->barang_kode);
            $sheet->setCellValue('C' . $baris, $value->barang_nama);
            $sheet->setCellValue('D' . $baris, $value->harga_beli);
            $sheet->setCellValue('E' . $baris, $value->harga_jual);
            $sheet->setCellValue('F' . $baris, $value->kategori->kategori_nama);
            $baris++;
            $no++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Barang'); // set title sheet

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = 'Data Barang ' . date('Y-m-d H:i:s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
    }

    public function export_pdf()
    {
        // Fetch data from BarangModel
        $barang = BarangModel::select('kategori_id', 'barang_kode', 'barang_nama', 'harga_beli', 'harga_jual')
            ->orderBy('kategori_id')
            ->orderBy('barang_kode')
            ->with('kategori')
            ->get();
    
        // Load view and pass data
        $pdf = Pdf::loadView('/barang/export_pdf', ['barang' => $barang]);
    
        // Set paper size and orientation (optional)
        $pdf->setPaper('A4', 'portrait');
    
        // Stream the PDF to the browser
        return $pdf->stream('Data Barang ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function edit_ajax(string $id)
    {
        $barang = BarangModel::find($id);

        return view('barang.edit_ajax', ['barang' => $barang]);
    }

    public function delete_ajax(Request $request, $id)
    {
        try {
            if ($request->ajax() || $request->wantsJson()) {
                $barang = BarangModel::find($id);
                if ($barang) {
                    $barang->delete();
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

    public function confirm_ajax(string $id)
    {
        $barang = BarangModel::find($id);
        return view('barang.confirm_ajax', ['barang' => $barang]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_id'   => 'required',
                'barang_kode'   => 'required',
                'barang_nama'   => 'required',
                'harga_beli'    => 'required',
                'harga_jual'    => 'required'
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

            $check = BarangModel::find($id);

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
    //         'title' => 'Tambah Barang',
    //         'list' => ['Home', 'Barang', 'Tambah']
    //     ];
    //     $page = (object)[
    //         'title' => 'Tambah Barang baru'
    //     ];
    //     $barang = BarangModel::all();
    //     $kategori = KategoriModel::all(); // ambil data barang untuk ditampilkan di form
    //     $activeMenu = 'barang'; //set menu yang sedang aktif

    //     return view('barang.create', ['breadcrumb' => $breadcrumb, 'page' => $page, 'barang' => $barang, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'kategori_id'   => 'required|integer',
    //         'barang_kode'   => 'required|string|min:3|unique:m_level,level_kode',
    //         'barang_nama'   => 'required|string|max:100',
    //         'harga_beli'    => 'required|integer',
    //         'harga_jual'    => 'required|integer'

    //     ]);

    //     BarangModel::create([
    //         'kategori_id'   => $request->kategori_id,
    //         'barang_kode'   => $request->barang_kode,
    //         'barang_nama'   => $request->barang_nama,
    //         'harga_beli'    => $request->harga_beli,
    //         'harga_jual'    => $request->harga_jual

    //     ]);

    //     return redirect('/barang')->with('success', 'Data Barang berhasil disimpan');
    // }

    // public function edit(string $id)
    // {
    //     $barang = BarangModel::find($id);
    //     $kategori = KategoriModel::all();

    //     $breadcrumb = (object)[
    //         'title' => 'Edit Barang',
    //         'list' => ['Home', 'Barang', 'Edit']
    //     ];

    //     $page = (object)[
    //         'title' => 'Edit Barang'
    //     ];

    //     $activeMenu = 'barang';
    //     return view('barang.edit', ['breadcrumb' => $breadcrumb, 'page' => $page, 'barang' => $barang, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    // }

    // public function update(Request $request, string $id)
    // {
    //     $request->validate([
    //         'barang_kode'   => 'required|string|min:3|unique:m_barang,barang_kode,' . $id . ',barang_id',
    //         'barang_nama'   => 'required|string|max:100',
    //         'kategori_id'   => 'required|integer',
    //         'harga_beli'    => 'required|integer',
    //         'harga_jual'    => 'required|integer'

    //     ]);

    //     BarangModel::find($id)->update([
    //         'barang_kode'   => $request->barang_kode,
    //         'barang_nama'   => $request->barang_nama,
    //         'kategori_id'   => $request->kategori_id,
    //         'harga_beli'    => $request->harga_beli,
    //         'harga_jual'    => $request->harga_jual
    //     ]);

    //     return redirect('/barang')->with('success', 'Data barang berhasil diubah');
    // }

    // public function destroy(string $id)
    // {
    //     $check = BarangModel::find($id);
    //     if (!$check) {
    //         return redirect('/barang')->with('error', 'Data Barang tidak ditemukan');
    //     }

    //     try {
    //         BarangModel::destroy($id); // Hapus data barang

    //         return redirect('/barang')->with('success', 'Data Barang berhasil dihapus');
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         // Jika terjadi error ketika menghapus data , redirect kemabli ke halaman dengan membawa pesan error
    //         return redirect('/barang')->with('error', 'Data barang gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini');
    //     }
    // }
}