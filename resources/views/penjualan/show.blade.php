@extends('layouts.template')
@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools"></div>
        </div>
        <div class="card-body">
            @empty($penjualan)
                <div class="alert alert-danger alert-dismissible">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!</h5>
                    Data yang Anda cari tidak ditemukan.
                </div>
            @else
                <table class="table table-bordered table-striped table-hover table-sm">
                    <tr>
                        <th>ID Penjualan</th>
                        <td>{{ $penjualan->penjualan_kode }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td>{{ $penjualan->user->username }}</td>
                    </tr>
                    <tr>
                        <th>Pembeli</th>
                        <td>{{ $penjualan->pembeli }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Penjualan</th>
                        <td>{{ $penjualan->penjualan_tanggal }}</td>
                    </tr>
                </table>
                <h4>Detail Penjualan</h4>
                <table class="table table-bordered table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualan->details as $detail)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $detail->barang->barang_nama }}</td>
                                <td>{{ $detail->detail_harga }}</td>
                                <td>{{ $detail->detail_jumlah }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endempty
            <a href="{{ url('/penjualan_barang') }}" class="btn btn-sm btn-default mt-2">Kembali</a>
        </div>
    </div>
@endsection
@push('css')
@endpush
@push('js')
@endpush