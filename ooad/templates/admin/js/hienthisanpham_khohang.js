const products = [
  {
    id: 1,
    name: "Bắp cải hữu cơ",
    desc: "Bắp cải hữu cơ tươi ngon",
    origin: "Việt Nam",
    comp: "100% bắp cải hữu cơ",
    weight: "500g",
    exp: "15/04/2025",
    qty: 34,
    price: 25000,
    img: "../images/image/product1.webp",
  },
  {
    id: 2,
    name: "Cải ngọt hữu cơ",
    desc: "Cải ngọt tươi sạch",
    origin: "Đà Lạt",
    comp: "100% cải ngọt tự nhiên",
    weight: "300g",
    exp: "20/04/2025",
    qty: 45,
    price: 18000,
    img: "../images/image/product2.webp",
  },
  {
    id: 3,
    name: "Cà chua Đà Lạt",
    desc: "Cà chua sạch chất lượng cao",
    origin: "Đà Lạt",
    comp: "100% cà chua tự nhiên",
    weight: "1kg",
    exp: "30/04/2025",
    qty: 60,
    price: 35000,
    img: "../images/image/product3.webp",
  },
  {
    id: 4,
    name: "Khoai tây",
    desc: "Khoai tây sạch",
    origin: "Việt Nam",
    comp: "100% khoai tây",
    weight: "1kg",
    exp: "25/04/2025",
    qty: 5,
    price: 30000,
    img: "../images/image/product1.webp",
  },
  {
    id: 5,
    name: "Cà rốt",
    desc: "Cà rốt hữu cơ",
    origin: "Đà Lạt",
    comp: "100% cà rốt",
    weight: "500g",
    exp: "18/04/2025",
    qty: 15,
    price: 22000,
    img: "../images/image/product2.webp",
  },
];

let currentPage = 1;
const itemsPerPage = 5;
let filteredProducts = [...products];
let activeProductId = null; // Biến để theo dõi sản phẩm đang được hiển thị

