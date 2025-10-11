function toggleAdvancedSearch() {
  const searchContainer = document.getElementById("search-filter-container");

  if (!searchContainer) {
    console.error("Không tìm thấy phần tử search-filter-container");
    return;
  }

  if (
    searchContainer.style.display === "none" ||
    searchContainer.style.display === ""
  ) {
    // Hiển thị container
    searchContainer.style.display = "block";

    // Thêm class show sau một khoảng thời gian ngắn để kích hoạt hiệu ứng
    setTimeout(() => {
      searchContainer.classList.add("show");
    }, 10);
  } else {
    // Xóa class show trước
    searchContainer.classList.remove("show");

    // Ẩn container sau khi hiệu ứng hoàn thành
    setTimeout(() => {
      searchContainer.style.display = "none";
    }, 300);
  }
}

// Ẩn mặc định khi trang tải
window.onload = function () {
  const searchContainer = document.getElementById("search-filter-container");
  if (searchContainer) {
    searchContainer.style.display = "none";

    // Thêm CSS cần thiết cho hiệu ứng
    if (!searchContainer.classList.contains("mobile-search-transition")) {
      searchContainer.classList.add("mobile-search-transition");
    }
  }
};

function performSearch() {
  var category = document.getElementById("categoryFilter").value;
  var minPrice = document.getElementById("minPrice").value;
  var maxPrice = document.getElementById("maxPrice").value;

  console.log(
    "Tìm kiếm với phân loại:",
    category,
    "Giá từ:",
    minPrice,
    "đến:",
    maxPrice
  );

  // Thêm logic gửi dữ liệu tìm kiếm lên server hoặc lọc danh sách sản phẩm
}

function resetMobileFilters() {
  document.getElementById("categoryFilter").value = "";
  document.getElementById("minPrice").value = "";
  document.getElementById("maxPrice").value = "";

  console.log("Đã đặt lại bộ lọc");
}

function setPrice(min, max) {
  if (min === 0 && max === 200000) {
    document.getElementById("minPrice").value = 0;
    document.getElementById("maxPrice").value = 200000;
  } else if (min === 200000 && max === 500000) {
    document.getElementById("minPrice").value = 200000;
    document.getElementById("maxPrice").value = 500000;
  } else {
    document.getElementById("minPrice").value = min;
    document.getElementById("maxPrice").value = max > 0 ? max : "";
  }
}
