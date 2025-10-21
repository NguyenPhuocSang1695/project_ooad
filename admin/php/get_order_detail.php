<?php
header('Content-Type: application/json');
include 'connect.php';

if ($myconn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $myconn->connect_error]));
}
 
$orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;

if (!$orderId) {
    die(json_encode(['success' => false, 'error' => 'Order ID is required']));
}

// Lấy thông tin đơn hàng và người mua
$orderQuery = "SELECT o.*, u.FullName, u.Phone, u.Address,
    CONCAT(u.Address, ', ', d.name, ', ', p.name) as full_address
    FROM orders o
    JOIN users u ON o.Username = u.Username 
    LEFT JOIN province p ON o.Province = p.province_id
    LEFT JOIN district d ON o.District = d.district_id
    WHERE o.OrderID = ?";

$stmt = $myconn->prepare($orderQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderResult = $stmt->get_result();
$orderInfo = $orderResult->fetch_assoc();

if (!$orderInfo) {
    die(json_encode(['success' => false, 'error' => 'Order not found']));
}

// Lấy chi tiết sản phẩm trong đơn hàng
$productsQuery = "SELECT od.*, p.ProductName, p.ImageURL 
                 FROM orderdetails od 
                 JOIN products p ON od.ProductID = p.ProductID 
                 WHERE od.OrderID = ?";

$stmt = $myconn->prepare($productsQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$productsResult = $stmt->get_result();
$products = [];

while ($product = $productsResult->fetch_assoc()) {
    $products[] = [
        'productName' => $product['ProductName'],
        'quantity' => $product['Quantity'],
        'unitPrice' => $product['UnitPrice'],
        'totalPrice' => $product['TotalPrice'],
        'imageUrl' => $product['ImageURL']
    ];
}

// Chuẩn bị response
$response = [
    'success' => true,
    'order' => [
        'orderId' => $orderInfo['OrderID'],
        'orderDate' => $orderInfo['DateGeneration'],
        'status' => $orderInfo['Status'],
        'receiverName' => $orderInfo['FullName'],
        'receiverPhone' => $orderInfo['Phone'],
        'receiverAddress' => $orderInfo['full_address'],
        'paymentMethod' => $orderInfo['PaymentMethod'],
        'totalAmount' => $orderInfo['TotalAmount'],
        'products' => $products
    ]
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$stmt->close();
$myconn->close();
?>