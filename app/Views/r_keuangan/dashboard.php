<?= $this->extend('template/index'); ?>

<?= $this->section('page_content'); ?>

<div class="row">
    <div class="col-xl-3 col-md-3 mb-4 mx-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Waktu saat ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="jam"></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-3 mb-4 mx-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Transaksi bulan ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp<?= number_format($totalApprovedNominal) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col">
        <div class="card shadow mx-2">
            <div class="card-header">
                Riwayat Transaksi
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
                                <th>Bukti Transfer</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($transaksi as $tr): ?>
                                <?php if ($tr['id_status_transaksi'] == 1): ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= $tr['kodebooking_transaksi']; ?></td>
                                        <td><?= $tr['npm']; ?></td>
                                        <td>
                                            <?php if ($tr['id_jenis_transaksi'] == 1): ?>
                                                <span class="badge badge-info"><?= $tr['nama_jenis_transaksi']; ?></span>
                                            <?php elseif ($tr['id_jenis_transaksi'] == 2): ?>
                                                <span class="badge badge-warning"><?= $tr['nama_jenis_transaksi']; ?></span>
                                            <?php elseif ($tr['id_jenis_transaksi'] == 3): ?>
                                                <span class="badge badge-success"><?= $tr['nama_jenis_transaksi']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?='Rp ' . number_format($tr['saldoawal_transaksi']); ?></td>
                                        <td><?='Rp ' . number_format($tr['nominal_transaksi']); ?></td>
                                        <td><?='Rp ' . number_format($tr['saldoakhir_transaksi']); ?></td>
                                        <td>
                                            <?php if ($tr['id_status_transaksi'] == 1): ?>
                                                <span class="badge badge-danger"><?= $tr['nama_status_transaksi']; ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-success"><?= $tr['nama_status_transaksi']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                        <?php if ($tr['id_jenis_pembayaran'] == 2 && $tr['id_status_transaksi'] != 4 && !empty($tr['bukti_pembayaran'])) : ?>
    <a target="_blank" href="<?= base_url('uploads/bukti/' . $tr['bukti_pembayaran']); ?>" class="btn btn-primary" style="padding: 5px 5px;">Lihat Bukti</a>
<?php endif; ?>
                                        </td>
                                        <td><?= $tr['updated_at']; ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var myVar = setInterval(function() { myTimer() }, 1000);

    function myTimer() {
        var d = new Date();
        document.getElementById("jam").innerHTML =  d.toLocaleTimeString();
    }
</script>

<?= $this->endSection(); ?>
