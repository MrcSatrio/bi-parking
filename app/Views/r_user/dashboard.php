<?= $this->extend('template/index'); ?>

<?= $this->section('page_content'); ?>
<?php if (session()->getFlashdata('popup')): ?>
    <?php
    $data['showPopup'] = true;
    session()->remove('popup'); // Hapus flash data 'popup' setelah ditampilkan
    ?>
<?php else: ?>
    <?php $data['showPopup'] = false; ?>
<?php endif; ?>

<!-- Menampilkan berkas terbaru -->
<div class="container">
    <?php
    // Mengurutkan array berdasarkan waktu terbaru
    usort($berkas, function ($a, $b) {
        return strtotime($b->created_at) - strtotime($a->created_at);
    });

    // Mengambil satu berkas terbaru
    $berkasTerbaru = array_slice($berkas, 0, 1);
    ?>

    <!-- Menampilkan berkas terbaru -->
    <?php foreach ($berkasTerbaru as $row): ?>
        <tr>
            <td>
                <embed src="<?= base_url('uploads/berkas/' . $row->berkas); ?>" type="application/pdf" width="100%" height="500px">
            </td>
        </tr>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-xl-3 col-md-3 mb-4 mx-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Waktu Saat Ini
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
                            Sisa Saldo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp<?= number_format($user['saldo']) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-3 mb-4 mx-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Transaksi bulan ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp<?= number_format($totalApprovedNominal) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Popup -->
<<div class="popup-overlay" id="popupOverlay">
    <div class="popup-content" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">PERHATIAN</h5>
                </div>
                <div class="modal-body">
                    <?php
                    $pesan = ""; // Variabel untuk menyimpan pesan yang akan ditampilkan

                    if ($user['npm'] === $user['nomor_kartu']) {
                        $pesan .= "<li>Harap Aktivasi kartu Anda.</li>";
                    }

                    if ($user['npm'] . '@gmail.com' === $user['email']) {
                        $pesan .= "<li>Harap Perbarui email Anda.</li>";
                    }

                    if (md5($user['npm']) === ($user['password'])) {
                        $pesan .= "<li>Harap Perbarui Password Anda.</li>";
                    }

                    if (!empty($pesan)) {
                        echo '<div class="alert alert-warning" role="alert">';
                        echo "<ul>";
                        echo $pesan;
                        echo "</ul>";
                        echo "</div>";
                    }
                    ?>
                </div>
                <?php if (!empty($pesan)): ?>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" aria-label="Close" id="closePopupBtn">Tutup</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (empty($pesan)): ?>
<script>
    var popupOverlay = document.getElementById("popupOverlay");
    popupOverlay.parentNode.removeChild(popupOverlay);
</script>
<?php endif; ?>



<!-- CSS untuk gaya popup -->
<style>
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
    }

    .popup-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        width: 400px;
        max-width: 90%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        background-color: #f8f9fa;
        padding: 10px;
        border-bottom: 1px solid #ccc;
        text-align: center;
    }

    .modal-title {
        margin: 0;
        font-size: 18px;
        color: #333;
    }

    .modal-body {
        margin-top: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert {
        padding: 10px;
        border-radius: 5px;
        color: #856404;
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
    }

    .modal-footer {
        text-align: right;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: #fff;
        border-radius: 3px;
        border: none;
        padding: 8px 16px;
        font-size: 14px;
        cursor: pointer;
    }
</style>


<!-- JavaScript untuk mengontrol popup -->
<script>
    window.addEventListener('DOMContentLoaded', function() {
        var showPopup = <?php echo json_encode($data['showPopup']); ?>;
        if (showPopup) {
            var popupOverlay = document.getElementById('popupOverlay');
            popupOverlay.style.display = 'flex';
        }
    });

    function closePopup() {
        var popupOverlay = document.getElementById('popupOverlay');
        popupOverlay.style.display = 'none';
    }

    document.getElementById('closePopupBtn').addEventListener('click', closePopup);
</script>

<script>
    var myVar = setInterval(function() { myTimer() }, 1000);

    function myTimer() {
        var d = new Date();
        document.getElementById("jam").innerHTML =  d.toLocaleTimeString();
    }
</script>
<?= $this->endSection(); ?>
