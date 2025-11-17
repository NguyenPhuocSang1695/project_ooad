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

    public function create($importDate, $totalAmount, $note, $products)
    {
        try {
            // Validate
            $this->setImportDate($importDate)
                ->setTotalAmount($totalAmount)
                ->setNote($note)
                ->setProducts($products);

            $validation = $this->isValid();
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            $this->connection->begin_transaction();

            // Insert receipt (KHÔNG có supplier_id)
            $sql = "INSERT INTO import_receipt (import_date, total_amount, note)
                    VALUES (?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            if (!$stmt) throw new Exception($this->connection->error);

            $note = $note ?? "";
            $stmt->bind_param("sds", $importDate, $totalAmount, $note);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $receiptId = $stmt->insert_id;
            $this->setReceiptId($receiptId);

            // Insert details + stock + mapping table
            foreach ($products as $product) {

                // Lấy supplier_id từ bảng products
                $sqlSupp = "SELECT supplier_id FROM products WHERE ProductID = ?";
                $stmtSupp = $this->connection->prepare($sqlSupp);
                $stmtSupp->bind_param("i", $product['product_id']);
                $stmtSupp->execute();
                $resSupp = $stmtSupp->get_result();
                $rowSupp = $resSupp->fetch_assoc();

                $supplierId = $rowSupp['supplier_id'] ?? null;

                if (!$supplierId) {
                    throw new Exception("Không tìm thấy supplier của sản phẩm ID: " . $product['product_id']);
                }

                // Insert mapping receipt - supplier - product
                $sqlLink = "INSERT INTO import_receipt_product_supplier
                    (import_receipt_id, supplier_id, ProductID)
                    VALUES (?, ?, ?)";

                $stmtLink = $this->connection->prepare($sqlLink);
                $stmtLink->bind_param("iii", $receiptId, $supplierId, $product['product_id']);
                $stmtLink->execute();

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

    public function update($receiptId, $importDate, $totalAmount, $note, $products)
    {
        try {
            // Validate
            $this->setReceiptId($receiptId)
                ->setImportDate($importDate)
                ->setTotalAmount($totalAmount)
                ->setNote($note)
                ->setProducts($products);

            $validation = $this->isValid();
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            $this->connection->begin_transaction();

            // Lấy chi tiết cũ để trừ kho
            $oldDetails = $this->getDetails($receiptId);
            foreach ($oldDetails as $detail) {
                $this->updateStock($detail['product_id'], $detail['quantity'], 'subtract');
            }

            // Xóa chi tiết cũ + bảng trung gian
            $this->deleteDetails($receiptId);
            $this->deleteLinks($receiptId);

            // UPDATE receipt
            $sql = "UPDATE import_receipt 
                    SET import_date = ?, total_amount = ?, note = ?
                    WHERE receipt_id = ?";

            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("sdsi", $importDate, $totalAmount, $note, $receiptId);

            if (!$stmt->execute()) throw new Exception($stmt->error);

            // Thêm chi tiết mới + mapping
            foreach ($products as $product) {

                // Lấy supplier_id từ bảng products
                $sqlSupp = "SELECT supplier_id FROM products WHERE ProductID = ?";
                $stmtSupp = $this->connection->prepare($sqlSupp);
                $stmtSupp->bind_param("i", $product['product_id']);
                $stmtSupp->execute();
                $resSupp = $stmtSupp->get_result();
                $rowSupp = $resSupp->fetch_assoc();

                $supplierId = $rowSupp['supplier_id'] ?? null;

                if (!$supplierId) {
                    throw new Exception("Không tìm thấy supplier của sản phẩm ID: " . $product['product_id']);
                }

                // Insert mapping receipt - supplier - product
                $sqlLink = "INSERT INTO import_receipt_product_supplier
                    (import_receipt_id, supplier_id, ProductID)
                    VALUES (?, ?, ?)";

                $stmtLink = $this->connection->prepare($sqlLink);
                $stmtLink->bind_param("iii", $receiptId, $supplierId, $product['product_id']);
                $stmtLink->execute();

                // detail
                $this->createDetail($receiptId, $product);

                // update stock
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
}
