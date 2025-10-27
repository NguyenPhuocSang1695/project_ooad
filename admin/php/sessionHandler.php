<?php
// sessionHandler.php - OOP version
// - Khi include file này, nó **không** echo gì cả.
// - Nếu bạn muốn endpoint trả JSON khi gọi trực tiếp, có thể gọi SessionManager::handleRequest().

class SessionManager {
    private string $sessionName = 'admin_session';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->sessionName);
            session_start();
        }
    }

    public function getUserInfo(): array {
        // Trả về mảng trống khi chưa đăng nhập (không echo)
        if (isset($_SESSION['Username']) && isset($_SESSION['FullName']) && isset($_SESSION['Role'])) {
            $defaultAvatar = '../../assets/images/admin.jpg';
            if ($_SESSION['Role'] === 'admin') {
                $defaultAvatar = '../../assets/images/sang.jpg';
            }
            return [
                'status' => 'success',
                'username' => $_SESSION['Username'],
                'fullname' => $_SESSION['FullName'],
                'role' => $_SESSION['Role'],
                'avatar' => $defaultAvatar
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Không tìm thấy thông tin đăng nhập'
        ];
    }

    // Nếu bạn muốn endpoint /php/sessionHandler.php trả JSON khi request trực tiếp tới file này:
    public static function handleRequest(): void {
        $mgr = new self();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($mgr->getUserInfo(), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Nếu file này được gọi trực tiếp (qua fetch / request), trả JSON
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    SessionManager::handleRequest();
}
