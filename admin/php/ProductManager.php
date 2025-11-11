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
}
