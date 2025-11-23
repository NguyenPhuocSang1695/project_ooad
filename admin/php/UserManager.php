<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/User.php';

class UserManager
{
    public $dbConnection;

    public function __construct($dbConnection = null)
    {
        if ($dbConnection === null) {
            $dbConnection = new DatabaseConnection();
            $dbConnection->connect();
        }
        $this->dbConnection = $dbConnection;
    }

    /**
     * Get total number of users. If $role provided, count only that role.
     * @param string|null $role
     * @return int
     */
    public function getTotalUsers($role = null)
    {
        if ($role) {
            $sql = "SELECT COUNT(*) as total FROM users WHERE Role = ?";
            $res = $this->dbConnection->queryPrepared($sql, [$role], "s");
            $row = $res->fetch_assoc();
            return (int)$row['total'];
        }

        $sql = "SELECT COUNT(*) as total FROM users";
        $res = $this->dbConnection->query($sql);
        $row = $res->fetch_assoc();
        return (int)$row['total'];
    }

    /**
     * Search users by keyword
     * @param string $keyword Search term
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @return array Array of User objects
     */
    public function searchUsers($keyword, $offset = 0, $limit = 10) 
    {
        $keyword = "%{$keyword}%";
    $sql = "SELECT user_id, Username, FullName, Phone, Status, Role 
                FROM users 
                WHERE Username LIKE ? 
                   OR FullName LIKE ? 
                   OR Phone LIKE ? 
                ORDER BY CASE WHEN Role = 'admin' THEN 0 ELSE 1 END, Role 
                LIMIT ?, ?";
        
        $res = $this->dbConnection->queryPrepared(
            $sql, 
            [$keyword, $keyword, $keyword, (int)$offset, (int)$limit],
            "sssii"
        );

        $users = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $users[] = new User($row);
            }
        }
        return $users;
    }

    /**
     * Get total count of search results
     * @param string $keyword Search term
     * @return int Total number of matches
     */
    public function getTotalSearchResults($keyword)
    {
        $keyword = "%{$keyword}%";
        $sql = "SELECT COUNT(*) as total 
                FROM users 
                WHERE Username LIKE ? 
                   OR FullName LIKE ? 
                   OR Phone LIKE ?";
        
        $res = $this->dbConnection->queryPrepared(
            $sql, 
            [$keyword, $keyword, $keyword],
            "sss"
        );
        
        $row = $res->fetch_assoc();
        return (int)$row['total'];
    }

    /**
     * Get users with paging and optional role filter.
     * Returns array of User objects.
     */
    public function getUsers($offset = 0, $limit = 10, $role = null)
    {
        // Keep same ordering: admin first, then others
        if ($role) {
            $sql = "SELECT user_id, Username, FullName, Phone, Status, Role FROM users WHERE Role = ? ORDER BY CASE WHEN Role = 'admin' THEN 0 ELSE 1 END, Role LIMIT ?, ?";
            $res = $this->dbConnection->queryPrepared($sql, [$role, (int)$offset, (int)$limit], "sii");
        } else {
            $sql = "SELECT user_id, Username, FullName, Phone, Status, Role FROM users ORDER BY CASE WHEN Role = 'admin' THEN 0 ELSE 1 END, Role LIMIT ?, ?";
            $res = $this->dbConnection->queryPrepared($sql, [(int)$offset, (int)$limit], "ii");
        }

        $users = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $users[] = new User($row);
            }
        }

        return $users;
    }

    /**
     * Get orders for a user with pagination
     * @param int $userId User ID
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @return array [orders=>[], total=>int]
     */
    public function getUserOrders(int $userId, int $offset = 0, int $limit = 10): array
    {
        // Ensure connection
        $conn = $this->dbConnection->getConnection();
        if (!$conn && method_exists($this->dbConnection, 'connect')) {
            $this->dbConnection->connect();
            $conn = $this->dbConnection->getConnection();
        }
        if (!$conn) return ['orders' => [], 'total' => 0];

        // Count
        $sqlCount = 'SELECT COUNT(*) AS total FROM orders WHERE user_id = ?';
        $resC = $this->dbConnection->queryPrepared($sqlCount, [$userId], 'i');
        $rowC = $resC ? $resC->fetch_assoc() : ['total' => 0];
        $total = (int)($rowC['total'] ?? 0);

        // Data - only select columns that exist in current schema
        $sql = "SELECT OrderID, user_id, PaymentMethod, CustomerName, Phone, DateGeneration, TotalAmount, address_id, voucher_id
                FROM orders 
                WHERE user_id = ? 
                ORDER BY DateGeneration DESC, OrderID DESC 
                LIMIT ?, ?";
        $res = $this->dbConnection->queryPrepared($sql, [$userId, $offset, $limit], 'iii');
        $orders = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $orders[] = $r;
            }
        }
        return ['orders' => $orders, 'total' => $total];
    }

    /**
     * Return list of provinces for selects
     */
    public function getProvinces()
    {
        $sql = "SELECT province_id, name FROM province ORDER BY name";
        $res = $this->dbConnection->query($sql);
        $provinces = [];
        while ($row = $res->fetch_assoc()) {
            $provinces[] = $row;
        }
        return $provinces;
    }

    /**
     * Add a new user into database
     * Expects keys: username, fullname, phone, password, role, status
     * Rule: If role = customer, username and password are optional and will not be inserted when empty.
     * Returns [success=>bool, message=>string]
     */
    public function addUser(array $data): array
    {
        // Basic normalization
        $username = trim($data['username'] ?? '');
        $fullname = trim($data['fullname'] ?? '');
        $phone    = trim($data['phone'] ?? '');
        $password = (string)($data['password'] ?? '');
        $confirm  = (string)($data['confirm_password'] ?? '');
        $role     = strtolower(trim($data['role'] ?? 'customer')) === 'admin' ? 'admin' : 'customer';
        // Default status is always Active when adding new user
        $status   = 'Active';

        // Validate
        if ($fullname === '') {
            return ['success' => false, 'message' => 'Họ và tên không được để trống.'];
        }
        if ($phone === '' || !preg_match('/^0\d{9}$/', $phone)) {
            return ['success' => false, 'message' => 'Số điện thoại không hợp lệ. Phải có 10 chữ số và bắt đầu bằng số 0.'];
        }
        if ($role === 'admin') {
            // Admin must have username and password
            if ($username === '' || !preg_match('/^[A-Za-z0-9_\-.]{3,30}$/', $username)) {
                return ['success' => false, 'message' => 'Tên đăng nhập (admin) không hợp lệ.'];
            }
            if ($password === '' || $password !== $confirm) {
                return ['success' => false, 'message' => 'Mật khẩu (admin) bắt buộc và phải khớp xác nhận.'];
            }
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
            }
        } else {
            // Customer: username/password optional; if provided, validate
            if ($username !== '' && !preg_match('/^[A-Za-z0-9_\-.]{3,30}$/', $username)) {
                return ['success' => false, 'message' => 'Tên đăng nhập không hợp lệ.'];
            }
            if ($password !== '' || $confirm !== '') {
                if ($password !== $confirm) {
                    return ['success' => false, 'message' => 'Xác nhận mật khẩu không khớp.'];
                }
                if ($password !== '' && strlen($password) < 6) {
                    return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
                }
            }
        }

        // Ensure we have mysqli connection
        $conn = $this->dbConnection->getConnection();
        if (!$conn && method_exists($this->dbConnection, 'connect')) {
            $this->dbConnection->connect();
            $conn = $this->dbConnection->getConnection();
        }
        if (!$conn) return ['success' => false, 'message' => 'Không thể kết nối CSDL'];

        // Duplicate check: phone number
        $checkPhoneSql = 'SELECT 1 FROM users WHERE Phone = ? LIMIT 1';
        $stmtPhone = $conn->prepare($checkPhoneSql);
        if (!$stmtPhone) return ['success' => false, 'message' => 'Lỗi prepare kiểm tra trùng SĐT: ' . $conn->error];
        $stmtPhone->bind_param('s', $phone);
        $stmtPhone->execute();
        $resPhone = $stmtPhone->get_result();
        $dupePhone = $resPhone && $resPhone->num_rows > 0;
        if ($resPhone) $resPhone->free();
        $stmtPhone->close();
        if ($dupePhone) {
            return ['success' => false, 'message' => 'Số điện thoại đã được sử dụng bởi người dùng khác.'];
        }

        // Duplicate check: only when username provided
        if ($username !== '') {
            $checkSql = 'SELECT 1 FROM users WHERE Username = ? LIMIT 1';
            $stmt = $conn->prepare($checkSql);
            if (!$stmt) return ['success' => false, 'message' => 'Lỗi prepare kiểm tra trùng: ' . $conn->error];
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();
            $dupe = $res && $res->num_rows > 0;
            if ($res) $res->free();
            $stmt->close();
            if ($dupe) {
                return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.'];
            }
        }

        // Hash password if provided; allow NULL when empty
        $passwordHash = null;
        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        }

        // Build INSERT for users - only basic fields, no address
        $fields = ['Username','FullName','Phone','Role','Status','PasswordHash'];
        $placeholders = ['?','?','?','?','?','?'];
        $types = 'ssssss';
        // When role is customer and username empty, insert NULL username
        $usernameValue = ($username === '') ? null : $username;
        $params = [&$usernameValue, &$fullname, &$phone, &$role, &$status, &$passwordHash];

        $sql = 'INSERT INTO users (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Lỗi prepare thêm người dùng: ' . $conn->error];
        }

        // bind dynamically
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $err = $stmt->error;
        $stmt->close();

        if ($ok) {
            return ['success' => true, 'message' => 'Thêm người dùng thành công'];
        }
        return ['success' => false, 'message' => 'Không thể thêm người dùng: ' . $err];
    }

    /**
     * Get full user details for editing (basic info only, no address)
     */
    public function getUserDetails(string $username): array
    {
        // Ensure we have a mysqli connection
        $conn = $this->dbConnection->getConnection();
        if (!$conn && method_exists($this->dbConnection, 'connect')) {
            $this->dbConnection->connect();
            $conn = $this->dbConnection->getConnection();
        }
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối CSDL'];
        }

        $username = trim($username);

        // Get basic user info only
        $sql = "SELECT user_id, Username, FullName, Phone, Role, Status FROM users WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error];
        $stmt->bind_param('s', $username);
        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Lỗi thực thi truy vấn: ' . $stmt->error];
        }
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $res->free();
        $stmt->close();

        if (!$data) {
            return ['success' => false, 'message' => 'Không tìm thấy người dùng'];
        }

        // Normalize payload
        $payload = [
            'user_id' => (int)$data['user_id'],
            'username' => $data['Username'] ?? '',
            'fullname' => $data['FullName'] ?? '',
            'phone'    => $data['Phone'] ?? '',
            'role'     => (string)($data['Role'] ?? 'customer'),
            'status'   => (string)($data['Status'] ?? 'Active'),
        ];
        return ['success' => true, 'data' => $payload];
    }

    /**
     * Get full user details by user_id (basic info only, no address)
     */
    public function getUserDetailsById(int $userId): array
    {
        $conn = $this->dbConnection->getConnection();
        if (!$conn && method_exists($this->dbConnection, 'connect')) {
            $this->dbConnection->connect();
            $conn = $this->dbConnection->getConnection();
        }
        if (!$conn) return ['success' => false, 'message' => 'Không thể kết nối CSDL'];

        // Get basic user info only
        $sql = "SELECT user_id, Username, FullName, Phone, Role, Status FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error];
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Lỗi thực thi truy vấn: ' . $stmt->error];
        }
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $res->free();
        $stmt->close();

        if (!$data) return ['success' => false, 'message' => 'Không tìm thấy người dùng'];

        $payload = [
            'user_id' => (int)$data['user_id'],
            'username' => $data['Username'] ?? '',
            'fullname' => $data['FullName'] ?? '',
            'phone'    => $data['Phone'] ?? '',
            'role'     => (string)($data['Role'] ?? 'customer'),
            'status'   => (string)($data['Status'] ?? 'Active'),
        ];
        return ['success' => true, 'data' => $payload];
    }

    /**
     * Update user info and address
     */
    public function updateUser(array $data): array
    {
        // Ensure we have a connection
        if (!$this->dbConnection->getConnection()) {
            $this->dbConnection->connect();
        }

        // Keys and potential new username
        $userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        $username = trim($data['username'] ?? '');
        $newUsername = trim($data['new_username'] ?? '');
        $fullname = trim($data['fullname'] ?? '');
        $phone    = trim($data['phone'] ?? '');
        $roleIn   = strtolower(trim($data['role'] ?? 'customer'));
        $role     = ($roleIn === 'admin') ? 'admin' : 'customer';
        // Status may be omitted (null) to indicate "do not change"
        $status = null;
        if (array_key_exists('status', $data) && $data['status'] !== null) {
            $statusIn = strtolower(trim($data['status']));
            // DB enum is 'Active' or 'Block'
            $status = (in_array($statusIn, ['inactive','block','blocked','0'], true)) ? 'Block' : 'Active';
        }
        $password = (string)($data['password'] ?? '');
        $confirm  = (string)($data['confirm_password'] ?? '');

        // Session context for permissions
        $currentUser = (string)($data['_currentUser'] ?? '');
        $currentRole = (string)($data['_currentRole'] ?? '');

        if ((($userId <= 0) && $username === '') || $fullname === '' || $phone === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc'];
        }

        // Validate phone number format: 10 digits starting with 0
        if (!preg_match('/^0\d{9}$/', $phone)) {
            return ['success' => false, 'message' => 'Số điện thoại không hợp lệ. Phải có 10 chữ số và bắt đầu bằng số 0.'];
        }

        // Check user exists using OOP method
        try {
            if ($userId > 0) {
                $res = $this->dbConnection->queryPrepared(
                    'SELECT user_id, Username, Role FROM users WHERE user_id = ?',
                    [$userId],
                    'i'
                );
            } else {
                $res = $this->dbConnection->queryPrepared(
                    'SELECT user_id, Username, Role FROM users WHERE Username = ?',
                    [$username],
                    's'
                );
            }
            
            $rowUser = $res->fetch_assoc();
            $exists = (bool)$rowUser;
            $res->free();
            
            if (!$exists) {
                return ['success' => false, 'message' => 'Người dùng không tồn tại'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi truy vấn người dùng: ' . $e->getMessage()];
        }

        $targetRole = (string)($rowUser['Role'] ?? 'customer');
        $username = $rowUser['Username'] ?? $username; // normalize current username from DB
        $userId = (int)($rowUser['user_id'] ?? $userId);

        // Keep existing role from database - don't allow role changes via this update
        $role = $targetRole;

        // Check if phone number is already used by another user using OOP method
        try {
            $resPhone = $this->dbConnection->queryPrepared(
                'SELECT user_id FROM users WHERE Phone = ? AND user_id != ? LIMIT 1',
                [$phone, $userId],
                'si'
            );
            
            $phoneDuplicate = $resPhone->fetch_assoc();
            $resPhone->free();
            
            if ($phoneDuplicate) {
                return ['success' => false, 'message' => 'Số điện thoại đã được sử dụng bởi người dùng khác.'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi kiểm tra số điện thoại: ' . $e->getMessage()];
        }

        // Permission rules - accept both 'admin' from DB and 'nhân viên' from session
        $isSelfAdmin = (($currentRole === 'admin' || $currentRole === 'nhân viên') && $currentUser !== '' && strcasecmp($currentUser, $username) === 0);
        $isEditingAnotherAdmin = ($targetRole === 'admin' && !$isSelfAdmin);
        
        // Simplify: Don't allow username changes at all
        // Always keep the existing username
        $newUsername = $username;
        
        // Don't allow password changes through this form
        $willChangePassword = false;

        // Get mysqli connection for transaction
        $conn = $this->dbConnection->getConnection();
        
        // Begin transaction
        $conn->begin_transaction();
        try {
            // Update ONLY FullName and Phone - keep Username, Role, Status unchanged
            $this->dbConnection->queryPrepared(
                'UPDATE users SET FullName = ?, Phone = ? WHERE user_id = ?',
                [$fullname, $phone, $userId],
                'ssi'
            );

            $conn->commit();
            return ['success' => true, 'message' => 'Cập nhật thông tin thành công'];
        } catch (Exception $ex) {
            $conn->rollback();
            return ['success' => false, 'message' => $ex->getMessage()];
        }
    }

    /**
     * Toggle user status (Block/Active) - soft delete
     * Expects keys: user_id, _currentUser, _currentRole
     * Returns [success=>bool, message=>string]
     */
    public function toggleUserStatus(array $data): array
    {
        // Ensure we have a mysqli connection
        $conn = $this->dbConnection->getConnection();
        if (!$conn && method_exists($this->dbConnection, 'connect')) {
            $this->dbConnection->connect();
            $conn = $this->dbConnection->getConnection();
        }
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối CSDL'];
        }

        $userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $currentUser = strtolower(trim((string)($data['_currentUser'] ?? '')));
    $currentRole = strtolower(trim((string)($data['_currentRole'] ?? '')));

        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Thiếu hoặc không hợp lệ user_id'];
        }

        // If role not present in session, try to resolve from DB by current username
        if ($currentRole === '' && $currentUser !== '') {
            $stmtR = $conn->prepare('SELECT Role FROM users WHERE Username = ? LIMIT 1');
            if ($stmtR) {
                $stmtR->bind_param('s', $currentUser);
                if ($stmtR->execute()) {
                    $resR = $stmtR->get_result();
                    $rowR = $resR ? $resR->fetch_assoc() : null;
                    if ($resR) $resR->free();
                    if ($rowR && isset($rowR['Role'])) {
                        $currentRole = strtolower(trim((string)$rowR['Role']));
                    }
                }
                $stmtR->close();
            }
        }

        // Only admin can toggle user status - check both 'admin' role from DB and 'nhân viên' from session
        if ($currentRole !== 'admin' && $currentRole !== 'nhân viên') {
            return ['success' => false, 'message' => 'Không có quyền khóa/mở khóa người dùng'];
        }

        // Check target user exists and fetch username/role/status
        $stmt = $conn->prepare('SELECT user_id, Username, Role, Status FROM users WHERE user_id = ?');
        if (!$stmt) return ['success' => false, 'message' => 'Lỗi truy vấn người dùng: ' . $conn->error];
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        if ($res) $res->free();
        $stmt->close();
        if (!$row) return ['success' => false, 'message' => 'Người dùng không tồn tại'];

        $targetUsername = strtolower(trim((string)($row['Username'] ?? '')));
        $currentStatus = $row['Status'] ?? 'Active';

        // Prevent blocking self
        if ($currentUser !== '' && $targetUsername !== '' && strcmp($currentUser, $targetUsername) === 0) {
            return ['success' => false, 'message' => 'Không thể khóa tài khoản đang đăng nhập'];
        }

        // Toggle status: Active <-> Block
        $newStatus = ($currentStatus === 'Active') ? 'Block' : 'Active';
        $action = ($newStatus === 'Block') ? 'khóa' : 'mở khóa';
        
        $ok = $this->dbConnection->queryPrepared('UPDATE users SET Status = ? WHERE user_id = ?', [$newStatus, $userId], 'si');
        if ($ok) return ['success' => true, 'message' => 'Đã ' . $action . ' người dùng thành công'];
        return ['success' => false, 'message' => 'Không thể ' . $action . ' người dùng'];
    }

    
}

?>
