<?php

include '../php/connect.php';
require_once '../php/SupplierManager.php';
// include '../php/check_session.php';

$connectDb = new DatabaseConnection();
$connectDb->connect();
$myconn = $connectDb->getConnection();

// Khởi tạo SupplierManager
$supplierManager = new SupplierManager($myconn);

// Xử lý thêm/sửa nhà cung cấp
// === XỬ LÝ POST (thay toàn bộ khối if POST) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $supplierData = [
        'supplier_name'  => $_POST['supplier_name'] ?? '',
        'phone'          => $_POST['phone'] ?? '',
        'email'          => $_POST['email'] ?? null,
        'address_detail' => $_POST['address_detail'] ?? null,
        'ward_id'        => $_POST['ward_id'] ?? 0,
    ];

    if ($_POST['action'] === 'add') {
        $result = $supplierManager->create($supplierData);
    } elseif ($_POST['action'] === 'edit') {
        $supplier_id = $_POST['supplier_id'] ?? 0;
        $result = $supplierManager->update($supplier_id, $supplierData);
    }

    // TRẢ JSON (không redirect)
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'] ?? ($result['success'] ? 'Thành công!' : 'Lỗi!')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy thông tin nhà cung cấp nếu đang edit
$editSupplier = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $editSupplier = $supplierManager->getById($id);
}

// Lấy danh sách nhà cung cấp
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$suppliers = $supplierManager->getAll($searchTerm);

