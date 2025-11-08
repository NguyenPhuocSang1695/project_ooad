<?php
// JSON endpoint: update user information
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }

    // Session for permission checks
    session_name('admin_session');
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $currentUser = isset($_SESSION['Username']) ? (string)$_SESSION['Username'] : '';
    $currentRole = isset($_SESSION['Role']) ? (string)$_SESSION['Role'] : '';

    require_once __DIR__ . '/connect.php';
    require_once __DIR__ . '/UserManager.php';

    // Collect payload safely
    $payload = [
        'user_id'        => isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0,
        // original username key
        'username'        => isset($_POST['username']) ? trim($_POST['username']) : '',
        // potential new username value
        'new_username'    => isset($_POST['new_username']) ? trim($_POST['new_username']) : '',
        'fullname'        => isset($_POST['fullname']) ? trim($_POST['fullname']) : '',
        'phone'           => isset($_POST['phone']) ? trim($_POST['phone']) : '',
        'role'            => isset($_POST['role']) ? trim($_POST['role']) : 'customer',
        'status'          => isset($_POST['status']) ? trim($_POST['status']) : 'Active',
        'province'        => isset($_POST['province']) && $_POST['province'] !== '' ? (int)$_POST['province'] : null,
        'district'        => isset($_POST['district']) && $_POST['district'] !== '' ? (int)$_POST['district'] : null,
        'ward'            => isset($_POST['ward']) && $_POST['ward'] !== '' ? (int)$_POST['ward'] : null,
        'address'         => isset($_POST['address']) ? trim($_POST['address']) : '',
        'password'        => isset($_POST['password']) ? (string)$_POST['password'] : '',
        'confirm_password'=> isset($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '',
        // session context
        '_currentUser'    => $currentUser,
        '_currentRole'    => $currentRole,
    ];

    $userManager = new UserManager($myconn ?? null);
    $result = $userManager->updateUser($payload);

    // If success and admin just updated own username/fullname, refresh session values
    if (is_array($result) && !empty($result['success'])) {
        $isSelf = ($currentUser !== '' && strcasecmp($currentUser, ($payload['username'] ?? '')) === 0);
        if ($isSelf) {
            // Update username in session if changed
            $newU = $payload['new_username'] ?: $payload['username'];
            if (!empty($newU) && $newU !== $currentUser) {
                $_SESSION['Username'] = $newU;
                $currentUser = $newU;
            }
            if (!empty($payload['fullname'])) {
                $_SESSION['FullName'] = $payload['fullname'];
            }
            if (!empty($payload['role'])) {
                $_SESSION['Role'] = $payload['role'];
            }
        }
    }

    // Ensure clean JSON output
    ob_clean();
    echo json_encode($result);
    exit;
} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
    exit;
}

?>
