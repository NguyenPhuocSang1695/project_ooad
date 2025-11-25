<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết người dùng</title>

    <link href="../icon/css/all.css" rel="stylesheet">
    <link href="../style/generall.css" rel="stylesheet">
    <link href="../style/main1.css" rel="stylesheet">
    <link href="../style/LogInfo.css" rel="stylesheet">
    <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style/userDetail.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/responsiveCustomer.css">
</head>

<body>
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once '../php/connect.php';
    require_once '../php/UserManager.php';
    require_once '../php/User.php';

    // Initialize database connection
    $myconn = new DatabaseConnection();
    $myconn->connect();

    // Get user_id or username from URL
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $username = isset($_GET['username']) ? trim($_GET['username']) : '';

    if ($userId <= 0 && $username === '') {
        echo "<div class='alert alert-danger'>Không tìm thấy thông tin người dùng</div>";
        exit;
    }

    try {
        $userManager = new UserManager($myconn);

        // Get user details
        if ($userId > 0) {
            $result = $userManager->getUserDetailsById($userId);
        } else {
            $result = $userManager->getUserDetails($username);
        }

        if (!$result['success']) {
            echo "<div class='alert alert-danger'>{$result['message']}</div>";
            exit;
        }

        $userData = $result['data'];
        $userId = $userData['user_id'];

        // Get orders with pagination
        $records_per_page = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        $offset = ($page - 1) * $records_per_page;

        $ordersResult = $userManager->getUserOrders($userId, $offset, $records_per_page);
        $orders = $ordersResult['orders'];
        $total_orders = $ordersResult['total'];
        $total_pages = $total_orders > 0 ? (int)ceil($total_orders / $records_per_page) : 1;

        // Calculate total amount spent
        $total_amount_spent = 0;
        foreach ($orders as $order) {
            $total_amount_spent += isset($order['TotalAmount']) ? (float)$order['TotalAmount'] : 0;
        }

        // Get total from all orders (not just current page)
        $conn = $myconn->getConnection();
        $stmt = $conn->prepare("SELECT COALESCE(SUM(TotalAmount), 0) as total FROM orders WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $totalResult = $stmt->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $total_amount_all = $totalRow['total'];
        $stmt->close();
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Lỗi: " . htmlspecialchars($e->getMessage()) . "</div>";
        exit;
    }
    ?>

    <!-- header -->
    <?php require_once 'header_sidebar.php'; ?>

    <!-- main container -->
    <div class="user-detail-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Chi tiết người dùng</h1>
            <div class="page-actions">
                <a href="customer.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <!-- User Info Section -->
        <div class="user-info-card">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <div class="info-row">
                    <span class="info-label">Họ và tên:</span>
                    <span class="info-value"><?php echo htmlspecialchars($userData['fullname']); ?></span>
                </div>
                <?php if ($userData['role'] === 'admin'): ?>
                    <div class="info-row">
                        <span class="info-label">Username:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userData['username']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="info-row">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value"><?php echo htmlspecialchars($userData['phone']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vai trò:</span>
                    <span class="info-value">
                        <span class="role-badge"><?php echo $userData['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng'; ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">
                        <span class="status-badge <?php echo $userData['status'] === 'Active' ? 'status-active' : 'status-blocked'; ?>">
                            <?php echo $userData['status'] === 'Active' ? 'Hoạt động' : 'Đã khóa'; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Tổng đơn hàng</div>
                    <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Tổng chi tiêu</div>
                    <div class="stat-value"><?php echo number_format($total_amount_all, 0, ',', '.'); ?> VND</div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="orders-section">
            <h2 class="section-title">Lịch sử đơn hàng</h2>

            <?php if (!empty($orders)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Tên khách hàng</th>
                            <th>Ngày tạo</th>
                            <th>Phương thức thanh toán</th>
                            <th>Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr class="order-row" data-order-id="<?php echo (int)$order['OrderID']; ?>" style="cursor:pointer;">
                                <td data-label="Mã đơn">#<?php echo htmlspecialchars($order['OrderID']); ?></td>
                                <td data-label="Tên khách hàng"><?php echo htmlspecialchars($order['CustomerName'] ?? 'N/A'); ?></td>
                                <td data-label="Ngày tạo"><?php echo htmlspecialchars($order['DateGeneration'] ?? 'N/A'); ?></td>
                                <td data-label="Thanh toán">
                                    <?php
                                    $paymentMethod = $order['PaymentMethod'] ?? 'N/A';
                                    switch ($paymentMethod) {
                                        case 'CASH':
                                            echo 'Thanh toán tại quầy';
                                            break;
                                        case 'COD':
                                            echo 'Thanh toán khi nhận hàng';
                                            break;
                                        case 'BANKING':
                                            echo 'Chuyển khoản ngân hàng';
                                            break;
                                        default:
                                            echo htmlspecialchars($paymentMethod);
                                    }
                                    ?>
                                </td>
                                <td data-label="Tổng tiền"><?php echo number_format($order['TotalAmount'] ?? 0, 0, ',', '.'); ?> VND</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <button class="page-btn active"><?php echo $i; ?></button>
                            <?php else: ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="page-btn"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-inbox"></i>
                    <p>Người dùng chưa có đơn hàng nào</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Chi tiết đơn hàng -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: #6aa173; color: white; border-radius: 12px 12px 0 0; border: none; padding: 20px;">
                    <h5 class="modal-title" id="orderDetailLabel" style="font-weight: 700; font-size: 18px;">Chi tiết đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body">
                    <div id="orderDetailContent" style="max-height: 600px; overflow-y: auto;">
                        <!-- Chi tiết sẽ được load bằng JavaScript -->
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 15px 20px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="asset/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/checklog.js"></script>
    <script src="../js/main.js"></script>

    <script>
        // Hàm chuyển đổi phương thức thanh toán sang Tiếng Việt
        function formatPaymentMethod(method) {
            if (!method) return "Không rõ";

            const normalizedMethod = method.toLowerCase().trim();
            const paymentMethods = {
                cod: "Thanh toán khi nhận hàng",
                banking: "Chuyển khoản ngân hàng",
                cash: "Thanh toán tại quầy",
            };

            return paymentMethods[normalizedMethod] || method;
        }

        // Hàm hiển thị chi tiết đơn hàng trong modal
        function showOrderDetailModal(orderId) {
            console.log("[SHOW_DETAIL] Loading order:", orderId);

            // Fetch order details from API
            fetch(`../php/get_order_detail.php?orderId=${encodeURIComponent(orderId)}`)
                .then((response) => response.json())
                .then((data) => {
                    console.log("[ORDER_DETAIL] Data:", data);

                    if (!data.success) {
                        throw new Error(data.error || "Không thể tải chi tiết đơn hàng");
                    }

                    const order = data.order;

                    // Build products table HTML
                    let productsHTML = "";
                    order.products.forEach((product, index) => {
                        productsHTML += `
                            <tr>
                                <td style="text-align: center;">${index + 1}</td>
                                <td>${product.productName}</td>
                                <td style="text-align: center;">${product.quantity}</td>
                                <td style="text-align: right;">${parseInt(product.unitPrice).toLocaleString("vi-VN")} VND</td>
                                <td style="text-align: right;">${parseInt(product.totalPrice).toLocaleString("vi-VN")} VND</td>
                            </tr>
                        `;
                    });

                    // Determine address display text
                    const hasNoAddress = !order.address || order.address.trim() === "";
                    const addressDisplay = hasNoAddress ? "Không có" : order.address;

                    // Update modal content
                    const modalBody = document.querySelector("#orderDetailModal .modal-body");
                    if (modalBody) {
                        modalBody.innerHTML = `
                            <div style="padding: 20px;">
                                <!-- Order Info Section -->
                                <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
                                    <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">📋 Thông tin đơn hàng</h5>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                        <div>
                                            <label style="color: #666; font-size: 12px; text-transform: uppercase;">Mã đơn hàng</label>
                                            <p style="margin: 5px 0; font-weight: 600; color: #333;">#${order.orderId}</p>
                                        </div>
                                        <div>
                                            <label style="color: #666; font-size: 12px; text-transform: uppercase;">Ngày tạo</label>
                                            <p style="margin: 5px 0; font-weight: 600; color: #333;">${new Date(order.orderDate).toLocaleString("vi-VN")}</p>
                                        </div>
                                        <div>
                                            <label style="color: #666; font-size: 12px; text-transform: uppercase;">Phương thức thanh toán: </label>
                                            <p style="margin: 5px 0; font-weight: 600; color: #333;">${formatPaymentMethod(order.paymentMethod)}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Customer Info Section -->
                                <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
                                    <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">👤 Thông tin khách hàng: </h5>
                                    ${
                                        (order.customerName && String(order.customerName).trim() !== "Không có") ||
                                        (order.customerPhone && String(order.customerPhone).trim() !== "Không có" && String(order.customerPhone).trim() !== "0000000000")
                                            ? `
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                            ${order.customerName && String(order.customerName).trim() !== "Không có " ? `
                                            <div>
                                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Họ tên: </label>
                                                <p style="margin: 5px 0; font-weight: 600; color: #000000ff;">${order.customerName}</p>
                                            </div>
                                            ` : ""}
                                            ${order.customerPhone && String(order.customerPhone).trim() !== "Không có" && String(order.customerPhone).trim() !== "0000000000" ? `
                                            <div>
                                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Số điện thoại: </label>
                                                <p style="margin: 5px 0; font-weight: 600; color: #333;">${order.customerPhone}</p>
                                            </div>
                                            ` : ""}
                                        </div>
                                        `
                                            : `<p>Không có</p>`
                                    }
                                </div>
                                
                                <!-- Address Section -->
                                <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
                                    <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">📍 Địa chỉ giao hàng: </h5>
                                    <p style="margin: 0; color: #333; line-height: 1.6;">${addressDisplay}</p>
                                </div>
                                
                                <!-- Products Section -->
                                <div style="margin-bottom: 30px;">
                                    <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">📦 Sản phẩm (${order.productCount})</h5>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead style="background-color: #f8f9fa; border-bottom: 2px solid #ddd;">
                                            <tr>
                                                <th style="padding: 12px; text-align: center; color: #666; font-weight: 600;">STT</th>
                                                <th style="padding: 12px; text-align: left; color: #666; font-weight: 600;">Sản phẩm</th>
                                                <th style="padding: 12px; text-align: center; color: #666; font-weight: 600;">Số lượng</th>
                                                <th style="padding: 12px; text-align: right; color: #666; font-weight: 600;">Đơn giá</th>
                                                <th style="padding: 12px; text-align: right; color: #666; font-weight: 600;">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${productsHTML}
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Voucher Section (if exists) -->
                                ${order.voucher ? `
                                <div style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #f5f7fa 0%, #d4edda 100%); border-radius: 10px; border-left: 5px solid #6de323ff; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                        <span style="font-size: 24px;">🎁</span>
                                        <h5 style="margin: 0; color: #2c3e50; font-weight: 700; font-size: 16px;">Mã giảm giá đã áp dụng</h5>
                                        <span style="display: inline-block; padding: 4px 10px; background-color: #4bec32ff; color: white; border-radius: 20px; font-size: 11px; font-weight: 600;">Đã dùng</span>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                                        <div style="padding: 10px; background-color: rgba(255,255,255,0.8); border-radius: 6px;">
                                            <label style="color: #7f8c8d; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Mã voucher</label>
                                            <p style="margin: 8px 0 0 0; font-weight: 700; color: #2c3e50; font-size: 15px;">${order.voucher.name}</p>
                                        </div>
                                        <div style="padding: 10px; background-color: rgba(255,255,255,0.8); border-radius: 6px;">
                                            <label style="color: #7f8c8d; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Tỷ lệ giảm</label>
                                            <p style="margin: 8px 0 0 0; font-weight: 700; color: #e74c3c; font-size: 15px;">${order.voucher.discountPercent}%</p>
                                        </div>
                                        <div style="padding: 10px; background-color: rgba(255,255,255,0.8); border-radius: 6px;">
                                            <label style="color: #7f8c8d; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Số tiền giảm</label>
                                            <p style="margin: 8px 0 0 0; font-weight: 700; color: #27ae60; font-size: 15px;">-${parseInt(order.voucher.discountAmount).toLocaleString("vi-VN")} VND</p>
                                        </div>
                                    </div>
                                    ${order.voucher.conditions ? `
                                    <div style="margin-top: 12px; padding: 10px; background-color: rgba(100,150,200,0.1); border-radius: 6px; border-left: 3px solid #3498db;">
                                        <label style="color: #2c3e50; font-size: 11px; text-transform: uppercase; font-weight: 600;">Điều kiện áp dụng</label>
                                        <p style="margin: 6px 0 0 0; color: #555; font-size: 13px;">${order.voucher.conditions}</p>
                                    </div>
                                    ` : ""}
                                </div>
                                ` : ""}
                                
                                <!-- Total Section -->
                                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #27ae60;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 16px; font-weight: 600; color: #333;">Tổng tiền: </span>
                                        <span style="font-size: 24px; font-weight: 700; color: #27ae60;">${parseInt(order.totalAmount).toLocaleString("vi-VN")} VND</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById("orderDetailModal"));
                    modal.show();

                    console.log("[ORDER_DETAIL] Modal displayed successfully");
                })
                .catch((error) => {
                    console.error("[ERROR_DETAIL]", error);
                    alert("Lỗi khi tải chi tiết đơn hàng: " + error.message);
                });
        }

        // Click on order row to view order details
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.order-row').forEach(function(row) {
                row.addEventListener('click', function(e) {
                    e.preventDefault();
                    const orderId = this.getAttribute('data-order-id');
                    if (orderId) {
                        showOrderDetailModal(orderId);
                    }
                });
            });
        });
    </script>
</body>

</html>