<?php
include '../php/check_session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Đơn Hàng</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="../style/main1.css" rel="stylesheet">
  <link href="../style/orderStyle.css" rel="stylesheet">
  <link href="../style/LogInfo.css" rel="stylesheet">
  <!-- <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet"> -->
  <link rel="stylesheet" href="../../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../style/reponsiveOrder.css">
  <style>
    a {
      text-decoration: none;

    }

    .container-function-selection {
      cursor: pointer;
      font-size: 10px;
      font-weight: bold;
      margin-bottom: 0px;
      width: 54px;
    }

    .button-function-selection {
      margin-bottom: 3px;
    }

    .header-right-section {
      display: flex;
      flex-direction: row;
      gap: 10px;
      margin-top: 20px;
    }

    .name-employee {
      margin-top: -14px;
    }

    .notification {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      padding: 20px 40px;
      border-radius: 8px;
      color: white;
      font-size: 16px;
      font-weight: 500;
      z-index: 9999;
      text-align: center;
      min-width: 300px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      visibility: hidden;
      opacity: 0;
      transition: opacity 0.3s, visibility 0.3s;
    }

    .notification.show {
      visibility: visible;
      opacity: 1;
      animation: fadeInScale 0.3s ease forwards;
    }

    .notification.success {
      background-color: #4CAF50;
    }

    .notification.error {
      background-color: #f44336;
    }

    .notification.info {
      background-color: #2196F3;
    }

    .notification i {
      margin-right: 8px;
      font-size: 18px;
    }

    @keyframes fadeInScale {
      from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.7);
      }

      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }
    }

    @keyframes fadeOutScale {
      from {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }

      to {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.7);
      }
    }

    .notification.hide {
      animation: fadeOutScale 0.3s ease forwards;
    }

    /* Pagination Styles */
    .select_list {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 20px;
      gap: 10px;
    }

    .select_list button {
      padding: 8px 16px;
      border: 1px solid #ddd;
      background-color: white;
      cursor: pointer;
      border-radius: 4px;
      transition: all 0.3s ease;
    }

    .select_list button:hover:not(:disabled) {
      background-color: #6aa173;
      color: white;
      border-color: #6aa173;
    }

    .select_list button:disabled {
      cursor: not-allowed;
      opacity: 0.5;
    }

    #pageNumbers {
      display: flex;
      gap: 5px;
    }

    .page-btn {
      padding: 8px 12px;
      border: 1px solid #ddd;
      background-color: white;
      cursor: pointer;
      border-radius: 4px;
      transition: all 0.3s ease;
    }

    .page-btn:hover:not(.active) {
      background-color: #f0f0f0;
    }

    .page-btn.active {
      background-color: #6aa173;
      color: white;
      border-color: #6aa173;
    }

    .ellipsis {
      padding: 8px 12px;
      color: #666;
    }
  </style>

</head>

