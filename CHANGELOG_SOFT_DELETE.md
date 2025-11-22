# Thay đổi: Chức năng Khóa/Mở khóa người dùng (Soft Delete)

## Tổng quan
Đã chuyển đổi từ chức năng **xóa cứng** (hard delete) sang **khóa/mở khóa** người dùng (soft delete). Người dùng sẽ không bị xóa khỏi database mà chỉ thay đổi trạng thái `Status` giữa `Active` và `Block`.

## Các file đã thay đổi

### 1. Backend - PHP

#### `admin/php/UserManager.php`
- **Đổi tên method**: `deleteUser()` → `toggleUserStatus()`
- **Chức năng mới**: 
  - Chuyển đổi trạng thái người dùng giữa `Active` và `Block`
  - Kiểm tra quyền admin/nhân viên
  - Không cho phép khóa chính mình
  - Sử dụng UPDATE thay vì DELETE
- **Thông báo**: Thay đổi từ "Xóa người dùng" thành "Khóa/Mở khóa người dùng"

#### `admin/php/delete_user.php`
- Cập nhật endpoint để gọi `toggleUserStatus()` thay vì `deleteUser()`
- Đổi comment từ "delete user" → "toggle user status (Block/Active)"

### 2. Frontend - JavaScript

#### `admin/js/delete-user.js`
- **Đổi tên function**: `handleDelete()` → `handleToggleStatus()`
- **Thay đổi logic**:
  - Nhận thêm tham số `currentStatus` để xác định hành động (khóa/mở khóa)
  - Xóa confirm thứ 2 (không cần xác nhận 2 lần cho khóa)
  - Cập nhật thông báo động theo trạng thái hiện tại
- **Đổi ID button**: `deleteUserBtn` → `toggleUserStatusBtn`
- **Đổi class button**: `.btn-delete-user` → `.btn-toggle-status`
- **Thêm attribute**: `data-user-status` để lưu trạng thái hiện tại

### 3. Frontend - HTML/PHP

#### `admin/index/customer.php`
- **Thêm cột "Trạng thái"** vào bảng danh sách người dùng
- **Hiển thị status badge**:
  - Màu xanh cho "Hoạt động" (Active)
  - Màu đỏ cho "Đã khóa" (Block)
- **Đổi nút "Xóa"** thành nút động:
  - Icon: `fa-lock` khi Active, `fa-unlock` khi Block
  - Text: "Khóa" khi Active, "Mở khóa" khi Block
- **Cập nhật class**: `.btn-delete-user` → `.btn-toggle-status`
- **Thêm attribute**: `data-user-status` vào mỗi row
- Cập nhật colspan từ 4 → 5 cho empty state

#### `admin/index/userDetail.php`
- **Đổi nút "Xóa người dùng"** thành nút động "Khóa/Mở khóa tài khoản"
- **Màu nút**:
  - Warning (vàng) khi Active - hiển thị "Khóa tài khoản"
  - Success (xanh) khi Block - hiển thị "Mở khóa tài khoản"
- **Bỏ comment** phần hiển thị Vai trò và Trạng thái
- **Thêm attribute**: `data-user-status` cho button

### 4. Frontend - CSS

#### `admin/style/customer-table.css`
- Đổi class `.status-inactive` → `.status-blocked`
- Thay thế `.btn-delete-user` bằng `.btn-toggle-status`
- **Màu nút toggle**:
  - Background: gradient vàng cam (#f39c12 → #e67e22)
  - Hover: chuyển tối hơn
  - Shadow: màu vàng cam
- Giữ nguyên CSS cho `.status-active` và `.status-blocked`

#### `admin/style/userDetail.css`
- CSS `.status-blocked` đã có sẵn (không cần thay đổi)
- CSS cho button được kế thừa từ Bootstrap classes

## Database Schema

Không có thay đổi schema. Sử dụng cột `Status` có sẵn:
```sql
`Status` enum('Active','Block') NOT NULL DEFAULT 'Active'
```

## Quyền truy cập

- Chỉ **admin** và **nhân viên** mới có quyền khóa/mở khóa người dùng
- Không thể khóa chính mình (tài khoản đang đăng nhập)
- Kiểm tra session để xác định người dùng hiện tại

## Lợi ích của Soft Delete

1. **Bảo toàn dữ liệu**: Lịch sử đơn hàng, giao dịch không bị mất
2. **Có thể khôi phục**: Dễ dàng mở khóa tài khoản khi cần
3. **An toàn hơn**: Tránh xóa nhầm dữ liệu quan trọng
4. **Tuân thủ ràng buộc FK**: Không vi phạm foreign key constraints
5. **Audit trail**: Giữ lại thông tin người dùng cho mục đích kiểm toán

## Testing

Các trường hợp cần test:
- [x] Admin khóa tài khoản customer
- [x] Admin mở khóa tài khoản customer đã bị khóa
- [x] Không thể khóa chính mình
- [x] Hiển thị đúng trạng thái trên bảng danh sách
- [x] Hiển thị đúng trạng thái trên trang chi tiết
- [x] Nút toggle hiển thị đúng text và icon theo trạng thái

## Lưu ý

- File `delete_user.php` giữ nguyên tên để không phá vỡ routing/API endpoint
- JavaScript file `delete-user.js` giữ nguyên tên để không cần thay đổi include trong HTML
- Các class CSS cũ (`.btn-delete-user`) đã được thay thế hoàn toàn bằng `.btn-toggle-status`

## Ngày cập nhật
19/11/2025
