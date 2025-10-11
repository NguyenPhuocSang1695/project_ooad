<!DOCTYPE html>
<?php
session_start();
require_once('../src/php/token.php');
require_once('../src/php/check_token_v1.php');
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  require_once('../src/php/connect.php');

  // Lấy dữ liệu từ form
  $fullname = $_POST['fullname'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $province = $_POST['province'];
  $district = $_POST['district'];
  $ward = $_POST['wards'];
  $address = $_POST['address'];
  $password = $_POST['password'];
  $confirmPassword = $_POST['confirm-password'];

  // Kiểm tra trùng username
  $checkUsername = $conn->prepare("SELECT UserName FROM users WHERE UserName = ?");
  $checkUsername->bind_param("s", $username);
  $checkUsername->execute();
  $checkUsername->store_result();
  if ($checkUsername->num_rows > 0) {
    $errors['username'] = "Tên đăng nhập đã tồn tại!";
  }
  $checkUsername->close();

  // Kiểm tra trùng email
  $checkEmail = $conn->prepare("SELECT Email FROM users WHERE Email = ?");
  $checkEmail->bind_param("s", $email);
  $checkEmail->execute();
  $checkEmail->store_result();
  if ($checkEmail->num_rows > 0) {
    $errors['email'] = "Email đã tồn tại!";
  }
  $checkEmail->close();

  // Các kiểm tra đầu vào
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Email không hợp lệ!";
  }
  if (!preg_match("/^[a-z0-9_-]{3,16}$/", $username)) {
    $errors['username'] = "Tên đăng nhập không hợp lệ!";
  }
  if (!preg_match("/^0[0-9]{9}$/", $phone)) {
    $errors['phone'] = "Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 số và bắt đầu bằng số 0.";
  }
  if ($password !== $confirmPassword) {
    $errors['confirm-password'] = "Mật khẩu xác nhận không khớp!";
  }
  if (!preg_match("/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*()]).{8,}/", $password)) {
    $errors['password'] = "Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.";
  }
  if (!preg_match('/^([\p{L}]+(?:\s[\p{L}]+){0,79})$/u', $fullname)) {
    $errors['fullname'] = "Chỉ được chứa chữ cái và tối đa 80 ký tự.";
  }
  if (empty($province)) {
    $errors['province'] = "Không được để trống tỉnh/thành!";
  }
  if (empty($district)) {
    $errors['district'] = "Không được để trống quận/huyện!";
  }
  if (empty($ward)) {
    $errors['ward'] = "Không được để trống phường/xã!";
  }
  if (empty($address)) {
    $errors['address'] = "Không được để trống địa chỉ!";
  }

  if (!empty($province)) {
    $stmt = $conn->prepare("SELECT district_id, name FROM district WHERE province_id = ?");
    $stmt->bind_param("i", $province);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $districts[] = $row;
    }
    $stmt->close();
  }

  if (!empty($district)) {
    $stmt = $conn->prepare("SELECT wards_id, name FROM wards WHERE district_id = ?");
    $stmt->bind_param("i", $district);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $wards[] = $row;
    }
    $stmt->close();
  }
  // Nếu không có lỗi thì thêm vào CSDL
  if (empty($errors)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (FullName, UserName, Email, Phone, Province, District, Ward, Address, PasswordHash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $fullname, $username, $email, $phone, $province, $district, $ward, $address, $passwordHash);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Chuyển trang sau khi đăng ký thành công
    header("Location: user-login.php");
    exit;
  }
  $conn->close();
}
// Kết nối để load danh sách tỉnh/thành (đặt ở cuối để luôn chạy được cả GET)
$conn = new mysqli("localhost", "root", "", "c01db");
if ($conn->connect_error) {
  die("Kết nối thất bại: " . $conn->connect_error);
}


$provinceQuery = "SELECT province_id, name FROM province ORDER BY name ASC";
$provinceResult = $conn->query($provinceQuery);

$cart_count = 0;

