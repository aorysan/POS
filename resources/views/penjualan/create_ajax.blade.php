<form action="{{ url('/penjualan/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data Penjualan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Hidden input field for user_id -->
                <input type="hidden" name="user_id" value="{{ $currentUserId }}" required>

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
                                <th>Harga Jual</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="barang_id[]" class="form-control barang-select" required>
                                        <option value="">- Pilih Barang -</option>
                                        @foreach($barangs as $barang)
                                            <option value="{{ $barang->barang_id }}" data-harga="{{ $barang->harga_jual }}">{{ $barang->barang_nama }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="detail_harga[]" class="form-control detail-harga" readonly required>
                                </td>
                                <td>
                                    <input type="number" name="detail_jumlah[]" class="form-control detail-jumlah" required>
                                </td>
                                <td>
                                    <input type="number" name="detail_total[]" class="form-control detail-total" readonly required>
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
                penjualan_kode: { required: true },
                pembeli: { required: true },
                penjualan_tanggal: { required: true },
                'barang_id[]': { required: true },
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
                        <select name="barang_id[]" class="form-control barang-select" required>
                            <option value="">- Pilih Barang -</option>
                            @foreach($barangs as $barang)
                                <option value="{{ $barang->barang_id }}" data-harga="{{ $barang->harga_jual }}">{{ $barang->barang_nama }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="detail_harga[]" class="form-control detail-harga" readonly required>
                    </td>
                    <td>
                        <input type="number" name="detail_jumlah[]" class="form-control detail-jumlah" required>
                    </td>
                    <td>
                        <input type="number" name="detail_total[]" class="form-control detail-total" readonly required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-barang-row">Hapus</button>
                    </td>
                </tr>
            `;
            $('#barang-table tbody').append(newRow);
            bindBarangSelectEvent();
        });

        // Remove barang row
        $('#barang-table').on('click', '.remove-barang-row', function () {
            $(this).closest('tr').remove();
        });

        // Initialize event binding
        bindBarangSelectEvent();

        function bindBarangSelectEvent() {
            $('.barang-select').on('change', function () {
                var barangId = $(this).val();
                var harga = $(this).find('option:selected').data('harga');
                var jumlah = $(this).closest('tr').find('.detail-jumlah').val();
                var total = harga * jumlah;

                $(this).closest('tr').find('.detail-harga').val(harga);
                $(this).closest('tr').find('.detail-total').val(total);

                // Update total when quantity changes
                $(this).closest('tr').find('.detail-jumlah').on('input', function () {
                    jumlah = $(this).val();
                    total = harga * jumlah;
                    $(this).closest('tr').find('.detail-total').val(total);
                });
            });
        }
    });
</script>