document.addEventListener("DOMContentLoaded", function () {
  const totalPages = 5; 
  let currentPage = localStorage.getItem("currentPage") ? parseInt(localStorage.getItem("currentPage")) : 1; 

  const pageNumbersContainer = document.getElementById("pageNumbers");
  const prevPageButton = document.getElementById("prevPage");
  const nextPageButton = document.getElementById("nextPage");

  function renderPagination() {
      pageNumbersContainer.innerHTML = "";

      let startPage = Math.max(1, currentPage - 1);
      let endPage = Math.min(totalPages, startPage + 2);
      if (endPage - startPage < 2) {
          startPage = Math.max(1, endPage - 2);
      } 

      if (startPage > 1) {
          pageNumbersContainer.appendChild(createPageButton(1));
          if (startPage > 2) {
              pageNumbersContainer.appendChild(createEllipsis());
          }
      }

      for (let i = startPage; i <= endPage; i++) {
          pageNumbersContainer.appendChild(createPageButton(i));
      }

      if (endPage < totalPages) {
          if (endPage < totalPages - 1) {
              pageNumbersContainer.appendChild(createEllipsis());
          }
          pageNumbersContainer.appendChild(createPageButton(totalPages));
      }

      prevPageButton.disabled = currentPage === 1;
      nextPageButton.disabled = currentPage === totalPages;
  }

  function createPageButton(page) {
      const button = document.createElement("button");
      button.innerText = page;
      button.style.fontWeight = page === currentPage ? "bold" : "normal";
      button.addEventListener("click", function () {
          navigateToPage(page);
      });
      return button;
  }

  function createEllipsis() {
      const span = document.createElement("span");
      span.innerText = "...";
      return span;
  }

  function navigateToPage(page) {
      localStorage.setItem("currentPage", page);
      window.location.href = `orderPage${page}.html`;
  }

  function changePage(direction) {
      if (direction === 'prev' && currentPage > 1) {
          navigateToPage(currentPage - 1);
      } else if (direction === 'next' && currentPage < totalPages) {
          navigateToPage(currentPage + 1);
      }
  }

  prevPageButton.addEventListener("click", function () { changePage('prev'); });
  nextPageButton.addEventListener("click", function () { changePage('next'); });

  renderPagination();
});