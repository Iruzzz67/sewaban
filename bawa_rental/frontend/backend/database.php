<?php
// backend/database.php
require_once 'config.php';

class Database {
    private $connection;
    
    public function __construct() {
        $config = Config::getDBConfig();
        
        try {
            $this->connection = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            $this->handleError('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->connection->prepare($sql);
        
        try {
            $stmt->execute($data);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->handleError('Insert failed: ' . $e->getMessage());
        }
    }
    
    public function select($table, $where = []) {
        $sql = "SELECT * FROM $table";
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = :$key";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($where);
        
        return $stmt->fetchAll();
    }
    
    private function handleError($message) {
        error_log($message);
        throw new Exception('Database error occurred');
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Helper function
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db;
}
?>