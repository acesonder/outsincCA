<?php
/**
 * OUTSINC - Database Configuration
 * Outreach Someone In Need of Change
 */

class Database {
    // ⚠️ LOCALHOST DEVELOPMENT CREDENTIALS ONLY!
    // DO NOT use these credentials in production!
    // Change to a dedicated database user with a strong password before deploying.
    private $host = 'localhost';
    private $db_name = 'outsinc_db';
    private $username = 'root';
    private $password = '';  // Blank for localhost - CHANGE IN PRODUCTION!
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
