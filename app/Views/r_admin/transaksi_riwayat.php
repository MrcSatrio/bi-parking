<?= $this->extend('template/index'); ?>

<?= $this->section('page_content'); ?>

<?php if (!empty(session()->getFlashdata('error'))) : ?>
    <div class="alert alert-danger" role="alert">
        <?= session()->getFlashdata('error'); ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col">
        <div class="card shadow mx-2">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Riwayat Transaksi</h4>
                <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100" action="<?= base_url() ?><?= $user['nama_role']; ?>/search" method="POST">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Keyword" name="keyword">
                        <div class="form-group">
                            <label for="start_date" class="text-white">Tanggal Mulai:</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="end_date" class="text-white">Tanggal Akhir:</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="jenis_transaksi" class="text-white">Status Transaksi:</label>
                            <select name="id_status_transaksi" id="id_status_transaksi" class="form-control">
                                <option value="">All</option>
                                <option value="3">Approved</option>
                                <option value="1">Pending</option>
                                <option value="4">Cancel</option>
                                <option value="2">Parkir</option>
                            </select>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-light" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive-lg">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Kode Booking</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Jenis Transaksi</th>
                                <th>Nominal Transaksi</th>
                                <th>Status</th>
                                <th>Metode Pembayaran</th>
                                <th>Bukti Transfer</th>
                                <th>Tanggal</th>
                                <th>Validator</th>
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
                                    <td><?= $tr['nama']; ?></td>
                                    <td>
                                        <?php if ($tr['id_jenis_transaksi'] == 1) : ?>
                                            <span class="badge badge-info"><?= $tr['nama_jenis_transaksi']; ?></span>
                                        <?php elseif ($tr['id_jenis_transaksi'] == 2) : ?>
                                            <span class="badge badge-warning"><?= $tr['nama_jenis_transaksi']; ?></span>
                                        <?php elseif ($tr['id_jenis_transaksi'] == 3) : ?>
                                            <span class="badge badge-success"><?= $tr['nama_jenis_transaksi']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= 'Rp ' . number_format($tr['nominal_transaksi']); ?></td>
                                    <td>
                                        <?php if ($tr['id_status_transaksi'] == 1) : ?>
                                            <span class="badge badge-warning"><?= $tr['nama_status_transaksi']; ?></span>
                                        <?php elseif ($tr['id_status_transaksi'] == 2) : ?>
                                            <span class="badge badge-success"><?= $tr['nama_status_transaksi']; ?></span>
                                        <?php elseif ($tr['id_status_transaksi'] == 3) : ?>
                                            <span class="badge badge-success"><?= $tr['nama_status_transaksi']; ?></span>
                                        <?php elseif ($tr['id_status_transaksi'] == 4) : ?>
                                            <span class="badge badge-danger"><?= $tr['nama_status_transaksi']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($tr['id_jenis_pembayaran'] == 1) : ?>
                                            <span class="badge badge-primary"><?= $tr['nama_jenis_pembayaran']; ?></span>
                                        <?php elseif ($tr['id_jenis_pembayaran'] == 2) : ?>
                                            <span class="badge badge-secondary"><?= $tr['nama_jenis_pembayaran']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($tr['id_jenis_pembayaran'] == 2 && $tr['id_status_transaksi'] != 4 && !empty($tr['bukti_pembayaran'])) : ?>
                                            <a target="_blank" href="<?= base_url('uploads/bukti/' . $tr['bukti_pembayaran']); ?>" class="btn btn-primary" style="padding: 5px 5px;">Lihat Bukti</a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $tr['updated_at']; ?></td>
                                    <td><?= $tr['validator']; ?></td>
                                    <td>
    <div class="btn-group">
        <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false" data-reference="parent">
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu shadow">
            <?php if ($tr['id_status_transaksi'] == 1) : ?>
                <form method="POST" action="<?= base_url(); ?>admin/cancel/<?= base64_encode($tr['id_transaksi']); ?>">
                    <input type="hidden" name="id_status_transaksi" value="4">
                    <button type="submit" class="dropdown-item btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan?')">Batalkan</button>
                </form>
            <?php endif; ?>
            <?php if ($tr['id_status_transaksi'] == 3) : ?>
                <form method="POST" action="<?= base_url(); ?>admin/cetak/<?= $tr['id_transaksi']; ?>">
                    <button type="submit" class="dropdown-item btn btn-primary">Cetak</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?= $pager->links('pagination', 'pagination'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>