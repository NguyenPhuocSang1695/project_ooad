<?php
include '../php/check_session.php';

class AnalyzePage
{
  private array $userInfo;

  public function __construct()
  {
    // Nếu check_session.php lưu thông tin user vào $_SESSION, dùng nó; nếu không thì dùng fallback
    $this->userInfo = $_SESSION['user'] ?? [
      'fullname' => 'Ẩn danh',
      'role' => 'Chức vụ',
      'avatar' => '../../assets/images/admin.jpg'
    ];
  }

  public function render(): void
  {
    $fullname = htmlspecialchars($this->userInfo['FullName'] ?? $this->userInfo['fullname']);
    $role = htmlspecialchars($this->userInfo['Role'] ?? $this->userInfo['role']);
    $avatar = htmlspecialchars($this->userInfo['Avatar'] ?? $this->userInfo['avatar']);

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Thống Kê Kinh Doanh</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../style/generall.css">
  <link rel="stylesheet" href="../style/analyzeStyle.css">
  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link rel="stylesheet" href="../style/LogInfo.css">
  <link rel="stylesheet" href="../style/reponsiveAnalyze.css">
  <!-- Icons -->
  <link rel="stylesheet" href="../icon/css/all.css">
  <!-- Bootstrap (JS only) -->
  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>
<body>
  <div class="header">
    <div class="index-menu">
      <i class="fa-solid fa-bars" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample">
      </i>
      <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(176, 176, 176);" class="offcanvas-header">
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
                <i class="fa-solid fa-warehouse" style="font-size: 20px; color: #FAD4AE;"></i></button>
              <p>Kho hàng</p>
            </div>
          </a>
          <a href="customer.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-users" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p style="color: black;text-align: center; font-size: 10x;">Người dùng</p>
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
              <button class="button-function-selection" style="background-color: #6aa173;">
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
      <p class="header-left-title">Thống kê</p>
    </div>

    <div class="header-middle-section">
      <img class="logo-store" src="../../assets/images/LOGO-2.jpg" alt="logo">
    </div>

    <div class="header-right-section">
      <div class="bell-notification">
        <i class="fa-regular fa-bell" style="color: #64792c; font-size: 45px; width:100%;"></i>
      </div>
      <div>
        <div class="position-employee">
          <p id="employee-role">{$role}</p>
        </div>
        <div class="name-employee">
          <p id="employee-name">{$fullname}</p>
        </div>
      </div>
      <div>
        <img class="avatar" src="{$avatar}" alt="" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
      </div>
      <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
        aria-labelledby="offcanvasWithBothOptionsLabel">
        <div style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(176, 176, 176);" class="offcanvas-header">
          <img class="avatar" src="{$avatar}" alt="">
          <div style="display: flex; flex-direction: column; height: 95px;">
            <h4 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">Username</h4>
            <h5 id="employee-displayname">{$fullname}</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="accountPage.php" class="navbar_user">
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
              <i class="fa-solid fa-house" style="font-size: 20px; color: #FAD4AE;"></i>
            </button>
            <p>Tổng quan</p>
          </div>
        </a>
      </div>
      <a href="wareHouse.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-warehouse" style="font-size: 20px; color: #FAD4AE;"></i></button>
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
          <button class="button-function-selection">
            <i class="fa-solid fa-list-check" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Đơn hàng</p>
        </div>
      </a>
      <a href="analyzePage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection" style="background-color: #6aa173;">
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

    <!-- Container 1: Khách hàng -->
    <div class="container">
      <div class="filter-section">
        <form id="analyze-form" method="POST" action="">
          <label for="start-date">Từ ngày:</label>
          <input type="date" id="start-date" name="start_date" required>
          <label for="end-date">Đến ngày:</label>
          <input type="date" id="end-date" name="end_date" required>
          <button type="submit" name="submit">Lọc <i class="fa-solid fa-filter"></i></button>
        </form>
      </div>
        <h2>Khách hàng mua hàng nhiều nhất</h2>
      <table>
        <thead>
          <tr style="text-align: center;">
            <th>STT</th>
            <th>Tên khách hàng</th>
            <th>Số lượng đơn đã mua</th>
            <th class="total-amount">Tổng tiền (VND)</th>
            <th>Chi tiết đơn</th>
          </tr>
        </thead>
        <tbody id="customer-table">
          <tr>
            <td colspan="5" style="text-align: center;">Vui lòng chọn khoảng thời gian phù hợp</td>
          </tr>
        </tbody>
      </table>
      
    <h2 class="section-title">Sản phẩm bán chạy nhất</h2>
<table class="table" id="top-products-table">
  <thead>
    <tr style="text-align: center;">
      <th>STT</th>
      <th>Tên sản phẩm</th>
      <th>Số lượng bán</th>
      <th>Tổng tiền (VND)</th>
    </tr>
  </thead>
  <style>
    #top-products-table thead tr th {
      background: #1c8e2e;
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
}
  </style>
  <tbody id="top-products-body">
    <tr><td colspan="4" style="text-align:center;">Đang tải...</td></tr>
  </tbody>
</table>

<h2 class="section-title">Sản phẩm bán chậm nhất</h2>
<table class="table" id="worst-products-table">
  <thead>
    <tr style="text-align: center;">
      <th>STT</th>
      <th>Tên sản phẩm</th>
      <th>Số lượng bán</th>
      <th>Tổng tiền (VND)</th>
    </tr>
  </thead>
  <style>
    #worst-products-table thead tr th {
      background: #1c8e2e;
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
    
}
  </style>
  <tbody id="worst-products-body">
    <tr><td colspan="4" style="text-align:center;">Đang tải...</td></tr>
  </tbody>
