<?php
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
}
