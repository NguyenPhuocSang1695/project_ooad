<?php
session_start();
require_once('../src/php/connect.php');
require_once('../src/php/token.php');
// require_once('../src/php/check_token_v2.php');
// require_once('../src/php/check_status.php');

require __DIR__ . '/../src/Jwt/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// Kiểm tra xem cookie 'token' có tồn tại không
if (!isset($_COOKIE['token'])) {
  header("Location: user-login.php?error=login_required&redirect=gio-hang.php");
  exit();
}

try {
  $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
  $username = $decoded->data->Username;
} catch (Exception $e) {
  header("Location: user-login.php?error=login_required&redirect=gio-hang.php");
  exit();
}


// Xử lý thêm, cập nhật và xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // THÊM SẢN PHẨM VÀO GIỎ
  if (isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = max(1, min(100, intval($_POST['quantity'])));

    $stmt = $conn->prepare("SELECT ProductID, ProductName, Price, ImageURL FROM products WHERE ProductID = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($product = $result->fetch_assoc()) {
      if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
      if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['Quantity'] += $quantity;
      } else {
        $_SESSION['cart'][$product_id] = [
          'ProductID'   => $product['ProductID'],
          'ProductName' => $product['ProductName'],
          'Price'       => $product['Price'],
          'ImageURL'    => $product['ImageURL'],
          'Quantity'    => $quantity
        ];
      }
    }
    $stmt->close();
    header("Location: gio-hang.php");
    exit;
  }
  // Cập nhật số lượng
  if (isset($_POST['update_product_id'], $_POST['quantity'])) {
    $pid = intval($_POST['update_product_id']);
    $newQty = max(1, intval($_POST['quantity']));
    if (isset($_SESSION['cart'][$pid])) {
      $_SESSION['cart'][$pid]['Quantity'] = $newQty;
    }
    header("Location: gio-hang.php");
    exit;
  }


  // Xóa sản phẩm
  if (isset($_POST['remove_product_id'])) {
    $product_id_to_remove = $_POST['remove_product_id'];

    // 1. Kiểm tra xem biến session giỏ hàng có tồn tại không
    if (isset($_SESSION['cart'])) {
      // 2. Duyệt qua các sản phẩm trong giỏ hàng
      foreach ($_SESSION['cart'] as $key => $item) {
        // 3. Tìm sản phẩm cần xóa
        if ($item['ProductID'] == $product_id_to_remove) {
          // 4. Xóa sản phẩm khỏi giỏ hàng bằng hàm unset()
          unset($_SESSION['cart'][$key]);
          break; // Dừng vòng lặp sau khi xóa sản phẩm
        }
      }
      // 5.  Sắp xếp lại chỉ mục của mảng để tránh bị thiếu phần tử.  Điều này rất quan trọng!
      $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    // Chuyển hướng về trang giỏ hàng để hiển thị các thay đổi
    header("Location: gio-hang.php"); // Quan trọng: Chuyển hướng để tránh các vấn đề khi tải lại trang
    exit();
  }
  // Xử lý nút đặt hàng
  if (isset($_POST['checkout'])) {
    header("Location: thanh-toan.php");
    exit;
  }
}