function renderProducts() {
  const tbody = document.getElementById("productsBody");
  tbody.innerHTML = "";
  const start = (currentPage - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  const paginatedProducts = filteredProducts.slice(start, end);

  paginatedProducts.forEach((product) => {
    const row = document.createElement("tr");
    row.className = product.qty < 10 ? "low-stock" : "";
    row.innerHTML = `
            <td><img src="${
              product.img
            }" alt="Product" class="product-image"></td>
            <td>${product.name}</td>
            <td>${product.exp}</td>
            <td>${product.qty} ${
      product.qty < 10 ? '<span class="low-stock-warning">(Sắp hết)</span>' : ""
    }</td>
            <td>${product.price.toLocaleString()} VND</td>
            <td style="text-align: right;">
                <button class="action-btn" onclick="showProductDetails(${
                  product.id
                })">Xử lý</button>
            </td>
        `;
    tbody.appendChild(row);
  });

  updatePagination();
}

function showProductDetails(productId) {
  if (activeProductId === productId) {
    document.getElementById("productDetailsOverlay").classList.remove("active");
    activeProductId = null;
    return;
  }

  const product = products.find((p) => p.id === productId);
  const overlay = document.getElementById("productDetailsOverlay");
  const detailsContent = document.getElementById("productDetailsContent");

  detailsContent.innerHTML = `
        <h3>Thông tin sản phẩm</h3>
        <div class="details-grid">
            <p><strong>Tên:</strong> ${product.name}</p>
            <p><strong>Mô tả:</strong> ${product.desc}</p>
            <p><strong>Xuất xứ:</strong> ${product.origin}</p>
            <p><strong>Thành phần:</strong> ${product.comp}</p>
            <p><strong>Khối lượng:</strong> ${product.weight}</p>
            <p><strong>Hạn sử dụng:</strong> ${product.exp}</p>
            <p><strong>Số lượng:</strong> ${product.qty}</p>
            <p><strong>Giá:</strong> ${product.price.toLocaleString()} VND</p>
        </div>
        <div class="popup-actions">
            <button onclick="showEditForm(${
              product.id
            })" class="edit-btn">Sửa</button>
            <button onclick="confirmDeleteProduct(${
              product.id
            })" class="delete-btn">Xóa</button>
            <button onclick="closeDetails()" class="close-btn">Đóng</button>
        </div>
    `;

  overlay.classList.add("active");
  activeProductId = productId;
}
function showEditForm(productId) {
  const product = products.find((p) => p.id === productId);
  const detailsContent = document.getElementById("productDetailsContent");

  detailsContent.innerHTML = `
        <h3>Chỉnh sửa sản phẩm</h3>
        <div class="form-grid">
            <div class="form-group">
                <img src="${product.img}" class="edit-image-preview" id="editImagePreview-${product.id}" alt="Current Image">
                <input type="file" class="image-upload" accept="image/*" onchange="updateImagePreview(event, ${product.id})">
                <button class="remove-image-btn" onclick="removeImage(${product.id})">Xóa ảnh</button>
            </div>
            <div class="form-group">
                <label style="font-size: 13px; font-weight: bold;">Tên sản phẩm:</label>
                <input type="text" value="${product.name}" class="form-input" id="editName-${product.id}">
                <label style="font-size: 13px; font-weight: bold;">Mô tả:</label>
                <input type="text" value="${product.desc}" class="form-input" id="editDesc-${product.id}">
                <label style="font-size: 13px; font-weight: bold;">Xuất xứ:</label>
                <input type="text" value="${product.origin}" class="form-input" id="editOrigin-${product.id}">
                <label style="font-size: 13px; font-weight: bold;">Thành phần:</label>
                <input type="text" value="${product.comp}" class="form-input" id="editComp-${product.id}">
                <label style="font-size: 13px; font-weight: bold;">Khối lượng tịnh:</label>
                <input type="text" value="${product.weight}" class="form-input" id="editWeight-${product.id}">
                <label style="font-size: 13px; font-weight: bold;">Hạn sử dụng:</label>
                <input type="text" value="${product.exp}" class="form-input" id="editExp-${product.id}">
                <label style="font-size: 13px; font-weight: bold;">Số lượng:</label>
                <input type="number" value="${product.qty}" class="form-input" id="editQty-${product.id}" min="0">
                <label style="font-size: 13px; font-weight: bold;">Giá bán (VND):</label>
                <input type="number" value="${product.price}" class="form-input" id="editPrice-${product.id}" min="0">
            </div>
        </div>
        <div class="form-actions">
            <button onclick="saveEdit(${product.id})" class="save-btn">Lưu</button>
            <button onclick="showProductDetails(${product.id})" class="cancel-btn">Hủy</button>
        </div>
    `;
}
function updateImagePreview(event, productId) {
  const preview = document.getElementById(`editImagePreview-${productId}`);
  preview.src = URL.createObjectURL(event.target.files[0]);
}

function removeImage(productId) {
  if (confirm("Bạn có chắc muốn xóa ảnh này không?")) {
    const preview = document.getElementById(`editImagePreview-${productId}`);
    preview.src = "#";
    preview.style.display = "none";
  }
}

function saveEdit(productId) {
  if (confirm("Bạn có chắc chắn muốn lưu thay đổi?")) {
    const productIndex = products.findIndex((p) => p.id === productId);
    products[productIndex] = {
      ...products[productIndex],
      name: document.getElementById(`editName-${productId}`).value,
      desc: document.getElementById(`editDesc-${productId}`).value,
      origin: document.getElementById(`editOrigin-${productId}`).value,
      comp: document.getElementById(`editComp-${productId}`).value,
      weight: document.getElementById(`editWeight-${productId}`).value,
      exp: document.getElementById(`editExp-${productId}`).value,
      qty: parseInt(document.getElementById(`editQty-${productId}`).value),
      price: parseInt(document.getElementById(`editPrice-${productId}`).value),
      img: document.getElementById(`editImagePreview-${productId}`).src,
    };
    filteredProducts = [...products];
    alert("Đã lưu thay đổi thành công!");
    showProductDetails(productId); // Hiển thị lại chi tiết sau khi lưu
  }
}

function closeDetails() {
  document.getElementById("productDetailsOverlay").classList.remove("active");
  activeProductId = null;
}

function updatePagination() {
  const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
  document.getElementById(
    "pageInfo"
  ).textContent = `Trang ${currentPage} / ${totalPages}`;
  document.querySelector(".page-btn:first-child").disabled = currentPage === 1;
  document.querySelector(".page-btn:last-child").disabled =
    currentPage === totalPages;
}

function prevPage() {
  if (currentPage > 1) {
    currentPage--;
    renderProducts();
  }
}

function nextPage() {
  const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
  if (currentPage < totalPages) {
    currentPage++;
    renderProducts();
  }
}

function searchProducts() {
  const searchTerm = document
    .querySelector(".search-input")
    .value.toLowerCase();
  filteredProducts = products.filter(
    (product) =>
      product.name.toLowerCase().includes(searchTerm) ||
      product.desc.toLowerCase().includes(searchTerm)
  );
  currentPage = 1;
  renderProducts();
}

function toggleAddProductForm() {
  document.getElementById("addProductForm").classList.toggle("active");
}

function previewImage(event) {
  const preview = document.getElementById("imagePreview");
  preview.src = URL.createObjectURL(event.target.files[0]);
  preview.style.display = "block";
}
function showAddProductPopup() {
  const overlay = document.getElementById("addProductOverlay");
  // Reset form khi mở popup
  document.getElementById("addName").value = "";
  document.getElementById("addDesc").value = "";
  document.getElementById("addOrigin").value = "";
  document.getElementById("addComp").value = "";
  document.getElementById("addWeight").value = "";
  document.getElementById("addExp").value = "";
  document.getElementById("addQty").value = "";
  document.getElementById("addPrice").value = "";
  const imagePreview = document.getElementById("imagePreview");
  imagePreview.src = "#";
  imagePreview.style.display = "none";

  overlay.classList.add("active");
}
function closeAddProductPopup() {
  if (confirm("Bạn có muốn hủy thêm sản phẩm này?")) {
    document.getElementById("addProductOverlay").classList.remove("active");
  }
}
// function confirmAddProduct() {
//     if (confirm('Bạn có chắc chắn muốn thêm sản phẩm này?')) {
//         const newProduct = {
//             id: products.length + 1,
//             name: document.getElementById('addName').value,
//             desc: document.getElementById('addDesc').value,
//             origin: document.getElementById('addOrigin').value,
//             comp: document.getElementById('addComp').value,
//             weight: document.getElementById('addWeight').value,
//             exp: document.getElementById('addExp').value,
//             qty: parseInt(document.getElementById('addQty').value),
//             price: parseInt(document.getElementById('addPrice').value),
//             img: document.getElementById('imagePreview').src || '../image/default.jpg'
//         };
//         products.push(newProduct);
//         filteredProducts = [...products];
//         alert('Đã thêm sản phẩm thành công!');
//         document.getElementById('addProductOverlay').classList.remove('active');
//         renderProducts();
//     }
// }

// function cancelAddProduct() {
//     if (confirm('Bạn có muốn hủy thêm sản phẩm này?')) {
//         toggleAddProductForm();
//     }
// }

function confirmDeleteProduct(productId) {
  if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này?")) {
    const productIndex = products.findIndex((p) => p.id === productId);
    products.splice(productIndex, 1);
    filteredProducts = [...products];
    alert("Đã xóa sản phẩm thành công!");
    document.getElementById("productDetailsSection").classList.remove("active");
    activeProductId = null;
    renderProducts();
  }
}

