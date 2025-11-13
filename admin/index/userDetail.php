<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../php/connect.php';
require_once '../php/User.php';

// Check if accessed with user_id or username parameter
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if ($userId === 0 && $username === '') {
    http_response_code(400);
    echo '<div class="alert alert-danger">Thiếu tham số user_id hoặc username</div>';
    exit;
}

// Pagination for orders
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $records_per_page;

try {
    // Initialize database connection using OOP
    $db = new DatabaseConnection();
    $db->connect();

    // Get user basic info from users table using queryPrepared()
    // Ưu tiên tìm theo user_id, fallback về username
    if ($userId > 0) {
        $sql = "SELECT user_id, Username, FullName, Phone, Role, Status, DateGeneration 
                FROM users 
                WHERE user_id = ?";
        $result = $db->queryPrepared($sql, [$userId], 'i');
    } else {
        $sql = "SELECT user_id, Username, FullName, Phone, Role, Status, DateGeneration 
                FROM users 
                WHERE Username = ?";
        $result = $db->queryPrepared($sql, [$username], 's');
    }
    
    $userData = $result->fetch_assoc();

    if (!$userData) {
        throw new Exception('Không tìm thấy người dùng');
    }

    // Create User object using OOP
    $user = new User([
        'user_id' => $userData['user_id'],
        'Username' => $userData['Username'],
        'FullName' => $userData['FullName'],
        'Phone' => $userData['Phone'],
        'Role' => $userData['Role'],
        'Status' => $userData['Status']
    ]);

    // Get most recent order address for display (if any) using queryPrepared()
    $addressText = 'Chưa có thông tin địa chỉ';
    $sqlAddr = "SELECT o.address_id, a.address_detail, a.ward_id 
                FROM orders o 
                JOIN address a ON a.address_id = o.address_id 
                WHERE o.user_id = ? 
                ORDER BY o.DateGeneration DESC 
                LIMIT 1";
    $resAddr = $db->queryPrepared($sqlAddr, [$userData['user_id']], 'i');
    
    if ($rowAddr = $resAddr->fetch_assoc()) {
        $addressDetail = $rowAddr['address_detail'];
        $wardId = $rowAddr['ward_id'];
        
        // Get ward, district, province names using queryPrepared()
        $sqlLoc = "SELECT w.name as ward_name, d.name as district_name, p.name as province_name 
                   FROM ward w 
                   JOIN district d ON d.district_id = w.district_id 
                   JOIN province p ON p.province_id = d.province_id 
                   WHERE w.ward_id = ?";
        $resLoc = $db->queryPrepared($sqlLoc, [$wardId], 'i');
        
        if ($rowLoc = $resLoc->fetch_assoc()) {
            $addressParts = array_filter([
                $addressDetail,
                $rowLoc['ward_name'],
                $rowLoc['district_name'],
                $rowLoc['province_name']
            ]);
            $addressText = implode(', ', $addressParts);
        }
    }

    // Count total orders for this user using queryPrepared()
    $sqlCount = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
    $resCount = $db->queryPrepared($sqlCount, [$userData['user_id']], 'i');
    $rowCount = $resCount->fetch_assoc();
    $total_orders = (int)$rowCount['total'];

    // Get orders with pagination using queryPrepared()
    $sqlOrders = "SELECT OrderID, Status, PaymentMethod, CustomerName, Phone, DateGeneration, TotalAmount 
                  FROM orders 
                  WHERE user_id = ? 
                  ORDER BY DateGeneration DESC, OrderID DESC 
                  LIMIT ?, ?";
    $resOrders = $db->queryPrepared($sqlOrders, [$userData['user_id'], $offset, $records_per_page], 'iii');
    
    $orders = [];
    while ($row = $resOrders->fetch_assoc()) {
        $orders[] = $row;
    }

    $total_pages = $total_orders > 0 ? (int)ceil($total_orders / $records_per_page) : 1;

} catch (Throwable $e) {
    echo '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $user = null;
    $orders = [];
    $total_pages = 1;
    $total_orders = 0;
    $addressText = '';
}

// Include header and sidebar
include 'header_sidebar.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chi tiết người dùng - <?= $user ? htmlspecialchars($user->getFullname()) : 'Không tìm thấy' ?></title>
  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../style/userDetail.css" rel="stylesheet">
