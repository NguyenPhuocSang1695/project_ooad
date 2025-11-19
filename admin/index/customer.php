<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý người dùng</title>
  
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="../style/main1.css" rel="stylesheet">
  <link href="../style/LogInfo.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../style/customer-search.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/responsiveCustomer.css">
  <link rel="stylesheet" href="../style/add-user-modal.css">
  <!-- Customer table CSS loaded last - contains all customer page styles -->
  <link href="../style/customer-table.css" rel="stylesheet">
  <script src="../js/customer-search.js" defer></script>
  <script src="../js/add-user.js" defer></script>
  <script src="../js/edit-user.js" defer></script>
  <script src="../js/delete-user.js" defer></script>
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
  <?php
  
  require_once 'header_sidebar.php';
  ?>
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
               placeholder="Tìm kiếm theo tên, số điện thoại hoặc username..." 
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

    <!-- Quick debug: show number of users retrieved. Add ?debug=1 to see raw data. -->
    <div class="user-count" style="margin:8px 0;color:#333;">Số bản ghi người dùng: <?php echo is_array($users) ? count($users) : 0; ?></div>
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
      <div style="background:#f8f9fa;border:1px solid #ddd;padding:8px;margin-bottom:8px;overflow:auto;max-height:300px;">
        <strong>DEBUG: raw users data</strong>
        <pre><?php var_dump($users); ?></pre>
      </div>
    <?php endif; ?>
    <table class="user-table">
      <thead>
        <tr>
          <th>Họ và tên</th>
          <th>Số điện thoại</th>
          <th>Vai trò</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php foreach ($users as $user): ?>
            <tr class="user-row" data-user-id="<?php echo (int)$user->getId(); ?>" data-username="<?php echo htmlspecialchars($user->getUsername()); ?>" data-user-status="<?php echo htmlspecialchars($user->getStatus()); ?>" style="cursor:pointer;">
              <td><?php echo htmlspecialchars($user->getFullname()); ?></td>
              <td><?php echo htmlspecialchars($user->getPhone()); ?></td>
              <td><span class="role-badge"><?php echo $user->getRoleText(); ?></span></td>
              <td>
                <span class="status-badge <?php echo $user->isActive() ? 'status-active' : 'status-blocked'; ?>">
                  <?php echo $user->getStatusText(); ?>
                </span>
              </td>
              <td class="action-buttons">
                <button class="btn-edit" onclick="(function(btn){ var tr=btn.closest('tr'); var un=tr?tr.getAttribute('data-username'):''; var uid=tr?parseInt(tr.getAttribute('data-user-id')||'0',10):0; showEditUserPopup(un, uid); })(this)">
                  <i class="fas fa-edit"></i> Sửa
                </button>
                <button class="btn-toggle-status">
                  <i class="fas <?php echo $user->isActive() ? 'fa-lock' : 'fa-unlock'; ?>"></i>
                  <?php echo $user->isActive() ? 'Khóa' : 'Mở khóa'; ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center">Không có dữ liệu người dùng</td>
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

    <script>
    // Row click -> navigate to user detail, ignore clicks on edit button
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('tr.user-row').forEach(function(tr){
        tr.addEventListener('click', function(e){
          if (e.target.closest('.btn-edit') || e.target.closest('.btn-toggle-status')) return; // don't navigate when clicking edit/toggle
          const userId = this.getAttribute('data-user-id');
          const username = this.getAttribute('data-username');
          
          // Ưu tiên dùng user_id, fallback về username
          if (userId) {
            window.location.href = 'userDetail.php?user_id=' + encodeURIComponent(userId);
          } else if (username) {
            window.location.href = 'userDetail.php?username=' + encodeURIComponent(username);
          }
        });
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