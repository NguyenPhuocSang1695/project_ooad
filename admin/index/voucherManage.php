<?php

require_once '../php/check_session.php';
require_once '../php/connect.php';
if (!isset($_SESSION['Phone'])) {
  header('Location: ../index.php');
  exit();
}

$myconn = new DatabaseConnection();
try {
  $myconn->connect();
  
  // Lấy danh sách voucher
  $sqlVouchers = "SELECT * FROM vouchers ORDER BY id DESC";
  $voucherResult = $myconn->query($sqlVouchers);
} catch (Exception $e) {
  // Log lỗi
  error_log("Lỗi voucherManage: " . $e->getMessage());
  $voucherResult = null;
  $errorMessage = $e->getMessage();
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
        <img class="avatar" src="../../assets/images/sang.jpg" alt="Avatar" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
      </div>
      <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
        aria-labelledby="offcanvasWithBothOptionsLabel">
        <div style=" 
            border-bottom-width: 1px;
            border-bottom-style: solid;
            border-bottom-color: rgb(176, 176, 176);" class="offcanvas-header">
          <img class="avatar" src="../../assets/images/sang.jpg" alt="Avatar">
          <div class="admin">
            <h4 class="offcanvas-title" id="offcanvasWithBothOptionsLabel"><?php echo $_SESSION['FullName'] ?></h4>
            <h5><?php echo $_SESSION['Phone'] ?></h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="./accountPage.php" class="navbar_user">
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
      <a href="voucherManage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection" style="background-color: #6aa173;">
            <i class="fa-solid fa-ticket" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Mã giảm giá</p>
        </div>
      </a>
      <a href="accountPage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection">
            <i class="fa-solid fa-circle-user" style="
                     font-size: 20px;
                     color: #FAD4AE;
                 "></i>
          </button>
          <p>Tài khoản</p>
        </div>
      </a>
    </div>




    <div class="voucher-wrapper">
      <!-- FORM THÊM VOUCHER -->
      <div class="voucher-form-container">
        <h2>🎟️ Thêm Voucher Mới</h2>
        <?php if (isset($errorMessage)): ?>
          <div style="background: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <strong>❌ Lỗi:</strong> <?php echo htmlspecialchars($errorMessage); ?>
          </div>
        <?php endif; ?>
        <form class="voucher-form">
          <div class="form-group">
            <label for="name">Tên voucher:</label>
            <input type="text" id="name" name="name" required placeholder="VD: GiamGia20">
          </div>

          <div class="form-group">
            <label for="percen_decrease">Phần trăm giảm (%):</label>
            <input type="number" id="percen_decrease" name="percen_decrease" min="0" max="100" required placeholder="VD: 20">
          </div>

          <div class="form-group">
            <label for="condition">Điều kiện (VNĐ):</label>
            <input type="number" id="condition" name="condition" min="0" required placeholder="VD: 500000">
          </div>

          <div class="form-group">
            <label for="status">Trạng thái:</label>
            <select name="status" id="status">
              <option value="active">Hoạt động</option>
              <option value="inactive">Ngừng hoạt động</option>
            </select>
          </div>

          <div class="form-btns">
            <button type="submit" class="btn-submit">
              <i class="fa-solid fa-plus"></i> Thêm Voucher
            </button>
          </div>
        </form>

      </div>

      <!-- DANH SÁCH VOUCHER -->
      <div class="voucher-list-container">
        <div class="list-header">
          <h2>📋 Danh Sách Voucher</h2>
          <div class="search-box">
            <i class="fa-solid fa-search"></i>
            <input type="text" id="searchVoucher" placeholder="Tìm kiếm voucher...">
          </div>
        </div>

        <div class="voucher-list">
          <?php if ($voucherResult && $voucherResult->num_rows > 0): ?>
            <?php while ($voucher = $voucherResult->fetch_assoc()): ?>
              <div class="voucher-card" data-id="<?php echo $voucher['id']; ?>">
                <div class="voucher-header">
                  <div class="voucher-name">
                    <i class="fa-solid fa-ticket"></i>
                    <strong><?php echo htmlspecialchars($voucher['name']); ?></strong>
                  </div>
                  <span class="voucher-status <?php echo $voucher['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo $voucher['status'] === 'active' ? '✓ Hoạt động' : '✕ Tạm dừng'; ?>
                  </span>
                </div>

                <div class="voucher-details">
                  <div class="detail-item">
                    <span class="detail-label">Giảm giá:</span>
                    <span class="detail-value discount"><?php echo $voucher['percen_decrease']; ?>%</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Điều kiện:</span>
                    <span class="detail-value">≥ <?php echo number_format($voucher['conditions'], 0, ',', '.'); ?>đ</span>
                  </div>
                </div>

                <div class="voucher-actions">
                  <button class="btn-edit" onclick="editVoucher(<?php echo $voucher['id']; ?>)">
                    <i class="fa-solid fa-pen-to-square"></i> Sửa
                  </button>
                  <button class="btn-delete" onclick="deleteVoucher(<?php echo $voucher['id']; ?>, '<?php echo htmlspecialchars($voucher['name']); ?>')">
                    <i class="fa-solid fa-trash"></i> Xóa
                  </button>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="empty-state">
              <i class="fa-solid fa-inbox"></i>
              <p>Chưa có voucher nào</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>





  </div>

  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      font-family: "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    .voucher-wrapper {
      display: grid;
      grid-template-columns: 450px 1fr;
      gap: 30px;
      padding: 40px;
      max-width: 1400px;
      margin: 7rem auto;

    }

    /* FORM CONTAINER */
    .voucher-form-container {
      background: #ffffff;
      padding: 35px;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      height: fit-content;
      position: sticky;
      top: 40px;
    }

    .voucher-form-container h2 {
      text-align: center;
      background: #22543d;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 25px;
      font-weight: 700;
      font-size: 1.5rem;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px 14px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 14px;
      background-color: #f7fafc;
      transition: all 0.3s ease;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #667eea;
      background-color: #fff;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-group select {
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%232d3748' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 14px center;
      padding-right: 40px;
    }

    .form-btns {
      margin-top: 25px;
    }

    .btn-submit {
      width: 100%;
      padding: 14px;
      background: #22543d;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(34, 197, 94, 0.5);
    }

    /* LIST CONTAINER */
    .voucher-list-container {
      background: #ffffff;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      max-height: calc(100vh - 120px);
      overflow-y: auto;
    }

    .list-header {
      margin-bottom: 25px;
    }

    .list-header h2 {
      background: #22543d;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      font-weight: 700;
      font-size: 1.5rem;
      margin-bottom: 15px;
    }

    .search-box {
      position: relative;
    }

    .search-box i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #a0aec0;
    }

    .search-box input {
      width: 100%;
      padding: 12px 12px 12px 45px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 14px;
      background-color: #f7fafc;
      transition: all 0.3s ease;
      box-sizing: border-box;
    }

    .search-box input:focus {
      outline: none;
      border-color: #667eea;
      background-color: #fff;
    }

    /* VOUCHER CARDS */
    .voucher-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .voucher-card {
      background: linear-gradient(135deg, #f7fafc 0%, #ffffff 100%);
      border: 2px solid #e2e8f0;
      border-radius: 15px;
      padding: 20px;
      transition: all 0.3s ease;
    }

    .voucher-card:hover {
      border-color: #276749;
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
      transform: translateY(-2px);
    }

    .voucher-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .voucher-name {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      color: #2d3748;
    }

    .voucher-name i {
      color: #667eea;
      font-size: 18px;
    }

    .voucher-status {
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }

    .status-active {
      background-color: #c6f6d5;
      color: #22543d;
    }

    .status-inactive {
      background-color: #fed7d7;
      color: #742a2a;
    }

    .voucher-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
      padding: 15px;
      background: white;
      border-radius: 10px;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .detail-label {
      font-size: 12px;
      color: #718096;
      font-weight: 500;
    }

    .detail-value {
      font-size: 15px;
      color: #2d3748;
      font-weight: 600;
    }

    .detail-value.discount {
      color: #e53e3e;
      font-size: 18px;
    }

    .voucher-actions {
      display: flex;
      gap: 10px;
    }

    .btn-edit,
    .btn-delete {
      flex: 1;
      padding: 10px;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-edit {
      background: #27A4F2;
      color: white;
    }

    .btn-edit:hover {
      background-color: #6EC2F7;
      transform: translateY(-2px);
    }

    .btn-delete {
      background-color: #e53e3e;
      color: white;
    }

    .btn-delete:hover {
      background-color: #f56565;
      transform: translateY(-2px);
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #a0aec0;
    }

    .empty-state i {
      font-size: 60px;
      margin-bottom: 15px;
    }

    .empty-state p {
      font-size: 16px;
      margin: 0;
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
      .voucher-wrapper {
        grid-template-columns: 1fr;
      }

      .voucher-form-container {
        position: static;
      }
    }

    @media (max-width: 768px) {
      .voucher-wrapper {
        padding: 20px;
        gap: 20px;
      }

      .voucher-details {
        grid-template-columns: 1fr;
      }
    }
  </style>



  <!-- POPUP CHỈNH SỬA VOUCHER -->
  <div id="editVoucherModal" class="modal_voucher" style="display:none;">
    <div class="modal-content">
      <h2>✏️ Chỉnh sửa Voucher</h2>
      <form id="editVoucherForm">
        <input type="hidden" id="edit_id" name="id">

        <div class="form-group">
          <label for="edit_name">Tên voucher:</label>
          <input type="text" id="edit_name" name="name" required>
        </div>

        <div class="form-group">
          <label for="edit_percen_decrease">Phần trăm giảm (%):</label>
          <input type="number" id="edit_percen_decrease" name="percen_decrease" min="0" max="100" required>
        </div>

        <div class="form-group">
          <label for="edit_condition">Điều kiện (VNĐ):</label>
          <input type="number" id="edit_condition" name="condition" min="0" required>
        </div>

        <div class="form-group">
          <label for="edit_status">Trạng thái:</label>
          <select id="edit_status" name="status">
            <option value="active">Hoạt động</option>
            <option value="inactive">Ngừng hoạt động</option>
          </select>
        </div>

        <div class="modal-actions">
          <button type="submit" class="btn-save">💾 Lưu thay đổi</button>
          <button type="button" class="btn-cancel" onclick="closeEditModal()">Hủy</button>
        </div>
      </form>
    </div>
  </div>

  <style>
    /* Modal nền mờ */
    .modal_voucher {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 999;
    }

    .modal-content {
      background: #fff;
      border-radius: 15px;
      padding: 25px;
      width: 400px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      animation: fadeIn 0.3s ease;
    }

    .modal-content h2 {
      text-align: center;
      color: #22543d;
      margin-bottom: 20px;
    }

    .modal-actions {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }

    .btn-save {
      background: #22543d;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-save:hover {
      background: #276749;
    }

    .btn-cancel {
      background: #a0aec0;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>


  <script>
    // Add voucher 
    document.addEventListener("DOMContentLoaded", () => {
      const addForm = document.querySelector(".voucher-form");

      if (addForm) {
        addForm.addEventListener("submit", async function(e) {
          e.preventDefault();

          const formData = new FormData(this);

          try {
            const response = await fetch("../php/addVoucher.php", {
              method: "POST",
              body: formData
            });

            const result = await response.text();
            alert(result.trim());

            // Xóa nội dung form sau khi thêm
            this.reset();

            // Reload nhẹ danh sách voucher (nếu có)
            if (typeof loadVoucherList === "function") {
              loadVoucherList(); // nếu bạn có hàm load lại danh sách
            } else {
              location.reload();
            }
          } catch (error) {
            alert("Lỗi khi thêm voucher!");
            console.error(error);
          }
        });
      }
    });

    // --- Tìm kiếm voucher ---
    const searchInput = document.getElementById('searchVoucher');
    if (searchInput) {
      searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.voucher-card');
        cards.forEach(card => {
          const name = card.querySelector('.voucher-name strong').textContent.toLowerCase();
          card.style.display = name.includes(searchTerm) ? 'block' : 'none';
        });
      });
    }

    // --- Mở popup chỉnh sửa ---
    function editVoucher(id) {
      const card = document.querySelector(`.voucher-card[data-id="${id}"]`);
      if (!card) return;

      const name = card.querySelector('.voucher-name strong').textContent.trim();
      const discount = card.querySelector('.discount').textContent.replace('%', '').trim();
      const condition = card.querySelector('.detail-value:not(.discount)').textContent.replace(/[^\d]/g, '');
      const status = card.querySelector('.voucher-status').classList.contains('status-active') ? 'active' : 'inactive';

      document.getElementById('edit_id').value = id;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_percen_decrease').value = discount;
      document.getElementById('edit_condition').value = condition;
      document.getElementById('edit_status').value = status;
      document.getElementById('editVoucherModal').style.display = 'flex';
    }

    // --- Đóng popup ---
    function closeEditModal() {
      const modal = document.getElementById('editVoucherModal');
      if (modal) modal.style.display = 'none';
    }

    // --- Gửi form AJAX ---
    const editForm = document.getElementById('editVoucherForm');
    if (editForm) {
      editForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const response = await fetch('../php/editVoucher.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.text();

        alert(result);
        location.reload();
      });
    }

    // --- Xóa voucher ---
    // --- Xóa voucher ---
    async function deleteVoucher(id, name) {
      if (confirm(`Bạn có chắc chắn muốn xóa voucher "${name}" không?`)) {
        try {
          const formData = new FormData();
          formData.append('id', id);

          const response = await fetch('../php/deleteVoucher.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.text();
          alert(result.trim());
          location.reload(); // reload lại trang để cập nhật danh sách
        } catch (error) {
          alert('Lỗi khi xóa voucher!');
          console.error(error);
        }
      }
    }
  </script>

</body>