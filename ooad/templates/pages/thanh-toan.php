<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../src/php/connect.php');
require_once('../src/php/token.php');
require_once('../src/php/check_token_v2.php');
require __DIR__ . '/../src/Jwt/vendor/autoload.php';
require_once('../src/php/check_status.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra token
if (!isset($_COOKIE['token'])) {
  header("Location: login.php");
  exit;
}

try {
  $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
  $username = $decoded->data->Username;
  $_SESSION['username'] = $username;
} catch (Exception $e) {
  header("Location: login.php");
  exit;
}

// Hàm kiểm tra giỏ hàng có trống không
function isCartEmpty()
{
  // Kiểm tra session giỏ hàng
  if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    return true;
  }
  return false;
}

if (isCartEmpty()) {
  // Chuyển hướng về trang giỏ hàng
  header("Location: gio-hang.php");
  exit;
}

$user = null;
// Lấy thông tin user (gồm JOIN với province, district, ward)
if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];

  $sql_user = "
        SELECT 
            u.Username,
            u.FullName,
            u.Email,
            u.Phone,
            u.Address,
            p.name AS Province,
            d.name AS District,
            w.name AS Ward,
            u.Province AS ProvinceID,
            u.District AS DistrictID,
            u.Ward AS WardID
        FROM users u
        LEFT JOIN province p ON u.Province = p.province_id
        LEFT JOIN district d ON u.District = d.district_id
        LEFT JOIN wards w ON u.Ward = w.wards_id
        WHERE u.Username = ?
    ";

  $stmt = $conn->prepare($sql_user);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();
}
$cart_count =  0;

if (isset($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['Quantity'];
  }
}
// Kiểm tra giỏ hàng
$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Tính tổng
$total_amount = 0;
foreach ($cart_items as $item) {
  $total_amount += $item['Price'] * $item['Quantity'];
}
$total_price_formatted = number_format($total_amount, 0, ',', '.') . " VNĐ";

// Ngày hiện tại
$dateNow = date('Y-m-d H:i:s');

// Debug toàn bộ dữ liệu POST để kiểm tra


// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paymentMethod'])) {
  try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    $paymentMethod = $_POST['paymentMethod'] ?? 'COD';

    // Kiểm tra xem người dùng đang sử dụng thông tin mặc định hay thông tin mới
    if (isset($_POST['default-information']) && $_POST['default-information'] === 'true') {
      // Sử dụng thông tin mặc định từ bảng users
      $customerName = $user['FullName'];
      $phone = $user['Phone'];
      $address = $user['Address'];
      $provinceID = $user['ProvinceID'];
      $districtID = $user['DistrictID'];
      $wardID = $user['WardID'];
    } else {
      // Lấy thông tin mới từ form
      $customerName = isset($_POST['new_name']) ? trim($_POST['new_name']) : '';
      $phone = isset($_POST['new_sdt']) ? trim($_POST['new_sdt']) : '';
      $address = isset($_POST['new_diachi']) ? trim($_POST['new_diachi']) : '';
      $provinceID = isset($_POST['province']) ? (int)$_POST['province'] : 0;
      $districtID = isset($_POST['district']) ? (int)$_POST['district'] : 0;
      $wardID = isset($_POST['wards']) ? (int)$_POST['wards'] : 0;

      if (
        empty($customerName) || empty($phone) || empty($address) ||
        $provinceID <= 0 || $districtID <= 0 || $wardID <= 0
      ) {
        throw new Exception("Thông tin không hợp lệ. Vui lòng kiểm tra lại.");
      }
    }

    // Thêm đơn hàng mới vào bảng orders
    $status = 'execute';
    $stmt = $conn->prepare("INSERT INTO orders (Username, PaymentMethod, CustomerName, Phone, Province, District, Ward, DateGeneration, TotalAmount, Address, Status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
      "ssssiiisdss",
      $username,
      $paymentMethod,
      $customerName,
      $phone,
      $provinceID,
      $districtID,
      $wardID,
      $dateNow,
      $total_amount,
      $address,
      $status
    );

    if (!$stmt->execute()) {
      throw new Exception("Lỗi khi tạo đơn hàng: " . $stmt->error);
    }

    $orderID = $stmt->insert_id;
    $_SESSION['order_id'] = $orderID;
    $stmt->close();

    // Thêm chi tiết đơn hàng vào bảng orderdetails
    $stmt = $conn->prepare("INSERT INTO orderdetails (OrderID, ProductID, Quantity, UnitPrice, TotalPrice) VALUES (?, ?, ?, ?, ?)");

    if (!$stmt) {
      throw new Exception("Lỗi chuẩn bị câu lệnh chi tiết đơn hàng: " . $conn->error);
    }

    foreach ($cart_items as $item) {
      $productID = $item['ProductID'];
      $quantity = $item['Quantity'];
      $unitPrice = $item['Price'];
      $totalPrice = $unitPrice * $quantity;

      $stmt->bind_param("iiidd", $orderID, $productID, $quantity, $unitPrice, $totalPrice);

      if (!$stmt->execute()) {
        throw new Exception("Lỗi khi thêm sản phẩm vào đơn hàng: " . $stmt->error);
      }
    }
    $stmt->close();

    // Commit transaction nếu mọi thứ thành công
    $conn->commit();

    // Xóa giỏ hàng sau khi đặt hàng thành công
    unset($_SESSION['cart']);

    // Chuyển hướng đến trang hoàn tất
    header("Location: hoan-tat.php");
    exit;
  } catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    echo "<script>alert('Có lỗi xảy ra: " . $e->getMessage() . "');</script>";
  }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
  $product_id_to_remove = $_POST['remove_product_id'];
  if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
      if ($item['ProductID'] == $product_id_to_remove) {
        unset($_SESSION['cart'][$key]);
        break;
      }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
  }
  echo json_encode(['status' => 'success']);
  exit();
}