</table>




      <div class="revenue-summary">
        <h2>Tổng quan doanh thu</h2>
        <div class="summary-card revenue-card">
          <i class="fa-solid fa-money-bill-wave"></i>
          <h3>Tổng doanh thu</h3>
          <p id="total-revenue">0 </p>
          <span class="period">Trong khoảng thời gian đã chọn</span>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderDetailModal" class="order-modal">
      <div class="order-modal-content">
        <span class="order-modal-close">&times;</span>
        <div class="order-detail-header">
          <h2>Chi tiết đơn hàng #<span id="modalOrderId"></span></h2>
          <span id="modalOrderStatus" class="status-badge"></span>
        </div>

        <div class="order-info-grid">
          <div class="order-info-section">
            <h3>Thông tin đơn hàng</h3>
            <div class="info-row">
              <span class="info-label">Ngày đặt:</span>
              <span id="modalOrderDate" class="info-value"></span>
            </div>
            <div class="info-row">
              <span class="info-label">Phương thức TT:</span>
              <span id="modalPaymentMethod" class="info-value"></span>
            </div>
          </div>

          <div class="order-info-section">
            <h3>Thông tin khách hàng</h3>
            <div class="info-row">
              <span class="info-label">Họ tên:</span>
              <span id="modalReceiverName" class="info-value"></span>
            </div>
            <div class="info-row">
              <span class="info-label">Số điện thoại:</span>
              <span id="modalReceiverPhone" class="info-value"></span>
            </div>
            <div class="info-row">
              <span class="info-label">Địa chỉ:</span>
              <span id="modalReceiverAddress" class="info-value"></span>
            </div>
          </div>
        </div>

        <div class="order-products">
          <h3>Sản phẩm</h3>
          <div id="modalProductList" class="product-list"></div>
        </div>

        <div class="order-summary">
          <span class="total-amount">Tổng tiền: <span id="modalTotalAmount"></span></span>
        </div>
      </div>
    </div>

  </div>

  <script src="../js/analyzePage.js"></script>
  <script src="../js/checklog.js"></script>
  <script>
    // Lưu ý: vẫn giữ logic client-side để cập nhật UI nhanh
    document.addEventListener('DOMContentLoaded', () => {
      // nếu có dữ liệu cached từ JS, vẫn dùng nó
      const cachedUserInfo = localStorage.getItem('userInfo');
      if (cachedUserInfo) {
        const userInfo = JSON.parse(cachedUserInfo);
        document.querySelector('.name-employee p').textContent = userInfo.fullname;
        document.querySelector('.position-employee p').textContent = userInfo.role;
        document.querySelectorAll('.avatar').forEach(img => img.src = userInfo.avatar);
      }
    });
  </script>

  <style>
    input[type="date"] {
      -webkit-appearance: none;
      appearance: none;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      transition: all 0.3s ease;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .container-function-selection { cursor: pointer; font-size: 10px; font-weight: bold; margin-bottom: 0px; width: 54px; }
    .button-function-selection { margin-bottom: 3px; }
    .header-right-section { display: flex; flex-direction: row; gap: 10px; margin-top: 20px; }
    .name-employee { margin-top: -14px; }
  </style>

</body>
</html>
HTML;
  }
}

// Instantiate and render page
$page = new AnalyzePage();
$page->render();
?>