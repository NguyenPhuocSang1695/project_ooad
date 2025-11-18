<?php
require_once('connect.php');

class ProductManager

{
    private $db;
    private $itemsPerPage = 5; // thêm dòng này
    public function __construct()
    {
        $this->db = new DatabaseConnection();
        $this->db->connect();
    }

    // ✅ Lấy thông tin 1 sản phẩm theo ID
    public function getProductById($id)
    {
        $stmt = $this->db->getConnection()->prepare("
            SELECT p.*, c.Description AS CategoryName
            FROM products p
            LEFT JOIN categories c ON p.CategoryID = c.CategoryID
            WHERE p.ProductID = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        return $product;
    }

    // ✅ API: xử lý request từ client (ví dụ: get-product.php)
    public function handleGetProductRequest()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        $productId = intval($_GET['id']);

        try {
            $product = $this->getProductById($productId);

            if ($product) {
                echo json_encode($product, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['error' => 'Product not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getAllProductsPaginated($offset, $limit)
    {
        $stmt = $this->db->getConnection()->prepare("
            SELECT p.*, c.CategoryName
            FROM products p
            LEFT JOIN categories c ON p.CategoryID = c.CategoryID
            WHERE p.Status IN ('appear', 'hidden')
            ORDER BY p.ProductID DESC
            LIMIT ?, ?
        ");
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        return $products;
    }


    // ✅ Thêm vào trong class ProductController
    public function searchProducts($keyword = '', $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        // Câu truy vấn cơ bản
        $sql = "
        SELECT p.ProductID, p.ProductName, p.Price, p.ImageURL, p.Status,
               c.CategoryName, p.Description
        FROM products p
        LEFT JOIN categories c ON p.CategoryID = c.CategoryID
        WHERE p.Status IN ('appear', 'hidden')
    ";

        $params = [];
        $types = '';

        // Nếu có từ khóa tìm kiếm
        if (!empty($keyword)) {
            $sql .= " AND p.ProductName LIKE ?";
            $params[] = '%' . $keyword . '%';
            $types .= 's';
        }

        // Đếm tổng số sản phẩm
        $countSql = "
        SELECT COUNT(*) AS total
        FROM products p
        LEFT JOIN categories c ON p.CategoryID = c.CategoryID
        WHERE p.Status IN ('appear', 'hidden')
    " . (!empty($keyword) ? " AND p.ProductName LIKE ?" : '');

        $countStmt = $this->db->getConnection()->prepare($countSql);
        if (!empty($keyword)) $countStmt->bind_param('s', $params[0]);
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;
        $countStmt->close();

        // Thêm phân trang
        $sql .= " ORDER BY p.ProductID DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        $types .= 'ii';

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'ProductID' => $row['ProductID'],
                'ProductName' => $row['ProductName'],
                'CategoryName' => $row['CategoryName'],
                'Price' => $row['Price'],
                'ImageURL' => $row['ImageURL'],
                'Status' => $row['Status'],
                'Description' => $row['Description']
            ];
        }

        $stmt->close();

        return [
            'products' => $products,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => ceil($total / $limit),
                'totalProducts' => $total
            ]
        ];
    }

    public function getTotalProducts($keyword = '')
    {
        $sql = "SELECT COUNT(*) AS total FROM products p 
                LEFT JOIN categories c ON p.CategoryID = c.CategoryID
                WHERE p.Status IN ('appear', 'hidden')";
        $params = [];
        $types = '';

        if (!empty($keyword)) {
            $sql .= " AND p.ProductName LIKE ?";
            $params[] = '%' . $keyword . '%';
            $types .= 's';
        }

        $stmt = $this->db->getConnection()->prepare($sql);
        if (!empty($keyword)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();

        return $total;
    }


    public function getProductsPaginated($page = 1, $keyword = '')
    {
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $this->itemsPerPage;

        $sql = "SELECT p.*, c.CategoryName
                FROM products p
                LEFT JOIN categories c ON p.CategoryID = c.CategoryID
                WHERE p.Status IN ('appear', 'hidden')";
        $params = [];
        $types = '';

        if (!empty($keyword)) {
            $sql .= " AND p.ProductName LIKE ?";
            $params[] = '%' . $keyword . '%';
            $types .= 's';
        }

        $sql .= " ORDER BY p.ProductID DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $this->itemsPerPage;
        $types .= 'ii';

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();

        return $products;
    }

    // Render HTML sản phẩm + phân trang
    public function renderProducts($page = 1, $keyword = '')
    {
        $products = $this->getProductsPaginated($page, $keyword);
        $totalProducts = $this->getTotalProducts($keyword);
        $totalPages = ceil($totalProducts / $this->itemsPerPage);

        ob_start();
?>
        <tbody id="productsBody">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                    <tr class="product-row">
                        <td><img src="../..<?php echo $row['ImageURL']; ?>" alt="<?php echo htmlspecialchars($row['ProductName']); ?>" style="width:100px;height:100px;object-fit:cover;"></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['ProductName']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['CategoryName']); ?></td>
                        <td style="text-align: center;"><?php echo number_format($row['Price'], 0, ',', '.'); ?></td>
                        <td class="actions" style="text-align: center; display:flex;">


                            <button class="btn btn-success btn-sm me-2" onclick="viewProduct(<?php echo $row['ProductID']; ?>)">
                                <i class="fa-solid fa-eye"></i>
                            </button>


                            <button class="btn btn-primary btn-sm me-2" onclick="editProduct(<?php echo $row['ProductID']; ?>)">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <button type="button" class="btn btn-danger btn-sm me-2" onclick="confirmDelete(<?php echo $row['ProductID']; ?>)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Không có sản phẩm nào</td>
                </tr>
            <?php endif; ?>
        </tbody>

        <?php if ($totalPages > 1): ?>
            <tfoot>
                <tr id="pagiantion">
                    <td colspan="5" style="text-align:center;">
                        <ul class="pagination justify-content-center" style="display:inline-flex; padding-left:0; list-style:none;">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">&lt;</a></li>
                            <?php endif; ?>

                            <?php
                            $range = 2;
                            if ($page > $range + 2) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++) {
                                $active = ($i === $page) ? 'active' : '';
                                echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
                            }
                            if ($page < $totalPages - $range - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                echo "<li class='page-item'><a class='page-link' href='?page=$totalPages'>$totalPages</a></li>";
                            }
                            ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">&gt;</a></li>
                            <?php endif; ?>
                        </ul>
                    </td>
                </tr>
            </tfoot>
        <?php endif; ?>
<?php
        return ob_get_clean();
    }

    // Lấy sản phẩm phân trang + tìm kiếm
    public function getProductsPaginatedForSearch($page = 1, $keyword = '')
    {
        $offset = ($page - 1) * $this->itemsPerPage;

        $sql = "
            SELECT p.ProductID, p.ProductName, p.Price, p.ImageURL, p.Status,
                   c.CategoryName, p.Description
            FROM products p
            LEFT JOIN categories c ON p.CategoryID = c.CategoryID
            WHERE p.Status IN ('appear','hidden')
        ";

        $params = [];
        $types = '';

        if (!empty($keyword)) {
            $sql .= " AND p.ProductName LIKE ?";
            $params[] = '%' . $keyword . '%';
            $types .= 's';
        }

        // Đếm tổng sản phẩm
        $countSql = "
            SELECT COUNT(*) AS total
            FROM products p
            LEFT JOIN categories c ON p.CategoryID = c.CategoryID
            WHERE p.Status IN ('appear','hidden')
        ";
        if (!empty($keyword)) $countSql .= " AND p.ProductName LIKE ?";

        $countStmt = $this->db->getConnection()->prepare($countSql);
        if (!empty($keyword)) $countStmt->bind_param('s', $params[0]);
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;
        $countStmt->close();

        // Thêm phân trang
        $sql .= " ORDER BY p.ProductID DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $this->itemsPerPage;
        $types .= 'ii';

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();

        return [
            'products' => $products,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => ceil($total / $this->itemsPerPage),
                'totalProducts' => $total
            ]
        ];
    }

    public function close()
    {
        $this->db->close();
    }

    // Thêm sản phẩm mới
    function addProduct($productName, $categoryID, $price, $description, $supplierID, $quantity_in_stock, $fileImage)
    {
        $db = new DatabaseConnection();
        $db->connect();

        // Validate cơ bản
        if (!$productName || !$categoryID || !$price || !$supplierID) {
            return ["success" => false, "message" => "Vui lòng điền đầy đủ thông tin bắt buộc"];
        }

        // Kiểm tra sản phẩm đã tồn tại với cùng nhà cung cấp
        $checkSql = "SELECT COUNT(*) as count FROM products WHERE ProductName = ? AND Supplier_id = ?";
        $result = $db->queryPrepared($checkSql, [$productName, $supplierID], "si");
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            return ["success" => false, "message" => "Sản phẩm cùng tên với nhà cung cấp này đã tồn tại"];
        }

        // Xử lý ảnh
        $imageRelativeURL = '';
        if (isset($fileImage) && $fileImage['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../../assets/images/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $ext = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid("product_") . "." . $ext;
            $targetFilePath = $targetDir . $newFileName;

            if (!move_uploaded_file($fileImage['tmp_name'], $targetFilePath)) {
                return ["success" => false, "message" => "Không thể lưu ảnh."];
            }

            $imageRelativeURL = "/assets/images/" . $newFileName;
        } else {
            return ["success" => false, "message" => "Ảnh không hợp lệ hoặc chưa được chọn."];
        }

        // Thêm sản phẩm vào DB
        $insertSql = "INSERT INTO products 
        (ProductName, CategoryID, Price, Description, ImageURL, Status, Supplier_id, quantity_in_stock)
        VALUES (?, ?, ?, ?, ?, 'appear', ?, ?)";
        $params = [$productName, $categoryID, $price, $description, $imageRelativeURL, $supplierID, $quantity_in_stock];
        $types  = "siissii";

        if ($db->queryPrepared($insertSql, $params, $types)) {
            $db->close();
            return ["success" => true, "message" => "Thêm sản phẩm thành công"];
        } else {
            $db->close();
            return ["success" => false, "message" => "Lỗi khi thêm sản phẩm"];
        }
    }


    // Cập nhật sản phẩm
    function updateProduct($productId, $productName, $categoryID, $price, $description, $status = 'appear', $quantity = 0, $supplierID = 0, $fileImage = null)
    {
        $db = new DatabaseConnection();
        $db->connect();

        try {
            // Validate cơ bản
            if (!$productId || !$productName || !$categoryID || !$price) {
                return ['success' => false, 'message' => 'Thiếu dữ liệu bắt buộc'];
            }

            if ($price <= 0) return ['success' => false, 'message' => 'Giá sản phẩm phải lớn hơn 0'];
            if (!in_array($status, ['appear', 'hidden'])) return ['success' => false, 'message' => 'Trạng thái không hợp lệ'];

            // Lấy dữ liệu hiện tại
            $result = $db->queryPrepared("SELECT * FROM products WHERE ProductID = ?", [$productId], "i");
            $currentData = $result->fetch_assoc();
            if (!$currentData) return ['success' => false, 'message' => 'Sản phẩm không tồn tại'];

            $newImageURL = $currentData['ImageURL'];

            // Xử lý ảnh mới nếu có upload
            if ($fileImage && $fileImage['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                $maxSize = 2 * 1024 * 1024;

                if (!in_array($fileImage['type'], $allowedTypes)) return ['success' => false, 'message' => 'Định dạng file không hợp lệ'];
                if ($fileImage['size'] > $maxSize) return ['success' => false, 'message' => 'Kích thước file quá lớn (max 2MB)'];

                $ext = pathinfo($fileImage['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . uniqid() . '.' . $ext;
                $uploadDir = '../../assets/images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $uploadPath = $uploadDir . $filename;

                if (!move_uploaded_file($fileImage['tmp_name'], $uploadPath)) {
                    return ['success' => false, 'message' => 'Không thể tải lên hình ảnh'];
                }

                $newImageURL = '/assets/images/' . $filename;

                // Xóa ảnh cũ nếu khác
                if ($currentData['ImageURL'] && $currentData['ImageURL'] !== $newImageURL) {
                    $oldImagePath = '../../' . $currentData['ImageURL'];
                    if (file_exists($oldImagePath)) unlink($oldImagePath);
                }
            }

            // Kiểm tra có gì thay đổi không
            if (
                $currentData['ProductName'] === $productName &&
                (int)$currentData['CategoryID'] === (int)$categoryID &&
                (float)$currentData['Price'] === (float)$price &&
                $currentData['Description'] === $description &&
                $currentData['Status'] === $status &&
                (int)$currentData['Supplier_id'] === (int)$supplierID &&
                (int)$currentData['quantity_in_stock'] === (int)$quantity &&
                $currentData['ImageURL'] === $newImageURL
            ) {
                return ['success' => true, 'message' => 'Không có gì thay đổi', 'productId' => $productId];
            }

            // Cập nhật sản phẩm
            $sql = "UPDATE products SET 
                ProductName = ?, 
                CategoryID = ?, 
                Price = ?, 
                Description = ?, 
                Status = ?, 
                Supplier_id = ?, 
                quantity_in_stock = ?, 
                ImageURL = ?
            WHERE ProductID = ?";

            $params = [$productName, $categoryID, $price, $description, $status, $supplierID, $quantity, $newImageURL, $productId];
            $types  = "sidssissi";

            $db->queryPrepared($sql, $params, $types);

            return ['success' => true, 'message' => 'Cập nhật sản phẩm thành công', 'productId' => $productId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            $db->close();
        }
    }

    // Xóa sản phẩm
    function deleteOrHideProduct($productId)
    {
        if (!$productId) {
            return ['status' => 'error', 'message' => 'Product ID is required'];
        }

        $db = new DatabaseConnection();
        $db->connect();

        try {
            // Kiểm tra xem sản phẩm có trong orderdetails không
            $checkQuery = "SELECT COUNT(*) AS count FROM orderdetails WHERE ProductID = ?";
            $result = $db->queryPrepared($checkQuery, [$productId], "i");
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                // Nếu có trong đơn hàng, update status thành hidden
                $updateQuery = "UPDATE products SET Status = 'hidden' WHERE ProductID = ?";
                if ($db->queryPrepared($updateQuery, [$productId], "i")) {
                    return [
                        "status" => "hidden",
                        "message" => "Sản phẩm đã được ẩn vì đã tồn tại trong đơn hàng"
                    ];
                } else {
                    return ["status" => "error", "message" => "Lỗi khi ẩn sản phẩm"];
                }
            } else {
                // Nếu không có trong đơn hàng, xóa bình thường
                $imageQuery = "SELECT ImageURL FROM products WHERE ProductID = ?";
                $imageResult = $db->queryPrepared($imageQuery, [$productId], "i");
                $imageData = $imageResult->fetch_assoc();

                $deleteQuery = "DELETE FROM products WHERE ProductID = ?";
                if ($db->queryPrepared($deleteQuery, [$productId], "i")) {

                    // Xóa file ảnh nếu tồn tại
                    if ($imageData && !empty($imageData['ImageURL'])) {
                        // __DIR__ trỏ tới folder hiện tại, kết hợp với ../../ để ra root project
                        $imagePath = __DIR__ . '/../../' . ltrim($imageData['ImageURL'], '/');
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        } else {
                            error_log("File ảnh không tồn tại: " . $imagePath);
                        }
                    }

                    return [
                        "status" => "deleted",
                        "message" => "Đã xóa sản phẩm thành công"
                    ];
                } else {
                    return ["status" => "error", "message" => "Lỗi khi xóa sản phẩm"];
                }
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()];
        } finally {
            $db->close();
        }
    }


    // Lấy thông tin chi tiết của sản phẩm
    // Trong ProductManager.php
    public function getProductDetails($productId)
    {
        if (!is_numeric($productId)) {
            return [
                'success' => false,
                'message' => 'ID sản phẩm không hợp lệ'
            ];
        }

        $productId = (int)$productId;

        try {
            $conn = $this->db->getConnection();

            $sql = "
            SELECT 
                p.ProductID,
                p.ProductName,
                p.ImageURL,
                p.Price,
                p.Status,
                p.Description,
                p.quantity_in_stock,
                c.CategoryName,
                s.supplier_name AS SupplierName,
                COALESCE(SUM(CASE WHEN ird.receipt_id IS NOT NULL THEN ird.quantity ELSE 0 END), 0) as total_imported,
                COALESCE(SUM(CASE WHEN od.OrderID IS NOT NULL THEN od.Quantity ELSE 0 END), 0) as total_sold
            FROM products p
            LEFT JOIN categories c ON p.CategoryID = c.CategoryID
            LEFT JOIN suppliers s ON p.Supplier_id = s.supplier_id
            LEFT JOIN import_receipt_detail ird ON p.ProductID = ird.product_id
            LEFT JOIN orderdetails od ON p.ProductID = od.ProductID
            WHERE p.ProductID = ?
            GROUP BY p.ProductID
        ";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Lỗi SQL: ' . $conn->error
                ];
            }

            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm'
                ];
            }

            $product = $result->fetch_assoc();
            $stmt->close();

            return [
                'success' => true,
                'product' => $product
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage()
            ];
        }
    }
}
