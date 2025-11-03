<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../php/connect.php';
require_once '../php/UserManager.php';
require_once '../php/User.php';

$username = isset($_GET['username']) ? trim($_GET['username']) : '';
if ($username === '') {
    http_response_code(400);
    echo '<div class="alert alert-danger">Thiếu tham số username</div>';
    exit;
}

// Pagination for orders
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $records_per_page;

try {
    $db = new DatabaseConnection();
    $db->connect();
    $userManager = new UserManager($db);

    // Get user details
    $ud = $userManager->getUserDetails($username);
    if (!$ud['success']) {
        throw new Exception($ud['message'] ?? 'Không tìm thấy người dùng');
    }
    $user = $ud['data'];

    // Resolve human-readable address names
    $provinceName = $districtName = $wardName = '';
    if (!empty($user['ward_id'])) {
        $res = $db->queryPrepared('SELECT w.name as ward_name, d.name as district_name, p.name as province_name FROM ward w JOIN district d ON d.district_id = w.district_id JOIN province p ON p.province_id = d.province_id WHERE w.ward_id = ?', [(int)$user['ward_id']], 'i');
        if ($res && $row = $res->fetch_assoc()) {
            $wardName = $row['ward_name'];
            $districtName = $row['district_name'];
            $provinceName = $row['province_name'];
        }
    } elseif (!empty($user['district_id'])) {
        $res = $db->queryPrepared('SELECT d.name as district_name, p.name as province_name FROM district d JOIN province p ON p.province_id = d.province_id WHERE d.district_id = ?', [(int)$user['district_id']], 'i');
        if ($res && $row = $res->fetch_assoc()) {
            $districtName = $row['district_name'];
            $provinceName = $row['province_name'];
        }
    } elseif (!empty($user['province_id'])) {
        $res = $db->queryPrepared('SELECT name FROM province WHERE province_id = ?', [(int)$user['province_id']], 'i');
        if ($res && $row = $res->fetch_assoc()) { $provinceName = $row['name']; }
    }

    $addressText = htmlspecialchars(trim(($user['address_detail'] ?? '') . ' ' . $wardName . ' ' . $districtName . ' ' . $provinceName));

    // Orders
    $ordersRes = $userManager->getUserOrders($username, $offset, $records_per_page);
    $orders = $ordersRes['orders'] ?? [];
    $total_orders = (int)($ordersRes['total'] ?? 0);
    $total_pages = $total_orders > 0 ? (int)ceil($total_orders / $records_per_page) : 1;
} catch (Throwable $e) {
    echo '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $user = null;
    $orders = [];
    $total_pages = 1;
}
    include 'header_sidebar.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chi tiết người dùng</title>
  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .container { padding: 20px; }
    .section { background: #fff; border-radius: 8px; padding: 16px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .section h3 { margin: 0 0 12px; font-size: 18px; }
    .label { color: #666; width: 160px; display: inline-block; }
    .value { font-weight: 600; }
    .orders-table { width: 100%; border-collapse: collapse; }
    .orders-table th, .orders-table td { padding: 10px; border-bottom: 1px solid #eee; text-align:left; }
    .status-chip { padding: 2px 8px; border-radius: 12px; font-size: 12px; background: #f1f1f1; display:inline-block; }
    .status-execute { background:#fff3cd; }
    .status-ship { background:#cfe2ff; }
    .status-success { background:#d1e7dd; }
    .status-fail { background:#f8d7da; }
    .status-confirmed { background:#e2e3e5; }
    .page-actions { display:flex; gap:8px; align-items:center; }
    .back-link { text-decoration:none; display:inline-flex; align-items:center; gap:6px; color:#2c7a7b; }
  </style>
</head>
<body>
  <div class="container">
    <div class="page-actions">
      <a class="back-link" href="customer.php"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
    </div>

    <div class="section">
      <h3>Thông tin người dùng</h3>
      <?php if ($user): ?>
      <p><span class="label">Tên đăng nhập:</span> <span class="value"><?= htmlspecialchars($user['username']) ?></span></p>
      <p><span class="label">Họ và tên:</span> <span class="value"><?= htmlspecialchars($user['fullname']) ?></span></p>
      <p><span class="label">Email:</span> <span class="value"><?= htmlspecialchars($user['email']) ?></span></p>
      <p><span class="label">Số điện thoại:</span> <span class="value"><?= htmlspecialchars($user['phone']) ?></span></p>
      <p><span class="label">Vai trò:</span> <span class="value"><?= htmlspecialchars($user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng') ?></span></p>
      <p><span class="label">Trạng thái:</span> <span class="value"><?= htmlspecialchars($user['status'] === 'Active' ? 'Hoạt động' : 'Bị khóa') ?></span></p>
      <p><span class="label">Địa chỉ:</span> <span class="value"><?= $addressText ?></span></p>
      <?php else: ?>
      <div class="alert alert-warning">Không tìm thấy thông tin người dùng.</div>
      <?php endif; ?>
    </div>

    <div class="section">
      <h3>Lịch sử mua hàng</h3>
      <?php if (!empty($orders)): ?>
      <table class="orders-table">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Ngày tạo</th>
            <th>Trạng thái</th>
            <th>Thanh toán</th>
            <th>Tổng tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <?php 
            $status = $o['Status'];
            $statusClass = 'status-' . $status;
            $statusText = [
              'execute' => 'Đang xử lý',
              'ship' => 'Đang giao',
              'success' => 'Hoàn tất',
              'fail' => 'Thất bại',
              'confirmed' => 'Đã xác nhận'
            ][$status] ?? $status;
          ?>
          <tr>
            <td>#<?= htmlspecialchars($o['OrderID']) ?></td>
            <td><?= htmlspecialchars($o['DateGeneration']) ?></td>
            <td><span class="status-chip <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($statusText) ?></span></td>
            <td><?= htmlspecialchars($o['PaymentMethod']) ?></td>
            <td><?= number_format((float)$o['TotalAmount'], 0, ',', '.') ?> ₫</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if ($total_pages > 1): ?>
      <div class="pagination" style="margin-top:12px;">
        <?php if ($page > 1): ?>
          <a class="page-btn" href="?username=<?= urlencode($username) ?>&page=<?= $page-1 ?>">&laquo;</a>
        <?php endif; ?>
        <?php for ($i=1; $i <= $total_pages; $i++): ?>
          <?php if ($i == $page): ?>
            <span class="page-btn active"><?= $i ?></span>
          <?php else: ?>
            <a class="page-btn" href="?username=<?= urlencode($username) ?>&page=<?= $i ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
          <a class="page-btn" href="?username=<?= urlencode($username) ?>&page=<?= $page+1 ?>">&raquo;</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php else: ?>
      <div class="alert alert-info">Chưa có đơn hàng nào.</div>
      <?php endif; ?>
    </div>
  </div>

  <script src="asset/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    