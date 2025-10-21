<?php
// include '../php/check_session.php';
// session_name('admin_session');
// session_start();
require_once '../php/connect.php';
require_once '../php/ProductController.php';

$db = new DatabaseConnection();
$db->connect();
global $db;
$mysqli = $db->getConnection();
global $mysqli;
// class AutoProductLoader
// {
//   private $productId;
//   private $productData;

//   public function __construct()
//   {
//     $this->productId = isset($_GET['product_id']) ? $_GET['product_id'] : null;
//   }

//   public function hasProductId()
//   {
//     return !empty($this->productId);
//   }

//   public function generateAutoLoadScript()
//   {
//     if (!$this->hasProductId()) return '';

//     $productId = htmlspecialchars($this->productId, ENT_QUOTES, 'UTF-8');
//     $script = "
//         <script>
//             document.addEventListener('DOMContentLoaded', function() {
//                 fetch('../php/get-product.php?id={$productId}')
//                     .then(response => response.json())
//                     .then(data => {
//                         if (data) {
//                             const searchInput = document.querySelector('.search-input');
//                             if (searchInput) {
//                                 searchInput.value = data.ProductName;
//                                 searchProducts(1, {$productId});
//                             }
//                         }
//                     })
//                     .catch(error => console.error('Error:', error));
//             });
//         </script>";

//     return $script;
//   }
// }

// $product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;

