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
        $sql = "SELECT Username, FullName, Phone, Email, Status, Role 
                FROM users 
                WHERE Username LIKE ? 
                   OR FullName LIKE ? 
                   OR Phone LIKE ? 
                   OR Email LIKE ?
                ORDER BY CASE WHEN Role = 'admin' THEN 0 ELSE 1 END, Role 
                LIMIT ?, ?";
        
        $res = $this->dbConnection->queryPrepared(
            $sql, 
            [$keyword, $keyword, $keyword, $keyword, (int)$offset, (int)$limit],
            "ssssii"
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
                   OR Phone LIKE ? 
                   OR Email LIKE ?";
        
        $res = $this->dbConnection->queryPrepared(
            $sql, 
            [$keyword, $keyword, $keyword, $keyword],
            "ssss"
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
            $sql = "SELECT Username, FullName, Phone, Email, Status, Role FROM users WHERE Role = ? ORDER BY CASE WHEN Role = 'admin' THEN 0 ELSE 1 END, Role LIMIT ?, ?";
            $res = $this->dbConnection->queryPrepared($sql, [$role, (int)$offset, (int)$limit], "sii");
        } else {
            $sql = "SELECT Username, FullName, Phone, Email, Status, Role FROM users ORDER BY CASE WHEN Role = 'admin' THEN 0 ELSE 1 END, Role LIMIT ?, ?";
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
     * Expects keys: username, fullname, email, phone, password, role, status, province, district, ward, address
     * Returns [success=>bool, message=>string]
     */
    public function addUser(array $data): array
    {
        // Basic normalization
        $username = trim($data['username'] ?? '');
        $fullname = trim($data['fullname'] ?? '');
        $email    = trim($data['email'] ?? '');
        $phone    = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $confirm  = $data['confirm_password'] ?? '';
        $role     = strtolower(trim($data['role'] ?? 'customer')) === 'admin' ? 'admin' : 'customer';
    $statusIn = isset($data['status']) ? (string)$data['status'] : '1';
    // Map to DB enum: Active | Block
    $status   = (strtolower($statusIn) === 'block' || $statusIn === '0' || strtolower($statusIn) === 'inactive') ? 'Block' : 'Active';
    $province = isset($data['province']) && $data['province'] !== '' ? (int)$data['province'] : 0;
    $district = isset($data['district']) && $data['district'] !== '' ? (int)$data['district'] : 0;
    $ward     = isset($data['ward']) && $data['ward'] !== '' ? (int)$data['ward'] : 0;
        $address  = trim($data['address'] ?? '');

        // Validate
        if ($username === '' || !preg_match('/^[A-Za-z0-9_\-\.]{3,32}$/', $username)) {
            return ['success' => false, 'message' => 'Tên đăng nhập không hợp lệ.'];
        }
        if ($fullname === '') {
            return ['success' => false, 'message' => 'Họ và tên không được để trống.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không hợp lệ.'];
        }
        if ($phone !== '' && !preg_match('/^0\d{9}$/', $phone)) {
            return ['success' => false, 'message' => 'Số điện thoại không hợp lệ.'];
        }
        if ($password === '' || $password !== $confirm) {
            return ['success' => false, 'message' => 'Mật khẩu và xác nhận mật khẩu không khớp.'];
        }

        // Use raw mysqli to avoid die() inside helper and surface actual errors
        $conn = $this->dbConnection->getConnection();
        if (!$conn) {
            // try connect if not already
            if (method_exists($this->dbConnection, 'connect')) {
                $this->dbConnection->connect();
                $conn = $this->dbConnection->getConnection();
            }
        }

        // Duplicate checks (username/email) - use column name UserName to be safe
        $checkSql = "SELECT 1 FROM users WHERE UserName = ? OR Email = ? LIMIT 1";
        $stmt = $conn->prepare($checkSql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Lỗi prepare kiểm tra trùng: ' . $conn->error];
        }
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Tên đăng nhập hoặc email đã tồn tại.'];
        }
        $stmt->close();

        // Hash password (use bcrypt)
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

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
        $fields = ['UserName','FullName','Email','Phone','Role','Status','PasswordHash'];
        $placeholders = ['?','?','?','?','?','?','?'];
        $types = 'sssssss';
        $params = [&$username, &$fullname, &$email, &$phone, &$role, &$status, &$passwordHash];

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
            $sql = "SELECT u.UserName, u.FullName, u.Email, u.Phone, u.Role, u.Status,
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
            // Fallback inline address columns
            $sql = "SELECT u.UserName, u.FullName, u.Email, u.Phone, u.Role, u.Status,
                           u.Address AS address_detail, u.Ward AS ward_id,
                           u.District AS district_id, u.Province AS province_id
                    FROM users u WHERE u.UserName = ?";
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
            'username' => $data['UserName'] ?? '',
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

        $username = trim($data['username'] ?? '');
        $fullname = trim($data['fullname'] ?? '');
        $email    = trim($data['email'] ?? '');
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

        if ($username === '' || $fullname === '' || $email === '' || $phone === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc'];
        }

        // check user exists
        $stmt = $conn->prepare('SELECT 1 FROM users WHERE UserName = ?');
        if (!$stmt) return ['success' => false, 'message' => 'Lỗi truy vấn người dùng: ' . $conn->error];
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = (bool)$res->fetch_row();
        $res->free();
        $stmt->close();
        if (!$exists) return ['success' => false, 'message' => 'Người dùng không tồn tại'];

        $schema = $this->detectAddressSchema($conn);

        // Begin transaction
        $conn->begin_transaction();
        try {
            if ($schema['hasAddressId'] && $schema['addressIdColumn']) {
                // Get current address id
                $addrId = null;
                $sqlG = 'SELECT ' . $schema['addressIdColumn'] . ' AS address_id FROM users WHERE UserName = ?';
                $stmtG = $conn->prepare($sqlG);
                if (!$stmtG) throw new Exception('Lỗi prepare lấy địa chỉ: ' . $conn->error);
                $stmtG->bind_param('s', $username);
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

                    $sqlSet = 'UPDATE users SET ' . $schema['addressIdColumn'] . ' = ? WHERE UserName = ?';
                    $stmtSet = $conn->prepare($sqlSet);
                    if (!$stmtSet) throw new Exception('Lỗi prepare gán địa chỉ: ' . $conn->error);
                    $stmtSet->bind_param('is', $addrId, $username);
                    if (!$stmtSet->execute()) throw new Exception('Lỗi gán địa chỉ cho người dùng: ' . $stmtSet->error);
                    $stmtSet->close();
                }

                // Update user non-address fields
                $sqlU = 'UPDATE users SET FullName = ?, Email = ?, Phone = ?, Role = ?, Status = ? WHERE UserName = ?';
                $stmtU = $conn->prepare($sqlU);
                if (!$stmtU) throw new Exception('Lỗi prepare cập nhật người dùng: ' . $conn->error);
                $stmtU->bind_param('ssssss', $fullname, $email, $phone, $role, $status, $username);
                if (!$stmtU->execute()) throw new Exception('Lỗi cập nhật người dùng: ' . $stmtU->error);
                $stmtU->close();
            } else {
                // Inline address columns
                $sqlU = 'UPDATE users SET FullName = ?, Email = ?, Phone = ?, Role = ?, Status = ?, Province = ?, District = ?, Ward = ?, Address = ? WHERE UserName = ?';
                $stmtU = $conn->prepare($sqlU);
                if (!$stmtU) throw new Exception('Lỗi prepare cập nhật người dùng: ' . $conn->error);
                $stmtU->bind_param('sssssiiiss', $fullname, $email, $phone, $role, $status, $province, $district, $ward, $address, $username);
                if (!$stmtU->execute()) throw new Exception('Lỗi cập nhật người dùng: ' . $stmtU->error);
                $stmtU->close();
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
