// Global variable để theo dõi trang hiện tại
let currentPage = 1;

// Hàm chuyển đổi phương thức thanh toán sang Tiếng Việt
function formatPaymentMethod(method) {
  if (!method) return 'Không rõ';
  
  // Normalize method to lowercase for comparison
  const normalizedMethod = method.toLowerCase().trim();
  
  const paymentMethods = {
    'cod': 'Thanh toán khi nhận hàng',
    'banking': 'Chuyển khoản ngân hàng',
    'momo': 'Ví điện tử MoMo',
    'vnpay': 'VNPay',
    'cash': 'Tiền mặt'
  };
  
  return paymentMethods[normalizedMethod] || method;
}

document.addEventListener("DOMContentLoaded", function () {
  const filterForm = document.getElementById("filter-form");
  const filterModal = new bootstrap.Modal(
    document.getElementById("filterModal")
  );

  // Load danh sách voucher khi modal filter mở
  const voucherFilterSelect = document.getElementById("voucher-filter");
  const specificVoucherContainer = document.getElementById("specific-voucher-container");
  const specificVoucherSelect = document.getElementById("specific-voucher");

  // Hàm load danh sách voucher
  window.loadVouchersList = function () {
    fetch("../php/get-vouchers.php")
      .then((response) => response.json())
      .then((data) => {
        console.log("Vouchers data:", data);
        if (data.success && data.data && data.data.length > 0) {
          specificVoucherSelect.innerHTML = '<option value="">-- Tất cả voucher --</option>';
          data.data.forEach((voucher) => {
            const option = document.createElement("option");
            option.value = voucher.id;
            option.textContent = `${voucher.name} (${voucher.percen_decrease}% giảm)`;
            specificVoucherSelect.appendChild(option);
            console.log("Added voucher:", voucher.name);
          });
        } else {
          console.warn("No vouchers found or data.success is false");
          specificVoucherSelect.innerHTML = '<option value="">-- Không có voucher nào --</option>';
        }
      })
      .catch((error) => console.error("Error loading vouchers:", error));
  };

  // Load vouchers khi filter modal mở
  document.getElementById("filterModal").addEventListener("show.bs.modal", function () {
    window.loadVouchersList();
  });

  // Event listener khi thay đổi voucher-filter select
  if (voucherFilterSelect) {
    voucherFilterSelect.addEventListener("change", function () {
      if (this.value === "has_voucher") {
        specificVoucherContainer.style.display = "block";
      } else {
        specificVoucherContainer.style.display = "none";
        specificVoucherSelect.value = "";
      }
    });
  }

  // Event listener cho nút "Xem chi tiết" đơn hàng (view-btn)
  document.addEventListener("click", function (e) {
    if (e.target.closest(".view-btn")) {
      e.preventDefault();
      const row = e.target.closest("tr");
      const orderId = row?.querySelector("td:first-child")?.textContent?.trim();
      if (orderId) {
        console.log('[VIEW_ORDER] Order ID:', orderId);
        showOrderDetailModal(orderId);
      }
    }
  });

  if (filterForm) {
    filterForm.addEventListener("submit", function (e) {
      e.preventDefault();
      currentPage = 1; // Đặt lại về trang 1 khi submit form
      filterOrders(new FormData(filterForm)); // Chỉ gọi filterOrders khi submit form
      filterModal.hide(); // Đóng modal sau khi áp dụng bộ lọc
    });
  }

  const orderTableBody = document.getElementById("order-table-body");
  const districtInput = document.getElementById("district-input");
  const districtSuggestions = document.getElementById("district-suggestions");
  const cityInput = document.getElementById("city-input");
  const citySuggestions = document.getElementById("city-suggestions");
  const prevPageButton = document.getElementById("prevPage");
  const pageNumbersContainer = document.getElementById("pageNumbers");
  const nextPageButton = document.getElementById("nextPage");

  const limit = 5;
  const urlParams = new URLSearchParams(window.location.search);
  currentPage = parseInt(urlParams.get("page")) || 1;

  window.applyFilters = function () {
    currentPage = 1;
    filterOrders();
  };

  window.filterOrders = function (formData = null) {
    const dateFrom =
      formData?.get("date_from") ||
      document.getElementById("date-from")?.value ||
      "";
    const dateTo =
      formData?.get("date_to") ||
      document.getElementById("date-to")?.value ||
      "";
    const orderStatus =
      formData?.get("order_status") ||
      document.getElementById("order-status")?.value ||
      "all";
    const priceMin =
      formData?.get("price_min") ||
      document.getElementById("price-min")?.value ||
      "";
    const priceMax =
      formData?.get("price_max") ||
      document.getElementById("price-max")?.value ||
      "";
    const voucherFilter =
      formData?.get("voucher_filter") ||
      document.getElementById("voucher-filter")?.value ||
      "";
    const specificVoucher =
      formData?.get("specific_voucher") ||
      document.getElementById("specific-voucher")?.value ||
      "";

    const params = new URLSearchParams({
      page: currentPage,
      limit: limit,
    });

    if (dateFrom) params.set("date_from", dateFrom);
    if (dateTo) params.set("date_to", dateTo);
    if (orderStatus && orderStatus !== "all")
      params.set("order_status", orderStatus);
    if (priceMin) params.set("price_min", priceMin);
    if (priceMax) params.set("price_max", priceMax);
    if (voucherFilter) params.set("voucher_filter", voucherFilter);
    if (specificVoucher && voucherFilter === "has_voucher") params.set("specific_voucher", specificVoucher);

    window.history.pushState(
      {},
      "",
      `${window.location.pathname}?${params.toString()}`
    );

    fetch(`../php/filter_orders.php?${params.toString()}`)
      .then((response) => {
        return response.text().then((text) => {
          console.log("Raw response from filter_orders:", text);
          if (!response.ok) {
            throw new Error(
              `HTTP error! Status: ${response.status}, Response: ${text}`
            );
          }
          try {
            return JSON.parse(text);
          } catch (e) {
            throw new Error(`Invalid JSON: ${e.message}, Response: ${text}`);
          }
        });
      })
      .then((data) => {
        if (!orderTableBody) {
          console.error("Element order-table-body not found");
          return;
        }
        orderTableBody.innerHTML = "";
        if (data.success && data.orders && data.orders.length > 0) {
          data.orders.forEach((order) => {
            const row = document.createElement("tr");
            row.style.cursor = "pointer";
            row.addEventListener("click", function (e) {
              showOrderDetailModal(order.madonhang);
            });

            row.innerHTML = `
              <td>${order.madonhang || ""}</td>
              <td class="hide-index-tablet" title="${
                order.receiver_name
              }">${truncateText(order.receiver_name)}</td>
              <td>${formatDate(order.ngaytao) || ""}</td>
              <td class="hide-index-mobile">${formatCurrency(
                order.giatien || 0
              )}</td>
              <td>${order.receiver_phone || ""}</td>
            `;
            orderTableBody.appendChild(row);
          });
        } else {
          orderTableBody.innerHTML =
            '<tr><td colspan="5" class="no-data">Không có đơn hàng nào phù hợp</td></tr>';
        }
        const totalPages =
          data.total_pages !== undefined ? data.total_pages : 1;
        updatePagination(totalPages);
      })
      .catch((error) => {
        console.error("Error fetching orders:", error);
        if (orderTableBody) {
          orderTableBody.innerHTML = `<tr><td colspan="5" class="error-message">Đã xảy ra lỗi: ${error.message}</td></tr>`;
        }
      });
  };

  function truncateText(text, maxLength = 20) {
    if (text.length > maxLength) {
      return text.substring(0, maxLength) + "...";
    }
    return text;
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString("vi-VN", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    })
      .format(amount)
      .replace("₫", "");
  }

  function formatAddress(address, district, province) {
    return `${address}, ${district}, ${province}`;
  }

  function updatePagination(totalPages) {
    if (!pageNumbersContainer) {
      console.error("Element pageNumbers not found");
      return;
    }

    pageNumbersContainer.innerHTML = "";
    totalPages = totalPages > 0 ? totalPages : 1;

    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    // First page button
    if (startPage > 1) {
      const firstPageBtn = document.createElement("button");
      firstPageBtn.textContent = "1";
      firstPageBtn.classList.add("page-btn");
      firstPageBtn.addEventListener("click", () => {
        currentPage = 1;
        filterOrders();
      });
      pageNumbersContainer.appendChild(firstPageBtn);

      if (startPage > 2) {
        const ellipsis = document.createElement("span");
        ellipsis.textContent = "...";
        ellipsis.classList.add("ellipsis");
        pageNumbersContainer.appendChild(ellipsis);
      }
    }

    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
      const pageButton = document.createElement("button");
      pageButton.textContent = i;
      pageButton.classList.add("page-btn");
      if (i === currentPage) {
        pageButton.classList.add("active");
      }
      pageButton.addEventListener("click", () => {
        currentPage = i;
        filterOrders();
      });
      pageNumbersContainer.appendChild(pageButton);
    }

    // Last page button
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        const ellipsis = document.createElement("span");
        ellipsis.textContent = "...";
        ellipsis.classList.add("ellipsis");
        pageNumbersContainer.appendChild(ellipsis);
      }

      const lastPageBtn = document.createElement("button");
      lastPageBtn.textContent = totalPages;
      lastPageBtn.classList.add("page-btn");
      lastPageBtn.addEventListener("click", () => {
        currentPage = totalPages;
        filterOrders();
      });
      pageNumbersContainer.appendChild(lastPageBtn);
    }

    if (prevPageButton) {
      prevPageButton.disabled = currentPage === 1;
      prevPageButton.onclick = () => {
        if (currentPage > 1) {
          currentPage--;
          filterOrders();
        }
      };
    }

    if (nextPageButton) {
      nextPageButton.disabled = currentPage === totalPages;
      nextPageButton.onclick = () => {
        if (currentPage < totalPages) {
          currentPage++;
          filterOrders();
        }
      };
    }

    // Update URL with current page
    const params = new URLSearchParams(window.location.search);
    params.set("page", currentPage);
    window.history.pushState(
      {},
      "",
      `${window.location.pathname}?${params.toString()}`
    );
  }

  function handleDistrictInput() {
    if (!districtInput || !districtSuggestions) return;

    districtInput.addEventListener("input", function () {
      const query = this.value.trim();
      if (query.length >= 1) {
        fetch(
          `../php/get_Address.php?type=district&query=${encodeURIComponent(
            query
          )}`
        )
          .then((response) => {
            if (!response.ok) {
              throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
          })
          .then((text) => {
            console.log("Raw response from get_Address (district):", text);
            return JSON.parse(text);
          })
          .then((data) => {
            districtSuggestions.innerHTML = "";
            districtSuggestions.style.display = "block";
            if (data.success) {
              data.data.forEach((district) => {
                const li = document.createElement("li");
                li.textContent = district;
                li.addEventListener("click", () => {
                  districtInput.value = district;
                  districtSuggestions.style.display = "none";
                });
                districtSuggestions.appendChild(li);
              });
            }
          })
          .catch((error) => {
            console.error("Error fetching district suggestions:", error);
          });
      } else {
        districtSuggestions.style.display = "none";
      }
    });

    document.addEventListener("click", function (e) {
      if (e.target !== districtInput && e.target !== districtSuggestions) {
        districtSuggestions.style.display = "none";
      }
    });
  }

  function handleProvinceInput() {
    if (!cityInput || !citySuggestions) return;

    cityInput.addEventListener("input", function () {
      const query = this.value.trim();
      if (query.length >= 1) {
        fetch(
          `../php/get_Address.php?type=city&query=${encodeURIComponent(query)}`
        )
          .then((response) => {
            if (!response.ok) {
              throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
          })
          .then((text) => {
            console.log("Raw response from get_Address (province):", text);
            return JSON.parse(text);
          })
          .then((data) => {
            citySuggestions.innerHTML = "";
            citySuggestions.style.display = "block";
            if (data.success) {
              data.data.forEach((province) => {
                const li = document.createElement("li");
                li.textContent = province;
                li.addEventListener("click", () => {
                  cityInput.value = province;
                  citySuggestions.style.display = "none";
                  if (districtInput) {
                    districtInput.value = "";
                  }
                });
                citySuggestions.appendChild(li);
              });
            }
          })
          .catch((error) => {
            console.error("Error fetching city suggestions:", error);
          });
      } else {
        citySuggestions.style.display = "none";
      }
    });

    document.addEventListener("click", function (e) {
      if (e.target !== cityInput && e.target !== citySuggestions) {
        citySuggestions.style.display = "none";
      }
    });
  }

  function showUpdateStatusPopup(orderId, currentStatus) {
    const overlay = document.getElementById("updateStatusOverlay");
    if (!overlay) return;

    const statusOptions = document.getElementById("statusOptions");
    if (!statusOptions) return;

    const statusFlow = {
      execute: ["confirmed", "fail"], // Chờ xác nhận → Đã xác nhận hoặc Đã hủy
      confirmed: ["ship", "fail"], // Đã xác nhận → Đang giao hoặc Đã hủy
      ship: ["success", "fail"], // Đang giao → Đã giao hoặc Đã hủy
      success: [], // Đã giao → Kết thúc
      fail: [], // Đã hủy → Kết thúc
    };

    const statusLabels = {
      execute: "Chờ xác nhận",
      confirmed: "Đã xác nhận",
      ship: "Đang giao",
      success: "Hoàn thành",
      fail: "Đã hủy",
    };

    statusOptions.innerHTML = "";

    statusFlow[currentStatus]?.forEach((status) => {
      const button = document.createElement("button");
      button.textContent = statusLabels[status];
      button.addEventListener("click", () => {
        if (status === "fail") {
          showCancelConfirmation(orderId, status);
        } else {
          updateOrderStatus(orderId, status);
        }
        overlay.style.display = "none";
      });
      statusOptions.appendChild(button);
    });

    const currentStatusButton = document.createElement("button");
    currentStatusButton.textContent = statusLabels[currentStatus];
    currentStatusButton.disabled = true;
    currentStatusButton.classList.add("current-status");
    statusOptions.appendChild(currentStatusButton);

    const orderIdDisplay = document.getElementById("orderIdDisplay");
    if (orderIdDisplay) orderIdDisplay.textContent = orderId;

    overlay.style.display = "flex";
  }

  function showCancelConfirmation(orderId, status) {
    const confirmOverlay = document.createElement("div");
    confirmOverlay.className = "overlay";
    confirmOverlay.style.display = "flex";
    confirmOverlay.innerHTML = `
      <div class="popup" style="max-width: 400px;">
        <h3>Xác nhận hủy đơn hàng</h3>
        <p>Bạn có chắc chắn muốn hủy đơn hàng này không?</p>
        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
          <button class="btn btn-secondary" id="cancelNoBtn">Không</button>
          <button class="btn btn-danger" id="cancelYesBtn">Có, hủy đơn hàng</button>
        </div>
      </div>
    `;
    document.body.appendChild(confirmOverlay);

    // Thêm event listeners cho các nút
    document.getElementById('cancelNoBtn').addEventListener('click', () => {
      confirmOverlay.remove();
    });

    document.getElementById('cancelYesBtn').addEventListener('click', () => {
      confirmOverlay.remove();
      updateOrderStatus(orderId, status);
    });
  }

  function updateOrderStatus(orderId, newStatus) {
    fetch("../php/updateStatus.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        orderId: orderId,
        status: newStatus,
      }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.text();
      })
      .then((text) => {
        console.log("Raw response from updateStatus:", text);
        return JSON.parse(text);
      })
      .then((data) => {
        if (data.success) {
          const statusLabels = {
            execute: "Chờ xác nhận",
            confirmed: "Đã xác nhận",
            ship: "Đang giao",
            success: "Hoàn thành",
            fail: "Đã hủy",
          };
          showNotification(
            `Đơn hàng đã được cập nhật thành "${statusLabels[newStatus]}"!`,
            "success"
          );
          filterOrders();
        } else {
          showNotification(
            "Lỗi khi cập nhật trạng thái: " + (data.error || "Unknown error"),
            "error"
          );
        }
      })
      .catch((error) => {
        showNotification("Đã xảy ra lỗi: " + error.message, "error");
      });
  }

  function showNotification(message, type = "info") {
    // Xóa notification cũ nếu còn tồn tại
    const existingNotification = document.querySelector(".notification");
    if (existingNotification) {
      existingNotification.remove();
    }

    // Tạo notification mới
    const notification = document.createElement("div");
    notification.className = `notification ${type}`;
    notification.style.visibility = "hidden"; // Ẩn ban đầu để tránh nhấp nháy

    // Thêm icon phù hợp với loại thông báo
    let icon = "";
    switch (type) {
      case "success":
        icon = '<i class="fa-solid fa-circle-check"></i> ';
        break;
      case "error":
        icon = '<i class="fa-solid fa-circle-xmark"></i> ';
        break;
      case "info":
        icon = '<i class="fa-solid fa-circle-info"></i> ';
        break;
    }

    notification.innerHTML = icon + message;

    // Thêm vào body
    document.body.appendChild(notification);

    // Force reflow
    notification.offsetHeight;

    // Hiển thị notification
    notification.style.visibility = "visible";
    notification.classList.add("show");

    // Tự động ẩn sau 2 giây
    setTimeout(() => {
      notification.classList.add("hide");
      notification.classList.remove("show");

      // Đợi animation kết thúc rồi mới xóa element
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove();
        }
      }, 300);
    }, 2000);
  }

  function initPage() {
    const filterForm = document.getElementById("filter-form");
    const filterModal = new bootstrap.Modal(
      document.getElementById("filterModal")
    );

    const urlParams = new URLSearchParams(window.location.search);
    const orderStatus = urlParams.get("order_status");
    if (orderStatus) {
      const orderStatusSelect = document.getElementById("order-status");
      if (orderStatusSelect) {
        orderStatusSelect.value = orderStatus;
        filterOrders();
      }
    }

    if (filterForm) {
      filterForm.addEventListener("submit", function (e) {
        e.preventDefault();
        currentPage = 1;
        filterOrders(new FormData(filterForm));
        filterModal.hide();
      });
    }

    // Thêm sự kiện cho dropdown order-status
    const orderStatusSelect = document.getElementById("order-status");
    if (orderStatusSelect) {
      orderStatusSelect.addEventListener("change", function () {
        // currentPage = 1; // Đặt lại về trang 1 khi thay đổi trạng thái
        // filterOrders();
      });
    }

    handleDistrictInput();
    handleProvinceInput();

    filterOrders();
  }

  initPage();
});

