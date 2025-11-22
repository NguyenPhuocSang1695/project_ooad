<?php
// JSON endpoint: toggle user status (Block/Active)
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }

    // Start session for auth context
    session_name('admin_session');
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $currentRole = isset($_SESSION['Role']) ? strtolower(trim((string)$_SESSION['Role'])) : '';
    $currentUser = isset($_SESSION['Username']) ? trim((string)$_SESSION['Username']) : '';

    // Prepare data for manager permission logic; actual checks in UserManager::toggleUserStatus

    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu hoặc không hợp lệ user_id']);
        exit;
    }

    require_once __DIR__ . '/connect.php';
    require_once __DIR__ . '/UserManager.php';

    $manager = new UserManager($myconn ?? null);
    $result = $manager->toggleUserStatus([
        'user_id' => $userId,
        '_currentUser' => $currentUser,
        '_currentRole' => $currentRole,
    ]);
    echo json_encode($result);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
    exit;
}
