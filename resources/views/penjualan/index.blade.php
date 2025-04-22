@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card=title">{{$page->title}}</h3>
            <div class="card-tools">
                <button onclick="modalAction('{{ url('/penjualan/import') }}')" class="btn btn-info">Import Data</button>
                <a href="{{ url('/penjualan/export_excel') }}" class="btn btn-primary"><i class="fa fa-fileexcel"></i>Export Data(Excel)</a>
                <a href="{{ url('/penjualan/export_pdf') }}" class="btn btn-warning"><i class="fa fa-filepdf"></i> Export Data(PDF)</a>
                <button onclick="modalAction('{{ url('/penjualan/create_ajax') }}')" class="btn btn-success">Tambah Data(Ajax)</button>
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
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">- Semua -</option>
                                @foreach($user as $item)
                                    <option value="{{$item->user_id}}">{{$item->username}}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Kategori user</small>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-bordered table-striped table-hover table-sm" id="table_penjualan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>User</th>
                        <th>Kode Penjualan</th>
                        <th>Pembeli</th>
                        <th>Tanggal Penjualan</th>
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

        var dataPenjualan;

        $(document).ready(function () {
            dataPenjualan = $('#table_penjualan').DataTable({
                serverSide: true, // serverSide: true, jika ingin menggunakan server side processing
                ajax: {
                    "url": "{{ url('penjualan/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d) {
                        d.pembeli = $('#pembeli').val();
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
                        data: "penjualan_kode",
                        className: "",
                        orderable: true,
                        searchable: true
                    }, {
                        data: "pembeli",
                        className: "",
                        orderable: false,
                        searchable: false
                    }, {
                        data: "penjualan_tanggal",
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
            $('#user_id').on('change', function () {
                dataPenjualan.ajax.reload();
            })
        });
    </script>
@endpush