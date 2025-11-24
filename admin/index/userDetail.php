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
                                        case 'BANK':
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

    <script src="asset/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/checklog.js"></script>
    <script src="../js/main.js"></script>

    <script>
        // Click on order row to view order details
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.order-row').forEach(function(row) {
                row.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    if (orderId) {
                        window.location.href = 'orderPage.php?order_id=' + orderId;
                    }
                });
            });
        });
    </script>
</body>

</html>