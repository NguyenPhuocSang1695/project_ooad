<?php
// include '../php/check_session.php';
session_name('admin_session');

$pagination = [
  'currentPage' => 1,
  'totalPages' => 1
];
$orders = [];
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

    /* Modern Notification Styles */
    .notification {
      position: fixed;
      top: 50%;
      left: 50%;
      min-width: 360px;
      max-width: 450px;
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 50px rgba(65, 64, 53, 0.2);
      padding: 20px;
      display: flex;
      align-items: flex-start;
      gap: 16px;
      opacity: 0;
      transform: translateX(120%);
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      z-index: 9999;
      border-left: 5px solid;
    }

    .notification.show {
      opacity: 1;
      transform: translateX(0);
    }

    .notification.success {
      border-left-color: #10b981;
    }

    .notification.error {
      border-left-color: #ef4444;
    }

    .notification.warning {
      border-left-color: #f59e0b;
    }

    .notification.info {
      border-left-color: #3b82f6;
    }

    .notification-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      flex-shrink: 0;
      position: relative;
      overflow: hidden;
      font-weight: bold;
    }

    .notification-icon::before {
      content: '';
      position: absolute;
      inset: 0;
      background: currentColor;
      opacity: 0.1;
      border-radius: 12px;
    }

    .notification.success .notification-icon {
      color: #10b981;
    }

    .notification.error .notification-icon {
      color: #ef4444;
    }

    .notification.warning .notification-icon {
      color: #f59e0b;
    }

    .notification.info .notification-icon {
      color: #3b82f6;
    }

    .notification-content {
      flex: 1;
      padding-top: 2px;
    }

    .notification-title {
      font-size: 16px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 6px;
      line-height: 1.4;
    }

    .notification-message {
      font-size: 14px;
      color: #64748b;
      line-height: 1.6;
      white-space: pre-line;
    }

    .notification-close {
      width: 32px;
      height: 32px;
      border: none;
      background: #f1f5f9;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #64748b;
      font-size: 20px;
      transition: all 0.2s ease;
      flex-shrink: 0;
      line-height: 1;
    }

    .notification-close:hover {
      background: #e2e8f0;
      color: #1e293b;
    }

    .notification-progress {
      position: absolute;
      bottom: 0;
      left: 0;
      height: 4px;
      background: currentColor;
      opacity: 0.3;
      border-radius: 0 0 0 16px;
      animation: progress 5s linear forwards;
    }

    @keyframes progress {
      from { width: 100%; }
      to { width: 0%; }
    }

    /* Enhanced Success Notification */
    .enhanced-notification {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) scale(0.7);
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      z-index: 10000;
      width: 420px;
      max-width: 90vw;
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      overflow: hidden;
    }

    .enhanced-notification.show {
      opacity: 1;
      transform: translate(-50%, -50%) scale(1);
    }

    .enhanced-notification::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .notification-header {
      padding: 30px 30px 20px;
      text-align: center;
    }

    .success-icon-wrapper {
      display: inline-block;
      margin-bottom: 15px;
    }

    .success-checkmark {
      width: 80px;
      height: 80px;
      display: block;
      stroke-width: 3;
      stroke-miterlimit: 10;
    }

    .checkmark-check {
      transform-origin: 50% 50%;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      stroke: #10b981;
      stroke-width: 4;
      stroke-linecap: round;
      stroke-linejoin: round;
      animation: strokeCheck 0.6s ease-out forwards;
    }

    @keyframes strokeCheck {
      0% {
        stroke-dashoffset: 48;
        opacity: 0;
        transform: scale(0.5);
      }
      50% {
        opacity: 1;
      }
      100% {
        stroke-dashoffset: 0;
        opacity: 1;
        transform: scale(1);
      }
    }

    .notification-body {
      padding: 0 30px 20px;
      background: #f8f9fa;
    }

    .order-detail-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #e9ecef;
    }

    .order-detail-item:last-child {
      border-bottom: none;
    }

    .detail-label {
      font-size: 14px;
      color: #6c757d;
      font-weight: 500;
    }

    .detail-value {
      font-size: 15px;
      color: #2c3e50;
      font-weight: 600;
    }

    .detail-value.highlight {
      color: #10b981;
      font-size: 18px;
    }

    .notification-footer {
      padding: 20px 30px;
      text-align: center;
      background: white;
    }

    .success-message {
      font-size: 14px;
      color: #10b981;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    /* Responsive Notifications */
    @media (max-width: 640px) {
      .notification {
        top: 12px;
        right: 12px;
        left: 12px;
        min-width: auto;
        max-width: none;
      }

      .enhanced-notification {
        width: 350px;
      }

      .notification-title {
        font-size: 20px;
      }

      .success-checkmark {
        width: 60px;
        height: 60px;
      }

      .notification-body {
        padding: 0 20px 15px;
      }

      .notification-header {
        padding: 20px 20px 15px;
      }

      .notification-footer {
        padding: 15px 20px;
      }
    }

    /* Filter and Add Order Section */
    .filter-section {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    #add-order-button {
      background-color: #28a745;
      border-color: #28a745;
    }

    #add-order-button:hover {
      background-color: #218838;
      border-color: #1e7e34;
    }

    /* Add Order Form Styles */
    .product-item {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 10px;
      margin-bottom: 10px;
    }

    .remove-product {
      padding: 5px 8px;
    }

    .product-price {
      background-color: #f8f9fa;
    }

    #total-amount {
      font-weight: bold;
      color: #28a745;
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

    /* Order Detail Modal Styles */
    #orderDetailModal .modal-dialog {
      max-width: 700px;
    }

    #orderDetailModal .modal-content {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    #orderDetailModal .modal-header {
      background: #6aa173;
      color: white;
      border-radius: 12px 12px 0 0;
      border: none;
      padding: 20px;
    }

    #orderDetailModal .modal-header .modal-title {
      font-weight: 700;
      font-size: 18px;
    }

    #orderDetailModal .btn-close {
      filter: brightness(0) invert(1);
    }

    #orderDetailModal .modal-body {
      padding: 0;
      background: white;
    }

    #orderDetailModal .modal-footer {
      border-top: 1px solid #eee;
      padding: 15px 20px;
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

    /* Search Input Styling */
    #search-input {
      font-size: 14px;
      padding: 10px 12px 10px 35px !important;
      border-radius: 6px !important;
      border: 2px solid #e0e0e0 !important;
      transition: all 0.3s ease !important;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    #search-input:focus {
      border-color: #6aa173 !important;
      box-shadow: 0 4px 12px rgba(106, 161, 115, 0.15) !important;
      outline: none;
      background-color: #fafafa;
    }

    #search-input::placeholder {
      color: #999;
      font-style: italic;
    }

    .filter-section {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .filter-section button {
      border-radius: 6px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .filter-section button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
              <i class="fa-solid fa-chart-simple" style="font-size: 20px; color: #FAD4AE;"></i>
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
            <i class="fa-solid fa-chart-simple" style="font-size: 20px; color: #FAD4AE;"></i>
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
        <div class="filter-section" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <div style="width: 20rem; position: relative;">
            <input
              type="text"
              class="form-control"
              id="search-input"
              placeholder="Tìm kiếm mã đơn hàng hoặc tên khách hàng..."
              style="padding-left: 35px; border-radius: 6px; border: 2px solid #e0e0e0; transition: all 0.3s ease;">
            <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999;"></i>
          </div>
          <button type="button" class="btn btn-primary" id="filter-button" data-bs-toggle="modal" data-bs-target="#filterModal" style="border-radius: 6px;">
            <i class="fas fa-filter"></i> Bộ lọc
          </button>
          <button type="button" class="btn btn-success" id="add-order-button" data-bs-toggle="modal" data-bs-target="#addOrderModal" style="border-radius: 6px;">
            <i class="fas fa-plus"></i> Thêm đơn hàng
          </button>
        </div>

        <!-- Modal thêm đơn hàng mới -->
        <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="addOrderModalLabel">Thêm đơn hàng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="add-order-form">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="customer-phone" class="form-label">Số điện thoại:</label>
                      <input type="tel" class="form-control" id="customer-phone" name="customer_phone" pattern="[0-9]*" maxlength="10">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="customer-name" class="form-label">Tên khách hàng:</label>
                      <input type="text" class="form-control" id="customer-name" name="customer_name">
                    </div>
                  </div>

                  <div class="mb-3">
                    <div id="customer-history" style="display: none; padding: 10px; background-color: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 4px;">
                      <h6 style="margin: 0; color: #1976D2;">Lịch sử mua hàng</h6>
                      <p id="history-message" style="margin: 5px 0; color: #555; font-size: 14px;"></p>
                      <small id="history-details" style="color: #999;"></small>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Hình thức nhận hàng:</label>
                    <div class="row">
                      <div class="col-md-6 form-check">
                        <input class="form-check-input" type="radio" name="delivery_type" id="delivery_pickup" value="pickup" checked>
                        <label class="form-check-label" for="delivery_pickup">
                          Tại cửa hàng
                        </label>
                      </div>
                      <div class="col-md-6 form-check">
                        <input class="form-check-input" type="radio" name="delivery_type" id="delivery_address" value="address">
                        <label class="form-check-label" for="delivery_address">
                          Giao hàng 
                        </label>
                      </div>
                    </div>
                  </div>

                  <div id="address-section" style="display: none;" class="mb-3">
                    <label class="form-label">Địa chỉ giao hàng:</label>
                    <div class="row">
                      <div class="col-md-4 mb-2">
                        <select id="add-province" name="province" class="form-control">
                          <option value="">Chọn tỉnh/thành</option>
                        </select>
                      </div>
                      <div class="col-md-4 mb-2">
                        <select id="add-district" name="district" class="form-control">
                          <option value="">Chọn quận/huyện</option>
                        </select>
                      </div>
                      <div class="col-md-4 mb-2">
                        <select id="add-ward" name="ward" class="form-control">
                          <option value="">Chọn phường/xã</option>
                        </select>
                      </div>
                    </div>
                    <div class="mt-2">
                      <input type="text" class="form-control" id="address-detail" name="address_detail"
                        placeholder="Số nhà, tên đường...">
                    </div>
                  </div>

                  <div class="mb-3">
                    <div class="col-md-6 mb-3">
                      <label for="payment-method" class="form-label">Phương thức thanh toán:</label>
                      <select class="form-control" id="payment-method" name="payment_method" required>
    
                        <option value="CASH">Thanh toán tại quầy</option>
                        <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                        <option value="BANKING">Chuyển khoản ngân hàng</option>
                      </select>
                    </div>
                  </div>

                  <!-- Banking Payment -->
                  <div id="banking-info-section" style="display: none; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                    <h6 style="margin-bottom: 15px; font-weight: bold;">Thông tin chuyển khoản:</h6>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
                      <div>
                        <p style="margin: 8px 0; padding: 8px;  border-radius: 5px;">
                          <strong>Số tài khoản:</strong> 1028974123
                        </p>
                        <p style="margin: 8px 0; padding: 8px;  border-radius: 5px;">
                          <strong>Tên tài khoản:</strong> Nguyễn Văn A
                        </p>
                        <p style="margin: 8px 0; padding: 8px;  border-radius: 5px;">
                          <strong>Ngân hàng:</strong> Vietcombank
                        </p>
                        <p style="margin: 8px 0; padding: 8px;  border-radius: 5px;">
                          <strong>Chi nhánh:</strong> Bắc Bình Dương
                        </p>
                        <p style="margin: 8px 0; padding: 8px;  border-radius: 5px;">
                          <strong>Nội dung chuyển khoản:</strong> Mua hàng
                        </p>
             
                      </div>
                      <div style="display: flex; justify-content: center; align-items: center;">
                        <div id="qr-container" style="text-align: center;">
                          <img id="admin-qr-code" src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=Vietcombank|1028974123|0|Mua%20hang" alt="QR Code" style="max-width: 280px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="voucher-select" class="form-label">Mã giảm giá (Voucher):</label>
                    <select class="form-control" id="voucher-select" name="voucher_id">
                      <option value="">-- Không dùng voucher --</option>
                    </select>
                    <small id="voucher-message" class="form-text" style="margin-top: 5px;"></small>
                    <small id="voucher-note" class="form-text" style="margin-top: 5px; color: #ff9800; display: none;"><i class="fas fa-info-circle"></i>  Chương trình voucher hiện chưa áp dụng cho lần mua đầu tiên.</small>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Tổng tiền gốc:</label>
                      <input type="text" class="form-control" id="original-total" readonly style="background-color: #f8f9fa;">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Số tiền giảm:</label>
                      <input type="text" class="form-control" id="discount-amount" readonly style="background-color: #fff3cd; color: #856404;">
                    </div>
                  </div>

                  <div class="products-section mb-3">
                    <h6 class="mb-3">Sản phẩm</h6>
                    <div id="product-list">
                      <div class="product-item row mb-2">
                        <div class="col-md-5">
                          <div style="position: relative;">
                            <input type="text" class="form-control product-search" placeholder="🔍 Tìm kiếm sản phẩm..." style="margin-bottom: 5px;">
                            <select class="form-control product-select" name="products[]" required style="display: none;">
                              <option value="">Chọn sản phẩm</option>
                            </select>
                            <div class="product-options" style="border: 1px solid #ced4da; border-radius: 4px; max-height: 200px; overflow-y: auto; display: none; position: absolute; width: 100%; background: white; z-index: 1000; top: 38px;">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <input type="number" class="form-control product-quantity" name="quantities[]"
                            placeholder="Số lượng" min="1" required>
                        </div>
                        <div class="col-md-3">
                          <input type="text" class="form-control product-price" readonly>
                        </div>
                        <div class="col-md-1">
                          <button type="button" class="btn btn-danger btn-sm remove-product">
                            <i class="fas fa-times"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2" id="add-product">
                      <i class="fas fa-plus"></i> Thêm sản phẩm
                    </button>
                  </div>

                  <div class="mb-3">
                    <h6>Tổng tiền: <span id="total-amount">0</span> VNĐ</h6>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" form="add-order-form" class="btn btn-primary">Thêm đơn hàng</button>
              </div>
            </div>
          </div>
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
                    <label for="price-min" class="form-label">Giá tiền tối thiểu:</label>
                    <input type="number" id="price-min" name="price_min" class="form-control" placeholder="Ví dụ: 100000" min="0" step="1000">
                  </div>
                  <div class="mb-3">
                    <label for="price-max" class="form-label">Giá tiền tối đa:</label>
                    <input type="number" id="price-max" name="price_max" class="form-control" placeholder="Ví dụ: 5000000" min="0" step="1000">
                  </div>
                  <div class="mb-3">
                    <label for="voucher-filter" class="form-label">Lọc theo voucher:</label>
                    <select id="voucher-filter" name="voucher_filter" class="form-control">
                      <option value="">-- Tất cả đơn hàng --</option>
                      <option value="has_voucher">Có sử dụng voucher</option>
                      <option value="no_voucher">Không sử dụng voucher</option>
                    </select>
                  </div>
                  <div class="mb-3" id="specific-voucher-container" style="display: none;">
                    <label for="specific-voucher" class="form-label">Chọn voucher cụ thể:</label>
                    <select id="specific-voucher" name="specific_voucher" class="form-control">
                      <option value="">-- Tất cả voucher --</option>
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
            th:nth-child(5) {
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
                <th>Ngày tạo</th>
                <th>Phương thức thanh toán</th>
                <th class="hide-index-mobile">Giá tiền (VND)</th>
              </tr>
            </thead>
            <tbody id="order-table-body">
              <?php
              if (!empty($orders)) {
                foreach ($orders as $o) {
                  echo '<tr>';
                  echo '<td>#' . htmlspecialchars($o['OrderID']) . '</td>';
                  echo '<td>' . htmlspecialchars($o['DateGeneration']) . '</td>';
                  echo '<td>' . htmlspecialchars($o['PaymentMethod'] ?? 'N/A') . '</td>';
                  echo '<td class="hide-index-mobile">' . number_format($o['TotalAmount']) . '</td>';
                  echo '</tr>';
                }
              } else {
                echo '<tr><td colspan="4">Không có đơn hàng</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
        <!-- <div id="updateStatusOverlay" class="overlay" style="display: none;">
          <div class="popup">
            <h3>Cập nhật trạng thái đơn hàng</h3>
            <div id="statusOptions" class="status-options"></div>
            <button onclick="closeUpdateStatusPopup()" class="close-btn">Đóng</button>
          </div>
        </div> -->

        <!-- Modal Chi tiết đơn hàng -->
        <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="orderDetailLabel">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div id="orderDetailContent" style="max-height: 600px; overflow-y: auto;">
                  <!-- Chi tiết sẽ được load bằng JavaScript -->
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
              </div>
            </div>
          </div>
        </div>
        <div class="select_list" id="pagination-container">
          <div style="display:flex;align-items:center;gap:8px;">
            <button class="page-btn" id="prevPage">&lt;</button>
            <div id="pageNumbers"></div>
            <button class="page-btn" id="nextPage">&gt;</button>
          </div>
        </div>
      </div>
    </div>
    <script src="../js/checklog.js"></script>
    <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/orderPage.js"></script>
    <script src="../js/add-order.js"></script>
</body>

</html>