window.addEventListener("pageshow", function (event) {
  if (event.persisted) {
    // Reload lại trang nếu trang được load từ cache khi nhấn nút back
    window.location.reload();
  }
});