function initFilters() {
  const desktopForm = document.getElementById("filter-form-desktop");
  const mobileForm = document.getElementById("filter-form-mobile");
  const filterModal = new bootstrap.Modal(
    document.getElementById("filterModal")
  );

  // Xử lý form desktop
  if (desktopForm) {
    desktopForm.addEventListener("submit", function (e) {
      e.preventDefault();
      currentPage = 1; // Đặt lại về trang 1 khi submit form desktop
      filterOrders(new FormData(desktopForm));
    });
  }

  // Xử lý form mobile
  if (mobileForm) {
    mobileForm.addEventListener("submit", function (e) {
      e.preventDefault();
      currentPage = 1; // Đặt lại về trang 1 khi submit form mobile
      filterOrders(new FormData(mobileForm));
      filterModal.hide();
    });
  }

  // Đồng bộ dữ liệu giữa hai form
  function syncFormData(sourceForm, targetForm) {
    const formData = new FormData(sourceForm);
    for (let [name, value] of formData.entries()) {
      const targetInput = targetForm.querySelector(`[name="${name}"]`);
      if (targetInput) targetInput.value = value;
    }
  }

  // Đồng bộ khi thay đổi form desktop
  if (desktopForm) {
    desktopForm.addEventListener("change", function () {
      if (mobileForm) syncFormData(desktopForm, mobileForm);
    });
  }

  // Đồng bộ khi thay đổi form mobile
  if (mobileForm) {
    mobileForm.addEventListener("change", function () {
      if (desktopForm) syncFormData(mobileForm, desktopForm);
    });
  }

  // Khởi tạo lần đầu hiển thị dữ liệu không lọc
  filterOrders();
}

