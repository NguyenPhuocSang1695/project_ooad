// Fresh Add Order Handler - Clean Start

let allProducts = [];

// Beautiful Notification System
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    let icon = '✓';
    if (type === 'error') icon = '✗';
    if (type === 'info') icon = 'ℹ';
    if (type === 'warning') icon = '⚠';
    
    const iconSpan = document.createElement('span');
    iconSpan.className = `notification-icon ${type === 'success' ? 'animate-tick' : ''}`;
    iconSpan.textContent = icon;
    
    const messageSpan = document.createElement('span');
    messageSpan.className = 'notification-message';
    messageSpan.textContent = message;
    
    notification.appendChild(iconSpan);
    notification.appendChild(messageSpan);
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3500);
}

// Enhanced Success Notification
function showEnhancedSuccessNotification(orderId, totalAmount, productCount) {
    const notification = document.createElement('div');
    notification.className = 'enhanced-notification';
    notification.innerHTML = `
        <div class="notification-header">
            <div class="success-icon-wrapper">
                <svg class="success-checkmark" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <div class="notification-title">Đơn hàng đã được tạo!</div>
        </div>
        <div class="notification-body">
            <div class="order-detail-item">
                <span class="detail-label">Mã đơn hàng:</span>
                <span class="detail-value">#${orderId}</span>
            </div>
            <div class="order-detail-item">
                <span class="detail-label">Số sản phẩm:</span>
                <span class="detail-value">${productCount} sản phẩm</span>
            </div>
            <div class="order-detail-item">
                <span class="detail-label">Tổng tiền:</span>
                <span class="detail-value highlight">${parseInt(totalAmount).toLocaleString('vi-VN')} VNĐ</span>
            </div>
        </div>
        <div class="notification-footer">
            <div class="success-message">✓ Đơn hàng đang được xử lý</div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

// Add notification styles
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        bottom: 30px;
        right: 20px;
        padding: 12px 18px;
        border-radius: 6px;
        color: white;
        font-size: 13px;
        font-weight: 500;
        z-index: 9999;
        opacity: 0;
        transform: translateX(400px) scale(0.9);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        max-width: 350px;
        word-wrap: break-word;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification.show {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
    
    .notification-success {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .notification-error {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .notification-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .notification-warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: #333;
    }
    
    .notification-icon {
        font-size: 16px;
        font-weight: bold;
        min-width: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification-icon.animate-tick {
        animation: checkmark 0.6s ease-out;
    }
    
    @keyframes checkmark {
        0% {
            transform: scale(0) rotate(-45deg);
            opacity: 0;
        }
        50% {
            transform: scale(1.2) rotate(0deg);
        }
        100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }
    
    .notification-message {
        flex: 1;
    }
    
    .enhanced-notification {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.7);
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        width: 400px;
        max-width: 90vw;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        overflow: hidden;
    }
    
    .enhanced-notification.show {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    
    .enhanced-notification::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    .notification-header {
        padding: 30px 30px 20px;
        text-align: center;
    }
    
    .success-icon-wrapper {
        display: inline-block;
        margin-bottom: 15px;
    }
    
    .success-checkmark {
        width: 80px;
        height: 80px;
        display: block;
        stroke-width: 3;
        stroke-miterlimit: 10;
    }
    
    .checkmark-circle {
        display: none;
    }
    
    .checkmark-check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        stroke: #4CAF50;
        stroke-width: 4;
        stroke-linecap: round;
        stroke-linejoin: round;
        animation: strokeCheck 0.6s ease-out forwards;
    }
    
    @keyframes strokeCheck {
        0% {
            stroke-dashoffset: 48;
            opacity: 0;
            transform: scale(0.5);
        }
        50% {
            opacity: 1;
        }
        100% {
            stroke-dashoffset: 0;
            opacity: 1;
            transform: scale(1);
        }
    }
    
    
    @keyframes scale {
        0%, 100% {
            transform: none;
        }
        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }
    
    .notification-title {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .notification-body {
        padding: 0 30px 20px;
        background: #f8f9fa;
    }
    
    .order-detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .order-detail-item:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }
    
    .detail-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .detail-value.highlight {
        color: #667eea;
        font-size: 18px;
    }
    
    .notification-footer {
        padding: 20px 30px;
        text-align: center;
        background: white;
    }
    
    .success-message {
        font-size: 14px;
        color: #4CAF50;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    @media (max-width: 768px) {
        .enhanced-notification {
            width: 350px;
        }
        
        .notification-title {
            font-size: 20px;
        }
        
        .success-checkmark {
            width: 60px;
            height: 60px;
        }
        
        .notification-body {
            padding: 0 20px 15px;
        }
        
        .notification-header {
            padding: 20px 20px 15px;
        }
        
        .notification-footer {
            padding: 15px 20px;
        }
        
        .notification {
            bottom: 15px;
            right: 10px;
            left: 10px;
            max-width: none;
            padding: 10px 15px;
            font-size: 12px;
        }
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', function() {
    console.log('[INIT] Add Order JS loaded');
    
    // Get the form
    const form = document.getElementById('add-order-form');
    if (!form) {
        console.error('[ERROR] Form #add-order-form not found');
        return;
    }

    // Hide voucher note initially
    const voucherNote = document.getElementById('voucher-note');
    if (voucherNote) {
        voucherNote.style.display = 'none';
    }

    // Load provinces, products when page loads
    loadProvinces();
    loadProductsForAllSelects();

    // Form submit
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('[SUBMIT] Form submitted');
        await submitOrder();
    });

    // Province change
    document.getElementById('add-province')?.addEventListener('change', function() {
        console.log('[PROVINCE] Selected:', this.value);
        if (this.value) loadDistricts(this.value);
    });

    // District change
    document.getElementById('add-district')?.addEventListener('change', function() {
        console.log('[DISTRICT] Selected:', this.value);
        if (this.value) loadWards(this.value);
    });

    // Add product row button
    document.getElementById('add-product')?.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('[ADD_PRODUCT_ROW] Adding new row');
        addProductRow();
    });

    // Event delegation for product select and quantity changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select') || 
            e.target.classList.contains('product-quantity')) {
            updateTotalAmount();
            // Re-check voucher eligibility when total changes
            setTimeout(() => checkVoucherEligibility(), 100);
        }
        
        // Voucher selection change
        if (e.target.id === 'voucher-select') {
            console.log('[VOUCHER] Selected:', e.target.value);
            checkVoucherEligibility();
        }
    });

    // Event delegation for remove product button
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-product')) {
            e.preventDefault();
            console.log('[REMOVE_PRODUCT] Removing row');
            const row = e.target.closest('.product-item');
            if (row) {
                row.remove();
                updateTotalAmount();
                // Re-check voucher eligibility when total changes
                setTimeout(() => checkVoucherEligibility(), 100);
            }
        }
    });

    // Customer phone change 
    document.getElementById('customer-phone')?.addEventListener('change', function() {
        console.log('[PHONE] Changed:', this.value);
        if (this.value.length === 10) {
            fetchCustomerHistory(this.value);
        }
    });

    // Delivery type radio buttons
    document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('[DELIVERY_TYPE] Selected:', this.value);
            const addressSection = document.getElementById('address-section');
            
            if (this.value === 'address') {
                // Hiển thị phần địa chỉ khi chọn "Giao tận nơi"
                addressSection.style.display = 'block';
                console.log('[DELIVERY_TYPE] Showing address section');
            } else {
                // Ẩn phần địa chỉ khi chọn "Tận nơi"
                addressSection.style.display = 'none';
                console.log('[DELIVERY_TYPE] Hiding address section');
                
                // Clear address values
                document.getElementById('add-province').value = '';
                document.getElementById('add-district').value = '';
                document.getElementById('add-district').innerHTML = '<option value="">Chọn quận/huyện</option>';
                document.getElementById('add-ward').value = '';
                document.getElementById('add-ward').innerHTML = '<option value="">Chọn phường/xã</option>';
                document.getElementById('address-detail').value = '';
            }
        });
    });

    // Payment method change - Show/Hide QR Code
    document.getElementById('payment-method')?.addEventListener('change', function() {
        const bankingSection = document.getElementById('banking-info-section');
        const totalAmount = document.getElementById('total-amount')?.textContent || '0';
        
        if (this.value === 'BANKING') {
            bankingSection.style.display = 'block';
            // Update QR code with current total
            const cleanAmount = totalAmount.replace(/[^0-9]/g, '');
            const qrImage = document.getElementById('admin-qr-code');
            const bankAccountNumber = '1028974123';
            const bankName = 'Vietcombank';
            const qrText = bankAccountNumber + '|' + bankName + '|' + cleanAmount + '|Mua%20hang';
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent(qrText);
            qrImage.src = qrUrl;
            
            // Update amount display
            document.getElementById('admin-total-amount').textContent = totalAmount;
            console.log('[PAYMENT] Banking selected, QR updated');
        } else {
            bankingSection.style.display = 'none';
            console.log('[PAYMENT] Other method selected, QR hidden');
        }
    });

    // Update QR when total amount changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.id === 'total-amount' && document.getElementById('payment-method').value === 'BANKING') {
                const totalAmount = mutation.target.textContent;
                const cleanAmount = totalAmount.replace(/[^0-9]/g, '');
                const qrImage = document.getElementById('admin-qr-code');
                const bankAccountNumber = '1028974123';
                const bankName = 'Vietcombank';
                const qrText = bankAccountNumber + '|' + bankName + '|' + cleanAmount + '|Mua%20hang';
                const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent(qrText);
                qrImage.src = qrUrl;
                document.getElementById('admin-total-amount').textContent = totalAmount;
            }
        });
    });

    // Observe total amount for changes
    const totalAmountElement = document.getElementById('total-amount');
    if (totalAmountElement) {
        observer.observe(totalAmountElement, { characterData: true, subtree: true, childList: true });
    }
});


// Load provinces/cities
function loadProvinces() {
    console.log('[LOAD_PROVINCES] Fetching...');
    
    fetch('../php/get_Cities.php')
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('[PROVINCES] Got', data.data?.length || 0, 'items');
            
            const select = document.getElementById('add-province');
            if (!select) return;
            
            if (data.success && data.data && Array.isArray(data.data)) {
                data.data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.id;
                    option.textContent = province.name;
                    select.appendChild(option);
                });
            }
        })
        .catch(err => {
            console.error('[ERROR_PROVINCES]', err);
            showNotification('error', 'Lỗi khi tải danh sách tỉnh/thành phố');
        });
}

// Load districts
function loadDistricts(provinceId) {
    console.log('[LOAD_DISTRICTS] For province:', provinceId);
    
    // Kiểm tra element từ form thêm đơn hàng
    const districtSelect = document.getElementById('add-district');
    const wardSelect = document.getElementById('add-ward');
    
    // Kiểm tra element từ bộ lọc
    const filterDistrictSelect = document.getElementById('district-select');
    
    // Xác định element nào sẽ được update
    const targetDistrictSelect = districtSelect || filterDistrictSelect;
    
    if (!targetDistrictSelect) {
        console.error('[LOAD_DISTRICTS] No district select element found');
        return;
    }
    
    targetDistrictSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
    if (wardSelect) wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
    
    fetch(`../php/get_District.php?province_id=${encodeURIComponent(provinceId)}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('[DISTRICTS] Got', data.data?.length || 0, 'items');
            
            if (data.success && data.data && Array.isArray(data.data)) {
                data.data.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    targetDistrictSelect.appendChild(option);
                });
                console.log('[DISTRICTS] Loaded successfully for element:', targetDistrictSelect.id);
            } else {
                console.warn('[DISTRICTS] No data or not success:', data);
            }
        })
        .catch(err => {
            console.error('[ERROR_DISTRICTS]', err);
            // Chỉ show notification nếu được định nghĩa
            if (typeof showNotification !== 'undefined') {
                showNotification('error', 'Lỗi khi tải danh sách quận/huyện');
            }
        });
}

