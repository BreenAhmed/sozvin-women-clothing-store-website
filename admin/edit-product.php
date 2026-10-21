<?php
require_once 'auth.php';
requireAdmin();

$error = '';
$success = '';
$product = null;

// Get product ID from URL
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: products.php');
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Get product data
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: products.php');
        exit();
    }
    
    // Get product images
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY image_order ASC");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll();
    
    // Get collections for dropdown
    $stmt = $pdo->query("SELECT id, name FROM collections WHERE is_active = 1 ORDER BY name");
    $collections = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        // Handle image deletion
        if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            $delete_stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
            foreach ($_POST['delete_images'] as $image_id) {
                $delete_stmt->execute([$image_id, $product_id]);
            }
        }
        
        // Handle new image uploads
        if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
            $upload_dir = dirname(__DIR__) . '/uploads/products/';
            
            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("فشل في إنشاء مجلد الرفع: " . $upload_dir);
                }
            }
            
            // Check if directory is writable
            if (!is_writable($upload_dir)) {
                if (!chmod($upload_dir, 0755)) {
                    throw new Exception("مجلد الرفع غير قابل للكتابة: " . $upload_dir);
                }
            }
            
            // Get current max order
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(image_order), -1) as max_order FROM product_images WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $max_order = $stmt->fetch()['max_order'];
            
            // Process each uploaded file
            $file_count = count($_FILES['new_images']['name']);
            $img_stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, image_order, is_primary) VALUES (?, ?, ?, ?)");
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_info = pathinfo($_FILES['new_images']['name'][$i]);
                    $file_extension = strtolower($file_info['extension']);
                    
                    // Validate file type
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($file_extension, $allowed_types)) {
                        throw new Exception("نوع الملف غير مسموح به: " . $_FILES['new_images']['name'][$i]);
                    }
                    
                    // Validate file size (5MB max)
                    if ($_FILES['new_images']['size'][$i] > 5 * 1024 * 1024) {
                        throw new Exception("حجم الملف كبير جداً: " . $_FILES['new_images']['name'][$i]);
                    }
                    
                    // Generate unique filename
                    $filename = uniqid() . '_' . time() . '_' . $i . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['new_images']['tmp_name'][$i], $upload_path)) {
                        $max_order++;
                        $img_stmt->execute([
                            $product_id,
                            'uploads/products/' . $filename,
                            $max_order,
                            0 // New images are not primary by default
                        ]);
                    } else {
                        throw new Exception("فشل في رفع الصورة: " . $_FILES['new_images']['name'][$i]);
                    }
                }
            }
        }
        
        // Handle primary image change
        if (isset($_POST['primary_image_id']) && !empty($_POST['primary_image_id'])) {
            // Unset all primary images
            $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?")->execute([$product_id]);
            // Set new primary image
            $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?")
                ->execute([$_POST['primary_image_id'], $product_id]);
        }
        
        // Update primary image in products table for backward compatibility
        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
        $stmt->execute([$product_id]);
        $primary_image = $stmt->fetch();
        $image_url = $primary_image ? $primary_image['image_url'] : ($_POST['current_image_url'] ?? null);
        
        // Prepare update data
        $updateData = [
            'collection_id' => $_POST['collection_id'] ?: null,
            'name' => $_POST['name'],
            'name_ar' => $_POST['name_ar'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'image_url' => $image_url,
            'sizes' => !empty(trim($_POST['sizes'])) ? json_encode(array_filter(array_map('trim', explode(',', $_POST['sizes'])))) : null,
            'colors' => !empty(trim($_POST['colors'])) ? json_encode(array_filter(array_map('trim', explode(',', $_POST['colors'])))) : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Build update query
        $updateFields = [];
        $updateValues = [];
        foreach ($updateData as $field => $value) {
            $updateFields[] = "$field = ?";
            $updateValues[] = $value;
        }
        $updateValues[] = $product_id;
        
        $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateValues);
        
        $success = "تم تحديث المنتج بنجاح!";
        
        // Refresh product data
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        // Refresh product images
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY image_order ASC");
        $stmt->execute([$product_id]);
        $product_images = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $error = "خطأ في تحديث المنتج: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المنتج - إدارة سوزفين</title>
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
            max-width: 1000px;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
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

        .images-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .image-item {
            position: relative;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            padding: 0.5rem;
            background: white;
            transition: all 0.3s ease;
        }

        .image-item.primary {
            border-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
        }

        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }

        .image-item-actions {
            margin-top: 0.5rem;
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .image-item-actions label {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.85rem;
            cursor: pointer;
            flex: 1;
        }

        .image-item-actions input[type="checkbox"],
        .image-item-actions input[type="radio"] {
            width: auto;
            margin: 0;
        }

        .delete-img-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .delete-img-btn:hover {
            background: #c82333;
        }

        .primary-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
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
            <h2>تعديل المنتج: <?php echo htmlspecialchars($product['name']); ?></h2>
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
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="collection_id">المجموعة</label>
                            <select id="collection_id" name="collection_id">
                                <option value="">بدون مجموعة</option>
                                <?php foreach ($collections as $collection): ?>
                                    <option value="<?php echo $collection['id']; ?>" <?php echo ($product['collection_id'] == $collection['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($collection['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">اسم المنتج <span class="required">*</span></label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="name_ar">اسم المنتج (عربي)</label>
                            <input type="text" id="name_ar" name="name_ar" value="<?php echo htmlspecialchars($product['name_ar'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">الوصف</label>
                        <textarea id="description" name="description" placeholder="وصف المنتج..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-dollar-sign"></i> التسعير</h3>
                    
                    <div class="form-group">
                        <label for="price">السعر <span class="required">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-image"></i> الصور والوسائط</h3>
                    
                    <?php if (!empty($product_images)): ?>
                        <div class="form-group">
                            <label>الصور الحالية (<?php echo count($product_images); ?> صورة)</label>
                            <div class="help-text" style="margin-bottom: 1rem;">اختر صورة رئيسية، أو حدد الصور التي تريد حذفها</div>
                            
                            <div class="images-gallery">
                                <?php foreach ($product_images as $img): ?>
                                    <div class="image-item <?php echo $img['is_primary'] ? 'primary' : ''; ?>">
                                        <?php if ($img['is_primary']): ?>
                                            <span class="primary-badge">رئيسية</span>
                                        <?php endif; ?>
                                        <img src="<?php echo htmlspecialchars('../' . $img['image_url']); ?>" alt="Product Image">
                                        <div class="image-item-actions">
                                            <label>
                                                <input type="radio" name="primary_image_id" value="<?php echo $img['id']; ?>" <?php echo $img['is_primary'] ? 'checked' : ''; ?>>
                                                رئيسية
                                            </label>
                                            <label style="color: #dc3545;">
                                                <input type="checkbox" name="delete_images[]" value="<?php echo $img['id']; ?>">
                                                حذف
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" style="background: #fff3cd; color: #856404; border: 1px solid #ffeaa7;">
                            <i class="fas fa-exclamation-triangle"></i> لا توجد صور لهذا المنتج. يرجى إضافة صور جديدة.
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <label for="new_images">إضافة صور جديدة (يمكنك اختيار عدة صور)</label>
                        <input type="file" id="new_images" name="new_images[]" accept="image/*" class="file-input" multiple>
                        <div class="help-text">اختر صورة أو أكثر للمنتج (JPG, PNG, GIF, WEBP - الحد الأقصى 5MB لكل صورة)</div>
                        <div id="new-image-preview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem;"></div>
                    </div>
                    
                    <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                </div>
                
                <script>
                document.getElementById('new_images').addEventListener('change', function(e) {
                    const preview = document.getElementById('new-image-preview');
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
                            label.textContent = 'صورة جديدة ' + (index + 1);
                            label.style.cssText = 'text-align: center; margin-top: 5px; font-size: 12px; color: #666; font-weight: normal;';
                            
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
                            <label for="sizes">الأحجام المتاحة</label>
                            <input type="text" id="sizes" name="sizes" value="<?php 
                    if (!empty($product['sizes'])) {
                        $sizes = json_decode($product['sizes'], true);
                        echo htmlspecialchars(is_array($sizes) ? implode(',', $sizes) : $product['sizes']);
                    }
                ?>" placeholder="S,M,L,XL">
                            <div class="help-text">افصلي الأحجام بفواصل</div>
                        </div>
                        <div class="form-group">
                            <label for="colors">الألوان المتاحة</label>
                            <input type="text" id="colors" name="colors" value="<?php 
                    if (!empty($product['colors'])) {
                        $colors = json_decode($product['colors'], true);
                        echo htmlspecialchars(is_array($colors) ? implode(',', $colors) : $product['colors']);
                    }
                ?>" placeholder="أحمر، أزرق، أخضر">
                            <div class="help-text">افصلي الألوان بفواصل</div>
                        </div>
                    </div>


                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> الإعدادات</h3>
                    
                    <div class="form-row">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                            <label for="is_active">منتج نشط</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_featured" name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                            <label for="is_featured">منتج مميز</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> تحديث المنتج
                </button>
            </form>
        </div>
    </div>
</body>
</html>