// Khởi tạo khi trang đã load
document.addEventListener("DOMContentLoaded", function () {
  initFilters();
  loadCities();
});

window.resetFilter = function (formType) {
  const form = document.getElementById(`filter-form-${formType}`);
  if (!form) return;

  const dateFrom = form.querySelector('[name="date_from"]');
  const dateTo = form.querySelector('[name="date_to"]');
  const orderStatus = form.querySelector('[name="order_status"]');
  const citySelect = form.querySelector('[name="city"]');
  const districtSelect = form.querySelector('[name="district"]');

  if (dateFrom) dateFrom.value = "";
  if (dateTo) dateTo.value = "";
  if (orderStatus) orderStatus.value = "all";
  if (citySelect) citySelect.value = "";
  if (districtSelect) {
    districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
    districtSelect.value = "";
  }

  currentPage = 1; // Đặt lại về trang 1 khi reset bộ lọc
  showNotification("Đã đặt lại bộ lọc", "info");
  filterOrders();
};

document.addEventListener("DOMContentLoaded", function () {
  const filterForm = document.getElementById("filter-form");
  const filterModal = new bootstrap.Modal(
    document.getElementById("filterModal")
  );
  const resetFilterButton = document.getElementById("reset-filter");

  // Xử lý sự kiện submit form bộ lọc
  if (filterForm) {
    filterForm.addEventListener("submit", function (e) {
      e.preventDefault(); // Ngăn chặn reload trang
      currentPage = 1; // Đặt lại về trang 1 khi submit form
      filterOrders(new FormData(filterForm)); // Gọi hàm filterOrders với dữ liệu từ form
      filterModal.hide(); // Đóng modal sau khi áp dụng bộ lọc
    });
  }

  // Xử lý sự kiện đặt lại bộ lọc
  if (resetFilterButton) {
    resetFilterButton.addEventListener("click", function () {
      // Đặt lại các giá trị trong form
      filterForm.reset();

      currentPage = 1; // Đặt lại về trang 1 khi reset bộ lọc
      // filterOrders();
    });
  }
});

