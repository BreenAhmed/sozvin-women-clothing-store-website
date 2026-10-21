<?php
// Debug collection page
echo "<h1>Debug Collection Page</h1>";

// Connect to database
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Test collection lookup
    $collectionSlug = 'hijabs';
    echo "<p>🔍 Looking for collection with slug: <strong>$collectionSlug</strong></p>";
    
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE slug = ? AND is_active = 1");
    $stmt->execute([$collectionSlug]);
    $collection = $stmt->fetch();
    
    if ($collection) {
        echo "<p>✅ Collection found:</p>";
        echo "<ul>";
        echo "<li>ID: " . $collection['id'] . "</li>";
        echo "<li>Name: " . htmlspecialchars($collection['name']) . "</li>";
        echo "<li>Slug: " . htmlspecialchars($collection['slug']) . "</li>";
        echo "<li>Active: " . ($collection['is_active'] ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
        
        // Test products lookup
        $stmt = $pdo->prepare("SELECT * FROM products WHERE collection_id = ? AND is_active = 1 ORDER BY created_at DESC");
        $stmt->execute([$collection['id']]);
        $products = $stmt->fetchAll();
        
        echo "<p>📦 Products found: " . count($products) . "</p>";
        
        if (!empty($products)) {
            echo "<h2>Products:</h2>";
            foreach ($products as $product) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<strong>" . htmlspecialchars($product['name']) . "</strong><br>";
                echo "Price: " . $product['price'] . "<br>";
                echo "Description: " . htmlspecialchars($product['description'] ?? 'No description') . "<br>";
                echo "</div>";
            }
        }
        
    } else {
        echo "<p>❌ Collection not found!</p>";
        
        // Show all collections to debug
        $stmt = $pdo->query("SELECT * FROM collections");
        $allCollections = $stmt->fetchAll();
        
        echo "<p>🔍 All collections in database:</p>";
        echo "<ul>";
        foreach ($allCollections as $col) {
            echo "<li>ID: " . $col['id'] . ", Name: " . htmlspecialchars($col['name']) . ", Slug: " . htmlspecialchars($col['slug']) . ", Active: " . ($col['is_active'] ? 'Yes' : 'No') . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}
?>

