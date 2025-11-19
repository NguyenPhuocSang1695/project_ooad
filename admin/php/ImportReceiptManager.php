<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'ImportReceipt.php';

class ImportReceiptManager extends ImportReceipt
{
    private $connection;

    public function __construct($connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    // ==================== CREATE ====================

    public function create($importDate, $totalAmount, $note, $products, $supplierId)
    {
        try {
            // Validate
            $this->setSupplierId($supplierId)
                ->setImportDate($importDate)
                ->setTotalAmount($totalAmount)
                ->setNote($note)
                ->setProducts($products);

            $validation = $this->isValid(true);
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            $this->connection->begin_transaction();

            // Insert receipt (KHÔNG có supplier_id)
            $sql = "INSERT INTO import_receipt (import_date, total_amount, note, supplier_id)
                    VALUES (?, ?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            if (!$stmt) throw new Exception($this->connection->error);

            $note = $note ?? "";
            $stmt->bind_param("sdsi", $importDate, $totalAmount, $note, $supplierId);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $receiptId = $stmt->insert_id;
            $this->setReceiptId($receiptId);

            // Insert details + stock + mapping table
            foreach ($products as $product) {
                // Insert receipt detail
                $this->createDetail($receiptId, $product);

                // Update stock
                $this->updateStock($product['product_id'], $product['quantity'], 'add');
            }

            $this->connection->commit();

            return [
                'success' => true,
                'receipt_id' => $receiptId,
                'message' => 'Thêm phiếu nhập thành công!'
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

    // ==================== UPDATE ====================

    /**
     * Cập nhật phiếu nhập (KHÔNG dùng bảng import_receipt_product_supplier)
     */
    public function update($receiptId, $importDate, $totalAmount, $note, $products)
    {
        try {
            // Validate (không check supplier khi update)
            $this->setReceiptId($receiptId)
                ->setImportDate($importDate)
                ->setTotalAmount($totalAmount)
                ->setNote($note)
                ->setProducts($products);

            $validation = $this->isValid(false); // false = không check supplier
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            $this->connection->begin_transaction();

            // Lấy chi tiết cũ để trừ kho
            $oldDetails = $this->getDetails($receiptId);
            foreach ($oldDetails as $detail) {
                $this->updateStock($detail['product_id'], $detail['quantity'], 'subtract');
            }

            // Xóa chi tiết cũ
            $this->deleteDetails($receiptId);

            // UPDATE receipt (không update supplier_id)
            $sql = "UPDATE import_receipt 
                    SET import_date = ?, total_amount = ?, note = ?
                    WHERE receipt_id = ?";

            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("sdsi", $importDate, $totalAmount, $note, $receiptId);

            if (!$stmt->execute()) {
                throw new Exception("Lỗi update phiếu nhập: " . $stmt->error);
            }

            // Thêm chi tiết mới
            foreach ($products as $product) {
                // Validate product data
                if (empty($product['product_id']) || empty($product['quantity']) || !isset($product['import_price'])) {
                    throw new Exception("Thông tin sản phẩm không hợp lệ!");
                }

                // Insert detail
                $this->createDetail($receiptId, $product);

                // Update stock (cộng số lượng mới vào kho)
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

    // ==================== DELETE ====================

    public function delete($receiptId)
    {
        try {
            $this->connection->begin_transaction();

            // Lấy chi tiết để trừ kho
            $details = $this->getDetails($receiptId);
            foreach ($details as $detail) {
                $this->updateStock($detail['product_id'], $detail['quantity'], 'subtract');
            }

            // Xóa chi tiết
            $this->deleteDetails($receiptId);

            // Xóa mapping
            $this->deleteLinks($receiptId);

            // Xóa phiếu nhập
            $sql = "DELETE FROM import_receipt WHERE receipt_id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $receiptId);
            $stmt->execute();

            $this->connection->commit();

            return [
                'success' => true,
                'message' => 'Xóa phiếu nhập thành công!'
            ];
        } catch (Exception $e) {
            $this->connection->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ==================== INTERNAL ====================

    private function deleteLinks($receiptId)
    {
        $sql = "DELETE FROM import_receipt_product_supplier WHERE import_receipt_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
    }

    private function createDetail($receiptId, $product)
    {
        $productId = $product['product_id'];
        $quantity = $product['quantity'];
        $importPrice = $product['import_price'];
        $subtotal = $quantity * $importPrice;

        $sql = "INSERT INTO import_receipt_detail (receipt_id, product_id, quantity, import_price, subtotal)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iiidd", $receiptId, $productId, $quantity, $importPrice, $subtotal);
        $stmt->execute();
    }

    private function deleteDetails($receiptId)
    {
        $sql = "DELETE FROM import_receipt_detail WHERE receipt_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
    }

    private function updateStock($productId, $quantity, $action)
    {
        $operator = ($action === 'add') ? '+' : '-';

        $sql = "UPDATE products 
                SET quantity_in_stock = quantity_in_stock $operator ? 
                WHERE ProductID = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ii", $quantity, $productId);
        $stmt->execute();
    }

    // ==================== READ ====================

    public function getAll($orderBy = 'import_date', $order = 'DESC')
    {
        $sql = "SELECT * FROM import_receipt ORDER BY $orderBy $order";

        $result = $this->connection->query($sql);

        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    }

    public function getById($receiptId)
    {
        $sql = "SELECT * FROM import_receipt WHERE receipt_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getDetails($receiptId)
    {
        $sql = "SELECT ird.*, p.ProductName AS product_name
                FROM import_receipt_detail ird
                LEFT JOIN products p ON ird.product_id = p.ProductID
                WHERE ird.receipt_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $receiptId);
        $stmt->execute();

        $result = $stmt->get_result();

        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        return $details;
    }

    public function getFullDetails($receiptId)
    {
        $receipt = $this->getById($receiptId);
        if (!$receipt) return null;

        $receipt['details'] = $this->getDetails($receiptId);

        return $receipt;
    }

    public function getTotalValue()
    {
        $sql = "SELECT SUM(total_amount) AS total FROM import_receipt";
        $result = $this->connection->query($sql);
        return $result->fetch_assoc()['total'] ?? 0;
    }

    public function count()
    {
        $sql = "SELECT COUNT(*) AS count FROM import_receipt";
        $result = $this->connection->query($sql);
        return $result->fetch_assoc()['count'] ?? 0;
    }

    public function getProductsBySupplier($supplierId)
    {
        $sql = "SELECT ProductID, ProductName, Price 
            FROM products 
            WHERE supplier_id = ?
            ORDER BY ProductName ASC";

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("i", $supplierId);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        return $products;
    }
}
