<?php
/**
 * Create Videos Table Script
 * Run this ONCE to create the videos table
 * 
 * Visit: https://yourdomain.com/create_videos_table.php
 */

require_once 'config/database.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Videos Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Creating Videos Table...</h2>

<?php

try {
    $pdo = getDBConnection();
    
    echo "<p>Checking if videos table exists...</p>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'videos'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "<div class='success'>✓ Videos table already exists!</div>";
    } else {
        echo "<p>Creating videos table...</p>";
        
        // Create videos table
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($sql);
        echo "<div class='success'>✓ Videos table created successfully!</div>";
    }
    
    // Create uploads/videos directory
    $upload_dir = __DIR__ . '/uploads/videos/';
    if (!is_dir($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            echo "<div class='success'>✓ Created uploads/videos directory!</div>";
        } else {
            echo "<div class='error'>⚠ Could not create uploads/videos directory. Please create it manually with 755 permissions.</div>";
        }
    } else {
        echo "<div class='success'>✓ Uploads/videos directory exists!</div>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Setup Complete!</h3>";
    echo "<p><a href='admin/videos.php'>Go to Videos Admin</a> | <a href='videos.php'>View Videos Page</a></p>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Code:</strong> " . $e->getCode() . "</p>";
    echo "</div>";
}

?>

    </div>
</body>
</html>

