<?php
include('../php/connect.php');
include('../php/login_check.php');
// include '../php/check_session.php';
$avatarPath = ($_SESSION['Role'] === 'admin')
  ? "../../assets/images/admin.jpg"
  : "../../assets/images/sang.jpg";
$orderID = isset($_GET['code_Product']) ? $_GET['code_Product'] : null;

if ($orderID) {
  // 1. Lấy thông tin tổng quan đơn hàng
  $sql_order = "SELECT o.OrderID, o.DateGeneration, o.Status, o.PaymentMethod
                  FROM orders o
                  WHERE o.OrderID = ?";
  $stmt_order = $myconn->prepare($sql_order);
  $stmt_order->bind_param("i", $orderID);
  $stmt_order->execute();
  $result_order = $stmt_order->get_result();
  $orderInfo = $result_order->fetch_assoc();

  if ($orderInfo) {
    $orderDetailID = $orderInfo['OrderID'];
    $orderDateOriginal = $orderInfo['DateGeneration'];
    $orderDate = date('d/m/Y', strtotime($orderDateOriginal));
    $orderStatus = $orderInfo['Status'];
    $paymentMethod = $orderInfo['PaymentMethod'];
    $estimatedDeliveryDate = date('d/m/Y', strtotime($orderDateOriginal . ' + 4 days'));

    // 2. Lấy chi tiết đơn hàng (có thể có nhiều sản phẩm)
    $sql_details = "SELECT od.OrderID, od.ProductID, od.Quantity, od.UnitPrice, od.TotalPrice, 
                               p.ProductName, p.ImageURL
                        FROM orderdetails od
                        JOIN products p ON od.ProductID = p.ProductID
                        WHERE od.OrderID = ?";
    $stmt_details = $myconn->prepare($sql_details);
    $stmt_details->bind_param("i", $orderID);
    $stmt_details->execute();
    $result_details = $stmt_details->get_result();
    $orderDetails = [];
    while ($row = $result_details->fetch_assoc()) {
      $orderDetails[] = $row;
    }

    // 3. Lấy thông tin thanh toán
    $sql_payment =    "SELECT 
                        SUM(od.Quantity) AS TotalQuantity, o.TotalAmount
                        FROM orderdetails od
                        JOIN orders o ON od.OrderID = o.OrderID
                        JOIN users u ON o.Username= u.Username
                        WHERE od.OrderID = ?";
    $stmt_payment = $myconn->prepare($sql_payment);
    $stmt_payment->bind_param("i", $orderID);
    $stmt_payment->execute();
    $result_payment = $stmt_payment->get_result();
    $paymentInfo = $result_payment->fetch_assoc();

    $totalQuantity = $paymentInfo['TotalQuantity'];
    $totalProductAmount = $paymentInfo['TotalAmount'];
    $total = $totalProductAmount;

    // 4. Lấy thông tin người nhận từ bảng orders
    $sql_user = "SELECT
            u.FullName,
            o.CustomerName,
            o.Phone,
            o.Address,
            p.name as province_name,
            d.name as district_name,
            w.name as ward_name
        FROM orders o
        Join users u on u.Username=o.Username
        LEFT JOIN province p ON o.Province = p.province_id
        LEFT JOIN district d ON o.District = d.district_id
        LEFT JOIN wards w ON o.Ward = w.wards_id
        WHERE o.OrderID = ?";

    $stmt_user = $myconn->prepare($sql_user);
    $stmt_user->bind_param("i", $orderID);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $userInfo = $result_user->fetch_assoc();

    if ($userInfo) {
      $receiverName = $userInfo['CustomerName'];
      $receiverPhone = $userInfo['Phone'];
      $receiverAddress = $userInfo['Address'] . ', ' .
        $userInfo['ward_name'] . ', ' .
        $userInfo['district_name'] . ', ' .
        $userInfo['province_name'];
    } else {
      echo "Không tìm thấy thông tin người nhận";
      exit;
    }
  }
} else {
  echo "Không có mã đơn hàng";
  exit;
}


