<?php
require_once 'auth.php';
requireAdmin();

$success = $error = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "تم حذف الفيديو بنجاح!";
    } catch (Exception $e) {
        $error = "خطأ في حذف الفيديو: " . $e->getMessage();
    }
}

// Handle toggle active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE videos SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$_GET['toggle']]);
        $success = "تم تحديث حالة الفيديو!";
    } catch (Exception $e) {
        $error = "خطأ في تحديث الحالة: " . $e->getMessage();
    }
}

// Get all videos
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM videos ORDER BY sort_order ASC, created_at DESC");
    $videos = $stmt->fetchAll();
} catch (Exception $e) {
    $videos = [];
    $error = "خطأ في تحميل الفيديوهات: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفيديوهات - سوزفين</title>
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

        .page-header h2 {
            color: #333;
            font-size: 2rem;
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

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .video-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .video-thumbnail {
            width: 100%;
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-thumbnail .play-icon {
            position: absolute;
            font-size: 3rem;
            color: rgba(255,255,255,0.9);
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .video-info {
            padding: 1.5rem;
        }

        .video-info h3 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .video-info p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #999;
        }

        .video-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-edit {
            background: #667eea;
            color: white;
        }

        .btn-edit:hover {
            background: #5568d3;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .btn-toggle {
            background: #ffc107;
            color: #333;
        }

        .btn-toggle:hover {
            background: #e0a800;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-featured {
            background: #fff3cd;
            color: #856404;
            margin-right: 0.5rem;
        }

        .no-videos {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .no-videos i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .videos-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
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
            <a href="videos.php" class="nav-link" style="background: #f0f2ff;">الفيديوهات</a>
            <a href="?logout=1" class="logout-btn">تسجيل الخروج</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>إدارة الفيديوهات</h2>
            <a href="add-video.php" class="add-btn">
                <i class="fas fa-plus"></i> إضافة فيديو جديد
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($videos)): ?>
            <div class="no-videos">
                <i class="fas fa-video"></i>
                <h3>لا توجد فيديوهات</h3>
                <p>ابدئي بإضافة فيديو جديد</p>
                <a href="add-video.php" class="add-btn" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i> إضافة فيديو جديد
                </a>
            </div>
        <?php else: ?>
            <div class="videos-grid">
                <?php foreach ($videos as $video): ?>
                    <div class="video-card">
                        <div class="video-thumbnail">
                            <?php if ($video['thumbnail_url']): ?>
                                <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            <div style="display: <?php echo $video['thumbnail_url'] ? 'none' : 'flex'; ?>; align-items: center; justify-content: center; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-play-circle play-icon"></i>
                            </div>
                            <?php if ($video['video_type'] === 'upload'): ?>
                                <div style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.75rem;">
                                    <i class="fas fa-upload"></i> مرفوع
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="video-info">
                            <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                            <?php if ($video['description']): ?>
                                <p><?php echo htmlspecialchars($video['description']); ?></p>
                            <?php endif; ?>
                            <div class="video-meta">
                                <div>
                                    <?php if ($video['is_featured']): ?>
                                        <span class="status-badge status-featured">مميز</span>
                                    <?php endif; ?>
                                    <span class="status-badge <?php echo $video['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $video['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-eye"></i> <?php echo number_format($video['views']); ?>
                                </div>
                            </div>
                            <div class="video-actions">
                                <a href="edit-video.php?id=<?php echo $video['id']; ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                                <a href="?toggle=<?php echo $video['id']; ?>" class="btn btn-toggle" onclick="return confirm('هل أنت متأكدة من تغيير الحالة؟');">
                                    <i class="fas fa-toggle-<?php echo $video['is_active'] ? 'on' : 'off'; ?>"></i>
                                </a>
                                <a href="?delete=<?php echo $video['id']; ?>" class="btn btn-delete" onclick="return confirm('هل أنت متأكدة من حذف هذا الفيديو؟');">
                                    <i class="fas fa-trash"></i> حذف
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

