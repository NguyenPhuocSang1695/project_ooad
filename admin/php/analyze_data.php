<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'connect.php';
require_once 'sessionHandler.php';

class Analyzer {
    private mysqli $conn;
    private string $startDate;
    private string $endDate;

    public function __construct(mysqli $conn, ?string $start, ?string $end) {
        $this->conn = $conn;
        $this->startDate = $this->normalizeStart($start);
        $this->endDate = $this->normalizeEnd($end);
    }

    private function normalizeStart(?string $s): string {
        if ($s && preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s . ' 00:00:00';
        return date('Y-m-01') . ' 00:00:00';
    }

    private function normalizeEnd(?string $e): string {
        if ($e && preg_match('/^\d{4}-\d{2}-\d{2}$/', $e)) return $e . ' 23:59:59';
        return date('Y-m-d') . ' 23:59:59';
    }

    public function run(): void {
        try {
            $data = [
                'success' => true,
                'customers' => $this->getTopCustomers(),
                'products' => $this->getTopProducts(),
                'total_revenue' => $this->getTotalRevenue(),
                'revenue_change' => null,
                'best_selling' => $this->getBestSelling(),
                'worst_selling' => $this->getWorstSelling()
            ];
            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private function getTotalRevenue(): float {
        $sql = "SELECT SUM(TotalAmount) AS total 
                FROM orders 
                WHERE DateGeneration BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $this->startDate, $this->endDate);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return floatval($result['total'] ?? 0);
    }

    private function getTopCustomers(): array {
        $sql = "SELECT o.Username, u.FullName, COUNT(o.OrderID) AS order_count,
                       MAX(o.DateGeneration) AS latest_order_date,
                       SUM(o.TotalAmount) AS total_amount
                FROM orders o
                JOIN users u ON o.Username = u.Username
                WHERE o.DateGeneration BETWEEN ? AND ?
                GROUP BY o.Username
                ORDER BY total_amount DESC
                LIMIT 20";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $this->startDate, $this->endDate);
        $stmt->execute();
        $res = $stmt->get_result();
        $customers = [];
        while ($r = $res->fetch_assoc()) {
            $customers[] = [
                'customer_name' => $r['FullName'],
                'order_count' => (int)$r['order_count'],
                'latest_order_date' => $r['latest_order_date'],
                'total_amount' => floatval($r['total_amount']),
                'order_links' => $this->getOrdersByUser($r['Username'])
            ];
        }
        return $customers;
    }

    private function getOrdersByUser(string $username): array {
        $sql = "SELECT OrderID FROM orders 
                WHERE Username = ? AND DateGeneration BETWEEN ? AND ?
                ORDER BY DateGeneration DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $username, $this->startDate, $this->endDate);
        $stmt->execute();
        $res = $stmt->get_result();
        $orders = [];
        while ($r = $res->fetch_assoc()) {
            $orders[] = ['id' => $r['OrderID']];
        }
        return $orders;
    }

    private function getTopProducts(): array {
        $sql = "SELECT p.ProductID, p.ProductName,
                       SUM(od.Quantity) AS quantity_sold,
                       SUM(od.TotalPrice) AS total_amount
                FROM orderdetails od
                JOIN products p ON od.ProductID = p.ProductID
                JOIN orders o ON od.OrderID = o.OrderID
                WHERE o.DateGeneration BETWEEN ? AND ?
                GROUP BY p.ProductID
                ORDER BY quantity_sold DESC
                LIMIT 20";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $this->startDate, $this->endDate);
        $stmt->execute();
        $res = $stmt->get_result();
        $products = [];
        while ($r = $res->fetch_assoc()) {
            $products[] = [
                'product_name' => $r['ProductName'],
                'quantity_sold' => (int)$r['quantity_sold'],
                'total_amount' => floatval($r['total_amount']),
                'order_links' => $this->getOrdersByProduct((int)$r['ProductID'])
            ];
        }
        return $products;
    }

    private function getOrdersByProduct(int $productId): array {
        $sql = "SELECT DISTINCT o.OrderID 
                FROM orders o
                JOIN orderdetails od ON o.OrderID = od.OrderID
                WHERE od.ProductID = ? AND o.DateGeneration BETWEEN ? AND ?
                ORDER BY o.DateGeneration DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iss', $productId, $this->startDate, $this->endDate);
        $stmt->execute();
        $res = $stmt->get_result();
        $orders = [];
        while ($r = $res->fetch_assoc()) {
            $orders[] = ['id' => $r['OrderID']];
        }
        return $orders;
    }

    private function getBestSelling(): mixed {
        $sql = "SELECT p.ProductName, SUM(od.Quantity) AS quantity,
                       SUM(od.TotalPrice) AS revenue
                FROM orderdetails od
                JOIN products p ON od.ProductID = p.ProductID
                JOIN orders o ON od.OrderID = o.OrderID
                WHERE o.DateGeneration BETWEEN ? AND ?
                GROUP BY p.ProductID
                ORDER BY revenue DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $this->startDate, $this->endDate);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        if (!$r) return 'Chưa có dữ liệu';
        return [
            'name' => $r['ProductName'],
            'quantity' => (int)$r['quantity'],
            'revenue' => floatval($r['revenue']),
            'contribution' => 0
        ];
    }

    private function getWorstSelling(): mixed {
        $sql = "SELECT p.ProductName, SUM(od.Quantity) AS quantity,
                       SUM(od.TotalPrice) AS revenue
                FROM orderdetails od
                JOIN products p ON od.ProductID = p.ProductID
                JOIN orders o ON od.OrderID = o.OrderID
                WHERE o.DateGeneration BETWEEN ? AND ?
                GROUP BY p.ProductID
                HAVING revenue > 0
                ORDER BY revenue ASC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $this->startDate, $this->endDate);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        if (!$r) return 'Chưa có dữ liệu';
        return [
            'name' => $r['ProductName'],
            'quantity' => (int)$r['quantity'],
            'revenue' => floatval($r['revenue']),
            'contribution' => 0
        ];
    }
}

// ========== CHẠY ==========
try {
    $db = new DatabaseConnection();
    $db->connect();
    $conn = $db->getConnection();

    $start = $_POST['start_date'] ?? null;
    $end = $_POST['end_date'] ?? null;

    $analyzer = new Analyzer($conn, $start, $end);
    $analyzer->run();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
