<?php
require_once 'auth.php';
requireAdmin();

$success = $error = '';

// Get collections for dropdown
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT id, name FROM collections WHERE is_active = 1 ORDER BY name");
    $collections = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error loading collections: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        $image_url = null;
        $uploaded_images = [];
        
        // Handle multiple image uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_dir = dirname(__DIR__) . '/uploads/products/';
            
            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("فشل في إنشاء مجلد الرفع: " . $upload_dir);
                }
            }
            
            // Check if directory is writable and try to make it writable if needed
            if (!is_writable($upload_dir)) {
                if (!chmod($upload_dir, 0755)) {
                    throw new Exception("مجلد الرفع غير قابل للكتابة: " . $upload_dir);
                }
            }
            
            // Process each uploaded file
            $file_count = count($_FILES['images']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_info = pathinfo($_FILES['images']['name'][$i]);
                    $file_extension = strtolower($file_info['extension']);
                    
                    // Validate file type
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($file_extension, $allowed_types)) {
                        throw new Exception("نوع الملف غير مسموح به: " . $_FILES['images']['name'][$i]);
                    }
                    
                    // Validate file size (5MB max)
                    if ($_FILES['images']['size'][$i] > 5 * 1024 * 1024) {
                        throw new Exception("حجم الملف كبير جداً: " . $_FILES['images']['name'][$i]);
                    }
                    
                    // Generate unique filename
                    $filename = uniqid() . '_' . time() . '_' . $i . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                        $uploaded_images[] = [
                            'url' => 'uploads/products/' . $filename,
                            'order' => $i,
                            'is_primary' => ($i === 0) // First image is primary
                        ];
                        
                        // Set the first image as the main image_url for backward compatibility
                        if ($i === 0) {
                            $image_url = 'uploads/products/' . $filename;
                        }
                    } else {
                        throw new Exception("فشل في رفع الصورة: " . $_FILES['images']['name'][$i]);
                    }
                }
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO products (
                collection_id, name, name_ar, description,
                price, is_active, is_featured, image_url, sizes, colors
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['collection_id'] ?: null,
            $_POST['name'],
            $_POST['name'], // Use the same name for Arabic
            $_POST['description'] ?: null,
            $_POST['price'],
            isset($_POST['is_active']) ? 1 : 0,
            isset($_POST['is_featured']) ? 1 : 0,
            $image_url,
            !empty(trim($_POST['sizes'])) ? json_encode(array_filter(array_map('trim', explode(',', $_POST['sizes'])))) : null,
            !empty(trim($_POST['colors'])) ? json_encode(array_filter(array_map('trim', explode(',', $_POST['colors'])))) : null
        ]);
        
        // Get the inserted product ID
        $product_id = $pdo->lastInsertId();
        
        // Insert images into product_images table
        if (!empty($uploaded_images)) {
            $img_stmt = $pdo->prepare("
                INSERT INTO product_images (product_id, image_url, image_order, is_primary)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($uploaded_images as $img) {
                $img_stmt->execute([
                    $product_id,
                    $img['url'],
                    $img['order'],
                    $img['is_primary'] ? 1 : 0
                ]);
            }
        }
        
        $success = "تم إضافة المنتج بنجاح مع " . count($uploaded_images) . " صورة!";
        
        // Clear form data
        $_POST = array();
        
    } catch (Exception $e) {
        $error = "خطأ في إضافة المنتج: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج جديد - إدارة سوزفين</title>
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
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
            <h2>إضافة منتج جديد</h2>
            <a href="products.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> العودة للمنتجات
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
                        <label for="name">اسم المنتج <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">وصف المنتج</label>
                        <textarea id="description" name="description" placeholder="وصف المنتج..." rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="collection_id">المجموعة</label>
                            <select id="collection_id" name="collection_id">
                                <option value="">اختر المجموعة</option>
                                <?php foreach ($collections as $collection): ?>
                                    <option value="<?php echo $collection['id']; ?>" <?php echo ($_POST['collection_id'] ?? '') == $collection['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($collection['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>


                </div>

                <div class="form-section">
                    <h3><i class="fas fa-dollar-sign"></i> التسعير</h3>
                    
                    <div class="form-group">
                        <label for="price">السعر <span class="required">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-image"></i> الصور والوسائط</h3>
                    
                    <div class="form-group">
                        <label for="images">صور المنتج (يمكنك اختيار عدة صور)</label>
                        <input type="file" id="images" name="images[]" accept="image/*" class="file-input" multiple>
                        <div class="help-text">اختر صورة أو أكثر للمنتج (JPG, PNG, GIF, WEBP - الحد الأقصى 5MB لكل صورة). الصورة الأولى ستكون الصورة الرئيسية.</div>
                        <div id="image-preview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem;"></div>
                    </div>
                </div>
                
                <script>
                document.getElementById('images').addEventListener('change', function(e) {
                    const preview = document.getElementById('image-preview');
                    preview.innerHTML = '';
                    
                    const files = Array.from(e.target.files);
                    files.forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            const div = document.createElement('div');
                            div.style.cssText = 'position: relative; border: 2px solid #e1e5e9; border-radius: 8px; padding: 5px; background: white;';
                            
                            const img = document.createElement('img');
                            img.src = event.target.result;
                            img.style.cssText = 'width: 100%; height: 150px; object-fit: cover; border-radius: 5px;';
                            
                            const label = document.createElement('div');
                            label.textContent = index === 0 ? 'صورة رئيسية' : 'صورة ' + (index + 1);
                            label.style.cssText = 'text-align: center; margin-top: 5px; font-size: 12px; color: ' + (index === 0 ? '#28a745' : '#666') + '; font-weight: ' + (index === 0 ? 'bold' : 'normal') + ';';
                            
                            div.appendChild(img);
                            div.appendChild(label);
                            preview.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    });
                });
                </script>

                <div class="form-section">
                    <h3><i class="fas fa-tags"></i> الخصائص والخيارات</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sizes">المقاسات المتاحة</label>
                            <input type="text" id="sizes" name="sizes" value="<?php echo htmlspecialchars($_POST['sizes'] ?? ''); ?>" placeholder="S,M,L,XL">
                            <div class="help-text">افصلي المقاسات بفواصل</div>
                        </div>
                        <div class="form-group">
                            <label for="colors">الألوان المتاحة</label>
                            <input type="text" id="colors" name="colors" value="<?php echo htmlspecialchars($_POST['colors'] ?? ''); ?>" placeholder="Red,Blue,Green">
                            <div class="help-text">افصلي الألوان بفواصل</div>
                        </div>
                    </div>


                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> الإعدادات</h3>
                    
                    <div class="form-row">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>>
                            <label for="is_active">منتج نشط</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_featured" name="is_featured" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                            <label for="is_featured">منتج مميز</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus"></i> إضافة المنتج
                </button>
            </form>
        </div>
    </div>
</body>
</html>
