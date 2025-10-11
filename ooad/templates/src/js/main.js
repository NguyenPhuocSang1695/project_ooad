// Kiểm tra xem có đang đăng nhập hay không và hiển thị tên người dùng tương ứng
function singIn(e) {
  event.preventDefault();
  var username = document.getElementById("username").value;
  var password = document.getElementById("password").value;
  var user = {
    username: username,
    password: password,
  };

  var json = JSON.stringify(user);
  localStorage.setItem(username, json);
  localStorage.setItem("isLogin", true);
  document.getElementById("offcanvasExampleLabel").innerHTML =
    "Nguyễn Phước Sang";
}

document.addEventListener("DOMContentLoaded", function () {
  // Lấy trạng thái đăng nhập từ localStorage
  var isLogin = JSON.parse(localStorage.getItem("isLogin")) || false;

  // Kiểm tra trạng thái từ localStorage
  if (isLogin === true) {
    document.getElementById("offcanvasExampleLabel").innerHTML =
      "Nguyễn Phước Sang";
    document.querySelectorAll(".login-logout").forEach(function (element) {
      element.style.display = "none";
    });
  } else {
    document.getElementById("offcanvasExampleLabel").innerHTML =
      "Xin vui lòng đăng nhập!";
    document.querySelectorAll(".hs-ls-dx").forEach(function (element) {
      element.style.display = "none";
    });
  }
});

function clickCart() {
  var isLogin = JSON.parse(localStorage.getItem("isLogin")) || false;

  // Kiểm tra nếu đã đăng nhập
  if (isLogin === true) {
    // Nếu đã đăng nhập, chuyển tới trang giỏ hàng
    window.location.href = "thanh-toan.html";
  } else {
    // Nếu chưa đăng nhập, chuyển tới trang đăng nhập
    window.location.href = "user-login.html";
  }
}

function logOut() {
  localStorage.setItem("isLogin", false); // Cập nhật trạng thái đăng xuất
  document.getElementById("offcanvasExampleLabel").innerHTML =
    "Xin vui lòng đăng nhập";
  document.getElementById("logoutButton").style.display = "none"; // Ẩn nút Đăng xuất
}
