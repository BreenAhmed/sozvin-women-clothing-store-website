<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to database
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get collection ID from query parameter
    $collectionId = isset($_GET['collection_id']) ? (int)$_GET['collection_id'] : 0;
    
    if ($collectionId <= 0) {
        throw new Exception('Invalid collection ID');
    }
    
    // Fetch products for this collection
    $stmt = $pdo->prepare("SELECT * FROM products WHERE collection_id = ? AND is_active = 1 ORDER BY created_at DESC");
    $stmt->execute([$collectionId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'products' => $products,
        'count' => count($products)
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

