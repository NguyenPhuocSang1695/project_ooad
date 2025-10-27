<?php
require_once '/connect.php';
$db = new DatabaseConnection();
$db->connect();
$conn = $db->getConnection();

$result = $conn->query("SELECT * FROM voucher");
?>
<h2>Danh sách Voucher</h2>
<a href="addVoucher.php">+ Thêm voucher mới</a>
<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Giảm (%)</th>
        <th>Điều kiện</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['percen_decrease'] ?>%</td>
            <td><?= $row['condition'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <a href="editVoucher.php?id=<?= $row['id'] ?>">Sửa</a> |
                <a href="deleteVoucher.php?id=<?= $row['id'] ?>" onclick="return confirm('Xóa voucher này?')">Xóa</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>