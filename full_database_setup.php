<?php
/**
 * Full Database Setup - Creates ALL tables from scratch
 * Run this ONCE to set up your entire database
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Full Database Setup - Starting...</h2>";
    
    // 1. Create collections table first (needed for products foreign key)
    echo "<p><strong>Step 1:</strong> Creating collections table...</p>";
    $sql = "
    CREATE TABLE IF NOT EXISTS collections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        name_ar VARCHAR(100) NOT NULL,
        description TEXT,
        description_ar TEXT,
        slug VARCHAR(100) UNIQUE NOT NULL,
        image_url VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Collections table created!</p>";
    
    // 2. Insert default collections
    echo "<p><strong>Step 2:</strong> Inserting default collections...</p>";
    $sql = "
    INSERT IGNORE INTO collections (name, name_ar, description, description_ar, slug, sort_order) VALUES
    ('Hijabs', 'الحجاب', 'Elegant and comfortable hijabs', 'حجابات أنيقة ومريحة', 'hijabs', 1),
    ('Underwears', 'الملابس الداخلية', 'Comfortable and stylish underwear', 'ملابس داخلية مريحة وأنيقة', 'underwears', 2),
    ('Lingerie', 'لانجري', 'Sophisticated lingerie', 'ملابس داخلية أنيقة', 'lingerie', 3),
    ('Sleeping Sets', 'ملابس النوم', 'Comfortable sleepwear', 'ملابس نوم مريحة', 'sleeping-sets', 4),
    ('Sportswear', 'ملابس رياضية', 'Active sportswear', 'ملابس رياضية نشطة', 'sportswear', 5)
    ";
    $affected = $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Inserted $affected collections!</p>";
    
    // 3. Create users table
    echo "<p><strong>Step 3:</strong> Creating users table...</p>";
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Users table created!</p>";
    
    // 4. Insert default admin user
    echo "<p><strong>Step 4:</strong> Creating admin user...</p>";
    $sql = "
    INSERT IGNORE INTO users (username, email, password_hash, role) VALUES 
    ('admin', 'admin@sozvin.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
    ";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Admin user created! (Username: admin, Password: admin123)</p>";
    
    // 5. Create products table
    echo "<p><strong>Step 5:</strong> Creating products table...</p>";
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
        KEY idx_collection (collection_id),
        KEY idx_active (is_active),
        CONSTRAINT fk_products_collection 
            FOREIGN KEY (collection_id) 
            REFERENCES collections(id) 
            ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Products table created!</p>";
    
    // 6. Create product_images table
    echo "<p><strong>Step 6:</strong> Creating product_images table...</p>";
    $pdo->exec("DROP TABLE IF EXISTS product_images");
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
        KEY idx_primary (product_id, is_primary),
        CONSTRAINT fk_product_images_product 
            FOREIGN KEY (product_id) 
            REFERENCES products(id) 
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Product images table created!</p>";
    
    // 7. Migrate any existing product images
    echo "<p><strong>Step 7:</strong> Migrating existing product images...</p>";
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
    echo "<p style='color: green;'>✓ Migrated $affected existing product images!</p>";
    
    // 8. Create helper view
    echo "<p><strong>Step 8:</strong> Creating products_with_images view...</p>";
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
    echo "<p style='color: green;'>✓ Helper view created!</p>";
    
    // 8.5. Create videos table
    echo "<p><strong>Step 8.5:</strong> Creating videos table...</p>";
    $pdo->exec("DROP TABLE IF EXISTS videos"); // Drop if exists to recreate with new schema
    $sql = "
    CREATE TABLE videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        title_ar VARCHAR(200) NOT NULL,
        description TEXT,
        description_ar TEXT,
        video_url VARCHAR(500),
        video_file VARCHAR(500),
        thumbnail_url VARCHAR(500),
        video_type ENUM('youtube', 'vimeo', 'direct', 'embed', 'upload') DEFAULT 'youtube',
        duration INT DEFAULT 0,
        views INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        is_featured BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_active (is_active),
        KEY idx_featured (is_featured),
        KEY idx_sort (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Videos table created!</p>";
    
    // 9. Create indexes
    echo "<p><strong>Step 9:</strong> Creating indexes...</p>";
    try {
        $pdo->exec("CREATE INDEX idx_collections_active ON collections(is_active)");
        $pdo->exec("CREATE INDEX idx_collections_slug ON collections(slug)");
        echo "<p style='color: green;'>✓ Indexes created!</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>Note: Some indexes may already exist</p>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Database Setup Complete!</h3>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; border: 1px solid #c3e6cb; margin: 20px 0;'>";
    echo "<h4 style='margin-top: 0;'>What's Ready:</h4>";
    echo "<ul>";
    echo "<li>✓ Collections table with 5 default collections</li>";
    echo "<li>✓ Products table ready for your products</li>";
    echo "<li>✓ Product images table for multiple images</li>";
    echo "<li>✓ Videos table for video gallery</li>";
    echo "<li>✓ Users table with admin account</li>";
    echo "<li>✓ Helper view for easy querying</li>";
    echo "</ul>";
    echo "<h4>Login Credentials:</h4>";
    echo "<p><strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin123</p>";
    echo "</div>";
    
    echo "<div style='display: flex; gap: 15px; margin-top: 20px;'>";
    echo "<a href='index.php' style='display: inline-block; padding: 15px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Homepage</a>";
    echo "<a href='admin/login.php' style='display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Admin Panel</a>";
    echo "</div>";
    
    // Show table structure for verification
    echo "<hr>";
    echo "<h3>Verification - Tables Created:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li style='color: green; font-weight: bold;'>✓ $table</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; border: 1px solid #f5c6cb;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Error Occurred</h3>";
    echo "<p style='color: #721c24;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "<hr>";
    echo "<h4>Troubleshooting:</h4>";
    echo "<ol>";
    echo "<li>Make sure MySQL/MariaDB is running in XAMPP</li>";
    echo "<li>Check database connection settings in <code>config/database.php</code></li>";
    echo "<li>Make sure the 'sozvin' database exists (create it in phpMyAdmin if needed)</li>";
    echo "<li>Check MySQL error log for more details</li>";
    echo "</ol>";
    echo "</div>";
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Database Setup - Sozvin</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 1000px;
            margin: 30px auto;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h2 {
            color: #333;
            border-bottom: 3px solid #ff6b9d;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        h3 {
            color: #555;
            margin-top: 25px;
        }
        p {
            line-height: 1.6;
            margin: 10px 0;
        }
        a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        ul {
            line-height: 1.8;
        }
        hr {
            border: none;
            border-top: 2px solid #eee;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
    </div>
</body>
</html>

