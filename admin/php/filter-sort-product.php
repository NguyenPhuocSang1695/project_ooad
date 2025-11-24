<?php
require_once './connect.php';

header('Content-Type: application/json');

$db = new DatabaseConnection();
$db->connect();
$mysqli = $db->getConnection();

// Lấy parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
$priceMin = isset($_GET['price_min']) ? (float)$_GET['price_min'] : 0;
$priceMax = isset($_GET['price_max']) ? (float)$_GET['price_max'] : 0;
$sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'name_asc';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$customLimit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;

$itemsPerPage = $customLimit;
$offset = ($page - 1) * $itemsPerPage;

// Build WHERE clause
$whereConditions = ["p.Status != 'deleted'"];
$params = [];
$types = '';

if (!empty($keyword)) {
    $whereConditions[] = "p.ProductName LIKE ?";
    $params[] = "%$keyword%";
    $types .= 's';
}

if (!empty($categoryFilter) && $categoryFilter !== 'all') {
    $whereConditions[] = "p.CategoryID = ?";
    $params[] = $categoryFilter;
    $types .= 'i';
}

if ($priceMin > 0) {
    $whereConditions[] = "p.Price >= ?";
    $params[] = $priceMin;
    $types .= 'd';
}

if ($priceMax > 0) {
    $whereConditions[] = "p.Price <= ?";
    $params[] = $priceMax;
    $types .= 'd';
}

if (!empty($statusFilter) && $statusFilter !== 'all') {
    // Special filter: out_of_stock should check quantity
    if ($statusFilter === 'out_of_stock') {
        // Products with no stock (zero or less)
        $whereConditions[] = "(p.quantity_in_stock IS NULL OR p.quantity_in_stock = 0)";
        // no param to bind
    } else if ($statusFilter === 'near_out_of_stock') {
        // Products with stock less than or equal to 5
        $whereConditions[] = "(p.quantity_in_stock IS NOT NULL AND p.quantity_in_stock > 0 AND p.quantity_in_stock <= 5)";
        // no param to bind

    } else {
        $whereConditions[] = "p.Status = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }
}

$whereClause = implode(' AND ', $whereConditions);

// Determine ORDER BY
$orderBy = 'p.ProductName ASC';
switch ($sortBy) {
    case 'name_asc':
        $orderBy = 'p.ProductName ASC';
        break;
    case 'name_desc':
        $orderBy = 'p.ProductName DESC';
        break;
    case 'price_asc':
        $orderBy = 'p.Price ASC';
        break;
    case 'price_desc':
        $orderBy = 'p.Price DESC';
        break;
    case 'newest':
        $orderBy = 'p.ProductID DESC';
        break;
    case 'oldest':
        $orderBy = 'p.ProductID ASC';
        break;
}

// Count total
$countSql = "SELECT COUNT(*) as total 
             FROM Products p 
             WHERE $whereClause";

$countStmt = $mysqli->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get products
$sql = "SELECT 
            p.ProductID,
            p.ProductName,
            p.CategoryID,
            c.CategoryName,
            p.Price,
            p.ImageURL,
            p.Status,
            p.Description,
            p.quantity_in_stock,
            p.Supplier_id
        FROM Products p
        JOIN Categories c ON p.CategoryID = c.CategoryID
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT ? OFFSET ?";

$stmt = $mysqli->prepare($sql);

// Add LIMIT and OFFSET params
$params[] = $itemsPerPage;
$params[] = $offset;
$types .= 'ii';

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'ProductID' => $row['ProductID'],
        'ProductName' => $row['ProductName'],
        'CategoryID' => $row['CategoryID'],
        'CategoryName' => $row['CategoryName'],
        'Price' => (float)$row['Price'],
        'ImageURL' => $row['ImageURL'],
        'Status' => $row['Status'],
        'Description' => $row['Description'],
        'quantity_in_stock' => (int)$row['quantity_in_stock'],
        'Supplier_id' => $row['Supplier_id']
    ];
}

echo json_encode([
    'success' => true,
    'products' => $products,
    'pagination' => [
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems,
        'itemsPerPage' => $itemsPerPage
    ]
]);

$stmt->close();
$countStmt->close();
$mysqli->close();
