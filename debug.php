<?php
// Debug page to check database and collections
echo "<h1>Debug Information</h1>";

// Check if database config exists
if (file_exists('config/database.php')) {
    echo "<p>✅ Database config file exists</p>";
    
    try {
        require_once 'config/database.php';
        $pdo = getDBConnection();
        echo "<p>✅ Database connection successful</p>";
        
        // Check if collections table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'collections'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Collections table exists</p>";
            
            // Check collections count
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM collections");
            $total = $stmt->fetch()['total'];
            echo "<p>📊 Total collections in database: $total</p>";
            
            // Check active collections
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM collections WHERE is_active = 1");
            $active = $stmt->fetch()['total'];
            echo "<p>📊 Active collections: $active</p>";
            
            // Show all collections
            $stmt = $pdo->query("SELECT * FROM collections ORDER BY id");
            $collections = $stmt->fetchAll();
            
            if (!empty($collections)) {
                echo "<h2>All Collections:</h2>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Name</th><th>Slug</th><th>Active</th><th>Sort Order</th></tr>";
                
                foreach ($collections as $collection) {
                    echo "<tr>";
                    echo "<td>" . $collection['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($collection['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($collection['slug']) . "</td>";
                    echo "<td>" . ($collection['is_active'] ? 'Yes' : 'No') . "</td>";
                    echo "<td>" . $collection['sort_order'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>❌ No collections found in database</p>";
            }
            
        } else {
            echo "<p>❌ Collections table does not exist</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p>❌ Database config file not found</p>";
}

// Check collection pages
echo "<h2>Collection Pages Check:</h2>";
$collectionPages = [
    'hijabs.php',
    'underwears.php', 
    'lingerie.php',
    'sportswear.php',
    'sleeping-sets.php'
];

foreach ($collectionPages as $page) {
    if (file_exists("collections/$page")) {
        echo "<p>✅ $page exists</p>";
    } else {
        echo "<p>❌ $page missing</p>";
    }
}
?>