// Hàm load districts riêng cho bộ lọc
window.loadDistrictsForFilter = function (provinceId) {
  console.log('[LOAD_DISTRICTS_FILTER] For province:', provinceId);
  
  const districtSelect = document.getElementById('district-select');
  
  if (!districtSelect) {
    console.error('[LOAD_DISTRICTS_FILTER] district-select not found');
    return;
  }
  
  districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
  
  if (!provinceId) {
    console.log('[LOAD_DISTRICTS_FILTER] No province ID');
    return;
  }
  
  fetch(`../php/get_District.php?province_id=${encodeURIComponent(provinceId)}`)
    .then(res => {
      console.log('[LOAD_DISTRICTS_FILTER] Response status:', res.status);
      if (!res.ok) {
        throw new Error(`HTTP error! Status: ${res.status}`);
      }
      return res.json();
    })
    .then(data => {
      console.log('[LOAD_DISTRICTS_FILTER] Data:', data);
      
      if (data.success && data.data && Array.isArray(data.data)) {
        console.log('[LOAD_DISTRICTS_FILTER] Got', data.data.length, 'items');
        data.data.forEach(district => {
          const option = document.createElement('option');
          option.value = district.id;
          option.textContent = district.name;
          districtSelect.appendChild(option);
        });
        console.log('[LOAD_DISTRICTS_FILTER] Loaded successfully');
      } else {
        console.warn('[LOAD_DISTRICTS_FILTER] No data:', data);
      }
    })
    .catch(err => {
      console.error('[LOAD_DISTRICTS_FILTER] Error:', err);
    });
};

