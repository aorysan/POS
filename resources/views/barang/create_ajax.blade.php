<form action="{{ url('/barang/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Kategori</label>
                    <input value="" type="kategori_id" name="kategori_id" id="kategori_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Kode</label>
                    <input value="" type="text" name="barang_kode" id="barang_kode" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nama</label>
                    <input value="" type="text" name="barang_nama" id="barang_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga Beli</label>
                    <input value="" type="barang_beli" name="barang_beli" id="barang_beli" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga Jual</label>
                    <input value="" type="barang_jual" name="barang_jual" id="barang_jual" class="form-control" required>
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
                kategori_id: { required: true},
                barang_kode: { required: true},
                barang_nama: { required: true},
                barang_beli: { required: true},
                barang_jual: { required: true}
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
                            dataBarang.ajax.reload();
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
    });
</script>