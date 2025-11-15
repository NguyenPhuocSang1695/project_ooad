<?php
/**
 * Order Model
 * Đại diện cho một đơn hàng
 */
class Order {
    private $orderId;
    private $userId;
    private $username;
    private $customerName;
    private $phone;
    private $paymentMethod;
   // private $status;
    private $addressId;
    private $dateGeneration;
    private $totalAmount;
    private $voucherId;
    private $deliveryType;
    
    // Address details (from JOIN)
    private $addressDetail;
    private $wardName;
    private $districtName;
    private $provinceName;

    public function __construct($data = []) {
        $this->orderId = $data['OrderID'] ?? $data['order_id'] ?? null;
        $this->userId = $data['user_id'] ?? null;
        $this->username = $data['Username'] ?? $data['username'] ?? null;
        $this->customerName = $data['CustomerName'] ?? $data['customer_name'] ?? null;
        $this->phone = $data['Phone'] ?? $data['phone'] ?? null;
        $this->paymentMethod = $data['PaymentMethod'] ?? $data['payment_method'] ?? null;
      //  $this->status = $data['Status'] ?? $data['status'] ?? 'execute';
        $this->addressId = $data['address_id'] ?? null;
        $this->dateGeneration = $data['DateGeneration'] ?? $data['date_generation'] ?? null;
        $this->totalAmount = $data['TotalAmount'] ?? $data['total_amount'] ?? 0;
        $this->voucherId = $data['voucher_id'] ?? null;
        $this->deliveryType = $data['delivery_type'] ?? 'pickup';
        
        // Address details
        $this->addressDetail = $data['address_detail'] ?? null;
        $this->wardName = $data['ward_name'] ?? null;
        $this->districtName = $data['district_name'] ?? null;
        $this->provinceName = $data['province_name'] ?? null;
    }

    // Getters
    public function getOrderId() { return $this->orderId; }
    public function getUserId() { return $this->userId; }
    public function getUsername() { return $this->username; }
    public function getCustomerName() { return $this->customerName; }
    public function getPhone() { return $this->phone; }
    public function getPaymentMethod() { return $this->paymentMethod; }
   // public function getStatus() { return $this->status; }
    public function getAddressId() { return $this->addressId; }
    public function getDateGeneration() { return $this->dateGeneration; }
    public function getTotalAmount() { return $this->totalAmount; }
    public function getVoucherId() { return $this->voucherId; }
    public function getDeliveryType() { return $this->deliveryType; }
    
    // Address detail getters
    public function getAddressDetail() { return $this->addressDetail; }
    public function getWardName() { return $this->wardName; }
    public function getDistrictName() { return $this->districtName; }
    public function getProvinceName() { return $this->provinceName; }

    // Setters
    public function setOrderId($orderId) { $this->orderId = $orderId; }
    public function setUserId($userId) { $this->userId = $userId; }
    public function setUsername($username) { $this->username = $username; }
    public function setCustomerName($customerName) { $this->customerName = $customerName; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setPaymentMethod($paymentMethod) { $this->paymentMethod = $paymentMethod; }
   // public function setStatus($status) { $this->status = $status; }
    public function setAddressId($addressId) { $this->addressId = $addressId; }
    public function setDateGeneration($dateGeneration) { $this->dateGeneration = $dateGeneration; }
    public function setTotalAmount($totalAmount) { $this->totalAmount = $totalAmount; }
    public function setVoucherId($voucherId) { $this->voucherId = $voucherId; }
    public function setDeliveryType($deliveryType) { $this->deliveryType = $deliveryType; }

    /**
     * Validate order data
     */
    public function validate() {
        // Note: customerName and phone validation is done in add-order.php
        // based on delivery_type, so we don't validate them here
        if (empty($this->paymentMethod)) {
            throw new Exception('Phương thức thanh toán không được để trống');
        }
        if ($this->totalAmount < 0) {
            throw new Exception('Tổng tiền không được âm');
        }
        return true;
    }

    /**
     * Get full delivery address
     */
    public function getFullAddress() {
        $parts = [];
        if (!empty($this->addressDetail)) $parts[] = $this->addressDetail;
        if (!empty($this->wardName)) $parts[] = $this->wardName;
        if (!empty($this->districtName)) $parts[] = $this->districtName;
        if (!empty($this->provinceName)) $parts[] = $this->provinceName;
        return implode(', ', $parts);
    }

    /**
     * Convert to array
     */
    public function toArray() {
        return [
            'OrderID' => $this->orderId,
            'user_id' => $this->userId,
            'Username' => $this->username,
            'CustomerName' => $this->customerName,
            'Phone' => $this->phone,
            'PaymentMethod' => $this->paymentMethod,
          //  'Status' => $this->status,
            'address_id' => $this->addressId,
            'DateGeneration' => $this->dateGeneration,
            'TotalAmount' => $this->totalAmount,
            'voucher_id' => $this->voucherId,
            'delivery_type' => $this->deliveryType,
            'address_detail' => $this->addressDetail,
            'ward_name' => $this->wardName,
            'district_name' => $this->districtName,
            'province_name' => $this->provinceName
        ];
    }

    /**
     * Get status label in Vietnamese
     */
    // public function getStatusLabel() {
    //     $statusMap = [
    //         'execute' => 'Chờ xác nhận',
    //         'confirmed' => 'Đã xác nhận',
    //         'ship' => 'Đang giao',
    //         'success' => 'Hoàn thành',
    //         'fail' => 'Đã hủy'
    //     ];
    //     return $statusMap[$this->status] ?? $this->status;
    // }

    /**
     * Check if status is valid for transition
     */
    // public function canTransitionTo($newStatus) {
    //     $validStatuses = ['execute', 'confirmed', 'ship', 'success', 'fail'];
    //     return in_array($newStatus, $validStatuses);
    // }
}
?>