window.loadCities = function () {
  console.log("[LOAD_CITIES] Starting to load cities...");
  
  fetch("../php/get_Cities.php")
    .then((response) => {
      console.log("[LOAD_CITIES] Response status:", response.status);
      if (!response.ok) {
        throw new Error(`Failed to fetch cities: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      console.log("[LOAD_CITIES] Data received:", data);
      
      if (!data.success) {
        throw new Error(data.error || "Unknown error");
      }
      
      const citySelect = document.getElementById("city-select");
      if (!citySelect) {
        console.error("[LOAD_CITIES] Element city-select not found");
        return;
      }
      
      console.log("[LOAD_CITIES] Found city-select, populating with", data.data.length, "cities");
      
      citySelect.innerHTML = '<option value="">Chọn thành phố</option>';
      data.data.forEach((city) => {
        const option = document.createElement("option");
        option.value = city.id;
        option.textContent = city.name;
        citySelect.appendChild(option);
      });
      
      console.log("[LOAD_CITIES] Cities loaded successfully");
    })
    .catch((error) => {
      console.error("[LOAD_CITIES] Error:", error);
      const citySelect = document.getElementById("city-select");
      if (citySelect) {
        citySelect.innerHTML = '<option value="">Lỗi tải thành phố</option>';
      }
    });
};

function closeUpdateStatusPopup() {
  document.getElementById("updateStatusOverlay").style.display = "none";
}

document.addEventListener("DOMContentLoaded", function () {
  function setupResponsiveFilters() {
    const filterSection = document.querySelector(".filter-section");
    const filterGrid = document.querySelector(".filter-grid");

    if (filterSection && filterGrid && window.innerWidth <= 992) {
      if (!document.querySelector(".filter-toggle-btn")) {
        const toggleBtn = document.createElement("button");
        toggleBtn.className = "filter-toggle-btn";
        toggleBtn.innerHTML = 'Bộ lọc <i class="fa-solid fa-chevron-down"></i>';

        toggleBtn.addEventListener("click", function () {
          this.classList.toggle("active");
          filterGrid.classList.toggle("show");
        });

        filterSection.insertBefore(toggleBtn, filterGrid);
      }
    } else if (filterSection && window.innerWidth > 992) {
      const toggleBtn = document.querySelector(".filter-toggle-btn");
      if (toggleBtn) {
        toggleBtn.remove();
        filterGrid.classList.remove("show");
      }
    }
  }
  setupResponsiveFilters();
  window.addEventListener("resize", setupResponsiveFilters);
  document.addEventListener("click", function (event) {
    const filterGrid = document.querySelector(".filter-grid.show");
    const toggleBtn = document.querySelector(".filter-toggle-btn");

    if (
      filterGrid &&
      toggleBtn &&
      !filterGrid.contains(event.target) &&
      !toggleBtn.contains(event.target)
    ) {
      filterGrid.classList.remove("show");
      toggleBtn.classList.remove("active");
    }
  });

  const filterForm = document.getElementById("filter-form");
  if (filterForm) {
    filterForm.addEventListener("submit", function (e) {
      e.preventDefault();
      currentPage = 1; // Đặt lại về trang 1 khi submit form
      filterOrders();
      if (window.innerWidth <= 992) {
        const filterGrid = document.querySelector(".filter-grid");
        const toggleBtn = document.querySelector(".filter-toggle-btn");
        if (filterGrid && toggleBtn) {
          filterGrid.classList.add("show");
          toggleBtn.classList.add("active");
        }
      }
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const filterCollapse = document.getElementById("filterCollapse");
  const filterToggleBtn = document.querySelector(".filter-toggle-btn");

  if (filterCollapse && filterToggleBtn) {
    filterCollapse.addEventListener("show.bs.collapse", function () {
      filterToggleBtn.querySelector("i").style.transform =
        "translateY(-50%) rotate(180deg)";
    });

    filterCollapse.addEventListener("hide.bs.collapse", function () {
      filterToggleBtn.querySelector("i").style.transform =
        "translateY(-50%) rotate(0)";
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  initFilters();
});


function showOrderDetailModal(orderId) {
  console.log('[SHOW_DETAIL] Loading order:', orderId);
  
  // Fetch order details from API
  fetch(`../php/get_order_detail.php?orderId=${encodeURIComponent(orderId)}`)
    .then(response => response.json())
    .then(data => {
      console.log('[ORDER_DETAIL] Data:', data);
      
      if (!data.success) {
        throw new Error(data.error || 'Không thể tải chi tiết đơn hàng');
      }
      
      const order = data.order;
      console.log('[ORDER_DETAIL] Voucher:', order.voucher);
      
      // Build products table HTML
      let productsHTML = '';
      order.products.forEach((product, index) => {
        productsHTML += `
          <tr>
            <td style="text-align: center;">${index + 1}</td>
            <td>${product.productName}</td>
            <td style="text-align: center;">${product.quantity}</td>
            <td style="text-align: right;">${parseInt(product.unitPrice).toLocaleString('vi-VN')} VNĐ</td>
            <td style="text-align: right;">${parseInt(product.totalPrice).toLocaleString('vi-VN')} VNĐ</td>
          </tr>
        `;
      });
      
      // Update modal content
      const modalBody = document.querySelector('#orderDetailModal .modal-body');
      if (modalBody) {
        
        modalBody.innerHTML = `
          <div style="padding: 20px;">
            <!-- Order Info Section -->
            <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
              <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">📋 Thông tin đơn hàng</h5>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                  <label style="color: #666; font-size: 12px; text-transform: uppercase;">Mã đơn hàng</label>
                  <p style="margin: 5px 0; font-weight: 600; color: #333;">#${order.orderId}</p>
                </div>
                <div>
                  <label style="color: #666; font-size: 12px; text-transform: uppercase;">Ngày tạo</label>
                  <p style="margin: 5px 0; font-weight: 600; color: #333;">${new Date(order.orderDate).toLocaleString('vi-VN')}</p>
                </div>
                <div>
                  <label style="color: #666; font-size: 12px; text-transform: uppercase;">Phương thức thanh toán: </label>
                  <p style="margin: 5px 0; font-weight: 600; color: #333;">${formatPaymentMethod(order.paymentMethod)}</p>
                </div>
              </div>
            </div>
            
            <!-- Customer Info Section -->
            <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
              <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">👤 Thông tin khách hàng</h5>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                  <label style="color: #666; font-size: 12px; text-transform: uppercase;">Họ tên: </label>
                  <p style="margin: 5px 0; font-weight: 600; color: #333;">${order.customerName}</p>
                </div>
                <div>
                  <label style="color: #666; font-size: 12px; text-transform: uppercase;">Số điện thoại: </label>
                  <p style="margin: 5px 0; font-weight: 600; color: #333;">${order.customerPhone}</p>
                </div>
              </div>
            </div>
            
            <!-- Address Section -->
            <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
              <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">📍 Địa chỉ giao hàng: </h5>
              <p style="margin: 0; color: #333; line-height: 1.6;">${order.address}</p>
            </div>
            
            <!-- Products Section -->
            <div style="margin-bottom: 30px;">
              <h5 style="margin-bottom: 15px; color: #333; font-weight: 600;">📦 Sản phẩm (${order.productCount})</h5>
              <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #f8f9fa; border-bottom: 2px solid #ddd;">
                  <tr>
                    <th style="padding: 12px; text-align: center; color: #666; font-weight: 600;">STT</th>
                    <th style="padding: 12px; text-align: left; color: #666; font-weight: 600;">Sản phẩm</th>
                    <th style="padding: 12px; text-align: center; color: #666; font-weight: 600;">Số lượng</th>
                    <th style="padding: 12px; text-align: right; color: #666; font-weight: 600;">Đơn giá</th>
                    <th style="padding: 12px; text-align: right; color: #666; font-weight: 600;">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  ${productsHTML}
                </tbody>
              </table>
            </div>
            
            <!-- Voucher Section (if exists) -->
            ${order.voucher ? `
              <div style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 10px; border-left: 5px solid #667eea; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                  <span style="font-size: 24px;">🎁</span>
                  <h5 style="margin: 0; color: #2c3e50; font-weight: 700; font-size: 16px;">Mã giảm giá đã áp dụng</h5>
                  <span style="display: inline-block; padding: 4px 10px; background-color: #667eea; color: white; border-radius: 20px; font-size: 11px; font-weight: 600;">Đã dùng</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                  <div style="padding: 10px; background-color: rgba(255,255,255,0.8); border-radius: 6px;">
                    <label style="color: #7f8c8d; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Mã voucher</label>
                    <p style="margin: 8px 0 0 0; font-weight: 700; color: #2c3e50; font-size: 15px;">${order.voucher.name}</p>
                  </div>
                  <div style="padding: 10px; background-color: rgba(255,255,255,0.8); border-radius: 6px;">
                    <label style="color: #7f8c8d; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Tỷ lệ giảm</label>
                    <p style="margin: 8px 0 0 0; font-weight: 700; color: #e74c3c; font-size: 15px;">${order.voucher.discountPercent}%</p>
                  </div>
                  <div style="padding: 10px; background-color: rgba(255,255,255,0.8); border-radius: 6px;">
                    <label style="color: #7f8c8d; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Số tiền giảm</label>
                    <p style="margin: 8px 0 0 0; font-weight: 700; color: #27ae60; font-size: 15px;">-${parseInt(order.voucher.discountAmount).toLocaleString('vi-VN')} VNĐ</p>
                  </div>
                </div>
                ${order.voucher.conditions ? `
                  <div style="margin-top: 12px; padding: 10px; background-color: rgba(100,150,200,0.1); border-radius: 6px; border-left: 3px solid #3498db;">
                    <label style="color: #2c3e50; font-size: 11px; text-transform: uppercase; font-weight: 600;">Điều kiện áp dụng</label>
                    <p style="margin: 6px 0 0 0; color: #555; font-size: 13px;">${order.voucher.conditions}</p>
                  </div>
                ` : ''}
              </div>
            ` : ''}
            
            <!-- Total Section -->
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 16px; font-weight: 600; color: #333;">Thành tiền: </span>
                <span style="font-size: 24px; font-weight: 700; color: #667eea;">${parseInt(order.totalAmount).toLocaleString('vi-VN')} VNĐ</span>
              </div>
            </div>
          </div>
        `;
      }
      
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
      modal.show();
      
      console.log('[ORDER_DETAIL] Modal displayed successfully');
    })
    .catch(error => {
      console.error('[ERROR_DETAIL]', error);
      alert('Lỗi khi tải chi tiết đơn hàng: ' + error.message);
    });
}