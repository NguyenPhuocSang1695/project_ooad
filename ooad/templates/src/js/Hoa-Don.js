function generateInvoiceID() {
  return "HD" + Math.floor(Math.random() * 1000000);
}

function getCurrentDate() {
  let today = new Date();
  return today.toLocaleDateString("vi-VN");
}

function loadInvoice() {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let customer = JSON.parse(localStorage.getItem("customer")) || {};
  let tbody = document.getElementById("invoice-body");
  let total = 0;
  tbody.innerHTML = "";

  cart.forEach((item) => {
    let subtotal = item.price * item.quantity;
    total += subtotal;

    let row = `<tr>
                  <td>${item.name}</td>
                  <td><img src="${item.image}" alt="${item.name}"></td>
                  <td>${item.quantity}</td>
                  <td>${item.price.toLocaleString()}đ</td>
                  <td>${subtotal.toLocaleString()}đ</td>
              </tr>`;
    tbody.innerHTML += row;
  });

  document.getElementById("total-price").textContent =
    total.toLocaleString() + "đ";
  document.getElementById("invoice-id").textContent = generateInvoiceID();
  document.getElementById("purchase-date").textContent = getCurrentDate();
  document.getElementById("customer-name").textContent =
    customer.name || "Không có";
  document.getElementById("customer-phone").textContent =
    customer.phone || "Không có";
  document.getElementById("customer-address").textContent =
    customer.address || "Không có";
}

function goBack() {
  window.location.href = "san-pham.html"; // Quay lại trang giỏ hàng
}

loadInvoice();
