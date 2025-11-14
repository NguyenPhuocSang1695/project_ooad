<?php

/**
 * File: classes/ImportReceiptManager.php
 * Class ImportReceiptManager - Quản lý các thao tác với phiếu nhập
 */

require_once 'ImportReceipt.php';

class ImportReceiptManager extends ImportReceipt
{
    private $connection;

    /**
     * Constructor
     */
    public function __construct($connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    // ==================== CRUD OPERATIONS ====================

    /**
     * Thêm phiếu nhập mới
     */
    public function create($importDate, $totalAmount, $note, $supplierId, $products)
    {
        try {
            // Validate dữ liệu
            $this->setImportDate($importDate)
                ->setTotalAmount($totalAmount)
                ->setNote($note)
                ->setSupplierId($supplierId)
                ->setProducts($products);

            $validation = $this->isValid();
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            // Bắt đầu transaction
            $this->connection->begin_transaction();

            // Thêm phiếu nhập
            $sql = "INSERT INTO import_receipt (import_date, total_amount, note, supplier_id) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
                throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $this->connection->error);
            }

            $stmt->bind_param("sdsi", $importDate, $totalAmount, $note, $supplierId);

            if (!$stmt->execute()) {
                throw new Exception('Lỗi khi thêm phiếu nhập: ' . $stmt->error);
            }

            $receiptId = $stmt->insert_id;
            $this->setReceiptId($receiptId);

            // Thêm chi tiết phiếu nhập và cập nhật tồn kho
            foreach ($products as $product) {
                $this->createDetail($receiptId, $product);
                $this->updateStock($product['product_id'], $product['quantity'], 'add');
            }

            // Commit transaction
            $this->connection->commit();

            return [
                'success' => true,
                'receipt_id' => $receiptId,
                'message' => 'Thêm phiếu nhập thành công!'
            ];
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            if ($this->connection->connect_errno === 0) {
                $this->connection->rollback();
            }
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cập nhật phiếu nhập
     */
    public function update($receiptId, $importDate, $totalAmount, $note, $supplierId, $products)
    {
        try {
            // Validate
            $this->setReceiptId($receiptId)
                ->setImportDate($importDate)
                ->setTotalAmount($totalAmount)
                ->setNote($note)
                ->setSupplierId($supplierId)
                ->setProducts($products);

            $validation = $this->isValid();
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            $this->connection->begin_transaction();

            // Lấy thông tin cũ để trừ tồn kho
            $oldDetails = $this->getDetails($receiptId);
            foreach ($oldDetails as $detail) {
                $this->updateStock($detail['product_id'], $detail['quantity'], 'subtract');
            }

            // Xóa chi tiết cũ
            $this->deleteDetails($receiptId);

            // Cập nhật thông tin phiếu nhập
            $sql = "UPDATE import_receipt 
                    SET import_date = ?, total_amount = ?, note = ?, supplier_id = ? 
                    WHERE receipt_id = ?";
            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
                throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $this->connection->error);
            }

            $stmt->bind_param("sdsii", $importDate, $totalAmount, $note, $supplierId, $receiptId);

            if (!$stmt->execute()) {
                throw new Exception('Lỗi khi cập nhật phiếu nhập: ' . $stmt->error);
            }

            // Thêm chi tiết mới và cập nhật tồn kho
            foreach ($products as $product) {
                $this->createDetail($receiptId, $product);
                $this->updateStock($product['product_id'], $product['quantity'], 'add');
            }

            $this->connection->commit();

            return [
                'success' => true,
                'receipt_id' => $receiptId,
                'message' => 'Cập nhật phiếu nhập thành công!'
            ];
        } catch (Exception $e) {
            if ($this->connection->connect_errno === 0) {
                $this->connection->rollback();
            }
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Xóa phiếu nhập
     */
    public function delete($receiptId)
    {
        try {
            $this->connection->begin_transaction();

            // Lấy thông tin chi tiết để trừ lại số lượng
            $details = $this->getDetails($receiptId);
            foreach ($details as $detail) {
                $this->updateStock($detail['product_id'], $detail['quantity'], 'subtract');
            }

            // Xóa chi tiết
            $this->deleteDetails($receiptId);

            // Xóa phiếu nhập
            $sql = "DELETE FROM import_receipt WHERE receipt_id = ?";
            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
                throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $this->connection->error);
            }

            $stmt->bind_param("i", $receiptId);

            if (!$stmt->execute()) {
                throw new Exception('Lỗi khi xóa phiếu nhập: ' . $stmt->error);
            }

            $this->connection->commit();

            return [
                'success' => true,
                'message' => 'Xóa phiếu nhập thành công!'
            ];
        } catch (Exception $e) {
            if ($this->connection->connect_errno === 0) {
                $this->connection->rollback();
            }
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // ==================== DETAIL OPERATIONS ====================

    /**
     * Thêm chi tiết phiếu nhập
     */
    private function createDetail($receiptId, $product)
    {
        $productId = $product['product_id'];
        $quantity = $product['quantity'];
        $importPrice = $product['import_price'];
        $subtotal = $quantity * $importPrice;

        $sql = "INSERT INTO import_receipt_detail (receipt_id, product_id, quantity, import_price, subtotal) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $this->connection->error);
        }

        $stmt->bind_param("iiidd", $receiptId, $productId, $quantity, $importPrice, $subtotal);

        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thêm chi tiết phiếu nhập: ' . $stmt->error);
        }
    }

    /**
     * Lấy chi tiết phiếu nhập
     */
    public function getDetails($receiptId)
    {
        $sql = "SELECT ird.*, p.ProductName as product_name 
                FROM import_receipt_detail ird
                LEFT JOIN products p ON ird.product_id = p.ProductID
                WHERE ird.receipt_id = ?";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $this->connection->error);
        }

        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
        $result = $stmt->get_result();

        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        return $details;
    }

    /**
     * Xóa chi tiết phiếu nhập
     */
    private function deleteDetails($receiptId)
    {
        $sql = "DELETE FROM import_receipt_detail WHERE receipt_id = ?";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $this->connection->error);
        }

        $stmt->bind_param("i", $receiptId);

        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi xóa chi tiết phiếu nhập: ' . $stmt->error);
        }
    }

    // ==================== STOCK OPERATIONS ====================

    /**
     * Cập nhật tồn kho
     */
    private function updateStock($productId, $quantity, $action = 'add')
    {
        $operator = ($action === 'add') ? '+' : '-';
        $sql = "UPDATE products 
                SET quantity_in_stock = quantity_in_stock $operator ? 
                WHERE ProductID = ?";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $this->connection->error);
        }

        $stmt->bind_param("ii", $quantity, $productId);

        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi cập nhật tồn kho: ' . $stmt->error);
        }
    }

    // ==================== READ OPERATIONS ====================

    /**
     * Lấy danh sách tất cả phiếu nhập
     */
    public function getAll($orderBy = 'import_date', $order = 'DESC')
    {
        $sql = "SELECT ir.*, s.supplier_name 
                FROM import_receipt ir 
                LEFT JOIN suppliers s ON ir.supplier_id = s.supplier_id 
                ORDER BY ir.$orderBy $order";

        $result = $this->connection->query($sql);

        $receipts = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $receipts[] = $row;
            }
        }
        return $receipts;
    }

    /**
     * Lấy thông tin một phiếu nhập theo ID
     */
    public function getById($receiptId)
    {
        $sql = "SELECT ir.*, s.supplier_name 
                FROM import_receipt ir 
                LEFT JOIN suppliers s ON ir.supplier_id = s.supplier_id 
                WHERE ir.receipt_id = ?";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Lấy phiếu nhập với chi tiết đầy đủ
     */
    public function getFullDetails($receiptId)
    {
        $receipt = $this->getById($receiptId);

        if (!$receipt) {
            return null;
        }

        $receipt['details'] = $this->getDetails($receiptId);
        return $receipt;
    }

    /**
     * Tìm kiếm phiếu nhập
     */
    public function search($keyword)
    {
        $keyword = "%$keyword%";
        $sql = "SELECT ir.*, s.supplier_name 
                FROM import_receipt ir 
                LEFT JOIN suppliers s ON ir.supplier_id = s.supplier_id 
                WHERE ir.receipt_id LIKE ? 
                   OR ir.note LIKE ? 
                   OR s.supplier_name LIKE ?
                ORDER BY ir.import_date DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sss", $keyword, $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();

        $receipts = [];
        while ($row = $result->fetch_assoc()) {
            $receipts[] = $row;
        }
        return $receipts;
    }

    /**
     * Lọc phiếu nhập theo tháng
     */
    public function filterByMonth($month, $year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $sql = "SELECT ir.*, s.supplier_name 
                FROM import_receipt ir 
                LEFT JOIN suppliers s ON ir.supplier_id = s.supplier_id 
                WHERE MONTH(ir.import_date) = ? AND YEAR(ir.import_date) = ?
                ORDER BY ir.import_date DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ii", $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $receipts = [];
        while ($row = $result->fetch_assoc()) {
            $receipts[] = $row;
        }
        return $receipts;
    }

    // ==================== STATISTICS ====================

    /**
     * Tính tổng giá trị nhập hàng
     */
    public function getTotalValue($startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $sql = "SELECT SUM(total_amount) as grand_total 
                    FROM import_receipt 
                    WHERE import_date BETWEEN ? AND ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT SUM(total_amount) as grand_total FROM import_receipt";
            $result = $this->connection->query($sql);
        }

        $row = $result->fetch_assoc();
        return $row['grand_total'] ?? 0;
    }

    /**
     * Đếm số lượng phiếu nhập
     */
    public function count($startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $sql = "SELECT COUNT(*) as total 
                    FROM import_receipt 
                    WHERE import_date BETWEEN ? AND ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT COUNT(*) as total FROM import_receipt";
            $result = $this->connection->query($sql);
        }

        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    // ==================== HELPER METHODS ====================

    /**
     * Lấy danh sách nhà cung cấp
     */
    public function getSuppliers()
    {
        $sql = "SELECT supplier_id, supplier_name, phone, email 
                FROM suppliers 
                ORDER BY supplier_name";
        $result = $this->connection->query($sql);

        $suppliers = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $suppliers[] = $row;
            }
        }
        return $suppliers;
    }

    /**
     * Lấy danh sách sản phẩm
     */
    public function getProducts()
    {
        $sql = "SELECT ProductID, ProductName, Price, quantity_in_stock 
                FROM products 
                WHERE Status = 'appear' 
                ORDER BY ProductName";
        $result = $this->connection->query($sql);

        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }

    /**
     * Kiểm tra phiếu nhập có tồn tại không
     */
    public function exists($receiptId)
    {
        $sql = "SELECT COUNT(*) as count FROM import_receipt WHERE receipt_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
}
