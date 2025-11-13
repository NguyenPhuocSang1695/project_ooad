document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('analyze-form');
    const customerTable = document.getElementById('customer-table');
    const productTable = document.getElementById('product-table');
    const totalRevenue = document.getElementById('total-revenue');
    const bestSelling = document.getElementById('best-selling');
    const worstSelling = document.getElementById('worst-selling');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const bestSellingQuantity = document.getElementById('best-selling-quantity');
    const worstSellingQuantity = document.getElementById('worst-selling-quantity');
    const modal = document.getElementById('orderDetailModal');
    const closeBtn = document.querySelector('.order-modal-close');

    // ✅ thêm bảng top 5 sản phẩm
    const topProductsTable = document.getElementById('top-products-table');

    // Khôi phục giá trị filter
    function restoreFilterValues() {
        const savedStartDate = localStorage.getItem('analyze_start_date');
        const savedEndDate = localStorage.getItem('analyze_end_date');

        if (savedStartDate) startDate.value = savedStartDate;
        else startDate.value = new Date().toISOString().slice(0, 8) + '01';
        if (savedEndDate) endDate.value = savedEndDate;
        else endDate.value = new Date().toISOString().slice(0, 10);
    }

    function saveFilterValues() {
        localStorage.setItem('analyze_start_date', startDate.value);
        localStorage.setItem('analyze_end_date', endDate.value);
    }

    function formatCurrency(number) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(number).replace('₫', '');
    }

    function formatPercentage(number) {
        return number.toFixed(2) + '%';
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function showError(message) {
        if (customerTable) {
            customerTable.innerHTML = `<tr><td colspan="6" style="text-align: center;">${message}</td></tr>`;
        }
        if (productTable) {
            productTable.innerHTML = `<tr><td colspan="6" style="text-align: center;">${message}</td></tr>`;
        }
        if (topProductsTable) {
            topProductsTable.innerHTML = `<tr><td colspan="4" style="text-align:center;">${message}</td></tr>`;
        }
        if (totalRevenue) totalRevenue.textContent = '0 ';
        if (bestSelling) bestSelling.textContent = 'Chưa có dữ liệu';
        if (worstSelling) worstSelling.textContent = 'Chưa có dữ liệu';
        if (bestSellingQuantity) bestSellingQuantity.textContent = '';
        if (worstSellingQuantity) worstSellingQuantity.textContent = '';
    }

    function updateStatistics(data) {
        if (totalRevenue) {
            totalRevenue.innerHTML = `<span class="value">${formatCurrency(data.total_revenue || 0)}</span>`;
            if (data.revenue_change) {
                const changeClass = data.revenue_change > 0 ? 'positive-change' : 'negative-change';
                const changeIcon = data.revenue_change > 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                totalRevenue.innerHTML += `
                    <span class="change ${changeClass}">
                      <i class="fa-solid ${changeIcon}"></i>
                      ${Math.abs(data.revenue_change)}% so với kỳ trước
                    </span>`;
            }
        }

        if (bestSelling && data.best_selling) {
            if (typeof data.best_selling === 'string') {
                bestSelling.innerHTML = `${data.best_selling}`;
            } else {
                bestSelling.innerHTML = `<span class="product-name">${data.best_selling.name}</span>`;
                if (bestSellingQuantity && data.best_selling.quantity) {
                    bestSellingQuantity.innerHTML = `
                        <div>Đã bán: ${data.best_selling.quantity} sản phẩm</div>
                        <div>Doanh thu: ${formatCurrency(data.best_selling.revenue)}</div>
                        <div>Đóng góp: ${formatPercentage(data.best_selling.contribution)} doanh thu</div>`;
                }
            }
        }

        if (worstSelling && data.worst_selling) {
            if (typeof data.worst_selling === 'string') {
                worstSelling.innerHTML = `${data.worst_selling}`;
            } else {
                worstSelling.innerHTML = `<span class="product-name">${data.worst_selling.name}</span>`;
                if (worstSellingQuantity && data.worst_selling.quantity) {
                    worstSellingQuantity.innerHTML = `
                        <div>Đã bán: ${data.worst_selling.quantity} sản phẩm</div>
                        <div>Doanh thu: ${formatCurrency(data.worst_selling.revenue)}</div>
                        <div>Đóng góp: ${formatPercentage(data.worst_selling.contribution)} doanh thu</div>`;
                }
            }
        }
    }

    function updateCustomerTable(customers) {
        if (customerTable) {
            customerTable.innerHTML = customers.length ?
                customers.map((c, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${c.customer_name}</td>
                        <td>${c.order_count}</td>
                        <td class="total-amount">${formatCurrency(c.total_amount)}</td>
                        <td class="order-detail-link">
                            <button class="btn btn-info order-view-button"
                                onclick="showOrderList('${c.customer_name}', ${JSON.stringify(c.order_links).replace(/"/g, '&quot;')})">
                                <i class="fa-solid fa-circle-info"></i> Xem đơn hàng
                            </button>
                        </td>
                    </tr>`).join('')
                : '<tr><td colspan="5" style="text-align:center;">Không có dữ liệu</td></tr>';
        }
    }

    function updateProductTable(products) {
        if (productTable) {
            productTable.innerHTML = products.length ?
                products.map((p, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${p.product_name}</td>
                        <td>${p.quantity_sold}</td>
                        <td class="total-amount">${formatCurrency(p.total_amount)}</td>
                        <td class="order-detail-link">
                            <button class="btn btn-info order-view-button"
                                onclick="showOrderList('${p.product_name}', ${JSON.stringify(p.order_links).replace(/"/g, '&quot;')})">
                                <i class="fa-solid fa-circle-info"></i> Xem đơn hàng
                            </button>
                        </td>
                    </tr>`).join('')
                : '<tr><td colspan="6" style="text-align:center;">Không có dữ liệu</td></tr>';
        }
    }

    // 🆕 HÀM MỚI: Hiển thị bảng top 5 sản phẩm bán chạy nhất
    function updateTopProductsTable(topProducts) {
  const tbody = document.getElementById('top-products-body');
  if (!tbody) return;

  tbody.innerHTML = topProducts.length
    ? topProducts
        .map(
          (p, i) => `
      <tr>
        <td>${i + 1}</td>
        <td>${p.product_name}</td>
        <td>${p.quantity_sold}</td>
        <td class="total-amount">${formatCurrency(p.total_amount)}</td>
      </tr>`
        )
        .join('')
    : '<tr><td colspan="4" style="text-align:center;">Không có dữ liệu</td></tr>';
}

