
<?php
include 'connect.php';

$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$customers = [];
$items = [];
$total_revenue = 0;
$best_selling_item = null;
$least_selling_item = null;

if ($start_date && $end_date) {
    $sql_customers = "SELECT c.id, c.name, SUM(o.total) as total_spent, 
            GROUP_CONCAT(CONCAT('<a href=\"orderDetail2.html?id=', o.id, '\" class=\"order-link\">Đơn ', o.id, '</a> - ', o.total, ' VND (', DATE_FORMAT(o.order_date, '%d/%m/%Y'), ')') SEPARATOR '<br>') as orders
            FROM customers c
            JOIN orders o ON c.id = o.customer_id
            WHERE o.order_date BETWEEN ? AND ? 
            GROUP BY c.id, c.name
            ORDER BY total_spent DESC
            LIMIT 5";
    $stmt_customers = $myconn->prepare($sql_customers);
    $stmt_customers->bind_param("ss", $start_date, $end_date);
    $stmt_customers->execute();
    $result_customers = $stmt_customers->get_result();
    while ($row = mysqli_fetch_assoc($result_customers)) {
        $customers[] = $row;
    }
    $stmt_customers->close();

    $sql_items = "SELECT p.id, p.name, SUM(od.quantity) as total_quantity, SUM(od.quantity * od.price) as total_revenue,
            GROUP_CONCAT(CONCAT('<a href=\"orderDetail2.html?id=', o.id, '\" class=\"order-link\">Đơn ', o.id, '</a> - ', od.quantity, ' x ', od.price, ' VND (', DATE_FORMAT(o.order_date, '%d/%m/%Y'), ')') SEPARATOR '<br>') as orders
            FROM products p
            JOIN order_details od ON p.id = od.product_id
            JOIN orders o ON od.order_id = o.id
            WHERE o.order_date BETWEEN ? AND ?
            GROUP BY p.id, p.name
            ORDER BY total_quantity DESC";
    $stmt_items = $myconn->prepare($sql_items);
    $stmt_items->bind_param("ss", $start_date, $end_date);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    while ($row = mysqli_fetch_assoc($result_items)) {
        $items[] = $row;
        $total_revenue += $row['total_revenue'];
    }
    $stmt_items->close();

    if (!empty($items)) {
        $best_selling_item = $items[0];
        $least_selling_item = end($items);
    }
}