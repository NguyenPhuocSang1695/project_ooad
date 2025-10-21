document.addEventListener('DOMContentLoaded', () => {
  fetch('test.php') // Gửi request đến test.php
      .then(response => response.json()) // Chuyển đổi response thành JSON
      .then(data => {
          const container = document.getElementById('imageContainer');
          container.innerHTML = ""; // Xóa nội dung "Đang tải sản phẩm..."
          
          if (data.images && data.images.length > 0) {
              data.images.forEach(imageURL => {
                  const img = document.createElement('img');
                  img.src = imageURL;
                  img.alt = "Product Image";
                  img.className = "product-img";
                  container.appendChild(img);
              });
          } else {
              container.innerHTML = "<p>Không có sản phẩm nào.</p>";
          }
      })
      .catch(error => {
          console.error("Lỗi khi tải ảnh:", error);
          document.getElementById('imageContainer').innerHTML = "<p>Lỗi khi tải sản phẩm.</p>";
      });
});