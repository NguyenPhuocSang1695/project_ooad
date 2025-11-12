document.addEventListener('DOMContentLoaded', function() {
    // Event listener for Add User button
    const addUserBtn = document.getElementById('addUser');
    const modal = document.getElementById('addUserModal');
    const closeBtn = modal.querySelector('.btn-close');
    const cancelBtn = modal.querySelector('.btn-secondary');

    if (addUserBtn) {
        addUserBtn.addEventListener('click', function() {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling of background
            applyRolePasswordRule();
        });
    }

    // Close modal when clicking close button
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    });

    // Close modal when clicking cancel button
    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    });

    // Toggle password requirement based on role
    function applyRolePasswordRule() {
        const roleSelect = document.getElementById('role');
        const passwordRow = document.getElementById('passwordRow');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const usernameRow = document.getElementById('usernameRow');
        const usernameInput = document.getElementById('username');

        const isAdmin = roleSelect && roleSelect.value === 'admin';
        if (isAdmin) {
            if (passwordRow) passwordRow.style.display = '';
            if (passwordInput) passwordInput.required = true;
            if (confirmInput) confirmInput.required = true;
            if (usernameRow) usernameRow.style.display = '';
            if (usernameInput) usernameInput.required = true;
        } else {
            if (passwordRow) passwordRow.style.display = 'none';
            if (passwordInput) { passwordInput.required = false; passwordInput.value = ''; }
            if (confirmInput) { confirmInput.required = false; confirmInput.value = ''; }
            if (usernameRow) usernameRow.style.display = 'none';
            if (usernameInput) { usernameInput.required = false; usernameInput.value = ''; }
        }
    }

    // React to role changes
    const roleSelectEl = document.getElementById('role');
    if (roleSelectEl) {
        roleSelectEl.addEventListener('change', applyRolePasswordRule);
    }

    // Form submission handler
    document.getElementById('submitAddUser').addEventListener('click', function() {
        const form = document.getElementById('addUserForm');
        // Ensure role-based required attributes are applied before validity check
        applyRolePasswordRule();
        if (form.checkValidity()) {
            const formData = new FormData(form);
            
            fetch('../php/add_user.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                // Debug raw response if JSON parsing fails
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Raw add_user.php response:', text);
                    throw new Error('Phản hồi không phải JSON hợp lệ');
                }
                
                if (data.success) {
                    alert('Người dùng đã được thêm thành công!');
                    // Hide custom modal
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                    location.reload(); // Reload the page to show new user
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi thêm người dùng');
            });
        } else {
            form.reportValidity();
        }
    });
});
