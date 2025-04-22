<form action="{{ url('/penjualan/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data Penjualan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>User</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">- Pilih User -</option>
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}">{{ $user->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Kode Penjualan</label>
                    <input value="" type="text" name="penjualan_kode" id="penjualan_kode" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Pembeli</label>
                    <input value="" type="text" name="pembeli" id="pembeli" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Penjualan</label>
                    <input type="date" name="penjualan_tanggal" id="penjualan_tanggal" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Detail Barang</label>
                    <table class="table table-bordered table-striped" id="barang-table">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                    <button type="button" class="btn btn-danger remove-barang-row">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary add-barang-row">Tambah Barang</button>
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
        $("#form-tambah").validate({
            rules: {
                user_id: { required: true },
                penjualan_kode: { required: true },
                pembeli: { required: true },
                penjualan_tanggal: { required: true },
                'barang_id[]': { required: true },
                'detail_harga[]': { required: true, number: true },
                'detail_jumlah[]': { required: true, number: true }
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

        // Add new barang row
        $('.add-barang-row').click(function () {
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
                        <button type="button" class="btn btn-danger remove-barang-row">Hapus</button>
                    </td>
                </tr>
            `;
            $('#barang-table tbody').append(newRow);
        });

        // Remove barang row
        $('#barang-table').on('click', '.remove-barang-row', function () {
            $(this).closest('tr').remove();
        });
    });
</script>