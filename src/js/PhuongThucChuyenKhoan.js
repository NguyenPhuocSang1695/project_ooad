document.addEventListener("DOMContentLoaded", function () {
  // Lấy các phần tử DOM
  const form = document.querySelector("form");
  const paymentMethods = document.querySelectorAll(
    'input[name="paymentMethod"]'
  );
  const bankingForm = document.getElementById("banking-form");

  // Kiểm tra phương thức thanh toán ban đầu và ẩn/hiện form chuyển khoản
  function updateBankingFormVisibility() {
    const selectedMethod = document.querySelector(
      'input[name="paymentMethod"]:checked'
    );
    if (selectedMethod) {
      bankingForm.style.display =
        selectedMethod.value === "Banking" ? "block" : "none";

      // Nếu không phải Banking, loại bỏ thuộc tính required cho các trường trong banking form
      const requiredFields = document.querySelectorAll(".banking-required");
      requiredFields.forEach(function (field) {
        if (selectedMethod.value !== "Banking") {
          field.removeAttribute("required");
        } else {
          field.setAttribute("required", "required");
        }
      });
    }
  }

  // Khởi tạo trạng thái hiển thị
  updateBankingFormVisibility();

  // Thêm sự kiện cho các radio button
  paymentMethods.forEach(function (method) {
    method.addEventListener("change", updateBankingFormVisibility);
  });

  // Nếu dùng form validation của trình duyệt, các trường hidden cũng được kiểm tra
  // Tắt validation mặc định của trình duyệt và tự xử lý
  form.setAttribute("novalidate", "true");

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const selectedMethod = document.querySelector(
      'input[name="paymentMethod"]:checked'
    );

    if (!selectedMethod) {
      alert("Vui lòng chọn phương thức thanh toán");
      return;
    }

    // Nếu là Banking, kiểm tra các trường bắt buộc
    if (selectedMethod.value === "Banking") {
      const requiredFields = document.querySelectorAll(".banking-required");
      let isValid = true;
      let emptyFields = [];

      requiredFields.forEach(function (field) {
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add("error");
          const label = field.previousElementSibling;
          const fieldName =
            field.placeholder ||
            (label && label.textContent.trim()) ||
            "Trường này";
          emptyFields.push(fieldName);
        } else {
          field.classList.remove("error");
        }
      });

      if (!isValid) {
        alert(
          "Vui lòng nhập đầy đủ thông tin thanh toán qua chuyển khoản: " +
            emptyFields.join(", ")
        );
        return;
      }
    }

    // Nếu đã vượt qua tất cả kiểm tra, submit form
    console.log("Đang gửi form...");
    this.submit();
  });

  // Thêm CSS cho việc hiển thị lỗi
  const style = document.createElement("style");
  style.textContent = `
      .error {
        border: 1px solid red;
        background-color: #fff0f0;
      }
    `;
  document.head.appendChild(style);
});
