<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Database Structure Diagnosis</h2>";
    
    // Check products table structure
    echo "<h3>Products Table Structure:</h3>";
    $stmt = $pdo->query("SHOW CREATE TABLE products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . htmlspecialchars($result['Create Table']) . "</pre>";
    
    // Check products table engine and charset
    echo "<h3>Products Table Info:</h3>";
    $stmt = $pdo->query("
        SELECT 
            ENGINE, 
            TABLE_COLLATION,
            CREATE_OPTIONS
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = 'sozvin' 
        AND TABLE_NAME = 'products'
    ");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($info);
    echo "</pre>";
    
    // Check column details for products.id
    echo "<h3>Products.id Column Details:</h3>";
    $stmt = $pdo->query("
        SELECT 
            COLUMN_NAME,
            COLUMN_TYPE,
            IS_NULLABLE,
            COLUMN_KEY,
            EXTRA
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'sozvin' 
        AND TABLE_NAME = 'products'
        AND COLUMN_NAME = 'id'
    ");
    $col_info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($col_info);
    echo "</pre>";
    
    // Check if product_images exists
    echo "<h3>Product Images Table Status:</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_images'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "<p style='color: green;'>Table EXISTS</p>";
        $stmt = $pdo->query("SHOW CREATE TABLE product_images");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>" . htmlspecialchars($result['Create Table']) . "</pre>";
    } else {
        echo "<p style='color: red;'>Table DOES NOT EXIST</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Structure Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        pre {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #ddd;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #ff6b9d;
            padding-bottom: 10px;
        }
        h3 {
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
</body>
</html>

