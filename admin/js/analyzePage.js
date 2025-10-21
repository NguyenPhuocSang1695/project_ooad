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
  
    // Khôi phục giá trị filter từ localStorage
    function restoreFilterValues() {
      const savedStartDate = localStorage.getItem('analyze_start_date');
      const savedEndDate = localStorage.getItem('analyze_end_date');
      
      if (savedStartDate) {
        startDate.value = savedStartDate;
      } else {
        startDate.value = new Date().toISOString().slice(0, 8) + '01';
      }
      
      if (savedEndDate) {
        endDate.value = savedEndDate;
      } else {
        endDate.value = new Date().toISOString().slice(0, 10);
      }
    }

    function saveFilterValues() {
      localStorage.setItem('analyze_start_date', startDate.value);
      localStorage.setItem('analyze_end_date', endDate.value);
    }
  
    function formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
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
  
    function showError(message) {
      if (customerTable) {
        customerTable.innerHTML = `<tr><td colspan="6" style="text-align: center;">${message}</td></tr>`;
      }
      if (productTable) {
        productTable.innerHTML = `<tr><td colspan="6" style="text-align: center;">${message}</td></tr>`;
      }
      if (totalRevenue) {
        totalRevenue.textContent = '0 ';
      }
      if (bestSelling) {
        bestSelling.textContent = 'Chưa có dữ liệu';
      }
      if (worstSelling) {
        worstSelling.textContent = 'Chưa có dữ liệu';
      }
      if (bestSellingQuantity) {
        bestSellingQuantity.textContent = '';
      }
      if (worstSellingQuantity) {
        worstSellingQuantity.textContent = '';
      }
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
            </span>
          `;
        }
      }
       
      // mặt hàng bán chạy nhất
      if (bestSelling && data.best_selling) {
        if (typeof data.best_selling === 'string') {
          bestSelling.innerHTML = `${data.best_selling}`;
        } else {
          bestSelling.innerHTML = `
            <span class="product-name">${data.best_selling.name}</span>
          `;
          
          // Hiển thị số lượng đã bán
          if (bestSellingQuantity && data.best_selling.quantity) {
            bestSellingQuantity.innerHTML = `
              <div>Đã bán: ${data.best_selling.quantity} sản phẩm</div>
              <div>Doanh thu: ${formatCurrency(data.best_selling.revenue)}</div>
              <div>Đóng góp: ${formatPercentage(data.best_selling.contribution)} doanh thu</div>
            `;
          }
        }
      } else if (bestSelling) {
        bestSelling.innerHTML = 'Chưa có dữ liệu';
        if (bestSellingQuantity) {
          bestSellingQuantity.innerHTML = '';
        }
      }
      
      // Cập nhật mặt hàng bán ế nhất
      if (worstSelling && data.worst_selling) {
        if (typeof data.worst_selling === 'string') {
          worstSelling.innerHTML = `${data.worst_selling}`;
        } else {
          worstSelling.innerHTML = `
            <span class="product-name">${data.worst_selling.name}</span>
          `;
          
          // Hiển thị số lượng đã bán
          if (worstSellingQuantity && data.worst_selling.quantity) {
            worstSellingQuantity.innerHTML = `
              <div>Đã bán: ${data.worst_selling.quantity} sản phẩm</div>
              <div>Doanh thu: ${formatCurrency(data.worst_selling.revenue)}</div>
              <div>Đóng góp: ${formatPercentage(data.worst_selling.contribution)} doanh thu</div>
            `;
          }
        }
      } else if (worstSelling) {
        worstSelling.innerHTML = 'Chưa có dữ liệu';
        if (worstSellingQuantity) {
          worstSellingQuantity.innerHTML = '';
        }
      }
    }

    // Close modal when clicking close button or outside
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Hàm hiển thị modal với thông tin đơn hàng
    function showOrderDetail(orderId) {
        console.log("Opening order detail for ID:", orderId);
        fetch(`../php/get_order_detail.php?orderId=${orderId}`)
            .then(response => {
                console.log("Response status:", response.status);
                return response.json();
            })
            .then(data => {
                console.log("Received data:", data);
                if (data.success) {
                    const order = data.order;
                    const modal = document.getElementById('orderDetailModal');
                    
                    // Kiểm tra và cập nhật từng phần tử
                    const elements = {
                        modalOrderId: document.getElementById('modalOrderId'),
                        modalOrderDate: document.getElementById('modalOrderDate'),
                        modalPaymentMethod: document.getElementById('modalPaymentMethod'),
                        modalReceiverName: document.getElementById('modalReceiverName'),
                        modalReceiverPhone: document.getElementById('modalReceiverPhone'),
                        modalReceiverAddress: document.getElementById('modalReceiverAddress'),
                        modalTotalAmount: document.getElementById('modalTotalAmount'),
                        modalOrderStatus: document.getElementById('modalOrderStatus'),
                        modalProductList: document.getElementById('modalProductList')
                    };

                    console.log("Found elements:", Object.keys(elements).filter(key => elements[key] !== null));
                    console.log("Missing elements:", Object.keys(elements).filter(key => elements[key] === null));

                    // Cập nhật thông tin cơ bản nếu phần tử tồn tại
                    if (elements.modalOrderId) elements.modalOrderId.textContent = order.orderId;
                    if (elements.modalOrderDate) elements.modalOrderDate.textContent = formatDate(order.orderDate);
                    if (elements.modalPaymentMethod) elements.modalPaymentMethod.textContent = order.paymentMethod;
                    if (elements.modalReceiverName) elements.modalReceiverName.textContent = order.receiverName;
                    if (elements.modalReceiverPhone) elements.modalReceiverPhone.textContent = order.receiverPhone;
                    if (elements.modalReceiverAddress) elements.modalReceiverAddress.textContent = order.receiverAddress;
                    if (elements.modalTotalAmount) elements.modalTotalAmount.textContent = formatCurrency(order.totalAmount);
                    
                    // Cập nhật trạng thái đơn hàng
                    if (elements.modalOrderStatus) {
                        elements.modalOrderStatus.textContent = getStatusText(order.status);
                        elements.modalOrderStatus.className = 'status-badge status-' + order.status.toLowerCase();
                    }
                    
                    // Hiển thị danh sách sản phẩm
                    if (elements.modalProductList && order.products) {
                        elements.modalProductList.innerHTML = order.products.map(product => `
                            <div class="product-item">
                                <img src="${product.imageUrl}" alt="${product.productName}" class="product-image">
                                <div class="product-details">
                                    <div class="product-name">${product.productName}</div>
                                    <div class="product-price">
                                        ${product.quantity} x ${formatCurrency(product.unitPrice)} = ${formatCurrency(product.totalPrice)}
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                    
                    // Hiển thị modal
                    if (modal) {
                        modal.style.display = "block";
                    } else {
                        console.error("Modal element not found!");
                    }
                } else {
                    console.error("Error from server:", data.error);
                    alert('Không thể tải thông tin đơn hàng: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải thông tin đơn hàng');
            });
    }
    function getStatusText(status) {
        const statusMap = {
            'execute': 'Đang xử lý',
            'ship': 'Đang giao hàng',
            'success': 'Hoàn thành',
            'fail': 'Đã hủy'
        };
        return statusMap[status] || status;
    }

    //  hiển thị đơn hàng trong bảng customer và product
    function updateCustomerTable(customers) {
        if (customerTable) {
            customerTable.innerHTML = customers.length ? 
                customers.map((customer, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${customer.customer_name}</td> 
                        <td>${customer.order_count}</td>
                        <td>${formatDate(customer.latest_order_date)}</td>
                        <td class="total-amount">${formatCurrency(customer.total_amount)}</td>
                        <td class="order-detail-link">
                            <button class="btn btn-info order-view-button" 
                                    onclick="showOrderList('${customer.customer_name}', ${JSON.stringify(customer.order_links).replace(/"/g, '&quot;')})">
                                <i class="fa-solid fa-circle-info"></i>
                                Xem đơn hàng
                            </button>
                        </td>
                    </tr> 
                `).join('') :
                '<tr><td colspan="6" style="text-align: center;">Không có dữ liệu trong khoảng thời gian này</td></tr>';
        }
    }

    function updateProductTable(products) {
        if (productTable) {
            productTable.innerHTML = products.length ?
                products.map((product, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${product.product_name}</td>
                        <td>${product.quantity_sold}</td>
                        <td class="total-amount">${formatCurrency(product.total_amount)}</td>
                        <td class="order-detail-link">
                            <button class="btn btn-info order-view-button"
                                    onclick="showOrderList('${product.product_name}', ${JSON.stringify(product.order_links).replace(/"/g, '&quot;')})">
                                <i class="fa-solid fa-circle-info"></i>
                                Xem đơn hàng
                            </button>
                        </td>
                    </tr>
                `).join('') :
                '<tr><td colspan="6" style="text-align: center;">Không có dữ liệu trong khoảng thời gian này</td></tr>';
            
            if (products && products.length > 5) {
                productTable.closest('table').classList.add('scrollable-table');
          
                let scrollIndicator = document.getElementById('product-scroll-indicator');
                if (!scrollIndicator) {
                  scrollIndicator = document.createElement('div');
                  scrollIndicator.id = 'product-scroll-indicator';
                  scrollIndicator.className = 'scroll-indicator';
                  scrollIndicator.innerHTML = '<i class="fa-solid fa-angles-down"></i> Cuộn xuống để xem thêm';
                  productTable.closest('table').after(scrollIndicator);
                }
                scrollIndicator.style.display = 'block';
              } else {
      
                productTable.closest('table').classList.remove('scrollable-table');
                
                const scrollIndicator = document.getElementById('product-scroll-indicator');
                if (scrollIndicator) {
                  scrollIndicator.style.display = 'none';
                }
              }
        }
    }
    window.showOrderDetail = showOrderDetail;

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
      .then(response => { 
        if (!response.ok) {
          return response.json().then(err => Promise.reject(err));
        }
        return response.json();
      })
      .then(data => {
        if (!data.success) {
          throw new Error(data.error || 'Có lỗi xảy ra');
        }
 
        updateCustomerTable(data.customers);
  
        updateProductTable(data.products);
  
        updateStatistics(data);
        saveFilterValues(); 
      })
      .catch(error => {
        console.error('Error:', error);
        showError('Có lỗi xảy ra khi tải dữ liệu: ' + (error.error || error.message));
      });
    });
    restoreFilterValues();
    form.dispatchEvent(new Event('submit'));
  });