$cart_count =  0;
if (isset($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['Quantity'];
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

$cart_items = isset($_SESSION['cart']) ? array_values($_SESSION['cart']) : [];
$total = 0;
foreach ($cart_items as $item) {
  $total += $item['Price'] * $item['Quantity'];
}
$total_price_formatted = number_format($total, 0, ',', '.') . " VNĐ";
// Xoá cookie cart_quantity
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- CSS  -->
  <link rel="stylesheet" href="../src/css/gio-hang.css">
  <link rel="stylesheet" href="../src/css/gio-hang-php.css">
  <link rel="stylesheet" href="../src/css/user-sanpham.css" />
  <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../src/css/search-styles.css">
  <link rel="stylesheet" href="../assets/icon/fontawesome-free-6.7.2-web/css/all.min.css">
  <link rel="stylesheet" href="../src/css/searchAdvanceMobile.css">
  <link rel="stylesheet" href="../src/css/footer.css">
  <link rel="stylesheet" href="../src/css/brandname.css">
  <!-- JS  -->
  <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script src="../src/js/Trang_chu.js"></script>
  <script src="../src/js/reloadPage.js"></script>
  <!-- <script src="../src/js/main.js"></script> -->
  <script src="../src/js/search-common.js"></script>
  <script src="../src/js/onOffSeacrhAdvance.js"></script>
  <!-- Lọc sản phẩm theo phân loại  -->
  <!-- <script src="../src/js/filter-product.js"></script> -->
  <script src="../src/js/search-index.js"></script>
  <title>Giỏ hàng</title>
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
      <a href="#"><span>Giỏ hàng</span></a>
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
      <p class="text-success h1 text-center text-uppercase">Giỏ hàng</p>
    </div>
    <div class="infor-order bg-light">
      <div class="status-order">
        <img class="cart-2" src="../assets/images/cart.svg" alt="cart">
        <hr>
        <img src="../assets/images/id-card.svg" alt="id-card">
        <hr>
        <img src="../assets/images/circle-check.svg" alt="ccheck">
      </div>
      <?php if (count($cart_items) > 0): ?>
        <?php foreach ($cart_items as $item): ?>

          <div class="order">
            <div class="order-img">
              <img src="<?php echo ".." . $item['ImageURL']; ?>" width="120" class="cart-image">
            </div>

            <div class="frame">
              <div class="name-price">
                <p><strong><?php echo htmlspecialchars($item['ProductName']); ?></strong></p>

                <!-- Giá sản phẩm hiển thị, gán thêm data-price để JS dễ lấy -->
                <p class="price" data-price="<?php echo $item['Price']; ?>">
                  <strong><?php echo number_format($item['Price'], 0, ',', '.') . " VNĐ"; ?></strong>
                </p>
              </div>

              <div class="function">
                <!-- Button trigger modal -->
                <form action="gio-hang.php" method="POST" id="remove-form-<?php echo $item['ProductID']; ?>">
                  <input type="hidden" name="remove_product_id" value="<?php echo $item['ProductID']; ?>">

                  <!-- Nút icon mở modal -->
                  <button type="button" class="btn"
                    style=" width: 53px; height: 33px;"
                    data-bs-toggle="modal"
                    data-bs-target="#exampleModal-<?php echo $item['ProductID']; ?>">
                    <i class="fa-solid fa-trash" style="font-size: 25px;"></i>
                  </button>

                  <!-- Modal xác nhận -->
                  <div class="modal fade w-100" id="exampleModal-<?php echo $item['ProductID']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel-<?php echo $item['ProductID']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">

                        <div class="modal-header">
                          <h1 class="modal-title fs-5" id="exampleModalLabel-<?php echo $item['ProductID']; ?>">Thông báo</h1>
                          <button type="button" class="btn-close" style="width: 10%;" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body d-flex justify-content-center align-items-center">
                          Bạn có chắc muốn xóa sản phẩm chứ!
                        </div>

                        <div class="modal-footer d-flex flex-row">
                          <button type="button" class="btn btn-secondary" style="width: 20%;" data-bs-dismiss="modal">Đóng</button>

                          <!-- Nút Xóa submit form -->
                          <button type="button" class="btn btn-primary" style="width: 45%;" onclick="document.getElementById('remove-form-<?php echo $item['ProductID']; ?>').submit();">Xóa</button>
                        </div>

                      </div>
                    </div>
                  </div>
                </form>

                <div class="add-del">
                  <div class="oder">
                    <div class="wrapper">
                      <form action="gio-hang.php" method="POST" class="update-form">
                        <!-- Truyền ProductID để xác định sản phẩm cần cập nhật -->
                        <input type="hidden" name="update_product_id" value="<?php echo $item['ProductID']; ?>">

                        <!-- Nút giảm số lượng -->
                        <button type="button" class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>

                        <!-- Trường số lượng, gán thuộc tính data-price để JS dùng cho tính toán nếu cần -->
                        <input type="number" name="quantity" value="<?php echo max(1, $item['Quantity']); ?>" min="1"
                          class="quantity-input" data-price="<?php echo $item['Price']; ?>">

                        <!-- Nút tăng số lượng -->
                        <button type="button" class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
                      </form>
                      <script src="../src/js/gio-hang.js"></script>
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
      </div>

      <form action="gio-hang.php" method="POST" name="ThanhToan">
        <div class="dat-hang" style="gap: 10px;">
          <a style="text-decoration: none;" href="../index.php"><button type="button" class="btn btn-secondary" style="width: 185px; height: 50px; margin: 10px 0 15px 0;">Tiếp tục mua hàng</button></a>

          <button type="submit" class="btn btn-success" name="checkout" style="width: 185px;
          height: 50px; margin: 10px 0 15px 0;">ĐẶT HÀNG</button>
        </div>
      </form>
      <div class="text" style="margin-bottom: 10px;">
        <!-- quay về trang chủ  -->

      </div>
    </div>


  </div>

  <!-- <div class="type-tree" id="type-tree"></div>
  <div id="product-list">Kết quả ở đây</div> -->

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
  <!-- </div> -->

</body>

</html>