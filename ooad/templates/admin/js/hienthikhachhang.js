// Hàm mã hóa password 
function hashPassword(password) {
    let hash = 0;
    for (let i = 0; i < password.length; i++) {
        const char = password.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    return hash.toString(16);
}

// Dữ liệu mẫu người dùng và lịch sử đơn hàng
// let users = [
//     { 
//         id: 1, 
//         fullName: "Nguyễn Thanh Tùng", 
//         phone: "9999 999 999", 
//         email: "nguyentb@gmail.com", 
//         password: hashPassword("123456"), 
//         gender: "Nam", 
//         hometown: "Thái Bình", 
//         totalOrders: 5, 
//         type: "Kim cương", 
//         status: "active", 
//         img: "../image/sontung.webp",
//         orders: [
//             { id: "#123123", productImg: "../../assets/images/CAY13.jpg", amount: 500000, date: "2025-03-20" },
//             { id: "#123124", productImg: "../../assets/images/CAY14.jpg", amount: 300000, date: "2025-03-19" },
//             { id: "#123125", productImg: "../../assets/images/CAY12.jpg", amount: 700000, date: "2025-03-18" }
//         ]
//     },
//     { 
//         id: 2, 
//         fullName: "Hiếu 2nd", 
//         phone: "8888 888 888", 
//         email: "hieu2nd@gmail.com", 
//         password: hashPassword("abcdef"), 
//         gender: "Nam", 
//         hometown: "Hồ Chí Minh", 
//         totalOrders: 3, 
//         type: "Vàng", 
//         status: "active", 
//         img: "../image/hth.webp",
//         orders: [
//             { id: "#123126", productImg: "../image/product1.webp", amount: 200000, date: "2025-03-21" }
//         ]
//     },
//     { 
//         id: 3, 
//         fullName: "Diễm", 
//         phone: "7777 777 777", 
//         email: "diem@gmail.com", 
//         password: hashPassword("xyz789"), 
//         gender: "Nữ", 
//         hometown: "Đồng Nai", 
//         totalOrders: 2, 
//         type: "Bạc", 
//         status: "active", 
//         img: "../image/baolam.jpg",
//         orders: [
//             { id: "#123127", productImg: "../image/product1.webp", amount: 450000, date: "2025-03-20" }
//         ]
//     }
// ];

// Hiển thị danh sách người dùng dạng bảng
function renderUsers(filteredUsers = users) {
    const userList = document.getElementById('userList');
    userList.innerHTML = '';
    filteredUsers.forEach(user => {
        const tr = document.createElement('tr');
        tr.className = user.status === 'locked' ? 'locked' : '';
        tr.innerHTML = `
            <td>${user.id}</td>
            <td>${user.fullName}</td>
            <td>${user.phone}</td>
            <td>${user.email}</td>
            <td>${user.status === 'locked' ? 'Đã khoá' : 'Hoạt động'}</td>
        `;
        tr.onclick = () => showUserDetails(user.id);
        userList.appendChild(tr);
    });
}

// Function to handle page changes
function changePage(pageNumber) {
    // Update URL with new page number
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('page', pageNumber);
    window.location.href = currentUrl.toString();
}

// Function to handle search with pagination
function searchUsers() {
    const searchTerm = document.querySelector('.search-bar-customer').value.toLowerCase();
    fetch(`../php/search-users.php?search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            const userList = document.getElementById('userList');
            userList.innerHTML = '';
            
            if (data.users.length > 0) {
                data.users.forEach(user => {
                    const tr = document.createElement('tr');
                    const statusText = user.Status === 'Active' ? 'Hoạt động' : 'Đã khóa';
                    const statusClass = user.Status === 'Active' ? 'text-success' : 'text-danger';
                    tr.innerHTML = `
                        <td>${user.Username}</td>
                        <td>${user.FullName}</td>
                        <td>${user.Phone}</td>
                        <td>${user.Email}</td>
                        <td class="${statusClass}">${statusText}</td>
                        <td><button class="btn btn-outline-warning" onclick="showEditUserPopup('${user.Username}')">Chỉnh sửa</button></td>
                    `;
                    userList.appendChild(tr);
                });
            } else {
                userList.innerHTML = '<tr><td colspan="6" style="text-align: center;">Không tìm thấy kết quả</td></tr>';
            }
        })
        .catch(error => console.error('Error:', error));
}

// Hiển thị chi tiết người dùng và lịch sử 5 ngày gần đây
function showUserDetails(userId) {
    const user = users.find(u => u.id === userId);
    const detailsContent = document.getElementById('userDetailsContent');
    const currentDate = new Date("2025-03-22");
    const fiveDaysAgo = new Date(currentDate);
    fiveDaysAgo.setDate(currentDate.getDate() - 5);

    const recentOrders = user.orders.filter(order => {
        const orderDate = new Date(order.date);
        return orderDate >= fiveDaysAgo && orderDate <= currentDate;
    });

    detailsContent.innerHTML = `
        <h3>Thông tin người dùng</h3>
        <div class="form-group">
            <label>Họ và tên:</label>
            <p>${user.fullName}</p>
        </div>
        <div class="form-group">
            <label>Số điện thoại:</label>
            <p>${user.phone}</p>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <p>${user.email}</p>
        </div>
        <div class="form-group">
            <label>Giới tính:</label>
            <p>${user.gender}</p>
        </div>
        <div class="form-group">
            <label>Quê quán:</label>
            <p>${user.hometown}</p>
        </div>
        <div class="form-group">
            <label>Tổng đơn:</label>
            <p>${user.totalOrders}</p>
        </div>
        <div class="form-group">
            <label>Loại khách hàng:</label>
            <p>${user.type}</p>
        </div>
        <div class="form-group">
            <label>Trạng thái:</label>
            <p>${user.status === 'locked' ? 'Đã khoá' : 'Hoạt động'}</p>
        </div>
        <div class="history-list">
            <h4>Lịch sử đơn hàng (5 ngày gần đây)</h4>
            ${recentOrders.length > 0 ? recentOrders.map(order => `
                <div class="history-item">
                    <img src="${order.productImg}" style="width: 50px; height: 50px; border: 3px solid #35635A;">
                    <p>${order.id} - ${order.amount.toLocaleString()} VND - ${order.date}</p>
                </div>
            `).join('') : '<p>Không có đơn hàng nào trong 5 ngày gần đây.</p>'}
        </div>
        <div class="form-actions">
            <button onclick="showEditUserPopup(${user.id})" class="save-btn">Chỉnh sửa</button>
            <button onclick="toggleLockUser(${user.id})" class="save-btn" style="background: ${user.status === 'locked' ? '#28A745' : '#D95E5E'}">${user.status === 'locked' ? 'Mở khoá' : 'Khoá'}</button>
            <button onclick="closeUserDetailsPopup()" class="cancel-btn">Đóng</button>
        </div>
    `;
    document.getElementById('userDetailsOverlay').classList.add('active');
}

// Sửa lại hàm showEditUserPopup để hiển thị và chọn đúng địa chỉ
function showEditUserPopup(username) {
    // Fetch user details from the server
    fetch(`../php/get-user-details.php?userId=${username}`)
        .then(response => response.json())
        .then(user => {
            if (user.error) {
                alert('Không thể tải thông tin người dùng: ' + user.error);
                return;
            }

            // Fill in the edit form with user data
            document.getElementById('editUsername').value = user.Username;
            document.getElementById('editFullName').value = user.FullName;
            document.getElementById('editEmail').value = user.Email || '';
            document.getElementById('editPhone').value = user.Phone;
            document.getElementById('editAddress').value = user.Address;
            document.getElementById('editStatus').value = user.Status;

            // Set province and trigger change event
            const provinceSelect = document.getElementById('editProvince');
            provinceSelect.value = user.Province;
            provinceSelect.dispatchEvent(new Event('change'));

            // Set district and ward after province is loaded
            setTimeout(() => {
                const districtSelect = document.getElementById('editDistrict');
                districtSelect.value = user.District;
                districtSelect.dispatchEvent(new Event('change'));

                // Set ward after district is loaded
                setTimeout(() => {
                    const wardSelect = document.getElementById('editWard');
                    wardSelect.value = user.Ward;
                }, 500);
            }, 500);

            document.getElementById('editUserOverlay').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi tải thông tin người dùng');
        });
}

function closeUserDetailsPopup() {
    document.getElementById('userDetailsOverlay').classList.remove('active');
}

// Hiển thị popup thêm người dùng
function showAddUserPopup() {
    document.getElementById('addUserOverlay').style.display = 'flex';
}

function closeAddUserPopup() {
    document.getElementById('addUserOverlay').style.display = 'none';
}

function addUser() {
    // Validate form trước khi submit
    if (!validateForm()) {
        return;
    }

    const userData = {
        username: document.getElementById('addUsername').value,
        fullname: document.getElementById('addFullName').value,
        email: document.getElementById('addEmail').value,
        password: document.getElementById('addPassword').value,
        phone: document.getElementById('addPhone').value,
        address: document.getElementById('addAddress').value,
        province: document.getElementById('addProvince').value,
        district: document.getElementById('addDistrict').value,
        ward: document.getElementById('addWard').value,
        status: document.getElementById('addStatus').value
    };

    // Gửi request đến server
    fetch('../php/add-user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Thêm người dùng thành công!');
            closeAddUserPopup();
            location.reload(); // Refresh để hiển thị người dùng mới
        } else {
            alert(data.message || 'Có lỗi xảy ra khi thêm người dùng');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thêm người dùng');
    });
}

function validateForm() {
    let isValid = true;
    
    // Validate username
    const username = document.getElementById('addUsername').value;
    if (username.length < 3) {
        document.getElementById('username-error').textContent = 'Tên tài khoản phải có ít nhất 3 ký tự';
        isValid = false;
    } else {
        document.getElementById('username-error').textContent = '';
    }

    // Validate fullname
    const fullname = document.getElementById('addFullName').value;
    if (!fullname) {
        document.getElementById('fullname-error').textContent = 'Vui lòng nhập họ tên';
        isValid = false;
    } else {
        document.getElementById('fullname-error').textContent = '';
    }

    // Validate email (không bắt buộc nhưng phải đúng định dạng nếu có)
    const email = document.getElementById('addEmail').value;
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('email-error').textContent = 'Email không hợp lệ';
        isValid = false;
    } else {
        document.getElementById('email-error').textContent = '';
    }

    // Validate password
    const password = document.getElementById('addPassword').value;
    if (password.length < 6) {
        document.getElementById('password-error').textContent = 'Mật khẩu phải có ít nhất 6 ký tự';
        isValid = false;
    } else {
        document.getElementById('password-error').textContent = '';
    }

    // Validate phone
    const phone = document.getElementById('addPhone').value;
    if (!/^[0-9]{10}$/.test(phone)) {
        document.getElementById('phone-error').textContent = 'Số điện thoại phải có 10 chữ số';
        isValid = false;
    } else {
        document.getElementById('phone-error').textContent = '';
    }

    // Validate các trường địa chỉ
    const address = document.getElementById('addAddress').value;
    const province = document.getElementById('addProvince').value;
    const district = document.getElementById('addDistrict').value;
    const ward = document.getElementById('addWard').value;

    if (!address) {
        document.getElementById('address-error').textContent = 'Vui lòng nhập địa chỉ';
        isValid = false;
    }
    if (!province) {
        document.getElementById('province-error').textContent = 'Vui lòng nhập tỉnh/thành phố';
        isValid = false;
    }
    if (!district) {
        document.getElementById('district-error').textContent = 'Vui lòng nhập quận/huyện';
        isValid = false;
    }
    if (!ward) {
        document.getElementById('ward-error').textContent = 'Vui lòng nhập phường/xã';
        isValid = false;
    }

    return isValid;
}

// Hiển thị popup chỉnh sửa người dùng
function closeEditUserPopup() {
    document.getElementById('editUserOverlay').style.display = 'none';
}

// Sửa lại hàm saveUserEdit để gửi thông tin địa chỉ đúng
function saveUserEdit() {
    const userData = {
        username: document.getElementById('editUsername').value,
        fullname: document.getElementById('editFullName').value,
        email: document.getElementById('editEmail').value,
        phone: document.getElementById('editPhone').value,
        address: document.getElementById('editAddress').value,
        province: document.getElementById('editProvince').value,
        district: document.getElementById('editDistrict').value,
        ward: document.getElementById('editWard').value,
        status: document.getElementById('editStatus').value
    };

    if (!validateEditForm(userData)) {
        return;
    }

    fetch('../php/update-user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cập nhật thông tin thành công!');
            closeEditUserPopup();
            location.reload();
        } else {
            alert('Có lỗi xảy ra: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật thông tin');
    });
}

function validateEditForm(userData) {
    if (!userData.fullname || !userData.phone || !userData.address || 
        !userData.province || !userData.district || !userData.ward) {
        alert('Vui lòng điền đầy đủ thông tin bắt buộc');
        return false;
    }

    if (userData.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userData.email)) {
        alert('Email không hợp lệ');
        return false;
    }

    if (!/^[0-9]{10}$/.test(userData.phone)) {
        alert('Số điện thoại phải có 10 chữ số');
        return false;
    }

    return true;
}

// Khoá/Mở khoá người dùng
function toggleLockUser(userId) {
    const user = users.find(u => u.id === userId);
    if (user.status === 'active' && confirm('Bạn có chắc chắn muốn khoá người dùng này?')) {
        user.status = 'locked';
        alert('Đã khoá người dùng thành công!');
    } else if (user.status === 'locked' && confirm('Bạn có chắc chắn muốn mở khoá người dùng này?')) {
        user.status = 'active';
        alert('Đã mở khoá người dùng thành công!');
    }
    // document.getElementById('userDetailsOverlay').classList.remove('active');
    // renderUsers();
};

// Khởi tạo
// document.addEventListener('DOMContentLoaded', () => {
//     renderUsers();
// }
// );

// Hàm tải danh sách tỉnh/thành phố
async function loadProvinces() {
    try {
        const response = await fetch('../php/get_Cities.php');
        const provinces = await response.json();
        const provinceSelect = document.getElementById('addProvince');
        provinceSelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';
        provinces.forEach(province => {
            provinceSelect.innerHTML += `<option value="${province.id}">${province.name}</option>`;
        });
    } catch (error) {
        console.error('Lỗi khi tải danh sách tỉnh/thành:', error);
    }
}

// Hàm tải danh sách quận/huyện
async function loadDistricts() {
    const provinceId = document.getElementById('addProvince').value;
    if (!provinceId) return;

    try {
        const response = await fetch(`../php/get_District.php?province_id=${provinceId}`);
        const districts = await response.json();
        const districtSelect = document.getElementById('addDistrict');
        districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        districts.forEach(district => {
            districtSelect.innerHTML += `<option value="${district.id}">${district.name}</option>`;
        });
    } catch (error) {
        console.error('Lỗi khi tải danh sách quận/huyện:', error);
    }
}

// Hàm tải danh sách phường/xã
async function loadWards() {
    const districtId = document.getElementById('addDistrict').value;
    if (!districtId) return;

    try {
        const response = await fetch(`../php/get_Address.php?district_id=${districtId}`);
        const wards = await response.json();
        const wardSelect = document.getElementById('addWard');
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        wards.forEach(ward => {
            wardSelect.innerHTML += `<option value="${ward.id}">${ward.name}</option>`;
        });
    } catch (error) {
        console.error('Lỗi khi tải danh sách phường/xã:', error);
    }
}

// Thêm event listener cho select box tỉnh/thành phố
document.getElementById('addProvince').addEventListener('change', function() {
    const provinceId = this.value;
    const districtSelect = document.getElementById('addDistrict');
    
    // Reset quận/huyện select box
    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
    
    if (provinceId) {
        // Tạo form data
        const formData = new FormData();
        formData.append('province_id', provinceId);
        
        // Gửi request AJAX
        fetch('../php/add_district.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Thêm các option mới vào select box quận/huyện
                data.districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.district_id;
                    option.textContent = district.name;
                    districtSelect.appendChild(option);
                });
            } else {
                console.error('Lỗi:', data.message);
            }
        })
        .catch(error => console.error('Lỗi:', error));
    }
});

// Thêm event listener cho select box quận/huyện
document.getElementById('addDistrict').addEventListener('change', function() {
    const districtId = this.value;
    const wardSelect = document.getElementById('addWard');
    
    // Reset phường/xã select box
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    
    if (districtId) {
        // Tạo form data
        const formData = new FormData();
        formData.append('district_id', districtId);
        
        // Gửi request AJAX
        fetch('../php/add_ward.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Thêm các option mới vào select box phường/xã
                data.wards.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward.wards_id;
                    option.textContent = ward.name;
                    wardSelect.appendChild(option);
                });
            } else {
                console.error('Lỗi:', data.message);
            }
        })
        .catch(error => console.error('Lỗi:', error));
    }
});

// Event listener cho select box tỉnh/thành phố trong form chỉnh sửa
document.getElementById('editProvince').addEventListener('change', function() {
    const provinceId = this.value;
    const districtSelect = document.getElementById('editDistrict');
    
    // Reset quận/huyện select box
    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
    document.getElementById('editWard').innerHTML = '<option value="">Chọn Phường/Xã</option>';
    
    if (provinceId) {
        const formData = new FormData();
        formData.append('province_id', provinceId);
        
        fetch('../php/add_district.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.district_id;
                    option.textContent = district.name;
                    districtSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Lỗi:', error));
    }
});

// Event listener cho select box quận/huyện trong form chỉnh sửa
document.getElementById('editDistrict').addEventListener('change', function() {
    const districtId = this.value;
    const wardSelect = document.getElementById('editWard');
    
    // Reset phường/xã select box
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    
    if (districtId) {
        const formData = new FormData();
        formData.append('district_id', districtId);
        
        fetch('../php/add_ward.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.wards.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward.wards_id;
                    option.textContent = ward.name;
                    wardSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Lỗi:', error));
    }
});
