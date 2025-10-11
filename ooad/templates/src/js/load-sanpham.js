document.addEventListener("DOMContentLoaded", function () {
  const addToCartForm = document.getElementById("add-to-cart-form");
  const cartCountElement = document.getElementById("mni-cart-count");
  const cartDropdown = document.querySelector(".cart-dropdown");

  if (addToCartForm) {
    addToCartForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(addToCartForm);

      fetch("../pages/add-to-cart.php", {
        // Đã sửa lại đường dẫn
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then((data) => {
          console.log("DATA:", data); // Kiểm tra dữ liệu trả về từ server

          if (data.success) {
            console.log("Cart items:", data.cart_items);
            console.log("Total price:", data.total_price);

            // Cập nhật số lượng trên icon
            if (cartCountElement) {
              cartCountElement.textContent = data.totalQuantity;
            }

            // Cập nhật dropdown giỏ hàng
            if (cartDropdown) {
              if (data.cart_items && data.cart_items.length > 0) {
                while (cartDropdown.firstChild) {
                  cartDropdown.removeChild(cartDropdown.firstChild);
                }

                data.cart_items.forEach((item) => {
                  const cartItemDiv = document.createElement("div");
                  cartItemDiv.classList.add("cart-item");
                  cartItemDiv.innerHTML = `
                                    <img src="../${item.ImageURL}" alt="${
                    item.ProductName
                  }" class="cart-thumb"/>
                                    <div class="cart-item-details">
                                        <h5>${item.ProductName}</h5>
                                        <p>Giá: ${formatCurrency(
                                          item.Price
                                        )}</p>
                                        <p>${item.Quantity} × ${formatCurrency(
                    item.Price
                  )}</p>
                                    </div>
                                `;
                  cartDropdown.appendChild(cartItemDiv);
                });
                // Add tổng tiền vào giỏ hàng
                const totalDiv = document.createElement("div");
                totalDiv.classList.add("cart-total"); // Thêm class để dễ dàng style
                totalDiv.innerHTML = `<p>Tổng cộng: ${formatCurrency(
                  data.total_price
                )}</p>`;
                cartDropdown.appendChild(totalDiv);
              } else {
                cartDropdown.innerHTML = "<p>Giỏ hàng của bạn đang trống.</p>";
              }
            }
            // Hiển thị thông báo thành công với tên sản phẩm
            const productName =
              document.querySelector(".nametree h2").textContent;
            displayMessage(`Đã thêm "${productName}" vào giỏ hàng!`, "success");
          } else {
            console.error("Lỗi thêm vào giỏ hàng:", data.message);
            displayMessage(`Lỗi: ${data.message}`, "error"); // Hiện thị thông báo lỗi
          }
        })
        .catch((error) => {
          console.error("Lỗi fetch:", error);
          displayMessage(
            "Không thể thêm vào giỏ hàng: " + error.message,
            "error"
          );
        });
    });
  }

  // Hàm format tiền tệ (tái sử dụng)
  function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);
  }

  // Thay thế hàm displayMessage với phiên bản cải tiến
  function displayMessage(message, type = "success") {
    const messageElement = document.createElement("div");
    messageElement.classList.add("message", type);
    messageElement.textContent = message;

    // Xóa thông báo cũ nếu có
    const existingMessage = document.querySelector(".message");
    if (existingMessage) {
      existingMessage.remove();
    }

    document.body.appendChild(messageElement);

    // Thêm hiệu ứng fade out trước khi xóa
    setTimeout(() => {
      messageElement.classList.add("fade-out");
      setTimeout(() => {
        messageElement.remove();
      }, 500); // Đợi animation kết thúc rồi xóa
    }, 2500); // Hiển thị trong 2.5 giây
  }
});