</head>
<body>
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">
        <i class="fas fa-user-circle"></i> Chi tiết người dùng
      </h1>
      <div class="page-actions">
        <a class="back-link" href="customer.php">
          <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
      </div>
    </div>

    <?php if ($user): ?>
      <div class="content-grid">
        <!-- User Info Card -->
        <div class="section">
          <h3 class="section-title">
            <i class="fas fa-id-card"></i>
            Thông tin cá nhân
          </h3>
          
          <div class="user-avatar">
            <?= strtoupper(mb_substr($user->getFullname(), 0, 1)) ?>
          </div>

          <div class="info-row">
            <span class="label">
              <i class="fas fa-user"></i>
              Tên đăng nhập
            </span>
            <span class="value"><?= htmlspecialchars($user->getUsername()) ?></span>
          </div>

          <div class="info-row">
            <span class="label">
              <i class="fas fa-signature"></i>
              Họ và tên
            </span>
            <span class="value"><?= htmlspecialchars($user->getFullname()) ?></span>
          </div>

          <div class="info-row">
            <span class="label">
              <i class="fas fa-phone"></i>
              Số điện thoại
            </span>
            <span class="value"><?= htmlspecialchars($user->getPhone()) ?></span>
          </div>

          <div class="info-row">
            <span class="label">
              <i class="fas fa-calendar-alt"></i>
              Ngày đăng ký
            </span>
            <span class="value"><?= isset($userData['DateGeneration']) ? date('d/m/Y H:i', strtotime($userData['DateGeneration'])) : 'Chưa có thông tin' ?></span>
          </div>

          <div class="info-row">
            <span class="label">
              <i class="fas fa-user-tag"></i>
              Vai trò
            </span>
            <span class="value">
              <span class="role-badge <?= $user->isAdmin() ? 'role-admin' : 'role-customer' ?>">
                <?= $user->getRoleText() ?>
              </span>
            </span>
          </div>

          <div class="info-row">
            <span class="label">
              <i class="fas fa-toggle-on"></i>
              Trạng thái
            </span>
            <span class="value">
              <span class="status-badge <?= $user->isActive() ? 'status-active' : 'status-blocked' ?>">
                <?= $user->getStatusText() ?>
              </span>
            </span>
          </div>

          <div class="info-row">
            <span class="label">
              <i class="fas fa-map-marker-alt"></i>
              Địa chỉ
            </span>
            <span class="value"><?= htmlspecialchars($addressText) ?></span>
          </div>
        </div>

        <!-- Order Statistics Card -->
        <div>
          <div class="section">
            <h3 class="section-title">
              <i class="fas fa-chart-line"></i>
              Thống kê đơn hàng
            </h3>
            
            <?php
            // Calculate order statistics
            $totalOrders = $total_orders;
            $successOrders = 0;
            $totalRevenue = 0;
            
            foreach ($orders as $order) {
              if ($order['Status'] === 'success') {
                $successOrders++;
              }
              $totalRevenue += (float)$order['TotalAmount'];
            }
            ?>

            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-value"><?= $totalOrders ?></div>
                <div class="stat-label">Tổng đơn hàng</div>
              </div>
              <div class="stat-card green">
                <div class="stat-value"><?= $successOrders ?></div>
                <div class="stat-label">Đơn hoàn tất</div>
              </div>
              <div class="stat-card orange">
                <div class="stat-value"><?= number_format($totalRevenue / 1000000, 1) ?>M</div>
                <div class="stat-label">Tổng chi tiêu</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order History Section -->
      <div class="section">
        <h3 class="section-title">
          <i class="fas fa-shopping-bag"></i>
          Lịch sử mua hàng
        </h3>

        <?php if (!empty($orders)): ?>
          <table class="orders-table">
            <thead>
              <tr>
                <th>Mã đơn</th>
                <th>Tên khách hàng</th>
                <th>Ngày tạo</th>
                <th>Trạng thái</th>
                <th>Thanh toán</th>
                <th>Tổng tiền</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
                <?php 
                  $status = $order['Status'];
                  $statusClass = 'status-' . $status;
                  $statusText = [
                    'execute' => 'Đang xử lý',
                    'ship' => 'Đang giao',
                    'success' => 'Hoàn tất',
                    'fail' => 'Thất bại',
                    'confirmed' => 'Đã xác nhận'
                  ][$status] ?? ucfirst($status);
                ?>
                <tr>
                  <td><strong>#<?= htmlspecialchars($order['OrderID']) ?></strong></td>
                  <td><?= htmlspecialchars($order['CustomerName'] ?? $user->getFullname()) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($order['DateGeneration'])) ?></td>
                  <td>
                    <span class="status-chip <?= htmlspecialchars($statusClass) ?>">
                      <?= htmlspecialchars($statusText) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($order['PaymentMethod']) ?></td>
                  <td><strong><?= number_format((float)$order['TotalAmount'], 0, ',', '.') ?> ₫</strong></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php if ($total_pages > 1): ?>
            <?php
            // Build pagination URL based on which parameter was used
            $baseUrl = '?';
            if ($userId > 0) {
                $baseUrl .= 'user_id=' . urlencode($userId);
            } else {
                $baseUrl .= 'username=' . urlencode($username);
            }
            ?>
            <div class="pagination">
              <?php if ($page > 1): ?>
                <a class="page-btn" href="<?= $baseUrl ?>&page=<?= $page - 1 ?>">
                  <i class="fas fa-chevron-left"></i>
                </a>
              <?php endif; ?>
              
              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                  <span class="page-btn active"><?= $i ?></span>
                <?php else: ?>
                  <a class="page-btn" href="<?= $baseUrl ?>&page=<?= $i ?>">
                    <?= $i ?>
                  </a>
                <?php endif; ?>
              <?php endfor; ?>
              
              <?php if ($page < $total_pages): ?>
                <a class="page-btn" href="<?= $baseUrl ?>&page=<?= $page + 1 ?>">
                  <i class="fas fa-chevron-right"></i>
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <p>Người dùng chưa có đơn hàng nào</p>
          </div>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <div class="section">
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i>
          Không tìm thấy thông tin người dùng.
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../js/checklog.js"></script>
  <script src="../js/main.js"></script>
</body>
</html>