<?php
require_once 'connect.php';

class Account
{
    private $conn;
    private $username;

    public function __construct($username = null)
    {
        $db = new DatabaseConnection();
        $db->connect();
        $this->conn = $db->getConnection();
        $this->username = $username;
    }

    /**
     * Lấy thông tin tài khoản đầy đủ
     * @return array|null Thông tin tài khoản hoặc null nếu không tìm thấy
     */
    public function getAccountInfo()
    {
        $sql = "SELECT u.Username, u.FullName, u.Email, u.Role, u.Phone, u.address_id, 
                a.address_detail, a.ward_id,
                pr.province_id, pr.name as province_name, 
                dr.district_id, dr.name as district_name, 
                w.ward_id, w.name as ward_name
                FROM users u
                LEFT JOIN address a ON u.address_id = a.address_id
                LEFT JOIN ward w ON a.ward_id = w.ward_id
                LEFT JOIN district dr ON w.district_id = dr.district_id
                LEFT JOIN province pr ON dr.province_id = pr.province_id
                WHERE u.Username = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $this->username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    /**
     * Cập nhật thông tin tài khoản
     * @param array $data Dữ liệu cần cập nhật
     * @return array Kết quả cập nhật
     */
    public function updateAccount($data)
    {
        try {
            // Validate dữ liệu đầu vào
            $validationResult = $this->validateAccountData($data);
            if (!$validationResult['success']) {
                return $validationResult;
            }

            $this->conn->begin_transaction();

            // Cập nhật thông tin user
            $updateUserResult = $this->updateUserInfo(
                $data['fullname'],
                $data['phone'],
                $data['email']
            );

            if ($updateUserResult) {
                $this->conn->commit();
                return [
                    'success' => true,
                    'message' => 'Cập nhật thông tin thành công',
                    'data' => [
                        'fullname' => $data['fullname'],
                        'phone' => $data['phone'],
                        'email' => $data['email']
                    ]
                ];
            } else {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Không có thay đổi nào được thực hiện'];
            }
        } catch (Exception $e) {
            if ($this->conn) {
                $this->conn->rollback();
            }
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    /**
     * Validate dữ liệu tài khoản
     * @param array $data Dữ liệu cần validate
     * @return array Kết quả validate
     */
    private function validateAccountData($data)
    {
        // Kiểm tra các trường bắt buộc
        if (empty($data['fullname']) || empty($data['phone']) || empty($data['email'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        }

        // Validate phone (10-11 số)
        if (!preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
            return ['success' => false, 'message' => 'Số điện thoại không hợp lệ (phải từ 10-11 chữ số)'];
        }

        // Validate họ tên (không chứa số và ký tự đặc biệt)
        if (!preg_match('/^[a-zA-ZÀ-ỹ\s]+$/u', $data['fullname'])) {
            return ['success' => false, 'message' => 'Họ tên không hợp lệ'];
        }

        return ['success' => true];
    }

    /**
     * Lấy address_id của user
     * @return int|null address_id hoặc null
     */

    /**
     * Cập nhật thông tin địa chỉ
     * @param int $addressId ID địa chỉ
     * @param string $addressDetail Chi tiết địa chỉ
     * @param string $wardId Mã phường/xã
     * @return bool Kết quả cập nhật
     */
    private function updateAddress($addressId, $addressDetail, $wardId)
    {
        $sql = "UPDATE address SET address_detail = ?, ward_id = ? WHERE address_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $addressDetail, $wardId, $addressId);
        return $stmt->execute();
    }

    /**
     * Cập nhật thông tin user
     * @param string $fullName Họ tên
     * @param string $phone Số điện thoại
     * @param string $email Email
     * @return bool Kết quả cập nhật
     */
    private function updateUserInfo($fullName, $phone, $email)
    {
        $sql = "UPDATE users SET FullName = ?, Phone = ?, Email = ? WHERE Username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $fullName, $phone, $email, $this->username);
        $stmt->execute();

        return $stmt->affected_rows >= 0;
    }


    /**
     * Đổi mật khẩu
     * @param string $oldPassword Mật khẩu cũ
     * @param string $newPassword Mật khẩu mới
     * @return array Kết quả đổi mật khẩu
     */
    public function changePassword($oldPassword, $newPassword)
    {
        try {
            // Kiểm tra mật khẩu cũ
            $sql = "SELECT Password FROM users WHERE Username = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $this->username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'Không tìm thấy tài khoản'];
            }

            $user = $result->fetch_assoc();

            // Verify mật khẩu cũ
            if (!password_verify($oldPassword, $user['Password'])) {
                return ['success' => false, 'message' => 'Mật khẩu cũ không đúng'];
            }

            // Validate mật khẩu mới
            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'];
            }

            // Hash mật khẩu mới
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Cập nhật mật khẩu
            $sql = "UPDATE users SET Password = ? WHERE Username = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $hashedPassword, $this->username);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
            } else {
                return ['success' => false, 'message' => 'Lỗi khi đổi mật khẩu'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy danh sách tỉnh/thành phố
     * @return array Danh sách tỉnh
     */


    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
