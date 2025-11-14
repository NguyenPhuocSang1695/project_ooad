<?php
// php/SupplierManager.php

require_once 'Supplier.php';

class SupplierManager
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Thêm nhà cung cấp mới
     */
    public function create($data)
    {
        // Tạo đối tượng Supplier để validate
        $supplier = new Supplier([
            'supplier_name' => $data['supplier_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address_detail' => $data['address_detail'] ?? '',
            'ward_id' => $data['ward_id'] ?? 0
        ]);

        $validation = $supplier->validate(false); // false = thêm mới
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode("<br>", $validation['errors'])];
        }

        $this->conn->begin_transaction();
        try {
            // Bước 1: Tạo địa chỉ trước
            $sqlAddress = "INSERT INTO address (ward_id, address_detail) VALUES (?, ?)";
            $stmtAddress = $this->conn->prepare($sqlAddress);
            $stmtAddress->bind_param("is", $data['ward_id'], $data['address_detail']);
            $stmtAddress->execute();
            $address_id = $this->conn->insert_id;

            // Bước 2: Tạo nhà cung cấp
            $sqlSupplier = "INSERT INTO suppliers (supplier_name, phone, email, address_id) 
                        VALUES (?, ?, ?, ?)";
            $stmtSupplier = $this->conn->prepare($sqlSupplier);
            $stmtSupplier->bind_param(
                "sssi",
                $data['supplier_name'],
                $data['phone'],
                $data['email'],
                $address_id
            );
            $stmtSupplier->execute();

            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Cập nhật thông tin nhà cung cấp
     */
    public function update($supplier_id, $data)
    {
        // === 1. Kiểm tra supplier tồn tại ===
        $existing = $this->getById($supplier_id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Nhà cung cấp không tồn tại'];
        }

        // Gộp dữ liệu (giữ nguyên nếu không nhập)
        $updateData = [
            'supplier_name'  => trim($data['supplier_name'] ?? '') ?: $existing->getSupplierName(),
            'phone'          => trim($data['phone'] ?? '') ?: $existing->getPhone(),
            'email'          => isset($data['email']) && trim($data['email']) !== ''
                ? trim($data['email'])
                : $existing->getEmail(),
            'address_detail' => isset($data['address_detail']) && trim($data['address_detail']) !== ''
                ? trim($data['address_detail'])
                : $existing->getAddressDetail(),
            'ward_id'        => isset($data['ward_id']) && $data['ward_id'] !== ''
                ? intval($data['ward_id'])
                : $existing->getWardId(),
        ];

        // === TRUYỀN DỮ LIỆU CŨ VÀO VALIDATE ===
        $oldData = [
            'address_detail' => $existing->getAddressDetail(),
            'ward_id'        => $existing->getWardId()
        ];

        // Tạo Supplier để validate
        $supplier = new Supplier($updateData);
        $validation = $supplier->validate(true, $oldData); // true + oldData
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => implode("\n", $validation['errors']) // \n để xuống dòng trong alert
            ];
        }

        // DEBUG: Log thông tin existing
        error_log("===== UPDATE DEBUG START =====");
        error_log("Supplier ID: " . $supplier_id);
        error_log("Existing Address ID: " . ($existing->getAddressId() ?? 'NULL'));
        error_log("Existing Ward ID: " . ($existing->getWardId() ?? 'NULL'));
        error_log("Input data: " . json_encode($data, JSON_UNESCAPED_UNICODE));

        // === 2. Gộp dữ liệu ===
        $updateData = [
            'supplier_name'  => trim($data['supplier_name'] ?? '') ?: $existing->getSupplierName(),
            'phone'          => trim($data['phone'] ?? '') ?: $existing->getPhone(),
            'email'          => isset($data['email']) ? trim($data['email']) : $existing->getEmail(),
            'address_detail' => isset($data['address_detail']) ? trim($data['address_detail']) : $existing->getAddressDetail(),
            'ward_id'        => isset($data['ward_id']) && $data['ward_id'] !== '' ? intval($data['ward_id']) : $existing->getWardId(),
        ];

        error_log("Merged data: " . json_encode($updateData, JSON_UNESCAPED_UNICODE));

        // === 3. Validate ===
        if (empty($updateData['supplier_name'])) {
            return ['success' => false, 'message' => 'Tên nhà cung cấp không được để trống'];
        }
        if (empty($updateData['phone'])) {
            return ['success' => false, 'message' => 'Số điện thoại không được để trống'];
        }

        // === 4. Cập nhật trong transaction ===
        $this->conn->begin_transaction();
        try {
            // Cập nhật bảng suppliers
            $sqlSupplier = "UPDATE suppliers SET 
            supplier_name = ?, 
            phone = ?, 
            email = ?
            WHERE supplier_id = ?";

            $stmtSupplier = $this->conn->prepare($sqlSupplier);
            if (!$stmtSupplier) {
                throw new Exception("Prepare supplier failed: " . $this->conn->error);
            }

            $stmtSupplier->bind_param(
                "sssi",
                $updateData['supplier_name'],
                $updateData['phone'],
                $updateData['email'],
                $supplier_id
            );

            if (!$stmtSupplier->execute()) {
                throw new Exception("Execute supplier failed: " . $stmtSupplier->error);
            }

            error_log("Supplier updated. Affected rows: " . $stmtSupplier->affected_rows);

            // Xử lý address
            $addressId = $existing->getAddressId();
            error_log("Processing address. Address ID: " . ($addressId ?? 'NULL'));

            if ($addressId && $addressId > 0) {
                // Đã có address -> UPDATE
                error_log("Updating existing address ID: " . $addressId);
                error_log("New ward_id: " . $updateData['ward_id']);
                error_log("New address_detail: " . $updateData['address_detail']);

                $sqlAddress = "UPDATE address SET 
                ward_id = ?, 
                address_detail = ? 
                WHERE address_id = ?";

                $stmtAddress = $this->conn->prepare($sqlAddress);
                if (!$stmtAddress) {
                    throw new Exception("Prepare address failed: " . $this->conn->error);
                }

                $stmtAddress->bind_param(
                    "isi",
                    $updateData['ward_id'],
                    $updateData['address_detail'],
                    $addressId
                );

                if (!$stmtAddress->execute()) {
                    throw new Exception("Execute address update failed: " . $stmtAddress->error);
                }

                error_log("Address updated successfully. Affected rows: " . $stmtAddress->affected_rows);
            } elseif (!empty($updateData['ward_id']) && !empty($updateData['address_detail'])) {
                // Chưa có address -> INSERT mới
                error_log("Creating new address");

                $sqlAddress = "INSERT INTO address (ward_id, address_detail) VALUES (?, ?)";
                $stmtAddress = $this->conn->prepare($sqlAddress);

                if (!$stmtAddress) {
                    throw new Exception("Prepare insert address failed: " . $this->conn->error);
                }

                $stmtAddress->bind_param(
                    "is",
                    $updateData['ward_id'],
                    $updateData['address_detail']
                );

                if (!$stmtAddress->execute()) {
                    throw new Exception("Execute insert address failed: " . $stmtAddress->error);
                }

                $newAddressId = $this->conn->insert_id;
                error_log("New address created with ID: " . $newAddressId);

                // Update supplier với address_id mới
                $sqlUpdateSupplier = "UPDATE suppliers SET address_id = ? WHERE supplier_id = ?";
                $stmtUpdateSupplier = $this->conn->prepare($sqlUpdateSupplier);
                $stmtUpdateSupplier->bind_param("ii", $newAddressId, $supplier_id);

                if (!$stmtUpdateSupplier->execute()) {
                    throw new Exception("Update supplier address_id failed: " . $stmtUpdateSupplier->error);
                }

                error_log("Supplier linked to new address");
            } else {
                error_log("No address to update (missing ward_id or address_detail)");
            }

            $this->conn->commit();
            error_log("===== UPDATE SUCCESS =====");
            return ['success' => true, 'message' => 'Cập nhật thành công'];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("===== UPDATE FAILED =====");
            error_log("Error: " . $e->getMessage());
            return ['success' => false, 'message' => "Lỗi: " . $e->getMessage()];
        }
    }

    /**
     * Lấy thông tin nhà cung cấp theo ID
     */
    public function getById($supplierId)
    {
        $sql = "
        SELECT 
            s.*,
            a.address_id,
            a.address_detail,
            a.ward_id,
            w.name AS ward_name,
            d.name AS district_name,
            d.district_id,
            pv.name AS province_name,
            pv.province_id,
            COALESCE(prod.total_products, 0) AS TotalProducts,
            COALESCE(prod.total_amount, 0) AS TotalAmount
        FROM suppliers s
        LEFT JOIN address a ON s.address_id = a.address_id
        LEFT JOIN ward w ON a.ward_id = w.ward_id
        LEFT JOIN district d ON w.district_id = d.district_id
        LEFT JOIN province pv ON d.province_id = pv.province_id
        LEFT JOIN (
            SELECT 
                ir.supplier_id,
                COUNT(DISTINCT ird.product_id) AS total_products,
                SUM(ird.quantity * ird.import_price) AS total_amount
            FROM import_receipt ir
            JOIN import_receipt_detail ird ON ir.receipt_id = ird.receipt_id
            GROUP BY ir.supplier_id
        ) prod ON s.supplier_id = prod.supplier_id
        WHERE s.supplier_id = ?
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $supplierId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return new Supplier($row);
        }
        return null;
    }

    public function getAll($searchTerm = '')
    {
        $sql = "
        SELECT 
            s.*,
            a.address_detail,
            w.name AS ward_name,
            d.name AS district_name,
            d.district_id,
            pv.name AS province_name,
            pv.province_id,
            COALESCE(prod.total_products, 0) AS TotalProducts,
            COALESCE(prod.total_amount, 0) AS TotalAmount
        FROM suppliers s
        LEFT JOIN address a ON s.address_id = a.address_id
        LEFT JOIN ward w ON a.ward_id = w.ward_id
        LEFT JOIN district d ON w.district_id = d.district_id
        LEFT JOIN province pv ON d.province_id = pv.province_id
        LEFT JOIN (
            SELECT 
                ir.supplier_id,
                COUNT(DISTINCT ird.product_id) AS total_products,
                SUM(ird.quantity * ird.import_price) AS total_amount
            FROM import_receipt ir
            JOIN import_receipt_detail ird ON ir.receipt_id = ird.receipt_id
            GROUP BY ir.supplier_id
        ) prod ON s.supplier_id = prod.supplier_id
        WHERE s.supplier_name LIKE ? 
           OR s.phone LIKE ? 
           OR s.email LIKE ?
        ORDER BY s.supplier_id DESC
    ";

        $stmt = $this->conn->prepare($sql);
        $searchParam = "%$searchTerm%";
        $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();

        $suppliers = [];
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = new Supplier($row);
        }

        return $suppliers;
    }

    /**
     * Đếm tổng số nhà cung cấp
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) as total FROM suppliers";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Tính tổng giá trị hàng hóa
     */
    public function getTotalValue()
    {
        $sql = "SELECT SUM(p.quantity_in_stock * p.Price) as total_amount FROM products p";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total_amount'] ?? 0;
    }

    /**
     * Lấy danh sách sản phẩm của nhà cung cấp
     */
    public function getProducts($supplierId)
    {
        $sql = "SELECT ProductID, ProductName, quantity_in_stock as Quantity, Price as UnitPrice,
                (quantity_in_stock * Price) as TotalValue
                FROM products 
                WHERE Supplier_id = ? AND Status = 'appear'
                ORDER BY ProductName";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $supplierId);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        $totalAmount = 0;

        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
            $totalAmount += $row['TotalValue'];
        }

        return [
            'products' => $products,
            'totalAmount' => $totalAmount
        ];
    }

    /**
     * Xóa nhà cung cấp
     */
    public function delete($supplierId)
    {
        try {
            $this->conn->begin_transaction();

            // Kiểm tra xem nhà cung cấp có sản phẩm không
            $sql = "SELECT COUNT(*) as total FROM products WHERE Supplier_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $supplierId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['total'] > 0) {
                throw new Exception("Không thể xóa nhà cung cấp đang có sản phẩm!");
            }

            // Lấy address_id
            $supplier = $this->getById($supplierId);
            $addressId = $supplier ? $supplier->getAddressId() : null;

            // Xóa nhà cung cấp
            $sql = "DELETE FROM suppliers WHERE supplier_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $supplierId);

            if (!$stmt->execute()) {
                throw new Exception("Lỗi khi xóa nhà cung cấp: " . $stmt->error);
            }

            // Xóa địa chỉ nếu có
            if ($addressId) {
                $sql = "DELETE FROM address WHERE address_id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("i", $addressId);
                $stmt->execute();
            }

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Xóa nhà cung cấp thành công!'
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy danh sách tỉnh/thành phố
     */
    public function getProvinces()
    {
        $sql = "SELECT province_id, name FROM province ORDER BY name";
        $result = $this->conn->query($sql);

        $provinces = [];
        while ($row = $result->fetch_assoc()) {
            $provinces[] = $row;
        }

        return $provinces;
    }

    /**
     * Lấy danh sách quận/huyện theo tỉnh
     */
    public function getDistricts($provinceId)
    {
        $sql = "SELECT district_id, name FROM district WHERE province_id = ? ORDER BY name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $provinceId);
        $stmt->execute();
        $result = $stmt->get_result();

        $districts = [];
        while ($row = $result->fetch_assoc()) {
            $districts[] = $row;
        }

        return $districts;
    }

    /**
     * Lấy danh sách phường/xã theo quận
     */
    public function getWards($districtId)
    {
        $sql = "SELECT ward_id, name FROM ward WHERE district_id = ? ORDER BY name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $districtId);
        $stmt->execute();
        $result = $stmt->get_result();

        $wards = [];
        while ($row = $result->fetch_assoc()) {
            $wards[] = $row;
        }

        return $wards;
    }
}
