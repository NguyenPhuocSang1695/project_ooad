<?php
session_start();
require_once('../src/php/token.php');
// require_once('../src/php/check_status.php');
require_once('../src/php/check_status_v2.php');

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
    <link rel="stylesheet" type="text/css" href="../src/css/phan-loai.css" />
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/icon/fontawesome-free-6.7.2-web/css/all.min.css" />
    <link rel="stylesheet" href="../src/css/search-styles.css" />
    <link rel="stylesheet" href="../assets/libs/fontawesome-free-6.6.0-web/css/all.min.css" />
    <link rel="stylesheet" href="../src/css/searchAdvanceMobile.css" />
    <link rel="stylesheet" href="../src/css/user-sanpham.css" />
    <link rel="stylesheet" href="../src/css/brandname.css">
    <link rel="stylesheet" href="../src/css/footer.css">
    <!-- JS  -->
    <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="../src/js/main.js"></script> -->
    <script src="../src/js/search-common.js"></script>
    <script src="../src/js/onOffSeacrhAdvance.js"></script>
    <script src="../src/js/phan-loai.js"></script>
    <script src="../src/js/search-index.js"></script>
    <script src="../src/js/reloadPage.js"></script>
    <title>Phân loại</title>
    <style>
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }

        .product-info {
            margin-top: 15px;
        }

        .product-name {
            margin: 10px 0;
            font-size: 1.1em;
        }

        .product-price {
            color: #1c8e2e;
            font-weight: bold;
            margin: 10px 0;
        }

        .btn-view-product {
            display: inline-block;
            padding: 8px 15px;
            background-color: #1c8e2e;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .btn-view-product:hover {
            background-color: #27ae60;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
            padding: 20px;
        }

        .pagination-item {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination-item.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        .filter-section {
            /* background: #f8f9fa; */
            padding: 15px 0;
            margin-bottom: 20px;
        }

        .sort-wrapper {
            display: flex;
            align-items: center;
            justify-content: right;
            gap: 10px;
        }

        .sort-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
            min-width: 150px;
        }

        .sort-select:hover {
            border-color: #2ecc71;
        }

        .sort-select:focus {
            outline: none;
            border-color: #2ecc71;
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
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
                                window.location.href = "./search-result.php?q=" + encodeURIComponent(searchInput);
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
                                                <p>Giá: <?php echo number_format($item['Price'], 0, ',', '.') . " VND"; ?></p>
                                                <p><?php echo $item['Quantity']; ?> × <?php echo number_format($item['Price'], 0, ',', '.'); ?>VND</p>
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
            <a href="#"><span>Tất cả sản phẩm</span></a>
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
        <div class="container">
            <div class="filter-section">
                <div class="container">
                    <div class="sort-wrapper">
                        <label for="sort-select">Sắp xếp theo:</label>
                        <select id="sort-select" class="sort-select" onchange="sortProducts(this.value)">
                            <option value="default">Mặc định</option>
                            <option value="name-asc">Tên A-Z</option>
                            <option value="name-desc">Tên Z-A</option>
                            <option value="price-asc">Giá tăng dần</option>
                            <option value="price-desc">Giá giảm dần</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php
            require_once('../php-api/connectdb.php');

            // Số sản phẩm mỗi trang
            $itemsPerPage = 8;

            // Lấy trang hiện tại
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);

            // Lấy kiểu sắp xếp
            $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'default';

            // Tính vị trí bắt đầu
            $offset = ($page - 1) * $itemsPerPage;

            // Kết nối database
            $conn = connect_db();

            // Xây dựng câu query với ORDER BY tùy theo sortBy
            $orderBy = "ProductID DESC"; // Mặc định
            switch ($sortBy) {
                case 'name-asc':
                    $orderBy = "ProductName ASC";
                    break;
                case 'name-desc':
                    $orderBy = "ProductName DESC";
                    break;
                case 'price-asc':
                    $orderBy = "Price ASC";
                    break;
                case 'price-desc':
                    $orderBy = "Price DESC";
                    break;
            }

            // Đếm tổng số sản phẩm
            $countQuery = "SELECT COUNT(*) as total FROM products WHERE Status != 'hidden'";
            $countResult = $conn->query($countQuery);
            $totalItems = $countResult->fetch_assoc()['total'];

            // Tính tổng số trang
            $totalPages = ceil($totalItems / $itemsPerPage);

            // Query lấy sản phẩm cho trang hiện tại với sắp xếp
            $query = "SELECT * FROM products WHERE Status != 'hidden' ORDER BY $orderBy LIMIT ?, ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $offset, $itemsPerPage);
            $stmt->execute();
            $result = $stmt->get_result();

            // Hiển thị grid sản phẩm
            echo '<div class="all_product">';
            echo '<div class="products-grid">';

            while ($product = $result->fetch_assoc()) {
                echo '<div class="product-card">';
                echo '<div class="product-image">';
                echo '<img src="..' . $product['ImageURL'] . '" alt="' . htmlspecialchars($product['ProductName']) . '">';
                echo '</div>';
                echo '<div class="product-info">';
                echo '<h3 class="product-name" style="font-weight: bold;">' . htmlspecialchars($product['ProductName']) . '</h3>';
                echo '<div class="product-price">' . number_format($product['Price'], 0, ',', '.') . ' VND</div>';
                echo '<a href="user-sanpham.php?id=' . $product['ProductID'] . '" class="btn btn-success">Xem chi tiết</a>';
                echo '</div>';
                echo '</div>';
            }

            echo '</div>'; // Đóng products-grid
            echo '</div>'; // Đóng all_product

            // Hiển thị phân trang
            if ($totalPages > 1) {
                echo '<div class="pagination" id="pagination">';

                // Trong phần hiển thị phân trang, thay đổi các href của link phân trang
                $sortParam = isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '';

                // Nút Previous
                $prevPage = max(1, $page - 1);
                echo '<a href="?page=' . $prevPage . $sortParam . '" class="btn btn-secondary pagination-item ' . ($page == 1 ? 'disabled' : '') . '">';
                echo '<i class="fas fa-chevron-left"></i>';
                echo '</a>';

                // Hiển thị các số trang
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                if ($startPage > 1) {
                    echo '<a href="?page=1' . $sortParam . '" class="btn btn-secondary pagination-item">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="btn btn-secondary pagination-ellipsis">...</span>';
                    }
                }

                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = ($i == $page) ? 'btn-success' : 'btn-secondary';
                    echo '<a href="?page=' . $i . $sortParam . '" class="btn pagination-item ' . $activeClass . '">' . $i . '</a>';
                }

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="btn btn-secondary pagination-ellipsis">...</span>';
                    }
                    echo '<a href="?page=' . $totalPages . $sortParam . '" class="btn btn-secondary pagination-item">' . $totalPages . '</a>';
                }

                // Nút Next
                $nextPage = min($totalPages, $page + 1);
                echo '<a href="?page=' . $nextPage . $sortParam . '" class="btn btn-secondary pagination-item ' . ($page == $totalPages ? 'disabled' : '') . '">';
                echo '<i class="fas fa-chevron-right"></i>';
                echo '</a>';
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>
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
        function sortProducts(sortBy) {
            // Lấy URL hiện tại
            let url = new URL(window.location.href);

            // Cập nhật hoặc thêm tham số sort
            url.searchParams.set('sort', sortBy);

            // Đặt lại trang về 1 khi thay đổi sắp xếp
            url.searchParams.set('page', '1');

            // Chuyển hướng đến URL mới với tham số sắp xếp
            window.location.href = url.toString();
        }

        // Đặt giá trị select box khi load trang
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const sortValue = urlParams.get('sort') || 'default';
            document.getElementById('sort-select').value = sortValue;
        });
    </script>
</body>

</html>