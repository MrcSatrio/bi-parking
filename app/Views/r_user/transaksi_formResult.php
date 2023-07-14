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
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="text-center">KODE PEMESANAN</h2>
                </div>
                <div class="card-header">
                    <div class="mb-4">
                        <center>
                        <label for="saldo" class="form-label">Kode Pemesanan:</label> <br>
                        </center>
                        <h1 class="text-center">
                            <p class="booking-code"><?= $booking_code ?><button class="btn copy-button"><img src="<?= base_url() ?>assets/img/kopi.png" width="24px"></button></p>
                        </h1>
                    </div>
                    <div class="mb-4">
                        <center>
                        <label for="denda" class="form-label">Total Pembayaran:</label> <br>
                        </center>
                        <h1 class="text-center">
                            <p class="nominal"><?= number_format($nominal_saldo, 0, ',', '.') ?> <button class="btn copy-button"><img src="<?= base_url() ?>assets/img/kopi.png" width="24px"></button></p>
                        </h1>
                    </div>
                    <?php if ($jenis_pembayaran == 2) : ?>
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
                        <?php if ($transaksi['id_jenis_pembayaran'] == 2 && empty($transaksi['bukti_pembayaran'])) : ?>
                            <div class="card-header text-white mb-4">
                                <center>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadModal<?= base64_encode($transaksi['id_transaksi']); ?>">Upload Bukti Transfer</button>
                                </center>                         
                            </div>
                        <?php endif; ?>
                    <?php elseif ($jenis_pembayaran == 1) : ?>
                        <div class="card-header text-white bg-warning mb-4">
                            <p class="text-center">Silahkan bayar di bagian keuangan</p>
                        </div>
                    <?php endif; ?>
                    <div class="text-center">
                        <!-- kembali ke halaman sebelumnya -->
                        <a href="<?= base_url() ?>user/riwayatTransaksi" class="btn btn-primary btn-block">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


                <script>
                    const copyButtons = document.querySelectorAll('.copy-button');
                    copyButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const textToCopy = this.parentNode.parentNode.innerText.trim();
                            const tempInput = document.createElement('input');
                            tempInput.value = textToCopy;
                            document.body.appendChild(tempInput);
                            tempInput.select();
                            document.execCommand('copy');
                            document.body.removeChild(tempInput);
                            alert('Teks berhasil disalin!');
                        });
                    });
                </script>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uploadModal<?= base64_encode($transaksi['id_transaksi']); ?>" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel<?= base64_encode($transaksi['id_transaksi']); ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-white">
                                                <h5 class="modal-title" id="uploadModalLabel<?= base64_encode($transaksi['id_transaksi']); ?>">Upload Bukti Pembayaran</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="<?= base_url(); ?>user/bukti/<?= base64_encode($transaksi['id_transaksi']); ?>" enctype="multipart/form-data">
                                                    <div class="form-group">
                                                        <label for="bukti_pembayaran<?= base64_encode($transaksi['id_transaksi']); ?>">Pilih File Bukti Pembayaran:</label>
                                                        <input type="file" class="form-control-file" id="bukti_pembayaran<?= base64_encode($transaksi['id_transaksi']); ?>" name="bukti_pembayaran" accept=".jpg, .jpeg, .png" required maxFileSize="4194304">
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Upload</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
</div>
<?= $this->endSection(); ?>
