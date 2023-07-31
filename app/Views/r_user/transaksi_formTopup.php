<?= $this->extend('template/index'); ?>

<?php $this->section('page_content'); ?>

<div class="card shadow mx-5">
    <div class="card-body">
        <h1 class="card-title">Top-Up</h1>
        <?php if (!empty(session()->getFlashdata('error'))) : ?>
            <div class="alert alert-danger" role="alert">
                <h4>Error</h4>
                <hr>
                <?= session()->getFlashdata('error'); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty(session()->getFlashdata('member'))) : ?>
            <div class="alert alert-danger" role="alert">
                <h4>Mohon Maaf, Tidak Bisa TopUp Saldo</h4>
                <hr>
                <?= session()->getFlashdata('member'); ?>
            </div>
        <?php endif; ?>
        <form action="<?= base_url() ?>user/transaksi_topup" method="POST">
            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Nomor Pokok Mahasiswa</label>
                <div class="col-sm-9">
                    <input type="text" readonly class="form-control" value="<?= $user['npm']; ?>" name="npm">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Metode Pembayaran</label>
                <div class="col-sm-9">
                    <select name="jenis_pembayaran" class="form-control form-control-user custom-select" onchange="showCustomNominal(this.value)">
                        <option selected>Pilih Metode Pembayaran</option>
                        <option value="1">Cash</option>
                        <option value="2">Ewallet (Instant)(Fee 2%)</option>
                        <option value="3">Bank (Instant)(Fee 25%)</option>
                        <option value="4">QRIS (Instant)(Fee 1%)</option>
                    </select>
                </div>
            </div>
            <div id="nominalContainer"> <!-- Wrap the Nominal dropdown in a container -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nominal</label>
                    <div class="col-sm-9">
                        <select name="nominal" class="form-control form-control-user custom-select">
                            <option selected>Pilih Nominal Saldo</option>
                            <option value="20000">20.000</option>
                            <option value="50000">50.000</option>
                            <option value="100000">100.000</option>
                            <option value="150000">150.000</option>
                            <option value="200000">200.000</option>
                        </select>
                    </div>
                </div>
            </div>
            <div id="customNominalContainer" style="display: none;">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nominal</label>
                    <div class="col-sm-9">
                        <input type="number" name="nominal" class="form-control form-control-user" min="10000" placeholder="Masukan Nominal (Minimal 10.000)">
                    </div>
                </div>
            </div>

            <div class="col-sm-9">
                <input type="hidden" class="form-control" value="1" name="jenis_transaksi">
                <input type="hidden" class="form-control" value="<?= $user['saldo']; ?>" name="saldoawal">
                <input type="hidden" class="form-control" value="1" name="status_transaksi">
            </div>
            <div class="col-sm-9 mt-4">
                <input type="submit" class="btn btn-primary">
            </div>
        </form>
    </div>
</div>
<script>
    function showCustomNominal(selectedValue) {
        var customNominalContainer = document.getElementById('customNominalContainer');
        var nominalContainer = document.getElementById('nominalContainer');
        var nominalSelect = document.querySelector('select[name="nominal"]');

        if (selectedValue === '1') { // Cash
            customNominalContainer.style.display = 'none';
            nominalContainer.style.display = 'block'; // Show the Nominal dropdown container
            nominalSelect.disabled = false;
        } else { // Ewallet, Bank, QRIS
            customNominalContainer.style.display = 'block';
            nominalContainer.style.display = 'none'; // Hide the Nominal dropdown container
            nominalSelect.disabled = true;
            nominalSelect.value = 'custom'; // Set the select to "Custom Nominal" option
        }
    }
</script>
<?= $this->endSection(); ?>