// 🆕 Hiển thị bảng Top 5 sản phẩm bán chậm nhất
function updateWorstProductsTable(worstProducts) {
  const tbody = document.getElementById('worst-products-body');
  if (!tbody) return;

  tbody.innerHTML = worstProducts.length
    ? worstProducts
        .map(
          (p, i) => `
      <tr>
        <td>${i + 1}</td>
        <td>${p.product_name}</td>
        <td>${p.quantity_sold}</td>
        <td class="total-amount">${formatCurrency(p.total_amount)}</td>
      </tr>`
        )
        .join('')
    : '<tr><td colspan="4" style="text-align:center;">Không có dữ liệu</td></tr>';
}

window.showOrderList = function(customerOrProductName, orderLinks) {
    const modal = document.getElementById('orderDetailModal');
    const modalBody = document.getElementById('orderDetailBody');

    if (!modal || !modalBody) {
        alert("Không tìm thấy modal để hiển thị danh sách đơn hàng!");
        return;
    }

    // Tạo danh sách đơn hàng
const ordersHTML = orderLinks.length
  ? orderLinks.map(link => `
      <li>Đơn hàng ${link.id}</></li>
    `).join('')
  : '<li>Không có đơn hàng nào</li>';


    // Cập nhật nội dung modal
    modalBody.innerHTML = `
        <h4>Đơn hàng của: ${customerOrProductName}</h4>
        <ul>${ordersHTML}</ul>
    `;

    // Hiện modal (nếu bạn dùng Bootstrap)
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
};

// window.showOrderList = function(customerName, orderList) {
//     const modal = document.getElementById('orderDetailModal');
//     const message = document.getElementById('orderMessage');

//     if (!modal || !message) {
//         alert("Không tìm thấy modal để hiển thị thông tin!");
//         return;
//     }

//     console.log("Danh sách đơn hàng:", orderList);

//     // Lấy danh sách ID đơn hàng
//     let orderIds = "";
//     if (Array.isArray(orderList)) {
//         orderIds = orderList.map(o => o.OrderID).join(", ");
//     } else {
//         orderIds = orderList; // đề phòng chỉ có 1 đơn
//     }

//     // Hiển thị nội dung
//     message.innerHTML = `<h3>${customerName} đã đặt các đơn hàng có ID: ${orderIds}.</h3>`;

//     // Hiện modal
//     modal.style.display = 'block';

//     // Đóng modal khi bấm dấu × hoặc nền đen
//     const closeBtn = modal.querySelector('.order-modal-close');
//     closeBtn.onclick = () => modal.style.display = 'none';
//     window.onclick = (event) => {
//         if (event.target === modal) modal.style.display = 'none';
//     };
// };


    // === SUBMIT FORM ===
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        if (startDate.value > endDate.value) {
            showError('Ngày bắt đầu không thể lớn hơn ngày kết thúc');
            return;
        }

        const formData = new FormData(form);

        fetch('../php/analyze_data.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);

                } catch (e) {
                    console.error('❌ JSON parse error:', e, '\nServer returned:', text);
                    throw new Error('Phản hồi không hợp lệ từ máy chủ.');
                }

                if (!data.success) throw new Error(data.error || 'Có lỗi xảy ra');

                updateCustomerTable(data.customers);
                updateProductTable(data.products);
                updateStatistics(data);
                updateTopProductsTable(data.top_products); // ✅ GỌI THÊM PHẦN MỚI
                updateWorstProductsTable(data.worst_products);
                saveFilterValues();
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Có lỗi xảy ra khi tải dữ liệu: ' + (error.message || 'Không rõ nguyên nhân'));
            });
    });

    restoreFilterValues();
    form.dispatchEvent(new Event('submit'));
});
