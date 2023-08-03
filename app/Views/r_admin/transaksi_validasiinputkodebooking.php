<?= $this->extend('template/index'); ?>
<?= $this->section('page_content'); ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-warning mb-3 shadow mx-2">
            <div class="card-body">
                <h4 class="card-title text-center">Kode Booking : <b><?= $transaksi['kodebooking_transaksi']; ?></b></h4>
                <div class="table-responsive-md">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td colspan="2">
                                    <h5>Informasi Mahasiswa</h5>
                                </td>
                            </tr>
                            <tr>
                                <td>Nomor Pokok Mahasiswa</td>
                                <td><?= $transaksi['npm']; ?></td>
                            </tr>
                            <tr>
                                <td>Nama</td>
                                <td><?= $transaksi['nama']; ?></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td><?= $transaksi['email']; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h5>Transaksi</h5>
                                </td>
                            </tr>
                            <tr>
                                <td>Jenis Transaksi</td>
                                <td>
                                    <?php if ($transaksi['id_jenis_transaksi'] == 1) : ?>
                                        <span class="badge badge-info"><?= $transaksi['nama_jenis_transaksi']; ?></span>
                                    <?php elseif ($transaksi['id_jenis_transaksi'] == 2) : ?>
                                        <span class="badge badge-warning"><?= $transaksi['nama_jenis_transaksi']; ?></span>
                                    <?php elseif ($transaksi['id_jenis_transaksi'] == 3) : ?>
                                        <span class="badge badge-success"><?= $transaksi['nama_jenis_transaksi']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Nominal</td>
                                <td><?= 'Rp ' . number_format($transaksi['nominal_transaksi'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Saldo Akhir</td>
                                <td><?= 'Rp ' . number_format($transaksi['saldoakhir_transaksi'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr class="table-active">
                                <td>Total Bayar</td>
                                <th class="text-danger">
                                    <?php if ($transaksi['id_jenis_transaksi'] == 2) : ?>
                                        <?= $total_harga = $harga; ?>
                                    <?php else : ?>
                                        <?= 'Rp ' . number_format($total_harga = $transaksi['nominal_transaksi'], 0, ',', '.'); ?>
                                    <?php endif; ?>
                                </th>
                            </tr>
                            <form action="<?= base_url() ?>admin/transaksi_approve" method="post" id="confirmationForm">
                                <?php if ($transaksi['id_jenis_transaksi'] == 2) : ?>
                                    <tr>
                                        <td>Nomor Kartu Baru</td>
                                        <td>
                                            <input type="text" class="form-control" name="nomor_kartu" placeholder="Scan Kartu" required>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="2">
                                        <p class="card-text"><small class="text-muted">*pastikan sudah membayar sebelum submit</small></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="hidden" name="total_harga" value="<?= $total_harga; ?>">
                                        <input type="hidden" name="kode_booking" value="<?= $transaksi['kodebooking_transaksi']; ?>">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </td>
                                </tr>
                            </form>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.js"></script>
<script>
    document.getElementById('confirmationForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Mencegah form dari pengiriman langsung

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin Transaksi?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aksi yang akan dijalankan jika pengguna memilih "Ya"
                this.submit(); // Submit form setelah konfirmasi
            }
        });
    });
</script>

<?= $this->endSection(); ?>
