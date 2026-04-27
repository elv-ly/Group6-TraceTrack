<?php

class Database {
    // Database configuration
    private $host     = "localhost";
    private $db_name  = "tracetrack";
    private $username = "root";
    private $password = "";
    public  $conn;

    // Create and return PDO database connection
    public function getConnection() {
        $this->conn = null;
        try {
            // Establish connection with MySQL
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            // Set error mode to exceptions
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Fetch results as associative arrays by default
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
