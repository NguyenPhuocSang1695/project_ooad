function onFormSubmit(form) {
  // Lấy thông tin form (ví dụ)
  var data = {
    name: form.name.value,
    sdt: form.sdt.value,
    diachi: form.diachi.value,
    province: form.province.value,
    district: form.district.value,
    ward: form.ward.value,
  };

  // Lưu vào cookie (hoặc localStorage tuỳ bạn)
  var jsonData = JSON.stringify(data);
  document.cookie = "order_info=" + jsonData;

  // Nếu bạn muốn submit bằng AJAX, xử lý ở đây

  // Nếu muốn chặn submit truyền thống:
  return false;
}
