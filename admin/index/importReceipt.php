<?php
// Include các file cần thiết
include '../php/connect.php';
require_once '../php/ImportReceiptManager.php';
// include '../php/check_session.php';

// Khởi tạo kết nối database
$connectDb = new DatabaseConnection();
$connectDb->connect();
$myconn = $connectDb->getConnection();

// Khởi tạo ImportReceiptManager
$receiptManager = new ImportReceiptManager($myconn);

// ==================== XỬ LÝ REQUEST ====================

// Xử lý THÊM phiếu nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $result = $receiptManager->create(
        $_POST['import_date'],
        $_POST['total_amount'],
        $_POST['note'],
        $_POST['suppliers'],
        $_POST['products'] ?? []
    );

    if ($result['success']) {
        echo "<script>
            alert('Thêm phiếu nhập thành công!');
            window.location.href = 'importReceipt.php';
          </script>";
        exit();
    } else {
        $error_message = $result['message'];
        echo "<script>alert('$error_message'); window.history.back();</script>";
        exit();
    }
}

// Xử lý CẬP NHẬT phiếu nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $result = $receiptManager->update(
        $_POST['receipt_id'],
        $_POST['import_date'],
        $_POST['total_amount'],
        $_POST['note'],
        $_POST['suppliers'],
        $_POST['products'] ?? []
    );

    if ($result['success']) {
        echo "<script>
            alert('Cập nhật phiếu nhập thành công!');
            window.location.href = 'importReceipt.php';
          </script>";
        exit();
    } else {
        $error_message = $result['message'];
        echo "<script>alert('$error_message'); window.history.back();</script>";
        exit();
    }
}

// Xử lý XÓA phiếu nhập
if (isset($_GET['delete'])) {
    $receiptId = intval($_GET['delete']);
    $result = $receiptManager->delete($receiptId);

    if ($result['success']) {
        echo "<script>
            alert('Xóa phiếu nhập thành công!');
            window.location.href = 'importReceipt.php';
          </script>";
        exit();
    } else {
        $error_message = $result['message'];
        echo "<script>alert('$error_message'); window.history.back();</script>";
        exit();
    }
}

// ==================== LẤY DỮ LIỆU CHO VIEW ====================

// Lấy danh sách phiếu nhập
$receipts = $receiptManager->getAll('import_date', 'DESC');

// Lấy danh sách nhà cung cấp
$suppliers = $receiptManager->getSuppliers();

// Lấy danh sách sản phẩm
$products = $receiptManager->getProducts();

// Tính tổng giá trị nhập hàng
$grandTotal = $receiptManager->getTotalValue();

