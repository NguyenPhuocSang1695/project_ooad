<?php
require_once './connect.php';
require_once './Voucher.php';
/**
 * Class VoucherManager - Quản lý các thao tác với voucher
 */
class VoucherManager extends Voucher
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseConnection();
        $this->db->connect();
    }

    /**
     * Thêm voucher mới
     * @return array ['success' => bool, 'message' => string]
     */
    public function addVoucher($name, $percen_decrease, $conditions, $status)
    {
        // Validate dữ liệu
        $name = trim($name);
        if (empty($name) || $percen_decrease === '' || $conditions === '' || empty($status)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin!'
            ];
        }

        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("INSERT INTO vouchers (name, percen_decrease, conditions, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdis", $name, $percen_decrease, $conditions, $status);

            if ($stmt->execute()) {
                $insertedId = $conn->insert_id;
                $stmt->close();

                return [
                    'success' => true,
                    'message' => 'Thêm voucher thành công!',
                    'id' => $insertedId
                ];
            } else {
                $stmt->close();
                return [
                    'success' => false,
                    'message' => 'Lỗi khi thêm voucher!'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cập nhật voucher
     * @return array ['success' => bool, 'message' => string]
     */
    public function editVoucher($id, $name = null, $percen_decrease = null, $conditions = null, $status = null)
    {
        if (empty($id)) {
            return [
                'success' => false,
                'message' => 'Thiếu ID voucher.'
            ];
        }

        try {
            // Lấy dữ liệu hiện tại
            $oldData = $this->db->queryPrepared("SELECT * FROM vouchers WHERE id = ?", [$id], "i")->fetch_assoc();

            if (!$oldData) {
                return [
                    'success' => false,
                    'message' => 'Voucher không tồn tại.'
                ];
            }

            // Sử dụng giá trị mới nếu có, nếu không giữ giá trị cũ
            $name = $name !== null ? trim($name) : $oldData['name'];
            $percen_decrease = $percen_decrease !== null ? $percen_decrease : $oldData['percen_decrease'];
            $conditions = $conditions !== null ? $conditions : $oldData['conditions'];
            $status = $status !== null ? $status : $oldData['status'];

            // Cập nhật
            $sql = "UPDATE vouchers 
                    SET name = ?, 
                        percen_decrease = ?, 
                        conditions = ?, 
                        status = ? 
                    WHERE id = ?";
            $params = [$name, $percen_decrease, $conditions, $status, $id];
            $types = "sdisi";

            $result = $this->db->queryPrepared($sql, $params, $types);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Cập nhật voucher thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không có thay đổi nào hoặc lỗi khi cập nhật.'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Xóa voucher
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteVoucher($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID voucher không hợp lệ!'
            ];
        }

        try {
            $conn = $this->db->getConnection();

            // Kiểm tra voucher có tồn tại
            $check_stmt = $conn->prepare("SELECT * FROM vouchers WHERE id = ?");
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows === 0) {
                $check_stmt->close();
                return [
                    'success' => false,
                    'message' => 'Voucher không tồn tại hoặc đã bị xóa!'
                ];
            }
            $check_stmt->close();

            // Tiến hành xóa
            $delete_stmt = $conn->prepare("DELETE FROM vouchers WHERE id = ?");
            $delete_stmt->bind_param("i", $id);

            if ($delete_stmt->execute()) {
                $delete_stmt->close();
                return [
                    'success' => true,
                    'message' => 'Xóa voucher thành công!'
                ];
            } else {
                $delete_stmt->close();
                return [
                    'success' => false,
                    'message' => 'Xóa voucher thất bại!'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thông tin voucher theo ID
     * @return Voucher|null
     */
    public function getVoucherById($id)
    {
        try {
            $result = $this->db->queryPrepared("SELECT * FROM vouchers WHERE id = ?", [$id], "i");
            $data = $result->fetch_assoc();

            if ($data) {
                return new Voucher(
                    $data['id'],
                    $data['name'],
                    $data['percen_decrease'],
                    $data['conditions'],
                    $data['status']
                );
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Lấy danh sách tất cả voucher
     * @return array
     */
    public function getAllVouchers()
    {
        try {
            $result = $this->db->query("SELECT * FROM vouchers");
            $vouchers = [];

            while ($row = $result->fetch_assoc()) {
                $vouchers[] = new Voucher(
                    $row['id'],
                    $row['name'],
                    $row['percen_decrease'],
                    $row['conditions'],
                    $row['status']
                );
            }

            return $vouchers;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Đóng kết nối database
     */
    public function closeConnection()
    {
        $this->db->close();
    }

    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
