<?= $this->extend('auth/template/index');

$this->section('content'); ?>
<?php
$successMessage = session()->get('success');
$errorMessage = session()->get('error');
$erortoken = session()->get('tokenerror');
?>
<?php if (isset($successMessage)) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Display error alert if there is an error message in the session
            <?php if (!empty($successMessage)) : ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses...',
                    text: '<?php echo addslashes($successMessage); ?>', // Addslashes to escape single quotes
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>
<?php if (isset($erortoken)) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Display error alert if there is an error message in the session
            <?php if (!empty($erortoken)) : ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal..',
                    text: '<?php echo addslashes($erortoken); ?>', // Addslashes to escape single quotes
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>
<?php if (isset($errorMessage)) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Display error alert if there is an error message in the session
            <?php if (!empty($errorMessage)) : ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal..',
                    text: '<?php echo addslashes($errorMessage); ?>', // Addslashes to escape single quotes
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<div class="container">
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
                                        <form class="login-card-form" action="<?= base_url() ?>updatepassword" method="post" class="user">
                                            <div class="form-item">
                                                <span class="form-item-icon material-symbols-rounded">
                                                    Key
                                                </span>
                                                <input type="text" name="token" placeholder="Input Token">
                                            </div>

                                            <div class="form-item">
                                                <span class="form-item-icon material-symbols-rounded">
                                                    <i class="fa fa-lock"></i>
                                                </span>
                                                <input type="password" name="password" placeholder="New Password" id="password">
                                                <span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                            </div>

                                            <div class="form-item">
                                                <span class="form-item-icon material-symbols-rounded">
                                                    <i class="fa fa-lock"></i>
                                                </span>
                                                <input type="password" required placeholder="Confirm New Password" name="confirmpassword" id="confirmpassword">
                                                <span toggle="#confirmpassword" class="fa fa-fw fa-eye field-icon toggle-confirm-password"></span>
                                            </div>
                                            <button type="submit">
                                                Ubah Password
                                            </button>
                                        </form>
                                        <hr>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
            <script>
                // Function to toggle password visibility for a given field
                function togglePasswordVisibility(passwordField, togglePassword) {
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
                }

                // Event listeners for the password fields
                var passwordField = document.getElementById("password");
                var togglePassword = document.querySelector(".toggle-password");

                var confirmPasswordField = document.getElementById("confirmpassword");
                var toggleConfirmPassword = document.querySelector(".toggle-confirm-password");

                togglePassword.addEventListener("click", function() {
                    togglePasswordVisibility(passwordField, togglePassword);
                });

                toggleConfirmPassword.addEventListener("click", function() {
                    togglePasswordVisibility(confirmPasswordField, toggleConfirmPassword);
                });
            </script>



            <?= $this->endSection(); ?>