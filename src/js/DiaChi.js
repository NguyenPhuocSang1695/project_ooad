document.getElementById("province").addEventListener("change", function () {
  const provinceId = this.value;
  const districtSelect = document.getElementById("district");

  // Reset danh sách quận
  districtSelect.innerHTML = "<option>Đang tải...</option>";

  fetch("ajax_get_district.php?province_id=" + provinceId)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Lỗi mạng: " + response.statusText);
      }
      return response.json();
    })
    .then((data) => {
      // Xóa các option cũ
      districtSelect.innerHTML = "";

      data.forEach((district) => {
        const option = document.createElement("option");
        option.value = district.id;
        option.textContent = district.name;
        districtSelect.appendChild(option);
      });
    })
    .catch((error) => {
      console.error("Lỗi khi tải quận/huyện:", error);
      districtSelect.innerHTML = "<option>Lỗi tải dữ liệu</option>";
    });
});

document.getElementById("district").addEventListener("change", function () {
  const districtId = this.value;
  const wardSelect = document.getElementById("wards");

  // Reset danh sách phường
  wardSelect.innerHTML = "<option>Đang tải...</option>";

  fetch("ajax_get_wards.php?district_id=" + districtId)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Lỗi mạng: " + response.statusText);
      }
      return response.json();
    })
    .then((data) => {
      // Xóa các option cũ
      wardSelect.innerHTML = "";

      data.forEach((wards) => {
        const option = document.createElement("option");
        option.value = wards.id;
        option.textContent = wards.name;
        wardSelect.appendChild(option);
      });
    })
    .catch((error) => {
      console.error("Lỗi khi tải phường/xã:", error);
      wardSelect.innerHTML = "<option>Lỗi tải dữ liệu</option>";
    });
});
