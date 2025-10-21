'use strict'

// Lấy tất cả các form cần áp dụng xác thực Bootstrap
const forms = document.querySelectorAll('.needs-validation')

// Duyệt qua tất cả các form
Array.from(forms).forEach(form => {
  form.addEventListener('submit', event => {
    // Kiểm tra tính hợp lệ của form
    if (!form.checkValidity()) {
      event.preventDefault();  // Ngừng hành động gửi form
      event.stopPropagation(); // Ngừng sự kiện gửi tiếp
    }
    
    // Kiểm tra mật khẩu và xác nhận mật khẩu
    let password = document.getElementById("password").value;
    let cnfrmPassword = document.getElementById("cnfrm-password").value;
    let message = document.getElementById("message");

    // Kiểm tra mật khẩu có trống không
    if (password.length != 0) {
      if (password != cnfrmPassword) {
        message.textContent = "Sai mật khẩu";  // Hiển thị thông báo nếu mật khẩu không khớp
        message.style.color = "#ff4d4d";  // Nền đỏ
        event.preventDefault();  // Ngừng gửi form nếu mật khẩu không khớp
      } else {
        message.textContent = "Đúng";  // Hiển thị thông báo nếu mật khẩu khớp
        message.style.color = "#1dcd59";  // Nền xanh
      }
    } else {
      alert("form rỗng!");  // Nếu mật khẩu trống
      message.textContent = "";
      event.preventDefault();  // Ngừng gửi form nếu mật khẩu trống
    }

    form.classList.add('was-validated');  // Thêm lớp 'was-validated' vào form
  }, false);
});
