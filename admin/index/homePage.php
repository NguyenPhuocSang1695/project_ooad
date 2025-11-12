<?php
include '../php/connect.php';
// include '../php/check_session.php';

$connectDb = new DatabaseConnection();
$connectDb->connect();
$myconn = $connectDb->getConnection();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý bán hàng - Tổng quan trang Admin</title>

  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <!-- <link href="../style/main1.css" rel="stylesheet"> -->
  <link href="../style/main2.css" rel="stylesheet">
  <link href="../style/LogInfo.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/responsiveHomePage.css">

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
              <button class="button-function-selection" style="background-color: #6aa173;">
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
      <p class="header-left-title">Tổng quan</p>
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

  <div class="side-bar">
    <div class="backToHome">
      <a href="homePage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection" style="background-color: #6aa173; margin-top: 35px;">
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

  <!-- MAIN -->
  <div class="container-main">
    <div class="dashboard-overview">
      <?php
      // Tổng số đơn hàng
      $sql = "SELECT COUNT(*) AS totalExOder FROM orders";
      $result = $connectDb->query($sql);
      while ($row = $result->fetch_assoc()) {
        echo "<a href='./orderPage.php?order_status=execute' style='text-decoration: none; color: inherit;'>
            <div class='overview-card'>
              <h3>{$row['totalExOder']}</h3> <br>
              <p>Đơn hàng</p>
            </div>
          </a>";
      }

      // Tổng số sản phẩm trong kho
      $sql = "SELECT COUNT(*) AS QuantityProduct FROM products";
      $result = $connectDb->query($sql);
      while ($row = $result->fetch_assoc()) {
        echo "<a href='./wareHouse.php' style='text-decoration: none; color: inherit;'>
            <div class='overview-card'>
              <h3>{$row['QuantityProduct']}</h3> <br>
              <p>Sản phẩm trong kho</p>
            </div>
          </a>";
      }

      // Sản phẩm hết hàng (ví dụ: tồn kho = 0)
      $sql = "SELECT COUNT(*) AS lowStock FROM products WHERE quantity_in_stock = 0";
      $result = $connectDb->query($sql);
      while ($row = $result->fetch_assoc()) {
        echo "<a href='./wareHouse.php?status=out_of_stock' style='text-decoration: none; color: inherit;'>
            <div class='overview-card'>
              <h3>{$row['lowStock']}</h3> <br>
              <p>Sản phẩm hết hàng</p>
            </div>
          </a>";
      }

      // Sản phẩm sắp hết hàng (ví dụ: tồn kho > 0 và <= 5)
      $sql = "SELECT COUNT(*) AS lowStock FROM products WHERE quantity_in_stock > 0 AND quantity_in_stock <=5";
      $result = $connectDb->query($sql);
      while ($row = $result->fetch_assoc()) {
        echo "<a href='./wareHouse.php?status=near_out_of_stock' style='text-decoration: none; color: inherit;'>
            <div class='overview-card'>
              <h3>{$row['lowStock']}</h3> <br>
              <p>Sản phẩm sắp hết hàng</p>
            </div>
          </a>";
      }

      // Tổng số khách hàng
      $sql = "SELECT COUNT(*) AS QuantityUser FROM users WHERE Role='customer'";
      $result = $connectDb->query($sql);
      while ($row = $result->fetch_assoc()) {
        echo "<a href='./customer.php?from=home' style='text-decoration: none; color: inherit;'>
            <div class='overview-card'>
              <h3>{$row['QuantityUser']}</h3> <br>
              <p>Khách hàng</p>
            </div>
          </a>";
      }
      ?>
    </div>


    <div class="order-section">
      <p class="section-title">Đơn hàng mới</p>
      <!-- <a href="orderPage.php"><button class="button-handle" style="white-space:nowrap;">Xem thêm</button></a> -->
      <?php
      $sql = "SELECT o.*, u.FullName
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.user_id

          
          ORDER BY o.OrderID DESC
          LIMIT 5";

      $result = $connectDb->query($sql);

      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<div class='overview-order'>
              <div class='info-overview-order'>
                <p>{$row['FullName']} <span class='label customer'>Customer</span></p>
                <p>Mã đơn hàng: {$row['OrderID']}</p>
                <p> | </p>
                <p>Ngày đặt hàng: " . date('d/m/Y', strtotime($row['DateGeneration'])) . "</p>
                
              </div>
              <div>
                <a href='orderPage.php' style='text-decoration: none; color: black;'>
                  <button class='button-handle'>Xem đơn hàng</button>
                </a>
              </div>
            </div>";
        }

        echo "<div class= 'd-flex justify-content-center mt-3'>
              <a href='orderPage.php'><button class='button-handle' style='white-space:nowrap;'>Xem thêm</button></a>
              </div>";
      } else {
        echo "<div class='overview-order'><p>Không có đơn hàng nào</p></div>";
      }
      ?>
    </div>

    <div class="inventory-section">
      <p class="section-title">Sản phẩm mới</p>

      <?php
      $sql = "SELECT p.*, c.CategoryName 
              FROM products p 
              LEFT JOIN categories c ON p.CategoryID = c.CategoryID 
              WHERE p.Status = 'appear'
              ORDER BY p.ProductID DESC
              LIMIT 5";
      $result = $connectDb->query($sql);

      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<div class='overview-order'>
                  <div><img class='avatar-customer' src='../..{$row['ImageURL']}' alt='Product'></div>
                  <div class='info-overview-order'>
                    <p>{$row['ProductName']} <span class='label product'>Product</span></p>
                    <p>Danh mục: {$row['CategoryName']}</p>
                    <p> | </p>
                    <p>Giá: " . number_format($row['Price'], 0, ',', '.') . " VNĐ</p>
                  </div>
                  <div><a href='wareHouse.php?edit={$row['ProductID']}' style='text-decoration: none; color: black;'>
                    <button class='button-handle'><p>Chi tiết</p></button></a>
                  </div>
                </div>";
        }
        echo "<div class= 'd-flex justify-content-center mt-3'>
              <a href='wareHouse.php'><button class='button-handle' style='white-space:nowrap;'>Xem thêm</button></a>
              </div>";
      } else {
        echo "<div class='overview-order'><p>Không có sản phẩm mới</p></div>";
      }

      $connectDb->close();
      ?>
    </div>
  </div>

  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../js/checklog.js"></script>
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
  </script>

  <!-- Bootstrap JS -->
  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../js/checklog.js"></script>
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
  </script>
</body>

</html>