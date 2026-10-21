<?php
require_once 'auth.php';
requireAdmin();

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "Product deleted successfully!";
    } catch (Exception $e) {
        $error = "Error deleting product: " . $e->getMessage();
    }
}

// Handle product status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$_GET['toggle']]);
        $success = "Product status updated!";
    } catch (Exception $e) {
        $error = "Error updating product: " . $e->getMessage();
    }
}

// Get products with collection information
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT p.*, c.name as collection_name 
        FROM products p 
        LEFT JOIN collections c ON p.collection_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات - إدارة سوزفين</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            color: #667eea;
            font-size: 24px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-link {
            color: #667eea;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: #f0f2ff;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .add-btn {
            background: #28a745;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .products-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            background: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .table-header h3 {
            color: #333;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            margin-right: 0.5rem;
            display: inline-block;
        }

        .edit-btn {
            background: #ffc107;
            color: #212529;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .toggle-btn {
            background: #6c757d;
            color: white;
        }

        .price {
            font-weight: 600;
            color: #28a745;
        }

        .stock {
            font-weight: 500;
        }

        .stock-low {
            color: #dc3545;
        }

        .stock-ok {
            color: #28a745;
        }

        /* RTL Layout Support */
        body {
            direction: rtl;
            text-align: right;
        }

        html[dir="rtl"] {
            direction: rtl;
        }

        [dir="rtl"] .navbar {
            text-align: right;
        }

        [dir="rtl"] .navbar-right {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .page-header {
            text-align: right;
        }

        [dir="rtl"] table {
            text-align: right;
        }

        [dir="rtl"] th,
        [dir="rtl"] td {
            text-align: right;
        }

        [dir="rtl"] .action-btn {
            margin-right: 0;
            margin-left: 0.5rem;
        }

        /* Mobile responsive styles */
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .navbar h1 {
                font-size: 20px;
            }
            
            .navbar-right {
                flex-direction: column;
                gap: 0.8rem;
            }
            
            .container {
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-header h2 {
                font-size: 1.8rem;
            }
            
            .add-btn {
                padding: 0.8rem 1.5rem;
                font-size: 0.95rem;
            }
            
            .products-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 600px;
            }
            
            .table-header h3 {
                font-size: 1.3rem;
            }
            
            th, td {
                padding: 0.8rem 0.6rem;
                font-size: 0.9rem;
            }
            
            .product-image {
                width: 40px;
                height: 40px;
            }
            
            .action-btn {
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
                margin: 0.2rem;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 0.8rem;
            }
            
            .navbar h1 {
                font-size: 18px;
            }
            
            .container {
                padding: 0.8rem;
            }
            
            .page-header h2 {
                font-size: 1.6rem;
            }
            
            .add-btn {
                padding: 0.7rem 1.2rem;
                font-size: 0.9rem;
            }
            
            .table-header h3 {
                font-size: 1.2rem;
            }
            
            th, td {
                padding: 0.6rem 0.4rem;
                font-size: 0.8rem;
            }
            
            .product-image {
                width: 35px;
                height: 35px;
            }
            
            .action-btn {
                padding: 0.4rem 0.6rem;
                font-size: 0.75rem;
                margin: 0.1rem;
            }
            
            .status-badge {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>إدارة سوزفين</h1>
        <div class="navbar-right">
            <a href="dashboard.php" class="nav-link">لوحة التحكم</a>
            <a href="products.php" class="nav-link">المنتجات</a>
            <a href="collections.php" class="nav-link">المجموعات</a>
            <a href="?logout=1" class="logout-btn">تسجيل الخروج</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>إدارة المنتجات</h2>
            <a href="add-product.php" class="add-btn">
                <i class="fas fa-plus"></i> إضافة منتج جديد
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="products-table">
            <div class="table-header">
                <h3>كتالوج المنتجات</h3>
            </div>
            
            <?php if (!empty($products)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>الاسم</th>
                            <th>المجموعة</th>
                            <th>السعر</th>
                            <th>المخزون</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if ($product['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product" class="product-image">
                                    <?php else: ?>
                                        <div class="product-image" style="background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <?php if ($product['name_ar']): ?>
                                        <br><small style="color: #666;"><?php echo htmlspecialchars($product['name_ar']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['collection_name'] ?? 'Uncategorized'); ?></td>
                                <td class="price"><?php echo number_format($product['price'], 2); ?></td>
                                <td class="stock <?php echo $product['stock_quantity'] < 10 ? 'stock-low' : 'stock-ok'; ?>">
                                    <?php echo $product['stock_quantity']; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $product['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
                                    <a href="?toggle=<?php echo $product['id']; ?>" class="action-btn toggle-btn" 
                                       onclick="return confirm('تغيير حالة المنتج؟')">
                                        <i class="fas fa-toggle-on"></i> تبديل
                                    </a>
                                    <a href="?delete=<?php echo $product['id']; ?>" class="action-btn delete-btn" 
                                       onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                        <i class="fas fa-trash"></i> حذف
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 2rem; text-align: center; color: #666;">
                    <i class="fas fa-box" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>لم يتم العثور على منتجات</h3>
                    <p>ابدئي بإضافة منتجك الأول إلى الكتالوج.</p>
                    <a href="add-product.php" class="add-btn" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> أضيفي منتجك الأول
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