if (isset($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['Quantity'];
  }
}
// Kiểm tra giỏ hàng
$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Cập nhật giá & ẩn/sửa giỏ hàng theo database mới nhất
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
  $cart_product_ids = array_column($_SESSION['cart'], 'ProductID');
  $placeholders = implode(',', array_fill(0, count($cart_product_ids), '?'));
  // Lấy luôn Price và Status
  $sql = "SELECT ProductID, Price, Status 
          FROM products 
          WHERE ProductID IN ($placeholders)";
  require_once '../php-api/connectdb.php';
  $conn = connect_db();
  $stmt = $conn->prepare($sql);
  if ($stmt) {
    $stmt->bind_param(str_repeat('i', count($cart_product_ids)), ...$cart_product_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $price_map = [];
    $status_map = [];
    while ($row = $result->fetch_assoc()) {
      $price_map[$row['ProductID']] = $row['Price'];
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

<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- CSS  -->
  <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../src/css/user-sanpham.css" />

  <link rel="stylesheet" href="../src/css/user-register.css">
  <link rel="stylesheet" href="../assets/icon/fontawesome-free-6.7.2-web/css/all.min.css">
  <link rel="stylesheet" href="../src/css/search-styles.css">
  <link rel="stylesheet" href="../src/css/searchAdvanceMobile.css">
  <link rel="stylesheet" href="../src/css/footer.css">
  <!-- JS  -->
  <script src="../src/js/search-common.js"></script>
  <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script></script>
  <!-- <script src="../src/js/main.js"></script> -->
  <script src="../src/js/onOffSeacrhAdvance.js"></script>
  <script src="../src/js/search-index.js"></script>
  <title>Đăng ký</title>
  <style>
    /* hiện lỗi */
    .error-message {
      color: red;
      font-size: 12px;
      margin-top: 5px;
    }

    .container1 {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      max-width: 900px;
      /* Điều chỉnh kích thước theo nhu cầu */
      margin: auto;
      margin-top: 35px;
    }

    .form-card {
      flex: 1;
      width: 50%;
      box-sizing: border-box;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .form-card {
        width: 100%;
      }
    }

    .form-group label {
      font-weight: bold;
    }
  </style>
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
              <form id="searchForm" class="search-form" method="get">
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
                window.location.href = "./search-result.php?q=" + encodeURIComponent(searchInput);
              });
            </script>
            <div class="cart-wrapper">
              <div class="cart-icon">
                <a href="gio-hang.php">
                  <img src="../assets/images/cart.svg" alt="cart" />
                  <span class="cart-count" id="mni-cart-count"
                    style="position: absolute; margin-top: -10px; background-color: red; color: white; border-radius: 50%; padding: 2px 5px; font-size: 12px;">
                    <?php echo $cart_count; ?>
                  </span>
                </a>
              </div>
              <div class="cart-dropdown">
                <?php if (count($cart_items) > 0): ?>
                  <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                      <img src="<?php echo ".." . $item['ImageURL']; ?>" alt="<?php echo $item['ProductName']; ?>"
                        class="cart-thumb" />
                      <div class="cart-item-details">
                        <h5><?php echo $item['ProductName']; ?></h5>
                        <p>Giá: <?php echo number_format($item['Price'], 0, ',', '.') . " VNĐ"; ?></p>
                        <p><?php echo $item['Quantity']; ?> × <?php echo number_format($item['Price'], 0, ',', '.'); ?>VNĐ
                        </p>
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
      <a href="#"><span>Đăng ký</span></a>
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
  <!-- MAIN -->
  <div class="container1">
    <div class="form-card">
      <div class="form-image">
        <h2>Tham gia ngay với chúng tôi</h2>
        <p>Tạo tài khoản để trải nghiệm các tính năng tuyệt vời:</p>
        <ul class="feature-list">
          <li>Giao diện trực quan, dễ sử dụng</li>
          <li>Bảo mật thông tin tuyệt đối</li>
          <li>Hỗ trợ khách hàng 24/7</li>
        </ul>
      </div>

      <div class="form-card">
        <div class="form-content">
          <div class="form-header">
            <h1>Tạo tài khoản mới</h1>
            <p style="font-weight: bold">Điền thông tin dưới đây để bắt đầu</p>
          </div>
          <form method="POST" action="">
            <div class="form-row">
              <div class="form-group">
                <label for="fullname">Họ và tên</label>
                <input type="text" id="fullname" name="fullname" class="form-control" required
                  value="<?php echo isset($errors['fullname']) ? '' : htmlspecialchars($_POST['fullname'] ?? ''); ?>">
                <p class="error-message"><?php echo $errors['fullname'] ?? ''; ?></p>
              </div>
              <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" class="form-control" required
                  value="<?php echo isset($errors['username']) ? '' : htmlspecialchars($_POST['username'] ?? ''); ?>">
                <p class="error-message"><?php echo $errors['username'] ?? ''; ?></p>
              </div>
            </div>

            <div class="form-group">
              <label for="email">Địa chỉ email</label>
              <input type="text" id="email" required name="email" class="form-control"
                value="<?php echo isset($errors['email']) ? '' : htmlspecialchars($_POST['email'] ?? ''); ?>">
              <p class="error-message"><?php echo $errors['email'] ?? ''; ?></p>
            </div>

            <div class="form-group">
              <label for="phone">Số điện thoại</label>
              <input type="text" id="phone" name="phone" class="form-control" required
                value="<?php echo isset($errors['phone']) ? '' : htmlspecialchars($_POST['phone'] ?? ''); ?>">

              <p class="error-message"><?php echo $errors['phone'] ?? ''; ?></p>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="province">Tỉnh/Thành phố</label>
                <select id="province" name="province" class="form-control">
                  <option value="">Chọn một tỉnh</option>
                  <?php
                  if ($provinceResult->num_rows > 0) {
                    while ($row = $provinceResult->fetch_assoc()) {
                      $selected = (isset($_POST['province']) && $_POST['province'] == $row['province_id']) ? 'selected' : '';
                      echo '<option value="' . $row['province_id'] . '" ' . $selected . '>' . $row['name'] . '</option>';
                    }
                  }
                  ?>
                </select>
                <p class="error-message"><?php echo $errors['province'] ?? ''; ?></p>
              </div>

              <div class="form-group">
                <label for="district">Quận/Huyện</label>
                <select id="district" name="district" class="form-control">
                  <option value="">Chọn quận/huyện</option>
                  <?php
                  foreach ($districts as $district) {
                    $selected = (isset($_POST['district']) && $_POST['district'] == $district['district_id']) ? 'selected' : '';
                    echo '<option value="' . $district['district_id'] . '" ' . $selected . '>' . $district['name'] . '</option>';
                  }
                  ?>
                </select>
                <p class="error-message"><?php echo $errors['district'] ?? ''; ?></p>
              </div>
            </div>

            <div class="form-group">
              <label for="wards">Phường/Xã</label>
              <select id="wards" name="wards" class="form-control">
                <option value="">Chọn phường/xã</option>
                <?php
                foreach ($wards as $ward) {
                  $selected = (isset($_POST['wards']) && $_POST['wards'] == $ward['wards_id']) ? 'selected' : '';
                  echo '<option value="' . $ward['wards_id'] . '" ' . $selected . '>' . $ward['name'] . '</option>';
                }
                ?>
              </select>
              <p class="error-message"><?php echo $errors['ward'] ?? ''; ?></p>
            </div>

            <div class="form-group">
              <label for="address">Địa chỉ</label>
              <input type="text" id="address" name="address" class="form-control" required
                value="<?php echo isset($errors['address']) ? '' : htmlspecialchars($_POST['address'] ?? ''); ?>">

              <p class="error-message"><?php echo $errors['address'] ?? ''; ?></p>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <p class="error-message"><?php echo $errors['password'] ?? ''; ?></p>
              </div>
              <div class="form-group">
                <label for="confirm-password">Xác nhận mật khẩu</label>
                <input type="password" id="confirm-password" name="confirm-password" class="form-control" required>
                <p class="error-message"><?php echo $errors['confirm-password'] ?? ''; ?></p>
              </div>
            </div>

            <button type="submit" class="btn">Đăng ký ngay</button>
            <!-- <button type="reset" class="btn btn-reset" onclick="resetForm()">Làm mới</button> -->
            <div class="checkbox-container">
              <p for>Bạn đã có tài khoản? <a href="user-login.php">Đăng nhập</a> </p>
            </div>
          </form>
        </div>
      </div>
      <script>
        function validateForm() {
          var password = document.getElementById("password").value;
          var confirmPassword = document.getElementById("confirm-password").value;
          if (password !== confirmPassword) {
            document.getElementById("confirm-password-error").innerText = "Mật khẩu xác nhận không khớp!";
            return false;
          }
          return true;
        }

        function resetForm() {
          document.querySelector("form").reset(); // Reset các ô nhập liệu

          // Xóa luôn các thông báo lỗi
          let errorMessages = document.querySelectorAll(".error-message");
          errorMessages.forEach(msg => {
            msg.innerText = ""; // Xóa nội dung lỗi
            msg.style.display = "none"; // Ẩn lỗi luôn
          });
        }
      </script>
    </div>
  </div>

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

  <script src="../src/js/user-register.js"></script>
  <script src="../src/js/Trang_chu.js"></script>

  <script src="../src/js/jquery-3.7.1.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#province').on('change', function() {
        var province_id = $(this).val();
        if (province_id) {
          $.ajax({
            url: 'ajax_get_district.php',
            method: 'GET',
            dataType: "json",
            data: {
              province_id: province_id
            },
            success: function(data) {
              $('#district').empty().append('<option value="">Chọn quận/huyện</option>');
              $.each(data, function(i, district) {
                $('#district').append($('<option>', {
                  value: district.id,
                  text: district.name
                }));
              });
              $('#wards').empty().append('<option value="">Chọn phường/xã</option>');
            }
          });
        } else {
          $('#district').empty().append('<option value="">Chọn quận/huyện</option>');
          $('#wards').empty().append('<option value="">Chọn phường/xã</option>');
        }
      });

      $('#district').on('change', function() {
        var district_id = $(this).val();
        if (district_id) {
          $.ajax({
            url: 'ajax_get_wards.php',
            method: 'GET',
            dataType: "json",
            data: {
              district_id: district_id
            },
            success: function(data) {
              $('#wards').empty().append('<option value="">Chọn phường/xã</option>');
              $.each(data, function(i, wards) {
                $('#wards').append($('<option>', {
                  value: wards.id,
                  text: wards.name
                }));
              });
            }
          });
        } else {
          $('#wards').empty().append('<option value="">Chọn phường/xã</option>');
        }
      });
    });
  </script>

</body>

</html>