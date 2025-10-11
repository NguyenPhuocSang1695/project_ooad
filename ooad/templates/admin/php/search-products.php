<?php
header('Content-Type: application/json');
require_once 'connect.php';

function removeVietnameseDiacritics($str)
{
    $diacritics = array(
        'à' => 'a',
        'á' => 'a',
        'ạ' => 'a',
        'ả' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ầ' => 'a',
        'ấ' => 'a',
        'ậ' => 'a',
        'ẩ' => 'a',
        'ẫ' => 'a',
        'ă' => 'a',
        'ằ' => 'a',
        'ắ' => 'a',
        'ặ' => 'a',
        'ẳ' => 'a',
        'ẵ' => 'a',
        'è' => 'e',
        'é' => 'e',
        'ẹ' => 'e',
        'ẻ' => 'e',
        'ẽ' => 'e',
        'ê' => 'e',
        'ề' => 'e',
        'ế' => 'e',
        'ệ' => 'e',
        'ể' => 'e',
        'ễ' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'ị' => 'i',
        'ỉ' => 'i',
        'ĩ' => 'i',
        'ò' => 'o',
        'ó' => 'o',
        'ọ' => 'o',
        'ỏ' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ồ' => 'o',
        'ố' => 'o',
        'ộ' => 'o',
        'ổ' => 'o',
        'ỗ' => 'o',
        'ơ' => 'o',
        'ờ' => 'o',
        'ớ' => 'o',
        'ợ' => 'o',
        'ở' => 'o',
        'ỡ' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'ụ' => 'u',
        'ủ' => 'u',
        'ũ' => 'u',
        'ư' => 'u',
        'ừ' => 'u',
        'ứ' => 'u',
        'ự' => 'u',
        'ử' => 'u',
        'ữ' => 'u',
        'ỳ' => 'y',
        'ý' => 'y',
        'ỵ' => 'y',
        'ỷ' => 'y',
        'ỹ' => 'y',
        'đ' => 'd',
        'À' => 'A',
        'Á' => 'A',
        'Ạ' => 'A',
        'Ả' => 'A',
        'Ã' => 'A',
        'Â' => 'A',
        'Ầ' => 'A',
        'Ấ' => 'A',
        'Ậ' => 'A',
        'Ẩ' => 'A',
        'Ẫ' => 'A',
        'Ă' => 'A',
        'Ằ' => 'A',
        'Ắ' => 'A',
        'Ặ' => 'A',
        'Ẳ' => 'A',
        'Ẵ' => 'A',
        'È' => 'E',
        'É' => 'E',
        'Ẹ' => 'E',
        'Ẻ' => 'E',
        'Ẽ' => 'E',
        'Ê' => 'E',
        'Ề' => 'E',
        'Ế' => 'E',
        'Ệ' => 'E',
        'Ể' => 'E',
        'Ễ' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Ị' => 'I',
        'Ỉ' => 'I',
        'Ĩ' => 'I',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ọ' => 'O',
        'Ỏ' => 'O',
        'Õ' => 'O',
        'Ô' => 'O',
        'Ồ' => 'O',
        'Ố' => 'O',
        'Ộ' => 'O',
        'Ổ' => 'O',
        'Ỗ' => 'O',
        'Ơ' => 'O',
        'Ờ' => 'O',
        'Ớ' => 'O',
        'Ợ' => 'O',
        'Ở' => 'O',
        'Ỡ' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Ụ' => 'U',
        'Ủ' => 'U',
        'Ũ' => 'U',
        'Ư' => 'U',
        'Ừ' => 'U',
        'Ứ' => 'U',
        'Ự' => 'U',
        'Ử' => 'U',
        'Ữ' => 'U',
        'Ỳ' => 'Y',
        'Ý' => 'Y',
        'Ỵ' => 'Y',
        'Ỷ' => 'Y',
        'Ỹ' => 'Y',
        'Đ' => 'D'
    );
    return strtr($str, $diacritics);
}

function normalizeSearchString($str)
{
    // Chuyển về chữ thường
    $str = mb_strtolower($str, 'UTF-8');
    // Loại bỏ các ký tự đặc biệt
    $str = preg_replace('/[^a-zA-Z0-9àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ\s]/u', '', $str);
    return $str;
}

