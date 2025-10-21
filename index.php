<?php
session_start();
require_once './src/php/token.php';
require_once('./src/php/check_status_index.php');
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
$total_price_formatted = number_format($total_amount, 0, ',', '.') . " VND";

// Cập nhật giá & ẩn/sửa giỏ hàng theo database mới nhất
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
  $cart_product_ids = array_column($_SESSION['cart'], 'ProductID');
  $placeholders = implode(',', array_fill(0, count($cart_product_ids), '?'));
  // Lấy luôn Price và Status
  $sql = "SELECT ProductID, Price, Status 
          FROM products 
          WHERE ProductID IN ($placeholders)";
  require_once './php-api/connectdb.php';
  $conn = connect_db();
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



?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- CSS  -->
  <link rel="stylesheet" type="text/css" href="./src/css/trang-chu.css" />
  <link rel="stylesheet" href="./assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/icon/fontawesome-free-6.7.2-web/css/all.min.css" />
  <link rel="stylesheet" href="./src/css/searchAdvanceMobile.css" />
  <link rel="stylesheet" href="./src/css/user-sanpham.css" />
  <link rel="stylesheet" href="./src/css/footer.css">
  <link rel="stylesheet" href="./src/css/brandname.css">
  <!-- JS  -->
  <!-- <script src="./src/js/main.js"></script> -->
  <script src="./assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script src="./src/js/onOffSeacrhAdvance.js"></script>
  <script src="./src/js/reloadPage.js"></script>
  <!-- <script src="./src/js/search.js"></script> -->
  <!-- <script src="./src/js/tim-kiem-nang-cao.js"></script> -->
  <!-- <script src="./src/js/search-common.js"></script> -->
  <!-- Tìm kiếm  -->
  <title>Trang Chủ</title>
  <!-- <script src="./src/js/search.js"></script> -->
  <!-- AVCCVSA -->
  <style>
    .for_more {
      display: flex;
      justify-content: center;
      margin: 40px 0;
    }

    .for_more button {
      padding: 12px 30px;
      font-size: 16px;
      font-weight: 500;
      background-color: #1c8e2e;
      color: white;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .for_more button:hover {
      background-color: #27ae60;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .for_more button:active {
      transform: translateY(0);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    /* Responsive cho mobile */
    @media (max-width: 768px) {
      .for_more button {
        padding: 10px 25px;
        font-size: 14px;
      }
    }
  </style>
</head>

<!-- <script src="./src/js/search.js"></script> -->

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
                    <input type="search" placeholder="Tìm kiếm sản phẩm..." id="searchInput"
                      name="search" class="search-input" />
                    <button type="button" class="advanced-search-toggle" id="advanced-search-toggle"
                      onclick="toggleAdvancedSearch()" title="Tìm kiếm nâng cao">
                      <i class="fas fa-sliders-h"></i>
                    </button>
                    <button type="submit" class="search-button" onclick="performSearch()"
                      title="Tìm kiếm">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>

                <!-- Form tìm kiếm nâng cao được thiết kế lại -->
                <div id="advancedSearchForm" class="advanced-search-panel" style="display: none">
                  <div class="advanced-search-header">
                    <h5>Tìm kiếm nâng cao</h5>
                    <button type="button" class="close-advanced-search"
                      onclick="toggleAdvancedSearch()">
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
                        require_once './php-api/connectdb.php'; // Đường dẫn đúng tới file kết nối

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
                          <input type="number" id="minPrice" name="minPrice" placeholder="Từ"
                            min="0" />
                          <span class="price-separator">-</span>
                          <input type="number" id="maxPrice" name="maxPrice" placeholder="Đến"
                            min="0" />
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

            <!-- Header, Giỏ hàng và user -->
            <div class="cart-wrapper">
              <div class="cart-icon">
                <a href="./pages/gio-hang.php"><img src="assets/images/cart.svg" alt="cart" />
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
                      <img src="<?php echo "." . $item['ImageURL']; ?>" alt="<?php echo $item['ProductName']; ?>" class="cart-thumb" />
                      <div class="cart-item-details">
                        <h5><?php echo $item['ProductName']; ?></h5>
                        <p>Giá: <?php echo number_format($item['Price'], 0, ',', '.') . " VND"; ?></p>
                        <p><?php echo $item['Quantity']; ?> × <?php echo number_format($item['Price'], 0, ',', '.') . " VND"; ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p>Giỏ hàng của bạn đang trống.</p>
                <?php endif; ?>
              </div>
            </div>
            <script src="./src/js/AnSanPham.js"></script>
            <div class="user-icon">
              <label for="tick" style="cursor: pointer">
                <img src="assets/images/user.svg" alt="" />
              </label>
              <input id="tick" hidden type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasExample" aria-controls="offcanvasExample" />
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
                        <a class="nav-link login-logout" href="./pages/user-register.php">Đăng ký</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link login-logout" href="./pages/user-login.php">Đăng nhập</a>
                      </li>
                    <?php else: ?>
                      <li class="nav-item">
                        <a class="nav-link hs-ls-dx" href="./pages/ho-so.php">Hồ sơ</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link hs-ls-dx" href="./pages/user-History.php">Lịch sử mua
                          hàng</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link hs-ls-dx" href="./src/php/logout.php">Đăng xuất</a>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
              data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar"
              aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar"
              aria-labelledby="offcanvasNavbarLabel">
              <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">
                  THEE TREE
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                  aria-label="Close"></button>
              </div>
              <div id="offcanvasbody" class="offcanvas-body offcanvas-fullscreen mt-20">
                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                  <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Trang chủ</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#">Giới thiệu</a>
                  </li>
                  <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button"
                      data-bs-toggle="dropdown" aria-expanded="false">
                      Sản phẩm
                    </a>
                    <ul class="dropdown-menu">
                      <?php
                      require_once './php-api/connectdb.php'; // hoặc đường dẫn đúng đến file connect của bạn
                      $conn = connect_db();

                      $sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryID ASC";
                      $result = $conn->query($sql);

                      if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          $categoryID = htmlspecialchars($row['CategoryID']);
                          $categoryName = htmlspecialchars($row['CategoryName']);
                          echo "<li><a class='dropdown-item' href='./pages/phan-loai.php?category_id=$categoryID'>$categoryName</a></li>";
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
                    <input class="form-control me-2" type="search" placeholder="Tìm kiếm"
                      aria-label="Search" style="height: 37.6px;" />
                    <!-- Nút tìm kiếm nâng cao trên mobile  -->
                    <button type="button" class="advanced-search-toggle"
                      onclick="toggleMobileSearch()" title="Tìm kiếm nâng cao">
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
                        require_once './php-api/connectdb.php'; // Đường dẫn đúng tới file kết nối

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
                          <input type="number" id="minPriceMobile" name="minPrice"
                            placeholder="Từ" min="0" />
                          <span class="price-separator">-</span>
                          <input type="number" id="maxPriceMobile" name="maxPrice"
                            placeholder="Đến" min="0" />
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
          <a href="index.php"><img class="img-fluid" src="./assets/images/LOGO-2.jpg" alt="LOGO" /></a>
        </div>
        <div class="brand-name">THE TREE</div>
      </div>
      <div class="choose">
        <ul>
          <li>
            <a href="index.php" style="font-weight: bold">Trang chủ</a>
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
                require_once './php-api/connectdb.php'; // hoặc đường dẫn đúng đến file connect của bạn
                $conn = connect_db();

                $sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryID ASC";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    $categoryID = htmlspecialchars($row['CategoryID']);
                    $categoryName = htmlspecialchars($row['CategoryName']);
                    echo "<li><a class='dropdown-item' href='./pages/phan-loai.php?category_id=$categoryID'>$categoryName</a></li>";
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
      <img src="./assets/images/CAY21.jpg" alt="CAY21" />
    </div>
  </div>

  <main>
    <!-- DANH MỤC SẢN PHẨM -->
    <?php
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'c01db';

    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
      die("Kết nối thất bại: " . $conn->connect_error);
    }

    // Truy vấn: Lấy sản phẩm đầu tiên (MIN ProductID) trong mỗi danh mục có ít nhất 1 sản phẩm Status = 'appear'
    $sql = "
  SELECT p.*, c.CategoryName
  FROM categories c
  JOIN (
    SELECT CategoryID, MIN(ProductID) AS MinProductID
    FROM products
    WHERE Status = 'appear'
    GROUP BY CategoryID
  ) AS sub ON c.CategoryID = sub.CategoryID
  JOIN products p ON p.ProductID = sub.MinProductID
  ORDER BY c.CategoryID;
";

    $result = $conn->query($sql);
    $products = [];
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $products[] = $row;
      }
    }
    $conn->close();
    ?>

    <section id="container_1" class="class">
      <h2 class="font_size">DANH MỤC SẢN PHẨM</h2>
      <div class="IMG">
        <?php foreach ($products as $product): ?>
          <div class="img__TREE">
            <a
              href="./pages/phan-loai.php?category_id=<?= $product['CategoryID'] ?>&category_name=<?= urlencode($product['CategoryName']) ?>">
              <img class="THE-TREE" src=".<?= htmlspecialchars($product['ImageURL']) ?>"
                alt="<?= htmlspecialchars($product['CategoryName']) ?>" />
              <p class="content_TREE-1"><?= htmlspecialchars($product['CategoryName']) ?></p>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>



    <section id="product1" class="section-p1">
      <!-- SẢN PHẨM MỚI -->
      <h2 class="font_size">SẢN PHẨM MỚI</h2>

      <div class="pro-container">
        <?php
        require_once './php-api/connectdb.php';
        $conn = connect_db();

        $limit = 8; // chỉ lấy đúng 8 sản phẩm

        // Truy vấn sản phẩm giới hạn 8
        $stmt = $conn->prepare('
    SELECT ProductID, ProductName, Price, ImageURL 
    FROM products 
    WHERE Status = "appear"
    ORDER BY ProductID DESC
    LIMIT ?');
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        // Hiển thị sản phẩm
        if ($result && $result->num_rows > 0):
          while ($product = $result->fetch_assoc()):
        ?>
            <div class="pro">
              <a style="text-decoration: none" href="./pages/user-sanpham.php?id=<?= htmlspecialchars($product['ProductID']) ?>">
                <img style="width: 100%; height: 300px;" src=".<?= htmlspecialchars($product['ImageURL']) ?>" alt="<?= htmlspecialchars($product['ProductName']) ?>" />
                <div class="item_name__price">
                  <p style="text-decoration: none; color: black; font-size: 20px; font-weight:bold"><?= htmlspecialchars($product['ProductName']) ?></p>
                  <span style="font-size: 20px"><?= number_format($product['Price'], 0, ',', '.') ?> VND</span>
                </div>
              </a>
            </div>
        <?php
          endwhile;
        else:
          echo "<p>Không có sản phẩm nào để hiển thị.</p>";
        endif;
        ?>

      </div>


      <div class="for_more">
        <a href="./pages/allproduct.php">
          <button>Xem thêm</button>
        </a>
      </div>
    </section>
  </main>

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
  <script>
    // search.js - Xử lý tìm kiếm cho website bán cây

    document.addEventListener("DOMContentLoaded", function() {
      // Kiểm tra nếu đang ở trang kết quả tìm kiếm
      if (window.location.pathname.includes("search-result.php")) {
        loadSearchResults();

        // Khởi tạo các nút lọc
        initializeFilterButtons();
      }

      // Khởi tạo sự kiện cho form tìm kiếm
      initializeSearchForms();
    });

    // Khởi tạo các form tìm kiếm
    function initializeSearchForms() {
      // Xử lý form tìm kiếm desktop
      const desktopForm = document.getElementById("searchForm");
      if (desktopForm) {
        desktopForm.addEventListener("submit", function(e) {
          e.preventDefault();
          performSearch();
        });
      }

      // Xử lý form tìm kiếm mobile
      const mobileForm = document.getElementById("searchFormMobile");
      if (mobileForm) {
        mobileForm.addEventListener("submit", function(e) {
          e.preventDefault();
          performSearchMobile();
        });
      }
    }

    // Khởi tạo các nút lọc sản phẩm
    function initializeFilterButtons() {
      // Thêm container lọc nếu chưa tồn tại
      createFilterContainer();

      // Lấy trạng thái lọc từ URL
      const urlParams = new URLSearchParams(window.location.search);
      const currentSort = urlParams.get("sort") || "default";

      // Cập nhật trạng thái active của các nút
      updateActiveFilterButton(currentSort);

      // Thêm event listener cho các nút lọc
      const filterButtons = document.querySelectorAll(".filter-button");
      filterButtons.forEach((button) => {
        button.addEventListener("click", function() {
          const sortBy = this.getAttribute("data-sort");

          // Lọc sản phẩm ngay tại client không cần refresh trang
          if (globalProductsData.length > 0) {
            const searchResultsContainer = document.getElementById("searchResults");
            if (searchResultsContainer) {
              // Cập nhật trạng thái active
              updateActiveFilterButton(sortBy);

              // Sắp xếp và hiển thị lại sản phẩm
              const sortedData = sortProductsData(globalProductsData, sortBy);
              displayProducts(sortedData, searchResultsContainer);

              // Cập nhật URL để lưu trạng thái sắp xếp (không refresh trang)
              updateUrlWithSort(sortBy);
            }
          }
        });
      });
    }

    // Cập nhật URL với tham số sắp xếp mà không làm mới trang
    function updateUrlWithSort(sortBy) {
      const url = new URL(window.location.href);
      const urlParams = url.searchParams;

      if (sortBy === "default") {
        urlParams.delete("sort");
      } else {
        urlParams.set("sort", sortBy);
      }

      // Cập nhật URL không làm mới trang
      window.history.pushState({}, "", url);
    }

    // Tạo container cho các nút lọc
    function createFilterContainer() {
      const searchResultsContainer = document.getElementById("searchResults");
      if (!searchResultsContainer) return;

      // Kiểm tra nếu container đã tồn tại
      if (document.getElementById("filter-container")) return;

      // Lấy trạng thái lọc từ URL
      const urlParams = new URLSearchParams(window.location.search);
      const currentSort = urlParams.get("sort") || "default";

      // Tạo và chèn container lọc trước kết quả tìm kiếm
      const filterContainerHTML = `
    <div id="filter-container" class="filter-container">
      <div class="filter-label">Sắp xếp theo:</div>
      <div class="filter-select-wrapper">
        <select id="filter-select" class="filter-select">
          <option value="default" ${
            currentSort === "default" ? "selected" : ""
          }>Mặc định</option>
          <option value="price-asc" ${
            currentSort === "price-asc" ? "selected" : ""
          }>Giá tăng dần</option>
          <option value="price-desc" ${
            currentSort === "price-desc" ? "selected" : ""
          }>Giá giảm dần</option>
          <option value="name-asc" ${
            currentSort === "name-asc" ? "selected" : ""
          }>Tên A-Z</option>
          <option value="name-desc" ${
            currentSort === "name-desc" ? "selected" : ""
          }>Tên Z-A</option>
        </select>
      </div>
    </div>
  `;

      // Chèn container lọc vào đầu kết quả tìm kiếm
      searchResultsContainer.insertAdjacentHTML("beforebegin", filterContainerHTML);

      // Thêm event listener cho select
      const filterSelect = document.getElementById("filter-select");
      if (filterSelect) {
        filterSelect.addEventListener("change", function() {
          const sortBy = this.value;

          // Lọc sản phẩm ngay tại client không cần refresh trang
          if (globalProductsData.length > 0) {
            const searchResultsContainer = document.getElementById("searchResults");
            if (searchResultsContainer) {
              // Sắp xếp và hiển thị lại sản phẩm
              const sortedData = sortProductsData(globalProductsData, sortBy);
              displayProducts(sortedData, searchResultsContainer);

              // Cập nhật URL để lưu trạng thái sắp xếp (không refresh trang)
              updateUrlWithSort(sortBy);
            }
          }
        });
      }
    }

    // Cập nhật trạng thái active của nút lọc
    function updateActiveFilterButton(sortBy) {
      // Xóa trạng thái active khỏi tất cả các nút
      const filterButtons = document.querySelectorAll(".filter-button");
      filterButtons.forEach((button) => {
        button.classList.remove("active");
      });

      // Thêm active cho nút được chọn
      const activeButton =
        document.querySelector(`.filter-button[data-sort="${sortBy}"]`) ||
        document.querySelector('.filter-button[data-sort="default"]');
      if (activeButton) {
        activeButton.classList.add("active");
      }
    }

    // Hàm chuyển đổi hiển thị form tìm kiếm nâng cao desktop
    function toggleAdvancedSearch() {
      const advancedForm = document.getElementById("advancedSearchForm");
      if (advancedForm) {
        if (advancedForm.style.display === "none") {
          advancedForm.style.display = "block";
        } else {
          advancedForm.style.display = "none";
        }
      }
    }

    // Hàm chuyển đổi hiển thị form tìm kiếm nâng cao mobile
    function toggleMobileSearch() {
      const mobileFilterContainer = document.getElementById(
        "search-filter-container-mobile"
      );
      if (mobileFilterContainer) {
        if (
          mobileFilterContainer.style.display === "none" ||
          !mobileFilterContainer.style.display
        ) {
          mobileFilterContainer.style.display = "block";
        } else {
          mobileFilterContainer.style.display = "none";
        }
      }
    }

    // Thiết lập giá trị khoảng giá cho form desktop
    function setPrice(min, max) {
      document.getElementById("minPrice").value = min;
      document.getElementById("maxPrice").value = max === 0 ? "" : max;
    }

    // Thiết lập giá trị khoảng giá cho form mobile
    function setPriceMobile(min, max) {
      document.getElementById("minPriceMobile").value = min;
      document.getElementById("maxPriceMobile").value = max === 0 ? "" : max;
    }

    // Đặt lại bộ lọc tìm kiếm desktop
    function resetFilters() {
      document.getElementById("categoryFilter").value = "Chọn phân loại";
      document.getElementById("minPrice").value = "";
      document.getElementById("maxPrice").value = "";
      document.getElementById("searchInput").value = "";
    }

    // Đặt lại bộ lọc tìm kiếm mobile
    function resetMobileFilters() {
      document.getElementById("categoryFilter-mobile").value = "";
      document.getElementById("minPriceMobile").value = "";
      document.getElementById("maxPriceMobile").value = "";
      document.querySelector('#searchFormMobile input[type="search"]').value = "";
    }

    // Xử lý tìm kiếm từ form desktop
    function performSearch() {
      const searchInput = document.getElementById("searchInput").value;
      const category = document.getElementById("categoryFilter").value;
      const minPrice = document.getElementById("minPrice").value;
      const maxPrice = document.getElementById("maxPrice").value;

      // Tạo URL tìm kiếm
      redirectToSearchPage(searchInput, category, minPrice, maxPrice);
    }

    // Xử lý tìm kiếm từ form mobile
    function performSearchMobile() {
      const searchInput = document.querySelector(
        '#searchFormMobile input[type="search"]'
      ).value;
      const category = document.getElementById("categoryFilter-mobile").value;
      const minPrice = document.getElementById("minPriceMobile").value;
      const maxPrice = document.getElementById("maxPriceMobile").value;

      // Tạo URL tìm kiếm
      redirectToSearchPage(searchInput, category, minPrice, maxPrice);
    }

    // Chuyển hướng đến trang kết quả tìm kiếm
    function redirectToSearchPage(search, category, minPrice, maxPrice) {
      let url = "./pages/search-result.php?q=" + encodeURIComponent(search);

      if (
        category &&
        category !== "Chọn phân loại" &&
        category !== "Tất cả phân loại"
      ) {
        url += "&category=" + encodeURIComponent(category);
      }

      if (minPrice) {
        url += "&minPrice=" + encodeURIComponent(minPrice);
      }

      if (maxPrice) {
        url += "&maxPrice=" + encodeURIComponent(maxPrice);
      }

      window.location.href = url;
    }

    // Khai báo biến để lưu trữ dữ liệu sản phẩm toàn cục
    let globalProductsData = [];

    // Tải kết quả tìm kiếm
    function loadSearchResults() {
      // Phân tích URL để lấy tham số tìm kiếm
      const urlParams = new URLSearchParams(window.location.search);
      const search = urlParams.get("q") || "";
      const category = urlParams.get("category") || "";
      const minPrice = urlParams.get("minPrice") || "";
      const maxPrice = urlParams.get("maxPrice") || "";
      const sortBy = urlParams.get("sort") || "default";

      // Hiển thị thông tin tìm kiếm
      displaySearchParams(search, category, minPrice, maxPrice);

      // Gọi API để lấy kết quả
      fetchSearchResults(search, category, minPrice, maxPrice, sortBy);
    }

    // Hiển thị tham số tìm kiếm
    function displaySearchParams(search, category, minPrice, maxPrice) {
      const searchParamsContainer = document.getElementById("searchParams");
      if (!searchParamsContainer) return;

      const searchParamsList = searchParamsContainer.querySelector(
        ".search-params-list"
      );
      if (!searchParamsList) return;

      // Tiêu đề kết quả tìm kiếm
      const title = searchParamsContainer.querySelector("h3");
      if (title) {
        title.textContent = "Kết quả tìm kiếm cho:";
      }

      // Xóa các tham số cũ
      searchParamsList.innerHTML = "";

      // Thêm tham số tìm kiếm vào danh sách
      if (search) {
        const searchParam = document.createElement("li");
        searchParam.innerHTML = `<span class="param-label">Từ khóa:</span> ${search}`;
        searchParamsList.appendChild(searchParam);
      }

      if (
        category &&
        category !== "Chọn phân loại" &&
        category !== "Tất cả phân loại"
      ) {
        const categoryParam = document.createElement("li");
        categoryParam.innerHTML = `<span class="param-label">Phân loại:</span> ${category}`;
        searchParamsList.appendChild(categoryParam);
      }

      if (minPrice || maxPrice) {
        const priceParam = document.createElement("li");
        let priceText = '<span class="param-label">Giá:</span> ';

        if (minPrice && maxPrice) {
          priceText += `${formatCurrency(minPrice)} - ${formatCurrency(maxPrice)}`;
        } else if (minPrice) {
          priceText += `Từ ${formatCurrency(minPrice)}`;
        } else if (maxPrice) {
          priceText += `Đến ${formatCurrency(maxPrice)}`;
        }

        priceParam.innerHTML = priceText;
        searchParamsList.appendChild(priceParam);
      }

      // Luôn hiển thị container, thêm thông báo mặc định nếu không có tham số
      if (searchParamsList.children.length === 0) {
        const defaultParam = document.createElement("li");
        defaultParam.innerHTML = `<span class="param-label">Tất cả sản phẩm</span>`;
        searchParamsList.appendChild(defaultParam);
      }

      // Luôn hiển thị container
      searchParamsContainer.style.display = "block";
    }

    // Định dạng giá tiền
    function formatCurrency(amount) {
      return new Intl.NumberFormat("vi-VN", {
        // style: "currency",
        // currency: "VND",
      }).format(amount) + " VND";
    }

    // Gọi API để lấy kết quả tìm kiếm
    function fetchSearchResults(
      search,
      category,
      minPrice,
      maxPrice,
      sortBy = "default"
    ) {
      const searchResultsContainer = document.getElementById("searchResults");
      if (!searchResultsContainer) return;

      // Hiển thị spinner loading
      searchResultsContainer.innerHTML = `
    <div class="loading-spinner">
      <i class="fas fa-spinner fa-spin"></i> Đang tải...
    </div>
  `;

      // Tạo URL API cơ bản
      let apiUrl = "./php-api/search.php";

      // Thêm các tham số tìm kiếm nếu có
      const params = new URLSearchParams();

      if (search) {
        params.append("q", search);
      }

      if (
        category &&
        category !== "Chọn phân loại" &&
        category !== "Tất cả phân loại"
      ) {
        params.append("category", category);
      }

      if (minPrice) {
        params.append("minPrice", minPrice);
      }

      if (maxPrice) {
        params.append("maxPrice", maxPrice);
      }

      // Thêm params vào URL nếu có
      if (params.toString()) {
        apiUrl += "?" + params.toString();
      }

      // Gọi API
      fetch(apiUrl)
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          return response.json();
        })
        .then((data) => {
          if (data.message) {
            displayNoResults(searchResultsContainer, data.message);
            return;
          }

          // Lưu dữ liệu sản phẩm vào biến toàn cục
          globalProductsData = data;

          // Sắp xếp dữ liệu nếu có yêu cầu sắp xếp khác
          const sortedData =
            sortBy !== "default" ? sortProductsData(data, sortBy) : data;

          // Hiển thị kết quả
          displayProducts(sortedData, searchResultsContainer);
        })
        .catch((error) => {
          console.error("Error:", error);
          displayError(searchResultsContainer, "Có lỗi xảy ra khi tải dữ liệu");
        });
    }

    // Sắp xếp dữ liệu sản phẩm (nếu API không hỗ trợ)
    function sortProductsData(products, sortBy) {
      if (!sortBy || sortBy === "default") {
        return products;
      }

      // Tạo bản sao để không ảnh hưởng đến mảng gốc
      const sortedProducts = [...products];

      switch (sortBy) {
        case "price-asc":
          sortedProducts.sort((a, b) => parseFloat(a.Price) - parseFloat(b.Price));
          break;
        case "price-desc":
          sortedProducts.sort((a, b) => parseFloat(b.Price) - parseFloat(a.Price));
          break;
        case "name-asc":
          sortedProducts.sort((a, b) =>
            a.ProductName.localeCompare(b.ProductName, "vi")
          );
          break;
        case "name-desc":
          sortedProducts.sort((a, b) =>
            b.ProductName.localeCompare(a.ProductName, "vi")
          );
          break;
        default:
          break;
      }

      return sortedProducts;
    }

    // Hiển thị lỗi
    function displayError(container, errorMessage) {
      container.innerHTML = `
    <div class="error-message">
      <i class="fas fa-exclamation-circle"></i>
      <p>Có lỗi xảy ra khi tải kết quả tìm kiếm: ${errorMessage}</p>
    </div>
  `;
    }

    // Hiển thị khi không có kết quả
    function displayNoResults(container, message) {
      container.innerHTML = `
    <div class="no-results-message" style="position: static !important;">
      <i class="fas fa-search"></i>
      <p>${
        message || "Không tìm thấy sản phẩm nào phù hợp với tìm kiếm của bạn"
      }</p>
      <div class="suggestions">
        <p>Gợi ý:</p>
        <ul>
          <li>Kiểm tra lỗi chính tả của từ khóa tìm kiếm</li>
          <li>Thử sử dụng từ khóa khác</li>
          <li>Thử tìm kiếm với ít bộ lọc hơn</li>
        </ul>
      </div>
    </div>
  `;
    }

    // Hiển thị danh sách sản phẩm
    function displayProducts(products, container) {
      // Nếu không có sản phẩm
      if (!products || products.length === 0) {
        displayNoResults(container);
        return;
      }

      // Lấy trang hiện tại từ URL
      const urlParams = new URLSearchParams(window.location.search);
      const currentPage = parseInt(urlParams.get("page")) || 1;

      // Số sản phẩm mỗi trang (đã sửa thành 8)
      const itemsPerPage = 8;

      // Tính chỉ số bắt đầu và kết thúc cho trang hiện tại
      const startIndex = (currentPage - 1) * itemsPerPage;
      const endIndex = Math.min(startIndex + itemsPerPage, products.length);

      // Lấy các sản phẩm cho trang hiện tại
      const currentPageProducts = products.slice(startIndex, endIndex);

      // Khởi tạo cấu trúc HTML cho danh sách sản phẩm
      let productsHTML = `
    <div class="search-results-count">Tìm thấy ${products.length} sản phẩm</div>
    <div class="products-grid">
  `;

      // Thêm mỗi sản phẩm vào grid
      currentPageProducts.forEach((product) => {
        productsHTML += `
      <div class="product-card">
        <div class="product-image">
          <img src="..${product.ImageURL}" alt="${product.ProductName}">
        </div>
        <div class="product-info">
          <h3 class="product-name" style = "font-weight: bold;">${
            product.ProductName
          }</h3>
          <div class="product-price">${formatCurrency(product.Price)}</div>
          <a href="user-sanpham.php?id=${
            product.ProductID
          }" class="btn-view-product">Xem chi tiết</a>
        </div>
      </div>
    `;
      });

      productsHTML += `</div>`;

      // Đặt HTML vào container
      container.innerHTML = productsHTML;

      // Hiển thị phân trang
      setupPagination(products.length);

      // Cuộn trang lên đầu khi thay đổi sắp xếp hoặc trang
      window.scrollTo({
        top: document.getElementById("searchParams").offsetTop - 100,
        behavior: "smooth",
      });
    }

    // Thiết lập phân trang
    function setupPagination(totalItems) {
      const paginationContainer = document.getElementById("pagination");
      if (!paginationContainer) return;

      // Số sản phẩm mỗi trang (đã sửa thành 8)
      const itemsPerPage = 8;

      // Chỉ hiển thị phân trang nếu có nhiều hơn số sản phẩm trên một trang
      if (totalItems <= itemsPerPage) {
        paginationContainer.style.display = "none";
        return;
      }

      // Lấy trang hiện tại từ URL
      const urlParams = new URLSearchParams(window.location.search);
      const currentPage = parseInt(urlParams.get("page")) || 1;

      // Tính tổng số trang (8 sản phẩm mỗi trang)
      const totalPages = Math.ceil(totalItems / itemsPerPage);

      // Tạo HTML phân trang
      let paginationHTML = "";

      // Nút Previous
      paginationHTML += `
    <a href="#" class="btn btn-secondary pagination-item ${
      currentPage === 1 ? "disabled" : ""
    }" 
       onclick="${
         currentPage > 1
           ? "changePage(" + (currentPage - 1) + ")"
           : "return false"
       }" 
       aria-label="Previous page">
      <i class="fas fa-chevron-left"></i>
    </a>
  `;

      // Số trang
      const startPage = Math.max(1, currentPage - 2);
      const endPage = Math.min(totalPages, currentPage + 2);

      // Hiển thị trang đầu tiên nếu cần
      if (startPage > 1) {
        paginationHTML += `
      <a href="#" class="btn btn-secondary pagination-item" onclick="changePage(1)">1</a>
    `;

        if (startPage > 2) {
          paginationHTML += `<span class="btn btn-secondary pagination-ellipsis">...</span>`;
        }
      }

      // Các số trang
      for (let i = startPage; i <= endPage; i++) {
        const btnClass = i === currentPage ? "btn-success" : "btn-secondary";
        paginationHTML += `
    <a href="#" class="btn pagination-item ${btnClass}" onclick="changePage(${i})">${i}</a>
  `;
      }

      // Hiển thị trang cuối cùng nếu cần
      if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
          paginationHTML += `<span class="btn btn-secondary pagination-ellipsis">...</span>`;
        }

        paginationHTML += `
      <a href="#" class=" btn btn-secondary pagination-item" onclick="changePage(${totalPages})">${totalPages}</a>
    `;
      }

      // Nút Next
      paginationHTML += `
    <a href="#" class="btn btn-secondary pagination-item ${
      currentPage === totalPages ? "disabled" : ""
    }" 
       onclick="${
         currentPage < totalPages
           ? "changePage(" + (currentPage + 1) + ")"
           : "return false"
       }" 
       aria-label="Next page">
      <i class="fas fa-chevron-right"></i>
    </a>
  `;

      // Đặt HTML phân trang
      paginationContainer.innerHTML = paginationHTML;
      paginationContainer.style.display = "flex";
    }

    // Thay đổi trang
    function changePage(page) {
      // Kiểm tra nếu có dữ liệu sản phẩm toàn cục, thực hiện chuyển trang không cần tải lại
      if (globalProductsData.length > 0) {
        const searchResultsContainer = document.getElementById("searchResults");
        if (searchResultsContainer) {
          // Cập nhật URL không làm mới trang
          const url = new URL(window.location.href);
          url.searchParams.set("page", page);
          window.history.pushState({}, "", url);

          // Lấy tham số sắp xếp hiện tại
          const sortBy = url.searchParams.get("sort") || "default";

          // Hiển thị lại sản phẩm với trang mới
          const sortedData = sortProductsData(globalProductsData, sortBy);
          displayProducts(sortedData, searchResultsContainer);

          return false;
        }
      }

      // Nếu không có dữ liệu toàn cục, quay lại cách cũ
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.set("page", page);

      // Cập nhật URL với trang mới
      window.location.search = urlParams.toString();
      return false; // Ngăn chặn hành vi mặc định của liên kết
    }

    // Lọc và sắp xếp sản phẩm - Hàm này chỉ giữ lại cho khả năng tương thích, hiện tại không sử dụng
    function sortProducts(sortBy) {
      if (globalProductsData.length > 0) {
        // Nếu đã có dữ liệu, sắp xếp tại client
        const searchResultsContainer = document.getElementById("searchResults");
        if (searchResultsContainer) {
          updateActiveFilterButton(sortBy);
          const sortedData = sortProductsData(globalProductsData, sortBy);
          displayProducts(sortedData, searchResultsContainer);
          updateUrlWithSort(sortBy);
        }
      } else {
        // Hàm cũ - giữ lại cho tương thích
        const urlParams = new URLSearchParams(window.location.search);

        if (sortBy === "default") {
          urlParams.delete("sort");
        } else {
          urlParams.set("sort", sortBy);
        }

        urlParams.delete("page"); // Quay về trang 1 khi thay đổi sắp xếp

        // Cập nhật URL với sắp xếp mới
        window.location.search = urlParams.toString();
      }
    }
  </script>
</body>

</html>