<?php
session_start();
require_once('../src/php/connect.php');
require_once('../src/php/token.php');
require_once('../src/php/check_token_v2.php');
require __DIR__ . '/../src/Jwt/vendor/autoload.php';
require_once('../src/php/check_status.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Kiểm tra xem cookie 'token' có tồn tại không
if (!isset($_COOKIE['token'])) {
  header("Location: login.php");
  exit;
}

try {
  // Giải mã token
  $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
  $username = $decoded->data->Username;
} catch (Exception $e) {
  // Nếu token không hợp lệ, hết hạn, hoặc bị chỉnh sửa => chuyển hướng login
  header("Location: login.php");
  exit;
}
$cart_count =  0;

if (isset($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['Quantity'];
  }
}
// Kiểm tra giỏ hàng
$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
// Lấy OrderID từ session
$orderID = $_SESSION['order_id'] ?? 0;
if (!$orderID) {
  header("Location: gio-hang.php");
  exit;
}
$errors = [];
// Lấy username từ session
$username = $_SESSION['username'] ?? '';

// Lấy thông tin đơn hàng và địa chỉ đầy đủ
$stmt = $conn->prepare("
  SELECT o.OrderID, o.DateGeneration, o.CustomerName, o.Phone, o.Address, 
         o.PaymentMethod, o.Status,
         p.name AS ProvinceName, d.name AS DistrictName, w.name AS WardName,
         o.TotalAmount
  FROM orders o
  LEFT JOIN province p ON o.Province = p.province_id  
  LEFT JOIN district d ON o.District = d.district_id
  LEFT JOIN wards w ON o.Ward = w.wards_id
  WHERE o.OrderID = ? AND o.Username = ?
");

$stmt->bind_param("is", $orderID, $username);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
  die("Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn hàng này.");
}

// Lấy chi tiết sản phẩm từ đơn hàng
$stmt = $conn->prepare("
  SELECT p.ProductName, p.ImageURL, od.Quantity, od.UnitPrice, (od.Quantity * od.UnitPrice) AS TotalPrice
  FROM orderdetails od
  JOIN products p ON od.ProductID = p.ProductID
  WHERE od.OrderID = ?
");
$stmt->bind_param("i", $orderID);
$stmt->execute();
$details = $stmt->get_result();
$stmt->close();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment-form'])) {

  if (isset($_POST['chon']) && $_POST['chon'] === 'default-information') {
    // Xử lý khi chọn thông tin mặc định
    $paymentMethod = $_POST['paymentMethod'] ?? 'COD';

    // Lấy dữ liệu từ session hoặc biến $user (như bạn đang làm)
    $username = $_SESSION['username'] ?? ''; // Giả sử có username trong session
    $fullName = $user['FullName'] ?? '';
    $phone = $user['Phone'] ?? '';
    $provinceID = $user['ProvinceID'] ?? null;
    $districtID = $user['DistrictID'] ?? null;
    $wardID = $user['WardID'] ?? null;
    $address = $user['Address'] ?? '';
    $dateNow = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại
    $total_amount = $_SESSION['total_amount'] ?? 0; // Giả sử có tổng tiền trong session

    // Insert vào bảng orders
    $stmt = $conn->prepare("
          INSERT INTO orders (Username, PaymentMethod, CustomerName, Phone, ProvinceID, DistrictID, WardID, DateGeneration, TotalAmount, Address, ProvinceName, DistrictName, WardName)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      ");

    // Lấy tên tỉnh, huyện, xã (tương tự như bạn đã làm ở phần thông tin mới)
    $provinceName = '';
    if ($provinceID) {
      $stmt_province = $conn->prepare("SELECT name FROM province WHERE province_id = ?");
      $stmt_province->bind_param("i", $provinceID);
      $stmt_province->execute();
      $stmt_province->bind_result($provinceName);
      $stmt_province->fetch();
      $stmt_province->close();
    }
    // Tương tự cho DistrictName và WardName

    $stmt->bind_param(
      "ssssiiisssss",
      $username,
      $paymentMethod,
      $fullName,
      $phone,
      $provinceID,
      $districtID,
      $wardID,
      $dateNow,
      $total_amount,
      $address,
      $provinceName,
      $districtName,
      $wardName
    );
    $stmt->execute();
    $orderID = $stmt->insert_id;
    $_SESSION['order_id'] = $orderID;
    $stmt->close();

    // Insert vào bảng orderdetails (như bạn đã làm)
    $stmt = $conn->prepare("INSERT INTO orderdetails (OrderID, ProductID, Quantity, UnitPrice, TotalPrice) VALUES (?, ?, ?, ?, ?)");
    foreach ($_SESSION['cart'] as $item) { // Giả sử giỏ hàng được lưu trong session
      $productID = $item['ProductID'];
      $quantity = $item['Quantity'];
      $unitPrice = $item['Price'];
      $totalPrice = $unitPrice * $quantity;
      $stmt->bind_param("iiidd", $orderID, $productID, $quantity, $unitPrice, $totalPrice);
      $stmt->execute();
    }
    $stmt->close();

    // Chuyển hướng hoặc thực hiện hành động sau khi đặt hàng thành công


  } elseif (isset($_POST['chon']) && $_POST['chon'] === 'new-information') {
    // Xử lý khi chọn thông tin mới
    $newName = trim($_POST['hidden-new-name']);
    $newSdt = trim($_POST['hidden-new-sdt']);
    $newDiachi = trim($_POST['hidden-new-diachi']);
    $provinceID = (int) $_POST['hidden-province'];
    $districtID = (int) $_POST['hidden-district'];
    $wardID = (int) $_POST['hidden-wards'];
    $paymentMethod = $_POST['paymentMethod'] ?? 'COD';
    $username = $_SESSION['username'] ?? ''; // Giả sử có username trong session
    $dateNow = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại
    $total_amount = $_SESSION['total_amount'] ?? 0; // Giả sử có tổng tiền trong session

    if (!empty($newName) && !empty($newSdt) && !empty($newDiachi) && $provinceID > 0 && $districtID > 0 && $wardID > 0) {
      // Lấy tên tỉnh, huyện, xã
      $provinceName = '';
      $stmt_province = $conn->prepare("SELECT name FROM province WHERE province_id = ?");
      $stmt_province->bind_param("i", $provinceID);
      $stmt_province->execute();
      $stmt_province->bind_result($provinceName);
      $stmt_province->fetch();
      $stmt_province->close();

      $districtName = '';
      $stmt_district = $conn->prepare("SELECT name FROM district WHERE district_id = ?");
      $stmt_district->bind_param("i", $districtID);
      $stmt_district->execute();
      $stmt_district->bind_result($districtName);
      $stmt_district->fetch();
      $stmt_district->close();

      $wardName = '';
      $stmt_ward = $conn->prepare("SELECT name FROM wards WHERE wards_id = ?");
      $stmt_ward->bind_param("i", $wardID);
      $stmt_ward->execute();
      $stmt_ward->bind_result($wardName);
      $stmt_ward->fetch();
      $stmt_ward->close();

      // Insert vào bảng orders
      $stmt = $conn->prepare("
              INSERT INTO orders (Username, PaymentMethod, CustomerName, Phone, ProvinceID, DistrictID, WardID, DateGeneration, TotalAmount, Address, ProvinceName, DistrictName, WardName)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
          ");
      $stmt->bind_param(
        "ssssiiisssss",
        $username,
        $paymentMethod,
        $newName,
        $newSdt,
        $provinceID,
        $districtID,
        $wardID,
        $dateNow,
        $total_amount,
        $newDiachi,
        $provinceName,
        $districtName,
        $wardName
      );
      if ($stmt->execute()) {
        echo "Dữ liệu mới đã được lưu vào cơ sở dữ liệu.";
      } else {
        echo "Lỗi khi lưu dữ liệu mới: " . $stmt->error;
      }
      $orderID = $stmt->insert_id;
      $_SESSION['order_id'] = $orderID;
      $stmt->close();

      // Insert vào bảng orderdetails (như bạn đã làm)
      $stmt = $conn->prepare("INSERT INTO orderdetails (OrderID, ProductID, Quantity, UnitPrice, TotalPrice) VALUES (?, ?, ?, ?, ?)");
      foreach ($_SESSION['cart'] as $item) { // Giả sử giỏ hàng được lưu trong session
        $productID = $item['ProductID'];
        $quantity = $item['Quantity'];
        $unitPrice = $item['Price'];
        $totalPrice = $unitPrice * $quantity;
        $stmt->bind_param("iiidd", $orderID, $productID, $quantity, $unitPrice, $totalPrice);
        $stmt->execute();
      }
      $stmt->close();


      header("Location: hoan-tat.php");
      exit();


      // Chuyển hướng hoặc thực hiện hành động sau khi đặt hàng thành công

    } else {
      // Xử lý lỗi nếu thông tin mới không đầy đủ
      echo "Vui lòng nhập đầy đủ thông tin mới.";
    }
  }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Lấy phương thức thanh toán từ form
  $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : 'COD';
  // Cập nhật phương thức thanh toán vào cơ sở dữ liệu (nếu cần)
  if (isset($_SESSION['order_id'])) {
    $orderID = $_SESSION['order_id'];

    $stmt = $conn->prepare("UPDATE orders SET PaymentMethod = ? WHERE OrderID = ?");
    $stmt->bind_param("si", $paymentMethod, $orderID);
    $stmt->execute();
    $stmt->close();
  }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Lấy dữ liệu từ form
  $fullname = $_POST['new_name'];
  $phone = $_POST['new_sdt'];

  if (!preg_match("/^[0-9]{10,11}$/", $phone)) {
    $errors['phone'] = "Số điện thoại không hợp lệ!";
  }

  if (!preg_match('/^([\p{L}]+(?:\s[\p{L}]+){0,79})$/u', $fullname)) {
    $errors['new_name'] = "Họ tên không hợp lệ! Chỉ được chứa chữ cái và tối đa 80 từ.";
  }
}
// Cập nhật giá & ẩn/sửa giỏ hàng theo database mới nhất
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
  $cart_product_ids = array_column($_SESSION['cart'], 'ProductID');
  $placeholders = implode(',', array_fill(0, count($cart_product_ids), '?'));
  // Lấy luôn Price và Status
  $sql = "SELECT ProductID, Price, Status 
          FROM products 
          WHERE ProductID IN ($placeholders)";
  $stmt = $conn->prepare($sql);
  if ($stmt) {
    $stmt->bind_param(str_repeat('i', count($cart_product_ids)), ...$cart_product_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $price_map  = [];
    $status_map = [];
    while ($row = $result->fetch_assoc()) {
      $price_map[$row['ProductID']]  = $row['Price'];
      $status_map[$row['ProductID']] = $row['Status'];
    }
    $stmt->close();

    // Duyệt session cart: nếu hidden ➔ unset; else ➔ cập nhật Price
    foreach ($_SESSION['cart'] as $key => $item) {
      $pid = $item['ProductID'];
      if (isset($status_map[$pid]) && $status_map[$pid] === 'hidden') {
        // xoá sản phẩm ẩn
        unset($_SESSION['cart'][$key]);
      } else if (isset($price_map[$pid])) {
        // cập nhật giá mới
        $_SESSION['cart'][$key]['Price'] = $price_map[$pid];
      }
    }
    // reset chỉ mục
    $_SESSION['cart'] = array_values($_SESSION['cart']);
  }
}

// Gián lại biến hiển thị và tính lại tổng
$cart_items = $_SESSION['cart'] ?? [];
$cart_count = count($cart_items);

// unset($_SESSION['cart']);
// setcookie('cart_quantity', '', time() - 3600, '/'); 

// Hiển thị thông tin chi tiết hóa đơn



?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- css  -->
  <link rel="stylesheet" href="../src/css/hoan-tat.css" />
  <link rel="stylesheet" href="../src/css/user-sanpham.css" />
  <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../assets/icon/fontawesome-free-6.7.2-web/css/all.min.css" />
  <link rel="stylesheet" href="../src/css/search-styles.css" />
  <link rel="stylesheet" href="../src/css/searchAdvanceMobile.css" />
  <link rel="stylesheet" href="../src/css/footer.css">
  <link rel="stylesheet" href="../src/css/brandname.css">
  <!-- JS  -->
  <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script src="../src/js/Trang_chu.js"></script>
  <!-- <script src="../src/js/main.js"></script> -->
  <script src="../src/js/search-common.js"></script>
  <script src="../src/js/onOffSeacrhAdvance.js"></script>
  <!-- <script src="../src/js/Hoa-Don.js"></script> -->
  <script src="../src/js/search-index.js"></script>
  <script src="../src/js/reloadPage.js"></script>
  <title>Hoàn tất đặt hàng</title>
</head>

<body>
  <div class="Sticky">
    <div class="container-fluid" style="padding: 0 !important">
      <!-- HEADER  -->
      <div class="header">
        <!-- MENU  -->
        <div class="grid">
          <div class="aaa"></div>
          <div class="item-header">
            <div class="search-group">
              <form id="searchForm" method="get">
                <div class="search-container">
                  <div class="search-input-wrapper">
                    <input type="search" placeholder="Tìm kiếm sản phẩm..." id="searchInput" name="search"
                      class="search-input" />
                    <button type="button" class="advanced-search-toggle" id="advanced-search-toggle"
                      onclick="toggleAdvancedSearch()" title="Tìm kiếm nâng cao">
                      <i class="fas fa-sliders-h"></i>
                    </button>
                    <button type="submit" class="search-button" onclick="performSearch()" title="Tìm kiếm">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>

                <!-- Form tìm kiếm nâng cao được thiết kế lại -->
                <div id="advancedSearchForm" class="advanced-search-panel" style="display: none">
                  <div class="advanced-search-header">
                    <h5>Tìm kiếm nâng cao</h5>
                    <button type="button" class="close-advanced-search" onclick="toggleAdvancedSearch()">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>

                  <!-- Panel tìm kiếm nâng cao  -->
                  <div class="search-filter-container" id="search-filter-container">
                    <div class="filter-group">
                      <label for="categoryFilter">
                        <i class="fas fa-leaf"></i> Phân loại sản phẩm
                      </label>
                      <select id="categoryFilter" name="category" class="form-select">
                        <option value="">Chọn phân loại</option>
                        <?php
                        require_once '../php-api/connectdb.php'; // Đường dẫn đúng tới file kết nối

                        $conn = connect_db();
                        $sql = "SELECT CategoryName FROM categories ORDER BY CategoryName ASC";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                          while ($row = $result->fetch_assoc()) {
                            $categoryName = htmlspecialchars($row['CategoryName']);
                            echo "<option value=\"$categoryName\">$categoryName</option>";
                          }
                        } else {
                          echo '<option value="">Không có phân loại</option>';
                        }

                        $conn->close();
                        ?>
                      </select>
                    </div>

                    <div class="filter-group">
                      <label for="priceRange">
                        <i class="fas fa-tag"></i> Khoảng giá
                      </label>
                      <div class="price-range-slider">
                        <div class="price-input-group">
                          <input type="number" id="minPrice" name="minPrice" placeholder="Từ" min="0" />
                          <span class="price-separator">-</span>
                          <input type="number" id="maxPrice" name="maxPrice" placeholder="Đến" min="0" />
                        </div>
                        <!-- <div class="price-ranges">
                          <button type="button" class="price-preset" onclick="setPrice(0, 200000)">
                            Dưới 200k
                          </button>
                          <button type="button" class="price-preset" onclick="setPrice(200000, 500000)">
                            200k - 500k
                          </button>
                          <button type="button" class="price-preset" onclick="setPrice(500000, 1000000)">
                            500k - 1tr
                          </button>
                          <button type="button" class="price-preset" onclick="setPrice(1000000, 0)">
                            Trên 1tr
                          </button>
                        </div> -->
                      </div>
                    </div>

                    <div class="filter-actions">
                      <button type="submit" class="btn-search" onclick="performSearch()">
                        <i class="fas fa-search"></i> Tìm kiếm
                      </button>
                      <button type="button" class="btn-reset" onclick="resetFilters()">
                        <i class="fas fa-redo-alt"></i> Đặt lại
                      </button>
                    </div>
                  </div>

                  <div class="search-tips">
                    <p>
                      <i class="fas fa-lightbulb"></i> Mẹo: Kết hợp nhiều điều
                      kiện để tìm kiếm chính xác hơn
                    </p>
                  </div>
                </div>
              </form>
            </div>

            <script>
              document.getElementById("searchForm").addEventListener("submit", function(e) {
                e.preventDefault(); // Ngăn chặn reload trang
                let searchInput = document.getElementById("searchInput").value;
                window.location.href = "search-result.php?q=" + encodeURIComponent(searchInput);
              });
            </script>
            <div class="cart-wrapper">
              <div class="cart-icon">
                <a href="gio-hang.php">
                  <img src="../assets/images/cart.svg" alt="cart" />
                  <span class="cart-count" id="mni-cart-count" style="position: absolute; margin-top: -10px; background-color: red; color: white; border-radius: 50%; padding: 2px 5px; font-size: 12px;">
                    <?php echo $cart_count; ?>
                  </span>
                </a>
              </div>
              <div class="cart-dropdown">
                <?php if (count($cart_items) > 0): ?>
                  <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                      <img src="<?php echo ".." . $item['ImageURL']; ?>" alt="<?php echo $item['ProductName']; ?>" class="cart-thumb" />
                      <div class="cart-item-details">
                        <h5><?php echo $item['ProductName']; ?></h5>
                        <p>Giá: <?php echo number_format($item['Price'], 0, ',', '.') . " VNĐ"; ?></p>
                        <p><?php echo $item['Quantity']; ?> × <?php echo number_format($item['Price'], 0, ',', '.'); ?>VNĐ</p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p>Giỏ hàng của bạn đang trống.</p>
                <?php endif; ?>
              </div>
            </div>
            <script src="../src/js/AnSanPham.js"></script>
            <div class="user-icon">
              <label for="tick" style="cursor: pointer">
                <img src="../assets/images/user.svg" alt="" />
              </label>
              <input id="tick" hidden type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
                aria-controls="offcanvasExample" />
              <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample"
                aria-labelledby="offcanvasExampleLabel">
                <div class="offcanvas-header">
                  <h5 class="offcanvas-title" id="offcanvasExampleLabel">
                    <?= $loggedInUsername ? "Xin chào, " . htmlspecialchars($loggedInUsername) : "Xin vui lòng đăng nhập" ?>
                  </h5>
                  <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                  <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                    <?php if (!$loggedInUsername): ?>
                      <li class="nav-item">
                        <a class="nav-link login-logout" href="user-register.php">Đăng ký</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link login-logout" href="user-login.php">Đăng nhập</a>
                      </li>
                    <?php else: ?>
                      <li class="nav-item">
                        <a class="nav-link hs-ls-dx" href="ho-so.php">Hồ sơ</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link hs-ls-dx" href="user-History.php">Lịch sử mua hàng</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link hs-ls-dx" href="../src/php/logout.php">Đăng xuất</a>
                      </li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- BAR  -->
          <nav class="navbar position-absolute">
            <div class="a">
              <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                  <h5 class="offcanvas-title" id="offcanvasNavbarLabel">
                    THEE TREE
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body offcanvas-fullscreen mt-20">
                  <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                    <li class="nav-item">
                      <a class="nav-link active" aria-current="page" href="../index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#">Giới thiệu</a>
                    </li>
                    <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Sản phẩm
                      </a>
                      <ul class="dropdown-menu">
                        <?php
                        require_once '../php-api/connectdb.php'; // hoặc đường dẫn đúng đến file connect của bạn
                        $conn = connect_db();

                        $sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryID ASC";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                          while ($row = $result->fetch_assoc()) {
                            $categoryID = htmlspecialchars($row['CategoryID']);
                            $categoryName = htmlspecialchars($row['CategoryName']);
                            echo "<li><a class='dropdown-item' href='./phan-loai.php?category_id=$categoryID'>$categoryName</a></li>";
                          }
                        } else {
                          echo "<li><span class='dropdown-item text-muted'>Không có danh mục</span></li>";
                        }

                        $conn->close();
                        ?>
                      </ul>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#">Tin tức</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#">Liên hệ</a>
                    </li>
                  </ul>
                  <form class="searchFormMobile mt-3" role="search" id="searchFormMobile">
                    <div class="d-flex">
                      <input class="form-control me-2" type="search" placeholder="Tìm kiếm" aria-label="Search"
                        style="height: 37.6px;" />
                      <!-- Nút tìm kiếm nâng cao trên mobile  -->
                      <button type="button" class="advanced-search-toggle" onclick="toggleMobileSearch()"
                        title="Tìm kiếm nâng cao">
                        <i class="fas fa-sliders-h"></i>
                      </button>

                      <button class="btn btn-outline-success" type="submit"
                        style="width: 76.3px;display: flex;justify-content: center;align-items: center;height: 37.6px;">
                        Tìm
                      </button>
                    </div>
                    <div id="search-filter-container-mobile" class="search-filter-container-mobile">
                      <div class="filter-group">
                        <label for="categoryFilter-mobile">
                          <i class="fas fa-leaf"></i> Phân loại sản phẩm
                        </label>
                        <select id="categoryFilter-mobile" name="category" class="form-select">
                          <option value="">Chọn phân loại</option>
                          <?php
                          require_once '../php-api/connectdb.php'; // Đường dẫn đúng tới file kết nối

                          $conn = connect_db();
                          $sql = "SELECT CategoryName FROM categories ORDER BY CategoryName ASC";
                          $result = $conn->query($sql);

                          if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                              $categoryName = htmlspecialchars($row['CategoryName']);
                              echo "<option value=\"$categoryName\">$categoryName</option>";
                            }
                          } else {
                            echo '<option value="">Không có phân loại</option>';
                          }

                          $conn->close();
                          ?>
                        </select>
                      </div>

                      <div class="filter-group">
                        <label for="priceRange">
                          <i class="fas fa-tag"></i> Khoảng giá
                        </label>
                        <div class="price-range-slider">
                          <div class="price-input-group">
                            <input type="number" id="minPriceMobile" name="minPrice" placeholder="Từ" min="0" />
                            <span class="price-separator">-</span>
                            <input type="number" id="maxPriceMobile" name="maxPrice" placeholder="Đến" min="0" />
                          </div>
                          <!-- <div class="price-ranges">
                          <button type="button" class="price-preset" onclick="setPriceMobile(0, 200000)">
                            Dưới 200k
                          </button>
                          <button type="button" class="price-preset" onclick="setPriceMobile(200000, 500000)">
                            200k - 500k
                          </button>
                          <button type="button" class="price-preset" onclick="setPriceMobile(500000, 1000000)">
                            500k - 1tr
                          </button>
                          <button type="button" class="price-preset" onclick="setPriceMobile(1000000, 0)">
                            Trên 1tr
                          </button>
                        </div> -->
                        </div>
                      </div>

                      <div class="filter-actions">
                        <button type="submit" class="btn-search" onclick="performSearchMobile()">
                          <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <button type="button" class="btn-reset" onclick="resetMobileFilters()">
                          <i class="fas fa-redo-alt"></i> Đặt lại
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </nav>
        </div>
      </div>

      <!-- NAV  -->
      <div class="nav">
        <div class="brand">
          <div class="brand-logo">
            <!-- Quay về trang chủ  -->
            <a href="../index.php"><img class="img-fluid" src="../assets/images/LOGO-2.jpg" alt="LOGO" /></a>
          </div>
          <div class="brand-name">THE TREE</div>
        </div>
        <div class="choose">
          <ul>
            <li>
              <a href="../index.php" style="font-weight: bold">Trang chủ</a>
            </li>
            <li><a href="#">Giới thiệu</a></li>
            <li>
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  Sản phẩm
                </a>
                <ul class="dropdown-menu">
                  <?php
                  require_once '../php-api/connectdb.php'; // hoặc đường dẫn đúng đến file connect của bạn
                  $conn = connect_db();

                  $sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryID ASC";
                  $result = $conn->query($sql);

                  if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      $categoryID = htmlspecialchars($row['CategoryID']);
                      $categoryName = htmlspecialchars($row['CategoryName']);
                      echo "<li><a class='dropdown-item' href='./phan-loai.php?category_id=$categoryID'>$categoryName</a></li>";
                    }
                  } else {
                    echo "<li><span class='dropdown-item text-muted'>Không có danh mục</span></li>";
                  }

                  $conn->close();
                  ?>
                </ul>
              </div>
            </li>
            <li><a href="">Tin tức</a></li>
            <li><a href="">Liên hệ</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <!-- SECTION  -->
  <div class="section">
    <div class="img-21">
      <!-- <img src="../assets/images/CAY21.jpg" alt="CAY21"> -->
    </div>
  </div>

  <section>
    <div class="loca">
      <a href="../index.php">
        <span>Trang chủ</span>
      </a>
      <span>></span>
      <a href="#"><span>Hoàn tất thanh toán</span></a>
    </div>

    <style>
      .loca {
        padding: 20px;
        margin: 20px 0;
        font-size: 16px;
        background-color: #f9f9f9;
      }

      .loca a {
        text-decoration: none;
        color: #666;
        transition: color 0.3s ease;
      }

      .loca a:hover {
        color: rgb(59, 161, 59);
      }

      .loca span {
        margin: 0 10px;
        color: #666;
        font-weight: bold;
      }

      /* Responsive cho mobile */
      @media (max-width: 768px) {
        .loca {
          padding: 10px;
          font-size: 14px;
        }

        .loca span {
          margin: 0 5px;
        }
      }
    </style>
  </section>

  <!-- ARTICLE -->
  <div class="article">
    <div class="title-cart">
      <p class="text-success h1 text-center text-uppercase">Hoàn tất</p>
    </div>

    <div class="infor-order bg-light">
      <div class="status-order">
        <img class="cart-2" src="../assets/images/cart.svg" alt="cart" />
        <hr />
        <img src="../assets/images/id-card.svg" class="id-01" alt="id-card" />
        <hr />
        <img src="../assets/images/circle-check.svg" class="id-02" alt="ccheck" />
      </div>

      <div class="noti-order-success">
        <p class="text-uppercase fw-bold w-100 text-center">
          bạn đã đặt hàng thành công
        </p>
      </div>

      <div class="noti-thanks">
        <p class="fs-4">
          THE TREE xin cảm ơn các bạn đã ủng hộ chúng tôi trong suốt thời gian
          qua.
        </p>
      </div>

      <div class="invoice-container">
        <h2>HÓA ĐƠN MUA HÀNG</h2>
        <?php
        // Hiển thị thông tin đơn hàng từ database
        if ($order): ?>
          <p><strong>Mã hóa đơn:</strong> <?= htmlspecialchars($order['OrderID']) ?></p>
          <p><strong>Ngày đặt hàng:</strong> <?= htmlspecialchars($order['DateGeneration']) ?></p>
          <p><strong>Tên khách hàng:</strong> <?= htmlspecialchars($order['CustomerName']) ?></p>
          <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['Phone']) ?></p>
          <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['Address']) ?>,
            <?= htmlspecialchars($order['WardName']) ?>,
            <?= htmlspecialchars($order['DistrictName']) ?>,
            <?= htmlspecialchars($order['ProvinceName']) ?>
          </p>
          <p><strong>Phương thức thanh toán:</strong>
            <?php
            if ($order['PaymentMethod'] === 'COD') {
              echo 'Thanh toán khi nhận hàng';
            } else if ($order['PaymentMethod'] === 'Banking') {
              echo 'Chuyển khoản';
            } else {
              echo htmlspecialchars($order['PaymentMethod']);
            }
            ?>
          </p>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Sản phẩm</th>
                  <th>Hình ảnh</th>
                  <th id="solg">Số lượng</th>
                  <th id="gia">Giá</th>
                  <th>Thành tiền</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($details && $details->num_rows > 0):
                  while ($row = $details->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['ProductName']) ?></td>
                      <td><img src="<?= ".." . htmlspecialchars($row['ImageURL']) ?>" alt="<?= htmlspecialchars($row['ProductName']) ?>" width="80"></td>
                      <td id="solg-02"><?= $row['Quantity'] ?></td>
                      <td id="gia-02"><?= number_format($row['UnitPrice'], 0, ',', '.') ?>đ</td>
                      <td><?= number_format($row['TotalPrice'], 0, ',', '.') ?>đ</td>
                    </tr>
                <?php endwhile;
                endif; ?>
              </tbody>
            </table>
          </div>
          <div class="total" style="color: red; font-size: 23px;">
            <strong>Tổng cộng: </strong> <?= number_format($order['TotalAmount'], 0, ',', '.') ?>đ
          </div>
        <?php else: ?>
          <p>Không tìm thấy thông tin đơn hàng.</p>
        <?php endif; ?>
      </div>

      <div class="continue-shopping">
        <a href="../index.php"><button class=" btn btn-success" style="margin: 17px 0; height: 43px;">Tiếp tục mua hàng</button></a>
      </div>

    </div>
  </div>
  <style>

  </style>
  <!-- FOOTER  -->
  <footer class=" footer">
    <div class="footer-column">
      <h3>The Tree</h3>
      <ul>
        <li><a href="#">Cây dễ chăm</a></li>
        <li><a href="#">Cây văn phòng</a></li>
        <li><a href="#">Cây dưới nước</a></li>
        <li><a href="#">Cây để bàn</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h3>Khám phá</h3>
      <ul>
        <li><a href="#">Cách chăm sóc cây</a></li>
        <li><a href="#">Lợi ích của cây xanh</a></li>
        <li><a href="#">Cây phong thủy</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h3>Khám phá thêm từ The Tree</h3>
      <ul>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Cộng tác viên</a></li>
        <li><a href="#">Liên hệ</a></li>
        <li><a href="#">Câu hỏi thường gặp</a></li>
        <li><a href="#">Đăng nhập</a></li>
      </ul>

    </div>

    <div class="footer-column newsletter">


      <h3>Theo dõi chúng tôi</h3>
      <div class="social-icons">
        <a href="#" aria-label="Pinterest">
          <i class="fa-brands fa-pinterest"></i>
        </a>
        <a href="#" aria-label="Facebook">
          <i class="fa-brands fa-facebook"></i>
        </a>
        <a href="#" aria-label="Instagram">
          <i class="fa-brands fa-instagram"></i>
        </a>
        <a href="#" aria-label="Twitter">
          <i class="fa-brands fa-x-twitter"></i>
        </a>
      </div>
    </div>

    <div class="copyright">
      © 2021 c01.nhahodau

      <div class="policies">
        <a href="#">Điều khoản dịch vụ</a>
        <span>|</span>
        <a href="#">Chính sách bảo mật</a>
        <span>|</span>
        <a href="#">Chính sách hoàn tiền</a>
        <span>|</span>
        <a href="#">Chính sách trợ năng</a>
      </div>
    </div>
    <!-- xong footer  -->
  </footer>
</body>

</html>