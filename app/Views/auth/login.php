<?= $this->extend('auth/template/index'); ?>

<?= $this->section('content'); ?>
<?php
$flashdata = session()->getFlashdata('berhasil');
$errorMessage = session()->get('msg');
$flashSuccess = session()->getFlashdata('success');
$flashSalah = session()->getFlashdata('salah');
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

            // Display success alert if there is a 'berhasil' flashdata in the session
            <?php if (!empty($flashdata)) : ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo addslashes($flashdata); ?>', // Addslashes to escape single quotes
                });
            <?php endif; ?>

            // Display success alert if there is a 'success' flashdata in the session
            <?php if (!empty($flashSuccess)) : ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo addslashes($flashSuccess); ?>', // Addslashes to escape single quotes
                });
            <?php endif; ?>

            // Display success alert if there is a 'salah' flashdata in the session
            <?php if (!empty($flashSalah)) : ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Salah!',
                    text: '<?php echo addslashes($flashSalah); ?>', // Addslashes to escape single quotes
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<div class="login-card-container">
    <div class="login-card">

        <div class="login-card-logo">
            <img src="<?= base_url() ?>assets/img/biu.png">
        </div>
        <div class="login-card-header">
            <h1>Selamat Datang!</h1>
        </div>
        <form class="login-card-form" method="post" action="<?= base_url('login') ?>">
            <div class="form-item">
                <span class="form-item-icon material-symbols-rounded">
                    person
                </span>
                <input type="text" placeholder="Username" required autofocus name="npm" id="npm">
            </div>

            <div class="form-item">
                <span class="form-item-icon material-symbols-rounded">
                    lock
                </span>
                <input type="password" required placeholder="Password" name="password" id="password">
                <span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
            </div>

            <center><img src="<?= base_url('captcha') ?>" alt="CAPTCHA"></center>

            <div class="form-item">
                <span class="form-item-icon material-symbols-rounded">smart_toy</span>
                <input type="text" class="form-control form-control-user" name="captcha" placeholder="Captcha" required>
            </div>

            <div class="footer-form">
                <a class="left-sentence" href="<?= base_url() ?>forgotpassword">Lupa Password</a><br>
                <a class="right-sentence" href="#" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo">Cek saldo</a>
            </div>
            <button type="submit">Masuk</button>
        </form>

        <div class="login-card-footer">

        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cek Saldo Pengguna</h5>
                </button>
            </div>
            <div class="modal-body">
                <div class="login-card">
                    <form class="login-card-form" action="<?= base_url() ?>ceksaldo" method="post">
                        <div class="form-item">
                            <span class="form-item-icon material-symbols-rounded">
                                person
                            </span>
                            <input type="text" placeholder="NPM" required autofocus name="npm" id="npm">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn-primary">Cek</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    var passwordField = document.getElementById("password");
    var togglePassword = document.querySelector(".toggle-password");

    togglePassword.addEventListener("click", function() {
        var type = passwordField.getAttribute("type");
        if (type === "password") {
            passwordField.setAttribute("type", "text");
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            passwordField.setAttribute("type", "password");
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");
        }
    });
</script>
<?= $this->endSection(); ?>