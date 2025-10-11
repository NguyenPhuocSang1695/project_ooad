// Hiển thị/ẩn form tìm kiếm nâng cao
function toggleAdvancedSearch() {
  const searchForm = document.getElementById("advancedSearchForm");

  if (searchForm.style.display === "none") {
    searchForm.style.display = "block";
    setTimeout(() => {
      searchForm.classList.add("show");
    }, 10);
  } else {
    searchForm.classList.remove("show");
    setTimeout(() => {
      searchForm.style.display = "none";
    }, 300);
  }
}

// Xóa nội dung ô tìm kiếm
document.addEventListener("DOMContentLoaded", function () {
  const clearButton = document.getElementById("clearSearch");
  clearButton.addEventListener("click", function () {
    document.getElementById("searchInput").value = "";
    document.getElementById("searchInput").focus();
    performSearch();
  });
});

// Thiết lập giá trị cho khoảng giá với các mức cài sẵn
function setPrice(min, max) {
  document.getElementById("minPrice").value = min;
  document.getElementById("maxPrice").value = max || "";
  // Thực hiện tìm kiếm ngay khi người dùng chọn khoảng giá
  performSearch();
}

// Chuyển đổi giá tiền từ chuỗi "xxx.xxx vnđ" sang số
function extractPrice(priceString) {
  // Loại bỏ tất cả các ký tự không phải số
  return parseInt(priceString.replace(/\D/g, ""));
}

// Đặt lại tất cả các bộ lọc
function resetFilters() {
  // Đặt lại các trường tìm kiếm
  document.getElementById("searchInput").value = "";
  document.getElementById("categoryFilter").value = "";
  document.getElementById("minPrice").value = "";
  document.getElementById("maxPrice").value = "";

  // Hiển thị lại tất cả sản phẩm
  const products = document.querySelectorAll("#productList .product");
  products.forEach((product) => {
    product.style.display = "";
  });

  // Ẩn thông báo không tìm thấy sản phẩm
  document.getElementById("noResultsMessage").style.display = "none";
}
