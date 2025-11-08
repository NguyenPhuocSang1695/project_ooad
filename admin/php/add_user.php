<?php
// Ensure we return clean JSON only
header('Content-Type: application/json; charset=utf-8');
// Start output buffering to strip any accidental output/BOM
if (function_exists('ob_get_level')) {
	while (ob_get_level() > 0) { ob_end_clean(); }
}
ob_start();

require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/UserManager.php';

// Only allow POST
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
	exit;
}

try {
	// Build DB connection for manager
	$db = new DatabaseConnection();
	$db->connect();
	$manager = new UserManager($db);

	// Gather POST data
	$payload = [
		'username'          => $_POST['username'] ?? '',
		'fullname'          => $_POST['fullname'] ?? '',
		'phone'             => $_POST['phone'] ?? '',
		'password'          => $_POST['password'] ?? '',
		'confirm_password'  => $_POST['confirm_password'] ?? '',
		'role'              => $_POST['role'] ?? 'customer',
		// Accept 'Active'|'Block' or legacy '1'|'0'
		'status'            => $_POST['status'] ?? 'Active',
		'province'          => $_POST['province'] ?? null,
		'district'          => $_POST['district'] ?? null,
		'ward'              => $_POST['ward'] ?? null,
		'address'           => $_POST['address'] ?? '',
	];

	$result = $manager->addUser($payload);
	if (!is_array($result)) {
		$result = ['success' => (bool)$result, 'message' => $result ? 'Thành công' : 'Thất bại'];
	}
	// Clear any prior output and send JSON
	if (function_exists('ob_get_length') && ob_get_length() !== false) {
		ob_clean();
	}
	echo json_encode($result);
	exit;
} catch (Throwable $e) {
	http_response_code(500);
	if (function_exists('ob_get_length') && ob_get_length() !== false) {
		ob_clean();
	}
	echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
	exit;
}
?>
?>