// Load wards
function loadWards(districtId) {
    console.log('[LOAD_WARDS] For district:', districtId);
    
    const wardSelect = document.getElementById('add-ward');
    if (!wardSelect) return;
    
    wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
    
    fetch(`../php/get-wards.php?district_id=${encodeURIComponent(districtId)}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('[WARDS] Got', data.data?.length || 0, 'items');
            
            if (data.success && data.data && Array.isArray(data.data)) {
                data.data.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward.id;
                    option.textContent = ward.name;
                    wardSelect.appendChild(option);
                });
            }
        })
        .catch(err => {
            console.error('[ERROR_WARDS]', err);
            showNotification('error', 'Lỗi khi tải danh sách phường/xã');
        });
}

// Load all products
function loadProductsForAllSelects() {
    console.log('[LOAD_PRODUCTS] Fetching all products...');
    
    fetch('../php/get-all-products.php')
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('[PRODUCTS] Got', data.products?.length || 0, 'products');
            
            if (data.success && data.products && Array.isArray(data.products)) {
                allProducts = data.products;
                refreshProductSelects();
            } else {
                console.error('[ERROR_PRODUCTS] Invalid data format:', data);
                showNotification('error', 'Lỗi khi tải danh sách sản phẩm');
            }
        })
        .catch(err => {
            console.error('[ERROR_PRODUCTS]', err);
            showNotification('error', 'Lỗi: ' + err.message);
        });
}

// Refresh all product selects with available products
function refreshProductSelects() {
    console.log('[REFRESH_SELECTS] Updating all product selects');
    
    document.querySelectorAll('.product-item').forEach(item => {
        const select = item.querySelector('.product-select');
        const searchInput = item.querySelector('.product-search');
        const optionsDiv = item.querySelector('.product-options');
        
        if (!select || !searchInput || !optionsDiv) return;
        
        const currentValue = select.value;
        
        // Update select options (for fallback)
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        // Build product options list
        optionsDiv.innerHTML = '';
        allProducts.forEach(product => {
            if (product.status === 'appear') {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} (${parseInt(product.price).toLocaleString()} VND)`;
                option.dataset.price = product.price;
                select.appendChild(option);
                
                // Also create searchable option div
                const optionDiv = document.createElement('div');
                optionDiv.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; hover-effect: true;';
                optionDiv.textContent = `${product.name} (${parseInt(product.price).toLocaleString()} VND)`;
                optionDiv.dataset.productId = product.id;
                optionDiv.dataset.productName = product.name.toLowerCase();
                optionDiv.dataset.price = product.price;
                
                optionDiv.addEventListener('mouseover', function() {
                    this.style.backgroundColor = '#f0f0f0';
                });
                optionDiv.addEventListener('mouseout', function() {
                    this.style.backgroundColor = 'transparent';
                });
                
                optionDiv.addEventListener('click', function() {
                    select.value = this.dataset.productId;
                    searchInput.value = this.textContent;
                    optionsDiv.style.display = 'none';
                    
                    // Trigger change event to update price
                    select.dispatchEvent(new Event('change'));
                });
                
                optionsDiv.appendChild(optionDiv);
            }
        });
        
        // Setup search functionality
        searchInput.removeEventListener('input', handleProductSearch);
        searchInput.addEventListener('input', handleProductSearch);
        
        // Restore previous selection
        if (currentValue) select.value = currentValue;
    });
}

