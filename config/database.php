<?php
// Database configuration for Sozvin
define('DB_HOST', 'localhost');     // Your MySQL host (usually localhost for XAMPP)
define('DB_NAME', 'u100517965_sozvin');        // Your database name
define('DB_USER', 'u100517965_sozvin');          // Your MySQL username (usually root for XAMPP)
define('DB_PASS', 'Brezzo#0751');              // Your MySQL password (usually empty for XAMPP)
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '3306');          // MySQL port (usually 3306)

// Create database connection
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed: " . $e->getMessage());
    }
}

// Test database connection with detailed information
function testConnection() {
    try {
        echo "<h2>Testing Sozvin Database Connection</h2>";
        echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
        echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
        echo "<p><strong>User:</strong> " . DB_USER . "</p>";
        echo "<p><strong>Port:</strong> " . DB_PORT . "</p>";
        
        $pdo = getDBConnection();
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test basic queries
        echo "<h3>Testing Database Queries:</h3>";
        
        // Test collections
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM collections");
        $collections = $stmt->fetch();
        echo "<p>📁 Collections: " . $collections['count'] . "</p>";
        
        // Test products
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $products = $stmt->fetch();
        echo "<p>📦 Products: " . $products['count'] . "</p>";
        
        // Test users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $users = $stmt->fetch();
        echo "<p>👥 Users: " . $users['count'] . "</p>";
        
        echo "<p style='color: green;'>✅ All database tests passed!</p>";
        echo "<p><strong>Next step:</strong> <a href='admin/login.php'>Go to Admin Panel</a></p>";
        
        return true;
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
        echo "<h3>Troubleshooting:</h3>";
        echo "<ul>";
        echo "<li>Make sure MySQL is running in XAMPP</li>";
        echo "<li>Verify database 'sozvin' exists in phpMyAdmin</li>";
        echo "<li>Check username and password in config/database.php</li>";
        echo "<li>Ensure port 3306 is not blocked</li>";
        echo "</ul>";
        return false;
    }
}

// Check if database exists
function checkDatabaseExists() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Get database status
function getDatabaseStatus() {
    if (!checkDatabaseExists()) {
        return "Database 'sozvin' does not exist. Please create it first in phpMyAdmin.";
    }
    
    try {
        $pdo = getDBConnection();
        return "Database 'sozvin' is connected and accessible.";
    } catch (Exception $e) {
        return "Database exists but connection failed: " . $e->getMessage();
    }
}
?>