// Lấy thống kê
$totalSuppliers = $supplierManager->count();
$totalAmount = $supplierManager->getTotalValue();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà cung cấp</title>

    <link rel="stylesheet" href="../style/header.css">
    <link rel="stylesheet" href="../style/sidebar.css">
    <link href="../icon/css/all.css" rel="stylesheet">
    <link href="../style/generall.css" rel="stylesheet">
    <link href="../style/main2.css" rel="stylesheet">
    <link href="../style/LogInfo.css" rel="stylesheet">
    <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/responsiveHomePage.css">

    <style>
        .supplier-container {
            padding: 20px;
            margin-left: 75px;
            margin-top: 80px;
        }

        .supplier-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 500px;
        }

        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #6aa173;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #5a8f63;
        }

        .supplier-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .supplier-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .supplier-table th {
            background-color: #6aa173;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .supplier-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .supplier-table tr:hover {
            background-color: #f9f9f9;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-edit,
        .btn-view {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-edit {
            background-color: #ffc107;
            color: white;
        }

        .btn-edit:hover {
            background-color: #e0a800;
        }

        .btn-view {
            background-color: #17a2b8;
            color: white;
        }

        .btn-view:hover {
            background-color: #138496;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #6aa173;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            color: #6aa173;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .supplier-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #6aa173;
            font-size: 32px;
            margin: 0 0 10px 0;
        }

        .stat-card p {
            color: #666;
            margin: 0;
        }

        .product-list {
            margin-top: 15px;
        }

        .product-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 768px) {
            .supplier-container {
                padding: 15px;
            }

            .supplier-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            .supplier-table {
                overflow-x: auto;
            }

            .supplier-table table {
                min-width: 800px;
            }
        }
    </style>

    <style>
        .btn-info {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-info:hover {
            background-color: #218838;
        }

        .info-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-item label {
            font-weight: 600;
            color: #6aa173;
            display: block;
            margin-bottom: 5px;
        }

        .info-item p {
            margin: 0;
            color: #333;
        }

        .info-full-width {
            grid-column: 1 / -1;
        }

        /* ================== RESPONSIVE TABLET & MOBILE ================== */
        @media (max-width: 1024px) {

            /* Ẩn sidebar cố định, chỉ giữ offcanvas */
            .side-bar {
                display: none !important;
            }

            /* Nội dung chính không còn bị đẩy bởi sidebar */
            .supplier-container {
                margin-left: 0 !important;
                margin-top: 70px;
                padding: 15px;
            }

            /* Header co lại trên tablet */
            .header-left-title {
                font-size: 1.4rem;
            }

            .header-right-section {
                gap: 10px;
            }

            .bell-notification i {
                font-size: 32px;
            }

            /* Thống kê 2 cột thay vì 1 hàng dài */
            .supplier-stats {
                grid-template-columns: 1fr 1fr;
            }

            /* Header tìm kiếm + nút thêm */
            .supplier-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            .search-box input {
                font-size: 14px;
                padding: 10px;
            }

            .btn-primary {
                padding: 10px 15px;
                font-size: 14px;
            }

            /* Table responsive - chuyển thành dạng card trên tablet nhỏ */
            .supplier-table {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .supplier-table table {
                min-width: 700px;
                /* giảm từ 800px xuống */
            }

            /* Ẩn cột ít quan trọng trên tablet nhỏ */
            .idncc,
            .idncc-2,
            .sdtncc,
            .sdtncc-2 {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .supplier-stats {
                grid-template-columns: 1fr;
            }

            .supplier-header {
                gap: 10px;
            }

            /* Table dạng card thay vì bảng truyền thống */
            .supplier-table table {
                min-width: unset;
                width: 100%;
                border: 0;
            }

            .supplier-table thead {
                display: none;
            }

            .supplier-table tr {
                display: block;
                background: white;
                margin-bottom: 15px;
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                padding: 15px;
                border: 1px solid #eee;
            }

            .supplier-table td {
                display: block;
                text-align: right;
                padding: 8px 0;
                border-bottom: none;
                position: relative;
                padding-left: 50%;
            }

            .supplier-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                font-weight: 600;
                color: #6aa173;
                text-align: left;
            }

            /* Gán nhãn cho từng cột */
            .supplier-table td:nth-child(1)::before {
                content: "ID:";
            }

            .supplier-table td:nth-child(2)::before {
                content: "Tên NCC:";
            }

            .supplier-table td:nth-child(3)::before {
                content: "Điện thoại:";
            }

            .supplier-table td:nth-child(4) {
                padding-left: 15px;
                text-align: center;
            }

            .action-buttons {
                justify-content: center;
                margin-top: 10px;
            }

            .action-buttons button {
                font-size: 12px;
                padding: 6px 10px;
            }

            /* Modal nhỏ hơn */
            .modal-content {
                width: 95%;
                padding: 20px;
            }

            .info-detail {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            /* .header {
                flex-wrap: wrap;
                padding: 10px;
            } */

            .header-middle-section img {
                height: 40px;
            }

            .header-left-title {
                font-size: 1.2rem;
            }

            .stat-card h3 {
                font-size: 24px;
            }

            .btn-primary {
                width: 100%;
                justify-content: center;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons button {
                width: 100%;
                margin-bottom: 5px;
            }
        }

        /* Fix cho iPad Pro 1024px vẫn bị đẩy */
        @media (max-width: 1024px) and (orientation: portrait) {
            .supplier-container {
                margin-left: 0 !important;
            }
        }
    </style>

    <style>
        /* Modal Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        /* Modal Content */
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        /* Modal Header */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #6aa173;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            color: #6aa173;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            line-height: 1;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #333;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6aa173;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group select {
            cursor: pointer;
            background-color: white;
        }

        /* Required indicator */
        .form-group label span {
            color: red;
            margin-left: 3px;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        /* Buttons */
        .btn-primary {
            background-color: #6aa173;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #5a8f63;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            font-weight: 500;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        /* Scrollbar styling cho modal */
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: #6aa173;
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: #5a8f63;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                padding: 20px;
                max-height: 85vh;
            }

            .modal-header h2 {
                font-size: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-primary,
            .btn-cancel {
                width: 100%;
                justify-content: center;
            }
        }

        @media (min-width: 426px) and (max-width: 768px) {
            .supplier-table td {
                margin-left: 70px;
            }

            thead .sdtncc,
            .sdtncc-2,
            .idncc,
            .idncc-2 {
                display: none;
            }
        }

        /* Animation khi mở modal */
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-overlay.active .modal-content {
            animation: modalFadeIn 0.3s ease-out;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
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
                            <button class="button-function-selection">
                                <i class="fa-solid fa-users" style="font-size: 20px; color: #FAD4AE;"></i>
                            </button>
                            <p>Người dùng</p>
                        </div>
                    </a>
                    <a href="orderPage.php" style="text-decoration: none; color: black;">
                        <div class="container-function-selection">
                            <button class="button-function-selection">
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
                            <button class="button-function-selection">
                                <i class="fa-solid fa-circle-user" style="font-size: 20px; color: #FAD4AE;"></i>
                            </button>
                            <p>Tài khoản</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="header-left-section">
            <p class="header-left-title">Nhà cung cấp</p>
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

    <!-- SIDEBAR -->
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
                <button class="button-function-selection">
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
                <button class="button-function-selection">
                    <i class="fa-solid fa-circle-user" style="font-size: 20px; color: #FAD4AE;"></i>
                </button>
                <p>Tài khoản</p>
            </div>
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="supplier-container">


        <!-- Statistics -->
        <div class="supplier-stats">
            <?php
            $totalSql = "SELECT COUNT(*) as Total FROM suppliers";
            $totalResult = $connectDb->query($totalSql);
            $totalSuppliers = $totalResult->fetch_assoc()['Total'];

            $amountSql = "SELECT SUM(ird.quantity * ird.import_price) AS TotalAmount
              FROM import_receipt_detail ird";

            $amountResult = $connectDb->query($amountSql);
            $totalAmount = $amountResult->fetch_assoc()['TotalAmount'] ?? 0;

            ?>
            <div class="stat-card">
                <h3><?php echo $totalSuppliers; ?></h3>
                <p>Nhà cung cấp</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($totalAmount, 0, ',', '.'); ?></h3>
                <p>Tổng giá trị nhập hàng (VND)</p>
            </div>
        </div>

        <!-- Header with search and add button -->
        <div class="supplier-header">
            <form class="search-box" method="GET">
                <input type="text" name="search" placeholder="Tìm kiếm nhà cung cấp..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-search"></i> Tìm kiếm
                </button>
            </form>
            <button class="btn-primary" onclick="openModal('add')">
                <i class="fa-solid fa-plus"></i> Thêm nhà cung cấp
            </button>
        </div>

        <!-- Supplier Table -->
        <div class="supplier-table">
            <table>
                <thead>
                    <tr>
                        <th class="idncc">ID</th>
                        <th>Tên nhà cung cấp</th>
                        <th class="sdtncc">Số điện thoại</th>
                        <!-- <th>Email</th> -->
                        <!-- <th>Địa chỉ</th> -->
                        <!-- <th>Số sản phẩm</th> -->
                        <!-- <th>Tổng giá trị</th> -->
                        <th class="d-flex justify-content-center align-items-center">Thao tác</th>

                    </tr>
                </thead>
                <tbody>
                    <?php if (count($suppliers) > 0): ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td class="idncc-2" data-label="ID"><?php echo $supplier->getSupplierId(); ?></td>
                                <td data-label="Tên nhà cung cấp"><strong><?php echo htmlspecialchars($supplier->getSupplierName()); ?></strong></td>
                                <td class="sdtncc-2" data-label="SĐT"><?php echo htmlspecialchars($supplier->getPhone()); ?></td>
                                <td class="d-flex flex-row justify-content-center align-items-center" style="padding-left:15px;">
                                    <div class="action-buttons">
                                        <button class="btn-info" onclick='viewSupplierInfo(<?php echo json_encode($supplier->toArray()); ?>)'>
                                            <i class="fa-solid fa-info-circle"></i> Thông tin
                                        </button>
                                        <button class="btn-edit" onclick='openModal("edit", <?php echo json_encode($supplier->toArray()); ?>)'>
                                            <i class="fa-solid fa-edit"></i> Sửa
                                        </button>
                                        <button class="btn-view" onclick="viewProducts(<?php echo $supplier->getSupplierId(); ?>)">
                                            <i class="fa-solid fa-eye"></i> Xem SP
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                Không tìm thấy nhà cung cấp nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>



    <!-- Modal Add/Edit Supplier -->
    <div id="supplierModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Thêm nhà cung cấp mới</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form id="supplierForm" method="POST" onsubmit="submitSupplier(event)">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="supplier_id" id="supplier_id">

                <div class="form-group">
                    <label>Tên nhà cung cấp <span style="color: red;">*</span></label>
                    <input type="text" name="supplier_name" id="supplier_name">
                </div>

                <!-- <div class="form-group">
                    <label>Người liên hệ <span style="color: red;">*</span></label>
                    <input type="text" name="contact_person" id="contactPerson" required>
                </div> -->

                <div class="form-group">
                    <label>Số điện thoại <span style="color: red;">*</span></label>
                    <input type="tel" name="phone" id="phone" length="10">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email">
                </div>

                <div class="form-group">
                    <label>Địa chỉ chi tiết</label>
                    <input type="text" name="address_detail" id="address_detail"></input>
                </div>



                <div class="form-group">
                    <label>Tỉnh/Thành phố</label>
                    <select name="province_id" id="province_id">
                        <option value="">Chọn tỉnh/thành phố</option>
                        <?php
                        $provinces = $myconn->query("SELECT province_id, name FROM province ORDER BY name");
                        while ($p = $provinces->fetch_assoc()) {
                            echo "<option value='{$p['province_id']}'>{$p['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Quận/Huyện</label>
                    <select name="district_id" id="district_id">
                        <option value="">Chọn quận/huyện</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Phường/Xã</label>
                    <select name="ward_id" id="ward_id">
                        <option value="">Chọn phường/xã</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-save"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal View Products -->
    <div id="productsModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Danh sách sản phẩm</h2>
                <button class="close-modal" onclick="closeProductsModal()">&times;</button>
            </div>
            <div id="productsList" class="product-list">
                <!-- Products will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <div id="supplierInfoModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fa-solid fa-info-circle"></i> Thông tin nhà cung cấp</h2>
                <button class="close-modal" onclick="closeSupplierInfoModal()">&times;</button>
            </div>
            <div class="info-detail">
                <div class="info-item">
                    <label><i class="fa-solid fa-hashtag"></i> Mã nhà cung cấp</label>
                    <p id="info_supplier_id"></p>
                </div>
                <div class="info-item">
                    <label><i class="fa-solid fa-building"></i> Tên nhà cung cấp</label>
                    <p id="info_supplier_name"></p>
                </div>
                <div class="info-item">
                    <label><i class="fa-solid fa-phone"></i> Số điện thoại</label>
                    <p id="info_phone"></p>
                </div>
                <div class="info-item">
                    <label><i class="fa-solid fa-envelope"></i> Email</label>
                    <p id="info_email"></p>
                </div>
                <div class="info-item info-full-width">
                    <label><i class="fa-solid fa-location-dot"></i> Địa chỉ đầy đủ</label>
                    <p id="info_address"></p>
                </div>
                <div class="info-item">
                    <label><i class="fa-solid fa-box"></i> Tổng số sản phẩm</label>
                    <p id="info_total_products"></p>
                </div>
                <div class="info-item">
                    <label><i class="fa-solid fa-money-bill"></i> Tổng giá trị</label>
                    <p id="info_total_amount"></p>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn-cancel" onclick="closeSupplierInfoModal()">Đóng</button>
            </div>
        </div>
    </div>

    <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/checklog.js"></script>

    <script>
        // Load user info
        document.addEventListener('DOMContentLoaded', () => {
            const cachedUserInfo = localStorage.getItem('userInfo');
            if (cachedUserInfo) {
                const userInfo = JSON.parse(cachedUserInfo);
                document.querySelector('.name-employee p').textContent = userInfo.fullname;
                document.querySelector('.position-employee p').textContent = userInfo.role;
                document.querySelectorAll('.avatar').forEach(img => img.src = userInfo.avatar);
            }
        });

        function openModal(action, data = null) {
            const modal = document.getElementById('supplierModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('supplierForm');

            // Lấy các input cần required khi add
            const requiredFields = [
                document.getElementById('supplier_name'),
                document.getElementById('phone'),
                document.getElementById('address_detail'),
                document.getElementById('province_id'),
                document.getElementById('district_id'),
                document.getElementById('ward_id')
            ];

            if (action === 'add') {
                title.textContent = 'Thêm nhà cung cấp mới';
                document.getElementById('formAction').value = 'add';
                form.reset();

                // Thêm required
                requiredFields.forEach(input => input.setAttribute('required', 'required'));
            } else if (action === 'edit' && data) {
                title.textContent = 'Chỉnh sửa nhà cung cấp';
                document.getElementById('formAction').value = 'edit';
                document.getElementById('supplier_id').value = data.supplier_id;
                document.getElementById('supplier_name').value = data.supplier_name;
                document.getElementById('phone').value = data.phone;
                document.getElementById('email').value = data.Email || '';
                document.getElementById('address_detail').value = data.address_detail || '';

                // Loại bỏ required
                requiredFields.forEach(input => input.removeAttribute('required'));

                // Load tỉnh/huyện/xã từ data
                const loadLocation = async () => {
                    const provinceSelect = document.getElementById('province_id');
                    const districtSelect = document.getElementById('district_id');
                    const wardSelect = document.getElementById('ward_id');

                    // --- 1. LOAD PROVINCES AND SET VALUE ---
                    try {
                        // Set province value nếu có
                        if (data.province_id) {
                            provinceSelect.value = data.province_id;
                        }

                        // --- 2. LOAD DISTRICTS ---
                        if (data.province_id) {
                            const districtResponse = await fetch(`../php/get_districts.php?province_id=${data.province_id}`);
                            const districts = await districtResponse.json();

                            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
                            districts.forEach(d => {
                                const opt = document.createElement('option');
                                opt.value = d.district_id;
                                opt.textContent = d.name;
                                districtSelect.appendChild(opt);
                            });

                            // Set district value AFTER loading options
                            if (data.district_id) {
                                districtSelect.value = data.district_id;
                            }
                        }

                        // --- 3. LOAD WARDS ---
                        if (data.district_id) {
                            const wardResponse = await fetch(`../php/get_wards.php?district_id=${data.district_id}`);
                            const wards = await wardResponse.json();

                            wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
                            wards.forEach(w => {
                                const opt = document.createElement('option');
                                opt.value = w.ward_id;
                                opt.textContent = w.name;
                                wardSelect.appendChild(opt);
                            });

                            // Set ward value AFTER loading options
                            if (data.ward_id) {
                                wardSelect.value = data.ward_id;
                            }
                        }
                    } catch (e) {
                        console.error('Error loading locations:', e);
                    }
                };

                // Gọi hàm load
                loadLocation();
            }

            modal.classList.add('active');
        }


        function closeModal() {
            document.getElementById('supplierModal').classList.remove('active');
        }





        function viewProducts(supplier_id) {
            const modal = document.getElementById('productsModal');
            const list = document.getElementById('productsList');

            list.innerHTML = '<p style="text-align: center; padding: 20px;">Đang tải...</p>';

            // Fetch products from server
            fetch(`../php/get_supplier_products.php?supplier_id=${supplier_id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.products && data.products.length > 0) {

                        let html = `
                    <div style="margin-bottom: 15px;">
                        <strong>Tổng giá trị: 
                            ${new Intl.NumberFormat('vi-VN').format(data.totalAmount)} VND
                        </strong>
                    </div>
                `;

                        data.products.forEach(product => {

                            html += `
                        <div style="margin-bottom: 10px;">
                            <strong>${product.ProductName}</strong>
                    `;

                            product.details.forEach(d => {
                                html += `
                            <div style="padding-left: 15px; margin-top: 5px;">
                                - Số lượng: ${d.Quantity} | 
                                  Đơn giá: ${new Intl.NumberFormat('vi-VN').format(d.UnitPrice)} | 
                                  Thành tiền: ${new Intl.NumberFormat('vi-VN').format(d.TotalValue)}
                            </div>
                        `;
                            });

                            html += `
                        <div style="padding-left: 15px; margin-top: 5px; font-weight: bold;">
                            → Tổng: ${new Intl.NumberFormat('vi-VN').format(product.totalProductValue)} VND
                        </div>
                        <hr>
                    </div>
                    `;
                        });

                        list.innerHTML = html;

                    } else {
                        list.innerHTML = '<p style="text-align: center; padding: 20px;">Chưa có sản phẩm nào</p>';
                    }
                })
                .catch(error => {
                    list.innerHTML = '<p style="text-align: center; padding: 20px; color: red;">Lỗi khi tải dữ liệu</p>';
                });

            modal.classList.add('active');
        }





        function closeProductsModal() {
            document.getElementById('productsModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const supplierModal = document.getElementById('supplierModal');
            const productsModal = document.getElementById('productsModal');

            if (event.target === supplierModal) {
                closeModal();
            }
            if (event.target === productsModal) {
                closeProductsModal();
            }
        }

        // Auto hide success message
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    </script>

    <!-- //Load quận and phường dựa trên tỉnh đã chọn -->
    <script>
        document.getElementById('province_id').addEventListener('change', function() {
            const provinceId = this.value;
            const districtSelect = document.getElementById('district_id');
            districtSelect.innerHTML = '<option value="">Đang tải...</option>';
            document.getElementById('ward_id').innerHTML = '<option value="">Chọn phường/xã</option>';

            if (provinceId) {
                fetch(`../php/get_districts.php?province_id=${provinceId}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">Chọn quận/huyện</option>';
                        data.forEach(d => {
                            html += `<option value="${d.district_id}">${d.name}</option>`;
                        });
                        districtSelect.innerHTML = html;
                    });
            } else {
                districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            }
        });

        document.getElementById('district_id').addEventListener('change', function() {
            const districtId = this.value;
            const wardSelect = document.getElementById('ward_id');
            wardSelect.innerHTML = '<option value="">Đang tải...</option>';

            if (districtId) {
                fetch(`../php/get_wards.php?district_id=${districtId}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">Chọn phường/xã</option>';
                        data.forEach(w => {
                            html += `<option value="${w.ward_id}">${w.name}</option>`;
                        });
                        wardSelect.innerHTML = html;
                    });
            } else {
                wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
            }
        });
    </script>


    <!-- Nếu người dùng chọn tỉnh/ thành phố, thì bắt buộc phải chọn quận/ huyện và phường/ xã. Nếu chưa chọn tỉnh/ thành phố thì không bắt buộc chọn quận/ huyện và phường/ xã. -->
    <script>
        const provinceSelect = document.getElementById('province_id');
        const districtSelect = document.getElementById('district_id');
        const wardSelect = document.getElementById('ward_id');
        const supplierForm = document.getElementById('supplierForm');

        provinceSelect.addEventListener('change', function() {
            const provinceId = this.value;

            if (provinceId) {
                // Khi chọn tỉnh, bắt buộc chọn huyện và xã
                districtSelect.setAttribute('required', 'required');
                wardSelect.setAttribute('required', 'required');
            } else {
                // Nếu chưa chọn tỉnh, remove required
                districtSelect.removeAttribute('required');
                wardSelect.removeAttribute('required');
                districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
                wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
            }

            // Load districts
            if (provinceId) {
                districtSelect.innerHTML = '<option value="">Đang tải...</option>';
                wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
                fetch(`../php/get_districts.php?province_id=${provinceId}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">Chọn quận/huyện</option>';
                        data.forEach(d => {
                            html += `<option value="${d.district_id}">${d.name}</option>`;
                        });
                        districtSelect.innerHTML = html;
                    });
            }
        });

        districtSelect.addEventListener('change', function() {
            const districtId = this.value;

            if (districtId) {
                wardSelect.setAttribute('required', 'required');
            } else {
                wardSelect.removeAttribute('required');
                wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
            }

            // Load wards
            if (districtId) {
                wardSelect.innerHTML = '<option value="">Đang tải...</option>';
                fetch(`../php/get_wards.php?district_id=${districtId}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">Chọn phường/xã</option>';
                        data.forEach(w => {
                            html += `<option value="${w.ward_id}">${w.name}</option>`;
                        });
                        wardSelect.innerHTML = html;
                    });
            }
        });

        // Validate form trước submit
        supplierForm.addEventListener('submit', function(e) {
            if (provinceSelect.value && (!districtSelect.value || !wardSelect.value)) {
                alert("Vui lòng chọn đầy đủ quận/huyện và phường/xã!");
                e.preventDefault();
            }
        });
    </script>

    <!-- THÊM JAVASCRIPT (đặt trước thẻ đóng </body>) -->
    <script>
        function viewSupplierInfo(data) {
            console.log(data);
            document.getElementById('info_supplier_id').textContent = data.supplier_id;
            document.getElementById('info_supplier_name').textContent = data.supplier_name;
            document.getElementById('info_phone').textContent = data.phone || 'Chưa cập nhật';
            document.getElementById('info_email').textContent = data.Email || 'Chưa cập nhật';

            const fullAddress = `${data.address_detail}, ${data.ward_name}, ${data.district_name}, ${data.province_name}`;
            document.getElementById('info_address').textContent = fullAddress;

            document.getElementById('info_total_products').textContent = data.TotalProducts + ' sản phẩm';
            document.getElementById('info_total_amount').textContent = new Intl.NumberFormat('vi-VN').format(data.TotalAmount) + ' VND';

            document.getElementById('supplierInfoModal').classList.add('active');
        }

        function closeSupplierInfoModal() {
            document.getElementById('supplierInfoModal').classList.remove('active');
        }

        // Thêm vào phần window.onclick hiện có
        window.onclick = function(event) {
            const supplierModal = document.getElementById('supplierModal');
            const productsModal = document.getElementById('productsModal');
            const infoModal = document.getElementById('supplierInfoModal');

            if (event.target === supplierModal) {
                closeModal();
            }
            if (event.target === productsModal) {
                closeProductsModal();
            }
            if (event.target === infoModal) {
                closeSupplierInfoModal();
            }
        }
    </script>

    <script>
        async function submitSupplier(e) {
            e.preventDefault();

            const form = document.getElementById('supplierForm');
            const formData = new FormData(form);

            try {
                const res = await fetch('supplierManage.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                // HIỆN ALERT VỚI LỖI NHIỀU DÒNG
                alert(data.message);

                if (data.success) {
                    closeModal();
                    location.reload(); // reload bảng
                }
            } catch (err) {
                alert('Lỗi kết nối! Vui lòng thử lại.');
                console.error(err);
            }
        }
    </script>
</body>

</html>