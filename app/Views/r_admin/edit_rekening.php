<!-- r_admin/edit_rekening.php -->
<?= $this->extend('template/index'); ?>

<?= $this->section('page_content'); ?>

<div class="card mx-5 shadow">
    <div class="card-header">
        Edit Rekening
    </div>
    <div class="card-body">
        <form action="<?= base_url('keuangan/update_rekening'); ?>" method="post">
            <input type="hidden" name="id" value="<?= $rekening['id']; ?>">

            <div class="form-group row">
                <label for="bank" class="col-sm-2 col-form-label">Bank</label>
                <div class="col-sm-10">
                    <input type="text" name="bank" id="bank" class="form-control" value="<?= $rekening['bank']; ?>" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="no_rekening" class="col-sm-2 col-form-label">Nomor Rekening</label>
                <div class="col-sm-10">
                    <input type="text" name="no_rekening" id="no_rekening" class="form-control" value="<?= $rekening['no_rekening']; ?>" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="nama_rekening" class="col-sm-2 col-form-label">Nama Rekening</label>
                <div class="col-sm-10">
                    <input type="text" name="nama_rekening" id="nama_rekening" class="form-control" value="<?= $rekening['nama_rekening']; ?>" required>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= base_url('admin/rekening'); ?>" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>