// **CHỈ CHUYỂN HƯỚNG NẾU KHÔNG PHẢI LÀ YÊU CẦU AJAX XÓA SẢN PHẨM**
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['remove_product_id'])) {
  if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location:thanh-toan.php');
    exit();
  }
}

// Cập nhật giá sản phẩm và Loại bỏ sản phẩm  theo database mới nhất
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
  $cart_product_ids = array_column($_SESSION['cart'], 'ProductID');

  $placeholders = implode(',', array_fill(0, count($cart_product_ids), '?'));
  $sql = "SELECT ProductID, Price FROM products WHERE ProductID IN ($placeholders)";
  $stmt = $conn->prepare($sql);

  if ($stmt) {
    $stmt->bind_param(str_repeat('i', count($cart_product_ids)), ...$cart_product_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    // Tạo một mảng [ProductID => Price]
    $price_map = [];
    while ($row = $result->fetch_assoc()) {
      $price_map[$row['ProductID']] = $row['Price'];
    }

    // Cập nhật lại giá trong giỏ hàng
    foreach ($_SESSION['cart'] as $key => $item) {
      $pid = $item['ProductID'];
      if (isset($price_map[$pid])) {
        $_SESSION['cart'][$key]['Price'] = $price_map[$pid];
      }
    }

    $stmt->close();
  }
}
// Gián lại biến hiển thị
$cart_items = $_SESSION['cart'] ?? [];
$cart_count = count($cart_items);

