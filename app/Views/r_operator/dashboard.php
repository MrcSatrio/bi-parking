<?= $this->extend('template/index'); ?>

<?php $this->section('page_content'); ?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card border-warning mb-3 shadow mx-2 h-100">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">
                    <b>PEMBAYARAN PARKIR</b>
                </h2>
                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= session()->getFlashdata('error'); ?>
                    </div>
                <?php endif; ?>
                <form action="<?= base_url($user['nama_role']) ?>/check-out" method="post">
                    <div class="mb-3">
                        <label class="form-label"><b>No Kartu</b></label>
                        <input type="number" class="form-control form-control-lg" placeholder="Masukkan Nomor Kartu" name="nomor_kartu" onkeydown="return event.key !== 'Enter';" autofocus oninput="validateNomorKartu(this)" maxlength="10">
                    </div>
                    <div class="text-center align-items-center mt-4">
                        <button type="submit" class="btn btn-outline-info flex-fill mx-5" name="nominal_transaksi" value="<?= $parkir_motor['nominal']; ?>" style="font-size: 24px; padding: 20px 40px;">
                            <i class="fas fa-motorcycle"></i>
                            MOTOR
                        </button>
                        <button type="submit" class="btn btn-outline-info flex-fill mx-5" name="nominal_transaksi" value="<?= $parkir_mobil['nominal']; ?>" style="font-size: 24px; padding: 20px 40px;">
                            <i class="fas fa-car"></i>
                            MOBIL
                        </button>
                    </div>
                    <input type="hidden" name="id_jenis_transaksi" value="3">
                    <input type="hidden" name="id_status_transaksi" value="2">
                    <input type="hidden" name="id_jenis_pembayaran" value="1">
                </form>
                <?php if (session()->getFlashdata('nama') && session()->getFlashdata('sisa_saldo')) : ?>
    <div class="text-center mt-4">
    <div class="alert alert-success" role="alert">
        <h4 class="mb-3">Transaksi Berhasil!</h4>
    </div>
        <div class="card border-success shadow">
            <div class="card-body">
                <h5 class="card-title">Rincian Transaksi</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <span class="fw-bold">Nama:</span> <?= session()->getFlashdata('nama'); ?>
                    </li>
                    <li class="list-group-item">
                        <span class="fw-bold">Sisa Saldo: Rp.</span> <?= number_format(session()->getFlashdata('sisa_saldo'), 0, ',', '.'); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function validateNomorKartu(input) {
        if (input.value.length > 10) {
            input.value = input.value.slice(0, 10); // Potong input jika melebihi 10 karakter
        }
    }
</script>

<?= $this->endSection(); ?>
