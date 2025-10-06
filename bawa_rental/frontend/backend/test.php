<?php
// test.php
require_once 'config.php';
require_once 'database.php';

try {
    $db = new Database();
    echo "✅ Database connected successfully!<br>";
    
    // Test table exists
    $result = $db->select('sewa_ban', []);
    echo "✅ Table sewa_ban accessible!<br>";
    
    echo "✅ Backend setup completed!";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>