function deleteProduct(productId) {
  if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
      fetch('/Web/admin/php/delete-product.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({ productId: productId })
      })
      .then(response => {
          if (!response.ok) {
              throw new Error('Network response was not ok');
          }
          return response.text();
      })
      .then(result => {
          if (result === "deleted" || result === "hidden") {
              // Ẩn sản phẩm khỏi giao diện trong cả 2 trường hợp
              const productElement = document.querySelector(`[data-product-id="${productId}"]`);
              if (productElement) {
                  productElement.remove();
              }
              alert(result === "deleted" ? "Đã xóa sản phẩm thành công!" : "Sản phẩm đã được ẩn do tồn tại trong đơn hàng!");
              
              // Refresh trang sau khi xóa
              window.location.reload();
          } else {
              alert("Có lỗi xảy ra khi xóa sản phẩm!");
          }
      })
      .catch(error => {
          console.error('Error:', error);
          alert("Có lỗi xảy ra khi xóa sản phẩm: " + error.message);
      });
  }
}

function editProduct(productId) {
  fetch(`../php/get-product.php?id=${productId}`)
    .then(response => response.json())
    .then(product => {
      document.getElementById('editProductId').value = product.ProductID;
      document.getElementById('editProductName').value = product.ProductName;
      document.getElementById('editCategoryID').value = product.CategoryID;
      document.getElementById('editPrice').value = product.Price;
      document.getElementById('editDescription').value = product.Description;
      document.getElementById('status').value = product.Status; // Load status
      
      const currentImage = document.getElementById('currentImage');
      currentImage.src = '../../' + product.ImageURL;
      currentImage.style.display = 'block';
      
      document.getElementById('editProductOverlay').style.display = 'flex';
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Có lỗi khi tải thông tin sản phẩm!');
    });
}

