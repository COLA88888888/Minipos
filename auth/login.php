<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true
    ]);
    session_start();
}
if (isset($_SESSION['checked']) && $_SESSION['checked'] === 1 && !empty($_SESSION['user_id'])) {
    header("Location: ../home/dashboard.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';
$site_logo = '../assets/img/logo/logo.png';
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Wlaodev</title>
    <link rel="shortcut icon" href="<?php echo $site_logo; ?>" type="image/x-icon">

    <!-- CSS Plugins & Theme -->
    <link rel="stylesheet" href="../plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../fontawesome-free-5.15.3-web/css/all.min.css">
    <link rel="stylesheet" href="../sweetalert/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/local-font.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../themes/login.css?v=<?php echo filemtime(__DIR__ . '/../themes/login.css'); ?>">
</head>
<body>

    <!-- Banner Background on Top -->
    <div class="login-banner-stripe"></div>
    <div class="login-banner-bg"></div>
    <div class="login-banner-overlay"></div>

    <!-- Login Container -->
    <div class="login-card-container">
        <!-- Main Card -->
        <div class="login-card">
            <!-- Overlapping Circle Logo Badge -->
            <div class="logo-badge">
                <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="POS Logo">
            </div>

            <!-- Title Header -->
            <div class="login-header-text">
                <h4>ເຂົ້າສູ່ລະບົບ POS System</h4>
                <p>POS Wlaodev</p>
                <!-- <div class="login-hint">ກະລຸນາເຂົ້າສູ່ລະບົບ...</div> -->
            </div>

            <!-- Login Form -->
            <form id="loginForm" autocomplete="off" novalidate>
                <!-- Autofill prevention -->
                <input type="text" style="display:none;" aria-hidden="true">
                <input type="password" style="display:none;" aria-hidden="true">

                <!-- Username Input -->
                <div class="custom-input-group">
                    <label for="username">ຊື່ຜູ້ໃຊ້:</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-control-custom" placeholder="ປ້ອນຊື່ຜູ້ໃຊ້ງານ..." autocomplete="off" required>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="custom-input-group">
                    <label for="password">ລະຫັດຜ່ານ:</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control-custom" placeholder="ປ້ອນລະຫັດຜ່ານ..." autocomplete="new-password" style="padding-right: 42px !important;" required>
                        <button type="button" class="btn-toggle-pass" id="togglePassword" tabindex="-1">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login-custom" id="btnLogin">
                    <i class="fas fa-user-check mr-1"></i> ເຂົ້າສູ່ລະບົບ
                </button>
            </form>
        </div>

        <!-- Footer Below Card -->
        <div class="bct-footer">
            <div class="bct-footer-copy">&copy; 2026 - POS System By <a href="https://laodev.la/" target="_blank" rel="noopener noreferrer">LaoDev.la</a></div>
            <div class="bct-social-links">
                <a href="https://www.facebook.com/profile.php?id=100067585680024&locale=th_TH" target="_blank" rel="noopener noreferrer" class="bct-social-link">
                    <i class="fab fa-facebook"></i> WLaoDev
                </a>
                <a href="https://wa.me/8562055949007" target="_blank" rel="noopener noreferrer" class="bct-social-link">
                    <i class="fab fa-whatsapp"></i> +856 20 55 949 007
                </a>
            </div>
        </div>
    </div>

    <!-- Required Scripts -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../sweetalert/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            sessionStorage.removeItem('isSessionActive');

            setTimeout(function() {
                $('#username').val('');
                $('#password').val('');
            }, 150);

            // Check if session/token has expired
            <?php if (isset($_GET['expired'])): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Token ໝົດອາຍຸແລ້ວ',
                text: 'ກະລຸນາເຂົ້າສູ່ລະບົບໃໝ່',
                confirmButtonColor: '#007bff',
                confirmButtonText: 'ຕົກລົງ'
            }).then(function() {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
            <?php endif; ?>

            // Toggle Password
            $('#togglePassword').on('click', function() {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $('#eyeIcon').toggleClass('fa-eye fa-eye-slash');
            });

            // Submit Form
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                var username = $.trim($('#username').val());
                var password = $('#password').val();
                var btn = $('#btnLogin');

                if (!username && !password) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ແຈ້ງເຕືອນ',
                        text: 'ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ງານ ແລະ ລະຫັດຜ່ານ!',
                        confirmButtonColor: '#007bff',
                        confirmButtonText: 'ຕົກລົງ',
                        heightAuto: false
                    }).then(function() {
                        $('#username').focus();
                    });
                    return;
                }

                if (!username) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ແຈ້ງເຕືອນ',
                        text: 'ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ງານ!',
                        confirmButtonColor: '#007bff',
                        confirmButtonText: 'ຕົກລົງ',
                        heightAuto: false
                    }).then(function() {
                        $('#username').focus();
                    });
                    return;
                }

                if (!password) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ແຈ້ງເຕືອນ',
                        text: 'ກະລຸນາປ້ອນລະຫັດຜ່ານ!',
                        confirmButtonColor: '#007bff',
                        confirmButtonText: 'ຕົກລົງ',
                        heightAuto: false
                    }).then(function() {
                        $('#password').focus();
                    });
                    return;
                }

                btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> ກຳລັງກວດສອບ...');

                $.ajax({
                    url: 'check_user.php',
                    type: 'POST',
                    data: { username: username, password: password },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var tabToken = 'TOKEN_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                            sessionStorage.setItem('pos_tab_active', tabToken);
                            sessionStorage.setItem('isSessionActive', 'true');
                            window.location.href = response.redirect;
                        } else {
                            btn.prop('disabled', false).html('<i class="fas fa-user-check mr-1"></i> ເຂົ້າສູ່ລະບົບ');
                            $('#loginForm')[0].reset();
                            $('#username').val('').focus();
                            $('#password').val('');

                            Swal.fire({
                                icon: 'error',
                                title: 'ແຈ້ງເຕືອນ',
                                text: response.message || 'ລະຫັດບໍ່ຖືກຕ້ອງ ກະລຸນາລອງໃໝ່',
                                confirmButtonColor: '#007bff',
                                confirmButtonText: 'ຕົກລົງ',
                                heightAuto: false
                            }).then(function() {
                                $('#username').focus();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        btn.prop('disabled', false).html('<i class="fas fa-user-check mr-1"></i> ເຂົ້າສູ່ລະບົບ');
                        $('#loginForm')[0].reset();
                        $('#username').val('').focus();
                        $('#password').val('');

                        Swal.fire({
                            icon: 'error',
                            title: 'ແຈ້ງເຕືອນ',
                            text: 'ລະຫັດບໍ່ຖືກຕ້ອງ ກະລຸນາລອງໃໝ່',
                            confirmButtonColor: '#007bff',
                            confirmButtonText: 'ຕົກລົງ',
                            heightAuto: false
                        }).then(function() {
                            $('#username').focus();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
