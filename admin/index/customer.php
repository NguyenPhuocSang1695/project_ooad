<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý người dùng</title>
  <link rel="stylesheet" href="../style/header.css">
  <link rel="stylesheet" href="../style/sidebar.css">
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="../style/main1.css" rel="stylesheet">
  <link href="../style/customer1.css" rel="stylesheet">
  <link href="../style/LogInfo.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../style/customer-table.css" rel="stylesheet">
  <link href="../style/customer-search.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/responsiveCustomer.css">
  <link rel="stylesheet" href="../style/add-user-modal.css">
  <script src="../js/customer-search.js" defer></script>
  <script src="../js/add-user.js" defer></script>
  <script src="../js/edit-user.js" defer></script>
  <style>
    .loading {
      background-image: url('data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQJCgAAACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkECQoAAAAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkECQoAAAAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQJCgAAACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAkKAAAALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==');
      background-repeat: no-repeat;
      background-position: right 45px center;
    }
  </style>
</head>

<body>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../php/connect.php';
require_once '../php/UserManager.php';
require_once '../php/User.php';

  // Check if accessed from homePage
  $fromHomePage = isset($_GET['from']) && $_GET['from'] === 'home';

  try {
      $userManager = new UserManager($myconn ?? null);
      $search = isset($_GET['search']) ? trim($_GET['search']) : '';
      
      // Calculate pagination using UserManager
      $records_per_page = 5;
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $page = max(1, $page);
      $offset = ($page - 1) * $records_per_page;

      if (!empty($search)) {
          // Nếu có từ khóa tìm kiếm
          $users = $userManager->searchUsers($search, $offset, $records_per_page);
          $total_records = $userManager->getTotalSearchResults($search);
      } else {
          // Nếu không có tìm kiếm, hiển thị tất cả users
          $total_records = $userManager->getTotalUsers($fromHomePage ? 'customer' : null);
          $users = $userManager->getUsers($offset, $records_per_page, $fromHomePage ? 'customer' : null);
      }

      $total_pages = $total_records > 0 ? (int)ceil($total_records / $records_per_page) : 1;
  } catch (Exception $e) {
      echo "<div class='alert alert-danger'>Lỗi khi tải dữ liệu: " . htmlspecialchars($e->getMessage()) . "</div>";
      $users = [];
      $total_pages = 1;
  }
  ?>
  
  <!-- Header và sidebar giữ nguyên -->

 <!-- header -->
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
              <button class="button-function-selection">
                <i class="fa-solid fa-warehouse" style="font-size: 20px; color: #FAD4AE;"></i>
              </button>
              <p>Kho hàng</p>
            </div>
          </a>
          <a href="customer.php" style="text-decoration: none; color: black;">
            <div class="container-function-selection">
              <button class="button-function-selection" style="background-color: #6aa173;">
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
      <p class="header-left-title">Người dùng</p>
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
          <div style="display: flex; flex-direction: column; height: 95px;">
            <h4 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">Username</h4>
            <h5 id="employee-displayname">Họ tên</h5> <!-- Thêm id ở đây -->
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
  <!-- sidebar -->
  <div class="side-bar">
    <div class="backToHome">
      <a href="homePage.php" style="text-decoration: none; color: black;">
        <div class="container-function-selection">
          <button class="button-function-selection" style=" margin-top: 35px;">
            <i class="fa-solid fa-house" style="font-size: 20px; color: #FAD4AE;"></i>
          </button>
          <p>Tổng quan</p>
        </div>
      </a>
    </div>
    <a href="wareHouse.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection">
          <i class="fa-solid fa-warehouse" style="font-size: 20px; color: #FAD4AE;"></i>
        </button>
        <p>Kho hàng</p>
      </div>
    </a>
    <a href="customer.php" style="text-decoration: none; color: black;">
      <div class="container-function-selection">
        <button class="button-function-selection" style="background-color: #6aa173;">
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
  <!-- main container -->
  <div class="customer-container">
    <div class="search-toggle-wrapper">
      <button id="toggleSearch" class="toggle-search-btn">
        <i class="fas fa-search"></i> Tìm kiếm
      </button>
      <button id="addUser" class="toggle-search-btn">
        <i class="fas fa-user-plus"></i> Thêm người dùng
      </button>
    </div>
    <div class="search-wrapper" id="searchWrapper" style="display: none;">
      <form class="search-container-customer" method="GET">
        <input type="text" 
               name="search"
               class="search-bar-customer" 
               placeholder="Tìm kiếm theo tên, email, số điện thoại..." 
               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button type="submit" class="search-button">
          <i class="fas fa-search"></i>
        </button>
      </form>
    </div>
    
    <div class="search-results-info">
        <?php if (isset($_GET['search']) && $_GET['search']): ?>
            <?php $totalResults = $userManager->getTotalSearchResults($_GET['search']); ?>
            <p>Tìm thấy <?php echo $totalResults; ?> kết quả cho "<?php echo htmlspecialchars($_GET['search']); ?>"</p>
        <?php endif; ?>

    <table class="user-table">
      <thead>
        <tr>
          <th>Tên đăng nhập</th>
          <th>Họ và tên</th>
          <th>Số điện thoại</th>
          <th>Email</th>
          <th>Vai trò</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php foreach ($users as $user): ?>
            <?php 
            $statusClass = $user->isActive() ? 'status-active' : 'status-inactive';
            ?>
            <tr>
              <td><?php echo htmlspecialchars($user->getUsername()); ?></td>
              <td><?php echo htmlspecialchars($user->getFullname()); ?></td>
              <td><?php echo htmlspecialchars($user->getPhone()); ?></td>
              <td><?php echo htmlspecialchars($user->getEmail()); ?></td>
              <td><span class="role-badge"><?php echo $user->getRoleText(); ?></span></td>
              <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $user->getStatusText(); ?></span></td>
              <td class="action-buttons">
                <button class="btn-edit" onclick="showEditUserPopup('<?php echo addslashes($user->getUsername()); ?>')">
                  <i class="fas fa-edit"></i> Sửa
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center">Không có dữ liệu người dùng</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
      <?php endif; ?>
      
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php if ($i == $page): ?>
          <button class="page-btn active"><?php echo $i; ?></button>
        <?php else: ?>
          <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="page-btn"><?php echo $i; ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      
      <?php if ($page < $total_pages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

    <script src="asset/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/checklog.js"></script>
    <script src="../js/main.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('.search-bar-customer');
        const searchForm = document.querySelector('.search-container-customer');

        // Xử lý khi submit form
        searchForm.addEventListener('submit', function(e) {
            const searchValue = searchInput.value.trim();
            if (searchValue === '') {
                e.preventDefault();
                window.location.href = 'customer.php';
            }
        });
    });
    </script>

  <?php
  define('INCLUDE_CHECK', true);
  require_once '../php/add_user_form.php';
  // Include edit user modal so JS can find its elements
  require_once '../php/edit_user_form.php';
  ?>
  
  </body>

</html>