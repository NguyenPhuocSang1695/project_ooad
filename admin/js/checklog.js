
document.addEventListener('DOMContentLoaded', () => {
    const cachedUserInfo = localStorage.getItem('userInfo');
    if (cachedUserInfo) {
        const userInfo = JSON.parse(cachedUserInfo);
        updateUIWithUserInfo(userInfo);
    }

    // Vẫn gọi API để cập nhật thông tin mới nhất
    fetch('../php/sessionHandler.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const userInfo = {
                    username: data.username,
                    fullname: data.fullname,
                    role: data.role,
                    avatar: data.role === 'admin' ? '../../assets/images/admin.jpg' : '../../assets/images/sang.jpg'
                };
                
                // Lưu thông tin vào localStorage
                localStorage.setItem('userInfo', JSON.stringify(userInfo));
                updateUIWithUserInfo(userInfo);
            } else {
                localStorage.removeItem('userInfo'); // Xóa thông tin cũ nếu có lỗi
                window.location.href = '../index.php';
            }
        })
        .catch(error => {
            console.error('Lỗi khi kiểm tra trạng thái đăng nhập:', error);
        });
});
function updateUIWithUserInfo(userInfo) {
    const nameElements = document.querySelectorAll('.name-employee p');
    nameElements.forEach(el => el.textContent = userInfo.fullname);
    const roleElements = document.querySelectorAll('.position-employee p');
    roleElements.forEach(el => el.textContent = userInfo.role);
    const usernameElements = document.querySelectorAll('#offcanvasWithBothOptionsLabel');
    usernameElements.forEach(el => el.textContent = userInfo.username);
    const displayNameElements = document.querySelectorAll('#employee-displayname');
    displayNameElements.forEach(el => el.textContent = userInfo.fullname);
    const avatarElements = document.querySelectorAll('.avatar');
    avatarElements.forEach(el => el.src = userInfo.avatar);
}

// function logout() {
//     localStorage.removeItem('userInfo'); 
//     fetch('../php/logout.php', { method: 'POST' })
//         .then(() => {
//             window.location.href = '../index.php';
//         })
//         .catch(error => {
//             console.error('Lỗi khi đăng xuất:', error);
//         });
// }

function loadPage(page) {
    fetch(page)
        .then(response => {
            if (!response.ok) {
                throw new Error('Không thể tải trang!');
            }
            return response.text();
        })
        .then(data => {
            document.getElementById('content').innerHTML = data;
        })
        .catch(error => {
            console.error('Lỗi khi tải trang:', error);
            alert('Không thể tải trang!');
        });
}