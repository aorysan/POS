@empty($penjualan)
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Kesalahan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!!!</h5>
                    Data yang anda cari tidak ditemukan
                </div>
                <a href="{{ url('/penjualan') }}" class="btn btn-warning">Kembali</a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/penjualan/' . $penjualan->penjualan_id . '/update_ajax') }}" method="POST" id="form-edit">
        @csrf
        @method('PUT')
        <div id="modal-master" class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Data Penjualan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>User</label>
                        <input value="{{ $penjualan->user->username }}" type="text" name="username" id="username" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Kode Penjualan</label>
                        <input value="{{ $penjualan->penjualan_kode }}" type="text" name="penjualan_kode" id="penjualan_kode" class="form-control">
                        <small id="error-penjualan_kode" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Pembeli</label>
                        <input value="{{ $penjualan->pembeli }}" type="text" name="pembeli" id="pembeli" class="form-control">
                        <small id="error-pembeli" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Penjualan</label>
                        <input type="date" name="penjualan_tanggal" id="penjualan_tanggal" class="form-control" value="{{ $penjualan->penjualan_tanggal ? \Carbon\Carbon::parse($penjualan->penjualan_tanggal)->format('Y-m-d') : '' }}">
                        <small id="error-penjualan_tanggal" class="error-text form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Detail Penjualan</label>
                        <table class="table table-bordered table-striped" id="detail-table">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penjualan->details as $detail)
                                    <tr>
                                        <td>
                                            <select name="barang_id[]" class="form-control" required>
                                                <option value="">- Pilih Barang -</option>
                                                @foreach($barangs as $barang)
                                                    <option value="{{ $barang->barang_id }}" {{ $detail->barang_id == $barang->barang_id ? 'selected' : '' }}>{{ $barang->barang_nama }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="detail_harga[]" class="form-control" value="{{ $detail->detail_harga }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="detail_jumlah[]" class="form-control" value="{{ $detail->detail_jumlah }}" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger remove-detail-row">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary add-detail-row">Tambah Detail</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </form>
    <script>
        $(document).ready(function () {
            $("#form-edit").validate({
                rules: {
                    username: { required: true },
                    penjualan_kode: { required: true },
                    pembeli: { required: true },
                    penjualan_tanggal: { required: true },
                    'detail_harga[]': { required: true },
                    'detail_jumlah[]': { required: true }
                },
                submitHandler: function (form) {
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        success: function (response) {
                            if (response.status) {
                                $('#myModal').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                });
                                dataPenjualan.ajax.reload();
                            } else {
                                $('.error-text').text('');
                                $.each(response.msgField, function (prefix, val) {
                                    $('#error-' + prefix).text(val[0]);
                                });
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: response.message
                                });
                            }
                        }
                    });
                    return false;
                },
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });

            // Add new detail row
            $('.add-detail-row').click(function () {
                var newRow = `
                    <tr>
                        <td>
                            <select name="barang_id[]" class="form-control" required>
                                <option value="">- Pilih Barang -</option>
                                @foreach($barangs as $barang)
                                    <option value="{{ $barang->barang_id }}">{{ $barang->barang_nama }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="detail_harga[]" class="form-control" required>
                        </td>
                        <td>
                            <input type="number" name="detail_jumlah[]" class="form-control" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-detail-row">Hapus</button>
                        </td>
                    </tr>
                `;
                $('#detail-table tbody').append(newRow);
            });

            // Remove detail row
            $('#detail-table').on('click', '.remove-detail-row', function () {
                $(this).closest('tr').remove();
            });
        });
    </script>
@endempty