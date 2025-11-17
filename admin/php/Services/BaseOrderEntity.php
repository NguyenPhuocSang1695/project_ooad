<?php
/**
 * BaseOrderEntity
 * Class cha chứa các method chung cho Order và OrderDetail
 * Cung cấp các utility methods dùng chung cho OrderManager
 */
abstract class BaseOrderEntity {
    protected $db;

    public function __construct(DatabaseConnection $db) {
        $this->db = $db;
    }

    /**
     * Get database connection
     */
    protected function getConnection() {
        $conn = $this->db->getConnection();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        return $conn;
    }

    /**
     * Format error message
     */
    protected function formatError($operation, $message) {
        return "Error during $operation: " . $message;
    }

    /**
     * Execute prepared statement safely
     */
    protected function executePrepared($query, $types, $params) {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            return $stmt;
        } catch (Exception $e) {
            throw new Exception($this->formatError("executePrepared", $e->getMessage()));
        }
    }

    /**
     * Get query result as array
     */
    protected function getResultArray($stmt) {
        try {
            $result = $stmt->get_result();
            $rows = [];
            
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            
            return $rows;
        } catch (Exception $e) {
            throw new Exception($this->formatError("getResultArray", $e->getMessage()));
        }
    }

    /**
     * Get single row result
     */
    protected function getSingleResult($stmt) {
        try {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row;
        } catch (Exception $e) {
            throw new Exception($this->formatError("getSingleResult", $e->getMessage()));
        }
    }

    /**
     * Validate data array
     */
    protected function validateData($data, $requiredFields = []) {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: " . $field);
            }
        }
        return true;
    }

    /**
     * Convert array to model instance
     */
    abstract public function mapToModel($data);

    /**
     * Get last insert ID
     */
    protected function getLastInsertId() {
        return $this->db->getConnection()->insert_id;
    }

    /**
     * Begin transaction
     */
    protected function beginTransaction() {
        $conn = $this->getConnection();
        $conn->begin_transaction();
    }

    /**
     * Commit transaction
     */
    protected function commitTransaction() {
        $conn = $this->getConnection();
        $conn->commit();
    }

    /**
     * Rollback transaction
     */
    protected function rollbackTransaction() {
        try {
            $conn = $this->getConnection();
            $conn->rollback();
        } catch (Exception $e) {
            // Silently fail if rollback fails
        }
    }

    /**
     * Close statement
     */
    protected function closeStatement($stmt) {
        if ($stmt) {
            $stmt->close();
        }
    }

    /**
     * Bind parameters safely
     */
    protected function bindParams($stmt, $types, $params) {
        try {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception($this->formatError("bindParams", $e->getMessage()));
        }
    }
}
?>
