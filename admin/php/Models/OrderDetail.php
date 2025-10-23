<?php
/**
 * OrderDetail Model
 * Đại diện cho chi tiết sản phẩm trong đơn hàng
 */
class OrderDetail {
    private $detailId;
    private $orderId;
    private $productId;
    private $quantity;
    private $unitPrice;
    private $totalPrice;
    private $productName;

    public function __construct($data = []) {
        $this->detailId = $data['detail_id'] ?? null;
        $this->orderId = $data['OrderID'] ?? $data['order_id'] ?? null;
        $this->productId = $data['ProductID'] ?? $data['product_id'] ?? null;
        $this->quantity = $data['Quantity'] ?? $data['quantity'] ?? 0;
        $this->unitPrice = $data['UnitPrice'] ?? $data['unit_price'] ?? 0;
        $this->totalPrice = $data['TotalPrice'] ?? $data['total_price'] ?? 0;
        $this->productName = $data['ProductName'] ?? $data['product_name'] ?? null;
    }

    // Getters
    public function getDetailId() { return $this->detailId; }
    public function getOrderId() { return $this->orderId; }
    public function getProductId() { return $this->productId; }
    public function getQuantity() { return $this->quantity; }
    public function getUnitPrice() { return $this->unitPrice; }
    public function getTotalPrice() { return $this->totalPrice; }
    public function getProductName() { return $this->productName; }

    // Setters
    public function setDetailId($detailId) { $this->detailId = $detailId; }
    public function setOrderId($orderId) { $this->orderId = $orderId; }
    public function setProductId($productId) { $this->productId = $productId; }
    public function setQuantity($quantity) { $this->quantity = intval($quantity); }
    public function setUnitPrice($unitPrice) { $this->unitPrice = floatval($unitPrice); }
    public function setTotalPrice($totalPrice) { $this->totalPrice = floatval($totalPrice); }
    public function setProductName($productName) { $this->productName = $productName; }

    /**
     * Calculate total price
     */
    public function calculateTotalPrice() {
        $this->totalPrice = $this->quantity * $this->unitPrice;
        return $this->totalPrice;
    }

    /**
     * Validate order detail
     */
    public function validate() {
        if (empty($this->productId) || $this->productId <= 0) {
            throw new Exception('Sản phẩm không hợp lệ');
        }
        if (empty($this->quantity) || $this->quantity <= 0) {
            throw new Exception('Số lượng phải lớn hơn 0');
        }
        if (empty($this->unitPrice) || $this->unitPrice <= 0) {
            throw new Exception('Giá sản phẩm phải lớn hơn 0');
        }
        return true;
    }

    /**
     * Convert to array
     */
    public function toArray() {
        return [
            'detail_id' => $this->detailId,
            'OrderID' => $this->orderId,
            'ProductID' => $this->productId,
            'Quantity' => $this->quantity,
            'UnitPrice' => $this->unitPrice,
            'TotalPrice' => $this->totalPrice,
            'ProductName' => $this->productName
        ];
    }
}
?>
