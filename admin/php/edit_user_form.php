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
          <!-- Hidden fields to identify user -->
          <input type="hidden" id="edit_original_username" name="username" />
          <input type="hidden" id="edit_user_id" name="user_id" />
          <input type="hidden" id="edit_role" name="role" />
          
          <!-- Only editable fields: Fullname and Phone -->
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="edit_fullname" class="form-label">Họ và tên</label>
              <input type="text" class="form-control" id="edit_fullname" name="fullname" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="edit_phone" class="form-label">Số điện thoại</label>
              <input type="text" class="form-control" id="edit_phone" name="phone" required pattern="^0\d{9}$" placeholder="Nhập 10 số, bắt đầu bằng 0">
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
