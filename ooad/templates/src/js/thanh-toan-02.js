document.addEventListener("DOMContentLoaded", function () {
  const bankingForm = document.getElementById("banking-form");
  const mastercard = document.getElementById("mastercard");

  mastercard.addEventListener("click", function () {
    if (bankingForm.style.display === "flex") {
      bankingForm.style.display = "none";
    } else {
      bankingForm.style.display = "flex";
    }
  });
});