// Đếm tổng số phiếu nhập
$totalReceipts = $receiptManager->count();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhập hàng - Phiếu nhập kho</title>

    <link rel="stylesheet" href="../style/header.css">
    <link rel="stylesheet" href="../style/sidebar.css">
    <link href="../icon/css/all.css" rel="stylesheet">
    <link href="../style/generall.css" rel="stylesheet">
    <link href="../style/main2.css" rel="stylesheet">
    <link href="../style/LogInfo.css" rel="stylesheet">
    <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/responsiveHomePage.css">

    <style>
        .import-container {
            margin-left: 75px;
            margin-top: 100px;
            padding: 20px;
            background-color: #f5f5f5;
            min-height: calc(100vh - 100px);
        }

        .import-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .import-header h2 {
            color: #64792c;
            margin: 0;
        }

        .btn-add-receipt {
            background-color: #6aa173;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-add-receipt:hover {
            background-color: #5a8f63;
            transform: translateY(-2px);
        }

        .receipt-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .receipt-table th {
            background-color: #6aa173;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 500;
        }

        .receipt-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .receipt-table tr:hover {
            background-color: #f9f9f9;
        }

        .btn-action {
            padding: 6px 12px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-view {
            background-color: #4CAF50;
            color: white;
        }

        .btn-edit {
            background-color: #2196F3;
            color: white;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .btn-action:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #6aa173;
            padding-bottom: 10px;
        }

        .modal-header h3 {
            color: #64792c;
            margin: 0;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .product-items {
            margin-top: 20px;
        }

        .product-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 60px;
            gap: 10px;
            margin-bottom: 10px;
            align-items: end;
        }

        .btn-remove-product {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-add-product {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .btn-submit {
            background-color: #6aa173;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-cancel {
            background-color: #999;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .total-summary {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            text-align: right;
        }

        .total-summary h4 {
            color: #64792c;
            margin: 0;
            font-size: 20px;
        }

        @media (max-width: 768px) {
            .import-container {
                margin-left: 0;
                margin-top: 80px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .product-item {
                grid-template-columns: 1fr;
            }

            .receipt-table {
                font-size: 12px;
            }
        }

        @media (max-width: 320px) {
            #filterMonth {
                width: 50px;
            }
        }

        @media (min-width: 426px) and (max-width: 768px) {
            .import-container {
                margin-left: 75px;
            }
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
                    <a href="importReceipt.php" style="text-decoration: none; color: black;">
                        <div class="container-function-selection">
                            <button class="button-function-selection" style="background-color: #6aa173;">
                                <i class="fa-solid fa-file-import" style="font-size: 18px; color: #FAD4AE;"></i>
                            </button>
                            <p style="color:black">Nhập hàng</p>
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
                            <p style="color:black">Tài khoản</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="header-left-section">
            <p class="header-left-title">Quản lý nhập hàng</p>
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
                <button class="button-function-selection" style="background-color: #6aa173;">
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

    <!-- MAIN CONTENT -->
    <div class="import-container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i> Thêm phiếu nhập thành công!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i> Cập nhật phiếu nhập thành công!
            </div>
        <?php endif; ?>

        <div class="import-header">
            <h2><i class="fa-solid fa-file-import"></i> Quản lý phiếu nhập kho</h2>
            <button class="btn-add-receipt" onclick="openModal()">
                <i class="fa-solid fa-plus"></i> Tạo phiếu nhập mới
            </button>
        </div>

        <div class="receipt-list">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm kiếm phiếu nhập..." onkeyup="searchReceipt()">
                <select id="filterMonth" onchange="filterByMonth()">
                    <option value="">Tất cả tháng</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>">Tháng <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <table class="receipt-table" id="receiptTable">
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Ngày nhập</th>
                        <th>Tổng tiền</th>
                        <th>Ghi chú</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM import_receipt ORDER BY receipt_id DESC";
                    $result = $connectDb->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr data-month='" . date('n', strtotime($row['import_date'])) . "'>
                                <td>PN{$row['receipt_id']}</td>
                                <td>" . date('d/m/Y H:i', strtotime($row['import_date'])) . "</td>
                                <td>" . number_format($row['total_amount'], 0, ',', '.') . " VND</td>
                                <td>" . ($row['note'] ? $row['note'] : 'Không có ghi chú') . "</td>
                                <td>
                                    <button class='btn-action btn-view' onclick='viewReceipt({$row['receipt_id']})'>
                                    <i class='fa-solid fa-eye'></i> Xem
                                    </button>
                                    <button class='btn-action btn-edit' onclick='editReceipt({$row['receipt_id']})'>
                                        <i class='fa-solid fa-pen-to-square'></i> Sửa
                                    </button>
                                </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center;'>Chưa có phiếu nhập nào</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <?php
            // Tính tổng
            $sql_total = "SELECT SUM(total_amount) as grand_total FROM import_receipt";
            $result_total = $connectDb->query($sql_total);
            $grand_total = $result_total->fetch_assoc()['grand_total'];
            ?>
            <div class="total-summary">
                <h4>Tổng giá trị nhập hàng: <?php echo number_format($grand_total, 0, ',', '.'); ?> VND</h4>
            </div>
        </div>
    </div>

    <!-- Modal Thêm phiếu nhập -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-file-import"></i> Tạo phiếu nhập mới</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="" id="importForm">
                <input type="hidden" name="action" value="add">

                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày nhập <span style="color: red;">*</span></label>
                        <input type="datetime-local" name="import_date" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Ghi chú</label>
                        <input type="text" name="note" placeholder="Nhập ghi chú (không bắt buộc)">
                    </div>
                </div>

                <div class="form-row">
                    <p>
                    <h4>Nhà cung cấp</h4>
                    </p>
                    <select name="suppliers" id="suppliers" style="padding:5px; width:100%;">
                        <option value="">-- Chọn nhà cung cấp --</option>
                        <?php
                        $sql = "SELECT supplier_id, supplier_name FROM suppliers";
                        $result = $connectDb->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['supplier_id'] . '">' . $row['supplier_name'] . '</option>';
                            }
                        } else {
                            echo '<option value="">Không có nhà cung cấp</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="product-items">
                    <h4>Danh sách sản phẩm nhập</h4>
                    <div id="productList">
                        <div class="product-item">
                            <div class="form-group">
                                <label>Sản phẩm <span style="color: red;">*</span></label>
                                <select name="products[0][product_id]" required onchange="updateSubtotal(0)">
                                    <option value="">Chọn sản phẩm</option>
                                    <?php
                                    $sql_products = "SELECT ProductID, ProductName, Price FROM products ORDER BY ProductName";
                                    $result_products = $connectDb->query($sql_products);
                                    while ($product = $result_products->fetch_assoc()) {
                                        echo "<option value='{$product['ProductID']}' data-price='{$product['Price']}'>{$product['ProductName']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Số lượng <span style="color: red;">*</span></label>
                                <input type="number" name="products[0][quantity]" required min="1" value="1" onchange="updateSubtotal(0)">
                            </div>
                            <div class="form-group">
                                <label>Giá nhập <span style="color: red;">*</span></label>
                                <input type="number" name="products[0][import_price]" required min="0" onchange="updateSubtotal(0)">
                            </div>
                            <div class="form-group">
                                <label>Thành tiền</label>
                                <input type="text" class="subtotal" readonly value="0">
                            </div>
                            <button type="button" class="btn-remove-product" onclick="removeProduct(this)" style="display: none;">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn-add-product" onclick="addProduct()">
                        <i class="fa-solid fa-plus"></i> Thêm sản phẩm
                    </button>
                </div>

                <div class="total-summary">
                    <h4>Tổng tiền: <span id="totalAmount">0</span> VND</h4>
                    <input type="hidden" name="total_amount" id="totalAmountInput" value="0">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Lưu phiếu nhập
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Xem chi tiết phiếu nhập -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-file-lines"></i> Chi tiết phiếu nhập</h3>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div id="viewContent">
                <!-- Nội dung sẽ được load bằng JavaScript -->
            </div>
        </div>
    </div>

    <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/checklog.js"></script>

    <script>
        let productCount = 1;

        // Hiển thị thông tin user
        document.addEventListener('DOMContentLoaded', () => {
            const cachedUserInfo = localStorage.getItem('userInfo');
            if (cachedUserInfo) {
                const userInfo = JSON.parse(cachedUserInfo);
                document.querySelector('.name-employee p').textContent = userInfo.fullname;
                document.querySelector('.position-employee p').textContent = userInfo.role;
                document.querySelectorAll('.avatar').forEach(img => img.src = userInfo.avatar);
            }
        });

        // Mở modal thêm phiếu nhập
        function openModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        // Đóng modal
        function closeModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('importForm').reset();
            productCount = 1;
            const productList = document.getElementById('productList');
            productList.innerHTML = productList.children[0].outerHTML;
            updateTotalAmount();
        }

        // Đóng modal xem chi tiết
        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        // Thêm sản phẩm mới
        function addProduct() {
            const productList = document.getElementById('productList');
            const newProduct = productList.children[0].cloneNode(true);

            // Cập nhật name attributes
            const inputs = newProduct.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/\[\d+\]/, `[${productCount}]`);
                }
                if (input.type !== 'button') {
                    input.value = input.type === 'number' ? '1' : '';
                }
            });

            // Hiển thị nút xóa
            const removeBtn = newProduct.querySelector('.btn-remove-product');
            removeBtn.style.display = 'block';

            // Reset subtotal
            newProduct.querySelector('.subtotal').value = '0';

            productList.appendChild(newProduct);
            productCount++;

            // Update onchange events
            const select = newProduct.querySelector('select');
            const quantities = newProduct.querySelectorAll('input[type="number"]');
            select.onchange = () => updateSubtotal(productCount - 1);
            quantities.forEach(input => {
                input.onchange = () => updateSubtotal(productCount - 1);
            });
        }

        // Xóa sản phẩm
        function removeProduct(button) {
            const productList = document.getElementById('productList');
            if (productList.children.length > 1) {
                button.closest('.product-item').remove();
                updateTotalAmount();
            }
        }

        // Cập nhật thành tiền cho từng sản phẩm
        function updateSubtotal(index) {
            const productItems = document.querySelectorAll('.product-item');
            const item = productItems[index];

            const quantity = parseFloat(item.querySelector('input[name*="quantity"]').value) || 0;
            const price = parseFloat(item.querySelector('input[name*="import_price"]').value) || 0;
            const subtotal = quantity * price;

            item.querySelector('.subtotal').value = subtotal.toLocaleString('vi-VN');

            updateTotalAmount();
        }

        // Cập nhật tổng tiền
        function updateTotalAmount() {
            let total = 0;
            const productItems = document.querySelectorAll('.product-item');

            productItems.forEach((item, index) => {
                const quantity = parseFloat(item.querySelector('input[name*="quantity"]').value) || 0;
                const price = parseFloat(item.querySelector('input[name*="import_price"]').value) || 0;
                total += quantity * price;
            });

            document.getElementById('totalAmount').textContent = total.toLocaleString('vi-VN');
            document.getElementById('totalAmountInput').value = total;
        }

        // Tìm kiếm phiếu nhập
        function searchReceipt() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('receiptTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length - 1; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const textValue = cell.textContent || cell.innerText;
                        if (textValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }
        }

        // Lọc theo tháng
        function filterByMonth() {
            const select = document.getElementById('filterMonth');
            const selectedMonth = select.value;
            const table = document.getElementById('receiptTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                if (!selectedMonth) {
                    rows[i].style.display = '';
                } else {
                    const month = rows[i].getAttribute('data-month');
                    rows[i].style.display = (month === selectedMonth) ? '' : 'none';
                }
            }
        }

        // Xem chi tiết phiếu nhập
        function viewReceipt(receiptId) {
            fetch(`../php/get_receipt_detail.php?id=${receiptId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = `
                            <div class="form-row">
                                <div class="form-group">
                                <label>Mã phiếu nhập:</label>
                                <p style="font-size: 16px; font-weight: 500;">PN${data.receipt.receipt_id}</p>
                                </div>
                                <div class="form-group">
                                <label>Ngày nhập:</label>
                                <p style="font-size: 16px; font-weight: 500;">${data.receipt.import_date}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nhà cung cấp:</label>
                                <p style="font-size: 16px;">${data.receipt.supplier_name || 'Không có nhà cung cấp'}</p>
                            </div>
                            <div class="form-group">
                                <label>Ghi chú:</label>
                                <p style="font-size: 16px;">${data.receipt.note || 'Không có ghi chú'}</p>
                            </div>
                            <h4 style="margin-top: 20px; color: #64792c;">Danh sách sản phẩm</h4>
                            <table class="receipt-table">
                                <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá nhập</th>
                                    <th>Thành tiền</th>
                                </tr>
                                </thead>
                                <tbody>
                        `;

                        data.details.forEach((item, index) => {
                            html += `
                <tr>
                  <td>${index + 1}</td>
                  <td>${item.product_name}</td>
                  <td>${item.quantity}</td>
                  <td>${parseFloat(item.import_price).toLocaleString('vi-VN')} VND</td>
                  <td>${parseFloat(item.subtotal).toLocaleString('vi-VN')} VND</td>
                </tr>
              `;
                        });

                        html += `
                </tbody>
              </table>
              <div class="total-summary">
                <h4>Tổng tiền: ${parseFloat(data.receipt.total_amount).toLocaleString('vi-VN')} VND</h4>
              </div>
              <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeViewModal()">Đóng</button>
              </div>
            `;

                        document.getElementById('viewContent').innerHTML = html;
                        document.getElementById('viewModal').style.display = 'block';
                    } else {
                        alert('Không thể tải thông tin phiếu nhập!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi khi tải thông tin!');
                });
        }

        // Xóa phiếu nhập
        function deleteReceipt(receiptId) {
            if (confirm('Bạn có chắc chắn muốn xóa phiếu nhập này?\nLưu ý: Số lượng tồn kho sẽ được trừ lại!')) {
                window.location.href = `importReceipt.php?delete=${receiptId}`;
            }
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const viewModal = document.getElementById('viewModal');

            if (event.target == addModal) {
                closeModal();
            }
            if (event.target == viewModal) {
                closeViewModal();
            }
        }

        // Validate form trước khi submit
        document.getElementById('importForm').addEventListener('submit', function(e) {
            const productItems = document.querySelectorAll('.product-item');
            let hasProduct = false;

            productItems.forEach(item => {
                const select = item.querySelector('select[name*="product_id"]');
                if (select.value) {
                    hasProduct = true;
                }
            });

            if (!hasProduct) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một sản phẩm!');
                return false;
            }

            if (confirm('Xác nhận tạo phiếu nhập này?')) {
                return true;
            } else {
                e.preventDefault();
                return false;
            }
        });






        // Thêm vào sau hàm editReceipt
        function editReceipt(receiptId) {
            fetch(`../php/get_receipt_detail.php?id=${receiptId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Đổi tiêu đề modal
                        document.querySelector('#addModal .modal-header h3').innerHTML =
                            '<i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa phiếu nhập';

                        // Đổi action thành update
                        document.querySelector('input[name="action"]').value = 'update';

                        // Thêm input ẩn cho receipt_id
                        let receiptIdInput = document.querySelector('input[name="receipt_id"]');
                        if (!receiptIdInput) {
                            receiptIdInput = document.createElement('input');
                            receiptIdInput.type = 'hidden';
                            receiptIdInput.name = 'receipt_id';
                            document.getElementById('importForm').appendChild(receiptIdInput);
                        }
                        receiptIdInput.value = receiptId;

                        // BỎ REQUIRED cho các trường khi edit
                        document.querySelectorAll('#importForm [required]').forEach(field => {
                            field.removeAttribute('required');
                        });

                        // Điền thông tin phiếu nhập
                        const importDate = new Date(data.receipt.import_date_raw);
                        const formattedDate = importDate.toISOString().slice(0, 16);
                        document.querySelector('input[name="import_date"]').value = formattedDate;
                        document.querySelector('input[name="note"]').value = data.receipt.note || '';

                        // Điền nhà cung cấp
                        const supplierSelect = document.querySelector('select[name="suppliers"]');
                        if (data.receipt.supplier_id) {
                            supplierSelect.value = data.receipt.supplier_id;
                        }

                        // Xóa các sản phẩm cũ
                        const productList = document.getElementById('productList');
                        productList.innerHTML = '';
                        productCount = 0;

                        // Thêm các sản phẩm từ phiếu nhập
                        data.details.forEach((item, index) => {
                            addProductForEdit(item, index === 0);
                        });

                        // Cập nhật tổng tiền
                        updateTotalAmount();

                        // Đổi nút submit
                        document.querySelector('.btn-submit').innerHTML =
                            '<i class="fa-solid fa-save"></i> Cập nhật phiếu nhập';

                        // Mở modal
                        document.getElementById('addModal').style.display = 'block';
                    } else {
                        alert('Không thể tải thông tin phiếu nhập!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi khi tải thông tin!');
                });
        }

        // Hàm thêm sản phẩm khi edit (không có required)
        function addProductForEdit(item, isFirst) {
            const productList = document.getElementById('productList');
            const newProduct = document.createElement('div');
            newProduct.className = 'product-item';

            newProduct.innerHTML = `
                    <div class="form-group">
                        <label>Sản phẩm</label>
                        <select name="products[${productCount}][product_id]" required onchange="updateSubtotal(${productCount})">
                            <option value="">Chọn sản phẩm</option>
                            <?php
                            $sql_products = "SELECT ProductID, ProductName, Price FROM products WHERE Status = 'appear' ORDER BY ProductName";
                            $result_products = $connectDb->query($sql_products);
                            while ($product = $result_products->fetch_assoc()) {
                                echo "<option value='{$product['ProductID']}' data-price='{$product['Price']}'>{$product['ProductName']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Số lượng</label>
                        <input type="number" name="products[${productCount}][quantity]" min="1" value="1" onchange="updateSubtotal(${productCount})">
                    </div>
                    <div class="form-group">
                        <label>Giá nhập</label>
                        <input type="number" name="products[${productCount}][import_price]" min="0" onchange="updateSubtotal(${productCount})">
                    </div>
                    <div class="form-group">
                        <label>Thành tiền</label>
                        <input type="text" class="subtotal" readonly value="0">
                    </div>
                    <button type="button" class="btn-remove-product" onclick="removeProduct(this)" style="display: ${isFirst ? 'none' : 'block'};">
                        <i class="fa-solid fa-times"></i>
                    </button>
                `;

            productList.appendChild(newProduct);

            // Set giá trị cho select sản phẩm
            const select = newProduct.querySelector('select[name*="product_id"]');
            select.value = item.product_id;

            // Set giá trị cho các trường khác
            newProduct.querySelector('input[name*="quantity"]').value = item.quantity;
            newProduct.querySelector('input[name*="import_price"]').value = item.import_price;
            newProduct.querySelector('.subtotal').value = parseFloat(item.subtotal).toLocaleString('vi-VN');

            productCount++;
        }

        // Sửa lại hàm closeModal để khôi phục required khi đóng
        function closeModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('importForm').reset();

            // Reset lại tiêu đề và nút
            document.querySelector('#addModal .modal-header h3').innerHTML =
                '<i class="fa-solid fa-file-import"></i> Tạo phiếu nhập mới';
            document.querySelector('.btn-submit').innerHTML =
                '<i class="fa-solid fa-save"></i> Lưu phiếu nhập';
            document.querySelector('input[name="action"]').value = 'add';

            // Xóa receipt_id nếu có
            const receiptIdInput = document.querySelector('input[name="receipt_id"]');
            if (receiptIdInput) {
                receiptIdInput.remove();
            }

            // KHÔI PHỤC REQUIRED cho chế độ thêm mới
            const form = document.getElementById('importForm');
            form.querySelector('input[name="import_date"]').setAttribute('required', '');

            // Reset product list với required
            productCount = 1;
            const productList = document.getElementById('productList');
            productList.innerHTML = `
                <div class="product-item">
                    <div class="form-group">
                        <label>Sản phẩm <span style="color: red;">*</span></label>
                        <select name="products[0][product_id]" required onchange="updateSubtotal(0)">
                            <option value="">Chọn sản phẩm</option>
                            <?php
                            $sql_products = "SELECT ProductID, ProductName, Price FROM products WHERE Status = 'appear' ORDER BY ProductName";
                            $result_products = $connectDb->query($sql_products);
                            while ($product = $result_products->fetch_assoc()) {
                                echo "<option value='{$product['ProductID']}' data-price='{$product['Price']}'>{$product['ProductName']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Số lượng <span style="color: red;">*</span></label>
                        <input type="number" name="products[0][quantity]" required min="1" value="1" onchange="updateSubtotal(0)">
                    </div>
                    <div class="form-group">
                        <label>Giá nhập <span style="color: red;">*</span></label>
                        <input type="number" name="products[0][import_price]" required min="0" onchange="updateSubtotal(0)">
                    </div>
                    <div class="form-group">
                        <label>Thành tiền</label>
                        <input type="text" class="subtotal" readonly value="0">
                    </div>
                    <button type="button" class="btn-remove-product" onclick="removeProduct(this)" style="display: none;">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            `;
            updateTotalAmount();
        }
    </script>
</body>

</html>

<?php
$connectDb->close();
?>