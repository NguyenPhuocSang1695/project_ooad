<?php
require_once './admin/php/connect.php';

// Khởi tạo và kết nối
$db = new DatabaseConnection();
$db->connect(); // tạo kết nối MySQL
$myconn = $db->getConnection(); // lấy đối tượng mysqli thực sự để dùng prepare()
session_name('admin_session');
session_start();

// Nếu đã đăng nhập rồi thì chuyển sang trang chính
if (isset($_SESSION['Username'])) {
    header("Location: ./admin/index/homePage.php");
    exit();
}

$errors = [
    'Username' => '',
    'password' => ''
];

if (isset($_POST['submit'])) {
    $username = trim($_POST['Username']);
    $password = trim($_POST['PasswordHash']);

    if (empty($username)) {
        $errors['Username'] = "Vui lòng nhập tên đăng nhập!";
    }
    if (empty($password)) {
        $errors['password'] = "Vui lòng nhập mật khẩu!";
    }

    if (empty($errors['Username']) && empty($errors['password'])) {
        $stmt = $myconn->prepare("SELECT Username, FullName, Role, PasswordHash, Status FROM users WHERE Username = ? AND Role = 'admin'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Kiểm tra trạng thái tài khoản
            if ($user['Status'] === 'Block') {
                echo "<script>alert('Tài khoản của bạn đã bị khóa 🔒');</script>";
                session_unset();
            } elseif (password_verify($password, $user['PasswordHash'])) {
                $_SESSION['Username'] = $user['Username'];
                $_SESSION['FullName'] = $user['FullName'];
                $_SESSION['Role'] = "Nhân viên"; // Hiển thị role tiếng Việt

                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showSuccessPopup('{$user['FullName']}');
                    });
                </script>";
            } else {
                $errors['password'] = "Mật khẩu không đúng!";
            }
        } else {
            $errors['Username'] = "Tên đăng nhập không tồn tại!";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Đăng nhập</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="./admin/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./admin/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="./admin/vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="./admin/vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="./admin/vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="./admin/css/util.css">
    <link rel="stylesheet" type="text/css" href="./admin/css/main.css">
    <link rel="stylesheet" href="./admin/style/generall.css">
    <link rel="stylesheet" href="./admin/icon/css/all.css">
    <style>
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
            color: #666;
        }

        .wrap-input100 {
            position: relative;
        }

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
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>

    <script>
        function showSuccessPopup(Fullname) {
            const overlay = document.getElementById('popupOverlay');
            const popup = document.getElementById('popupSuccess');
            const FullnameElement = document.getElementById('Fullname');

            FullnameElement.textContent = Fullname;
            overlay.style.display = 'block';
            popup.style.display = 'block';

            setTimeout(() => {
                window.location.href = './admin/index/homePage.php';
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordField = document.getElementById('passwordField');

            togglePassword.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
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
                    <img src="./assets/images/LOGO-2.jpg" alt="IMG">
                </div>

                <form class="login100-form validate-form" action="index.php" method="POST">
                    <span class="login100-form-title">
                        <h2> Đăng nhập</h2>
                    </span>

                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" name="Username" placeholder="Tên đăng nhập"
                            value="<?php echo isset($_POST['Username']) ? htmlspecialchars($_POST['Username']) : ''; ?>">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </span>
                    </div>
                    <?php if (!empty($errors['Username'])): ?>
                        <div class="error-message">
                            <?php echo $errors['Username']; ?>
                        </div>
                    <?php endif; ?>

                    <div class="wrap-input100 validate-input">
                        <input class="input100" id="passwordField" type="password" name="PasswordHash" placeholder="Mật khẩu">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                        <span class="toggle-password">
                            <i class="fa fa-eye" aria-hidden="true" style="display:none"></i>
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
        <h3>Xin chào, <span id="Fullname"></span>!</h3>
        <h4>
            <p>Đăng nhập thành công!</p>
        </h4>

    </div>

</body>

</html>