function calculateRelevanceScore($productName, $searchTerm)
{
    // Nếu không có từ khóa tìm kiếm, trả về điểm mặc định
    if (empty($searchTerm)) {
        return 1;
    }

    $score = 0;
    $searchTermLower = mb_strtolower($searchTerm, 'UTF-8');
    $productNameLower = mb_strtolower($productName, 'UTF-8');

    // Tính điểm cho tìm kiếm một ký tự
    if (mb_strlen($searchTermLower) === 1) {
        if (mb_strpos($productNameLower, $searchTermLower) !== false) {
            $score += 30;
        }
        return $score;
    }

    // Tính điểm cho tìm kiếm nhiều ký tự
    $namePos = mb_strpos($productNameLower, $searchTermLower);
    if ($namePos !== false) {
        $score += 100 - min($namePos, 50);
    }

    // Kiểm tra từ khóa có phải là tiền tố của tên sản phẩm
    if (mb_strpos($productNameLower, $searchTermLower) === 0) {
        $score += 50;
    }

    // Điểm cho độ dài phù hợp của tên sản phẩm
    $lengthDiff = abs(mb_strlen($productNameLower) - mb_strlen($searchTermLower));
    $score += max(0, 30 - $lengthDiff);

    return $score;
}

$searchTerm = isset($_POST['search']) ? trim($_POST['search']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$itemsPerPage = 5;
$offset = ($page - 1) * $itemsPerPage;
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

try {
    // Query cơ bản để lấy tất cả sản phẩm khi không có từ khóa
    $baseQuery = "SELECT p.ProductID, p.ProductName, p.Price, p.ImageURL, p.Status, 
                         c.CategoryName, p.Description
                  FROM products p 
                  LEFT JOIN categories c ON p.CategoryID = c.CategoryID 
                  WHERE (p.Status = 'appear' OR p.Status = 'hidden')";

    if ($product_id) {
        $baseQuery .= " AND p.ProductID = $product_id";
    } else if (!empty($searchTerm)) {
        // Chuẩn hóa chuỗi tìm kiếm khi có từ khóa
        $normalizedSearchTerm = normalizeSearchString($searchTerm);
        $searchTermNoAccents = removeVietnameseDiacritics($normalizedSearchTerm);

        if (mb_strlen($searchTerm) === 1) {
            // Xử lý đặc biệt cho tìm kiếm 1 ký tự
            $baseQuery .= " AND p.ProductName LIKE ?";
            $searchPattern = '%' . $searchTerm . '%';
            $stmt = $myconn->prepare($baseQuery . " ORDER BY p.ProductName ASC LIMIT ? OFFSET ?");
            $stmt->bind_param('sii', $searchPattern, $itemsPerPage, $offset);
        } else {
            // Xử lý tìm kiếm nhiều ký tự
            $baseQuery .= " AND (p.ProductName LIKE ? OR p.ProductName LIKE ?)";
            $searchPattern = '%' . $normalizedSearchTerm . '%';
            $searchPatternNoAccents = '%' . $searchTermNoAccents . '%';
            $stmt = $myconn->prepare($baseQuery . " ORDER BY p.ProductName ASC LIMIT ? OFFSET ?");
            $stmt->bind_param('ssii', $searchPattern, $searchPatternNoAccents, $itemsPerPage, $offset);
        }
    } else {
        // Khi không có từ khóa tìm kiếm, trả về tất cả sản phẩm
        $stmt = $myconn->prepare($baseQuery . " ORDER BY p.ProductID DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $itemsPerPage, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Đếm tổng số sản phẩm
    $countQuery = str_replace("p.ProductID, p.ProductName, p.Price, p.ImageURL, p.Status, 
                         c.CategoryName, p.Description", "COUNT(DISTINCT p.ProductID) as total", $baseQuery);
    $countStmt = $myconn->prepare($countQuery);

    if (!empty($searchTerm)) {
        if (mb_strlen($searchTerm) === 1) {
            $countStmt->bind_param('s', $searchPattern);
        } else {
            $countStmt->bind_param('ss', $searchPattern, $searchPatternNoAccents);
        }
    } else if ($product_id) {
        $countStmt->bind_param('i', $product_id);
    }

    $countStmt->execute();
    $totalResult = $countStmt->get_result()->fetch_assoc();
    $totalProducts = $totalResult['total'];
    $totalPages = ceil($totalProducts / $itemsPerPage);

    $products = array();
    while ($row = $result->fetch_assoc()) {
        // Tính điểm liên quan cho sản phẩm
        $relevanceScore = calculateRelevanceScore(
            $row['ProductName'],
            $searchTerm
        );

        // Thêm sản phẩm vào danh sách
        $products[] = array(
            'id' => $row['ProductID'],
            'name' => $row['ProductName'],
            'category' => $row['CategoryName'],
            'price' => number_format($row['Price'], 0, ',', '.'),
            'image' => '../..' . $row['ImageURL'],
            'status' => $row['Status'],
            'description' => $row['Description'],
            'relevance' => $relevanceScore
        );
    }

    // Sắp xếp sản phẩm theo điểm liên quan khi có từ khóa tìm kiếm
    if (!empty($searchTerm)) {
        usort($products, function ($a, $b) {
            return $b['relevance'] - $a['relevance'];
        });
    }

    echo json_encode([
        'success' => true,
        'products' => $products,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'itemsPerPage' => $itemsPerPage
        ],
        'searchTerm' => $searchTerm // Thêm để debug
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$myconn->close();
 