<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .image-test { margin: 10px 0; }
        img { max-width: 200px; max-height: 200px; border: 1px solid #ccc; margin: 5px; }
    </style>
</head>
<body>
    <h1>Image Diagnostic Tool</h1>
    
    <div class="test-section">
        <h2>1. Direct Image Access Test</h2>
        <p>Testing if images are accessible via direct URLs:</p>
        
        <div class="image-test">
            <h3>Collection Image:</h3>
            <p>URL: <code>uploads/collections/68afa0ba2f42f_1756340410.jpg</code></p>
            <img src="uploads/collections/68afa0ba2f42f_1756340410.jpg" alt="Collection Image" onerror="this.style.border='3px solid red'; this.alt='FAILED TO LOAD';">
        </div>
        
        <div class="image-test">
            <h3>Product Image:</h3>
            <p>URL: <code>uploads/products/68af97314e0a9_1756337969.png</code></p>
            <img src="uploads/products/68af97314e0a9_1756337969.png" alt="Product Image" onerror="this.style.border='3px solid red'; this.alt='FAILED TO LOAD';">
        </div>
    </div>
    
    <div class="test-section">
        <h2>2. Database Image URLs</h2>
        <?php
        require_once 'config/database.php';
        
        try {
            $pdo = getDBConnection();
            
            // Test collections
            $stmt = $pdo->query("SELECT id, name, image_url FROM collections WHERE image_url IS NOT NULL LIMIT 3");
            $collections = $stmt->fetchAll();
            
            foreach ($collections as $collection) {
                echo "<div class='image-test'>";
                echo "<h3>Collection: {$collection['name']}</h3>";
                echo "<p>Database URL: <code>{$collection['image_url']}</code></p>";
                echo "<img src='{$collection['image_url']}' alt='{$collection['name']}' onerror=\"this.style.border='3px solid red'; this.alt='FAILED TO LOAD';\">";
                echo "</div>";
            }
            
            // Test products
            $stmt = $pdo->query("SELECT id, name, image_url FROM products WHERE image_url IS NOT NULL LIMIT 3");
            $products = $stmt->fetchAll();
            
            foreach ($products as $product) {
                echo "<div class='image-test'>";
                echo "<h3>Product: {$product['name']}</h3>";
                echo "<p>Database URL: <code>{$product['image_url']}</code></p>";
                echo "<img src='{$product['image_url']}' alt='{$product['name']}' onerror=\"this.style.border='3px solid red'; this.alt='FAILED TO LOAD';\">";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>3. Server Information</h2>
        <p><strong>Current URL:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        <p><strong>Script Name:</strong> <?php echo $_SERVER['SCRIPT_NAME']; ?></p>
        <p><strong>HTTP Host:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
    </div>
    
    <div class="test-section">
        <h2>4. File System Check</h2>
        <?php
        $testFiles = [
            'uploads/collections/68afa0ba2f42f_1756340410.jpg',
            'uploads/products/68af97314e0a9_1756337969.png'
        ];
        
        foreach ($testFiles as $file) {
            if (file_exists($file)) {
                echo "<p class='success'>✅ $file exists (" . filesize($file) . " bytes)</p>";
            } else {
                echo "<p class='error'>❌ $file does not exist</p>";
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>5. Quick Links</h2>
        <p><a href="index.php">← Back to Main Website</a></p>
        <p><a href="debug.php">View Debug Information</a></p>
    </div>
</body>
</html>
