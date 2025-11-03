<?php
require_once __DIR__ . '/../Models/OrderDetail.php';

/**
 * OrderDetailRepository
 * Xử lý tất cả database operations liên quan đến OrderDetail
 */
class OrderDetailRepository {
    private $db;

    public function __construct(DatabaseConnection $db) {
        $this->db = $db;
    }

    /**
     * Find order detail by ID
     */
    public function find($detailId) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "SELECT od.*, p.ProductName 
                      FROM orderdetails od
                      LEFT JOIN products p ON od.ProductID = p.ProductID
                      WHERE od.detail_id = ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $detailId);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if (!$row) {
                return null;
            }
            
            return new OrderDetail($row);
        } catch (Exception $e) {
            throw new Exception("Error finding order detail: " . $e->getMessage());
        }
    }

    /**
     * Find all details for an order
     */
    public function findByOrderId($orderId) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "SELECT od.*, p.ProductName 
                      FROM orderdetails od 
                      JOIN products p ON od.ProductID = p.ProductID 
                      WHERE od.OrderID = ?
                      ORDER BY od.ProductID";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $orderId);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $details = [];
            
            while ($row = $result->fetch_assoc()) {
                $details[] = new OrderDetail($row);
            }
            
            $stmt->close();
            
            return $details;
        } catch (Exception $e) {
            throw new Exception("Error fetching order details: " . $e->getMessage());
        }
    }

    /**
     * Create new order detail
     */
    public function create(OrderDetail $detail) {
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
    public function update(OrderDetail $detail) {
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
            
            $stmt->bind_param("ddii", $quantity, $unitPrice, $totalPrice, $detailId);
            
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
    public function delete($detailId) {
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
    public function deleteByOrderId($orderId) {
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
}
?>
