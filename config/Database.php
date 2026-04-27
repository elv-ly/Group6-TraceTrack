<?php

class Database {
    // Database connection parameters
    private $host     = "localhost";      // Database server
    private $db_name  = "tracetrack";     // Database name
    private $username = "root";           // Database username
    private $password = "";               // Database password
    public  $conn;                        // PDO connection object

    // Create and return PDO database connection
    public function getConnection() {
        $this->conn = null;
        try {
            // Establish MySQL connection with UTF-8 encoding
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            // Throw exceptions on SQL errors
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Fetch results as associative arrays by default
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Display error message if connection fails
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
