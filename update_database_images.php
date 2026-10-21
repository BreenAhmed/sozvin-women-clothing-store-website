<?php
/**
 * Database Update Script for Product Images
 * Run this file once to update the database structure
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Starting Database Update...</h2>";
    
    // Ensure products table uses InnoDB
    echo "<p>Ensuring products table uses InnoDB engine...</p>";
    try {
        $pdo->exec("ALTER TABLE products ENGINE=InnoDB");
        echo "<p style='color: green;'>✓ Products table engine set to InnoDB!</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>Note: Products table may already be InnoDB</p>";
    }
    
    // Drop existing table if it exists to avoid conflicts
    echo "<p>Checking for existing product_images table...</p>";
    try {
        $pdo->exec("DROP TABLE IF EXISTS product_images");
        echo "<p style='color: blue;'>Cleared existing table (if any)</p>";
    } catch (Exception $e) {
        // Ignore errors
    }
    
    // Create product_images table
    echo "<p>Creating product_images table...</p>";
    $sql = "
    CREATE TABLE product_images (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        image_order INT DEFAULT 0,
        is_primary TINYINT(1) DEFAULT 0,
        alt_text VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_product_images_product (product_id),
        INDEX idx_product_images_order (product_id, image_order),
        INDEX idx_primary (product_id, is_primary),
        CONSTRAINT fk_product_images_product 
            FOREIGN KEY (product_id) 
            REFERENCES products(id) 
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Product images table created successfully!</p>";
    
    // Migrate existing product images
    echo "<p>Migrating existing product images...</p>";
    $sql = "
    INSERT INTO product_images (product_id, image_url, image_order, is_primary)
    SELECT id, image_url, 0, TRUE
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
    $sql = "
    CREATE OR REPLACE VIEW products_with_images AS
    SELECT 
        p.*,
        (SELECT GROUP_CONCAT(pi.image_url ORDER BY pi.image_order SEPARATOR ',')
         FROM product_images pi
         WHERE pi.product_id = p.id) as all_images,
        (SELECT pi.image_url 
         FROM product_images pi 
         WHERE pi.product_id = p.id AND pi.is_primary = TRUE 
         LIMIT 1) as primary_image,
        (SELECT COUNT(*) 
         FROM product_images pi 
         WHERE pi.product_id = p.id) as image_count
    FROM products p
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Helper view created successfully!</p>";
    
    echo "<h3 style='color: green;'>✓ Database update completed successfully!</h3>";
    echo "<p><a href='index.php'>Go to Homepage</a> | <a href='admin/dashboard.php'>Go to Admin Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
    
    // Check if table already exists
    if ($e->getCode() == '42S01') {
        echo "<p style='color: orange;'>Note: Table may already exist. This is OK if you've run this script before.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update - Product Images</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
    </style>
</head>
<body>
    <div class="container">
    </div>
</body>
</html>

