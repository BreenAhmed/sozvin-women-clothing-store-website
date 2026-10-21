<?php
require_once 'auth.php';
requireAdmin();

$success = $error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        $image_url = null;
        
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
            error_log("Collection upload attempt - File: " . $_FILES['image']['name'] . ", Size: " . $_FILES['image']['size'] . ", Temp: " . $_FILES['image']['tmp_name']);
            error_log("Collection upload path: " . $upload_path);
            error_log("Collection directory writable: " . (is_writable($upload_dir) ? 'Yes' : 'No'));
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/collections/' . $filename;
                error_log("Collection file uploaded successfully to: " . $upload_path);
            } else {
                $upload_error = error_get_last();
                throw new Exception("فشل في رفع الصورة. الخطأ: " . ($upload_error['message'] ?? 'Unknown error'));
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO collections (
                name, name_ar, slug, image_url, is_active, sort_order
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['name'], // Use the same name for Arabic
            strtolower(str_replace(' ', '-', $_POST['name'])), // Generate slug from name
            $image_url,
            isset($_POST['is_active']) ? 1 : 0,
            $_POST['sort_order'] ?: 0
        ]);
        
        $success = "تم إضافة المجموعة بنجاح!";
        
        // Clear form data
        $_POST = array();
        
    } catch (Exception $e) {
        $error = "خطأ في إضافة المجموعة: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مجموعة جديدة - إدارة سوزفين</title>
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
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #5a6268;
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

        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f2ff;
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
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .submit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .required {
            color: #dc3545;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 0.25rem;
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

        [dir="rtl"] .form-section {
            text-align: right;
        }

        [dir="rtl"] .form-group {
            text-align: right;
        }

        [dir="rtl"] .checkbox-group {
            justify-content: flex-start;
        }

        [dir="rtl"]         .back-btn {
            flex-direction: row-reverse;
        }

        .file-input {
            padding: 0.8rem;
            border: 2px dashed #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }

        .file-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .current-image {
            margin-top: 1rem;
            padding: 1rem;
            background: #e8f5e8;
            border-radius: 8px;
            border: 1px solid #c3e6c3;
        }

        .current-image p {
            margin: 0;
            color: #2d5a2d;
        }

        .current-image a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .current-image a:hover {
            text-decoration: underline;
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
            
            .form-row {
                flex-direction: column;
                gap: 1rem;
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
            <h2>إضافة مجموعة جديدة</h2>
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
                    
                    <div class="form-group">
                        <label for="name">اسم المجموعة <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>


                </div>

                <div class="form-section">
                    <h3><i class="fas fa-image"></i> الوسائط والإعدادات</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="image">صورة المجموعة</label>
                            <input type="file" id="image" name="image" accept="image/*" class="file-input">
                            <div class="help-text">اختر صورة للمجموعة (JPG, PNG, GIF - الحد الأقصى 5MB)</div>
                            <?php if (isset($_POST['image_url']) && $_POST['image_url']): ?>
                                <div class="current-image">
                                    <p>الصورة الحالية: <a href="<?php echo htmlspecialchars($_POST['image_url']); ?>" target="_blank">عرض الصورة</a></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="sort_order">ترتيب الفرز</label>
                            <input type="number" id="sort_order" name="sort_order" min="0" value="<?php echo htmlspecialchars($_POST['sort_order'] ?? '0'); ?>">
                            <div class="help-text">الأرقام الأقل تظهر أولاً</div>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>>
                        <label for="is_active">مجموعة نشطة</label>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus"></i> إضافة المجموعة
                </button>
            </form>
        </div>
    </div>
</body>
</html>
