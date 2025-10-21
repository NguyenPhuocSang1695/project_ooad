<?php
require_once __DIR__ . '/ProductController.php';

$controller = new ProductController();

// Accept both GET and POST and multiple param names for compatibility with different callers
$page = 1;
$keyword = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_POST['page']) ? (int)$_POST['page'] : 1);
    // Some clients send 'search' instead of 'keyword'
    $keyword = isset($_POST['search']) ? trim($_POST['search']) : (isset($_POST['keyword']) ? trim($_POST['keyword']) : '');
} else {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : (isset($_GET['search']) ? trim($_GET['search']) : '');
}

header('Content-Type: application/json; charset=utf-8');

echo json_encode($controller->getProductsPaginatedForSearch($page, $keyword), JSON_UNESCAPED_UNICODE);
