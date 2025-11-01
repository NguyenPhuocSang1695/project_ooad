<?php
require_once __DIR__ . '/../Models/Order.php';
require_once __DIR__ . '/../Models/OrderDetail.php';
require_once __DIR__ . '/../Repositories/OrderRepository.php';
require_once __DIR__ . '/../Repositories/OrderDetailRepository.php';

/**
 * OrderService
 * Xử lý business logic liên quan đến Order
 */
class OrderService {
    private $db;
    private $orderRepository;
    private $orderDetailRepository;

    public function __construct(DatabaseConnection $db) {
        $this->db = $db;
        $this->orderRepository = new OrderRepository($db);
        $this->orderDetailRepository = new OrderDetailRepository($db);
    }

    /**
     * Create new order with details
     */
    public function createOrder($userId, $customerName, $phone, $paymentMethod, $products, $addressId = null, $status = 'execute', $voucherId = null) {
        try {
            $conn = $this->db->getConnection();
            $conn->begin_transaction();

            // Create order
            $order = new Order([
                'user_id' => $userId,
                'CustomerName' => $customerName,
                'Phone' => $phone,
                'PaymentMethod' => $paymentMethod,
                'Status' => $status,
                'address_id' => $addressId,
                'voucher_id' => $voucherId,
                'TotalAmount' => 0
            ]);

            $orderId = $this->orderRepository->create($order);

            // Add order details
            $totalAmount = 0;
            foreach ($products as $product) {
                $detail = new OrderDetail([
                    'OrderID' => $orderId,
                    'ProductID' => $product['product_id'],
                    'Quantity' => $product['quantity'],
                    'UnitPrice' => $product['price']
                ]);

                $detail->calculateTotalPrice();
                $this->orderDetailRepository->create($detail);
                $totalAmount += $detail->getTotalPrice();
            }

            // Apply voucher discount if applicable
            if ($voucherId) {
                $voucherResult = $this->db->queryPrepared(
                    "SELECT percen_decrease FROM vouchers WHERE id = ? AND status = 'active'",
                    [$voucherId]
                );
                
                if ($voucherResult && $voucherResult->num_rows > 0) {
                    $voucher = $voucherResult->fetch_assoc();
                    $discount = ($totalAmount * $voucher['percen_decrease']) / 100;
                    $totalAmount = $totalAmount - $discount;
                }
            }

            // Update total amount
            $this->orderRepository->updateTotalAmount($orderId, intval(round($totalAmount)));

            $conn->commit();

            return $orderId;
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollback();
            }
            throw new Exception("Error creating order: " . $e->getMessage());
        }
    }

    /**
     * Get order by ID
     */
    public function getOrder($orderId) {
        try {
            $order = $this->orderRepository->find($orderId);
            if (!$order) {
                throw new Exception('Không tìm thấy đơn hàng với ID: ' . $orderId);
            }
            return $order;
        } catch (Exception $e) {
            throw new Exception("Error getting order: " . $e->getMessage());
        }
    }

    /**
     * Get order details
     */
    public function getOrderDetails($orderId) {
        try {
            return $this->orderDetailRepository->findByOrderId($orderId);
        } catch (Exception $e) {
            throw new Exception("Error getting order details: " . $e->getMessage());
        }
    }

    /**
     * Get order with all details
     */
    public function getOrderWithDetails($orderId) {
        try {
            $order = $this->getOrder($orderId);
            $details = $this->getOrderDetails($orderId);

            return [
                'order' => $order,
                'details' => $details
            ];
        } catch (Exception $e) {
            throw new Exception("Error getting order with details: " . $e->getMessage());
        }
    }

    /**
     * Update order status
     */
    public function updateStatus($orderId, $newStatus) {
        try {
            $order = $this->getOrder($orderId);

            if (!$order->canTransitionTo($newStatus)) {
                throw new Exception('Trạng thái không hợp lệ: ' . $newStatus);
            }

            $this->orderRepository->updateStatus($orderId, $newStatus);

            return true;
        } catch (Exception $e) {
            throw new Exception("Error updating order status: " . $e->getMessage());
        }
    }

    /**
     * List orders with filters and pagination
     */
    public function listOrders($filters = [], $page = 1, $limit = 5) {
        try {
            $result = $this->orderRepository->findWithFilters($filters, $page, $limit);

            // Convert Order objects to array format for API response
            $ordersData = [];
            foreach ($result['orders'] as $order) {
                $addressParts = [];
                if ($order->getAddressDetail()) {
                    $addressParts[] = $order->getAddressDetail();
                }
                if ($order->getWardName()) {
                    $addressParts[] = $order->getWardName();
                }
                if ($order->getDistrictName()) {
                    $addressParts[] = $order->getDistrictName();
                }
                if ($order->getProvinceName()) {
                    $addressParts[] = $order->getProvinceName();
                }
                $fullAddress = implode(', ', $addressParts);

                $ordersData[] = [
                    'madonhang' => $order->getOrderId(),
                    'ngaytao' => $order->getDateGeneration(),
                    'trangthai' => $order->getStatus(),
                    'trangthai_label' => $order->getStatusLabel(),
                    'giatien' => $order->getTotalAmount(),
                    'receiver_name' => $order->getCustomerName(),
                    'receiver_phone' => $order->getPhone(),
                    'receiver_address' => $fullAddress
                ];
            }

            return [
                'orders' => $ordersData,
                'total_pages' => $result['total_pages'],
                'current_page' => $result['current_page'],
                'per_page' => $result['per_page'],
                'total_records' => $result['total_records']
            ];
        } catch (Exception $e) {
            throw new Exception("Error listing orders: " . $e->getMessage());
        }
    }

    /**
     * Add product to existing order
     */
    public function addProductToOrder($orderId, $productId, $quantity, $price) {
        try {
            $conn = $this->db->getConnection();
            $conn->begin_transaction();

            // Create order detail
            $detail = new OrderDetail([
                'OrderID' => $orderId,
                'ProductID' => $productId,
                'Quantity' => $quantity,
                'UnitPrice' => $price
            ]);

            $detail->calculateTotalPrice();
            $this->orderDetailRepository->create($detail);

            // Update order total
            $details = $this->getOrderDetails($orderId);
            $totalAmount = 0;
            foreach ($details as $d) {
                $totalAmount += $d->getTotalPrice();
            }

            $this->orderRepository->updateTotalAmount($orderId, intval(round($totalAmount)));

            $conn->commit();

            return true;
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollback();
            }
            throw new Exception("Error adding product to order: " . $e->getMessage());
        }
    }

    /**
     * Remove product from order
     */
    public function removeProductFromOrder($detailId) {
        try {
            $conn = $this->db->getConnection();
            $conn->begin_transaction();

            $detail = $this->orderDetailRepository->find($detailId);
            if (!$detail) {
                throw new Exception('Chi tiết đơn hàng không tìm thấy');
            }

            $orderId = $detail->getOrderId();

            // Delete detail
            $this->orderDetailRepository->delete($detailId);

            // Update order total
            $details = $this->getOrderDetails($orderId);
            $totalAmount = 0;
            foreach ($details as $d) {
                $totalAmount += $d->getTotalPrice();
            }

            $this->orderRepository->updateTotalAmount($orderId, intval(round($totalAmount)));

            $conn->commit();

            return true;
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollback();
            }
            throw new Exception("Error removing product from order: " . $e->getMessage());
        }
    }

    /**
     * Calculate order total
     */
    public function calculateOrderTotal($orderId) {
        try {
            $details = $this->getOrderDetails($orderId);
            $total = 0;
            foreach ($details as $detail) {
                $total += $detail->getTotalPrice();
            }
            return intval(round($total));
        } catch (Exception $e) {
            throw new Exception("Error calculating total: " . $e->getMessage());
        }
    }

    /**
     * Cancel order (only if not shipped)
     */
    public function cancelOrder($orderId) {
        try {
            $order = $this->getOrder($orderId);

            // Check if order can be cancelled
            $cancelableStatuses = ['execute', 'confirmed'];
            if (!in_array($order->getStatus(), $cancelableStatuses)) {
                throw new Exception('Không thể hủy đơn hàng ở trạng thái này');
            }

            $this->updateStatus($orderId, 'fail');

            return true;
        } catch (Exception $e) {
            throw new Exception("Error cancelling order: " . $e->getMessage());
        }
    }
}
?>
