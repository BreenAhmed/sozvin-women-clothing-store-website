<?php
require_once 'auth.php';
requireAdmin();

// Get statistics
try {
    $pdo = getDBConnection();
    
    // Count products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch()['total'];
    
    // Count collections
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM collections");
    $totalCollections = $stmt->fetch()['total'];
    
    // Count active products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $activeProducts = $stmt->fetch()['total'];
    
    // Count videos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos");
    $totalVideos = $stmt->fetch()['total'];
    
    // Count active videos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE is_active = 1");
    $activeVideos = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الإدارة - سوزفين</title>
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #666;
            font-size: 14px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .action-card h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-card i {
            color: #667eea;
        }

        .action-btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            margin: 0.5rem 0.5rem 0.5rem 0;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }



        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .welcome-section h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .welcome-section p {
            font-size: 1.1rem;
            opacity: 0.9;
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

        [dir="rtl"] .stats-grid {
            text-align: right;
        }

        [dir="rtl"] .actions-grid {
            text-align: right;
        }

        [dir="rtl"] .action-card {
            text-align: right;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .welcome-section h2 {
                font-size: 1.8rem;
            }
            
            .welcome-section p {
                font-size: 1rem;
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
            
            .stat-card {
                padding: 1.5rem;
            }
            
            .stat-card h3 {
                font-size: 2rem;
            }
            
            .stat-card p {
                font-size: 0.9rem;
            }
            
            .action-card {
                padding: 1.5rem;
            }
            
            .action-card h3 {
                font-size: 1.3rem;
            }
            
            .action-card p {
                font-size: 0.9rem;
            }
            
            .action-btn {
                padding: 0.8rem 1.2rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>إدارة سوزفين</h1>
        <div class="navbar-right">
            <div class="user-info">
                <i class="fas fa-user"></i>
                مرحباً، <?php echo htmlspecialchars($_SESSION['username']); ?>
            </div>
            <a href="?logout=1" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h2>مرحباً بك في لوحة إدارة سوزفين</h2>
            <p>أديري مجموعاتك ومنتجاتك ومحتوى موقعك من مكان واحد.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-box"></i>
                <h3><?php echo $totalProducts ?? 0; ?></h3>
                <p>إجمالي المنتجات</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-layer-group"></i>
                <h3><?php echo $totalCollections ?? 0; ?></h3>
                <p>المجموعات</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h3><?php echo $activeProducts ?? 0; ?></h3>
                <p>المنتجات النشطة</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-video"></i>
                <h3><?php echo $totalVideos ?? 0; ?></h3>
                <p>الفيديوهات</p>
            </div>

        </div>

        <div class="actions-grid">
            <div class="action-card">
                <h3><i class="fas fa-plus"></i> إدارة المنتجات</h3>
                <p>أضيفي، عدلي، وأديري كتالوج منتجاتك</p>
                <a href="products.php" class="action-btn">عرض المنتجات</a>
                <a href="add-product.php" class="action-btn">إضافة منتج جديد</a>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-folder"></i> إدارة المجموعات</h3>
                <p>نظمي منتجاتك في مجموعات</p>
                <a href="collections.php" class="action-btn">عرض المجموعات</a>
                <a href="add-collection.php" class="action-btn">إضافة مجموعة جديدة</a>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-video"></i> إدارة الفيديوهات</h3>
                <p>أضيفي وأديري فيديوهاتك</p>
                <a href="videos.php" class="action-btn">عرض الفيديوهات</a>
                <a href="add-video.php" class="action-btn">إضافة فيديو جديد</a>
            </div>

        </div>


    </div>
</body>
</html>
