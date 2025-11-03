<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/User.php';

class UserManager
{
    private $dbConnection;

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
     * Get orders for a username with pagination
     * @return array [orders=>[], total=>int]
     */
    public function getUserOrders(string $username, int $offset = 0, int $limit = 10): array
    {
        // Ensure connection
        $conn = $this->dbConnection->getConnection();
        if (!$conn && method_exists($this->dbConnection, 'connect')) {
            $this->dbConnection->connect();
            $conn = $this->dbConnection->getConnection();
        }
        if (!$conn) return ['orders' => [], 'total' => 0];

        // Count
        $sqlCount = 'SELECT COUNT(*) AS total FROM orders WHERE Username = ?';
        $resC = $this->dbConnection->queryPrepared($sqlCount, [$username], 's');
        $rowC = $resC ? $resC->fetch_assoc() : ['total' => 0];
        $total = (int)($rowC['total'] ?? 0);

        // Data
        $sql = "SELECT OrderID, Username, Status, PaymentMethod, CustomerName, Phone, DateGeneration, TotalAmount, address_id 
                FROM orders 
                WHERE Username = ? 
                ORDER BY DateGeneration DESC, OrderID DESC 
                LIMIT ?, ?";
        $res = $this->dbConnection->queryPrepared($sql, [$username, $offset, $limit], 'sii');
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
     * Expects keys: username, fullname, phone, password, role, status, province, district, ward, address
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
        $statusIn = isset($data['status']) ? (string)$data['status'] : '1';
        // Map to DB enum: Active | Block
        $status   = (strtolower($statusIn) === 'block' || $statusIn === '0' || strtolower($statusIn) === 'inactive') ? 'Block' : 'Active';
        $province = isset($data['province']) && $data['province'] !== '' ? (int)$data['province'] : 0;
        $district = isset($data['district']) && $data['district'] !== '' ? (int)$data['district'] : 0;
        $ward     = isset($data['ward']) && $data['ward'] !== '' ? (int)$data['ward'] : 0;
        $address  = trim($data['address'] ?? '');

        // Validate
        if ($fullname === '') {
            return ['success' => false, 'message' => 'Họ và tên không được để trống.'];
        }
        if ($phone === '' || !preg_match('/^0\d{9}$/', $phone)) {
            return ['success' => false, 'message' => 'Số điện thoại không hợp lệ.'];
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

        // Detect users table columns to support either address_id or Province/District/Ward/Address schemas
        $dbName = '';
        if ($resDb = $conn->query('SELECT DATABASE() AS db')) {
            $rowDb = $resDb->fetch_assoc();
            $dbName = $rowDb['db'] ?? '';
            $resDb->free();
        }

        $cols = [];
        if ($dbName !== '') {
            $safeDb = $conn->real_escape_string($dbName);
            $resCols = $conn->query("SELECT LOWER(COLUMN_NAME) AS col FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".$safeDb."' AND TABLE_NAME='users'");
            if ($resCols) {
                while ($r = $resCols->fetch_assoc()) { $cols[$r['col']] = true; }
                $resCols->free();
            }
        }

        $hasAddressId = isset($cols['addressid']) || isset($cols['address_id']);
        $hasProvinceSchema = isset($cols['province']) && isset($cols['district']) && isset($cols['ward']) && isset($cols['address']);

        // If schema uses address table, insert address first and get id
        $addressId = 0;
        if ($hasAddressId) {
            $sqlAddr = 'INSERT INTO address (ward_id, address_detail) VALUES (?, ?)';
            $stmtA = $conn->prepare($sqlAddr);
            if (!$stmtA) {
                return ['success' => false, 'message' => 'Lỗi prepare thêm địa chỉ: ' . $conn->error];
            }
            $stmtA->bind_param('is', $ward, $address);
            $okA = $stmtA->execute();
            $errA = $stmtA->error;
            $addressId = $okA ? $conn->insert_id : 0;
            $stmtA->close();
            if (!$okA || $addressId <= 0) {
                return ['success' => false, 'message' => 'Không thể thêm địa chỉ: ' . $errA];
            }
        }

        // Build dynamic INSERT for users
    $fields = ['Username','FullName','Phone','Role','Status','PasswordHash'];
        $placeholders = ['?','?','?','?','?','?'];
        $types = 'ssssss';
        // When role is customer and username empty, insert NULL username
        $usernameValue = ($username === '') ? null : $username;
        $params = [&$usernameValue, &$fullname, &$phone, &$role, &$status, &$passwordHash];

        if ($hasAddressId) {
            // decide exact column name
            $addrCol = isset($cols['addressid']) ? 'AddressID' : 'address_id';
            $fields[] = $addrCol;
            $placeholders[] = '?';
            $types .= 'i';
            $params[] = &$addressId;
        } elseif ($hasProvinceSchema) {
            $fields = array_merge($fields, ['Province','District','Ward','Address']);
            $placeholders = array_merge($placeholders, ['?','?','?','?']);
            $types .= 'iiis';
            $params[] = &$province;
            $params[] = &$district;
            $params[] = &$ward;
            $params[] = &$address;
        }

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
        // Cleanup orphan address row if we created one and user insert failed
        if ($hasAddressId && $addressId > 0) {
            $conn->query('DELETE FROM address WHERE address_id=' . (int)$addressId);
        }
        return ['success' => false, 'message' => 'Không thể thêm người dùng: ' . $err];
    }

    /**
     * Detect users address schema and return details
     */
    private function detectAddressSchema(mysqli $conn): array
    {
        $result = [
            'hasAddressId' => false,
            'addressIdColumn' => null,
            'hasProvinceSchema' => false,
        ];

        $dbName = '';
        if ($resDb = $conn->query('SELECT DATABASE() AS db')) {
            $rowDb = $resDb->fetch_assoc();
            $dbName = $rowDb['db'] ?? '';
            $resDb->free();
        }
        if ($dbName === '') return $result;

        $safeDb = $conn->real_escape_string($dbName);
        $cols = [];
        $resCols = $conn->query("SELECT LOWER(COLUMN_NAME) AS col FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".$safeDb."' AND TABLE_NAME='users'");
        if ($resCols) {
            while ($r = $resCols->fetch_assoc()) { $cols[$r['col']] = true; }
            $resCols->free();
        }

        $result['hasAddressId'] = isset($cols['addressid']) || isset($cols['address_id']);
        $result['addressIdColumn'] = isset($cols['addressid']) ? 'AddressID' : (isset($cols['address_id']) ? 'address_id' : null);
        $result['hasProvinceSchema'] = isset($cols['province']) && isset($cols['district']) && isset($cols['ward']) && isset($cols['address']);
        return $result;
    }

    /**
     * Get full user details including address hierarchy for editing
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

        $schema = $this->detectAddressSchema($conn);

        // Base select
        $username = trim($username);
        $data = null;

        if ($schema['hasAddressId'] && $schema['addressIdColumn']) {
            $sql = "SELECT u.user_id, u.Username, u.FullName, u.Phone, u.Role, u.Status,
                           u.".$schema['addressIdColumn']." AS address_id,
                           a.address_detail, a.ward_id,
                           w.district_id, d.province_id
                    FROM users u
                    LEFT JOIN address a ON a.address_id = u.".$schema['addressIdColumn']."
                    LEFT JOIN ward w ON w.ward_id = a.ward_id
                    LEFT JOIN district d ON d.district_id = w.district_id
                    WHERE u.UserName = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) return ['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error];
            $stmt->bind_param('s', $username);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                $data = $res->fetch_assoc();
                $res->free();
            }
            $stmt->close();
        } else {
            // Fallback: if inline address columns do not exist, select only existing base fields
            $sql = "SELECT u.user_id, u.Username, u.FullName, u.Phone, u.Role, u.Status FROM users u WHERE u.Username = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) return ['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error];
            $stmt->bind_param('s', $username);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                $data = $res->fetch_assoc();
                $res->free();
            }
            $stmt->close();
        }

        if (!$data) {
            return ['success' => false, 'message' => 'Không tìm thấy người dùng'];
        }

        // Normalize payload
        $payload = [
            'user_id' => isset($data['user_id']) ? (int)$data['user_id'] : null,
            'username' => $data['Username'] ?? '',
            'fullname' => $data['FullName'] ?? '',
            'email'    => $data['Email'] ?? '',
            'phone'    => $data['Phone'] ?? '',
            'role'     => (string)($data['Role'] ?? 'customer'),
            'status'   => (string)($data['Status'] ?? 'Active'),
            'address_id' => isset($data['address_id']) ? (int)$data['address_id'] : null,
            'address_detail' => $data['address_detail'] ?? '',
            'ward_id'  => isset($data['ward_id']) ? (int)$data['ward_id'] : null,
            'district_id' => isset($data['district_id']) ? (int)$data['district_id'] : null,
            'province_id' => isset($data['province_id']) ? (int)$data['province_id'] : null,
        ];
        return ['success' => true, 'data' => $payload];
    }

    /**
     * Get full user details by user_id
     */
    public function getUserDetailsById(int $userId): array
    {
        $conn = $this->dbConnection->getConnection();
        if (!$conn && method_exists($this->dbConnection, 'connect')) {
            $this->dbConnection->connect();
            $conn = $this->dbConnection->getConnection();
        }
        if (!$conn) return ['success' => false, 'message' => 'Không thể kết nối CSDL'];

        $schema = $this->detectAddressSchema($conn);
        $data = null;

        if ($schema['hasAddressId'] && $schema['addressIdColumn']) {
            $sql = "SELECT u.user_id, u.Username, u.FullName, u.Phone, u.Role, u.Status,
                           u.".$schema['addressIdColumn']." AS address_id,
                           a.address_detail, a.ward_id,
                           w.district_id, d.province_id
                    FROM users u
                    LEFT JOIN address a ON a.address_id = u.".$schema['addressIdColumn']."
                    LEFT JOIN ward w ON w.ward_id = a.ward_id
                    LEFT JOIN district d ON d.district_id = w.district_id
                    WHERE u.user_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) return ['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error];
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                $data = $res->fetch_assoc();
                $res->free();
            }
            $stmt->close();
        } else {
            $sql = "SELECT u.user_id, u.Username, u.FullName, u.Phone, u.Role, u.Status FROM users u WHERE u.user_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) return ['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error];
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                $data = $res->fetch_assoc();
                $res->free();
            }
            $stmt->close();
        }

