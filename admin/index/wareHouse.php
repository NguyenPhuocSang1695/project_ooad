<?php
require_once '../php/connect.php';
require_once '../php/ProductManager.php';
$db = new DatabaseConnection();
$db->connect();
global $db;
$mysqli = $db->getConnection();
global $mysqli;

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kho hàng</title>

  <link href="../style/main_warehouse.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="../style/main.css" rel="stylesheet">
  <link href="../style/LogInfo.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/responsiveWareHouse.css">
  <link rel="stylesheet" href="../style/warehouse-pagination.css">
  <link rel="stylesheet" href="../style/wareHouse.css">
  <link rel="stylesheet" href="../style/wareHouseFilter.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <div class="header">
    <div class="index-menu">
      <i class="fa-solid fa-bars" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample"></i>
      <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div style="border-bottom: 1px solid rgb(176, 176, 176);" class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasExampleLabel">Mục lục</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="homePage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-house" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Tổng quan</p>
            </div>
          </a>
          <a href="wareHouse.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection" style="background-color: #6aa173;">
                <i class="fa-solid fa-warehouse" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Kho hàng</p>
            </div>
          </a>
          <a href="customer.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-users" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p style="color: black; text-align: center; font-size: 10x;">Người dùng</p>
            </div>
          </a>
          <a href="orderPage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-list-check" style="font-size: 18px; color: #FAD4AE;"></i>
              </button>
              <p style="color:black">Đơn hàng</p>
            </div>
          </a>
          <a href="importReceipt.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-file-import" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Nhập hàng</p>
            </div>
          </a>
          <a href="analyzePage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-chart-simple" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Thống kê</p>
            </div>
          </a>
          <a href="supplier.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-truck-field" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Nhà cung cấp</p>
            </div>
          </a>
          <a href="voucherManage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-ticket" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Mã giảm giá</p>
            </div>
          </a>
          <a href="voucherManage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-ticket" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Mã giảm giá</p>
            </div>
          </a>
          <a href="accountPage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-circle-user" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p style="color:black">Tài khoản</p>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="header-left-section">
      <p class="header-left-title">Kho hàng</p>
    </div>
    <div class="header-middle-section">
      <img class="logo-store" src="../../assets/images/LOGO-2.jpg">
    </div>
    <div class="header-right-section">
      <div class="bell-notification">
        <i class="fa-regular fa-bell" style="color: #64792c; font-size: 45px; width:100%;"></i>
      </div>
      <div>
        <div class="position-employee">
          <p id="employee-role">Chức vụ</p>
        </div>
        <div class="name-employee">
          <p id="employee-name">Ẩn danh</p>
        </div>
      </div>
      <div>
        <img class="avatar" src="../../assets/images/sang.jpg" alt="" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
      </div>
      <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
        aria-labelledby="offcanvasWithBothOptionsLabel">
        <div style="border-bottom: 1px solid rgb(176, 176, 176);" class="offcanvas-header">
          <img class="avatar" src="../../assets/images/sang.jpg" alt="">
          <div class="admin">
            <h4 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">Username</h4>
            <h5 id="employee-displayname">Họ tên</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <a href="accountPage.php" class="navbar_user">
            <i class="fa-solid fa-user"></i>
            <p>Thông tin cá nhân</p>
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

  <div class="side-bar">
    <div class="backToHome">
      <a href="homePage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection" style="margin-top: 35px;">
            <i class="fa-solid fa-house" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Tổng quan</p>
        </div>
      </a>
    </div>
    <a href="wareHouse.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection" style="background-color: #6aa173;">
          <i class="fa-solid fa-warehouse" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Kho hàng</p>
      </div>
    </a>
    <a href="customer.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-users" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Người dùng</p>
      </div>
    </a>
    <a href="orderPage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-list-check" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Đơn hàng</p>
      </div>
    </a>
    <a href="importReceipt.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-file-import" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Nhập hàng</p>
      </div>
    </a>
    <a href="analyzePage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-chart-simple" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Thống kê</p>
      </div>
    </a>
    <a href="supplierManage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-truck-field" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Nhà cung cấp</p>
      </div>
    </a>
    <a href="voucherManage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-ticket" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Mã giảm giá</p>
      </div>
    </a>
    <a href="accountPage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-circle-user" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Tài khoản</p>
      </div>
    </a>
  </div>

  <!-- MAIN   -->
  <div class="container-main-warehouse">
    <div class="warehouse-management">
      <form method="get" class="search-container">
        <input class="search-input" id="search-input" type="text" placeholder="Tìm kiếm sản phẩm...">
        <button type="submit" class="search-btn" id="search-btn">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>

      <p id="searchResultText" style="display: none; margin-top: 10px; font-style: italic; color: #555;"></p>

      <div class="filters-container">
        <div class="filters-row">
          <!-- Danh mục -->
          <div class="filter-group">
            <label for="categoryFilter">Danh mục:</label>
            <select id="categoryFilter" class="filter-select">
              <option value="all">Tất cả</option>
              <?php
              $sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryName ASC";
              $result = $mysqli->query($sql);
              if ($result) {
                while ($row = $result->fetch_assoc()) {
                  echo "<option value='{$row['CategoryID']}'>{$row['CategoryName']}</option>";
                }
              }
              ?>
            </select>
          </div>

          <!-- Trạng thái -->
          <div class="filter-group">
            <label for="statusFilter">Trạng thái:</label>
            <select id="statusFilter" class="filter-select">
              <option value="all">Tất cả</option>
              <option value="appear">Đang hiện</option>
              <option value="hidden">Đã ẩn</option>
              <option value="out_of_stock">Hết hàng</option>
              <option value="near_out_of_stock">Sắp hết hàng (số lượng ít hơn 5)</option>
            </select>
          </div>

          <!-- Giá từ -->
          <div class="filter-group">
            <label for="priceMin">Giá từ:</label>
            <input type="number" id="priceMin" class="filter-input" placeholder="0" min="0" step="1000">
          </div>

          <!-- Giá đến -->
          <div class="filter-group">
            <label for="priceMax">Giá đến:</label>
            <input type="number" id="priceMax" class="filter-input" placeholder="0" min="0" step="1000">
          </div>

          <!-- Sắp xếp -->
          <div class="filter-group">
            <label for="sortBy">Sắp xếp:</label>
            <select id="sortBy" class="filter-select">
              <option value="name_asc">Tên A-Z</option>
              <option value="name_desc">Tên Z-A</option>
              <option value="price_asc">Giá tăng dần</option>
              <option value="price_desc">Giá giảm dần</option>
              <option value="newest">Mới nhất</option>
              <option value="oldest">Cũ nhất</option>
            </select>
          </div>

          <!-- Nút reset -->
          <div class="filter-group">
            <button id="resetFilters" class="btn-reset-filters">
              <i class="fa-solid fa-rotate-right"></i> Reset
            </button>
          </div>
        </div>
      </div>

      <style>

      </style>

      <div class="management-content">
        <div class="products-section">
          <div class="section-header">
            <h2 class="section-title">Quản Lý Kho Hàng</h2>
            <button class="btn btn-success add-product-btn" id="add-product-btn" onclick="showAddProductOverlay()">Thêm Sản Phẩm</button>
          </div>

          <table class="products-table" id="productsTable">
            <thead>
              <tr>
                <th>Ảnh</th>
                <th style="text-align: center;">Tên sản phẩm</th>
                <th style="text-align: center;">Danh mục</th>
                <th style="text-align: center;">Giá (VND)</th>
                <th style="text-align: center;"></th>
              </tr>
            </thead>

            <!-- Body + pagination do renderProducts() xuất ra -->
            <?php
            $controller = new ProductManager();
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            echo $controller->renderProducts($page);
            ?>

            <tbody id="productsBody">
              <!-- Nội dung được load bằng JS -->
            </tbody>
          </table>

          <!-- <div id="pagination" class="pagination" style="display: none"></div> -->

          <!-- Main pagination (server-rendered by ProductManager->renderProducts) -->
          <div id="pagination-search" style="text-align:center; margin-top:20px;"></div>

        </div>
      </div>
    </div>


    <!-- Popup overlay cho thông tin sản phẩm
    <div class="product-details-overlay" id="productDetailsOverlay">
      <div class="product-details-content" id="productDetailsContent"></div>
    </div> -->

    <!-- Popup overlay cho add product-->
    <div class="add-product-overlay" id="addProductOverlay">
      <div class="add-product-content">
        <!-- Đóng form  -->
        <button type="button" id="closeButton" class="btn btn-secondary"
          style="margin: 0 0 10px 0;width: 30px; height: 30px; display: flex; justify-content: center; align-items: center;"
          id="closeButton"><i class="fa-solid fa-xmark"></i></button>
        <!-- Form thêm sản phẩm  -->
        <div class="card">
          <h2>Thêm Sản Phẩm</h2>
          <form id="productForm" method="POST" enctype="multipart/form-data">

            <div class="form-group">
              <label for="productName">Tên sản phẩm(*)</label>
              <input type="text" id="productName" name="productName" required placeholder="Nhập tên sản phẩm">
            </div>

            <div class="form-group">
              <label for="categoryID">Danh mục(*)</label>
              <select id="categoryID" name="categoryID" required>
                <?php
                // Truy vấn lấy danh mục
                $sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryID ASC";
                $result = $mysqli->query($sql);

                if ($result && $result->num_rows > 0) {
                  // Lặp qua từng danh mục và hiển thị
                  while ($row = $result->fetch_assoc()) {
                    $categoryID = htmlspecialchars($row['CategoryID']);
                    $categoryName = htmlspecialchars($row['CategoryName']);
                    echo "<option value='$categoryID'>$categoryName</option>";
                  }
                } else {
                  // Nếu không có danh mục
                  echo "<option value=''>Không có danh mục</option>";
                }
                ?>
              </select>
            </div>

            <!-- Nhà cung cấp  -->
            <div class="form-group">
              <label for="SupplierAddProduct" class="form-label">Nhà cung cấp(*)</label>
              <select class="form-control" id="SupplierAddProduct" name="SupplierAddProduct" required>
                <option value="" disabled>-- Chọn nhà cung cấp --</option>
                <?php
                $sql = "select supplier_id, supplier_name from suppliers";
                $result = $mysqli->query($sql);
                $suppliers = [];
                while ($row = $result->fetch_assoc()) {
                  $suppliers[] = $row;
                }
                ?>
                <?php foreach ($suppliers as $supplier): ?>
                  <option value="<?= $supplier['supplier_id'] ?>">
                    <?= htmlspecialchars($supplier['supplier_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Số Lượng  -->
            <div class="form-group">
              <label for="priceAddProduct">Số lượng(*)</label>
              <input type="number" id="priceAddProduct" name="priceAddProduct" required placeholder="Nhập giá sản phẩm" min="0">
            </div>

            <div class="form-group">
              <label for="price">Giá(*)</label>
              <input type="number" id="price" name="price" required placeholder="Nhập giá sản phẩm" min="0">
            </div>

            <div class="form-group">
              <label for="description">Mô tả(*)</label>
              <textarea id="description" name="description" required placeholder="Công dụng, cách chăm sóc, nguồn gốc, ..."></textarea>
            </div>

            <div class="form-group">
              <label for="imageURL">Ảnh sản phẩm(*)</label>
              <input type="file" id="imageURL" name="imageURL" required accept=".jpg ,.jpeg,.png,.gif">
              <p class="category-note">Chọn ảnh sản phẩm (PNG, JPG, JPEG, GIF)</p> <br>
              <p class="category-note">Kích thước tối đa: 2MB</p><br>
              <p class="category-note">Kích thước tối thiểu: 300x300px</p><br>
              <img id="imagePreview" class="image-preview" src="#" alt="Preview image" style="display:none;">
            </div>

            <button type="submit" class="btn btn-success">Thêm Sản Phẩm</button>
          </form>


          <!-- Hiển thị ảnh trước khi upload   -->
          <script>
            document.getElementById('imageURL').addEventListener('change', function(event) {
              const file = event.target.files[0];

              // Kiểm tra nếu không có file
              if (!file) return;

              // Kiểm tra dung lượng (<= 2MB)
              const maxSize = 2 * 1024 * 1024; // 2MB
              if (file.size > maxSize) {
                alert("Ảnh vượt quá kích thước tối đa 2MB!");
                event.target.value = ""; // Reset input
                document.getElementById('imagePreview').style.display = 'none';
                return;
              }

              // Nếu hợp lệ, hiển thị ảnh preview
              const reader = new FileReader();
              reader.onload = function() {
                const imagePreview = document.getElementById('imagePreview');
                imagePreview.style.display = 'block';
                imagePreview.src = reader.result;
              };
              reader.readAsDataURL(file);
            });
            // Hàm hiển thị overlay
            function showAddProductOverlay() {
              const overlay = document.getElementById("addProductOverlay");
              if (overlay) {
                overlay.style.display = "flex";
              }
            }
            document.getElementById('closeButton').addEventListener('click', function() {
              const overlay = document.getElementById('addProductOverlay');
              if (overlay.style.display === 'flex') {
                overlay.style.display = 'none'; // Ẩn overlay khi nhấn nút đóng
              }
            });
          </script>

        </div>
      </div>
    </div>
  </div>

  <!-- Edit Product Overlay -->
  <div class="product-details-overlay" id="editProductOverlay">
    <div class="product-details-content">
      <button type="button" class="close-btn btn-secondary" onclick="closeEditOverlay()">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="card">
        <div class="card-body">
          <h3 class="card-title mb-4">Chỉnh sửa sản phẩm</h3>
          <form id="editProductForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="editProductId" name="productId">

            <div class="row">
              <div class="col-md-4">
                <div class="image-preview-container mb-3">
                  <img id="currentImage" class="img-fluid mb-2" src="#" alt="Current image">
                  <div class="mb-3">
                    <label for="editImageURL" class="form-label">Thay đổi ảnh</label>
                    <input type="file" class="form-control" id="editImageURL" name="imageURL" accept=".jpg,.jpeg,.png">
                    <p class="category-note">Chọn ảnh sản phẩm (PNG, JPG, JPEG)</p>
                    <p class="category-note">Kích thước tối đa: 2MB</p>
                  </div>
                </div>
              </div>

              <div class="col-md-8">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="editProductName" class="form-label">Tên sản phẩm</label>
                    <input type="text" class="form-control" id="editProductName" name="productName" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="editCategoryID" class="form-label">Danh mục</label>
                    <select class="form-control" id="editCategoryID" name="categoryID" required>
                      <option value="" disabled>-- Chọn danh mục --</option>
                      <?php
                      // Truy vấn danh sách category
                      $sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryID ASC";
                      $result = $mysqli->query($sql);

                      if ($result) {
                        while ($row = $result->fetch_assoc()) {
                          // Nếu muốn chọn sẵn category đang có của sản phẩm
                          $selected = (isset($product) && $product['CategoryID'] == $row['CategoryID']) ? 'selected' : '';
                          echo '<option value="' . $row['CategoryID'] . '" ' . $selected . '>' . htmlspecialchars($row['CategoryName']) . '</option>';
                        }
                      } else {
                        echo '<option value="">Không có danh mục</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="editPrice" class="form-label">Giá (VND)</label>
                    <input type="number" class="form-control" id="editPrice" name="price" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="editStatus" class="form-label">Trạng thái</label>
                    <select class="form-control" id="editStatus" name="status" required>
                      <option value="appear">Hiện</option>
                      <option value="hidden">Ẩn</option>
                    </select>
                  </div>
                </div>

                <div class="row">
                  <!-- Nhà cung cấp -->
                  <div class="col-md-6 mb-3">
                    <label for="editSupplier" class="form-label">Nhà cung cấp</label>
                    <select class="form-control" id="editSupplier" name="supplierID" required>
                      <option value="" disabled>-- Chọn nhà cung cấp --</option>
                      <?php
                      $sql = "select supplier_id, supplier_name from suppliers";
                      $result = $mysqli->query($sql);
                      $suppliers = [];
                      while ($row = $result->fetch_assoc()) {
                        $suppliers[] = $row;
                      }
                      ?>
                      <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= $supplier['supplier_id'] ?>">
                          <?= htmlspecialchars($supplier['supplier_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Số lượng sản phẩm -->
                  <div class="col-md-6 mb-3">
                    <label for="editQuantity" class="form-label">Số lượng</label>
                    <input type="number" class="form-control" id="editQuantity" name="quantity" value="<?= htmlspecialchars($product['Quantity']) ?>" min="0">
                  </div>
                </div>

                <div class="mb-3">
                  <label for="editDescription" class="form-label">Mô tả</label>
                  <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                </div>
              </div>
            </div>

            <div class="form-actions text-end mt-3 d-flex">
              <button type="submit" class="btn btn-primary btn-sm me-2">Lưu thay đổi</button>
              <button type="button" class="btn btn-secondary btn-sm me-2" onclick="closeEditOverlay()">Hủy</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  </div>

  <script src="./asset/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../js/add-product.js"></script>
  <script src="../js/checklog.js"></script>

  <script>
    // Hiện phần chỉnh sửa sản phẩm
    function editProduct(productId) {
      fetch(`../php/get-product.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
          //Hiển thị thông tin của sản phẩm từ database lên popup chỉnh sửa (placeholder)
          document.getElementById('editProductId').value = product.ProductID;
          document.getElementById('editProductName').value = product.ProductName;
          document.getElementById('editCategoryID').value = product.CategoryID;
          document.getElementById('editPrice').value = product.Price;
          document.getElementById('editDescription').value = product.Description;
          document.getElementById('editStatus').value = product.Status;
          document.getElementById('editQuantity').value = product.quantity_in_stock;
          document.getElementById('editSupplier').value = product.Supplier_id;

          const currentImage = document.getElementById('currentImage');
          currentImage.src = '../../' + product.ImageURL;
          currentImage.style.display = 'block';

          document.getElementById('editProductOverlay').style.display = 'flex';
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Có lỗi khi tải thông tin sản phẩm!');
        });
    }

    function closeEditOverlay() {
      document.getElementById('editProductOverlay').style.display = 'none';
    }

    // Preview image before upload
    document.getElementById('editImageURL').addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (!file) return;

      const maxSize = 2 * 1024 * 1024; // 2MB
      if (file.size > maxSize) {
        alert('Ảnh không được vượt quá 2MB');
        this.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('currentImage').src = e.target.result;
      }
      reader.readAsDataURL(file);
    });

    // Xử lí form chỉnh sửa sản phẩm 
    (function() {
      const editForm = document.getElementById('editProductForm');
      if (!editForm) return;

      editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        fetch('../php/update-product.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Cập nhật sản phẩm thành công!');
              window.location.reload();
            } else {
              throw new Error(data.message || 'Có lỗi xảy ra khi cập nhật sản phẩm');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra: ' + error.message);
          })
          .finally(() => {
            if (submitButton) submitButton.disabled = false;
          });
      });
    })();

    function confirmDelete(productId) {
      // Sử dụng tham số productId truyền vào thay vì lấy từ DOM
      if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')) {
        fetch('../php/delete-product.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              productId: productId // Dùng tham số truyền vào
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'deleted' || data.status === 'hidden') {
              alert(data.message);
              closeEditOverlay();
              location.reload();
            } else {
              throw new Error(data.message || 'Có lỗi xảy ra khi xóa sản phẩm');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra: ' + error.message);
          });
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const cachedUserInfo = localStorage.getItem('userInfo');
      if (cachedUserInfo) {
        const userInfo = JSON.parse(cachedUserInfo);
        document.querySelector('.name-employee p').textContent = userInfo.fullname;
        document.querySelector('.position-employee p').textContent = userInfo.role;
        document.querySelectorAll('.avatar').forEach(img => img.src = userInfo.avatar);
      }
    });

    // Initialize currentPage from server (so server-side ?page=X is respected)
    function loadProducts(page = 1) {
      const keyword = (document.getElementById('search-input') || {
        value: ''
      }).value.trim();
      const category = document.getElementById('categoryFilter').value;
      const status = document.getElementById('statusFilter').value;
      const priceMin = document.getElementById('priceMin').value;
      const priceMax = document.getElementById('priceMax').value;
      const sortBy = document.getElementById('sortBy').value;

      let url = `../php/filter-sort-product.php?page=${page}`;
      if (keyword) url += `&keyword=${encodeURIComponent(keyword)}`;
      if (category && category !== 'all') url += `&category=${category}`;
      if (status && status !== 'all') url += `&status=${status}`;
      if (priceMin) url += `&price_min=${priceMin}`;
      if (priceMax) url += `&price_max=${priceMax}`;
      url += `&sort_by=${sortBy}`;

      const hasFilters = keyword || (category && category !== 'all') ||
        (status && status !== 'all') || priceMin || priceMax;

      // Ẩn server-side pagination khi có filter
      const tfoot = document.querySelector('#productsTable tfoot');
      if (tfoot) {
        tfoot.style.display = hasFilters ? 'none' : '';
      }

      fetch(url)
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById('productsBody');
          tbody.innerHTML = '';

          if (data.success && data.products && data.products.length > 0) {
            data.products.forEach(p => {


              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td><img src="../..${p.ImageURL}" alt="${p.ProductName}" style="width:100px;height:100px;object-fit:cover;"></td>
                <td style="text-align: center;">${p.ProductName}</td>
                <td style="text-align: center;">${p.CategoryName}</td>
                <td style="text-align: center;">${Number(p.Price).toLocaleString('vi-VN')}</td>
                <td class="actions d-flex" style="text-align: center;">
                  <button class="btn btn-primary btn-sm me-2" onclick="editProduct(${p.ProductID})" style="margin-right: 8px;">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>
                  <button type="button" class="btn btn-danger btn-sm me-2" onclick="confirmDelete(${p.ProductID})">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </td>
                `;
              tbody.appendChild(tr);
            });
          } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:30px;">
          <i class="fa-solid fa-box-open" style="font-size:48px;color:#ccc;margin-bottom:10px;"></i>
          <p>Không tìm thấy sản phẩm nào</p>
        </td></tr>`;
          }

          // Chỉ render pagination khi có filter
          const paginationSearch = document.getElementById('pagination-search');
          if (paginationSearch) {
            paginationSearch.innerHTML = '';

            if (hasFilters && data.pagination && data.pagination.totalPages > 1) {
              renderPagination(paginationSearch, data.pagination);
            }
          }
        })
        .catch(err => {
          console.error('Error loading products:', err);
          alert('Có lỗi xảy ra khi tải sản phẩm!');
        });
    }

    // Tách hàm render pagination ra để code gọn hơn
    function renderPagination(container, pagination) {
      // Previous button
      const prevBtn = document.createElement('a');
      prevBtn.href = '#';
      prevBtn.className = `pagination-item ${pagination.currentPage === 1 ? 'disabled' : ''}`;
      prevBtn.innerHTML = '&lt;';
      prevBtn.onclick = (e) => {
        e.preventDefault();
        if (pagination.currentPage > 1) {
          currentPage = pagination.currentPage - 1;
          loadProducts(currentPage);
        }
      };
      container.appendChild(prevBtn);

      // First page
      if (pagination.currentPage > 2) {
        const firstBtn = document.createElement('a');
        firstBtn.href = '#';
        firstBtn.className = 'pagination-item';
        firstBtn.textContent = '1';
        firstBtn.onclick = (e) => {
          e.preventDefault();
          currentPage = 1;
          loadProducts(1);
        };
        container.appendChild(firstBtn);

        if (pagination.currentPage > 3) {
          const ellipsis = document.createElement('span');
          ellipsis.className = 'pagination-ellipsis';
          ellipsis.textContent = '...';
          container.appendChild(ellipsis);
        }
      }

      // Page numbers
      for (let i = Math.max(1, pagination.currentPage - 1); i <= Math.min(pagination.totalPages, pagination.currentPage + 1); i++) {
        const btn = document.createElement('a');
        btn.href = '#';
        btn.className = `pagination-item ${i === pagination.currentPage ? 'active' : ''}`;
        btn.textContent = i;
        btn.onclick = (e) => {
          e.preventDefault();
          currentPage = i;
          loadProducts(i);
        };
        container.appendChild(btn);
      }

      // Last page
      if (pagination.currentPage < pagination.totalPages - 1) {
        if (pagination.currentPage < pagination.totalPages - 2) {
          const ellipsis = document.createElement('span');
          ellipsis.className = 'pagination-ellipsis';
          ellipsis.textContent = '...';
          container.appendChild(ellipsis);
        }

        const lastBtn = document.createElement('a');
        lastBtn.href = '#';
        lastBtn.className = 'pagination-item';
        lastBtn.textContent = pagination.totalPages;
        lastBtn.onclick = (e) => {
          e.preventDefault();
          currentPage = pagination.totalPages;
          loadProducts(pagination.totalPages);
        };
        container.appendChild(lastBtn);
      }

      // Next button
      const nextBtn = document.createElement('a');
      nextBtn.href = '#';
      nextBtn.className = `pagination-item ${pagination.currentPage === pagination.totalPages ? 'disabled' : ''}`;
      nextBtn.innerHTML = '&gt;';
      nextBtn.onclick = (e) => {
        e.preventDefault();
        if (pagination.currentPage < pagination.totalPages) {
          currentPage = pagination.currentPage + 1;
          loadProducts(currentPage);
        }
      };
      container.appendChild(nextBtn);
    }



    // Event listeners cho các bộ lọc
    document.getElementById('categoryFilter').addEventListener('change', () => {
      currentPage = 1;
      loadProducts(1);
    });

    document.getElementById('statusFilter').addEventListener('change', () => {
      currentPage = 1;
      loadProducts(1);
    });

    document.getElementById('priceMin').addEventListener('change', () => {
      currentPage = 1;
      loadProducts(1);
    });

    document.getElementById('priceMax').addEventListener('change', () => {
      currentPage = 1;
      loadProducts(1);
    });

    document.getElementById('sortBy').addEventListener('change', () => {
      currentPage = 1;
      loadProducts(1);
    });

    // Reset filters
    document.getElementById('resetFilters').addEventListener('click', () => {
      document.getElementById('search-input').value = '';
      document.getElementById('categoryFilter').value = 'all';
      document.getElementById('statusFilter').value = 'all';
      document.getElementById('priceMin').value = '';
      document.getElementById('priceMax').value = '';
      document.getElementById('sortBy').value = 'newest';
      currentPage = 1;
      loadProducts(1);
    });

    // Prevent the search form from performing a full page submit and wire up search
    (function() {
      const searchForm = document.querySelector('.search-container');
      const searchInput = document.getElementById('search-input');
      const searchBtn = document.getElementById('search-btn');

      if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
          e.preventDefault();
          currentPage = 1;
          loadProducts(1);
        });
      }

      // Allow Enter key in the input to trigger the same
      if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            currentPage = 1;
            loadProducts(1);
          }
        });
      }

      if (searchBtn) {
        searchBtn.addEventListener('click', function(e) {
          e.preventDefault();
          currentPage = 1;
          loadProducts(1);
        });
      }
    })();
    // Load lần đầu (respect currentPage)
    // Thêm vào cuối phần script, trước dòng loadProducts(currentPage);

    document.addEventListener('DOMContentLoaded', function() {
      // Lấy parameter từ URL
      const urlParams = new URLSearchParams(window.location.search);
      const editProductId = urlParams.get('edit');

      if (editProductId) {
        editProduct(editProductId);
        // Xóa parameter edit khỏi URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
      }

      // Lấy parameter status từ URL
      const statusParam = urlParams.get('status');
      if (statusParam) {
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
          statusFilter.value = statusParam;
          currentPage = 1;
          loadProducts(1);
          return; // Dừng ở đây để không load 2 lần
        }
      }
    });
    loadProducts(currentPage);
  </script>

  <script>
    // Kiểm tra nếu có parameter edit trong URL
    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const editProductId = urlParams.get('edit');

      if (editProductId) {
        // Tự động mở popup chỉnh sửa với productId từ URL
        editProduct(editProductId);

        // Xóa parameter khỏi URL để tránh mở lại khi refresh
        const newUrl = window.location.pathname + window.location.search.replace(/[?&]edit=\d+/, '');
        window.history.replaceState({}, document.title, newUrl);
      }

      // Phần code xử lý status filter hiện tại
      const statusParam = urlParams.get('status');
      if (statusParam) {
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
          statusFilter.value = statusParam;
          currentPage = 1;
          loadProducts(1);
        }
      }

      // Load products nếu không có filter nào
      if (!editProductId && !statusParam) {
        loadProducts(currentPage);
      }
    });
  </script>
</body>

</html>