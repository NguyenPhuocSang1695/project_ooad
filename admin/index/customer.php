<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý người dùng</title>
  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="../style/main1.css" rel="stylesheet">
  <link href="../style/customer1.css" rel="stylesheet">
  <link href="../style/LogInfo.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/responsiveCustomer.css">
  <style>
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin-top: 20px;
    }

    .page-btn {
      padding: 5px 15px;
      border: 1px solid #ddd;
      background-color: #fff;
      cursor: pointer;
      border-radius: 4px;
      transition: all 0.3s ease;
    }

    .page-btn:disabled {
      background-color: #f5f5f5;
      cursor: not-allowed;
      color: #999;
    }

    .page-btn:hover:not(:disabled) {
      background-color: #f0f0f0;
    }

    .page-btn.active {
      background-color: #6aa173;
      color: white;
      border-color: #6aa173;
    }

    .pagination-container {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .page-number {
      padding: 5px 10px;
      border: 1px solid #ddd;
      background-color: #fff;
      cursor: pointer;
      border-radius: 4px;
      transition: all 0.3s ease;
    }

    .page-number:hover {
      background-color: #f0f0f0;
    }

    .page-number.active {
      background-color: #6aa173;
      color: white;
      border-color: #6aa173;
    }
  </style>
</head>

<body>
  <div class="header">
    <div class="index-menu">
      <i class="fa-solid fa-bars" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample"></i>
      <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div style="border-bottom: 1px solid rgb(176, 176, 176);" class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasExampleLabel">Mục lục</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="homePage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-house" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Tổng quan</p>
            </div>
          </a>
          <a href="wareHouse.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-warehouse" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Kho hàng</p>
            </div>
          </a>
          <a href="customer.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection" style="background-color: #6aa173;">
                <i class="fa-solid fa-users" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p style="color: black; text-align: center; font-size: 10x;">Người dùng</p>
            </div>
          </a>
          <a href="orderPage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-list-check" style="font-size: 18px; color: #FAD4AE;"></i>
              </button>
              <p style="color:black">Đơn hàng</p>
            </div>
          </a>
          <a href="analyzePage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-chart-simple" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Thống kê</p>
            </div>
          </a>
          <a href="accountPage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-circle-user" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p style="color:black">Tài khoản</p>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="header-left-section">
      <p class="header-left-title">Người dùng</p>
    </div>
    <div class="header-middle-section">
      <img class="logo-store" src="../../assets/images/LOGO-2.jpg">
    </div>
    <div class="header-right-section">
      <div class="bell-notification">
        <i class="fa-regular fa-bell" style="color: #64792c; font-size: 45px; width:100%;"></i>
      </div>
      <div>
        <div class="position-employee">
          <p id="employee-role">Chức vụ</p>
        </div>
        <div class="name-employee">
          <p id="employee-name">Ẩn danh</p>
        </div>
      </div>
      <div>
        <img class="avatar" src="../../assets/images/admin.jpg" alt="" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
      </div>
      <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
        aria-labelledby="offcanvasWithBothOptionsLabel">
        <div style="border-bottom: 1px solid rgb(176, 176, 176);" class="offcanvas-header">
          <img class="avatar" src="../../assets/images/admin.jpg" alt="">
          <div style="display: flex; flex-direction: column; height: 95px;">
            <h4 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">Username</h4>
            <h5 id="employee-displayname">Họ tên</h5> <!-- Thêm id ở đây -->
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="accountPage.php" class="navbar_user">
            <i class="fa-solid fa-user"></i>
            <p>Thông tin cá nhân</p>
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
  <div class="side-bar">
    <div class="backToHome">
      <a href="homePage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection" style=" margin-top: 35px;">
            <i class="fa-solid fa-house" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Tổng quan</p>
        </div>
      </a>
    </div>
    <a href="wareHouse.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-warehouse" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Kho hàng</p>
      </div>
    </a>
    <a href="customer.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection" style="background-color: #6aa173;">
          <i class="fa-solid fa-users" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Người dùng</p>
      </div>
    </a>
    <a href="orderPage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-list-check" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Đơn hàng</p>
      </div>
    </a>
    <a href="analyzePage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-chart-simple" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Thống kê</p>
      </div>
    </a>
    <a href="accountPage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-circle-user" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Tài khoản</p>
      </div>
    </a>
  </div>
  <!-- Nội dung chính -->
  <div class="container-main">
    <div class="left-section-customer">
      <div class="search-container-customer" style="margin-bottom: 20px;">
        <input class="search-bar-customer" type="text" placeholder="Tìm kiếm theo tên, email, số điện thoại..." onkeyup="searchUsers()">
        <button class="search-icon-customer" onclick="searchUsers()">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </div>
      <button type="button" class="btn btn-success" onclick="showAddUserPopup()">Thêm người dùng</button>
      <style>
        #userTable th:nth-child(1),
        th:nth-child(2),
        th:nth-child(3),
        th:nth-child(4),
        th:nth-child(5),
        th:nth-child(6) {
          text-align: center;
        }

        #userTable td {
          text-align: center;
        }
      </style>
      <table class="user-table" id="userTable">
        <thead>
          <tr>
            <th>Tên tài khoản</th>
            <th>Họ và tên</th>
            <th>Số điện thoại</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Trạng thái</th>
            <th></th>
          </tr>
        </thead>
        <tbody>

          <?php
          require_once '../php/connect.php';

          // Check if accessed from homePage
          $fromHomePage = isset($_GET['from']) && $_GET['from'] === 'home';
          
          // Get total number of users based on access source
          $count_query = "SELECT COUNT(*) as total FROM users";
          if ($fromHomePage) {
              $count_query .= " WHERE Role='customer'";
          }
          $count_result = mysqli_query($myconn, $count_query);
          $count_row = mysqli_fetch_assoc($count_result);
          $total_records = $count_row['total'];

          // Calculate total pages
          $records_per_page = 5;
          $total_pages = ceil($total_records / $records_per_page);

          // Get current page
          $page = isset($_GET['page']) ? $_GET['page'] : 1;
          $offset = ($page - 1) * $records_per_page;

          // Get users for current page
          $sql = "SELECT Username, FullName, Phone, Email, Status, Role FROM users";
          if ($fromHomePage) {
              $sql .= " WHERE Role='customer'";
          }
          $sql .= " ORDER BY CASE WHEN Role = 'admin' THEN 0 ELSE 1 END, Role
                   LIMIT $offset, $records_per_page";

          $result = mysqli_query($myconn, $sql);
          while ($row = mysqli_fetch_assoc($result)) {
            $statusText = $row['Status'] === 'Active' ? 'Hoạt động' : 'Đã khóa';
            $statusClass = $row['Status'] === 'Active' ? 'text-success' : 'text-danger';
            $roleText = $row['Role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng';

            echo "<tr>";
            echo "<td>" . $row['Username'] . "</td>";
            echo "<td>" . $row['FullName'] . "</td>";
            echo "<td>" . $row['Phone'] . "</td>";
            echo "<td>" . $row['Email'] . "</td>";
            echo "<td>" . $roleText . "</td>";
            echo "<td class='" . $statusClass . "'>" . $statusText . "</td>";
            echo "<td><button class='btn btn-outline-warning' onclick='showEditUserPopup(\"" . $row['Username'] . "\")'>Chỉnh sửa</button></td>";
            echo "</tr>";
          }
          ?>
        </tbody>
      </table>
      <div class="pagination">
        <?php
        // Previous button
        echo "<button onclick='changePage(" . ($page > 1 ? $page - 1 : 1) . ")' class='page-btn' " . ($page == 1 ? 'disabled' : '') . ">
          <i class='fas fa-chevron-left'></i>
        </button>";

        // Calculate page range
        $maxVisiblePages = 5;
        $startPage = max(1, $page - floor($maxVisiblePages / 2));
        $endPage = min($total_pages, $startPage + $maxVisiblePages - 1);

        if ($endPage - $startPage + 1 < $maxVisiblePages) {
          $startPage = max(1, $endPage - $maxVisiblePages + 1);
        }

        // First page
        if ($startPage > 1) {
          echo "<button onclick='changePage(1)' class='page-number'>1</button>";
          if ($startPage > 2) {
            echo "<span>...</span>";
          }
        }

        // Page numbers
        for ($i = $startPage; $i <= $endPage; $i++) {
          $activeClass = $i == $page ? 'active' : '';
          echo "<button onclick='changePage($i)' class='page-number $activeClass'>$i</button>";
        }

        // Last page
        if ($endPage < $total_pages) {
          if ($endPage < $total_pages - 1) {
            echo "<span>...</span>";
          }
          echo "<button onclick='changePage($total_pages)' class='page-number'>$total_pages</button>";
        }

        // Next button
        echo "<button onclick='changePage(" . ($page < $total_pages ? $page + 1 : $total_pages) . ")' class='page-btn' " . ($page == $total_pages ? 'disabled' : '') . ">
          <i class='fas fa-chevron-right'></i>
        </button>";
        ?>
      </div>
    </div>
  </div>

  <!-- Popup overlay cho thêm người dùng -->
  <div class="user-overlay" id="addUserOverlay">
    <div class="user-content">
      <h3>Thêm Người Dùng Mới</h3>
      <form id="addUserForm" onsubmit="event.preventDefault(); addUser();">
        <div class="form-group">
          <label>Tên tài khoản: <span class="required">*</span></label>
          <input type="text" id="addUsername" required minlength="3">
          <span class="error" id="username-error"></span>
        </div>
        <div class="form-group">
          <label>Họ và tên: <span class="required">*</span></label>
          <input type="text" id="addFullName" required>
          <span class="error" id="fullname-error"></span>
        </div>
        <div class="form-group">
          <label>Email:</label>
          <input type="email" id="addEmail">
          <span class="error" id="email-error"></span>
        </div>
        <div class="form-group">
          <label>Mật khẩu: <span class="required">*</span></label>
          <input type="password" id="addPassword" required minlength="8">
          <span class="error" id="password-error"></span>
        </div>
        <div class="form-group">
          <label>Xác nhận mật khẩu: <span class="required">*</span></label>
          <input type="password" id="addConfirmPassword" required minlength="8">
          <span class="error" id="confirm-password-error"></span>
        </div>
        <div class="form-group">
          <label>Số điện thoại: <span class="required">*</span></label>
          <input type="tel" id="addPhone" required pattern="[0-9]{10}">
          <span class="error" id="phone-error"></span>
        </div>
        <div class="form-group">
          <label>Địa chỉ chi tiết: <span class="required">*</span></label>
          <input type="text" id="addAddress" required placeholder="Số nhà, tên đường...">
          <span class="error" id="address-error"></span>
        </div>
        <div class="form-group">
          <label>Tỉnh/Thành phố: <span class="required">*</span></label>
          <select id="addProvince" required onchange="loadDistricts(this.value)">
            <option value="">Chọn tỉnh/thành phố</option>
            <?php
            require_once '../php/connect.php';
            $sql = "SELECT province_id, name FROM province ORDER BY name";
            $result = mysqli_query($myconn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
              echo "<option value='" . $row['province_id'] . "'>" . $row['name'] . "</option>";
            }
            ?>
          </select>
          <span class="error" id="province-error"></span>
        </div>
        <div class="form-group">
          <label>Quận/Huyện: <span class="required">*</span></label>
          <select id="addDistrict" required onchange="loadWards(this.value)">
            <option value="">Chọn quận/huyện</option>
          </select>
          <span class="error" id="district-error"></span>
        </div>
        <div class="form-group">
          <label>Phường/Xã: <span class="required">*</span></label>
          <select id="addWard" required>
            <option value="">Chọn phường/xã</option>
          </select>
          <span class="error" id="ward-error"></span>
        </div>
        <div class="form-group">
          <label>Vai trò: <span class="required">*</span></label>
          <select id="addRole" required>
            <option value="customer">Khách hàng</option>
            <option value="admin">Quản trị viên</option>
          </select>
          <span class="error" id="role-error"></span>
        </div>
        <div class="form-group">
          <label>Trạng thái:</label>
          <select id="addStatus">
            <option value="Active">Hoạt động</option>
            <option value="Block">Khóa</option>
          </select>
        </div>
        <div class="form-actions">
          <button type="submit" class="save-btn">Thêm</button>
          <button type="button" onclick="closeAddUserPopup()" class="cancel-btn">Hủy</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Popup overlay cho chỉnh sửa -->
  <div class="user-overlay" id="userDetailsOverlay">
    <div class="user-content" id="userDetailsContent">

    </div>
  </div>

  <!-- Popup overlay cho chỉnh sửa người dùng -->
  <div class="user-overlay" id="editUserOverlay">
    <div class="user-content" id="editUserContent">
      <h3>Chỉnh Sửa Thông Tin Người Dùng</h3>
      <form id="editUserForm">
        <div class="form-group">
          <label>Tên tài khoản:</label>
          <input type="text" id="editUsername" readonly>
        </div>
        <div class="form-group">
          <label>Họ và tên: <span class="required">*</span></label>
          <input type="text" id="editFullName" required>
        </div>
        <div class="form-group">
          <label>Email:</label>
          <input type="email" id="editEmail">
        </div>
        <div class="form-group">
          <label>Số điện thoại: <span class="required">*</span></label>
          <input type="tel" id="editPhone" required pattern="[0-9]{10}">
        </div>
        <div class="form-group">
          <label>Địa chỉ chi tiết: <span class="required">*</span></label>
          <input type="text" id="editAddress" required>
        </div>
        <div class="form-group">
          <label>Tỉnh/Thành phố: <span class="required">*</span></label>
          <select id="editProvince" required onchange="loadEditDistricts(this.value)">
            <option value="">Chọn tỉnh/thành phố</option>
            <?php
            $sql = "SELECT province_id, name FROM province ORDER BY name";
            $result = mysqli_query($myconn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
              echo "<option value='" . $row['province_id'] . "'>" . $row['name'] . "</option>";
            }
            ?>
          </select>
        </div>
        <div class="form-group">
          <label>Quận/Huyện: <span class="required">*</span></label>
          <select id="editDistrict" required onchange="loadEditWards(this.value)">
            <option value="">Chọn quận/huyện</option>
          </select>
        </div>
        <div class="form-group">
          <label>Phường/Xã: <span class="required">*</span></label>
          <select id="editWard" required>
            <option value="">Chọn phường/xã</option>
          </select>
        </div>
        <div class="form-group">
          <label>Trạng thái:</label>
          <select id="editStatus">
            <option value="Active">Hoạt động</option>
            <option value="Block">Khóa</option>
          </select>
        </div>
        <div class="form-actions">
          <button type="button" onclick="saveUserEdit()" class="save-btn">Lưu</button>
          <button type="button" onclick="closeEditUserPopup()" class="cancel-btn">Hủy</button>
        </div>
      </form>
    </div>
  </div>
  <script src="../js/hienthikhachhang.js"></script>
  <script src="../js/checklog.js"></script>
  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const cachedUserInfo = localStorage.getItem('userInfo');
      if (cachedUserInfo) {
        const userInfo = JSON.parse(cachedUserInfo);
        document.querySelector('.name-employee p').textContent = userInfo.fullname;
        document.querySelector('.position-employee p').textContent = userInfo.role;
        document.querySelectorAll('.avatar').forEach(img => img.src = userInfo.avatar);
      }
    });

    // Add loading state management
    let isLoading = false;
    let searchTimeout;

    function showLoading() {
      isLoading = true;
      const tableBody = document.querySelector('#userTable tbody');
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align: center;">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang tìm kiếm...</p>
          </td>
        </tr>
      `;
    }

    function hideLoading() {
      isLoading = false;
    }

    function showError(message) {
      const tableBody = document.querySelector('#userTable tbody');
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align: center; color: red;">
            <i class="fas fa-exclamation-circle"></i>
            ${message}
          </td>
        </tr>
      `;
    }

    function renderPagination(currentPage, totalPages) {
      const paginationContainer = document.querySelector('.pagination');
      let paginationHTML = '';

      // Previous button
      paginationHTML += `
        <button onclick="searchUsers(${currentPage - 1})" class="page-btn" ${currentPage === 1 ? 'disabled' : ''}>
          <i class="fas fa-chevron-left"></i>
        </button>`;

      // Page numbers
      const maxVisiblePages = 5;
      let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
      let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

      if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
      }

      if (startPage > 1) {
        paginationHTML += `
          <button onclick="searchUsers(1)" class="page-number">1</button>
          ${startPage > 2 ? '<span>...</span>' : ''}
        `;
      }

      for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
          <button onclick="searchUsers(${i})" class="page-number ${i === currentPage ? 'active' : ''}">
            ${i}
          </button>
        `;
      }

      if (endPage < totalPages) {
        paginationHTML += `
          ${endPage < totalPages - 1 ? '<span>...</span>' : ''}
          <button onclick="searchUsers(${totalPages})" class="page-number">${totalPages}</button>
        `;
      }

      paginationHTML += `
        <button onclick="searchUsers(${currentPage + 1})" class="page-btn" ${currentPage === totalPages ? 'disabled' : ''}>
          <i class="fas fa-chevron-right"></i>
        </button>`;

      paginationContainer.innerHTML = paginationHTML;
    }

    function searchUsers(page = 1) {
      const searchInput = document.querySelector('.search-bar-customer');
      const searchTerm = searchInput.value.trim();
      const tableBody = document.querySelector('#userTable tbody');
      const paginationContainer = document.querySelector('.pagination');

      // Show loading state
      tableBody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </td>
        </tr>
    `;

      fetch(`../php/search-users.php?search=${encodeURIComponent(searchTerm)}&page=${page}&per_page=5`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            tableBody.innerHTML = '';

            if (data.users.length === 0) {
              tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center">
                                <i class="fas fa-search"></i>
                                <p class="mt-2">Không tìm thấy người dùng nào phù hợp</p>
                            </td>
                        </tr>
                    `;
              paginationContainer.innerHTML = '';
            } else {
              data.users.forEach(user => {
                const statusText = user.status === 'Active' ? 'Hoạt động' : 'Đã khóa';
                const statusClass = user.status === 'Active' ? 'text-success' : 'text-danger';
                const roleText = user.role === 'admin' ? 'Quản trị viên' : 'Khách hàng';

                const row = `
                            <tr>
                                <td>${user.username}</td>
                                <td>${user.fullname}</td>
                                <td>${user.phone}</td>
                                <td>${user.email || 'Chưa cập nhật'}</td>
                                <td>${roleText}</td>
                                <td class="${statusClass}">${statusText}</td>
                                <td>
                                    <button class='btn btn-outline-warning' onclick='showEditUserPopup("${user.username}")'>
                                        Chỉnh sửa
                                    </button>
                                </td>
                            </tr>
                        `;
                tableBody.innerHTML += row;
              });

              // Update pagination
              if (data.pagination && data.pagination.totalPages > 0) {
                renderPagination(data.pagination.currentPage, data.pagination.totalPages);
              } else {
                paginationContainer.innerHTML = '';
              }
            }
          } else {
            tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            ${data.message || 'Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại.'}
                        </td>
                    </tr>
                `;
            paginationContainer.innerHTML = '';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        Đã xảy ra lỗi khi tìm kiếm. Vui lòng thử lại.
                    </td>
                </tr>
            `;
          paginationContainer.innerHTML = '';
        });
    }

    // Add debounce to search
    document.querySelector('.search-bar-customer').addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => searchUsers(1), 500);
    });

    // Add event listener for search button
    document.querySelector('.search-icon-customer').addEventListener('click', function() {
      searchUsers(1);
    });

    // Add event listener for Enter key
    document.querySelector('.search-bar-customer').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchUsers(1);
      }
    });

    function loadDistricts(provinceId) {
      if (!provinceId) {
        document.getElementById('addDistrict').innerHTML = '<option value="">Chọn quận/huyện</option>';
        document.getElementById('addWard').innerHTML = '<option value="">Chọn phường/xã</option>';
        return;
      }

      fetch(`../php/get-districts.php?province_id=${provinceId}`)
        .then(response => response.json())
        .then(data => {
          const districtSelect = document.getElementById('addDistrict');
          districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
          data.forEach(district => {
            districtSelect.innerHTML += `<option value="${district.district_id}">${district.name}</option>`;
          });
          document.getElementById('addWard').innerHTML = '<option value="">Chọn phường/xã</option>';
        })
        .catch(error => console.error('Error loading districts:', error));
    }

    function loadWards(districtId) {
      if (!districtId) {
        document.getElementById('addWard').innerHTML = '<option value="">Chọn phường/xã</option>';
        return;
      }

      console.log('Loading wards for district:', districtId);

      fetch(`../php/get-wards.php?district_id=${districtId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          console.log('Received wards data:', data);
          const wardSelect = document.getElementById('addWard');
          wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';

          if (Array.isArray(data) && data.length > 0) {
            data.forEach(ward => {
              wardSelect.innerHTML += `<option value="${ward.ward_id}">${ward.name}</option>`;
            });
          } else {
            console.warn('No wards found for district:', districtId);
            wardSelect.innerHTML += '<option value="" disabled>Không có phường/xã nào</option>';
          }
        })
        .catch(error => {
          console.error('Error loading wards:', error);
          const wardSelect = document.getElementById('addWard');
          wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
          wardSelect.innerHTML += '<option value="" disabled>Lỗi khi tải dữ liệu</option>';
        });
    }

    function addUser() {
      // Get form data
      const password = document.getElementById('addPassword').value;
      const confirmPassword = document.getElementById('addConfirmPassword').value;

      // Check if passwords match
      if (password !== confirmPassword) {
        document.getElementById('confirm-password-error').textContent = 'Mật khẩu xác nhận không khớp';
        return false;
      }

      const formData = new FormData();
      formData.append('username', document.getElementById('addUsername').value.trim());
      formData.append('fullname', document.getElementById('addFullName').value.trim());
      formData.append('email', document.getElementById('addEmail').value.trim());
      formData.append('password', password);
      formData.append('phone', document.getElementById('addPhone').value.trim());
      formData.append('address', document.getElementById('addAddress').value.trim());
      formData.append('province_id', document.getElementById('addProvince').value);
      formData.append('district_id', document.getElementById('addDistrict').value);
      formData.append('ward_id', document.getElementById('addWard').value);
      formData.append('status', document.getElementById('addStatus').value);
      formData.append('role', document.getElementById('addRole').value);

      // Clear previous error messages
      document.querySelectorAll('.error').forEach(error => error.textContent = '');

      // Send request to server
      fetch('../php/add-user.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Add new user to table
            const tableBody = document.querySelector('#userTable tbody');
            const row = document.createElement('tr');
            const statusText = data.user.status === 'Active' ? 'Hoạt động' : 'Đã khóa';
            const statusClass = data.user.status === 'Active' ? 'text-success' : 'text-danger';

            row.innerHTML = `
              <td>${data.user.username}</td>
              <td>${data.user.fullname}</td>
              <td>${data.user.phone}</td>
              <td>${data.user.email}</td>
              <td>${data.user.role}</td>
              <td class="${statusClass}">${statusText}</td>
              <td>
                <button class='btn btn-outline-warning' onclick='showEditUserPopup("${data.user.username}")'>
                  Chỉnh sửa
                </button>
              </td>
            `;
            tableBody.insertBefore(row, tableBody.firstChild);

            // Close popup and show success message
            closeAddUserPopup();
            alert('Thêm người dùng thành công!');

            // Clear form
            document.getElementById('addUserForm').reset();
          } else {
            // Show error message in appropriate error span
            if (data.message.includes('mật khẩu')) {
              document.getElementById('password-error').textContent = data.message;
            } else if (data.message.includes('tài khoản')) {
              document.getElementById('username-error').textContent = data.message;
              document.getElementById('addUsername').focus();
            } else {
              alert(data.message);
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Có lỗi xảy ra khi thêm người dùng');
        });

      return false;
    }


    document.getElementById('addConfirmPassword').addEventListener('input', function() {
      const password = document.getElementById('addPassword').value;
      const confirmPassword = this.value;
      const errorSpan = document.getElementById('confirm-password-error');

      if (password !== confirmPassword) {
        errorSpan.textContent = 'Mật khẩu xác nhận không khớp';
      } else {
        errorSpan.textContent = '';
      }
    });

    document.getElementById('addPassword').addEventListener('input', function() {
      const password = this.value;
      const confirmPassword = document.getElementById('addConfirmPassword').value;
      const errorSpan = document.getElementById('confirm-password-error');

      if (confirmPassword && password !== confirmPassword) {
        errorSpan.textContent = 'Mật khẩu xác nhận không khớp';
      } else {
        errorSpan.textContent = '';
      }
    });

    function showEditUserPopup(username) {
      fetch(`../php/get-user-details.php?username=${username}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const user = data.user;
            const currentUser = JSON.parse(localStorage.getItem('userInfo'));
            const isCurrentUser = currentUser && currentUser.username === user.username;

            document.getElementById('editUsername').value = user.username;
            document.getElementById('editFullName').value = user.fullname;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editPhone').value = user.phone;
            document.getElementById('editAddress').value = user.address;

            // Set province and load its districts
            const provinceSelect = document.getElementById('editProvince');
            provinceSelect.value = user.province_id;
            loadEditDistricts(user.province_id, user.district_id, user.ward_id);

            // Chỉ vô hiệu hóa select box trạng thái nếu là tài khoản của chính mình
            const statusSelect = document.getElementById('editStatus');
            statusSelect.value = user.status;
            if (isCurrentUser) {
              statusSelect.disabled = true;
              statusSelect.title = "Không thể thay đổi trạng thái của chính mình";
            } else {
              statusSelect.disabled = false;
              statusSelect.title = "";
            }

            // Show the popup
            document.getElementById('editUserOverlay').style.display = 'flex';
          } else {
            alert(data.message || 'Không thể tải thông tin người dùng');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Có lỗi xảy ra khi tải thông tin người dùng');
        });
    }

    function loadEditDistricts(provinceId, selectedDistrictId = null, selectedWardId = null) {
      if (!provinceId) {
        document.getElementById('editDistrict').innerHTML = '<option value="">Chọn quận/huyện</option>';
        document.getElementById('editWard').innerHTML = '<option value="">Chọn phường/xã</option>';
        return;
      }

      fetch(`../php/get-districts.php?province_id=${provinceId}`)
        .then(response => response.json())
        .then(data => {
          const districtSelect = document.getElementById('editDistrict');
          districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
          data.forEach(district => {
            districtSelect.innerHTML += `<option value="${district.district_id}">${district.name}</option>`;
          });

          if (selectedDistrictId) {
            districtSelect.value = selectedDistrictId;
            loadEditWards(selectedDistrictId, selectedWardId);
          }
        })
        .catch(error => console.error('Error loading districts:', error));
    }

    function loadEditWards(districtId, selectedWardId = null) {
      if (!districtId) {
        document.getElementById('editWard').innerHTML = '<option value="">Chọn phường/xã</option>';
        return;
      }

      fetch(`../php/get-wards.php?district_id=${districtId}`)
        .then(response => response.json())
        .then(data => {
          const wardSelect = document.getElementById('editWard');
          wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
          data.forEach(ward => {
            wardSelect.innerHTML += `<option value="${ward.ward_id}">${ward.name}</option>`;
          });

          if (selectedWardId) {
            wardSelect.value = selectedWardId;
          }
        })
        .catch(error => console.error('Error loading wards:', error));
    }
  </script>
</body>

</html>