document.addEventListener("DOMContentLoaded", function () {
  // Lấy các element từ DOM
  const defaultInformation = document.getElementById("default-information-form");
  const newInformation = document.getElementById("new-information-form");
  const defaultInformationButton = document.getElementById("default-information");
  const newInformationButton = document.getElementById("new-information");

  // Kiểm tra và xử lý form thông tin
  if (defaultInformationButton && newInformationButton && defaultInformation && newInformation) {
    function toggleForm() {
      if (defaultInformationButton.checked) {
        defaultInformation.style.display = "flex";
        newInformation.style.display = "none";
      } else if (newInformationButton.checked) {
        defaultInformation.style.display = "none";
        newInformation.style.display = "flex";
      }
    }

    defaultInformationButton.addEventListener("change", toggleForm);
    newInformationButton.addEventListener("change", toggleForm);
    toggleForm(); // Thiết lập trạng thái ban đầu
  }

  // Xử lý phương thức thanh toán
  const bankingForm = document.getElementById("banking-form");
  const paymentMethods = document.querySelectorAll('input[name="paymentMethod"]');

  if (bankingForm && paymentMethods.length > 0) {
    function toggleBankingForm() {
      const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked');
      if (selectedMethod) {
        bankingForm.style.display = selectedMethod.value === 'Banking' ? 'block' : 'none';
      }
    }

    paymentMethods.forEach(method => {
      method.addEventListener('change', toggleBankingForm);
    });
    toggleBankingForm(); // Thiết lập trạng thái ban đầu
  }
});
