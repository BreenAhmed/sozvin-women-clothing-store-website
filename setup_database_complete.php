<?php
/**
 * Complete Database Setup Script
 * This will create all tables including the products table and product_images table
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Complete Database Setup</h2>";
    
    // Create products table if it doesn't exist
    echo "<p>Creating products table...</p>";
    $sql = "
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        collection_id INT,
        name VARCHAR(200) NOT NULL,
        name_ar VARCHAR(200) NOT NULL,
        description TEXT,
        description_ar TEXT,
        price DECIMAL(10,2) NOT NULL,
        sale_price DECIMAL(10,2),
        sku VARCHAR(100) UNIQUE,
        stock_quantity INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        is_featured BOOLEAN DEFAULT FALSE,
        image_url VARCHAR(255),
        additional_images JSON,
        sizes JSON,
        colors JSON,
        tags JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Products table created successfully!</p>";
    
    // Drop existing product_images table if it exists
    echo "<p>Checking for existing product_images table...</p>";
    $pdo->exec("DROP TABLE IF EXISTS product_images");
    echo "<p style='color: blue;'>Cleared existing product_images table (if any)</p>";
    
    // Create product_images table WITHOUT foreign key first
    echo "<p>Creating product_images table...</p>";
    $sql = "
    CREATE TABLE product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        image_order INT DEFAULT 0,
        is_primary TINYINT(1) DEFAULT 0,
        alt_text VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_product_id (product_id),
        KEY idx_order (product_id, image_order),
        KEY idx_primary (product_id, is_primary)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Product images table created successfully!</p>";
    
    // Now add the foreign key constraint
    echo "<p>Adding foreign key constraint...</p>";
    try {
        $pdo->exec("
            ALTER TABLE product_images 
            ADD CONSTRAINT fk_product_images_product 
            FOREIGN KEY (product_id) 
            REFERENCES products(id) 
            ON DELETE CASCADE
        ");
        echo "<p style='color: green;'>✓ Foreign key constraint added successfully!</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>Note: Foreign key may already exist or couldn't be added: " . $e->getMessage() . "</p>";
    }
    
    // Migrate existing product images
    echo "<p>Migrating existing product images...</p>";
    $sql = "
    INSERT INTO product_images (product_id, image_url, image_order, is_primary)
    SELECT id, image_url, 0, 1
    FROM products
    WHERE image_url IS NOT NULL 
    AND image_url != ''
    AND NOT EXISTS (
        SELECT 1 FROM product_images WHERE product_id = products.id
    )
    ";
    
    $affected = $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Migrated $affected existing images!</p>";
    
    // Create helper view
    echo "<p>Creating products_with_images view...</p>";
    $pdo->exec("DROP VIEW IF EXISTS products_with_images");
    $sql = "
    CREATE VIEW products_with_images AS
    SELECT 
        p.*,
        (SELECT GROUP_CONCAT(pi.image_url ORDER BY pi.image_order SEPARATOR ',')
         FROM product_images pi
         WHERE pi.product_id = p.id) as all_images,
        (SELECT pi.image_url 
         FROM product_images pi 
         WHERE pi.product_id = p.id AND pi.is_primary = 1 
         LIMIT 1) as primary_image,
        (SELECT COUNT(*) 
         FROM product_images pi 
         WHERE pi.product_id = p.id) as image_count
    FROM products p
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Helper view created successfully!</p>";
    
    echo "<h3 style='color: green;'>✓ Database setup completed successfully!</h3>";
    echo "<p><a href='index.php'>Go to Homepage</a> | <a href='admin/dashboard.php'>Go to Admin Dashboard</a></p>";
    
    // Show table structure for verification
    echo "<h3>Verification - Product Images Table Structure:</h3>";
    $stmt = $pdo->query("SHOW CREATE TABLE product_images");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px; font-size: 12px;'>" . htmlspecialchars($result['Create Table']) . "</pre>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
    echo "<p style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
    echo "<strong>Troubleshooting Steps:</strong><br>";
    echo "1. Make sure MySQL is running<br>";
    echo "2. Check that the 'sozvin' database exists<br>";
    echo "3. Check that the 'collections' table exists (required for products foreign key)<br>";
    echo "4. Run <a href='../database_setup.sql'>database_setup.sql</a> first if tables don't exist<br>";
    echo "</p>";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #ff6b9d;
            padding-bottom: 10px;
        }
        a {
            color: #ff6b9d;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        pre {
            overflow-x: auto;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
    </div>
</body>
</html>

