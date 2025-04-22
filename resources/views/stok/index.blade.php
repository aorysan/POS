@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card=title">{{$page->title}}</h3>
            <div class="card-tools">
                <button onclick="modalAction('{{ url('/stok/import') }}')" class="btn btn-info">Import Barang</button>
                <a href="{{ url('/stok/export_excel') }}" class="btn btn-primary"><i class="fa fa-fileexcel"></i>Export
                    Barang(Excel)</a>
                <a href="{{ url('/stok/export_pdf') }}" class="btn btn-warning"><i class="fa fa-filepdf"></i> Export
                    Barang(PDF)</a>
                <button onclick="modalAction('{{ url('/stok/create_ajax') }}')" class="btn btn-success">Tambah Stok</button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{session('success')}}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{session('error')}}</div>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-1 control-label col-form-label">Filter:</label>
                        <div class="col-3">
                            <select name="supplier_id" id="supplier_id" class="form-control" required>
                                <option value="">- Semua -</option>
                                @foreach($supplier as $item)
                                    <option value="{{$item->supplier_id}}">{{$item->supplier_nama}}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Kategori supplier</small>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-bordered table-striped table-hover table-sm" id="table_stok">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Supplier Barang</th>
                        <th>Stok Barang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" databackdrop="static"
        data-keyboard="false" data-width="75%" aria-hidden="true"></div>

@endsection
@push('css')
@endpush

@push('js')
    <script>
        function modalAction(url = '') {
            $('#myModal').load(url, function () {
                $('#myModal').modal('show');
            });
        }
        var dataStok;

        $(document).ready(function () {
            dataStok = $('#table_stok').DataTable({
                serverSide: true,
                ajax: {
                    "url": "{{ url('stok/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d) {
                        d.supplier_id = $('#supplier_id').val();
                    }
                },
                columns: [
                    {
                        data: "DT_RowIndex",
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    }, {
                        data: "user.username",
                        className: "",
                        orderable: true,
                        searchable: true
                    }, {
                        data: "barang.barang_kode",
                        className: "",
                        orderable: true,
                        searchable: true
                    }, {
                        data: "barang.barang_nama",
                        className: "",
                        orderable: true,
                        searchable: true
                    }, {
                        data: "supplier.supplier_nama",
                        className: "",
                        orderable: false,
                        searchable: false
                    }, {
                        data: "stok_jumlah",
                        className: "",
                        orderable: false,
                        searchable: false
                    }, {
                        data: "aksi",
                        className: "",
                        orderable: false,
                        searchable: false
                    }
                ]
            });
            $('#supplier_id').on('change', function () {
                dataStok.ajax.reload();
            });
        });

    </script>
@endpush