<?= $this->extend('template/index'); ?>

<?= $this->section('page_content'); ?>
<?php if (!empty(session()->getFlashdata('success'))) : ?>
    <div class="alert alert-success" role="alert">
        <?= session()->getFlashdata('success'); ?>
    </div>
<?php endif; ?>
<div class="card mx-2 shadow">
    <div class="card-header">
        Info Rekening
    </div>
    <div class="card-body">
        <div class="table-responsive-lg">
            <table class="table table-hover">
                <thead class="table-success">
                    <tr>
                        <th>No.</th>
                        <th>Bank</th>
                        <th>No Rekening</th>
                        <th>Nama Rekening</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
            <?php foreach ($rekening as $row) : ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= $row['bank']; ?></td>
                    <td><?= $row['no_rekening']; ?></td>
                    <td><?= $row['nama_rekening']; ?></td>
                    <td>
                                <div class="btn-group">
                                <a class="btn btn-warning btn-sm" href="<?= base_url(); ?>keuangan/edit_rekening/<?= $row['id']; ?>"><i class="bi bi-pencil-square"></i></a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
