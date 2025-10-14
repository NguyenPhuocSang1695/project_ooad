
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login V1</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" href="style/generall.css">
    <link rel="stylesheet" href="icon/css/all.css">
    <!--===============================================================================================-->
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        @keyframes overlayFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .popup-success {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            text-align: center;
            z-index: 1000;
            animation: fadeIn 0.5s ease-in-out;
            /* Thêm hiệu ứng */
        }

        /* Áp dụng hiệu ứng cho nền mờ */
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            animation: overlayFadeIn 0.5s ease-in-out;
            /* Thêm hiệu ứng */
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            padding: 8px;
            border-radius: 4px;
            margin-top: -10px;
            margin-bottom: 15px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666666;
        }

        .wrap-input100 {
            position: relative;
        }
    </style>
    <script>
        function showSuccessPopup(userName) {
            console.log("Popup function triggered");
            const overlay = document.getElementById('popupOverlay');
            const popup = document.getElementById('popupSuccess');
            const userNameElement = document.getElementById('userName');

            userNameElement.textContent = userName;
            overlay.style.display = 'block';
            popup.style.display = 'block';

            setTimeout(() => {
                window.location.href = '../admin/index/homePage.php';
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordField = document.getElementById('passwordField');

            togglePassword.addEventListener('click', function() {
                // Chuyển đổi kiểu input
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);

                // Chuyển đổi icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</head>

<body>

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="../assets/images/LOGO-2.jpg" alt="IMG">
                </div>

                <form class="login100-form validate-form" action="index.php" method="POST">
                    <span class="login100-form-title">
                        Đăng nhập quản lý
                    </span>

                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" name="Username" placeholder="Tên đăng nhập" value="<?php echo isset($_POST['Username']) ? htmlspecialchars($_POST['Username']) : ''; ?>">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                        </span>
                    </div>
                    <?php if (!empty($errors['username'])): ?>
                        <div class="error-message">
                            <?php echo $errors['username']; ?>
                        </div>
                    <?php endif; ?>

                    <div class="wrap-input100 validate-input">
                        <input class="input100" id="passwordField" type="password" name="PasswordHash" placeholder="Mật khẩu">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                        <span class="toggle-password">
                            <i class="fa fa-eye" aria-hidden="true" style="display: none"></i>
                        </span>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="error-message">
                            <?php echo $errors['password']; ?>
                        </div>
                    <?php endif; ?>

                    <div class="container-login100-form-btn">
                        <button name="submit" type="submit" class="login100-form-btn" style="text-decoration: none; color: black;">
                            Đăng nhập
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup-success" id="popupSuccess">
        <div class="icon">✔</div>
        <h3>Xin chào, <span id="userName"></span>!</h3> <br>
        <p>Đăng nhập thành công!</p>
        <p>Chuyển hướng đến trang quản lý...</p>
    </div>

</body>

</html>