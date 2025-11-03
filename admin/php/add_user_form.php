<?php
// Prevent direct access to this file
if (!defined('INCLUDE_CHECK')) {
    http_response_code(403);
    die('Forbidden');
}
?>
<div class="modal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Thêm người dùng mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addUserForm" action="../php/add_user.php" method="POST">
          <div class="row mb-3">
            <div class="col-md-6" id="usernameRow">
              <label for="username" class="form-label">Tên đăng nhập</label>
              <input type="text" class="form-control" id="username" name="username">
            </div>
            <div class="col-md-6">
              <label for="fullname" class="form-label">Họ và tên</label>
              <input type="text" class="form-control" id="fullname" name="fullname" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="phone" class="form-label">Số điện thoại</label>
              <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>
          </div>
          <div class="row mb-3" id="passwordRow">
            <div class="col-md-6">
              <label for="password" class="form-label">Mật khẩu</label>
              <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="col-md-6">
              <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="role" class="form-label">Vai trò</label>
              <select class="form-select" id="role" name="role" required>
                <option value="">Chọn vai trò</option>
                <option value="admin">Admin</option>
                <option value="customer">Khách hàng</option>
              </select>
              <div class="form-text">Mật khẩu chỉ bắt buộc khi vai trò là Admin</div>
            </div>
            <div class="col-md-6">
              <label for="status" class="form-label">Trạng thái</label>
              <select class="form-select" id="status" name="status" required>
                <option value="Active">Hoạt động</option>
                <option value="Block">Không hoạt động</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="province" class="form-label">Tỉnh/Thành phố</label>
              <select class="form-select" id="province" name="province" required>
                <option value="">Chọn tỉnh/thành phố</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="district" class="form-label">Quận/Huyện</label>
              <select class="form-select" id="district" name="district" required>
                <option value="">Chọn quận/huyện</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="ward" class="form-label">Phường/Xã</label>
              <select class="form-select" id="ward" name="ward" required>
                <option value="">Chọn phường/xã</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="address" class="form-label">Địa chỉ cụ thể</label>
              <input type="text" class="form-control" id="address" name="address" required>
            </div>
          </div>
  </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-primary" id="submitAddUser">Thêm người dùng</button>
      </div>
    </div>
  </div>
</div>
