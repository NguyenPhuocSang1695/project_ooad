<?php

if (!class_exists('DatabaseConnection')) {
  class DatabaseConnection
  {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'c01db';
    private $connection;

    public function getConnection()
    {
      return $this->connection;
    }

    public function connect()
    {
      $this->connection = new mysqli(
        $this->host,
        $this->username,
        $this->password,
        $this->database
      );

      if ($this->connection->connect_error) {
        die("Kết nối thất bại: " . $this->connection->connect_error);
      }

      $this->connection->query("SET NAMES 'UTF8'");
    }

    public function query($sql)
    {
      if (!$this->connection) {
        die("Chưa kết nối đến cơ sở dữ liệu");
      }

      $result = $this->connection->query($sql);

      if ($result === false) {
        die("Lỗi truy vấn: " . $this->connection->error);
      }

      return $result;
    }

    public function close()
    {
      if ($this->connection) {
        $this->connection->close();
      }
    }


    // ✅ Hàm query có prepare
    public function queryPrepared($sql, $params = [], $types = "")
    {
      if (!$this->connection) {
        $this->connect();
      }

      $stmt = $this->connection->prepare($sql);
      if (!$stmt) {
        die("Lỗi prepare: " . $this->connection->error);
      }

      if (!empty($params)) {
        if ($types == "") {
          // Tự động đoán loại dữ liệu (i, d, s)
          foreach ($params as $param) {
            if (is_int($param)) $types .= "i";
            elseif (is_double($param)) $types .= "d";
            else $types .= "s";
          }
        }
        $stmt->bind_param($types, ...$params);
      }

      $stmt->execute();
      $result = $stmt->get_result();

      // Nếu là SELECT thì trả về result set
      if ($result !== false) {
        return $result;
      }

      // Nếu là UPDATE/DELETE/INSERT thì trả về true/false
      return $stmt->affected_rows > 0;
    }
  }
}
