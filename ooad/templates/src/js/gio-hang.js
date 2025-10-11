function changeQuantity(button, delta) {
  // Lấy phần tử input chứa số lượng
  const quantityInput = button
    .closest(".update-form")
    .querySelector(".quantity-input");
  let currentQuantity = parseInt(quantityInput.value, 10);
  const productId = button
    .closest(".update-form")
    .querySelector("input[name='update_product_id']").value;

  // Tính toán số lượng mới
  let newQuantity = currentQuantity + delta;

  // Kiểm tra số lượng mới không nhỏ hơn 1
  if (newQuantity < 1) return;

  // Cập nhật giá trị số lượng trong ô input
  quantityInput.value = newQuantity;

  // Gửi dữ liệu lên server để cập nhật giỏ hàng (có thể thêm AJAX để cập nhật vào session)
  const formData = new FormData();
  formData.append("product_id", productId);
  formData.append("quantity", newQuantity);

  fetch("cap-nhat-so-luong.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // Cập nhật lại tổng giỏ hàng
        updateCartTotal();
      } else {
        alert("Cập nhật thất bại: " + data.message);
      }
    })
    .catch((err) => {
      console.error("Lỗi:", err);
    });
}

function updateCartTotal() {
  let total = 0;
  document.querySelectorAll(".order").forEach((order) => {
    const quantityInput = order.querySelector(".quantity-input");
    // Lấy giá sản phẩm từ data-price
    const price = parseFloat(quantityInput.getAttribute("data-price")) || 0;
    const quantity = parseInt(quantityInput.value) || 1;
    const productTotal = price * quantity;
    total += productTotal;
  });
  // Cập nhật tổng tiền của giỏ hàng
  const totalElement = document.getElementById("total-price");
  if (totalElement) {
    totalElement.textContent = total.toLocaleString("vi-VN") + " VNĐ";
  }
}
