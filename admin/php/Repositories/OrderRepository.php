<?php
require_once __DIR__ . '/../Models/Order.php';

/**
 * OrderRepository
 * Xử lý tất cả database operations liên quan đến Order
 */
class OrderRepository {
    private $db;

    public function __construct(DatabaseConnection $db) {
        $this->db = $db;
    }

    /**
     * Find order by ID
     */
    public function find($orderId) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "SELECT o.OrderID, o.DateGeneration, o.TotalAmount, o.CustomerName, o.Phone, o.PaymentMethod,
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
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $orderId);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if (!$row) {
                return null;
            }
            
            return new Order($row);
        } catch (Exception $e) {
            throw new Exception("Error finding order: " . $e->getMessage());
        }
    }

    /**
     * Find orders with filters and pagination
     */
    public function findWithFilters($filters = [], $page = 1, $limit = 5) {
        try {
            $conn = $this->db->getConnection();
            $offset = ($page - 1) * $limit;
            
            $baseQuery = "FROM orders o 
                          LEFT JOIN address a ON o.address_id = a.address_id 
                          LEFT JOIN ward w ON a.ward_id = w.ward_id 
                          LEFT JOIN district d ON w.district_id = d.district_id 
                          LEFT JOIN province p ON d.province_id = p.province_id 
                          WHERE 1=1";
            
            $whereConditions = [];
            $params = [];
            $types = "";
            
            // Build WHERE clause
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
            if (!empty($filters['province_id'])) {
                $whereConditions[] = "p.province_id = ?";
                $params[] = intval($filters['province_id']);
                $types .= "i";
            }
            if (!empty($filters['district_id'])) {
                $whereConditions[] = "d.district_id = ?";
                $params[] = intval($filters['district_id']);
                $types .= "i";
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
            $query = "SELECT o.OrderID, o.DateGeneration, o.TotalAmount, o.CustomerName, o.Phone, o.PaymentMethod,
                             a.address_detail, w.name as ward_name, d.name as district_name, p.name as province_name " 
                    . $baseQuery . $whereClause . " ORDER BY o.DateGeneration DESC LIMIT ? OFFSET ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            // Combine params with pagination
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
    public function create(Order $order) {
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
            
            $stmt->bind_param("issssii", $userId, $customerName, $phone, $paymentMethod, $addressId, $voucherId, $totalAmount);
            
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
    public function updateTotalAmount($orderId, $amount) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "UPDATE orders SET TotalAmount = ? WHERE OrderID = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $amount, $orderId);
            
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
     * Update entire order
     */
    public function update(Order $order) {
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
            
            $stmt->bind_param("sssii", $customerName, $phone, $paymentMethod, $totalAmount, $orderId);
            
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
    public function delete($orderId) {
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
}
?>
