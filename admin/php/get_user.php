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
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

    require_once __DIR__ . '/connect.php';
    require_once __DIR__ . '/UserManager.php';

    $userManager = new UserManager($myconn ?? null);
    if ($username !== '') {
        $result = $userManager->getUserDetails($username);
    } elseif ($userId > 0) {
        $result = $userManager->getUserDetailsById($userId);
    } else {
        echo json_encode(['success' => false, 'message' => 'Thiếu tham số username hoặc user_id']);
        exit;
    }

    // If target role is not admin, do not load username (unset or blank)
    if (is_array($result) && !empty($result['success']) && isset($result['data']) && is_array($result['data'])) {
        $role = strtolower((string)($result['data']['role'] ?? ''));
        if ($role !== 'admin') {
            $result['data']['username'] = '';
        }
    }

    // Clear any accidental output
    ob_clean();
    echo json_encode($result);
    exit;
} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
    exit;
}