function getStatusInfo($status)
{
  switch ($status) {
    case 'execute':
      return [
        'text' => 'Chờ xác nhận',
        'class' => 'status-btn status-pending',
        'icon' => '<i class="fa-solid fa-spinner"></i>'
      ];
    case 'confirmed':
      return [
        'text' => 'Đã xác nhận',
        'class' => 'status-btn status-confirmed',
        'icon' => '<i class="fa-solid fa-circle-check"></i>'
      ];
    case 'ship':
      return [
        'text' => 'Đang giao hàng',
        'class' => 'status-btn status-shipping',
        'icon' => '<i class="fa-solid fa-truck"></i>'
      ];
    case 'success':
      return [
        'text' => 'Hoàn thành',
        'class' => 'status-btn status-completed',
        'icon' => '<i class="fa-solid fa-circle-check"></i>'
      ];
    case 'fail':
      return [
        'text' => 'Đã hủy',
        'class' => 'status-btn status-canceled',
        'icon' => '<i class="fa-solid fa-ban"></i>'
      ];
    default:
      return [
        'text' => 'Không xác định',
        'class' => 'status-btn status-unknown',
        'icon' => '<i class="fa-solid fa-question"></i>'
      ];
  }
}
function getPaymentStatusInfo($method)
{
  switch ($method) {
    case 'COD':
      return [
        'text' => 'Thanh toán khi nhận hàng',
        'class' => 'payment-cod',
        'icon' => '<i class="fa-solid fa-money-bill"></i>'
      ];
    case 'Banking':
      return [
        'text' => 'Chuyển khoản',
        'class' => 'payment-banking',
        'icon' => '<i class="fa-solid fa-building-columns"></i>'
      ];
    default:
      return [
        'text' => 'Chưa thanh toán',
        'class' => 'payment-pending',
        'icon' => '<i class="fa-solid fa-clock"></i>'
      ];
  }
}

function returnFinishPayment($method, $orderStatus)
{
  // Nếu đơn hàng đã hủy
  if ($orderStatus === 'fail') {
    return [
      'text' => 'Đơn hàng đã hủy',
      'class' => 'payment-status-canceled',
      'icon' => '<i class="fa-solid fa-ban"></i>',
      'showAmount' => false
    ];
  }

  // Xử lý theo phương thức thanh toán
  switch ($method) {
    case 'COD':
      if ($orderStatus === 'success') {
        return [
          'text' => 'Đã thanh toán COD',
          'class' => 'payment-status-completed',
          'icon' => '<i class="fa-solid fa-circle-check"></i>',
          'showAmount' => true
        ];
      } else {
        return [
          'text' => 'Chưa thanh toán (COD)',
          'class' => 'payment-status-pending',
          'icon' => '<i class="fa-solid fa-clock"></i>',
          'showAmount' => false
        ];
      }

    case 'Banking':
      return [
        'text' => 'Đã thanh toán (Chuyển khoản)',
        'class' => 'payment-status-completed',
        'icon' => '<i class="fa-solid fa-circle-check"></i>',
        'showAmount' => true
      ];
    default:
      return [
        'text' => 'Chưa xác định phương thức thanh toán',
        'class' => 'payment-status-unknown',
        'icon' => '<i class="fa-solid fa-question"></i>',
        'showAmount' => false
      ];
  }
}

