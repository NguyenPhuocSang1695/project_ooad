<?php
require_once '../php/check_session.php';
require_once '../php/connect.php';
$myconn = new DatabaseConnection();
$myconn->connect();

if (!isset($_SESSION['Username'])) {
  header('Location: ../index.php');
  exit();
}

$avatarPath = ($_SESSION['Role'] === 'admin')
  ? "../../assets/images/admin.jpg"
  : "../../assets/images/sang.jpg";

$username = $email = $role = $phone = $address = $FullName = '';
$addressDetail = $wardId = $districtId = $provinceId = '';
$wardName = $districtName = $provinceName = '';

$sql = "SELECT u.Username, u.FullName, u.Email, u.Role, u.Phone, u.address_id, 
        a.address_detail, a.ward_id,
        pr.province_id, pr.name as province_name, 
        dr.district_id, dr.name as district_name, 
        w.ward_id, w.name as ward_name
        FROM users u
        join address a ON u.address_id = a.address_id
        join ward w ON a.ward_id = w.ward_id
        JOIN district dr ON w.district_id = dr.district_id
        JOIN province pr ON dr.province_id = pr.province_id
        WHERE u.Username = ?";

$result = $myconn->queryPrepared($sql, [$_SESSION['Username']]);

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();

  $username = $row['Username'];
  $FullName = $row['FullName'];
  $email = $row['Email'];
  $role = $row['Role'];
  $phone = $row['Phone'];

  $addressDetail = $row['address_detail'];
  $wardId = $row['ward_id'];
  $districtId = $row['district_id'];
  $provinceId = $row['province_id'];

  $wardName = $row['ward_name'];
  $districtName = $row['district_name'];
  $provinceName = $row['province_name'];

  $address = $addressDetail . ', ' . $wardName . ', ' . $districtName . ', ' . $provinceName;
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
              <span class="user-email">📧 <?php echo $email ?></span>
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
              <label>Email:</label>
              <span id="display-email"><?php echo $email ?></span>
            </div>
            <div class="info-row">
              <label>Địa chỉ:</label>
              <span id="display-address"><?php echo $address ?></span>
            </div>
          </div>

          <button class="edit-btn" onclick="openEditModal()">
            <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa thông tin
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

        <div class="form-group">
          <label for="email">Email <span style="color: red;">*</span></label>
          <input type="email" id="email" name="email" value="<?php echo $email ?>" required>
        </div>

        <div class="form-group">
          <label for="province">Tỉnh/Thành phố</label>
          <select id="province" name="province" onchange="loadDistricts()">
            <option value="">-- Chọn Tỉnh/Thành phố --</option>
          </select>
        </div>

        <div class="form-group">
          <label for="district">Quận/Huyện</label>
          <select id="district" name="district" onchange="loadWards()">
            <option value="">-- Chọn Quận/Huyện --</option>
          </select>
        </div>

        <div class="form-group">
          <label for="ward">Phường/Xã</label>
          <select id="ward" name="ward_id">
            <option value="">-- Chọn Phường/Xã --</option>
          </select>
        </div>

        <div class="form-group">
          <label for="address_detail">Địa chỉ chi tiết</label>
          <input type="text" id="address_detail" name="address_detail" value="<?php echo $addressDetail ?>">
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeEditModal()">Hủy</button>
          <button type="submit" class="btn-save">Lưu thay đổi</button>
        </div>
      </form>
    </div>
  </div>

  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    // Dữ liệu ban đầu
    const initialData = {
      provinceId: '<?php echo $provinceId ?>',
      districtId: '<?php echo $districtId ?>',
      wardId: '<?php echo $wardId ?>'
    };

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

    // Load danh sách tỉnh/thành phố
    async function loadProvinces() {
      try {
        const response = await fetch('../php/get_locations.php?action=provinces');
        const data = await response.json();

        if (data.success) {
          const provinceSelect = document.getElementById('province');
          provinceSelect.innerHTML = '<option value="">-- Chọn Tỉnh/Thành phố --</option>';

          data.data.forEach(province => {
            const option = document.createElement('option');
            option.value = province.province_id;
            option.textContent = province.name;
            if (province.province_id === initialData.provinceId) {
              option.selected = true;
            }
            provinceSelect.appendChild(option);
          });

          // Load quận/huyện nếu đã có tỉnh
          if (initialData.provinceId) {
            await loadDistricts();
          }
        }
      } catch (error) {
        console.error('Lỗi khi tải danh sách tỉnh:', error);
      }
    }

    // Load danh sách quận/huyện
    async function loadDistricts() {
      const provinceId = document.getElementById('province').value;
      const districtSelect = document.getElementById('district');
      const wardSelect = document.getElementById('ward');

      districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
      wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';

      if (!provinceId) return;

      try {
        const response = await fetch(`../php/get_locations.php?action=districts&province_id=${provinceId}`);
        const data = await response.json();

        if (data.success) {
          data.data.forEach(district => {
            const option = document.createElement('option');
            option.value = district.district_id;
            option.textContent = district.name;
            if (district.district_id === initialData.districtId) {
              option.selected = true;
            }
            districtSelect.appendChild(option);
          });

          // Load phường/xã nếu đã có quận
          if (initialData.districtId) {
            await loadWards();
          }
        }
      } catch (error) {
        console.error('Lỗi khi tải danh sách quận/huyện:', error);
      }
    }

    // Load danh sách phường/xã
    async function loadWards() {
      const districtId = document.getElementById('district').value;
      const wardSelect = document.getElementById('ward');

      wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';

      if (!districtId) return;

      try {
        const response = await fetch(`../php/get_locations.php?action=wards&district_id=${districtId}`);
        const data = await response.json();

        if (data.success) {
          data.data.forEach(ward => {
            const option = document.createElement('option');
            option.value = ward.ward_id;
            option.textContent = ward.name;
            if (ward.ward_id === initialData.wardId) {
              option.selected = true;
            }
            wardSelect.appendChild(option);
          });
        }
      } catch (error) {
        console.error('Lỗi khi tải danh sách phường/xã:', error);
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
          document.getElementById('display-email').textContent = formData.get('email');

          // Cập nhật địa chỉ nếu có thay đổi
          const wardId = formData.get('ward_id');
          const addressDetail = formData.get('address_detail');
          if (wardId && addressDetail) {
            const wardText = document.getElementById('ward').options[document.getElementById('ward').selectedIndex].text;
            const districtText = document.getElementById('district').options[document.getElementById('district').selectedIndex].text;
            const provinceText = document.getElementById('province').options[document.getElementById('province').selectedIndex].text;
            document.getElementById('display-address').textContent = `${addressDetail}, ${wardText}, ${districtText}, ${provinceText}`;
          }

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
          }, 2000);

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
  </script>