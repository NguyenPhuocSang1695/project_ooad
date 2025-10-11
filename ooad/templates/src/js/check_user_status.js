// ../src/js/check_status.js

function checkAccountStatus() {
    fetch('../php/check_user_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.blocked) {
                alert('Tài khoản của bạn đã bị khóa.');
                document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                window.location.href = 'user-login.php'; 
            }
        })
        .catch(error => {
            console.error('Error checking user status:', error);
            // Trong trường hợp lỗi cũng logout
            document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            window.location.href = 'user-login.php';
        });
}

// Kiểm tra ngay khi trang load
checkAccountStatus();

// Option: nếu muốn kiểm tra định kỳ ví dụ 30 giây/lần:
setInterval(checkAccountStatus, 30000);
