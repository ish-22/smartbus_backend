<?php

// Test database connection
$host = '127.0.0.1';
$port = '3306';
$database = 'smartbus';
$username = 'root';
$password = '';

echo "Testing MySQL connection...\n";
echo "Host: $host:$port\n";
echo "Database: $database\n";
echo "Username: $username\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connection successful!\n\n";
    
    // Test users table
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE users");
        echo "Users table structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "\nUsers in database: $count\n";
        
    } else {
        echo "✗ Users table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

?>