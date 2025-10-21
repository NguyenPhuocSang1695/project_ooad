document.addEventListener("DOMContentLoaded", function () {
  const plus = document.querySelector(".plus");
  const minus = document.querySelector(".minus");
  const num = document.querySelector(".num");
  const quantityInput = document.getElementById("quantity");

  let quantity = parseInt(quantityInput.value);

  if (isNaN(quantity) || quantity < 1) {
    quantity = 1;
    quantityInput.value = 1;
  }

  num.innerText = quantity;

  plus.addEventListener("click", () => {
    quantity++;
    num.innerText = quantity;
    quantityInput.value = quantity;
  });

  minus.addEventListener("click", () => {
    if (quantity > 1) {
      quantity--;
      num.innerText = quantity;
      quantityInput.value = quantity;
    }
  });
});