// Handle product search
function handleProductSearch(e) {
    const searchValue = e.target.value.toLowerCase();
    const item = e.target.closest('.product-item');
    const optionsDiv = item.querySelector('.product-options');
    
    if (!optionsDiv) return;
    
    const options = optionsDiv.querySelectorAll('[data-product-id]');
    let hasVisible = false;
    
    options.forEach(option => {
        const productName = option.dataset.productName;
        if (searchValue === '' || productName.includes(searchValue)) {
            option.style.display = 'block';
            hasVisible = true;
        } else {
            option.style.display = 'none';
        }
    });
    
    optionsDiv.style.display = (searchValue === '' && hasVisible) ? 'block' : (hasVisible ? 'block' : 'none');
}

// Add a new product row
function addProductRow() {
    const productList = document.getElementById('product-list');
    if (!productList) {
        console.error('[ERROR] product-list not found');
        return;
    }

    const row = document.createElement('div');
    row.className = 'product-item row mb-2';
    row.innerHTML = `
        <div class="col-md-5">
            <div style="position: relative;">
                <input type="text" class="form-control product-search" placeholder="🔍 Tìm kiếm sản phẩm..." style="margin-bottom: 5px;">
                <select class="form-control product-select" name="products[]" required style="display: none;">
                    <option value="">Chọn sản phẩm</option>
                </select>
                <div class="product-options" style="border: 1px solid #ced4da; border-radius: 4px; max-height: 200px; overflow-y: auto; display: none; position: absolute; width: 100%; background: white; z-index: 1000; top: 38px;">
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control product-quantity" name="quantities[]" 
                   value="1" min="1" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control product-price" readonly>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm remove-product" style="width:100%;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    productList.appendChild(row);
    
    // Refresh product select for this row (including search functionality)
    refreshProductSelects();
    
    console.log('[ADD_ROW] New product row added');
}

// Update total amount
function updateTotalAmount() {
    let total = 0;
    const rows = document.querySelectorAll('.product-item');
    
    rows.forEach((row, idx) => {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.product-quantity');
        const priceInput = row.querySelector('.product-price');
        
        if (select && select.value) {
            const option = select.options[select.selectedIndex];
            const price = parseFloat(option.dataset.price || 0);
            const qty = parseInt(qtyInput.value) || 1;
            const rowTotal = price * qty;
            
            if (priceInput) {
                priceInput.value = parseInt(price).toLocaleString('vi-VN');
            }
            
            total += rowTotal;
            
            console.log(`[CALC] Row ${idx}: ${qty} x ${price} = ${rowTotal}`);
        }
    });
    
    const totalElement = document.getElementById('total-amount');
    if (totalElement) {
        totalElement.textContent = parseInt(total).toLocaleString('vi-VN');
    }
    
    // Update original-total (Tổng tiền gốc) when products change
    const originalTotalElement = document.getElementById('original-total');
    if (originalTotalElement) {
        originalTotalElement.value = parseInt(total).toLocaleString('vi-VN') + ' VNĐ';
    }

    // Update QR code if Banking payment is selected
    const paymentMethod = document.getElementById('payment-method')?.value;
    if (paymentMethod === 'BANKING') {
        const cleanAmount = parseInt(total).toString();
        const qrImage = document.getElementById('admin-qr-code');
        const bankAccountNumber = '1028974123';
        const bankName = 'Vietcombank';
        const qrText = bankAccountNumber + '|' + bankName + '|' + cleanAmount + '|Mua%20hang';
        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent(qrText);
        if (qrImage) {
            qrImage.src = qrUrl;
        }
        
        // Update displayed amount in banking section
        const adminTotalAmount = document.getElementById('admin-total-amount');
        if (adminTotalAmount) {
            adminTotalAmount.textContent = parseInt(total).toLocaleString('vi-VN');
        }
    }
    
    console.log('[TOTAL] Updated to:', total);
}

// Submit order
async function submitOrder() {
    console.log('[START] Submitting order...');
    
    try {
        // Collect form data
        const customerName = document.getElementById('customer-name')?.value?.trim() || '';
        const customerPhone = document.getElementById('customer-phone')?.value?.trim() || '';
        const paymentMethod = document.getElementById('payment-method')?.value || '';
        const status = document.getElementById('add-order-status')?.value || 'execute';
        
        console.log('[FORM] Name:', customerName);
        console.log('[FORM] Phone:', customerPhone);
        console.log('[FORM] Payment:', paymentMethod);
        console.log('[FORM] Status:', status);
        
        // Validate basic fields
        if (!customerName) {
            showNotification('warning', 'Vui lòng nhập tên khách hàng');
            return;
        }
        if (!customerPhone) {
            showNotification('warning', 'Vui lòng nhập số điện thoại');
            return;
        }
        // Validate phone number (Vietnamese format: 10 digits starting with 0)
        const phoneRegex = /^0[0-9]{9}$/;
        if (!phoneRegex.test(customerPhone) || customerPhone.length !== 10) {
            showNotification('warning', 'Số điện thoại không hợp lệ (phải là 10 chữ số, bắt đầu từ 0)');
            return;
        }
        if (!paymentMethod) {
            showNotification('warning', 'Vui lòng chọn phương thức thanh toán');
            return;
        }
        
        // Collect products
        const products = [];
        const rows = document.querySelectorAll('.product-item');
        
        console.log('[PRODUCTS] Found rows:', rows.length);
        
        rows.forEach((row, idx) => {
            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.product-quantity');
            
            if (select && select.value) {
                const productId = parseInt(select.value);
                const qty = parseInt(qtyInput.value) || 1;
                const price = parseFloat(select.options[select.selectedIndex]?.dataset?.price || 0);
                
                console.log(`[PRODUCT ${idx}] ID: ${productId}, Qty: ${qty}, Price: ${price}`);
                
                products.push({
                    product_id: productId,
                    quantity: qty,
                    price: price
                });
            }
        });
        
        if (products.length === 0) {
            showNotification('warning', 'Vui lòng thêm ít nhất một sản phẩm');
            return;
        }
        
        console.log('[PRODUCTS] Total:', products.length);
        
        // Prepare payload
        const voucherSelectElement = document.getElementById('voucher-select');
        const voucherId = voucherSelectElement?.value ? parseInt(voucherSelectElement.value) : null;
        
        const payload = {
            customer_name: customerName,
            customer_phone: customerPhone,
            payment_method: paymentMethod,
            status: status,
            voucher_id: voucherId,
            products: products,
            address: {
                ward_id: document.getElementById('add-ward')?.value || '',
                address_detail: document.getElementById('address-detail')?.value?.trim() || ''
            }
        };
        
        console.log('[PAYLOAD] Ready to send');
        console.log('[PAYLOAD] Voucher ID:', voucherId);
        console.log('[PAYLOAD] Data:', JSON.stringify(payload, null, 2));
        
        // Send to server
        console.log('[FETCH] Sending to /admin/php/add-order.php');
        
        const response = await fetch('../php/add-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        console.log('[RESPONSE] Status:', response.status);
        
        const text = await response.text();
        console.log('[RESPONSE] Raw text:', text);
        
        let result;
        try {
            result = JSON.parse(text);
            console.log('[RESULT] Parsed JSON:', result);
        } catch (e) {
            console.error('[PARSE_ERROR]', e.message);
            throw new Error('Invalid server response: ' + text.substring(0, 100));
        }
        
        // Check response
        if (!result.success) {
            console.error('[FAILED] Server returned success=false');
            console.error('[ERROR_MESSAGE]', result.message);
            throw new Error(result.message || 'Unknown error');
        }
        
        // Success!
        console.log('[SUCCESS] Order created! ID:', result.order_id);
        
        const totalAmount = products.reduce((sum, p) => sum + (p.price * p.quantity), 0);
        showEnhancedSuccessNotification(result.order_id, totalAmount, products.length);
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('addOrderModal'));
        if (modal) modal.hide();
        
        // Reset form
        document.getElementById('add-order-form').reset();
        
        const productList = document.getElementById('product-list');
        if (productList) {
            productList.innerHTML = `
                <div class="product-item row mb-2">
                    <div class="col-md-5">
                        <select class="form-control product-select" name="products[]" required>
                            <option value="">Chọn sản phẩm</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control product-quantity" name="quantities[]" 
                               value="1" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control product-price" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product" style="width:100%;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            refreshProductSelects();
        }
        
        // Reset total amount
        const totalElement = document.getElementById('total-amount');
        if (totalElement) totalElement.textContent = '0';
        
        const originalTotalElement = document.getElementById('original-total');
        if (originalTotalElement) originalTotalElement.value = '0 VNĐ';
        
        const discountElement = document.getElementById('discount-amount');
        if (discountElement) discountElement.value = '0 VNĐ';
        
        console.log('[RELOAD] Reloading page in 2 seconds...');
        setTimeout(() => {
            window.location.reload();
        }, 2000);
        
    } catch (error) {
        console.error('[EXCEPTION]', error);
        console.error('[EXCEPTION_MESSAGE]', error.message);
        showNotification('error', 'Lỗi: ' + error.message);
    }
}

