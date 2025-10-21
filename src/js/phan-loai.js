document.addEventListener("DOMContentLoaded", async function () {
  // Lấy tham số từ URL
  const params = new URLSearchParams(window.location.search);
  const categoryId = params.get("category_id");
  const categoryName = params.get("category_name");

  // Biến lưu trữ dữ liệu sản phẩm gốc
  let originalProducts = [];

  // Lấy danh mục từ API PHP
  async function getCategories() {
    try {
      const response = await fetch("../php-api/get_categories.php");
      const categories = await response.json();

      // Tạo mảng categoryMap động từ dữ liệu lấy được
      const categoryMap = {};
      categories.forEach((category) => {
        categoryMap[category.CategoryID] = category.CategoryName;
      });

      // Cập nhật danh mục trong giao diện
      updateCategoryDropdown(categories);

      // Nếu category_id có trong URL, hiển thị tên danh mục
      displayCategoryName(categoryMap, categoryId);

      // Gọi hàm để tải sản phẩm nếu category_id có
      if (categoryId) {
        await loadProducts(categoryId);
      }
    } catch (error) {
      console.error("Lỗi khi tải danh mục:", error);
    }
  }

  // Cập nhật danh mục vào dropdown
  function updateCategoryDropdown(categories) {
    const dropdownMenu = document.querySelector(".dropdown-menu");
    if (dropdownMenu) {
      dropdownMenu.innerHTML = ""; // Xóa danh sách cũ
      categories.forEach((category) => {
        const listItem = document.createElement("li");
        listItem.innerHTML = `<a class="dropdown-item" href="./phan-loai.php?category_id=${category.CategoryID}">${category.CategoryName}</a>`;
        dropdownMenu.appendChild(listItem);
      });
    }
  }

  // Hiển thị tên danh mục nếu có category_id
  function displayCategoryName(categoryMap, categoryId) {
    const categoryName = categoryMap[categoryId] || "Danh mục không tồn tại";
    const categoryElement = document.getElementById("product_type_list");
    if (categoryElement) {
      categoryElement.textContent = categoryName;
    }
  }

  // Lấy dữ liệu từ php để xử lý
  async function loadProducts(categoryId) {
    const productList = document.getElementById("product-list");
    try {
      const response = await fetch(
        `../php-api/filter-product.php?category_id=${categoryId}`
      );
      const text = await response.text();
      console.log("Raw API Response:", text);

      const data = JSON.parse(text);
      if (data.error) {
        productList.innerHTML = `<p class="text-danger">${data.error}</p>`;
        return;
      }

      // Lưu trữ dữ liệu gốc để sử dụng cho việc sắp xếp
      originalProducts = [...data];

      // Tạo dropdown sắp xếp
      createSortUI();

      // Hiển thị sản phẩm
      paginateProducts(data);
    } catch (error) {
      productList.innerHTML = `<p class="text-danger">Lỗi khi tải dữ liệu.</p>`;
    }
  }

  // Tạo giao diện sắp xếp sản phẩm
  function createSortUI() {
    const sortContainer = document.querySelector(".sortSelect");
    if (!sortContainer) return;

    sortContainer.innerHTML = `
      <div class="sort-container" style="text-align: right; margin: 15px 0;">
        <label for="sortProducts" style="margin-right: 10px; font-weight: bold;">Sắp xếp:</label>
        <select id="sortProducts" class="form-select" style="display: inline-block; width: 172px;">
          <option value="default">Mặc định</option>
          <option value="price-asc">Giá: Thấp đến cao</option>
          <option value="price-desc">Giá: Cao đến thấp</option>
          <option value="name-asc">Tên: A-Z</option>
          <option value="name-desc">Tên: Z-A</option>
        </select>
      </div>
    `;

    // Thêm sự kiện sắp xếp
    document
      .getElementById("sortProducts")
      .addEventListener("change", function () {
        sortProducts(this.value);
      });
  }

  // Hàm sắp xếp sản phẩm
  function sortProducts(sortType) {
    let sortedProducts = [...originalProducts];

    switch (sortType) {
      case "price-asc":
        sortedProducts.sort((a, b) => parseInt(a.Price) - parseInt(b.Price));
        break;
      case "price-desc":
        sortedProducts.sort((a, b) => parseInt(b.Price) - parseInt(a.Price));
        break;
      case "name-asc":
        sortedProducts.sort((a, b) =>
          a.ProductName.localeCompare(b.ProductName)
        );
        break;
      case "name-desc":
        sortedProducts.sort((a, b) =>
          b.ProductName.localeCompare(a.ProductName)
        );
        break;
      default:
        // Giữ thứ tự mặc định
        break;
    }

    // Cập nhật hiển thị
    paginateProducts(sortedProducts);
  }

  // Xử lý dữ liệu từ API, kiểm tra số lượng sản phẩm ở 1 trang
  function paginateProducts(data) {
    let currentPage = 1; // Vị trí mặc định là trang đầu tiên
    const itemsPerPage = 8; // Số lượng sản phẩm tối đa ở 1 trang
    // Tính tổng số trang cần thiết
    const totalPages = Math.ceil(data.length / itemsPerPage);

    // Hiển thị thông báo khi không có sản phẩm nào
    const productList = document.getElementById("product-list");
    if (data.length === 0) {
      productList.innerHTML = `<div class="alert alert-info w-100 text-center">Không tìm thấy sản phẩm nào</div>`;
      document.getElementById("pagination-button").innerHTML = "";
      return;
    }

    // Chuyển trang
    function renderPage(page) {
      productList.innerHTML = "";
      const start = (page - 1) * itemsPerPage;
      const pageData = data.slice(start, start + itemsPerPage);

      // Tạo container grid cho sản phẩm
      const productGrid = document.createElement("div");
      productGrid.className = "products-grid"; // Sử dụng lớp custom cho grid

      // // Thêm CSS cho grid vào productGrid
      // productGrid.style.display = "grid";
      // productGrid.style.gridTemplateColumns = "repeat(4, 1fr)";
      // productGrid.style.gap = "20px";

      pageData.forEach((product) => {
        productGrid.appendChild(createProductCard(product));
      });

      productList.appendChild(productGrid);

      // Cuộn lên đầu trang sau khi chuyển trang (sau khi render)
      requestAnimationFrame(() => {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });

      renderPagination();
    }

    // Nút phân trang
    function renderPagination() {
      const paginationDiv = document.getElementById("pagination-button");
      paginationDiv.innerHTML = "";

      // Tạo div wrapper cho nút phân trang
      const paginationWrapper = document.createElement("div");
      paginationWrapper.className = "d-flex justify-content-center mt-4";

      // Nút quay lại trang trước
      paginationWrapper.appendChild(
        createPaginationButton("‹", currentPage > 1, () =>
          renderPage(--currentPage)
        )
      );

      // Hiển thị các nút số trang
      if (totalPages <= 5) {
        // Hiển thị tất cả các trang nếu totalPages <= 5
        for (let i = 1; i <= totalPages; i++) {
          paginationWrapper.appendChild(
            createPaginationButton(
              i,
              true,
              () => {
                currentPage = i;
                renderPage(currentPage);
              },
              i === currentPage
            )
          );
        }
      } else {
        // Hiển thị trang 1
        paginationWrapper.appendChild(
          createPaginationButton(
            1,
            true,
            () => {
              currentPage = 1;
              renderPage(currentPage);
            },
            currentPage === 1
          )
        );

        // Xác định các trang hiển thị giữa
        let startPage = Math.max(2, currentPage - 1);
        let endPage = Math.min(totalPages - 1, currentPage + 1);

        // Nếu đang ở trang 1, hiển thị 2, 3, 4
        if (currentPage === 1) {
          endPage = 4;
        }
        // Nếu đang ở trang cuối, hiển thị n-3, n-2, n-1
        else if (currentPage === totalPages) {
          startPage = totalPages - 3;
        }

        // Thêm dấu "..." nếu cần
        if (startPage > 2) {
          paginationWrapper.appendChild(
            createPaginationButton("...", false, null, false)
          );
        }

        // Hiển thị các trang giữa
        for (let i = startPage; i <= endPage; i++) {
          if (i < totalPages) {
            paginationWrapper.appendChild(
              createPaginationButton(
                i,
                true,
                () => {
                  currentPage = i;
                  renderPage(currentPage);
                },
                i === currentPage
              )
            );
          }
        }

        // Thêm dấu "..." nếu cần
        if (endPage < totalPages - 1) {
          paginationWrapper.appendChild(
            createPaginationButton("...", false, null, false)
          );
        }

        // Hiển thị trang cuối
        paginationWrapper.appendChild(
          createPaginationButton(
            totalPages,
            true,
            () => {
              currentPage = totalPages;
              renderPage(currentPage);
            },
            currentPage === totalPages
          )
        );
      }

      // Nút đến trang tiếp theo
      paginationWrapper.appendChild(
        createPaginationButton("›", currentPage < totalPages, () =>
          renderPage(++currentPage)
        )
      );

      paginationDiv.appendChild(paginationWrapper);
    }

    renderPage(currentPage);
  }

  // Tạo danh sách sản phẩm
  function createProductCard(product) {
    // Tạo card sản phẩm
    const card = document.createElement("div");
    card.className = "card product-card";
    card.style.height = "100%";
    card.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";

    // Thêm hiệu ứng hover
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px)";
      this.style.boxShadow = "0 10px 20px rgba(0,0,0,0.1)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
      this.style.boxShadow = "none";
    });

    card.innerHTML = `
      <div class="card-body text-center">
        <a href="user-sanpham.php?id=${product.ProductID}">
          <img src="..${product.ImageURL}" class="img-fluid" 
            style="height: 300px; object-fit: contain;" alt="${
              product.ProductName
            }">
        </a>
        <h5 class="card-title mt-3" style="font-weight: bold; min-height: 50px;">
          <a href="user-sanpham.php?id=${
            product.ProductID
          }" class="text-decoration-none text-dark">
            ${product.ProductName}
          </a>
        </h5>
        <p class="card-text" style="color: green; font-weight: bold;">${Number(
          product.Price
        ).toLocaleString()} VND</p>
      </div>
    `;
    return card;
  }

  // Tạo nút phân trang
  function createPaginationButton(label, enabled, onClick, isActive = false) {
    const button = document.createElement("button");
    button.textContent = label;
    button.className = `btn ${
      isActive ? "btn-success active" : "btn-secondary"
    } m-1`;
    button.disabled = !enabled;
    if (enabled) button.addEventListener("click", onClick);
    return button;
  }

  // Thêm CSS cho responsive vào trang
  const addCustomStyles = () => {
    const styleEl = document.createElement("style");
    styleEl.textContent = `
      /* Responsive grid styles */
      .products-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
      }
      
      
      
      
      /* Tablet styles */
      @media (max-width: 900px) {
        .products-grid {
          grid-template-columns: repeat(2, 1fr);
        }
      }

      /* Mobile styles */
      @media (max-width: 600px) {
        .products-grid {
          grid-template-columns: repeat(1, 1fr);
          gap: 10px;
        }
        
        .card-title {
          font-size: 0.9rem;
          min-height: 40px;
        }
        
        .card-text {
          font-size: 0.9rem;
        }
      }
      
    `;
    document.head.appendChild(styleEl);
  };

  // Thêm CSS vào trang
  addCustomStyles();

  // Gọi hàm getCategories để lấy danh mục
  getCategories();
});
