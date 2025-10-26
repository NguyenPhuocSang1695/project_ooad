<?php
// JSON endpoint: get user details by username
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }

    $username = isset($_GET['username']) ? trim($_GET['username']) : '';
    if ($username === '') {
        echo json_encode(['success' => false, 'message' => 'Thiếu tham số username']);
        exit;
    }

    require_once __DIR__ . '/connect.php';
    require_once __DIR__ . '/UserManager.php';

    $userManager = new UserManager($myconn ?? null);
    $result = $userManager->getUserDetails($username);

    // Clear any accidental output
    ob_clean();
    echo json_encode($result);
    exit;
} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
    exit;
}
