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


}

?>