        if (!$data) return ['success' => false, 'message' => 'Không tìm thấy người dùng'];

        $payload = [
            'user_id' => isset($data['user_id']) ? (int)$data['user_id'] : null,
            'username' => $data['Username'] ?? '',
            'fullname' => $data['FullName'] ?? '',
            'email'    => $data['Email'] ?? '',
            'phone'    => $data['Phone'] ?? '',
            'role'     => (string)($data['Role'] ?? 'customer'),
            'status'   => (string)($data['Status'] ?? 'Active'),
            'address_id' => isset($data['address_id']) ? (int)$data['address_id'] : null,
            'address_detail' => $data['address_detail'] ?? '',
            'ward_id'  => isset($data['ward_id']) ? (int)$data['ward_id'] : null,
            'district_id' => isset($data['district_id']) ? (int)$data['district_id'] : null,
            'province_id' => isset($data['province_id']) ? (int)$data['province_id'] : null,
        ];
        return ['success' => true, 'data' => $payload];
    }

    /**
     * Update user info and address
     */
    public function updateUser(array $data): array
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

    // Keys and potential new username
    $userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $username = trim($data['username'] ?? '');
        $newUsername = trim($data['new_username'] ?? '');
        $fullname = trim($data['fullname'] ?? '');
        $phone    = trim($data['phone'] ?? '');
        $roleIn   = strtolower(trim($data['role'] ?? 'customer'));
        $role     = ($roleIn === 'admin') ? 'admin' : 'customer';
        $statusIn = strtolower(trim($data['status'] ?? 'active'));
        // DB enum is 'Active' or 'Block'
        $status   = (in_array($statusIn, ['inactive','block','blocked','0'], true)) ? 'Block' : 'Active';
        $province = isset($data['province']) && $data['province'] !== '' ? (int)$data['province'] : null;
        $district = isset($data['district']) && $data['district'] !== '' ? (int)$data['district'] : null;
        $ward     = isset($data['ward']) && $data['ward'] !== '' ? (int)$data['ward'] : null;
        $address  = trim($data['address'] ?? '');
        $password = (string)($data['password'] ?? '');
        $confirm  = (string)($data['confirm_password'] ?? '');

        // Session context for permissions
        $currentUser = (string)($data['_currentUser'] ?? '');
        $currentRole = (string)($data['_currentRole'] ?? '');

        if ((($userId <= 0) && $username === '') || $fullname === '' || $phone === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc'];
        }

        // check user exists
        if ($userId > 0) {
            $stmt = $conn->prepare('SELECT user_id, Username, Role FROM users WHERE user_id = ?');
            if (!$stmt) return ['success' => false, 'message' => 'Lỗi truy vấn người dùng: ' . $conn->error];
            $stmt->bind_param('i', $userId);
        } else {
            $stmt = $conn->prepare('SELECT user_id, Username, Role FROM users WHERE Username = ?');
            if (!$stmt) return ['success' => false, 'message' => 'Lỗi truy vấn người dùng: ' . $conn->error];
            $stmt->bind_param('s', $username);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rowUser = $res->fetch_assoc();
        $exists = (bool)$rowUser;
        $res->free();
        $stmt->close();
        if (!$exists) return ['success' => false, 'message' => 'Người dùng không tồn tại'];

        $targetRole = (string)($rowUser['Role'] ?? 'customer');
    $username = $rowUser['Username'] ?? $username; // normalize current username from DB
        $userId = (int)($rowUser['user_id'] ?? $userId);

        // Permission rules
        $isSelfAdmin = ($currentRole === 'admin' && $currentUser !== '' && strcasecmp($currentUser, $username) === 0);
        $isEditingAnotherAdmin = ($targetRole === 'admin' && !$isSelfAdmin);

        // Username change validation
        $willChangeUsername = ($newUsername !== '' && strcasecmp($newUsername, $username) !== 0);
        if ($willChangeUsername) {
            if (!$isSelfAdmin) {
                return ['success' => false, 'message' => 'Chỉ admin được đổi username của chính mình'];
            }
            // Validate new username pattern
            if (!preg_match('/^[A-Za-z0-9_\-.]{3,32}$/', $newUsername)) {
                return ['success' => false, 'message' => 'Tên đăng nhập mới không hợp lệ'];
            }
            // Check duplicate username
            $stmtC = $conn->prepare('SELECT 1 FROM users WHERE Username = ? LIMIT 1');
            if (!$stmtC) return ['success' => false, 'message' => 'Lỗi kiểm tra username: ' . $conn->error];
            $stmtC->bind_param('s', $newUsername);
            $stmtC->execute();
            $resC = $stmtC->get_result();
            $dupe = (bool)$resC->fetch_row();
            $resC->free();
            $stmtC->close();
            if ($dupe) {
                return ['success' => false, 'message' => 'Tên đăng nhập mới đã tồn tại'];
            }
        } else {
            $newUsername = $username; // no change
        }

        // Password change validation: only admin editing self
        $willChangePassword = ($password !== '' || $confirm !== '');
        if ($willChangePassword) {
            if (!$isSelfAdmin) {
                return ['success' => false, 'message' => 'Chỉ admin được đổi mật khẩu của chính mình'];
            }
            if ($password !== $confirm) {
                return ['success' => false, 'message' => 'Xác nhận mật khẩu không khớp'];
            }
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
            }
        }

        $schema = $this->detectAddressSchema($conn);

        // Begin transaction
        $conn->begin_transaction();
        try {
            if ($schema['hasAddressId'] && $schema['addressIdColumn']) {
                // Get current address id
                $addrId = null;
                $sqlG = 'SELECT ' . $schema['addressIdColumn'] . ' AS address_id FROM users WHERE user_id = ?';
                $stmtG = $conn->prepare($sqlG);
                if (!$stmtG) throw new Exception('Lỗi prepare lấy địa chỉ: ' . $conn->error);
                $stmtG->bind_param('i', $userId);
                $stmtG->execute();
                $resG = $stmtG->get_result();
                if ($rowG = $resG->fetch_assoc()) { $addrId = (int)$rowG['address_id']; }
                $resG->free();
                $stmtG->close();

                if ($addrId && $addrId > 0) {
                    // Update existing address
                    $sqlA = 'UPDATE address SET ward_id = ?, address_detail = ? WHERE address_id = ?';
                    $stmtA = $conn->prepare($sqlA);
                    if (!$stmtA) throw new Exception('Lỗi prepare cập nhật địa chỉ: ' . $conn->error);
                    $stmtA->bind_param('isi', $ward, $address, $addrId);
                    if (!$stmtA->execute()) throw new Exception('Lỗi cập nhật địa chỉ: ' . $stmtA->error);
                    $stmtA->close();
                } else {
                    // Create new address and set for user
                    $sqlInsA = 'INSERT INTO address (ward_id, address_detail) VALUES (?, ?)';
                    $stmtIA = $conn->prepare($sqlInsA);
                    if (!$stmtIA) throw new Exception('Lỗi prepare thêm địa chỉ: ' . $conn->error);
                    $stmtIA->bind_param('is', $ward, $address);
                    if (!$stmtIA->execute()) throw new Exception('Lỗi thêm địa chỉ: ' . $stmtIA->error);
                    $addrId = $conn->insert_id;
                    $stmtIA->close();

                    $sqlSet = 'UPDATE users SET ' . $schema['addressIdColumn'] . ' = ? WHERE user_id = ?';
                    $stmtSet = $conn->prepare($sqlSet);
                    if (!$stmtSet) throw new Exception('Lỗi prepare gán địa chỉ: ' . $conn->error);
                    $stmtSet->bind_param('ii', $addrId, $userId);
                    if (!$stmtSet->execute()) throw new Exception('Lỗi gán địa chỉ cho người dùng: ' . $stmtSet->error);
                    $stmtSet->close();
                }

                // Update user non-address fields (no Email in schema)
                $sqlU = 'UPDATE users SET FullName = ?, Phone = ?, Role = ?, Status = ?, Username = ? WHERE user_id = ?';
                $stmtU = $conn->prepare($sqlU);
                if (!$stmtU) throw new Exception('Lỗi prepare cập nhật người dùng: ' . $conn->error);
                $stmtU->bind_param('sssssi', $fullname, $phone, $role, $status, $newUsername, $userId);
                if (!$stmtU->execute()) throw new Exception('Lỗi cập nhật người dùng: ' . $stmtU->error);
                $stmtU->close();
            } else {
                // No users address columns in current schema -> update only base fields
                $sqlU = 'UPDATE users SET FullName = ?, Phone = ?, Role = ?, Status = ?, Username = ? WHERE user_id = ?';
                $stmtU = $conn->prepare($sqlU);
                if (!$stmtU) throw new Exception('Lỗi prepare cập nhật người dùng: ' . $conn->error);
                $stmtU->bind_param('sssssi', $fullname, $phone, $role, $status, $newUsername, $userId);
                if (!$stmtU->execute()) throw new Exception('Lỗi cập nhật người dùng: ' . $stmtU->error);
                $stmtU->close();
            }

            // Update password if required
            if ($willChangePassword) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $sqlP = 'UPDATE users SET PasswordHash = ? WHERE user_id = ?';
                $stmtP = $conn->prepare($sqlP);
                if (!$stmtP) throw new Exception('Lỗi prepare cập nhật mật khẩu: ' . $conn->error);
                $stmtP->bind_param('si', $hash, $userId);
                if (!$stmtP->execute()) throw new Exception('Lỗi cập nhật mật khẩu: ' . $stmtP->error);
                $stmtP->close();
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Cập nhật người dùng thành công'];
        } catch (Exception $ex) {
            $conn->rollback();
            return ['success' => false, 'message' => $ex->getMessage()];
        }
    }

    
}

?>
