<?php

namespace App\Http\Controllers;

use App\Models\PenjualanModel;
use App\Models\DetailModel;
use App\Models\UserModel;
use App\Models\BarangModel;
use App\Models\StokModel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PenjualanController extends Controller
{
    public function index()
    {
        $breadcrumb = (object)[
            'title' => 'Daftar Penjualan Barang',
            'list' => ['Home', 'Penjualan']
        ];

        $page = (object)[
            'title' => 'Daftar Penjualan'
        ];

        $activeMenu = 'penjualan'; // Set menu yang sedang aktif
        $user = UserModel::all(); // ambil data user untuk filter user

        return view('penjualan.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'user' => $user,  'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        // Select the columns that match the table in the image
        $penjualans = PenjualanModel::select(
            'penjualan_id',
            'user_id',
            'penjualan_kode',
            'pembeli',
            'penjualan_tanggal',
        )
            ->with('user'); // Load the user relationship to display user data

        // Filter by user_id if provided
        if ($request->user_id) {
            $penjualans->where('user_id', $request->user_id);
        }

        return DataTables::of($penjualans)
            ->addIndexColumn() // Add index column (DT_RowIndex)
            ->addColumn('aksi', function ($penjualan) {
                $btn = '<a href="' . url('/penjualan/' . $penjualan->penjualan_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi']) // Allow HTML in the 'aksi' column
            ->make(true);
    }

    public function show(string $id)
    {
        $penjualan = PenjualanModel::with('details')->findOrFail($id);
    
        $breadcrumb = (object)[
            'title' => 'Detail Penjualan Barang',
            'list' => ['Home', 'penjualan', 'Detail']
        ];
    
        $page = (object)[
            'title' => 'Detail Penjualan Barang'
        ];
    
        $activeMenu = 'penjualan';
        return view('penjualan.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'penjualan' => $penjualan, 'activeMenu' => $activeMenu]);
    }

    public function create_ajax()
    {
        $users = UserModel::all(); // Retrieve all users
        $barangs = BarangModel::all(); // Retrieve all barang
        $currentUserId = Auth::user()->user_id; // Get the authenticated user's ID
    
        return view('penjualan.create_ajax', compact('users', 'barangs', 'currentUserId'));
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'user_id' => 'required|exists:m_user,user_id',
                'penjualan_kode' => 'required',
                'pembeli' => 'required',
                'penjualan_tanggal' => 'required',
                'barang_id.*' => 'required|exists:m_barang,barang_id',
                'detail_harga.*' => 'required|numeric',
                'detail_jumlah.*' => 'required|numeric',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors()
                ]);
            }
    
            // Start a new database transaction
            DB::beginTransaction();
    
            try {
                $penjualan = PenjualanModel::create([
                    'user_id' => $request->user_id,
                    'penjualan_kode' => $request->penjualan_kode,
                    'pembeli' => $request->pembeli,
                    'penjualan_tanggal' => $request->penjualan_tanggal,
                ]);
    
                if (!$penjualan) {
                    throw new \Exception('Failed to create penjualan.');
                }
    
                foreach ($request->barang_id as $index => $barangId) {
                    $stok = StokModel::where('barang_id', $barangId)->first();
                    if (!$stok || $stok->stok_jumlah < $request->detail_jumlah[$index]) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Stok tidak mencukupi untuk barang dengan ID ' . $barangId,
                            'redirect' => 'penjualan/tambah'
                        ]);
                    }
    
                    $detail = DetailModel::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'barang_id' => $barangId,
                        'detail_harga' => $request->detail_harga[$index],
                        'detail_jumlah' => $request->detail_jumlah[$index],
                    ]);
    
                    if (!$detail) {
                        throw new \Exception('Failed to create detail for barang ID ' . $barangId);
                    }
    
                    // Decrease stock
                    $stok->stok_jumlah -= $request->detail_jumlah[$index];
                    if (!$stok->save()) {
                        throw new \Exception('Failed to update stock for barang ID ' . $barangId);
                    }
                }
    
                // Commit the transaction
                DB::commit();
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data penjualan berhasil disimpan'
                ]);
            } catch (\Exception $e) {
                // Rollback the transaction in case of error
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'redirect' => 'penjualan/tambah'
                ]);
            }
        }
        redirect('/');
    }

    public function import()
    {
        return view('penjualan.import');
    }
    public function export_excel(Request $request)
    {
        $penjualan = PenjualanModel::select('user_id', 'penjualan_kode', 'pembeli', 'penjualan_tanggal')
            ->orderBy('user_id', 'asc')
            ->with('user')
            ->get();
    
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Username');
        $sheet->setCellValue('C1', 'Kode Penjualan');
        $sheet->setCellValue('D1', 'Pembeli');
        $sheet->setCellValue('E1', 'Tanggal Penjualan');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    
        $no = 1;
        $baris = 2;
        foreach ($penjualan as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->user->username);
            $sheet->setCellValue('C' . $baris, $value->penjualan_kode);
            $sheet->setCellValue('D' . $baris, $value->pembeli);
            $sheet->setCellValue('E' . $baris, $value->penjualan_tanggal);
            $baris++;
            $no++;
        }
    
        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $sheet->setTitle('Data Penjualan Barang');
    
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = 'Data Penjualan ' . date('Y-m-d H:i:s') . '.xlsx';
    
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
        $penjualan = PenjualanModel::select('user_id', 'penjualan_kode', 'pembeli', 'penjualan_tanggal')
            ->orderBy('user_id', 'asc')
            ->with('user')
            ->get();
    
        $pdf = Pdf::loadView('/penjualan/export_pdf', ['penjualan' => $penjualan]);
        $pdf->setPaper('A4', 'portrait');
    
        return $pdf->stream('Data Penjualan ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function edit_ajax(string $id)
    {
        $penjualan = PenjualanModel::with('details')->find($id);
        $barangs = BarangModel::all(); // Retrieve all existing barang
    
        if (!$penjualan) {
            return view('penjualan.edit_ajax'); // Show empty state if not found
        }
        return view('penjualan.edit_ajax', ['penjualan' => $penjualan, 'barangs' => $barangs]);
    }

    public function delete_ajax(Request $request, $id)
    {
        try {
            if ($request->ajax() || $request->wantsJson()) {
                $penjualan = PenjualanModel::find($id);
                if ($penjualan) {
                    // Delete all related details
                    $penjualan->details()->delete();
                    // Delete the penjualan itself
                    $penjualan->delete();
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
        $penjualan = PenjualanModel::find($id);
        return view('penjualan.confirm_ajax', ['penjualan' => $penjualan]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'user_id' => 'required|exists:m_user,user_id',
                'penjualan_kode' => 'required',
                'pembeli' => 'required',
                'penjualan_tanggal' => 'required|date',
                'barang_id.*' => 'required|exists:m_barang,barang_id',
                'detail_harga.*' => 'required|numeric',
                'detail_jumlah.*' => 'required|numeric',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {    
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors()
                ]);
            }
    
            $penjualan = PenjualanModel::find($id);
            if ($penjualan) {
                // Get old details
                $oldDetails = $penjualan->details;
    
                // Update penjualan
                $penjualan->update([
                    'user_id' => $request->user_id,
                    'penjualan_kode' => $request->penjualan_kode,
                    'pembeli' => $request->pembeli,
                    'penjualan_tanggal' => $request->penjualan_tanggal,
                ]);
    
                // Delete existing details
                $penjualan->details()->delete();
    
                // Insert new details
                foreach ($request->barang_id as $index => $barangId) {
                    DetailModel::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'barang_id' => $barangId,
                        'detail_harga' => $request->detail_harga[$index],
                        'detail_jumlah' => $request->detail_jumlah[$index],
                    ]);
    
                    // Adjust stock
                    $stok = StokModel::where('barang_id', $barangId)->first();
                    if ($stok) {
                        // Find old detail for this barang_id
                        $oldDetail = $oldDetails->where('barang_id', $barangId)->first();
                        if ($oldDetail) {
                            // Adjust stock based on difference
                            $stok->stok_jumlah += $oldDetail->detail_jumlah - $request->detail_jumlah[$index];
                        } else {
                            // New barang, decrease stock
                            $stok->stok_jumlah -= $request->detail_jumlah[$index];
                        }
                        $stok->save();
                    }
                }
    
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
    //     $penjualan = PenjualanModel::all();
    //     $kategori = KategoriModel::all(); // ambil data penjualan untuk ditampilkan di form
    //     $activeMenu = 'penjualan'; //set menu yang sedang aktif

    //     return view('penjualan.create', ['breadcrumb' => $breadcrumb, 'page' => $page, 'penjualan' => $penjualan, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
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

    //     PenjualanModel::create([
    //         'kategori_id'   => $request->kategori_id,
    //         'barang_kode'   => $request->barang_kode,
    //         'barang_nama'   => $request->barang_nama,
    //         'harga_beli'    => $request->harga_beli,
    //         'harga_jual'    => $request->harga_jual

    //     ]);

    //     return redirect('/penjualan')->with('success', 'Data Barang berhasil disimpan');
    // }

    // public function edit(string $id)
    // {
    //     $penjualan = PenjualanModel::find($id);
    //     $kategori = KategoriModel::all();

    //     $breadcrumb = (object)[
    //         'title' => 'Edit Barang',
    //         'list' => ['Home', 'Barang', 'Edit']
    //     ];

    //     $page = (object)[
    //         'title' => 'Edit Barang'
    //     ];

    //     $activeMenu = 'penjualan';
    //     return view('penjualan.edit', ['breadcrumb' => $breadcrumb, 'page' => $page, 'penjualan' => $penjualan, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    // }

    // public function update(Request $request, string $id)
    // {
    //     $request->validate([
    //         'barang_kode'   => 'required|string|min:3|unique:m,barang_kode,' . $id . ',barang_id',
    //         'barang_nama'   => 'required|string|max:100',
    //         'kategori_id'   => 'required|integer',
    //         'harga_beli'    => 'required|integer',
    //         'harga_jual'    => 'required|integer'

    //     ]);

    //     PenjualanModel::find($id)->update([
    //         'barang_kode'   => $request->barang_kode,
    //         'barang_nama'   => $request->barang_nama,
    //         'kategori_id'   => $request->kategori_id,
    //         'harga_beli'    => $request->harga_beli,
    //         'harga_jual'    => $request->harga_jual
    //     ]);

    //     return redirect('/penjualan')->with('success', 'Data penjualan berhasil diubah');
    // }

    // public function destroy(string $id)
    // {
    //     $check = PenjualanModel::find($id);
    //     if (!$check) {
    //         return redirect('/penjualan')->with('error', 'Data Barang tidak ditemukan');
    //     }

    //     try {
    //         PenjualanModel::destroy($id); // Hapus data penjualan

    //         return redirect('/penjualan')->with('success', 'Data Barang berhasil dihapus');
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         // Jika terjadi error ketika menghapus data , redirect kemabli ke halaman dengan membawa pesan error
    //         return redirect('/penjualan')->with('error', 'Data penjualan gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini');
    //     }
    // }
}