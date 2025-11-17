<?php
class Supplier
{
    private $supplierId;
    private $supplierName;
    private $phone;
    private $email;
    private $addressId;
    private $addressDetail;
    private $wardId;
    private $wardName;
    private $districtName;
    private $provinceName;
    private $totalProducts = 0;
    private $totalAmount = 0;

    public function __construct($data)
    {
        if (is_array($data)) {
            $this->supplierId = $data['supplier_id'] ?? null;
            $this->supplierName = $data['supplier_name'] ?? '';
            $this->phone = $data['phone'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->addressId = $data['address_id'] ?? null;
            $this->addressDetail = $data['address_detail'] ?? '';
            $this->wardId = $data['ward_id'] ?? null;
            $this->wardName = $data['ward_name'] ?? '';
            $this->districtName = $data['district_name'] ?? '';
            $this->provinceName = $data['province_name'] ?? '';
            $this->totalProducts = $data['total_products'] ?? $data['TotalProducts'] ?? 0;
            $this->totalAmount   = $data['total_amount']   ?? $data['TotalAmount']   ?? 0;
        } else {
            $this->supplierName = $data['supplier_name'] ?? '';
            $this->phone = $data['phone'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->addressDetail = $data['address_detail'] ?? '';
            $this->wardId = $data['ward_id'] ?? null;
        }
    }

    // --- Getters ---
    public function getSupplierId()
    {
        return $this->supplierId;
    }
    public function getSupplierName()
    {
        return $this->supplierName;
    }
    public function getPhone()
    {
        return $this->phone;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getAddressId()
    {
        return $this->addressId;
    }
    public function getAddressDetail()
    {
        return $this->addressDetail;
    }
    public function getWardId()
    {
        return $this->wardId;
    }
    public function getWardName()
    {
        return $this->wardName;
    }
    public function getDistrictName()
    {
        return $this->districtName;
    }
    public function getProvinceName()
    {
        return $this->provinceName;
    }
    public function getTotalProducts()
    {
        return $this->totalProducts;
    }
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    public function getFullAddress()
    {
        $parts = array_filter([
            $this->addressDetail,
            $this->wardName,
            $this->districtName,
            $this->provinceName
        ]);
        return implode(', ', $parts);
    }

    public function toArray()
    {
        return [
            'supplier_id' => $this->supplierId,
            'supplier_name' => $this->supplierName,
            'phone' => $this->phone,
            'Email' => $this->email,
            'address_id' => $this->addressId,
            'address_detail' => $this->addressDetail,
            'ward_id' => $this->wardId,
            'ward_name' => $this->wardName,
            'district_name' => $this->districtName,
            'province_name' => $this->provinceName,
            'TotalProducts' => $this->totalProducts,
            'TotalAmount' => $this->totalAmount
        ];
    }

    /**
     * Xác thực dữ liệu nhà cung cấp
     * @param bool $isUpdate true nếu đang cập nhật, false nếu thêm mới
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate($isUpdate = false, $oldData = [])
    {
        $errors = [];

        // 1. Tên nhà cung cấp
        if (empty(trim($this->supplierName))) {
            $errors[] = "Tên nhà cung cấp không được để trống";
        }

        // 2. Số điện thoại
        if (empty(trim($this->phone))) {
            $errors[] = "Số điện thoại không được để trống";
        } elseif (!preg_match('/^(0|\+84)[3|5|7|8|9]\d{8}$/', $this->phone)) {
            $errors[] = "Số điện thoại không hợp lệ! Số điện thoại phải bắt đầu bằng 0 hoặc +84, theo sau là mã mạng (3, 5, 7, 8, 9) và 8 chữ số.";
        }

        // 3. Email (không bắt buộc)
        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ";
        }

        // 4. ĐỊA CHỈ
        if ($isUpdate) {
            // === CẬP NHẬT: Chỉ kiểm tra khi có thay đổi ===
            $oldAddressDetail = $oldData['address_detail'] ?? '';
            $oldWardId = $oldData['ward_id'] ?? 0;

            $newAddressDetail = $this->addressDetail ?? '';
            $newWardId = $this->wardId ?? 0;

            $hasChange = ($newAddressDetail !== $oldAddressDetail) || ($newWardId != $oldWardId);

            if ($hasChange) {
                if (!empty($newAddressDetail) && empty($newWardId)) {
                    $errors[] = "Nếu nhập chi tiết địa chỉ, phải chọn phường/xã";
                }
                if (empty($newAddressDetail) && !empty($newWardId)) {
                    $errors[] = "Nếu chọn phường/xã, phải nhập chi tiết địa chỉ";
                }
            }
        } else {
            // === THÊM MỚI: BẮT BUỘC NHẬP ĐỊA CHỈ ===
            if (empty(trim($this->addressDetail))) {
                $errors[] = "Chi tiết địa chỉ không được để trống";
            }
            if (empty($this->wardId)) {
                $errors[] = "Vui lòng chọn phường/xã";
            }
            // Nếu có nhập thì phải đủ (phòng trường hợp JS bị bypass)
            if (!empty($this->addressDetail) && empty($this->wardId)) {
                $errors[] = "Nếu nhập chi tiết địa chỉ, phải chọn phường/xã";
            }
            if (empty($this->addressDetail) && !empty($this->wardId)) {
                $errors[] = "Nếu chọn phường/xã, phải nhập chi tiết địa chỉ";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
