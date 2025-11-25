<?php
require_once __DIR__ . '/../Models/Order.php';
require_once __DIR__ . '/../Models/OrderDetail.php';
require_once __DIR__ . '/BaseOrderEntity.php';

/**
 * OrderManager
 * Kế thừa từ BaseOrderEntity
 * Quản lý tất cả business logic và database operations liên quan đến Order và OrderDetail
 */
class OrderManager extends BaseOrderEntity {
    
    /**
     * Map array data to Order model
     */
    public function mapToModel($data) {
        return new Order($data);
    }

    /**
     * Map array data to OrderDetail model
     */
    public function mapDetailToModel($data) {
        return new OrderDetail($data);
    }

    // ============================================
    // ORDER REPOSITORY METHODS
    // ============================================

    /**
     * Find order by ID
     */
    public function findOrder($orderId) {
        try {
            $query = "SELECT o.OrderID, o.DateGeneration, o.TotalAmount, o.CustomerName, o.Phone, o.PaymentMethod, o.voucher_id, o.user_id,
                             a.address_detail, 
                             w.name as ward_name, 
                             d.name as district_name, 
                             p.name as province_name
                      FROM orders o
                      LEFT JOIN address a ON o.address_id = a.address_id
                      LEFT JOIN ward w ON a.ward_id = w.ward_id
                      LEFT JOIN district d ON w.district_id = d.district_id
                      LEFT JOIN province p ON d.province_id = p.province_id
                      WHERE o.OrderID = ?";
            
            $stmt = $this->executePrepared($query, "i", [$orderId]);
            $row = $this->getSingleResult($stmt);
            $this->closeStatement($stmt);
            
            if (!$row) {
                return null;
            }
            
            return $this->mapToModel($row);
        } catch (Exception $e) {
            throw new Exception("Error finding order: " . $e->getMessage());
        }
    }