// ========== VOUCHER FUNCTIONS (History-Based) ==========

// Fetch customer history and load eligible vouchers
async function fetchCustomerHistory(phone) {
    console.log('[FETCH_HISTORY] For phone:', phone);
    console.log('[FETCH_HISTORY] Phone length:', phone.length);
    
    const historyDiv = document.getElementById('customer-history');
    const messageElement = document.getElementById('history-message');
    const detailsElement = document.getElementById('history-details');
    const voucherSelect = document.getElementById('voucher-select');
    
    console.log('[FETCH_HISTORY] Elements found:', {
        historyDiv: !!historyDiv,
        messageElement: !!messageElement,
        detailsElement: !!detailsElement,
        voucherSelect: !!voucherSelect
    });
    
    if (!historyDiv || !voucherSelect) {
        console.error('[ERROR] Elements not found');
        return;
    }
    
    try {
        // Get customer history
        const historyResponse = await fetch('../php/get_customer_history.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                customer_phone: phone
            })
        });
        
        const historyResult = await historyResponse.json();
        console.log('[HISTORY_RESULT]', historyResult);
        
        // Get eligible vouchers
        const voucherResponse = await fetch('../php/get_eligible_vouchers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                customer_phone: phone
            })
        });
        
        const voucherResult = await voucherResponse.json();
        console.log('[ELIGIBLE_VOUCHERS]', voucherResult);
        
        // Update history display
        if (historyResult.success && historyResult.has_purchased) {
            historyDiv.style.display = 'block';
            const customerNameDisplay = historyResult.customer_name ? `: ${historyResult.customer_name}` : '';
            if (messageElement) {
                messageElement.textContent = `✓ Khách hàng thân thiết${customerNameDisplay} - Tổng tiền lịch sử: ${parseInt(historyResult.total_spent).toLocaleString('vi-VN')} VNĐ`;
                messageElement.style.color = '#28a745';
            }
            if (detailsElement) {
                detailsElement.innerHTML = `${historyResult.order_count} đơn hàng thành công | Giá trị trung bình: ${Math.round(historyResult.total_spent / historyResult.order_count).toLocaleString('vi-VN')} VNĐ/đơn`;
            }
        } else {
            historyDiv.style.display = 'block';
            const customerNameDisplay = historyResult.customer_name ? `: ${historyResult.customer_name}` : '';
            if (messageElement) {
                messageElement.textContent = `⚠ Khách hàng mới${customerNameDisplay} - Chưa có lịch sử mua hàng`;
                messageElement.style.color = '#ff9800';
            }
            if (detailsElement) {
                detailsElement.innerHTML = '';
            }
            // Show note for new customer
            const voucherNote = document.getElementById('voucher-note');
            if (voucherNote) {
                voucherNote.style.display = 'block';
            }
        }
        
        // Hide note if customer is old
        if (historyResult.success && historyResult.has_purchased) {
            const voucherNote = document.getElementById('voucher-note');
            if (voucherNote) {
                voucherNote.style.display = 'none';
            }
        }
        
        // Update voucher dropdown
        voucherSelect.innerHTML = '<option value="">-- Không dùng voucher --</option>';
        
        if (voucherResult.success && voucherResult.eligible_vouchers.length > 0) {
            voucherResult.eligible_vouchers.forEach(voucher => {
                const option = document.createElement('option');
                option.value = voucher.id;
                option.textContent = `${voucher.name} - Giảm ${voucher.percen_decrease}% (Tối thiểu: ${voucher.conditions.toLocaleString('vi-VN')}đ)`;
                option.dataset.discount = voucher.percen_decrease;
                voucherSelect.appendChild(option);
            });
        } else if (voucherResult.success && !historyResult.success) {
            // Khách hàng mới
            const option = document.createElement('option');
            option.value = '';
            option.textContent = '⚠ Khách hàng mới - Không được áp dụng voucher';
            option.disabled = true;
            voucherSelect.appendChild(option);
        } else if (voucherResult.success && historyResult.has_purchased && voucherResult.eligible_vouchers.length === 0) {
            // Khách hàng cũ nhưng chưa đủ điều kiện
            const option = document.createElement('option');
            option.value = '';
            option.textContent = `ℹ Khách chỉ nhận voucher nếu tổng giá trị các đơn hàng trước đó đáp ứng điều kiện chương trình.`;
            option.disabled = true;
            voucherSelect.appendChild(option);
        }
        
    } catch (error) {
        console.error('[ERROR_FETCH_HISTORY]', error);
        historyDiv.style.display = 'none';
        voucherSelect.innerHTML = '<option value="">-- Không dùng voucher --</option>';
    }
}

