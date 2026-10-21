<?php
// Script to prepare images for deployment
require_once 'config/database.php';

echo "<h1>Prepare for Deployment</h1>";

echo "<h2>1. Current Image Status</h2>";

try {
    $pdo = getDBConnection();
    
    // Check collections
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM collections WHERE image_url IS NOT NULL");
    $collectionsWithImages = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM collections");
    $totalCollections = $stmt->fetch()['total'];
    
    echo "<p>Collections with images: $collectionsWithImages / $totalCollections</p>";
    
    // Check products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE image_url IS NOT NULL");
    $productsWithImages = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch()['total'];
    
    echo "<p>Products with images: $productsWithImages / $totalProducts</p>";
    
    echo "<h2>2. File System Check</h2>";
    
    // Check upload directories
    $uploadDirs = ['uploads/collections', 'uploads/products'];
    $totalFiles = 0;
    
    foreach ($uploadDirs as $dir) {
        if (is_dir($dir)) {
            $files = glob("$dir/*");
            $fileCount = count($files);
            $totalFiles += $fileCount;
            echo "<p>✅ $dir: $fileCount files</p>";
            
            // List files
            foreach ($files as $file) {
                $size = filesize($file);
                echo "<p style='margin-left: 20px;'>📄 " . basename($file) . " (" . number_format($size) . " bytes)</p>";
            }
        } else {
            echo "<p>❌ $dir: Directory not found</p>";
        }
    }
    
    echo "<p><strong>Total files to upload:</strong> $totalFiles</p>";
    
    echo "<h2>3. Deployment Instructions</h2>";
    echo "<div style='background: #e8f4fd; padding: 15px; border: 1px solid #b3d9ff;'>";
    echo "<h3>Step-by-Step Deployment:</h3>";
    echo "<ol>";
    echo "<li><strong>Upload all files</strong> to your web server</li>";
    echo "<li><strong>Upload the entire uploads/ directory</strong> with all subdirectories</li>";
    echo "<li><strong>Set file permissions:</strong>";
    echo "<ul>";
    echo "<li>Directories: 755 (rwxr-xr-x)</li>";
    echo "<li>Files: 644 (rw-r--r--)</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li><strong>Test image access</strong> by visiting: <code>yourdomain.com/uploads/collections/68afa0ba2f42f_1756340410.jpg</code></li>";
    echo "<li><strong>Check database connection</strong> on the server</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h2>4. Quick Test URLs</h2>";
    echo "<p>Test these URLs on your deployed server:</p>";
    echo "<ul>";
    
    // Get sample images
    $stmt = $pdo->query("SELECT image_url FROM collections WHERE image_url IS NOT NULL LIMIT 2");
    $collectionImages = $stmt->fetchAll();
    
    foreach ($collectionImages as $img) {
        echo "<li><code>yourdomain.com/{$img['image_url']}</code></li>";
    }
    
    $stmt = $pdo->query("SELECT image_url FROM products WHERE image_url IS NOT NULL LIMIT 2");
    $productImages = $stmt->fetchAll();
    
    foreach ($productImages as $img) {
        echo "<li><code>yourdomain.com/{$img['image_url']}</code></li>";
    }
    
    echo "</ul>";
    
    echo "<h2>5. Troubleshooting</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7;'>";
    echo "<h3>If images don't work after deployment:</h3>";
    echo "<ul>";
    echo "<li>Check file permissions (755 for directories, 644 for files)</li>";
    echo "<li>Verify all files were uploaded completely</li>";
    echo "<li>Check server error logs for any issues</li>";
    echo "<li>Test direct image URLs in browser</li>";
    echo "<li>Ensure .htaccess doesn't block image access</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='deployment-check.php'>Run Deployment Check</a> | <a href='index.php'>Back to Website</a></p>";
?>