$returnFinished = returnFinishPayment($paymentMethod, $orderStatus);
$statusInfo = getStatusInfo($orderStatus);
$paymentStatusInfo = getPaymentStatusInfo($paymentMethod);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <title>Đơn Hàng Số <?php echo $orderDetailID; ?></title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/order.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="../style/main1.css" rel="stylesheet">
  <link href="../style/orderDetail.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../style/LogInfo.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/responsiveOrder-detail.css">
  <style>
    /* Style cho bảng và container */
    .table-container {
      padding: 0;
      overflow-x: auto;
      max-height: 400px;
      overflow-y: auto;
    }

    .table-container table {
      width: 100%;
      border-collapse: collapse;
    }

    .table-container thead {
      position: sticky;
      top: 0;
      background-color: #f8f9fa;
      z-index: 1;
    }

    .table-container tbody {
      background-color: #fff;
    }

    .table-container::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }

    .table-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }

    .table-container::-webkit-scrollbar-thumb {
      background: var(--primary-color);
      border-radius: 10px;
    }

    .table-container::-webkit-scrollbar-thumb:hover {
      background: var(--secondary-color);
    }

    .table-container thead th {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    /* Animate scroll */
    .table-container {
      scroll-behavior: smooth;
    }

    /* Hover effect cho rows */
    .table-container tbody tr:hover {
      background-color: rgba(106, 161, 115, 0.05);
      transition: background-color 0.2s ease;
    }

    @media (max-width: 768px) {
      .table-container {
        max-height: 300px;
      }
    }

    .info-group {
      margin-bottom: 1rem;
      padding: 0.75rem;
      border-radius: 8px;
      background-color: #f8fafc;
      transition: all 0.2s ease;
    }

    .info-group:hover {
      background-color: #f1f5f9;
      transform: translateX(4px);
    }

    .info-label {
      color: #64748b;
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .info-label i {
      color: #6aa173;
      font-size: 1rem;
    }

    .info-value {
      color: #334155;
      font-weight: 500;
      font-size: 1rem;
      line-height: 1.5;
    }

    .shipping-details {
      padding: 1rem;
    }

    /* Style cho product image và name */
    .product-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .product-image {
      width: 50px;
      height: 50px;
      object-fit: cover;
    }

    .product-name {
      font-weight: 500;
    }

    .product-image {
      display: none;
    }
  </style>
</head>

<body>
  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <div class="header">
    <div class="index-menu">
      <i class="fa-solid fa-bars" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample"></i>
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
          <a href="../index/homePage.php" style="text-decoration: none; color: black;">
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
          <a href="../index/wareHouse.php" style="text-decoration: none; color: black;">
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
              <button class="button-function-selection" style="background-color: #6aa173; color: black;">
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

          <a href="accountPage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
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
      <p>Đơn số <?php echo $orderDetailID; ?></p>
    </div>
    <div class="header-middle-section">
      <img class="logo-store" src="../../assets/images/LOGO-2.jpg">
    </div>
    <div class="header-right-section">
      <div class="bell-notification">
        <i class="fa-regular fa-bell" style="
                        color: #64792c;
                        font-size: 45px;
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
        <img class="avatar" src="<?php echo $avatarPath ?>" alt="" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
      </div>
      <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
        aria-labelledby="offcanvasWithBothOptionsLabel">
        <div style=" border-bottom-width: 1px;
      border-bottom-style: solid;
      border-bottom-color: rgb(176, 176, 176);" class="offcanvas-header">
          <img class="avatar" src="<?php echo $avatarPath ?>" alt="">
          <div class="admin">
            <h4 class="offcanvas-title" id="offcanvasWithBothOptionsLabel"><?php echo $_SESSION['FullName'] ?></h4>
            <h5><?php echo $_SESSION['Username'] ?></h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="accountPage.php" class="navbar_user">
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

  <!-- Sidebar -->
  <div class="side-bar">
    <div class="backToHome">
      <a href="homePage.php" style="text-decoration: none; color: black;">
        <div class="backToHome">
          <button class="button-function-selection">
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
        <button class="button-function-selection" style="background-color: #6aa173;">
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

  <div class="content-wrapper">
    <div class="order-container">
      <div class="order-header">
        <div class="breadcrumb">
          <?php
          $source = isset($_GET['source']) ? $_GET['source'] : 'order';
          if ($source === 'analyze') {
            echo '<a href="analyzePage.php">Thống kê</a>';
          } else {
            echo '<a href="orderPage.php">Đơn hàng</a>';
          }
          ?>
          <span> Đơn số <?php echo $orderDetailID; ?></span>
        </div>
        <table class="status-bar">
          <thead>
            <tr>
              <th>MÃ ĐƠN HÀNG</th>
              <th>NGƯỜI ĐẶT</th>
              <th>NGÀY ĐẶT HÀNG</th>
              <th>Ngày giao (dự kiến)</th>
              <th>TRẠNG THÁI</th>
              <th>PHƯƠNG THỨC THANH TOÁN</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?php echo $orderDetailID; ?></td>
              <td><?php echo htmlspecialchars($userInfo['FullName']); ?></td>
              <td><?php echo $orderDate; ?></td>
              <td><?php echo $estimatedDeliveryDate; ?></td>
              <td class="<?php echo $statusInfo['class']; ?>">
                <?php echo $statusInfo['icon'] . ' ' . $statusInfo['text']; ?>
              </td>
              <td class="status paid"><?php echo $paymentStatusInfo['icon'] . ' ' . $paymentStatusInfo['text']; ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="main-content">
        <div class="left-section">
          <div class="section products">
            <div class="section-header">
              <span style="color:#21923c;"><i class="fa-regular fa-circle" style="margin-right: 5px;"></i>Chi tiết đơn hàng</span>
            </div>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>SẢN PHẨM</th>
                    <th style="text-align:center">SỐ LƯỢNG</th>
                    <th style="text-align:center">GIÁ (VND)</th>
                    <th style="text-align:center" class="hide-display">THÀNH TIỀN (VND)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($orderDetails)): ?>
                    <tr>
                      <td colspan="4">Không có sản phẩm nào trong đơn hàng này.</td>
                    </tr>
                  <?php else : ?>
                    <?php foreach ($orderDetails as $detail): ?>
                      <tr onclick="window.location='wareHouse.php?product_id=<?php echo $detail['ProductID']; ?>'" style="cursor: pointer;">
                        <td style="text-align:left">
                          <div class="product-container">
                            <img src="<?php echo '../..' . $detail['ImageURL']; ?>" alt="Product Image" class="product-image">
                            <span class="product-name">
                              <?php echo htmlspecialchars($detail['ProductName']); ?>
                            </span>
                          </div>
                        </td>
                        <td style="text-align:center"><?php echo $detail['Quantity']; ?></td>
                        <td style="text-align:center"><?php echo number_format($detail['UnitPrice'], 0, ',', '.') . ''; ?></td>
                        <td style="text-align:center" class="hide-display"><?php echo number_format($detail['TotalPrice'], 0, ',', '.') . ' '; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="section payment">
            <div class="section-header">
              <span>Thanh Toán: </span>
            </div>
            <div class="payment-details">
              <div class="payment-row">
                <span>Số lượng sản phẩm</span>
                <span><?php echo $totalQuantity; ?></span>
              </div>
              <div class="payment-row">
                <span>Tổng tiền hàng</span>
                <span><?php echo number_format($totalProductAmount, 0, ',', '.') . ' '; ?></span>
              </div>
              <div class="payment-row total">
                <span>Tổng giá trị đơn hàng</span>
                <span><?php echo number_format($total, 0, ',', '.') . ' '; ?></span>
              </div>

            </div>
          </div>
        </div>

        <div class="right-section">
          <div class="section shipping">
            <div class="section-header">
              <i class="fa-solid fa-truck-fast"></i>
              <span>Thông tin giao hàng</span>
            </div>
            <div class="shipping-details">
              <div class="info-group">
                <div class="info-label"><i class="fa-solid fa-user"></i> Người nhận:</div>
                <div class="info-value"><?php echo $receiverName; ?></div>
              </div>
              <div class="info-group">
                <div class="info-label"><i class="fa-solid fa-phone"></i> Số điện thoại:</div>
                <div class="info-value"><?php echo $receiverPhone; ?></div>
              </div>
              <div class="info-group">
                <div class="info-label"><i class="fa-solid fa-location-dot"></i> Địa chỉ:</div>
                <div class="info-value"><?php echo $receiverAddress; ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>