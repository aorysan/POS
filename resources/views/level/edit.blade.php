@extends('layouts.template')
@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $page->title }}</h3>
    </div>
    <div class="card-body">
        @if(empty($level))
            <div class="alert alert-danger alert-dismissible">
                <h5><i class="icon fas fa-ban"></i> Kesalahan!</h5>
                Data yang Anda cari tidak ditemukan.
            </div>
            <a href="{{ url('level') }}" class="btn btn-sm btn-default mt-2">Kembali</a>
        @else
            <form method="POST" action="{{ url('/level/'.$level->level_id)}}" class="form-horizontal">
                @csrf
                {!! method_field('PUT') !!} <!-- Menggunakan Blade directive untuk method PUT -->

                <div class="form-group row">
                    <label class="col-1 control-label col-form-label">Level ID</label>
                    <div class="col-11">
                        <input type="text" class="form-control" id="level_id" name="level_id" value="{{ old('level_id', $level->level_id) }}" required readonly>
                        @error('level_id')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- Level Kode -->
                <div class="form-group row">
                    <label class="col-1 control-label col-form-label">Level Kode</label>
                    <div class="col-11">
                        <input type="text" class="form-control" id="level_kode" name="level_kode" value="{{ old('level_kode', $level->level_kode) }}" required>
                        @error('level_kode')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- Level Nama -->
                <div class="form-group row">
                    <label class="col-1 control-label col-form-label">Level Nama</label>
                    <div class="col-11">
                        <input type="text" class="form-control" id="level_nama" name="level_nama" value="{{ old('level_nama', $level->level_nama) }}">
                        @error('level_nama')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @else
                            <small class="form-text text-muted">Abaikan jika tidak ingin mengganti nama level.</small>
                        @enderror
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="form-group row">
                    <label class="col-1 control-label col-form-label"></label>
                    <div class="col-11">
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                        <a class="btn btn-sm btn-default ml-1" href="{{ url('level') }}">Kembali</a>
                    </div>
                </div>
            </form> 
        @endif
    </div>
</div>
@endsection

@push('css')
@endpush

@push('js')
@endpush
