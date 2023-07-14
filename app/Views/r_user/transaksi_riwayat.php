<?= $this->extend('template/index'); ?>

<?= $this->section('page_content'); ?>
<?php if (!empty(session()->getFlashdata('success'))) : ?>
    <div class="alert alert-success" role="alert">
        <?= session()->getFlashdata('success'); ?>
    </div>
<?php endif; ?>
<?php if (!empty(session()->getFlashdata('error'))) : ?>
    <div class="alert alert-danger" role="alert">
        <?= session()->getFlashdata('error'); ?>
    </div>
<?php endif; ?>
<div class="row justify-content-center">
    <div class="col">
        <div class="card shadow mx-2">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Riwayat Transaksi</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-lg">
                    <table class="table table-hover">
                        <thead class="table-success">
                            <tr>
                                <th>#</th>
                                <th>Kode Booking</th>
                                <th>NIM</th>
                                <th>Jenis Transaksi</th>
                                <th>Saldo Awal</th>
                                <th>Nominal</th>
                                <th>Saldo Akhir</th>
                                <th>Status</th>
                                <th>Metode</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1 + ($limit * ($currentPage - 1)); // Variabel penomoran

                            foreach ($transaksi as $tr) :
                            ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= $tr['kodebooking_transaksi']; ?></td>
                                    <td><?= $tr['npm']; ?></td>
                                    <td>
                                        <?php if ($tr['id_jenis_transaksi'] == 1) : ?>
                                            <span class="badge badge-info"><?= $tr['nama_jenis_transaksi']; ?></span>
                                        <?php elseif ($tr['id_jenis_transaksi'] == 2) : ?>
                                            <span class="badge badge-warning"><?= $tr['nama_jenis_transaksi']; ?></span>
                                        <?php elseif ($tr['id_jenis_transaksi'] == 3) : ?>
                                            <span class="badge badge-success"><?= $tr['nama_jenis_transaksi']; ?></span>
                                        <?php endif ?>
                                    </td>
                                    <td>Rp.<?= number_format($tr['saldoawal_transaksi'], 0, ',', '.'); ?></td>
                                    <td>Rp.<?= number_format($tr['nominal_transaksi'], 0, ',', '.'); ?></td>
                                    <td>Rp.<?= number_format($tr['saldoakhir_transaksi'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if ($tr['id_status_transaksi'] == 1) : ?>
                                            <span class="badge badge-warning"><?= $tr['nama_status_transaksi']; ?></span>
                                        <?php elseif ($tr['id_status_transaksi'] == 2 || $tr['id_status_transaksi'] == 3) : ?>
                                            <span class="badge badge-success"><?= $tr['nama_status_transaksi']; ?></span>
                                        <?php elseif ($tr['id_status_transaksi'] == 4) : ?>
                                            <span class="badge badge-danger"><?= $tr['nama_status_transaksi']; ?></span>
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <?php if ($tr['id_jenis_pembayaran'] == 1) : ?>
                                            <span class="badge badge-info"><?= $tr['nama_jenis_pembayaran']; ?></span>
                                        <?php elseif ($tr['id_jenis_pembayaran'] == 2) : ?>
                                            <span class="badge badge-info"><?= $tr['nama_jenis_pembayaran']; ?></span>
                                        <?php endif ?>
                                    </td>
                                    <td><?= $tr['updated_at']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false" data-reference="parent">
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <?php if ($tr['id_status_transaksi'] == 1) : ?>
                                                <div class="dropdown-menu shadow">
                                                    <form method="POST" action="<?= base_url(); ?>user/cancel/<?= base64_encode($tr['id_transaksi']); ?>">
                                                        <input type="hidden" name="id_status_transaksi" value="4">
                                                        <button type="submit" class="dropdown-item btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan?')">Batalkan</button>
                                                    </form>
                                                    <?php if ($tr['id_jenis_pembayaran'] == 2 && empty($tr['bukti_pembayaran'])) : ?>
                                                        <button type="button" class="dropdown-item btn btn-warning" data-toggle="modal" data-target="#uploadModal<?= base64_encode($tr['id_transaksi']); ?>">Upload Bukti</button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Modal -->
                                <div class="modal fade" id="uploadModal<?= base64_encode($tr['id_transaksi']); ?>" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel<?= base64_encode($tr['id_transaksi']); ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="uploadModalLabel<?= base64_encode($tr['id_transaksi']); ?>">Upload Bukti Transfer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h2 class="text-center">KODE PEMESANAN</h2>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <center>
                                            <label for="saldo" class="form-label">Kode Pemesanan:</label> <br>
                                        </center>
                                        <h1 class="text-center">
                                            <p class="booking-code"><?= $tr['kodebooking_transaksi']; ?><button class="btn copy-button"><img src="<?= base_url() ?>assets/img/kopi.png" width="24px"></button></p>
                                        </h1>
                                    </div>
                                    <div class="mb-4">
                                        <center>
                                            <label for="denda" class="form-label">Total Pembayaran:</label> <br>
                                        </center>
                                        <h1 class="text-center">
                                            <p class="nominal"><?= number_format($tr['nominal_transaksi'], 0, ',', '.'); ?> <button class="btn copy-button"><img src="<?= base_url() ?>assets/img/kopi.png" width="24px"></button></p>
                                        </h1>
                                    </div>
                                    <div class="mb-4 text-center">
                                        <label for="rekening" class="form-label">No Rekening:</label>
                                        <br>
                                        <img src="https://mandiri-investasi.co.id/wp-content/uploads/2021/11/bsi-PNG.png" width="90px">
                                        <p style="font-size: 20px;">7195540521 <button class="btn copy-button"><img src="<?= base_url() ?>assets/img/kopi.png" width="24px"></button></p>
                                        <p style="font-size: 20px;">YAYASAN KEMAKMURAN DAN KESEJAHTERAAN ANAK BANGSA</p>
                                    </div>
                                    <div class="card-header text-white bg-warning mb-4">
                                        <p class="text-center">Harap Melakukan Transfer Sesuai Total Pembayaran</p>
                                    </div>
                                    <form method="POST" action="<?= base_url(); ?>user/bukti/<?= base64_encode($tr['id_transaksi']); ?>" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="formFile" class="form-label">Upload Bukti Pembayaran</label>
                                            <input type="file" class="form-control-file" id="bukti_pembayaran<?= base64_encode($tr['id_transaksi']); ?>" name="bukti_pembayaran" accept=".jpg, .jpeg, .png" required maxFileSize="4194304">
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">Upload</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center">
                                    <!-- kembali ke halaman sebelumnya -->
                                    <a href="<?= base_url() ?>user/riwayatTransaksi" class="btn btn-secondary" data-dismiss="modal">Kembali</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?= $pager->links('pagination', 'pagination'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .alert {
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #4e73df;
        color: #ffffff;
        font-weight: bold;
    }

    .form-inline .form-control {
        width: 300px;
    }

    .table {
        margin-bottom: 0;
    }

    .table-success th {
        background-color: #1cc88a;
        color: #ffffff;
        font-weight: bold;
    }

    .badge-info {
        background-color: #36b9cc;
        color: #ffffff;
    }

    .badge-warning {
        background-color: #f6c23e;
        color: #ffffff;
    }

    .badge-success {
        background-color: #1cc88a;
        color: #ffffff;
    }

    .badge-danger {
        background-color: #e74a3b;
        color: #ffffff;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: #ffffff;
    }

    .dropdown-menu {
        min-width: 8rem;
    }

    .dropdown-menu .dropdown-item {
        color: #000000;
    }

    .modal-header {
        background-color: #4e73df;
        color: #ffffff;
    }

    .modal-content {
        background-color: #f8f9fc;
    }

    .modal-body {
        padding: 1rem;
    }

    .card {
        margin-bottom: 1rem;
    }

    .card-header.bg-primary {
        background-color: #4e73df;
        color: #ffffff;
    }

    .card-header.bg-warning {
        background-color: #f6c23e;
        color: #ffffff;
    }

    .btn-primary {
        background-color: #4e73df;
        color: #ffffff;
    }

    .btn-warning {
        background-color: #f6c23e;
        color: #ffffff;
    }

    .btn-primary:hover {
        background-color: #2653ca;
    }

    .btn-warning:hover {
        background-color: #d1a828;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn-danger {
        background-color: #e74a3b;
        color: #ffffff;
    }

    .btn-danger:hover {
        background-color: #be261e;
    }

    .btn-info {
        background-color: #36b9cc;
        color: #ffffff;
    }

    .btn-info:hover {
        background-color: #258999;
    }

    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>

<script>
    $(document).ready(function() {
        $('.copy-button').click(function() {
            var text = $(this).parent().text();
            navigator.clipboard.writeText(text);
            alert('Text copied to clipboard');
        });
    });
</script>
<?= $this->endSection(); ?>
