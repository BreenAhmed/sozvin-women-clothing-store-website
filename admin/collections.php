<?php
require_once 'auth.php';
requireAdmin();

// Handle collection deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM collections WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "Collection deleted successfully!";
    } catch (Exception $e) {
        $error = "Error deleting collection: " . $e->getMessage();
    }
}

// Handle collection status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE collections SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$_GET['toggle']]);
        $success = "Collection status updated!";
    } catch (Exception $e) {
        $error = "Error updating collection: " . $e->getMessage();
    }
}

// Get collections with product counts
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM collections c 
        LEFT JOIN products p ON c.id = p.collection_id 
        GROUP BY c.id 
        ORDER BY c.sort_order, c.name
    ");
    $collections = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المجموعات - إدارة سوزفين</title>
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

        .collections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .collection-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .collection-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .collection-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .collection-header h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .collection-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .collection-content {
            padding: 1.5rem;
        }

        .collection-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .collection-description {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .collection-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            transition: all 0.3s ease;
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

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
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

        [dir="rtl"] .collection-card {
            text-align: right;
        }

        [dir="rtl"] .collection-stats {
            text-align: right;
        }

        [dir="rtl"] .collection-actions {
            justify-content: flex-start;
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
            
            .collections-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .collection-card {
                padding: 1.5rem;
            }
            
            .collection-header h3 {
                font-size: 1.4rem;
            }
            
            .collection-stats {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .collection-actions {
                flex-direction: column;
                gap: 0.8rem;
            }
            
            .action-btn {
                padding: 0.7rem 1rem;
                font-size: 0.9rem;
                text-align: center;
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
            
            .collection-card {
                padding: 1rem;
            }
            
            .collection-header h3 {
                font-size: 1.3rem;
            }
            
            .collection-stats {
                gap: 0.8rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.85rem;
            }
            
            .action-btn {
                padding: 0.6rem 0.8rem;
                font-size: 0.85rem;
            }
            
            .empty-state h3 {
                font-size: 1.4rem;
            }
            
            .empty-state p {
                font-size: 0.9rem;
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
            <h2>إدارة المجموعات</h2>
            <a href="add-collection.php" class="add-btn">
                <i class="fas fa-plus"></i> إضافة مجموعة جديدة
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

        <?php if (!empty($collections)): ?>
            <div class="collections-grid">
                <?php foreach ($collections as $collection): ?>
                    <div class="collection-card">
                        <div class="collection-header">
                            <h3><?php echo htmlspecialchars($collection['name']); ?></h3>
                            <?php if ($collection['name_ar']): ?>
                                <p><?php echo htmlspecialchars($collection['name_ar']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="collection-content">
                                                            <span class="status-badge <?php echo $collection['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $collection['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                </span>
                            
                            <div class="collection-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo $collection['product_count']; ?></div>
                                    <div class="stat-label">المنتجات</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo $collection['sort_order']; ?></div>
                                    <div class="stat-label">الترتيب</div>
                                </div>
                            </div>
                            
                            <?php if ($collection['description']): ?>
                                <div class="collection-description">
                                    <?php echo htmlspecialchars($collection['description']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="collection-actions">
                                <a href="edit-collection.php?id=<?php echo $collection['id']; ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                                <a href="?toggle=<?php echo $collection['id']; ?>" class="action-btn toggle-btn" 
                                   onclick="return confirm('تغيير حالة المجموعة؟')">
                                    <i class="fas fa-toggle-on"></i> تبديل
                                </a>
                                <a href="?delete=<?php echo $collection['id']; ?>" class="action-btn delete-btn" 
                                   onclick="return confirm('هل أنت متأكد من حذف هذه المجموعة؟ سيتم أيضاً حذف جميع المنتجات المرتبطة بها.')">
                                    <i class="fas fa-trash"></i> حذف
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>لم يتم العثور على مجموعات</h3>
                <p>ابدئي بإنشاء مجموعتك الأولى لتنظيم منتجاتك.</p>
                <a href="add-collection.php" class="add-btn" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i> إنشاء مجموعتك الأولى
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
