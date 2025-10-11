<?php
require_once __DIR__ . '/../Jwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = '1a3LM3W966D6QTJ5BJb9opunkUcw_d09NCOIJb9QZTsrneqOICoMoeYUDcd_NfaQyR787PAH98Vhue5g938jdkiyIZyJICytKlbjNBtebaHljIR6-zf3A2h3uy6pCtUFl1UhXWnV6madujY4_3SyUViRwBUOP-UudUL4wnJnKYUGDKsiZePPzBGrF4_gxJMRwF9lIWyUCHSh-PRGfvT7s1mu4-5ByYlFvGDQraP4ZiG5bC1TAKO_CnPyd1hrpdzBzNW4SfjqGKmz7IvLAHmRD-2AMQHpTU-hN2vwoA-iQxwQhfnqjM0nnwtZ0urE6HjKl6GWQW-KLnhtfw5n_84IRQ';

$loggedInUsername = null;

if (!empty($_COOKIE['token'])) {
    try {
      $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
      $loggedInUsername = $decoded->data->Username ?? null;
  } catch (Exception $e) {
      // Thêm thông báo chi tiết về lỗi
      error_log("Token không hợp lệ hoặc hết hạn: " . $e->getMessage());
      $loggedInUsername = null;
  }

}
?>