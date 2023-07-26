<?= $this->extend('auth/template/index');

$this->section('content'); ?>
<?php
$errorMessage = session()->get('msg');
?>
<?php if (isset($flashSuccess) || isset($flashdata) || isset($flashSalah) || isset($errorMessage)) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Display error alert if there is an error message in the session
            <?php if (!empty($errorMessage)) : ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '<?php echo addslashes($errorMessage); ?>', // Addslashes to escape single quotes
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<div class="container">

    <!-- Outer Row -->
    <div class="login-card-container">
    <div class="login-card">
    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Lupa Password</h1>
                                </div>
                                <form class="login-card-form" method="post" action="<?= base_url('passwordreset') ?>">
                <div class="form-item">
                    <span class="form-item-icon material-symbols-rounded">
                        Email
                    </span>
                    <input type="text" placeholder="Email" required autofocus name="email" id="email">
                </div>
                                    <button type="submit">
                                        Kirim Kode
                                    </button>
                                </form>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?= base_url() ?>">Kembali</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
    <?= $this->endSection(); ?>