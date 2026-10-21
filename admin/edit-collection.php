<?php
require_once 'auth.php';
requireAdmin();

$error = '';
$success = '';
$collection = null;

// Get collection ID from URL
$collection_id = $_GET['id'] ?? null;

if (!$collection_id) {
    header('Location: collections.php');
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Get collection data
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ?");
    $stmt->execute([$collection_id]);
    $collection = $stmt->fetch();
    
    if (!$collection) {
        header('Location: collections.php');
        exit();
    }
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        $image_url = $_POST['current_image_url'] ?? null; // Keep current image if no new one uploaded
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = dirname(__DIR__) . '/uploads/collections/';
            
            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("فشل في إنشاء مجلد الرفع: " . $upload_dir);
                }
            }
            
            // Check if directory is writable and try to make it writable if needed
            if (!is_writable($upload_dir)) {
                // Try to make directory writable
                if (!chmod($upload_dir, 0755)) {
                    throw new Exception("مجلد الرفع غير قابل للكتابة: " . $upload_dir);
                }
            }
            
            $file_info = pathinfo($_FILES['image']['name']);
            $file_extension = strtolower($file_info['extension']);
            
            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception("نوع الملف غير مسموح به. الأنواع المسموحة: JPG, PNG, GIF");
            }
            
            // Validate file size (5MB max)
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                throw new Exception("حجم الملف كبير جداً. الحد الأقصى: 5MB");
            }
            
            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            // Debug information
            error_log("Edit collection upload attempt - File: " . $_FILES['image']['name'] . ", Size: " . $_FILES['image']['size'] . ", Temp: " . $_FILES['image']['tmp_name']);
            error_log("Edit collection upload path: " . $upload_path);
            error_log("Edit collection directory writable: " . (is_writable($upload_dir) ? 'Yes' : 'No'));
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/collections/' . $filename;
                error_log("Edit collection file uploaded successfully to: " . $upload_path);
            } else {
                $upload_error = error_get_last();
                throw new Exception("فشل في رفع الصورة. الخطأ: " . ($upload_error['message'] ?? 'Unknown error'));
            }
        }
        
        // Prepare update data
        $updateData = [
            'name' => $_POST['name'],
            'name_ar' => $_POST['name_ar'],
            'description' => $_POST['description'],
            'description_ar' => $_POST['description_ar'],
            'slug' => $_POST['slug'],
            'image_url' => $image_url,
            'sort_order' => $_POST['sort_order'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Build update query
        $updateFields = [];
        $updateValues = [];
        foreach ($updateData as $field => $value) {
            $updateFields[] = "$field = ?";
            $updateValues[] = $value;
        }
        $updateValues[] = $collection_id;
        
        $sql = "UPDATE collections SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateValues);
        
        $success = "تم تحديث المجموعة بنجاح!";
        
        // Refresh collection data
        $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ?");
        $stmt->execute([$collection_id]);
        $collection = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = "خطأ في تحديث المجموعة: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المجموعة - إدارة سوزفين</title>
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
            transition: background-color 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
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
            max-width: 800px;
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

        .back-btn {
            background: #6c757d;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .form-section h3 {
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section i {
            color: #667eea;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .required {
            color: #dc3545;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 0.25rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .submit-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background: #5a6fd8;
        }

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
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .page-header h2 {
                font-size: 1.8rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }
            
            .form-section {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .form-section h3 {
                font-size: 1.3rem;
            }
            
            .form-group {
                margin-bottom: 1.2rem;
            }
            
            .form-group label {
                font-size: 0.95rem;
            }
            
            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 0.8rem;
                font-size: 0.95rem;
            }
            
            .checkbox-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.8rem;
            }
            
            .submit-btn {
                padding: 0.8rem 1.5rem;
                font-size: 0.95rem;
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
            
            .form-container {
                padding: 1rem;
            }
            
            .form-section {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .form-section h3 {
                font-size: 1.2rem;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
            
            .form-group label {
                font-size: 0.9rem;
            }
            
            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 0.7rem;
                font-size: 0.9rem;
            }
            
            .submit-btn {
                padding: 0.7rem 1.2rem;
                font-size: 0.9rem;
            }
            
            .back-btn {
                padding: 0.7rem 1.2rem;
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
            <h2>تعديل المجموعة: <?php echo htmlspecialchars($collection['name']); ?></h2>
            <a href="collections.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> العودة للمجموعات
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

        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> المعلومات الأساسية</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">اسم المجموعة <span class="required">*</span></label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($collection['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="name_ar">اسم المجموعة (عربي)</label>
                            <input type="text" id="name_ar" name="name_ar" value="<?php echo htmlspecialchars($collection['name_ar'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="slug">المعرف <span class="required">*</span></label>
                            <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($collection['slug']); ?>" required>
                            <div class="help-text">نسخة صديقة للرابط من الاسم (مثال: "elegant-lingerie")</div>
                        </div>
                        <div class="form-group">
                            <label for="sort_order">ترتيب الفرز</label>
                            <input type="number" id="sort_order" name="sort_order" min="0" value="<?php echo $collection['sort_order'] ?? 0; ?>">
                            <div class="help-text">الأرقام الأقل تظهر أولاً</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">الوصف</label>
                        <textarea id="description" name="description" placeholder="وصف المجموعة..."><?php echo htmlspecialchars($collection['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description_ar">الوصف (عربي)</label>
                        <textarea id="description_ar" name="description_ar" placeholder="وصف المجموعة باللغة العربية..."><?php echo htmlspecialchars($collection['description_ar'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-image"></i> الوسائط والإعدادات</h3>
                    
                    <div class="form-group">
                        <label for="image">صورة المجموعة</label>
                        <input type="file" id="image" name="image" accept="image/*" class="file-input">
                        <div class="help-text">اختر صورة جديدة للمجموعة (JPG, PNG, GIF - الحد الأقصى 5MB)</div>
                        <?php if ($collection['image_url']): ?>
                            <div class="current-image">
                                <p>الصورة الحالية: <a href="<?php echo htmlspecialchars($collection['image_url']); ?>" target="_blank">عرض الصورة</a></p>
                                <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($collection['image_url']); ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo $collection['is_active'] ? 'checked' : ''; ?>>
                        <label for="is_active">مجموعة نشطة</label>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> تحديث المجموعة
                </button>
            </form>
        </div>
    </div>
</body>
</html>