<body>
  <div class="header">
    <div class="header-left-section">
      <p class="header-left-title">Đơn Hàng</p>
    </div>
    <div class="header-middle-section">
      <img class="logo-store" src="../../assets/images/LOGO-2.jpg" alt="Logo">
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
        <img class="avatar" src="../../assets/images/admin.jpg" alt="Avatar" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
      </div>
      <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
        aria-labelledby="offcanvasWithBothOptionsLabel">
        <div style="border-bottom: 1px solid rgb(176, 176, 176);" class="offcanvas-header">
          <img class="avatar" src="../../assets/images/admin.jpg" alt="">
          <div class="admin">
            <h4 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">Username</h4>
            <h5 id="employee-displayname">Họ tên</h5>
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

  <div class="index-menu">
    <i class="fa-solid fa-bars" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
      aria-controls="offcanvasExample"></i>
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
      <div style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(176, 176, 176);"
        class="offcanvas-header">
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
            <button class="button-function-selection">
              <i class="fa-solid fa-users" style="font-size: 20px; color: #FAD4AE;"></i>
            </button>
            <p>Người dùng</p>
          </div>
        </a>
        <a href="orderPage.php" style="text-decoration: none; color: black;">
          <div class="container-function-selection">
            <button class="button-function-selection" style="background-color: #6aa173;">
              <i class="fa-solid fa-list-check" style="font-size: 18px; color: #FAD4AE;"></i>
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
    </div>
  </div>

  <div class="main-container">
    <div class="side-bar">
      <div class="backToHome">
        <a href="homePage.php" style="text-decoration: none; color: black;">
          <div class="container-function-selection">
            <button class="button-function-selection" style="margin-top: 35px;">
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
          <button class="button-function-selection">
            <i class="fa-solid fa-users" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Người dùng</p>
        </div>
      </a>
      <a href="orderPage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection" style="background-color: #6aa173;">
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
    <div class="main-content">
      <div class="container-order-management">
        <div class="container-bar-operation">
          <p style="font-size: 30px; font-weight: 700;">Quản lý đơn hàng</p>
        </div>
        <div class="filter-section">
          <button type="button" class="btn btn-primary" id="filter-button" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter"></i> Bộ lọc
          </button>
        </div>

        <!-- Modal hiển thị thông tin cần lọc -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Bộ lọc đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="filter-form">
                  <div class="mb-3">
                    <label for="date-from" class="form-label">Từ ngày:</label>
                    <input type="date" id="date-from" name="date_from" class="form-control">
                  </div>
                  <div class="mb-3">
                    <label for="date-to" class="form-label">Đến ngày:</label>
                    <input type="date" id="date-to" name="date_to" class="form-control">
                  </div>
                  <div class="mb-3">
                    <label for="order-status" class="form-label">Trạng thái:</label>
                    <select id="order-status" name="order_status" class="form-control">
                      <option value="all">Tất cả</option>
                      <option value="execute">Chờ xác nhận</option>
                      <option value="confirmed">Đã xác nhận</option>
                      <option value="ship">Đang giao</option>
                      <option value="success">Hoàn thành</option>
                      <option value="fail">Đã hủy</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="city-select" class="form-label">Tỉnh/Thành phố:</label>
                    <select id="city-select" name="city" class="form-control">
                      <option value="">Chọn thành phố</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="district-select" class="form-label">Quận/Huyện:</label>
                    <select id="district-select" name="district" class="form-control">
                      <option value="">Chọn quận/huyện</option>
                    </select>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" id="reset-filter" class="btn btn-warning">Đặt lại</button>
                    <button type="submit" form="filter-form" class="btn btn-primary" data-bs-dismiss="modal">Áp dụng</button>
                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>

        <div class="statistic-section">
          <style>
            .statistic-table th:nth-child(1),
            th:nth-child(2),
            th:nth-child(3),
            th:nth-child(4),
            th:nth-child(5),
            th:nth-child(6) {
              text-align: center;
            }

            .statistic-table td {
              text-align: center;
            }
          </style>
          <table class="statistic-table">
            <thead>
              <tr>
                <th>Mã đơn hàng</th>
                <th class="hide-index-tablet ">Người mua</th>
                <th>Ngày tạo</th>
                <th class="hide-index-mobile">Giá tiền (VND)</th>
                <th>Trạng thái</th>
                <th>Địa chỉ giao hàng</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="order-table-body">
              <!-- Dynamic content will be inserted here by JavaScript -->
            </tbody>
          </table>
        </div>
        <div id="updateStatusOverlay" class="overlay" style="display: none;">
          <div class="popup">
            <h3>Cập nhật trạng thái đơn hàng</h3>
            <div id="statusOptions" class="status-options"></div>
            <button onclick="closeUpdateStatusPopup()" class="close-btn">Đóng</button>
          </div>
        </div>
        <div class="select_list" id="pagination-container">
          <button id="prevPage">
            < </button>
              <div id="pageNumbers"></div>
              <button id="nextPage">></button>
        </div>
      </div>
    </div>
    <script src="../js/checklog.js"></script>
    <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/orderPage.js"></script>
</body>

</html>