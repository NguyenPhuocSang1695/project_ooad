// Xử lý sự kiện submit form
document.getElementById("productForm").addEventListener("submit", function (e) {
  e.preventDefault();

  let formData = new FormData(this);

  fetch("../php/add-product.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message || "Sản phẩm đã được thêm thành công!");
        window.location.reload();
        document.getElementById("productForm").reset(); // Reset form
        // Ẩn overlay sau khi thêm thành công
        document.getElementById("addProductOverlay").style.display = "none";
        // Reload trang sau khi thêm thành công
        window.location.reload();
      } else {
        alert(
          data.message ||
            "Có lỗi xảy ra hoặc sản phẩm đã tồn tại. Vui lòng thử lại."
        );
      }
    })
    .catch((error) => {
      // console.error("Lỗi:", error);
      alert("Có lỗi xảy ra. Vui lòng thử lại.");
    });
});

// Xử lý sự kiện nhấn Enter trong form
document
  .getElementById("productForm")
  .addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      e.preventDefault(); // Ngăn chặn hành vi mặc định của phím Enter
      // Trigger nút submit form
      document.querySelector("#productForm button[type='submit']").click();
    }
  });
