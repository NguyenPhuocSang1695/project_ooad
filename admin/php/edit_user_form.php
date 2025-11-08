<?php if (!defined('INCLUDE_CHECK')) die('No direct access'); ?>

<div class="modal" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" style="display:none;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">Sửa thông tin người dùng</h5>
        <button type="button" class="btn-close" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editUserForm" action="../php/update_user.php" method="POST">
          <!-- original username (readonly key) -->
          <input type="hidden" id="edit_original_username" name="username" />
          <input type="hidden" id="edit_user_id" name="user_id" />
          <div class="row mb-3">
            <div class="col-md-6" id="edit_username_col">
              <label for="edit_username" class="form-label">Tên đăng nhập</label>
              <input type="text" class="form-control" id="edit_username" name="new_username" readonly>
            </div>
            <div class="col-md-6">
              <label for="edit_fullname" class="form-label">Họ và tên</label>
              <input type="text" class="form-control" id="edit_fullname" name="fullname" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="edit_phone" class="form-label">Số điện thoại</label>
              <input type="text" class="form-control" id="edit_phone" name="phone" required>
            </div>
          </div>
          <!-- Password section (only visible when admin edits own account) -->
          <div class="row mb-3" id="edit_password_row" style="display:none;">
            <div class="col-md-6">
              <label for="edit_password" class="form-label">Mật khẩu mới</label>
              <input type="password" class="form-control" id="edit_password" name="password" minlength="6">
            </div>
            <div class="col-md-6">
              <label for="edit_confirm_password" class="form-label">Xác nhận mật khẩu</label>
              <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password" minlength="6">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="edit_role" class="form-label">Vai trò</label>
              <select class="form-select" id="edit_role" name="role">
                <option value="customer">Khách hàng</option>
                <option value="admin">Quản trị viên</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="edit_status" class="form-label">Trạng thái</label>
              <select class="form-select" id="edit_status" name="status">
                <option value="Active">Hoạt động</option>
                <option value="Block">Không hoạt động</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="edit_province" class="form-label">Tỉnh/Thành phố</label>
              <select class="form-select" id="edit_province" name="province">
                <option value="">Chọn tỉnh/thành phố</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="edit_district" class="form-label">Quận/Huyện</label>
              <select class="form-select" id="edit_district" name="district">
                <option value="">Chọn quận/huyện</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="edit_ward" class="form-label">Phường/Xã</label>
              <select class="form-select" id="edit_ward" name="ward">
                <option value="">Chọn phường/xã</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="edit_address" class="form-label">Địa chỉ cụ thể</label>
              <input type="text" class="form-control" id="edit_address" name="address">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelEditUser">Đóng</button>
        <button type="submit" class="btn btn-primary" form="editUserForm">Lưu thay đổi</button>
      </div>
    </div>
  </div>
</div>