// if ($product_id) {
//   echo "<script>
//     document.addEventListener('DOMContentLoaded', function() {
//       // Tự động tìm kiếm sản phẩm với ID cụ thể
//       fetch('../php/get-product.php?id=" . $product_id . "')
//         .then(response => response.json())
//         .then(data => {
//           if (data) {
//             const searchInput = document.querySelector('.search-input');
//             if (searchInput) {
//               searchInput.value = data.ProductName;
//               searchProducts(1, " . $product_id . ");
//             }
//           }
//         })
//         .catch(error => console.error('Error:', error));
//     });
//   </script>";
// }
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Popup overlay cho thêm sản phẩm */
    .add-product-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      /* Nền mờ */
      z-index: 1000;
      justify-content: center;
      align-items: center;
      margin: auto;
    }

    .add-product-content {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      width: 400px;
      max-height: 80vh;
      overflow-y: auto;
      position: relative;
    }

    /* Popup overlay */
    .product-details-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      /* Nền mờ */
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    /* .product-details-overlay.active {
      display: flex;
    } */

    .product-details-content {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      width: 400px;
      max-height: 80vh;
      overflow-y: auto;
      position: relative;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #ff4444;
      color: white;
      border: none;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      z-index: 10000000;
    }

    .close-btn:hover {
      background: #cc0000;
    }

    /* Đảm bảo nội dung trong popup không bị tràn */
    .details-grid p,
    .form-group label,
    .form-group input {
      font-size: 14px;
    }

    .form-grid {
      grid-template-columns: 1fr 2fr;
      gap: 15px;
    }

    .image-preview,
    .edit-image-preview {
      max-width: 150px;
    }

    /* Responsive */
    @media only screen and (max-width: 29.9375em) {

      .product-details-content,
      .add-product-content {
        width: 90%;
        padding: 15px;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }

      .details-grid p,
      .form-group label,
      .form-group input {
        font-size: 12px;
      }

      .image-preview,
      .edit-image-preview {
        max-width: 100px;
      }
    }

    @media only screen and (min-width: 30em) and (max-width: 63.9375em) {
      .product-details-content {
        width: 70%;
      }

      .add-product-content {
        padding: 20px;
        width: 66%;
      }
    }

    @media only screen and (min-width: 64em) {
      .product-details-content {
        width: 40%;
      }

      .add-product-content {
        padding: 25px;
        width: 550px;
      }

      .form-grid {
        grid-template-columns: 1fr 2fr;
        gap: 10px;
      }
    }

    /* Form thêm sản phẩm  */
    #add-product-btn {
      width: 150px;
    }

    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      width: 350px;
      max-width: 100%;
      width: 100%;
    }

    /* Tiêu đề của form */
    .card h2 {
      text-align: center;
      font-size: 24px;
      color: #333;
      margin-bottom: 20px;
    }

    /* Cài đặt khoảng cách cho các trường nhập liệu */
    .form-group {
      margin-bottom: 15px;
    }

    label {
      font-weight: bold;
      font-size: 14px;
      color: #555;
      display: block;
      margin-bottom: 5px;
    }

    input,
    textarea,
    select {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border-radius: 5px;
      border: 1px solid #ccc;
      background-color: #f9f9f9;
    }

    /* Cải tiến textarea */
    textarea {
      resize: vertical;
      height: 80px;
    }

    /* Hiển thị ảnh trước khi gửi */
    .image-preview {
      max-width: 200px;
      margin-top: 10px;
      display: block;
      margin-left: auto;
      margin-right: auto;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Nút gửi form */
    .btn {
      width: 100%;

      color: white;
      padding: 12px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      text-align: center;
      margin-top: 20px;
    }

    /* Các lỗi hoặc thông báo */
    .alert {
      padding: 10px;
      margin-bottom: 15px;
      background-color: #f44336;
      color: white;
      border-radius: 5px;
      text-align: center;
      font-size: 14px;
    }

    .alert-success {
      background-color: #4CAF50;
    }

    .image-preview {
      max-width: 200px;
      margin-top: 10px;
      display: block;
      margin-left: auto;
      margin-right: auto;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Mobile responsive */


    .category-note {
      font-size: 12px;
      color: #777;
      margin-top: 5px;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      width: 30px;
      height: 30px;
      background-color: #ff4444;
      border: none;
      border-radius: 50%;
      color: white;
      font-size: 16px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1002;
      transition: background-color 0.2s;
    }

    .close-btn:hover {
      background-color: #cc0000;
    }

    .product-details-overlay {
      /* // ...existing code... */
    }
  </style>
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
          <a href="analyzePage.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection">
                <i class="fa-solid fa-chart-simple" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Thống kê</p>
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
        <img class="avatar" src="../../assets/images/admin.jpg" alt="" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
      </div>
      <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
        aria-labelledby="offcanvasWithBothOptionsLabel">
        <div style="border-bottom: 1px solid rgb(176, 176, 176);" class="offcanvas-header">
          <img class="avatar" src="../../assets/images/admin.jpg" alt="">
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
    <a href="analyzePage.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-chart-simple" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Thống kê</p>
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

            <style>
              #productsTable td:nth-child(2),
              td:nth-child(3),
              td:nth-child(4) {
                text-align: center;
              }
            </style>

            <!-- Body + pagination do renderProducts() xuất ra -->
            <?php
            $controller = new ProductController();
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            echo $controller->renderProducts($page);
            ?>

            <tbody id="productsBody">
              <!-- Nội dung được load bằng JS -->
            </tbody>
          </table>

          <!-- Main pagination (server-rendered by ProductController->renderProducts) -->
          <div id="pagination-search" style="text-align:center; margin-top:20px;"></div>

        </div>
      </div>
    </div>


    <!-- Popup overlay cho thông tin sản phẩm -->
    <div class="product-details-overlay" id="productDetailsOverlay">
      <div class="product-details-content" id="productDetailsContent"></div>
    </div>

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
                require_once '../../php-api/connectdb.php'; // Kết nối tới CSDL
                $conn = connect_db();

                // Truy vấn lấy danh mục
                $sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryID ASC";
                $result = $conn->query($sql);

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

                $conn->close();
                ?>
              </select>
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
              <!-- <p class="category-note"></p><br> -->
              <!-- <p class="category-note">Chọn ảnh sản phẩm (PNG, JPG, JPEG)</p><br>
              <p class="category-note">Kích thước tối đa: 2MB</p><br>
              <p class="category-note">Kích thước tối thiểu: 300x300px</p><br> -->
              <img id="imagePreview" class="image-preview" src="#" alt="Preview image" style="display:none;">
            </div>

            <button type="submit" class="btn btn-success">Thêm Sản Phẩm</button>
          </form>


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
      <button type="button" class="close-btn" onclick="closeEditOverlay()">
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
                      <option value="1">Cây văn phòng</option>
                      <option value="2">Cây dưới nước</option>
                      <option value="3">Cây dễ chăm</option>
                      <option value="4">Cây để bàn</option>
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

                <div class="mb-3">
                  <label for="editDescription" class="form-label">Mô tả</label>
                  <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                </div>
              </div>
            </div>

            <div class="form-actions text-end mt-3">
              <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
              <button type="button" class="btn btn-danger me-2" onclick="confirmDelete()">Xóa sản phẩm</button>
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
    // Function to show edit product overlay
    function editProduct(productId) {
      fetch(`../php/get-product.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
          document.getElementById('editProductId').value = product.ProductID;
          document.getElementById('editProductName').value = product.ProductName;
          document.getElementById('editCategoryID').value = product.CategoryID;
          document.getElementById('editPrice').value = product.Price;
          document.getElementById('editDescription').value = product.Description;
          document.getElementById('editStatus').value = product.Status;

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

    // Handle edit product form submission safely (prevent default and use AJAX)
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

    function confirmDelete() {
      const productId = document.getElementById('editProductId').value;
      if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')) {
        fetch('../php/delete-product.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              productId: productId
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
    let currentPage = <?php echo isset($page) ? (int)$page : 1; ?>;

    function loadProducts(page = 1) {
      const keyword = (document.getElementById('search-input') || {
        value: ''
      }).value.trim();

      // Toggle visibility: when searching, hide server tfoot and main pagination, show pagination-search
      const tfoot = document.querySelector('#productsTable tfoot');
      const paginationMain = document.getElementById('pagination');
      const paginationSearch = document.getElementById('pagination-search');

      if (keyword) {
        if (tfoot) tfoot.style.display = 'none';
        if (paginationMain) paginationMain.style.display = 'none';
        if (paginationSearch) paginationSearch.style.display = '';
      } else {
        if (tfoot) tfoot.style.display = '';
        if (paginationMain) paginationMain.style.display = '';
        if (paginationSearch) paginationSearch.style.display = 'none';
      }

      fetch(`../php/search-products.php?page=${page}&keyword=${encodeURIComponent(keyword)}`)
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById('productsBody');
          tbody.innerHTML = '';

          if (data && data.products && data.products.length > 0) {
            data.products.forEach(p => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                        <td><img src="../..${p.ImageURL}" style="width:100px;height:100px;object-fit:cover;"></td>
                        <td style="text-align:center;">${p.ProductName}</td>
                        <td style="text-align:center;">${p.CategoryName}</td>
                        <td style="text-align:center;">${p.Price.toLocaleString()}</td>
                        <td style="text-align:center;">
                            <button class="btn btn-warning btn-sm" onclick="editProduct(${p.ProductID})">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </td>
                    `;
              tbody.appendChild(tr);
            });
          } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Không có sản phẩm nào</td></tr>`;
          }

          // Render pagination into the appropriate container
          const targetPagination = keyword ? document.getElementById('pagination-search') : document.getElementById('pagination');
          if (targetPagination) targetPagination.innerHTML = '';

          if (data && data.pagination && data.pagination.totalPages > 1) {
            for (let i = 1; i <= data.pagination.totalPages; i++) {
              const btn = document.createElement('button');
              btn.textContent = i;
              btn.style.margin = '0 3px';
              btn.className = (i === data.pagination.currentPage) ? 'active' : '';
              btn.onclick = () => {
                currentPage = i;
                loadProducts(i);
              };
              if (targetPagination) targetPagination.appendChild(btn);
            }
          }
        })
        .catch(err => console.error(err));
    }

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
    loadProducts(currentPage);

























    // document.addEventListener('DOMContentLoaded', function() {
    //   const searchInput = document.querySelector('.search-input');
    //   const searchBtn = document.querySelector('.search-btn');

    //   // Nhấn Enter trong ô input
    //   searchInput.addEventListener('keypress', function(e) {
    //     if (e.key === 'Enter') searchProducts(1);
    //   });

    //   // Nhấn nút tìm kiếm
    //   searchBtn.addEventListener('click', function() {
    //     searchProducts(1);
    //   });
    // });

    // // Gọi ngay khi trang load
    // document.addEventListener('DOMContentLoaded', () => {
    //   searchProducts(1);

    //   // Cho phép tìm kiếm khi người dùng gõ phím
    //   const searchInput = document.querySelector('.search-input');
    //   searchInput.addEventListener('keyup', () => searchProducts(1));
    // });


    // // Thêm debounce để tránh gọi API quá nhiều
    // let searchTimeout;
    // document.querySelector('.search-input').addEventListener('input', function() {
    //   clearTimeout(searchTimeout);
    //   searchTimeout = setTimeout(() => searchProducts(1), 300); // Giảm thời gian delay xuống 300ms
    // });

    // // Xử lý khi nhấn Enter
    // document.querySelector('.search-input').addEventListener('keypress', function(e) {
    //   if (e.key === 'Enter') {
    //     clearTimeout(searchTimeout);
    //     searchProducts(1);
    //   }
    // });

    // // Xử lý khi nhấn nút tìm kiếm
    // document.querySelector('.search-btn').addEventListener('click', function() {
    //   clearTimeout(searchTimeout);
    //   searchProducts(1);
    // });

    // // Load tất cả sản phẩm khi trang được tải
    // document.addEventListener('DOMContentLoaded', function() {
    //   searchProducts(1);
    // });
  </script>

  <style>
    .product-details-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .product-details-content {
      background: white;
      padding: 20px;
      border-radius: 8px;
      width: 90%;
      max-width: 800px;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
    }

    .image-preview-container img {
      max-width: 100%;
      height: auto;
      border-radius: 4px;
    }

    .form-actions {
      border-top: 1px solid #dee2e6;
      padding-top: 1rem;
    }

    .category-note {
      font-size: 12px;
      color: #777;
      margin-top: 5px;
    }
  </style>
</body>

</html>