// Thêm hàm mới để hiển thị danh sách đơn hàng trong modal
window.showOrderList = function(title, orders) {
        const modal = document.getElementById('orderDetailModal');
        const modalContent = modal.querySelector('.order-modal-content');
        
        // Cập nhật nội dung modal
        modalContent.innerHTML = `
            <span class="order-modal-close">&times;</span>
            <div class="order-list-header">
                <h2>Danh sách đơn hàng của ${title}</h2>
            </div>
            <div class="order-list-container">
                ${orders.map(order => `
                    <div class="order-list-item">
                        <div class="order-item-info">
                            <h3>Đơn hàng #${order.id}</h3>
                            <a href="../index/orderDetail2.php?code_Product=${order.id}&source=analyze" class="btn btn-view">
                                <i class="fa-solid fa-eye"></i>
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                `).join('')}
            </div>
            ${orders.length >= 5 ? '<div class="scroll-indicator"><i class="fa-solid fa-angles-down"></i> Cuộn xuống để xem thêm</div>' : ''}
        `;

        // Hiển thị modal
        modal.style.display = "block";

        // Xử lý scroll indicator
        const container = modalContent.querySelector('.order-list-container');
        const scrollIndicator = modalContent.querySelector('.scroll-indicator');
        
        if (scrollIndicator) {
            container.addEventListener('scroll', function() {
                if (container.scrollHeight - container.scrollTop <= container.clientHeight + 50) {
                    scrollIndicator.style.display = 'none';
                } else {
                    scrollIndicator.style.display = 'block';
                }
            });
        }

        // Xử lý nút đóng modal
        const closeBtn = modal.querySelector('.order-modal-close');
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        // Đóng modal khi click ngoài
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    };