    /**
     * Find orders with filters and pagination
     */
    public function findOrdersWithFilters($filters = [], $page = 1, $limit = 5) {
        try {
            $conn = $this->db->getConnection();
            if (!$conn) {
                throw new Exception("Database connection failed");
            }
            $offset = ($page - 1) * $limit;
            
            $baseQuery = "FROM orders o LEFT JOIN address a ON o.address_id = a.address_id LEFT JOIN ward w ON a.ward_id = w.ward_id LEFT JOIN district d ON w.district_id = d.district_id LEFT JOIN province p ON d.province_id = p.province_id WHERE 1=1";
            
            $whereConditions = [];
            $params = [];
            $types = "";
            
            // Build WHERE clause
            if (!empty($filters['search'])) {
                if (is_numeric($filters['search'])) {
                    $whereConditions[] = "(o.OrderID = ? OR o.CustomerName LIKE ?)";
                    $params[] = intval($filters['search']);
                    $searchValue = "%" . $filters['search'] . "%";
                    $params[] = $searchValue;
                    $types .= "is";
                } else {
                    $whereConditions[] = "(o.OrderID LIKE ? OR o.CustomerName LIKE ?)";
                    $searchValue = "%" . $filters['search'] . "%";
                    $params[] = $searchValue;
                    $params[] = $searchValue;
                    $types .= "ss";
                }
            }
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "DATE(o.DateGeneration) >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "DATE(o.DateGeneration) <= ?";
                $params[] = $filters['date_to'];
                $types .= "s";
            }
            if (!empty($filters['price_min'])) {
                $whereConditions[] = "o.TotalAmount >= ?";
                $params[] = floatval($filters['price_min']);
                $types .= "d";
            }
            if (!empty($filters['price_max'])) {
                $whereConditions[] = "o.TotalAmount <= ?";
                $params[] = floatval($filters['price_max']);
                $types .= "d";
            }
            if (!empty($filters['voucher_filter'])) {
                if ($filters['voucher_filter'] === 'has_voucher') {
                    $whereConditions[] = "o.voucher_id IS NOT NULL";
                    if (!empty($filters['specific_voucher'])) {
                        $whereConditions[] = "o.voucher_id = ?";
                        $params[] = intval($filters['specific_voucher']);
                        $types .= "i";
                    }
                } elseif ($filters['voucher_filter'] === 'no_voucher') {
                    $whereConditions[] = "o.voucher_id IS NULL";
                }
            }
            
            $whereClause = "";
            if (!empty($whereConditions)) {
                $whereClause = " AND " . implode(" AND ", $whereConditions);
            }
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total " . $baseQuery . $whereClause;
            $stmt = $conn->prepare($countQuery);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $countResult = $stmt->get_result();
            $countRow = $countResult->fetch_assoc();
            $totalRecords = intval($countRow['total']);
            $totalPages = ceil($totalRecords / $limit);
            $stmt->close();
            
            // Get orders with pagination
            $query = "SELECT o.OrderID, o.DateGeneration, o.TotalAmount, o.CustomerName, o.Phone, o.PaymentMethod, o.voucher_id, o.user_id, a.address_detail, w.name as ward_name, d.name as district_name, p.name as province_name " . $baseQuery . $whereClause . " ORDER BY o.DateGeneration DESC LIMIT ? OFFSET ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $limitVal = $limit;
            $offsetVal = $offset;
            $allParams = array_merge($params, [$limitVal, $offsetVal]);
            $allTypes = $types . "ii";
            
            $stmt->bind_param($allTypes, ...$allParams);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $orders = [];
            
            while ($row = $result->fetch_assoc()) {
                $orders[] = new Order($row);
            }
            
            $stmt->close();
            
            return [
                'orders' => $orders,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'per_page' => $limit
            ];
        } catch (Exception $e) {
            throw new Exception("Error fetching orders: " . $e->getMessage());
        }
    }

    /**
     * Create new order
     */
    public function createOrderRecord(Order $order) {
        try {
            $order->validate();
            
            $conn = $this->db->getConnection();
            $query = "INSERT INTO orders (user_id, CustomerName, Phone, PaymentMethod, address_id, voucher_id, DateGeneration, TotalAmount) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $userId = $order->getUserId();
            $customerName = $order->getCustomerName();
            $phone = $order->getPhone();
            $paymentMethod = $order->getPaymentMethod();
            $addressId = $order->getAddressId();
            $voucherId = $order->getVoucherId();
            $totalAmount = $order->getTotalAmount();
           // $status = $order->getStatus();
            
            // Bind: user_id(i), CustomerName(s), Phone(s), PaymentMethod(s), address_id(i), voucher_id(i), TotalAmount(d)
            $stmt->bind_param("isssiid", $userId, $customerName, $phone, $paymentMethod, $addressId, $voucherId, $totalAmount);
            
            if (!$stmt->execute()) {
                throw new Exception("Insert failed: " . $stmt->error);
            }
            
            $orderId = $conn->insert_id;
            $stmt->close();
            
            return $orderId;
        } catch (Exception $e) {
            throw new Exception("Error creating order: " . $e->getMessage());
        }
    }

    /**
     * Update order total amount
     */
    public function updateOrderTotalAmount($orderId, $amount) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "UPDATE orders SET TotalAmount = ? WHERE OrderID = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $amountVal = floatval($amount);
            $stmt->bind_param("di", $amountVal, $orderId);
            
            if (!$stmt->execute()) {
                throw new Exception("Update failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error updating total amount: " . $e->getMessage());
        }
    }

    /**
     * Update order status
     */
    // public function updateOrderStatus($orderId, $status) {
    //     try {
    //         $conn = $this->db->getConnection();
            
    //         $query = "UPDATE orders SET Status = ? WHERE OrderID = ?";
    //         $stmt = $conn->prepare($query);
    //         if (!$stmt) {
    //             throw new Exception("Prepare failed: " . $conn->error);
    //         }
            
    //         $stmt->bind_param("si", $status, $orderId);
            
    //         if (!$stmt->execute()) {
    //             throw new Exception("Update failed: " . $stmt->error);
    //         }
            
    //         $stmt->close();
            
    //         return true;
    //     } catch (Exception $e) {
    //         throw new Exception("Error updating order status: " . $e->getMessage());
    //     }
    // }

    /**
     * Update entire order
     */
    public function updateOrder(Order $order) {
        try {
            $order->validate();
            
            $conn = $this->db->getConnection();
            
            $query = "UPDATE orders SET CustomerName = ?, Phone = ?, PaymentMethod = ?, TotalAmount = ? 
                      WHERE OrderID = ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $orderId = $order->getOrderId();
            $customerName = $order->getCustomerName();
            $phone = $order->getPhone();
            $paymentMethod = $order->getPaymentMethod();
            $totalAmount = $order->getTotalAmount();
            
            $stmt->bind_param("sssdi", $customerName, $phone, $paymentMethod, $totalAmount, $orderId);
            
            if (!$stmt->execute()) {
                throw new Exception("Update failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error updating order: " . $e->getMessage());
        }
    }

    /**
     * Delete order
     */
    public function deleteOrder($orderId) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "DELETE FROM orders WHERE OrderID = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $orderId);
            
            if (!$stmt->execute()) {
                throw new Exception("Delete failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error deleting order: " . $e->getMessage());
        }
    }

    // ============================================
    // ORDER DETAIL REPOSITORY METHODS
    // ============================================

    /**
     * Find order detail by ID
     */
    public function findOrderDetail($detailId) {
        try {
            $query = "SELECT od.*, p.ProductName 
                      FROM orderdetails od
                      LEFT JOIN products p ON od.ProductID = p.ProductID
                      WHERE od.detail_id = ?";
            
            $stmt = $this->executePrepared($query, "i", [$detailId]);
            $row = $this->getSingleResult($stmt);
            $this->closeStatement($stmt);
            
            if (!$row) {
                return null;
            }
            
            return $this->mapDetailToModel($row);
        } catch (Exception $e) {
            throw new Exception("Error finding order detail: " . $e->getMessage());
        }
    }

    /**
     * Find all details for an order
     */
    public function findOrderDetailsByOrderId($orderId) {
        try {
            $query = "SELECT od.*, p.ProductName 
                      FROM orderdetails od 
                      JOIN products p ON od.ProductID = p.ProductID 
                      WHERE od.OrderID = ?
                      ORDER BY od.ProductID";
            
            $stmt = $this->executePrepared($query, "i", [$orderId]);
            $rows = $this->getResultArray($stmt);
            $this->closeStatement($stmt);
            
            $details = [];
            foreach ($rows as $row) {
                $details[] = $this->mapDetailToModel($row);
            }
            
            return $details;
        } catch (Exception $e) {
            throw new Exception("Error fetching order details: " . $e->getMessage());
        }
    }

    /**
     * Create new order detail
     */
    public function createOrderDetail(OrderDetail $detail) {
        try {
            $detail->validate();
            
            $conn = $this->db->getConnection();
            
            $query = "INSERT INTO orderdetails (OrderID, ProductID, Quantity, UnitPrice, TotalPrice) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $orderId = $detail->getOrderId();
            $productId = $detail->getProductId();
            $quantity = $detail->getQuantity();
            $unitPrice = $detail->getUnitPrice();
            $totalPrice = $detail->getTotalPrice();
            
            $stmt->bind_param("iiidd", $orderId, $productId, $quantity, $unitPrice, $totalPrice);
            
            if (!$stmt->execute()) {
                throw new Exception("Insert failed: " . $stmt->error);
            }
            
            $detailId = $conn->insert_id;
            $stmt->close();
            
            return $detailId;
        } catch (Exception $e) {
            throw new Exception("Error creating order detail: " . $e->getMessage());
        }
    }

    /**
     * Update order detail
     */
    public function updateOrderDetail(OrderDetail $detail) {
        try {
            $detail->validate();
            
            $conn = $this->db->getConnection();
            
            $query = "UPDATE orderdetails SET Quantity = ?, UnitPrice = ?, TotalPrice = ? 
                      WHERE detail_id = ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $quantity = $detail->getQuantity();
            $unitPrice = $detail->getUnitPrice();
            $totalPrice = $detail->getTotalPrice();
            $detailId = $detail->getDetailId();
            
            // Bind: Quantity(i), UnitPrice(d), TotalPrice(d), detail_id(i)
            $stmt->bind_param("iddi", $quantity, $unitPrice, $totalPrice, $detailId);
            
            if (!$stmt->execute()) {
                throw new Exception("Update failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error updating order detail: " . $e->getMessage());
        }
    }

    /**
     * Delete order detail
     */
    public function deleteOrderDetail($detailId) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "DELETE FROM orderdetails WHERE detail_id = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $detailId);
            
            if (!$stmt->execute()) {
                throw new Exception("Delete failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error deleting order detail: " . $e->getMessage());
        }
    }

    /**
     * Delete all details for an order
     */
    public function deleteOrderDetailsByOrderId($orderId) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "DELETE FROM orderdetails WHERE OrderID = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $orderId);
            
            if (!$stmt->execute()) {
                throw new Exception("Delete failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error deleting order details: " . $e->getMessage());
        }
    }

    // ============================================
    // BUSINESS LOGIC METHODS
    // ============================================

    /**
     * Create new order with details
     */
    public function createOrder($userId, $customerName, $phone, $paymentMethod, $products, $addressId = null, $voucherId = null) {
        try {
            $conn = $this->db->getConnection();
            
            // Validate voucher if provided
            if ($voucherId) {
                $voucherResult = $this->db->queryPrepared(
                    "SELECT id FROM vouchers WHERE id = ? AND status = 'active'",
                    [$voucherId]
                );
                
                if (!$voucherResult || $voucherResult->num_rows === 0) {
                    throw new Exception("Voucher không tồn tại hoặc không hoạt động (ID: " . $voucherId . ")");
                }
            }
            
            $conn->begin_transaction();

            // Create order
            $order = new Order([
                'user_id' => $userId,
                'CustomerName' => $customerName,
                'Phone' => $phone,
                'PaymentMethod' => $paymentMethod,
                // 'Status' => $status,
                'address_id' => $addressId,
                'voucher_id' => $voucherId,
                'TotalAmount' => 0
            ]);

            $orderId = $this->createOrderRecord($order);

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
                $this->createOrderDetail($detail);
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
            $this->updateOrderTotalAmount($orderId, intval(round($totalAmount)));

            // Trừ số lượng sản phẩm trong database
            foreach ($products as $product) {
                $productId = intval($product['product_id']);
                $quantity = intval($product['quantity']);
                
                // Update product quantity
                $updateQuery = "UPDATE products SET quantity_in_stock = quantity_in_stock - ? WHERE ProductID = ?";
                $updateStmt = $conn->prepare($updateQuery);
                if (!$updateStmt) {
                    throw new Exception("Prepare update failed: " . $conn->error);
                }
                
                $updateStmt->bind_param("ii", $quantity, $productId);
                if (!$updateStmt->execute()) {
                    throw new Exception("Update product quantity failed: " . $updateStmt->error);
                }
                $updateStmt->close();
                
                error_log("[INVENTORY] Product ID: " . $productId . " - Quantity decreased by: " . $quantity);
            }

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
            $order = $this->findOrder($orderId);
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
            return $this->findOrderDetailsByOrderId($orderId);
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
    // public function updateStatus($orderId, $newStatus) {
    //     try {
    //         $order = $this->getOrder($orderId);

    //         if (!$order->canTransitionTo($newStatus)) {
    //             throw new Exception('Trạng thái không hợp lệ: ' . $newStatus);
    //         }

    //         $this->updateOrderStatus($orderId, $newStatus);

    //         return true;
    //     } catch (Exception $e) {
    //         throw new Exception("Error updating order status: " . $e->getMessage());
    //     }
    // }

    /**
     * List orders with filters and pagination
     */
    public function listOrders($filters = [], $page = 1, $limit = 5) {
        try {
            $result = $this->findOrdersWithFilters($filters, $page, $limit);

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

                // Get voucher info if applied
                $voucherInfo = null;
                $voucherId = $order->getVoucherId();
                if ($voucherId) {
                    $voucherResult = $this->db->queryPrepared(
                        "SELECT name, percen_decrease FROM vouchers WHERE id = ?",
                        [$voucherId]
                    );
                    if ($voucherResult && $voucherResult->num_rows > 0) {
                        $voucher = $voucherResult->fetch_assoc();
                        $voucherInfo = [
                            'name' => $voucher['name'],
                            'percen_decrease' => intval($voucher['percen_decrease'])
                        ];
                    }
                }

                $ordersData[] = [
                    'madonhang' => $order->getOrderId(),
                    'ngaytao' => $order->getDateGeneration(),
                    'giatien' => $order->getTotalAmount(),
                    'pthanhtoan' => (!empty($order->getPaymentMethod()) && trim($order->getPaymentMethod()) !== '') ? $order->getPaymentMethod() : 'Không rõ',
                    'receiver_name' => (!empty($order->getCustomerName()) && trim($order->getCustomerName()) !== '') ? $order->getCustomerName() : 'Không rõ',
                    'receiver_phone' => (!empty($order->getPhone()) && trim($order->getPhone()) !== '') ? $order->getPhone() : 'Không rõ',
                    'receiver_address' => $fullAddress,
                    'voucher' => $voucherInfo
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
            $this->createOrderDetail($detail);

            // Update order total
            $details = $this->getOrderDetails($orderId);
            $totalAmount = 0;
            foreach ($details as $d) {
                $totalAmount += $d->getTotalPrice();
            }

            $this->updateOrderTotalAmount($orderId, intval(round($totalAmount)));

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

            $detail = $this->findOrderDetail($detailId);
            if (!$detail) {
                throw new Exception('Chi tiết đơn hàng không tìm thấy');
            }

            $orderId = $detail->getOrderId();

            // Delete detail
            $this->deleteOrderDetail($detailId);

            // Update order total
            $details = $this->getOrderDetails($orderId);
            $totalAmount = 0;
            foreach ($details as $d) {
                $totalAmount += $d->getTotalPrice();
            }

            $this->updateOrderTotalAmount($orderId, intval(round($totalAmount)));

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
    // public function cancelOrder($orderId) {
    //     try {
    //         $order = $this->getOrder($orderId);

    //         // Check if order can be cancelled
    //         $cancelableStatuses = ['execute', 'confirmed'];
    //         if (!in_array($order->getStatus(), $cancelableStatuses)) {
    //             throw new Exception('Không thể hủy đơn hàng ở trạng thái này');
    //         }

    //         $this->updateStatus($orderId, 'fail');

    //         return true;
    //     } catch (Exception $e) {
    //         throw new Exception("Error cancelling order: " . $e->getMessage());
    //     }
    // }
}
?>