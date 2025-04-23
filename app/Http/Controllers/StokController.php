<?php

namespace App\Http\Controllers;

use App\Models\StokModel;
use App\Models\UserModel;
use App\Models\SupplierModel;
use App\Models\BarangModel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class StokController extends Controller
{
    public function index()
    {
        $breadcrumb = (object)[
            'title' => 'Daftar Stok Barang',
            'list' => ['Home', 'Stok']
        ];

        $page = (object)[
            'title' => 'Daftar Barang yang sudah dimasukkan'
        ];

        $activeMenu = 'stok'; // Set menu yang sedang aktif
        $supplier = SupplierModel::all(); // ambil data supplier untuk filter supplier

        return view('stok.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'supplier' => $supplier,  'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        $stoks = StokModel::select('user_id', 'barang_id', 'supplier_id', 'stok_jumlah', 'stok_tanggal', 'stok_id') // Add 'stok_tanggal' here
                ->with('supplier', 'barang', 'user');
    
        if ($request->supplier_id) {
            $stoks->where('supplier_id', $request->supplier_id);
        }
    
        return DataTables::of($stoks)
            ->addIndexColumn() // Add index column (DT_RowIndex)
            ->addColumn('aksi', function ($stok) {
                $btn  = '<a href="' . url('/stok/' . $stok->barang_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(string $id)
    {
        $stok = StokModel::findOrFail($id);

        $breadcrumb = (object)[
            'title' => 'Detail Stok Barang',
            'list' => ['Home', 'stok', 'Detail']
        ];

        $page = (object)[
            'title' => 'Detail Stok Barang'
        ];

        $activeMenu = 'stok';
        return view('stok.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'stok' => $stok, 'activeMenu' => $activeMenu]);
    }

    // StokController.php
    public function create_ajax()
    {
        $users = UserModel::all(); // Retrieve all users
        $barangs = BarangModel::all(); // Retrieve all barangs
        $suppliers = SupplierModel::all(); // Retrieve all suppliers
        return view('stok.create_ajax', compact('users', 'barangs', 'suppliers'));
    }

    // StokController.php
    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'barang_id'     => 'required|exists:m_barang,barang_id',
                'supplier_id'   => 'required|exists:m_supplier,supplier_id',
                'stok_tanggal'  => 'required|date',
                'stok_jumlah'   => 'required|integer',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'msgField' => $validator->errors()
                ]);
            }
    
            // Automatically use the authenticated user's ID
            $request->merge(['user_id' => Auth::user()->user_id]);
    
            // Check if the stock already exists for the given barang_id and supplier_id
            $existingStock = StokModel::where('barang_id', $request->barang_id)
                                      ->where('supplier_id', $request->supplier_id)
                                      ->first();
    
            if ($existingStock) {
                // Update the existing stock
                $existingStock->stok_jumlah += $request->stok_jumlah;
                $existingStock->stok_tanggal = $request->stok_tanggal;
                $existingStock->save();
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data stok berhasil diperbarui',
                    'data' => $existingStock
                ]);
            } else {
                // Create a new record
                $stok = StokModel::create([
                    'user_id'       => $request->input('user_id'),
                    'barang_id'     => $request->input('barang_id'),
                    'supplier_id'   => $request->input('supplier_id'),
                    'stok_tanggal'  => $request->input('stok_tanggal'),
                    'stok_jumlah'   => $request->input('stok_jumlah'),
                ]);
    
                if ($stok) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data stok berhasil disimpan',
                        'data' => $stok
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data stok gagal disimpan'
                    ]);
                }
            }
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid request'
        ]);
    }

    public function import()
    {
        return view('stok.import');
    }

    public function import_excel(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_stok' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Failed',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_stok');
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
                            'user_id'       => $value['A'],
                            'barang_id'     => $value['B'],
                            'supplier_id'   => $value['C'],
                            'stok_jumlah'   => $value['D'],
                            'stok_tanggal'  => isset($value['E']) ? date('Y-m-d H:i:s', strtotime($value['E'])) : now(),
                        ];
                    }
                }
            }

            if (count($insert) > 0) {
                try {
                    StokModel::upsert($insert, ['user_id', 'barang_id', 'supplier_id'], ['stok_jumlah', 'stok_tanggal']);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Import failed: ' . $e->getMessage()
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No valid data to import'
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data successfully imported'
            ]);
        }
        redirect('/');
    }

    public function export_excel(Request $request)
    {
        $stok = StokModel::select('user_id', 'barang_id', 'supplier_id', 'stok_jumlah', 'stok_tanggal')
            ->orderBy('user_id', 'asc')
            ->with('barang', 'supplier', 'user')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'user_id');
        $sheet->setCellValue('C1', 'barang_id');
        $sheet->setCellValue('D1', 'supplier_id');
        $sheet->setCellValue('E1', 'stok_jumlah');
        $sheet->setCellValue('F1', 'stok_tanggal');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $no = 1;
        $baris = 2;
        foreach ($stok as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->user->user_id);
            $sheet->setCellValue('C' . $baris, $value->barang->barang_id);
            $sheet->setCellValue('D' . $baris, $value->supplier->supplier_id);
            $sheet->setCellValue('E' . $baris, $value->stok_jumlah);
            $sheet->setCellValue('F' . $baris, $value->stok_tanggal);
            $baris++;
            $no++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Stok Barang'); // set title sheet

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = 'Data Stok ' . date('Y-m-d H:i:s') . '.xlsx';

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
        // Fetch data from StokModel
        $stok = StokModel::select('user_id', 'barang_id', 'supplier_id', 'stok_jumlah')
            ->orderBy('user_id', 'asc')
            ->with('supplier', 'barang', 'user')
            ->get();

        // Load view and pass data
        $pdf = Pdf::loadView('/stok/export_pdf', ['stok' => $stok]);

        // Set paper size and orientation (optional)
        $pdf->setPaper('A4', 'portrait');

        // Stream the PDF to the browser
        return $pdf->stream('Data Stok Barang ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function edit_ajax(string $id)
    {
        $stok = StokModel::find($id);

        return view('stok.edit_ajax', ['stok' => $stok]);
    }

    public function delete_ajax(Request $request, $id)
    {
        try {
            if ($request->ajax() || $request->wantsJson()) {
                $stok = StokModel::find($id);
                if ($stok) {
                    $stok->delete();
                    return response()->json([
                        'status' => true,
                        'message' => 'Data stok berhasil dihapus'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data stok tidak ditemukan'
                    ]);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data stok gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
            ]);
        }
    }

    public function confirm_ajax(string $id)
    {
        $stok = StokModel::find($id);
        if (!$stok) {
            return view('stok.confirm_ajax'); // Show empty state if not found
        }
        return view('stok.confirm_ajax', ['stok' => $stok]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'barang_id'     => 'required|exists:m_barang,barang_id',
                'supplier_id'   => 'required|exists:m_supplier,supplier_id',
                'stok_tanggal'  => 'required|date',
                'stok_jumlah'   => 'required|integer',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'msgField' => $validator->errors()
                ]);
            }
    
            $stok = StokModel::find($id);
    
            if (!$stok) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data stok tidak ditemukan'
                ]);
            }
    
            // Update the existing stock
            $stok->stok_jumlah = $request->stok_jumlah;
            $stok->stok_tanggal = $request->stok_tanggal;
            $stok->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Data stok berhasil diperbarui',
                'data' => $stok
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid request'
        ]);
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
    //     $stok = StokModel::all();
    //     $kategori = KategoriModel::all(); // ambil data stok untuk ditampilkan di form
    //     $activeMenu = 'stok'; //set menu yang sedang aktif

    //     return view('stok.create', ['breadcrumb' => $breadcrumb, 'page' => $page, 'stok' => $stok, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
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

    //     StokModel::create([
    //         'kategori_id'   => $request->kategori_id,
    //         'barang_kode'   => $request->barang_kode,
    //         'barang_nama'   => $request->barang_nama,
    //         'harga_beli'    => $request->harga_beli,
    //         'harga_jual'    => $request->harga_jual

    //     ]);

    //     return redirect('/stok')->with('success', 'Data Barang berhasil disimpan');
    // }

    // public function edit(string $id)
    // {
    //     $stok = StokModel::find($id);
    //     $kategori = KategoriModel::all();

    //     $breadcrumb = (object)[
    //         'title' => 'Edit Barang',
    //         'list' => ['Home', 'Barang', 'Edit']
    //     ];

    //     $page = (object)[
    //         'title' => 'Edit Barang'
    //     ];

    //     $activeMenu = 'stok';
    //     return view('stok.edit', ['breadcrumb' => $breadcrumb, 'page' => $page, 'stok' => $stok, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
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

    //     StokModel::find($id)->update([
    //         'barang_kode'   => $request->barang_kode,
    //         'barang_nama'   => $request->barang_nama,
    //         'kategori_id'   => $request->kategori_id,
    //         'harga_beli'    => $request->harga_beli,
    //         'harga_jual'    => $request->harga_jual
    //     ]);

    //     return redirect('/stok')->with('success', 'Data stok berhasil diubah');
    // }

    // public function destroy(string $id)
    // {
    //     $check = StokModel::find($id);
    //     if (!$check) {
    //         return redirect('/stok')->with('error', 'Data Barang tidak ditemukan');
    //     }

    //     try {
    //         StokModel::destroy($id); // Hapus data stok

    //         return redirect('/stok')->with('success', 'Data Barang berhasil dihapus');
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         // Jika terjadi error ketika menghapus data , redirect kemabli ke halaman dengan membawa pesan error
    //         return redirect('/stok')->with('error', 'Data stok gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini');
    //     }
    // }
}