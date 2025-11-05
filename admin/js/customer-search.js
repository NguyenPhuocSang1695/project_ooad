document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleSearch');
    const searchWrapper = document.getElementById('searchWrapper');
    const userTable = document.querySelector('.user-table');

    // Initialize search state
    let isSearchVisible = false;

    // Handle search toggle
    toggleButton.addEventListener('click', function() {
        isSearchVisible = !isSearchVisible;
        
        if (isSearchVisible) {
            searchWrapper.style.display = 'block';
            toggleButton.innerHTML = '<i class="fas fa-times"></i> Đóng tìm kiếm';
            toggleButton.classList.add('active');
            
            // Animate search wrapper down
            setTimeout(() => {
                searchWrapper.classList.add('show');
                userTable.style.transform = 'translateY(20px)';
                userTable.style.transition = 'transform 0.3s ease';
            }, 10);
        } else {
            searchWrapper.classList.remove('show');
            toggleButton.innerHTML = '<i class="fas fa-search"></i> Tìm kiếm';
            toggleButton.classList.remove('active');
            userTable.style.transform = 'translateY(0)';
            
            // Hide search wrapper after animation
            setTimeout(() => {
                searchWrapper.style.display = 'none';
            }, 300);
        }
    });

    // Show search if there's a search query
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) {
        toggleButton.click(); // Simulate click to show search
    }
});