<?php
require_once '../php/check_session.php';
require_once '../php/connect.php';
$myconn = new DatabaseConnection();
$myconn->connect();

if (!isset($_SESSION['Username'])) {
  header('Location: ../../index.php');
  exit();
}

$avatarPath = ($_SESSION['Role'] === 'admin')
  ? "../../assets/images/admin.jpg"
  : "../../assets/images/sang.jpg";

$username = $role = $phone = $FullName = $dategeneration = '';

$sql = "SELECT u.Username, u.FullName, u.Role, u.Phone, u.DateGeneration
        FROM users u
        WHERE u.Username = ?";

$result = $myconn->queryPrepared($sql, [$_SESSION['Username']]);

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();

  $username = $row['Username'];
  $FullName = $row['FullName'];
  $role = $row['Role'];
  $phone = $row['Phone'];
  $dategeneration = $row['DateGeneration'];
} else {
  echo "Không tìm thấy thông tin người dùng.";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Tài khoản</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link rel="stylesheet" href="../icon/css/all.css">
  <link rel="stylesheet" href="../style/generall.css">
  <link rel="stylesheet" href="../style/main1.css">
  <link rel="stylesheet" href="../style/accountStyle.css">
  <link rel="stylesheet" href="../style/account.css">
  <link rel="stylesheet" href="./asset/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../style/LogInfo.css">
  <link rel="stylesheet" href="../style/reponsiveAccount.css">
</head>

<body>
  <div class="header">
    <div class="index-menu">
      <i class="fa-solid fa-bars" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample">
      </i>
      <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div style=" 
        border-bottom-width: 1px;
        border-bottom-style: solid;
        border-bottom-color: rgb(176, 176, 176);" class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasExampleLabel">Mục lục</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="homePage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-house" style="
                  font-size: 20px;
                  color: #FAD4AE;
                  "></i>
              </button>
              <p>Tổng quan</p>
            </div>
          </a>
          <a href="wareHouse.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-warehouse" style="font-size: 20px;
                  color: #FAD4AE;
              "></i></button>
              <p>Kho hàng</p>
            </div>
          </a>
          <a href="customer.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-users" style="
                              font-size: 20px;
                              color: #FAD4AE;
                          "></i>
              </button>
              <p style="color: black;text-align: center; font-size: 10x;">Người dùng</p>
            </div>
          </a>
          <a href="orderPage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-list-check" style="
                          font-size: 18px;
                          color: #FAD4AE;
                          "></i>
              </button>
              <p style="color:black">Đơn hàng</p>
            </div>
          </a>
          <a href="importReceipt.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-file-import" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Nhập hàng</p>
            </div>
          </a>
          <a href="analyzePage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-chart-simple" style="
                          font-size: 20px;
                          color: #FAD4AE;
                      "></i>
              </button>
              <p>Thống kê</p>
            </div>
          </a>
          <a href="supplierManage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection" style="background-color: #6aa173;">
                <i class="fa-solid fa-truck-field" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Nhà cung cấp</p>
            </div>
          </a>
          <a href="voucherManage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-ticket" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Mã giảm giá</p>
            </div>
          </a>
          <a href="accountPage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection" style="background-color: #6aa173;">
                <i class="fa-solid fa-circle-user" style="
                           font-size: 20px;
                           color: #FAD4AE;
                       "></i>
              </button>
              <p style="color:black">Tài khoản</p>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="header-left-section">
      <p class="header-left-title">Tài khoản</p>
    </div>
    <div class="header-middle-section">
      <img class="logo-store" src="../../assets/images/LOGO-2.jpg">
    </div>
    <div class="header-right-section">
      <div class="bell-notification">
        <i class="fa-regular fa-bell" style="
                        color: #64792c;
                        font-size: 45px;
                        width:100%;
                        "></i>
      </div>
      <div>
        <div class="position-employee">
          <p><?php echo $_SESSION['Role'] ?></p>
        </div>
        <div class="name-employee">
          <p><?php echo $_SESSION['FullName'] ?></p>
        </div>
      </div>
      <div>
        <img class="avatar" src="<?php echo $avatarPath; ?>" alt="Avatar" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
      </div>
      <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
        aria-labelledby="offcanvasWithBothOptionsLabel">
        <div style=" 
            border-bottom-width: 1px;
            border-bottom-style: solid;
            border-bottom-color: rgb(176, 176, 176);" class="offcanvas-header">
          <img class="avatar" src="<?php echo $avatarPath; ?>" alt="Avatar">
          <div class="admin">
            <h4 class="offcanvas-title" id="offcanvasWithBothOptionsLabel"><?php echo $_SESSION['FullName'] ?></h4>
            <h5><?php echo $_SESSION['Username'] ?></h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="" class="navbar_user">
            <i class="fa-solid fa-user"></i>
            <p>Thông tin cá nhân </p>
          </a>
          <a href="#logoutModal" class="navbar_logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            <p>Đăng xuất</p>
          </a>
          <div id="logoutModal" class="modal">
            <div class="modal_content">
              <h2>Xác nhận đăng xuất</h2>
              <p>Bạn có chắc chắn muốn đăng xuất không?</p>
              <div class="modal_actions">
                <a href="../php/logout.php" class="btn_2 confirm">Đăng xuất</a>
                <a href="#" class="btn_2 cancel">Hủy</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="main-container">
    <div class="side-bar">
      <div class="backToHome">
        <a href="homePage.php" style="text-decoration: none; color: black;">
          <div class="container-function-selection">
            <button class="button-function-selection" style="margin-top: 35px;">
              <i class="fa-solid fa-house" style="
              font-size: 20px;
              color: #FAD4AE;
              "></i>
            </button>
            <p>Tổng quan</p>
          </div>
        </a>
      </div>
      <a href="wareHouse.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-warehouse" style="font-size: 20px;
            color: #FAD4AE;
        "></i></button>
          <p>Kho hàng</p>
        </div>
      </a>
      <a href="customer.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-users" style="
                        font-size: 20px;
                        color: #FAD4AE;
                    "></i>
          </button>
          <p>Người dùng</p>
        </div>
      </a>
      <a href="orderPage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-list-check" style="
                    font-size: 20px;
                    color: #FAD4AE;
                    "></i>
          </button>
          <p>Đơn hàng</p>
        </div>
      </a>
      <a href="importReceipt.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-file-import" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Nhập hàng</p>
        </div>
      </a>
      <a href="analyzePage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-chart-simple" style="
                    font-size: 20px;
                    color: #FAD4AE;
                "></i>
          </button>
          <p>Thống kê</p>
        </div>
      </a>
      <a href="supplierManage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-truck-field" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Nhà cung cấp</p>
        </div>
      </a>
      <a href="voucherManage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-ticket" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Mã giảm giá</p>
        </div>
      </a>
      <a href="accountPage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection" style="background-color: #6aa173;">
            <i class="fa-solid fa-circle-user" style="
                     font-size: 20px;
                     color: #FAD4AE;
                 "></i>
          </button>
          <p>Tài khoản</p>
        </div>
      </a>
    </div>
    <div class="content-area">
      <div class="header-section">
        <div class="header-left">
          <h1>Thông tin tài khoản</h1>
          <p>Chi tiết thông tin của nhân viên hiện tại</p>
        </div>
        <div class="header-right">
          <div class="user-info">
            <span class="user-icon">NC</span>
            <div style="display: flex; flex-direction: column;">
              <span class="user-name"><?php echo $username ?></span>

            </div>
          </div>
        </div>
      </div>
      <div class="main-content">
        <div class="personal-info">
          <h1>Thông tin cá nhân</h1><br>
          <p>Thông tin chi tiết của nhân viên hiện tại</p>

          <div class="info-container">
            <div class="info-row">
              <label>Họ và tên:</label>
              <span id="display-fullname"><?php echo $FullName ?></span>
            </div>

            <div class="info-row">
              <label>Số điện thoại:</label>
              <span id="display-phone"><?php echo $phone ?></span>
            </div>

            <div class="info-row">
              <label>Ngày tạo tài khoản:</label>
              <span><?php echo $dategeneration ?></span>
            </div>
          </div>

          <button class="edit-btn" onclick="openEditModal()">
            <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa thông tin
          </button>

          <button class="edit-btn" onclick="openChangePasswordModal()">
            <i class="fa-solid fa-lock"></i> Đổi mật khẩu
          </button>

        </div>
      </div>
    </div>
  </div>

  <!-- Modal chỉnh sửa thông tin -->
  <div id="editModal" class="modal">
    <div class="modal-content-edit">
      <div class="modal-header">
        <h2>Chỉnh sửa thông tin cá nhân</h2>
        <button class="close" onclick="closeEditModal()">&times;</button>
      </div>

      <div id="alert" class="alert"></div>

      <form id="editForm">
        <div class="form-group">
          <label for="fullname">Họ và tên <span style="color: red;">*</span></label>
          <input type="text" id="fullname" name="fullname" value="<?php echo $FullName ?>" required>
        </div>

        <div class="form-group">
          <label for="phone">Số điện thoại <span style="color: red;">*</span></label>
          <input type="tel" id="phone" name="phone" value="<?php echo $phone ?>" required>
        </div>


        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeEditModal()">Hủy</button>
          <button type="submit" class="btn-save">Lưu thay đổi</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal đổi mật khẩu -->
  <div id="changePasswordModal" class="modal">
    <div class="modal-content-edit">
      <div class="modal-header">
        <h2>Đổi mật khẩu</h2>
        <button class="close" onclick="closeChangePasswordModal()">&times;</button>
      </div>

      <div id="alert-password" class="alert"></div>

      <form id="changePasswordForm">
        <div class="form-group">
          <label for="old_password">Mật khẩu hiện tại <span style="color: red;">*</span></label>
          <input type="password" id="old_password" name="old_password" required>
        </div>

        <div class="form-group">
          <label for="new_password">Mật khẩu mới <span style="color: red;">*</span></label>
          <input type="password" id="new_password" name="new_password" required>
        </div>

        <div class="form-group">
          <label for="confirm_password">Xác nhận mật khẩu mới <span style="color: red;">*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeChangePasswordModal()">Hủy</button>
          <button type="submit" class="btn-save">Đổi mật khẩu</button>
        </div>
      </form>
    </div>
  </div>


  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    function openEditModal() {
      document.getElementById('editModal').style.display = 'block';
      loadProvinces();
    }

    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
      document.getElementById('alert').style.display = 'none';
    }

    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
      const modal = document.getElementById('editModal');
      if (event.target === modal) {
        closeEditModal();
      }
    }

    // Xử lý submit form
    document.getElementById('editForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const alertDiv = document.getElementById('alert');
      const formData = new FormData(this);

      try {
        const response = await fetch('../php/update-account.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          // Hiển thị thông báo thành công
          alertDiv.className = 'alert alert-success';
          alertDiv.textContent = result.message;
          alertDiv.style.display = 'block';

          // Cập nhật hiển thị trên trang
          document.getElementById('display-fullname').textContent = formData.get('fullname');
          document.getElementById('display-phone').textContent = formData.get('phone');


          // Cập nhật tên ở header
          const nameElements = document.querySelectorAll('.name-employee p, .user-name, .offcanvas-title');
          nameElements.forEach(el => {
            if (el.classList.contains('user-name')) {
              return; // Username không đổi
            }
            el.textContent = formData.get('fullname');
          });

          // Đóng modal sau 2 giây
          setTimeout(() => {
            closeEditModal();
          }, 100);

        } else {
          // Hiển thị thông báo lỗi
          alertDiv.className = 'alert alert-error';
          alertDiv.textContent = result.message;
          alertDiv.style.display = 'block';
        }
      } catch (error) {
        alertDiv.className = 'alert alert-error';
        alertDiv.textContent = 'Có lỗi xảy ra: ' + error.message;
        alertDiv.style.display = 'block';
      }
    });



    function openChangePasswordModal() {
      document.getElementById('changePasswordModal').style.display = 'block';
    }

    function closeChangePasswordModal() {
      document.getElementById('changePasswordModal').style.display = 'none';
      document.getElementById('alert-password').style.display = 'none';
    }

    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
      const modal1 = document.getElementById('editModal');
      const modal2 = document.getElementById('changePasswordModal');
      if (event.target === modal1) closeEditModal();
      if (event.target === modal2) closeChangePasswordModal();
    }

    // Xử lý submit đổi mật khẩu
    document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const alertDiv = document.getElementById('alert-password');
      const formData = new FormData(this);

      // Kiểm tra xác nhận mật khẩu
      if (formData.get('new_password') !== formData.get('confirm_password')) {
        alertDiv.className = 'alert alert-error';
        alertDiv.textContent = 'Mật khẩu xác nhận không khớp!';
        alertDiv.style.display = 'block';
        return;
      }

      try {
        const response = await fetch('../php/change-password.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          alertDiv.className = 'alert alert-success';
          alertDiv.textContent = result.message;
          alertDiv.style.display = 'block';
          setTimeout(() => closeChangePasswordModal(), 1500);
        } else {
          alertDiv.className = 'alert alert-error';
          alertDiv.textContent = result.message;
          alertDiv.style.display = 'block';
        }
      } catch (error) {
        alertDiv.className = 'alert alert-error';
        alertDiv.textContent = 'Lỗi: ' + error.message;
        alertDiv.style.display = 'block';
      }
    });
  </script>