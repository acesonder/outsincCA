<?php
/**
 * OUTSINC - Database Configuration
 * Outreach Someone In Need of Change
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'outsinc_db';
    private $username = 'outsinc_user';
    private $password = 'change_me_in_production';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?>
