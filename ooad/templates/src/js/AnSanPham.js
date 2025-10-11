document.addEventListener('DOMContentLoaded', function () {
    function updateCartCount() {
      fetch('check_hidden_products.php')
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          const hiddenProducts = data.hidden_products;
          const cartItems = document.querySelectorAll('.cart-item');
          let updatedCartCount = 0;

          cartItems.forEach(item => {
            const productId = item.getAttribute('data-product-id');
            if (hiddenProducts.includes(parseInt(productId))) {
              item.remove();
            } else {
              const quantity = parseInt(item.querySelector('.quantity-input').value) || 0;
              updatedCartCount += quantity;
            }
          });

          // Cập nhật số lượng hiển thị trên giỏ hàng
          const cartCountElement = document.getElementById('mni-cart-count');
          if (cartCountElement) {
            cartCountElement.textContent = updatedCartCount;
          }
        })
        .catch(error => {
          console.error('Lỗi khi cập nhật giỏ hàng:', error);
        });
    }

    // Gọi hàm cập nhật giỏ hàng ngay khi tải trang
    updateCartCount();
  });