// Tính tổng SAU khi đã cập nhật giá
$total_amount = 0;
foreach ($cart_items as $item) {
  $total_amount += $item['Price'] * $item['Quantity'];
}
$total_price_formatted = number_format($total_amount, 0, ',', '.') . " VNĐ";
?>
<!DOCTYPE html>
<html>
<!-- Sửa infor-for-banking ở dòng 584  -->

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- CSS  -->
  <link rel="stylesheet" href="../src/css/thanh-toan-php.css" />
  <link rel="stylesheet" href="../src/css/thanh-toan.css" />
  <link rel="stylesheet" href="../src/css/user-sanpham.css" />
  <link rel="stylesheet" href="../assets/icon/fontawesome-free-6.7.2-web/css/all.min.css" />
  <link rel="stylesheet" href="../src/css/search-styles.css" />
  <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../src/css/searchAdvanceMobile.css" />
  <link rel="stylesheet" href="../src/css/footer.css">
  <link rel="stylesheet" href="../src/css/brandname.css">
  <!-- JS  -->
  <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script src="../src/js/main.js"></script> -->
  <script src="../src/js/search-common.js"></script>
  <script src="../src/js/onOffSeacrhAdvance.js"></script>
  <script src="../src/js/thanh-toan.js"></script>
  <script src="../src/js/search-index.js"></script>
  <script src="../src/js/gio-hang.js"></script>
  <script src="../src/js/PhuongThucChuyenKhoan.js"></script>

  <script src="../src/js/jquery-3.7.1.min.js"></script>

  <script src="../src/js/reloadPage.js"></script>

  <title>Hoàn tất thanh toán</title>
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

            <div class="cart-wrapper">
              <div class="cart-icon">
                <a href="gio-hang.php"><img src="../assets/images/cart.svg" alt="cart" />
                  <span class="cart-count" id="mni-cart-count" style="position: absolute; margin-top: -10px; background-color: red; color: white; border-radius: 50%; padding: 2px 5px; font-size: 12px;">
                    <?php
                    echo $cart_count;
                    ?>
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
                <!-- form tìm kiếm trên mobile  -->
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
      <a href="#"><span>Thanh toán</span></a>
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

  <main>
    <div class="container-payment">
      <h2>THANH TOÁN</h2>
      <div class="content">
        <div class="status-order">
          <i class="fa-solid fa-cart-shopping"></i>
          <hr style="border: 1px dashed black; width: 21%;">
          <i style="color: green;" class="fa-solid fa-id-card"></i>
          <hr style="border: 1px dashed black; width: 21%;">
          <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="option-address">
          <label for="default-information" style="cursor: pointer">
            <input type="radio" name="chon" id="default-information" style="cursor: pointer" checked> <span>Sử dụng thông tin mặc
              định</span>
          </label>
          <label for="new-information" style="cursor: pointer">
            <input type="radio" name="chon" id="new-information" style="cursor: pointer"> <span>Nhập thông tin mới</span>
          </label>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            // Lấy các phần tử radio button
            const defaultInformationRadio = document.getElementById('default-information');
            const newInformationRadio = document.getElementById('new-information');
            const defaultInformationForm = document.getElementById('default-information-form');
            const newInformationForm = document.getElementById('new-information-form');

            // Hàm để ẩn/hiện form
            function toggleForms() {
              if (defaultInformationRadio.checked) {
                defaultInformationForm.style.display = 'block';
                newInformationForm.style.display = 'none';
              } else if (newInformationRadio.checked) {
                defaultInformationForm.style.display = 'none';
                newInformationForm.style.display = 'block';
              }
            }

            // Khi người dùng thay đổi lựa chọn radio
            defaultInformationRadio.addEventListener('change', toggleForms);
            newInformationRadio.addEventListener('change', toggleForms);

            // Gọi hàm toggleForms khi trang được tải lên để xác định trạng thái form ban đầu
            toggleForms();
          });
        </script>
        <div id="default-information-form">
          <label><strong>Họ và tên</strong></label>
          <input type="text" value="<?= htmlspecialchars($user['FullName'] ?? '') ?>" disabled>
          <input type="hidden" name="FullName" value="<?= htmlspecialchars($user['FullName'] ?? '') ?>">


          <label><strong>Email</strong></label>
          <input type="email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" disabled>
          <input type="hidden" name="Email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>">

          <label><strong>Số điện thoại</strong></label>
          <input type="text" value="<?= htmlspecialchars($user['Phone'] ?? '') ?>" disabled>
          <input type="hidden" name="Phone" value="<?= htmlspecialchars($user['Phone'] ?? '') ?>">


          <label><strong>Địa chỉ</strong></label>
          <input type="text" value="<?= htmlspecialchars(($user['Address'] ?? '') . ', ' . ($user['Ward'] ?? '') . ', ' . ($user['District'] ?? '') . ', ' . ($user['Province'] ?? '')) ?>" disabled>
          <input type="hidden" name="Address" value="<?= htmlspecialchars($user['Address'] ?? '') ?>">
          <input type="hidden" name="Ward" value="<?= htmlspecialchars($user['Ward'] ?? '') ?>">
          <input type="hidden" name="District" value="<?= htmlspecialchars($user['District'] ?? '') ?>">
          <input type="hidden" name="Province" value="<?= htmlspecialchars($user['Province'] ?? '') ?>">
        </div>



        <form action="thanh-toan.php" id="new-information-form" method="POST">
          <input type="hidden" name="order_id" value="<?php echo $_SESSION['order_id'] ?? ''; ?>">

          <label for=""><strong>Họ và tên</strong></label>
          <input type="text" name="new_name" id="new-name" placeholder="Họ và tên" required>

          <label for=""><strong>Số điện thoại</strong></label>
          <input type="text" name="new_sdt" id="new-sdt" placeholder="Số điện thoại" required>

          <label for=""><strong>Địa chỉ</strong></label>
          <input type="text" name="new_diachi" id="new-diachi" placeholder="Nhập địa chỉ (số và đường)" required>

          <label for=""><strong>Tỉnh/Thành phố</strong></label>
          <select name="province" id="province" class="form-select">
            <option value="">Chọn tỉnh/thành phố</option>
            <?php
            require_once '../php-api/connectdb.php';
            $conn = connect_db();
            // Lấy danh sách tỉnh từ cơ sở dữ liệu
            $stmt = $conn->prepare("SELECT province_id, name FROM province");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
              echo '<option value="' . $row['province_id'] . '">' . htmlspecialchars($row['name']) . '</option>';
            }
            $stmt->close();
            ?>
          </select>

          <label for=""><strong>Quận/Huyện</strong></label>
          <select name="district" id="district" class="form-select">
            <option value="">Chọn quận/huyện</option>

          </select>

          <label for=""><strong>Phường/Xã</strong></label>
          <select name="wards" id="wards" class="form-select">
            <option value="">Chọn phường/xã</option>
          </select>
          <script src="../src/js/DiaChi.js"></script>
        </form>




        <div class="infor-goods">
          <hr style="border: 3px dashed green; width: 100%" />
          <?php if (count($cart_items) > 0): ?>
            <?php foreach ($cart_items as $item): ?>
              <div class="order">
                <div class="order-img">
                  <img src="<?php echo ".." . $item['ImageURL']; ?>" alt="<?php echo $product['ProductName']; ?>" />
                </div>
                <div class="frame">
                  <div class="name-price">
                    <p><strong><?php echo htmlspecialchars($item['ProductName']); ?></strong></p>
                    <p class="price" data-price="<?php echo $item['Price']; ?>">
                      <strong><?php echo number_format($item['Price'], 0, ',', '.') . " VNĐ"; ?></strong>
                    </p>
                  </div>
                  <div class="function">
                    <!-- Button trigger modal -->
                    <form onsubmit="event.preventDefault(); xoaSanPham(<?php echo $item['ProductID']; ?>);">
                      <input type="hidden" name="remove_product_id" value="<?php echo $item['ProductID']; ?>">
                      <button type="button" class="btn" style="width: 53px; height: 33px;" onclick="xoaSanPham(<?php echo $item['ProductID']; ?>)">
                        <i class="fa-solid fa-trash" style="font-size: 25px;"></i>
                      </button>
                    </form>

                    <script>
                      function xoaSanPham(productId) {
                        if (!confirm('Bạn có chắc chắn muốn xoá sản phẩm này khỏi giỏ hàng?')) return;

                        fetch('thanh-toan.php', {
                            method: 'POST',
                            headers: {
                              'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'remove_product_id=' + encodeURIComponent(productId)
                          })
                          .then(response => {
                            if (!response.ok) {
                              throw new Error('Network response was not ok');
                            }
                            return response.json();
                          })
                          .then(data => {
                            if (data.status === 'success') {
                              // Reload lại trang thanh-toan.php
                              window.location.reload();
                            } else {
                              alert('Xoá sản phẩm thất bại!');
                            }
                          })
                          .catch(err => {
                            console.error('Lỗi khi xoá sản phẩm:', err);
                            alert('Đã xảy ra lỗi khi xoá sản phẩm.');
                          });
                      }
                    </script>
                    <!-- Nútxóa và thêm số lượng sản phẩm  -->
                    <div class="add-del">
                      <div class="oder">
                        <div class="wrapper">
                          <form action="gio-hang.php" method="POST" class="update-form">
                            <!-- Truyền ProductID để xác định sản phẩm cần cập nhật -->
                            <input type="hidden" name="update_product_id" value="<?php echo $item['ProductID']; ?>">
                            <!-- Nút giảm số lượng -->
                            <!-- <button type="button" class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>                       -->
                            <!-- Trường số lượng, gán thuộc tính data-price để JS dùng cho tính toán nếu cần -->
                            <span class="quantity-display " style="margin-left:35px"><?php echo "x" . $item['Quantity']; ?></span>

                            <!-- Nút tăng số lượng -->
                            <!-- <button type="button" class="quantity-btn" onclick="changeQuantity(this, 1)">+</button> -->
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else:  ?>
            <p>Giỏ hàng của bạn đang trống</p>
          <?php endif; ?>
          <div class="frame-2">
            <div class="thanh-tien">
              Tổng : <span id="total-price"><?php echo $total_price_formatted; ?></span>
            </div>
            <hr style="border: 3px dashed green; width: 100%" />
          </div>

          <!-- Sửa action trỏ về chính file thanh-toan.php -->
          <form action="thanh-toan.php" method="POST" id="payment-form" onsubmit="return validateForm()">
            <div class="payment-method">
              <input type="hidden" name="default-information" id="use-default-info" value="">
              <input type="hidden" name="new_name" id="hidden-new-name">
              <input type="hidden" name="new_sdt" id="hidden-new-sdt">
              <input type="hidden" name="new_diachi" id="hidden-new-diachi">
              <input type="hidden" name="province" id="hidden-province">
              <input type="hidden" name="district" id="hidden-district">
              <input type="hidden" name="wards" id="hidden-wards">
              <label>
                <input type="radio" name="paymentMethod" value="COD" checked onchange="toggleBankingForm()" style="cursor: pointer">
                <span style="cursor: pointer">Thanh toán khi nhận hàng</span>
              </label>
              <label>
                <input type="radio" name="paymentMethod" value="Banking" onchange="toggleBankingForm()" style="cursor: pointer">
                <span style="cursor: pointer">Chuyển khoản</span>
              </label>
            </div>

            <!-- Form chuyển khoản -->
            <style>
              /* #banking-form p:nth-child (2) */
            </style>
            <div id="banking-form" style="display: none;">
              <p><span style="font-weight: bold;">Số tài khoản:</span> 1028974123</p>
              <p><span style="font-weight: bold;">Tên tài khoản:</span> Nguyễn Văn A</p>
              <p><span style="font-weight: bold;">Ngân hàng:</span> Vietcombank</p>
              <p><span style="font-weight: bold;">Chi nhánh:</span> Bắc Bình Dương</p>
              <p><span style="font-weight: bold;">Nội dung chuyển khoản:</span> Mua hàng</p>
            </div>

            <div class="payment-button" style="gap: 10px; flex-wrap: wrap;">
              <a style="text-decoration: none;" href="./gio-hang.php"><button type="button" class="btn btn-secondary" style="width: 185px; height: 50px;">Quay lại</button></a>
              <button type="submit" class="btn btn-success" style="width: 185px; height: 50px;">THANH TOÁN</button>
            </div>
          </form>

          <script>
            function toggleBankingForm() {
              const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
              const bankingForm = document.getElementById('banking-form');
              const bankingInputs = document.getElementsByClassName('banking-required');

              if (paymentMethod === 'Banking') {
                bankingForm.style.display = 'block';
                for (let input of bankingInputs) {
                  input.required = true;
                }
              } else {
                bankingForm.style.display = 'none';
                for (let input of bankingInputs) {
                  input.required = false;
                }
              }
            }

            function validateForm() {
              // Lấy radio button được chọn
              const defaultInfo = document.getElementById('default-information');
              const newInfo = document.getElementById('new-information');

              // Đặt giá trị cho trường hidden use-default-info
              document.getElementById('use-default-info').value = defaultInfo.checked ? "true" : "false";

              // Nếu chọn thông tin mới
              if (newInfo.checked) {
                // Lấy giá trị của các trường
                const newName = document.getElementById('new-name');
                const newSdt = document.getElementById('new-sdt');
                const newDiachi = document.getElementById('new-diachi');
                const province = document.getElementById('province');
                const district = document.getElementById('district');
                const wards = document.getElementById('wards');

                // Cập nhật các trường hidden
                document.getElementById('hidden-new-name').value = newName.value.trim();
                document.getElementById('hidden-new-sdt').value = newSdt.value.trim();
                document.getElementById('hidden-new-diachi').value = newDiachi.value.trim();
                document.getElementById('hidden-province').value = province.value;
                document.getElementById('hidden-district').value = district.value;
                document.getElementById('hidden-wards').value = wards.value;

                // Regex chỉ cho phép chữ cái Unicode và khoảng trắng (tối đa 80 từ)

                // Nếu đang sử dụng thông tin mới
                if (newInfo.checked) {
                  // Kiểm tra tên
                  const nameValue = newName.value.trim();
                  const nameRegex = /^([\p{L}]+(?:\s[\p{L}]+){0,79})$/u;
                  if (!nameRegex.test(nameValue)) {
                    alert("Họ và tên không hợp lệ! Chỉ được chứa chữ cái và khoảng trắng.");
                    newName.focus();
                    return false;
                  }

                  // Kiểm tra số điện thoại
                  const phoneValue = newSdt.value.trim();
                  const phoneRegex = /^0[0-9]{9}$/;
                  if (!phoneRegex.test(phoneValue)) {
                    alert("Số điện thoại không hợp lệ! Phải gồm 10 chữ số và bắt đầu bằng số 0.");
                    newSdt.focus();
                    return false;
                  }
                }


                if (!newDiachi.value.trim()) {
                  alert('Vui lòng nhập địa chỉ');
                  newDiachi.focus();
                  return false;
                }
                if (!province.value) {
                  alert('Vui lòng chọn tỉnh/thành phố');
                  province.focus();
                  return false;
                }
                if (!district.value) {
                  alert('Vui lòng chọn quận/huyện');
                  district.focus();
                  return false;
                }
                if (!wards.value) {
                  alert('Vui lòng chọn phường/xã');
                  wards.focus();
                  return false;
                }
              }

              // Kiểm tra phương thức thanh toán
              const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
              if (!paymentMethod) {
                alert('Vui lòng chọn phương thức thanh toán');
                return false;
              }

              // Nếu chọn thanh toán chuyển khoản
              if (paymentMethod.value === 'Banking') {
                const bankingInputs = document.getElementsByClassName('banking-required');
                for (let input of bankingInputs) {
                  if (!input.value.trim()) {
                    alert('Vui lòng điền đầy đủ thông tin thanh toán');
                    input.focus();
                    return false;
                  }
                }
              }

              return true;
            }

            // Thêm xử lý sự kiện cho radio buttons
            document.addEventListener('DOMContentLoaded', function() {
              const defaultInfo = document.getElementById('default-information');
              const newInfo = document.getElementById('new-information');
              const defaultForm = document.getElementById('default-information-form');
              const newForm = document.getElementById('new-information-form');

              function toggleForms() {
                if (defaultInfo.checked) {
                  defaultForm.style.display = 'block';
                  newForm.style.display = 'none';
                  // Xóa required attribute khi không sử dụng form mới
                  const inputs = newForm.querySelectorAll('input, select');
                  inputs.forEach(input => input.required = false);
                } else {
                  defaultForm.style.display = 'none';
                  newForm.style.display = 'block';
                  // Thêm required attribute khi sử dụng form mới
                  const inputs = newForm.querySelectorAll('input, select');
                  inputs.forEach(input => input.required = true);
                }
              }

              defaultInfo.addEventListener('change', toggleForms);
              newInfo.addEventListener('change', toggleForms);

              // Gọi hàm lần đầu để set trạng thái ban đầu
              toggleForms();
            });
          </script>

          <!-- <a href="../index.php" style="text-decoration: none; display: flex; justify-content: center; margin-bottom: 10px;">
            Tiếp tục mua hàng
          </a> -->
        </div>
      </div>
    </div>
  </main>
  <!-- FOOTER  -->
  <footer class="footer">
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