<?php
// Deployment Image Check Tool
require_once 'config/database.php';
require_once 'config/app.php';

echo "<h1>Deployment Image Check</h1>";

// Check environment
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = (
    $host === 'localhost' || 
    strpos($host, '127.0.0.1') !== false ||
    strpos($host, '192.168.') !== false
);

echo "<h2>Environment Detection</h2>";
echo "<p><strong>Host:</strong> $host</p>";
echo "<p><strong>Environment:</strong> " . ($isLocal ? "Local Development" : "Production") . "</p>";
echo "<p><strong>Base URL:</strong> " . AppConfig::getBaseUrl() . "</p>";

echo "<h2>Image URL Testing</h2>";

try {
    $pdo = getDBConnection();
    
    // Test collection images
    $stmt = $pdo->query("SELECT id, name, image_url FROM collections WHERE image_url IS NOT NULL LIMIT 3");
    $collections = $stmt->fetchAll();
    
    echo "<h3>Collection Images:</h3>";
    foreach ($collections as $collection) {
        $originalUrl = $collection['image_url'];
        $processedUrl = AppConfig::getImageUrl($originalUrl);
        
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
        echo "<h4>{$collection['name']}</h4>";
        echo "<p><strong>Original URL:</strong> $originalUrl</p>";
        echo "<p><strong>Processed URL:</strong> $processedUrl</p>";
        
        // Test if file exists
        $fileExists = file_exists($originalUrl);
        echo "<p><strong>File exists:</strong> " . ($fileExists ? "✅ Yes" : "❌ No") . "</p>";
        
        if ($fileExists) {
            echo "<img src='$processedUrl' style='max-width: 150px; max-height: 150px; border: 1px solid #ccc;' alt='{$collection['name']}' onerror=\"this.style.border='3px solid red'; this.alt='FAILED TO LOAD';\">";
        }
        echo "</div>";
    }
    
    // Test product images
    $stmt = $pdo->query("SELECT id, name, image_url FROM products WHERE image_url IS NOT NULL LIMIT 3");
    $products = $stmt->fetchAll();
    
    echo "<h3>Product Images:</h3>";
    foreach ($products as $product) {
        $originalUrl = $product['image_url'];
        $processedUrl = AppConfig::getImageUrl($originalUrl);
        
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
        echo "<h4>{$product['name']}</h4>";
        echo "<p><strong>Original URL:</strong> $originalUrl</p>";
        echo "<p><strong>Processed URL:</strong> $processedUrl</p>";
        
        // Test if file exists
        $fileExists = file_exists($originalUrl);
        echo "<p><strong>File exists:</strong> " . ($fileExists ? "✅ Yes" : "❌ No") . "</p>";
        
        if ($fileExists) {
            echo "<img src='$processedUrl' style='max-width: 150px; max-height: 150px; border: 1px solid #ccc;' alt='{$product['name']}' onerror=\"this.style.border='3px solid red'; this.alt='FAILED TO LOAD';\">";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Deployment Checklist</h2>";
echo "<div style='background: #f9f9f9; padding: 15px; border: 1px solid #ddd;'>";
echo "<h3>Before Deploying:</h3>";
echo "<ul>";
echo "<li>✅ Upload all files to your web server</li>";
echo "<li>✅ Upload the entire <code>uploads/</code> directory with all images</li>";
echo "<li>✅ Set proper file permissions (755 for directories, 644 for files)</li>";
echo "<li>✅ Ensure database is properly configured on the server</li>";
echo "<li>✅ Test image URLs work directly in browser</li>";
echo "</ul>";

echo "<h3>Common Issues & Solutions:</h3>";
echo "<ul>";
echo "<li><strong>Images not showing:</strong> Check file permissions and upload directory</li>";
echo "<li><strong>404 errors:</strong> Verify file paths and server configuration</li>";
echo "<li><strong>Mixed content errors:</strong> Use HTTPS for all resources</li>";
echo "<li><strong>Case sensitivity:</strong> Ensure file names match exactly</li>";
echo "</ul>";

echo "<h3>File Permission Commands (if you have SSH access):</h3>";
echo "<pre>";
echo "chmod -R 755 uploads/\n";
echo "find uploads/ -type f -exec chmod 644 {} \\;\n";
echo "</pre>";
echo "</div>";

echo "<p><a href='index.php'>← Back to Main Website</a></p>";
?>