// Check voucher eligibility and calculate discount (NO PERCENTAGE DIVISION)
async function checkVoucherEligibility() {
    const voucherSelect = document.getElementById('voucher-select');
    const voucherId = voucherSelect?.value || null;
    const totalAmountElement = document.getElementById('total-amount');
    // Parse the total amount correctly (remove formatting)
    const totalAmountText = totalAmountElement?.textContent || '0';
    const originalTotal = parseInt(totalAmountText.replace(/\D/g, '')) || 0;
    
    const messageElement = document.getElementById('voucher-message');
    const discountAmountElement = document.getElementById('discount-amount');
    const originalTotalElement = document.getElementById('original-total');
    
    // Reset display
    if (discountAmountElement) discountAmountElement.value = '0 VNĐ';
    if (messageElement) messageElement.textContent = '';
    
    // Update original total field
    if (originalTotalElement) {
        originalTotalElement.value = originalTotal.toLocaleString('vi-VN') + ' VNĐ';
    }
    
    // If no voucher selected, final total = original total
    if (!voucherId) {
        if (totalAmountElement) {
            totalAmountElement.textContent = originalTotal.toLocaleString('vi-VN');
        }
        return;
    }
    
    // If total is 0, don't check
    if (originalTotal === 0) {
        if (messageElement) messageElement.textContent = 'Vui lòng thêm sản phẩm';
        if (messageElement) messageElement.style.color = '#ff6b6b';
        return;
    }
    
    try {
        console.log('[CHECK_VOUCHER] voucherId:', voucherId, 'originalTotal:', originalTotal);
        
        // Find the selected option to get discount percentage
        const selectedOption = voucherSelect.options[voucherSelect.selectedIndex];
        const discountPercent = parseInt(selectedOption.dataset.discount || 0);
        
        // Calculate discount (percen_decrease is already a percentage, NOT divided by 100)
        const discountAmount = Math.round((originalTotal * discountPercent) / 100);
        const finalTotal = originalTotal - discountAmount;
        
        if (messageElement) {
            messageElement.textContent = `✓ Áp dụng voucher "${selectedOption.textContent.split('-')[0].trim()}" - Giảm ${discountPercent}%`;
            messageElement.style.color = '#28a745';
        }
        
        if (discountAmountElement) {
            discountAmountElement.value = discountAmount.toLocaleString('vi-VN') + ' VNĐ';
            discountAmountElement.style.backgroundColor = '#d4edda';
            discountAmountElement.style.color = '#155724';
        }
        
        // Update total-amount to show FINAL total (original - discount)
        if (totalAmountElement) {
            totalAmountElement.textContent = finalTotal.toLocaleString('vi-VN');
            console.log(`[DISCOUNT_CALC] Original: ${originalTotal}, Discount: ${discountAmount}, Final: ${finalTotal}`);
        }
        
    } catch (error) {
        console.error('[ERROR_CHECK_VOUCHER]', error);
        if (messageElement) {
            messageElement.textContent = '✗ Lỗi kiểm tra voucher';
            messageElement.style.color = '#f44336';
        }
        if (discountAmountElement) discountAmountElement.value = '0 VNĐ';
    }
}

