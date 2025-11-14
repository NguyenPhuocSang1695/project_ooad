<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../php/connect.php';
require_once '../php/User.php';
require_once '../php/UserManager.php';

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
    // Initialize UserManager with OOP pattern
    $userManager = new UserManager();
    
    // Get user details using UserManager
    $userResult = $userId > 0 
        ? $userManager->getUserDetailsById($userId)
        : $userManager->getUserDetails($username);
    
    if (!$userResult['success']) {
        throw new Exception($userResult['message']);
    }
    
    $userData = $userResult['data'];
    
    // Create User object
    $user = new User([
        'user_id' => $userData['user_id'],
        'Username' => $userData['username'],
        'FullName' => $userData['fullname'],
        'Phone' => $userData['phone'],
        'Role' => $userData['role'],
        'Status' => $userData['status']
    ]);

    // Get orders using UserManager
    $ordersResult = $userManager->getUserOrders($userData['user_id'], $offset, $records_per_page);
    $orders = $ordersResult['orders'];
    $total_orders = $ordersResult['total'];
    $total_pages = $total_orders > 0 ? (int)ceil($total_orders / $records_per_page) : 1;
    
    // Get address from most recent order (if exists)
    $addressText = 'Chưa có thông tin địa chỉ';
    if (!empty($orders)) {
        $db = $userManager->dbConnection ?? new DatabaseConnection();
        if (!$db->getConnection()) {
            $db->connect();
        }
        
        $firstOrder = $orders[0];
        if (isset($firstOrder['address_id']) && $firstOrder['address_id']) {
            $sqlAddr = "SELECT a.address_detail, a.ward_id 
                        FROM address a 
                        WHERE a.address_id = ?";
            $resAddr = $db->queryPrepared($sqlAddr, [$firstOrder['address_id']], 'i');
            
            if ($rowAddr = $resAddr->fetch_assoc()) {
                $addressDetail = $rowAddr['address_detail'];
                $wardId = $rowAddr['ward_id'];
                
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
        }
    }

} catch (Throwable $e) {
    echo '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $user = null;
    $orders = [];
    $total_pages = 1;
    $total_orders = 0;
    $addressText = '';
    $userData = [];
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
        <!-- Nút sửa và xoá người dùng -->
        <?php if ($user): ?>
        <button class="btn btn-primary" style="margin-left:12px" onclick="showEditUserPopup('<?= htmlspecialchars($user->getUsername()) ?>', <?= (int)$user->getId() ?>)">
          <i class="fas fa-edit"></i> Sửa thông tin
        </button>
        <button id="deleteUserBtn" class="btn btn-danger" style="margin-left:12px" data-user-id="<?= (int)$user->getId() ?>">
          <i class="fas fa-trash"></i> Xóa người dùng
        </button>
        <?php endif; ?>
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

          <!-- <div class="info-row">
            <span class="label">
              <i class="fas fa-user"></i>
              Tên đăng nhập
            </span>
            <span class="value"><?= htmlspecialchars($user->getUsername()) ?></span>
          </div> -->

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

          <!-- <div class="info-row">
            <span class="label">
              <i class="fas fa-calendar-alt"></i>
              Ngày đăng ký
            </span>
            <span class="value"><?= isset($userData['DateGeneration']) ? date('d/m/Y H:i', strtotime($userData['DateGeneration'])) : 'Chưa có thông tin' ?></span>
          </div> -->

          <!-- <div class="info-row">
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
          </div> -->

          <!-- <div class="info-row">
            <span class="label">
              <i class="fas fa-map-marker-alt"></i>
              Địa chỉ
            </span>
            <span class="value"><?= htmlspecialchars($addressText) ?></span>
          </div> -->
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
      $totalRevenue = 0.00;

      // Get total revenue from all orders (not just current page)
      if (!empty($userData) && isset($userData['user_id'])) {
        try {
          $db = $userManager->dbConnection ?? new DatabaseConnection();
          if (!$db->getConnection()) {
            $db->connect();
          }
          
          $sqlSum = "SELECT COALESCE(SUM(TotalAmount),0) AS total_revenue
                     FROM orders WHERE user_id = ?";
          $resSum = $db->queryPrepared($sqlSum, [$userData['user_id']], 'i');
          if ($resSum && ($rowSum = $resSum->fetch_assoc())) {
            $totalRevenue = (float)$rowSum['total_revenue'];
          }
        } catch (Throwable $e) {
          // Fallback: calculate from current page only
          foreach ($orders as $order) {
            $totalRevenue += (float)$order['TotalAmount'];
          }
        }
      }
      ?>

            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-value"><?= $totalOrders ?></div>
                <div class="stat-label">Tổng đơn hàng</div>
              </div>
              <div class="stat-card orange">
                <div class="stat-value"><?= number_format($totalRevenue, 0, ',', '.') ?> ₫</div>
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
                <!-- <th>Trạng thái</th> -->
                <th>Thanh toán</th>
                <th>Tổng tiền</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
                <tr style="cursor: pointer;" onclick="showOrderDetailModal(<?= (int)$order['OrderID'] ?>)" title="Click để xem chi tiết">
                  <td><strong>#<?= htmlspecialchars($order['OrderID']) ?></strong></td>
                  <td><?= htmlspecialchars($order['CustomerName'] ?? $user->getFullname()) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($order['DateGeneration'])) ?></td>
                  <!-- <td>
                    <span class="status-chip">
                      Status info
                    </span>
                  </td> -->
                  <td>
                    <?php
                    $paymentMethod = strtolower(trim($order['PaymentMethod']));
                    $paymentText = match($paymentMethod) {
                      'cash' => 'Tiền mặt',
                      'cod' => 'Thanh toán khi nhận hàng',
                      'banking' => 'Chuyển khoản ngân hàng',
                      'momo' => 'Ví điện tử MoMo',
                      'vnpay' => 'VNPay',
                      default => htmlspecialchars($order['PaymentMethod'])
                    };
                    echo $paymentText;
                    ?>
                  </td>
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

  <!-- Modal Chi tiết đơn hàng -->
  <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header" style="background: #6aa173; color: white; border-radius: 12px 12px 0 0;">
          <h5 class="modal-title" id="orderDetailLabel" style="font-weight: 700;">Chi tiết đơn hàng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
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

  <script src="asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../js/checklog.js"></script>
  <script src="../js/main.js"></script>
  <script src="../js/delete-user.js"></script>
  <script src="../js/edit-user.js"></script>
  
  <script>
  // Hàm chuyển đổi phương thức thanh toán sang Tiếng Việt
  function formatPaymentMethod(method) {
    if (!method) return 'Không rõ';
    const normalizedMethod = method.toLowerCase().trim();
    const paymentMethods = {
      'cod': 'Thanh toán khi nhận hàng',
      'banking': 'Chuyển khoản ngân hàng',
      'momo': 'Ví điện tử MoMo',
      'vnpay': 'VNPay',
      'cash': 'Tiền mặt'
    };
    return paymentMethods[normalizedMethod] || method;
  }

  function showOrderDetailModal(orderId) {
    console.log('[SHOW_DETAIL] Loading order:', orderId);
    
    // Fetch order details from API
    fetch(`../php/get_order_detail.php?orderId=${encodeURIComponent(orderId)}`)
      .then(response => response.json())
      .then(data => {
        console.log('[ORDER_DETAIL] Data:', data);
        
        if (!data.success) {
          throw new Error(data.error || 'Không thể tải chi tiết đơn hàng');
        }
        
        const order = data.order;
        console.log('[ORDER_DETAIL] Voucher:', order.voucher);
        
        // Build products table HTML
        let productsHTML = '';
        order.products.forEach((product, index) => {
          productsHTML += `
            <tr>
              <td style="text-align: center;">${index + 1}</td>
              <td>${product.productName}</td>
              <td style="text-align: center;">${product.quantity}</td>
              <td style="text-align: right;">${parseInt(product.unitPrice).toLocaleString('vi-VN')} VNĐ</td>
              <td style="text-align: right;">${parseInt(product.totalPrice).toLocaleString('vi-VN')} VNĐ</td>
            </tr>
          `;
        });
        
        // Update modal content
        const modalBody = document.querySelector('#orderDetailModal .modal-body');
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
                    <p style="margin: 5px 0; font-weight: 600; color: #333;">${new Date(order.orderDate).toLocaleString('vi-VN')}</p>
                  </div>
                  <div>
                    <label style="color: #666; font-size: 12px; text-transform: uppercase;">Phương thức thanh toán</label>
                    <p style="margin: 5px 0; font-weight: 600; color: #333;">${formatPaymentMethod(order.paymentMethod)}</p>
                  </div>
                </div>
              </div>
              
              <!-- Customer Info Section -->
              <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
                <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">👤 Thông tin khách hàng</h5>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                  <div>
                    <label style="color: #666; font-size: 12px; text-transform: uppercase;">Họ tên</label>
                    <p style="margin: 5px 0; font-weight: 600; color: #333;">${order.customerName}</p>
                  </div>
                  <div>
                    <label style="color: #666; font-size: 12px; text-transform: uppercase;">Số điện thoại</label>
                    <p style="margin: 5px 0; font-weight: 600; color: #333;">${order.customerPhone}</p>
                  </div>
                </div>
              </div>
              
              <!-- Address Section -->
              <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
                <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">📍 Địa chỉ giao hàng</h5>
                <p style="margin: 0; color: #333; line-height: 1.6;">${order.address}</p>
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
                      <p style="margin: 8px 0 0 0; font-weight: 700; color: #27ae60; font-size: 15px;">-${parseInt(order.voucher.discountAmount).toLocaleString('vi-VN')} VNĐ</p>
                    </div>
                  </div>
                  ${order.voucher.conditions ? `
                    <div style="margin-top: 12px; padding: 10px; background-color: rgba(100,150,200,0.1); border-radius: 6px; border-left: 3px solid #3498db;">
                      <label style="color: #2c3e50; font-size: 11px; text-transform: uppercase; font-weight: 600;">Điều kiện áp dụng</label>
                      <p style="margin: 6px 0 0 0; color: #555; font-size: 13px;">${order.voucher.conditions}</p>
                    </div>
                  ` : ''}
                </div>
              ` : ''}
              
              <!-- Total Section -->
              <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <span style="font-size: 16px; font-weight: 600; color: #333;">Thành tiền</span>
                  <span style="font-size: 24px; font-weight: 700; color: #667eea;">${parseInt(order.totalAmount).toLocaleString('vi-VN')} VNĐ</span>
                </div>
              </div>
            </div>
          `;
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
        modal.show();
        
        console.log('[ORDER_DETAIL] Modal displayed successfully');
      })
      .catch(error => {
        console.error('[ERROR_DETAIL]', error);
        alert('Lỗi khi tải chi tiết đơn hàng: ' + error.message);
      });
  }
  </script>
  
  <?php
    // Include edit user modal so the Edit button works
    define('INCLUDE_CHECK', true);
    require_once '../php/edit_user_form.php';
  ?>
</body>
</html>