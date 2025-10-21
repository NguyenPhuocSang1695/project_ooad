// Tìm kiếm cơ bản và gợi ý sản phẩm
// Biến toàn cục
let searchTimeout;
const MIN_CHARS_FOR_SUGGESTIONS = 2;

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

// Thiết lập giá cho các preset
function setPrice(min, max) {
  document.getElementById("minPrice").value = min;
  document.getElementById("maxPrice").value = max || "";
}

// Đặt lại các bộ lọc
function resetFilters() {
  document.getElementById("searchInput").value = "";
  if (document.getElementById("categoryFilter"))
    document.getElementById("categoryFilter").value = "";
  if (document.getElementById("minPrice"))
    document.getElementById("minPrice").value = "";
  if (document.getElementById("maxPrice"))
    document.getElementById("maxPrice").value = "";
}
