<?php
header('Content-Type: application/json');
include 'connect.php';

if ($myconn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $myconn->connect_error]);
    exit;
}
$myconn->set_charset("utf8");

// Lấy các tham số từ request
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
$offset = ($page - 1) * $limit;

$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$orderStatus = isset($_GET['order_status']) ? $_GET['order_status'] : '';
$provinceId = isset($_GET['province_id']) ? intval($_GET['province_id']) : 0;
$districtId = isset($_GET['district_id']) ? intval($_GET['district_id']) : 0;

// Sửa phần query SELECT với cú pháp chuẩn hơn
$selectQuery = "
    SELECT 
        OrderID AS madonhang,
        DateGeneration AS ngaytao,
        Status AS trangthai,
        TotalAmount AS giatien,
        CustomerName AS receiver_name,
        Phone AS receiver_phone,
        Address AS receiver_address,
        (SELECT name FROM province WHERE province_id = orders.Province) AS province_name,
        (SELECT name FROM district WHERE district_id = orders.District) AS district_name,
        (SELECT name FROM wards WHERE wards_id = orders.Ward) AS ward_name
    FROM orders
    WHERE 1=1";

$params = [];
$types = '';

if ($dateFrom) {
    $selectQuery .= " AND DATE(DateGeneration) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo) {
    $selectQuery .= " AND DATE(DateGeneration) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

if ($orderStatus && $orderStatus !== 'all') {
    $selectQuery .= " AND Status = ?";
    $params[] = $orderStatus;
    $types .= 's';
}

if ($provinceId > 0) {
    $selectQuery .= " AND Province = ?";
    $params[] = $provinceId;
    $types .= 'i';
}

if ($districtId > 0) {
    $selectQuery .= " AND District = ?";
    $params[] = $districtId;
    $types .= 'i';
}

$selectQuery .= " ORDER BY DateGeneration DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Sửa lại câu query đếm tổng số record
$countQuery = "
    SELECT COUNT(DISTINCT OrderID) as total 
    FROM orders
    WHERE 1=1";

if ($dateFrom) {
    $countQuery .= " AND DATE(DateGeneration) >= ?";
}

if ($dateTo) {
    $countQuery .= " AND DATE(DateGeneration) <= ?";
}

if ($orderStatus && $orderStatus !== 'all') {
    $countQuery .= " AND Status = ?";
}

if ($provinceId > 0) {
    $countQuery .= " AND Province = ?";
}

if ($districtId > 0) {
    $countQuery .= " AND District = ?";
}

$countParams = [];
$countTypes = '';

if ($dateFrom) {
    $countParams[] = $dateFrom;
    $countTypes .= 's';
}

if ($dateTo) {
    $countParams[] = $dateTo;
    $countTypes .= 's';
}

if ($orderStatus && $orderStatus !== 'all') {
    $countParams[] = $orderStatus;
    $countTypes .= 's';
}

if ($provinceId > 0) {
    $countParams[] = $provinceId;
    $countTypes .= 'i';
}

if ($districtId > 0) {
    $countParams[] = $districtId;
    $countTypes .= 'i';
}

$stmt = $myconn->prepare($countQuery);
if (!empty($countParams)) {
    $stmt->bind_param($countTypes, ...$countParams);
}

$stmt->execute();
$totalRecords = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

$stmt = $myconn->prepare($selectQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $receiver_address_parts = array_filter([
        $row['receiver_address'],
        $row['ward_name'],
        $row['district_name'],
        $row['province_name']
    ]);

    $orders[] = [
        'madonhang' => $row['madonhang'],
        'ngaytao' => $row['ngaytao'],
        'trangthai' => $row['trangthai'],
        'giatien' => $row['giatien'],
        'receiver_name' => $row['receiver_name'] ?? 'Không xác định',
        'receiver_phone' => $row['receiver_phone'] ?? 'Không xác định',
        'receiver_address' => implode(', ', $receiver_address_parts)
    ];
}

echo json_encode([
    'success' => true,
    'orders' => $orders,
    'total_pages' => $totalPages,
    'current_page' => $page
]);

$stmt->close();
$myconn->close();
?>