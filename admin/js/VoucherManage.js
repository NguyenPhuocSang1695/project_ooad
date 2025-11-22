// Add voucher
document.addEventListener("DOMContentLoaded", () => {
  const addForm = document.querySelector(".voucher-form");

  if (addForm) {
    addForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      try {
        const response = await fetch("../php/addVoucher.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.text();
        alert(result.trim());

        // Xóa nội dung form sau khi thêm
        this.reset();

        // Reload nhẹ danh sách voucher (nếu có)
        if (typeof loadVoucherList === "function") {
          loadVoucherList(); // nếu bạn có hàm load lại danh sách
        } else {
          location.reload();
        }
      } catch (error) {
        alert("Lỗi khi thêm mã giảm giá!");
        console.error(error);
      }
    });
  }
});

// --- Tìm kiếm voucher ---
const searchInput = document.getElementById("searchVoucher");
const noResults = document.getElementById("noResults");

if (searchInput) {
  searchInput.addEventListener("input", function (e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll(".voucher-card");

    let hasResult = false;

    cards.forEach((card) => {
      const id = card
        .querySelector(".voucher-id strong")
        .textContent.toLowerCase(); // MGG123

      const name = card
        .querySelector(".voucher-name strong")
        .textContent.toLowerCase(); // Tên: ABC

      // so sánh theo id hoặc theo tên
      const match = id.includes(searchTerm) || name.includes(searchTerm);

      card.style.display = match ? "block" : "none";

      if (match) hasResult = true;
    });

    // Hiển thị hoặc ẩn dòng "Không tìm thấy"
    noResults.style.display = hasResult ? "none" : "block";
  });
}

// --- Mở popup chỉnh sửa ---
function editVoucher(id) {
  const card = document.querySelector(`.voucher-card[data-id="${id}"]`);
  if (!card) return;

  const name = card.querySelector(".voucher-name strong").textContent.trim();
  const discount = card
    .querySelector(".discount")
    .textContent.replace("%", "")
    .trim();
  const condition = card
    .querySelector(".detail-value:not(.discount)")
    .textContent.replace(/[^\d]/g, "");
  const status = card
    .querySelector(".voucher-status")
    .classList.contains("status-active")
    ? "active"
    : "inactive";

  document.getElementById("edit_id").value = id;
  document.getElementById("edit_name").value = name;
  document.getElementById("edit_percen_decrease").value = discount;
  document.getElementById("edit_condition").value = condition;
  document.getElementById("edit_status").value = status;
  document.getElementById("editVoucherModal").style.display = "flex";
}

// --- Đóng popup ---
function closeEditModal() {
  const modal = document.getElementById("editVoucherModal");
  if (modal) modal.style.display = "none";
}

// --- Gửi form AJAX ---
const editForm = document.getElementById("editVoucherForm");
if (editForm) {
  editForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const response = await fetch("../php/editVoucher.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.text();

    alert(result);
    location.reload();
  });
}

// --- Xóa voucher ---
// --- Xóa voucher ---
async function deleteVoucher(id, name) {
  if (confirm(`Bạn có chắc chắn muốn xóa mã giảm giá "${name}" không?`)) {
    try {
      const formData = new FormData();
      formData.append("id", id);

      const response = await fetch("../php/deleteVoucher.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.text();
      alert(result.trim());
      location.reload(); // reload lại trang để cập nhật danh sách
    } catch (error) {
      alert("Lỗi khi xóa mã giảm giá!");
      console.error(error);
    }
  }
}