function closeEditOverlay() {
    const overlay = document.getElementById('editProductOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Thêm event listener cho nút đóng
document.addEventListener('DOMContentLoaded', function() {
    const closeButton = document.querySelector('#editProductOverlay .close-btn');
    if (closeButton) {
        closeButton.addEventListener('click', closeEditOverlay);
    }
});

document.addEventListener("DOMContentLoaded", renderProducts);

function searchProducts(page = 1, specificProductId = null) {
  const searchInput = document.querySelector('.search-input');
  const searchTerm = searchInput.value.trim();
  const tableBody = document.getElementById('productsBody');
  const paginationContainer = document.querySelector('.pagination');

  tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Đang tìm kiếm...</td></tr>';

  const formData = new FormData();
  formData.append('search', searchTerm);
  formData.append('page', page);
  if (specificProductId) {
    formData.append('product_id', specificProductId);
  }

  fetch('../php/search-products.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .catch(error => {
      console.error('Parse Error:', error);
      return { error: 'Invalid JSON response' };
    })
    .then(data => {
      if (data.error) {
        console.error('Server Error:', data.error);
        tableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">Có lỗi xảy ra. Vui lòng thử lại</td></tr>`;
        paginationContainer.innerHTML = '';
        return;
      }

      // Hiển thị sản phẩm
      tableBody.innerHTML = '';
      if (!data.products || data.products.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Không tìm thấy sản phẩm nào phù hợp</td></tr>';
        paginationContainer.innerHTML = '';
        return;
      }

      data.products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td><img src="${product.image}" alt="${product.name}" style="width: 100px; height: 100px; object-fit: cover;"></td>
          <td>${product.name}</td>
          <td>${product.category}</td>
          <td>${product.price}</td>
          <td class="actions">
            <button class="btn btn-warning btn-sm" onclick="editProduct(${product.id})">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
          </td>
        `;
        tableBody.appendChild(row);
      });

      // Xử lý phân trang
      updatePagination(data.pagination);
    })
    .catch(error => {
      console.error('Network Error:', error);
      tableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">Lỗi kết nối: ${error.message}</td></tr>`;
      paginationContainer.innerHTML = '';
    });
}

function updatePagination(pagination) {
  const paginationContainer = document.querySelector('.pagination');
  if (!pagination || pagination.totalPages <= 1) {
    paginationContainer.innerHTML = '';
    return;
  }

  let paginationHTML = '<ul class="pagination justify-content-center">';

  // Nút Previous
  if (pagination.currentPage > 1) {
    paginationHTML += `
      <li class="page-item">
        <a class="page-link" href="#" onclick="searchProducts(${pagination.currentPage - 1}); return false;"><<</a>
      </li>`;
  }

  // Hiển thị các số trang
  for (let i = 1; i <= pagination.totalPages; i++) {
    if (
      i === 1 ||
      i === pagination.totalPages ||
      (i >= pagination.currentPage - 2 && i <= pagination.currentPage + 2)
    ) {
      paginationHTML += `
        <li class="page-item ${i === pagination.currentPage ? 'active' : ''}">
          <a class="page-link" href="#" onclick="searchProducts(${i}); return false;">${i}</a>
        </li>`;
    } else if (
      i === pagination.currentPage - 3 ||
      i === pagination.currentPage + 3
    ) {
      paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
  }

  // Nút Next
  if (pagination.currentPage < pagination.totalPages) {
    paginationHTML += `
      <li class="page-item">
        <a class="page-link" href="#" onclick="searchProducts(${pagination.currentPage + 1}); return false;">>></a>
      </li>`;
  }

  paginationHTML += '</ul>';
  paginationContainer.innerHTML = paginationHTML;
}
