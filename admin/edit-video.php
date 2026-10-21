<?php
require_once 'auth.php';
requireAdmin();

$error = '';
$success = '';
$video = null;

$video_id = $_GET['id'] ?? null;

if (!$video_id) {
    header('Location: videos.php');
    exit();
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $video = $stmt->fetch();
    
    if (!$video) {
        header('Location: videos.php');
        exit();
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        $video_url = $_POST['video_url'] ?: null;
        $video_file = $video['video_file']; // Keep existing file if not uploading new one
        $video_type = $_POST['video_type'] ?: 'youtube';
        
        // Handle video file upload
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = dirname(__DIR__) . '/uploads/videos/';
            
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
            
            // Delete old video file if exists
            if ($video['video_file'] && file_exists(dirname(__DIR__) . '/' . $video['video_file'])) {
                @unlink(dirname(__DIR__) . '/' . $video['video_file']);
            }
            
            $file_info = pathinfo($_FILES['video_file']['name']);
            $file_extension = strtolower($file_info['extension']);
            
            // Validate file type
            $allowed_types = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv', 'flv'];
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception("نوع الملف غير مسموح به. الأنواع المسموحة: MP4, WebM, OGG, MOV, AVI, WMV, FLV");
            }
            
            // Validate file size (100MB max)
            $max_size = 100 * 1024 * 1024;
            if ($_FILES['video_file']['size'] > $max_size) {
                throw new Exception("حجم الملف كبير جداً. الحد الأقصى: 100MB");
            }
            
            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $upload_path)) {
                $video_file = 'uploads/videos/' . $filename;
                $video_type = 'upload';
                $video_url = null;
            } else {
                throw new Exception("فشل في رفع الفيديو");
            }
        }
        
        // Handle thumbnail upload
        $thumbnail_url = $_POST['thumbnail_url'] ?: null;
        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $thumb_dir = dirname(__DIR__) . '/uploads/videos/thumbnails/';
            
            if (!is_dir($thumb_dir)) {
                mkdir($thumb_dir, 0755, true);
            }
            
            // Delete old thumbnail if exists
            if ($video['thumbnail_url'] && strpos($video['thumbnail_url'], 'uploads/videos/thumbnails/') === 0) {
                if (file_exists(dirname(__DIR__) . '/' . $video['thumbnail_url'])) {
                    @unlink(dirname(__DIR__) . '/' . $video['thumbnail_url']);
                }
            }
            
            $file_info = pathinfo($_FILES['thumbnail_file']['name']);
            $file_extension = strtolower($file_info['extension']);
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_extension, $allowed_types)) {
                $filename = uniqid() . '_thumb_' . time() . '.' . $file_extension;
                $upload_path = $thumb_dir . $filename;
                
                if (move_uploaded_file($_FILES['thumbnail_file']['tmp_name'], $upload_path)) {
                    $thumbnail_url = 'uploads/videos/thumbnails/' . $filename;
                }
            }
        } elseif (!$thumbnail_url && $video['thumbnail_url']) {
            $thumbnail_url = $video['thumbnail_url']; // Keep existing thumbnail
        }
        
        $stmt = $pdo->prepare("
            UPDATE videos SET
                title = ?, title_ar = ?, description = ?, description_ar = ?,
                video_url = ?, video_file = ?, thumbnail_url = ?, video_type = ?,
                duration = ?, is_active = ?, is_featured = ?, sort_order = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['title'],
            $_POST['title_ar'] ?: $_POST['title'],
            $_POST['description'] ?: null,
            $_POST['description_ar'] ?: null,
            $video_url,
            $video_file,
            $thumbnail_url,
            $video_type,
            $_POST['duration'] ?: 0,
            isset($_POST['is_active']) ? 1 : 0,
            isset($_POST['is_featured']) ? 1 : 0,
            $_POST['sort_order'] ?: 0,
            $video_id
        ]);
        
        $success = "تم تحديث الفيديو بنجاح!";
        
        // Refresh video data
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);
        $video = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = "خطأ في تحديث الفيديو: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الفيديو - إدارة سوزفين</title>
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
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
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

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            width: 100%;
        }

        .submit-btn:hover {
            background: #5568d3;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 0.25rem;
        }

        .video-preview {
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .video-preview iframe {
            width: 100%;
            height: 300px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>إدارة سوزفين</h1>
        <div class="navbar-right">
            <a href="dashboard.php" class="nav-link">لوحة التحكم</a>
            <a href="videos.php" class="nav-link" style="background: #f0f2ff;">الفيديوهات</a>
            <a href="?logout=1" class="logout-btn">تسجيل الخروج</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>تعديل الفيديو: <?php echo htmlspecialchars($video['title']); ?></h2>
            <a href="videos.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> العودة للفيديوهات
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
                        <label for="title">عنوان الفيديو <span style="color: red;">*</span></label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="title_ar">العنوان بالعربية</label>
                        <input type="text" id="title_ar" name="title_ar" value="<?php echo htmlspecialchars($video['title_ar'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">الوصف</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($video['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description_ar">الوصف بالعربية</label>
                        <textarea id="description_ar" name="description_ar"><?php echo htmlspecialchars($video['description_ar'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-video"></i> الفيديو</h3>
                    
                    <?php if ($video['video_file']): ?>
                        <div class="form-group" style="background: #e8f5e8; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <strong>الفيديو الحالي:</strong> 
                            <a href="../<?php echo htmlspecialchars($video['video_file']); ?>" target="_blank" style="color: #667eea;">
                                <?php echo htmlspecialchars(basename($video['video_file'])); ?>
                            </a>
                            <div class="help-text">لرفع فيديو جديد، استخدمي الحقل أدناه</div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>اختر طريقة إضافة الفيديو</label>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="upload_method" value="url" <?php echo !$video['video_file'] ? 'checked' : ''; ?> onchange="toggleUploadMethod()">
                                <span>رابط فيديو</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="upload_method" value="file" <?php echo $video['video_file'] ? 'checked' : ''; ?> onchange="toggleUploadMethod()">
                                <span>رفع ملف فيديو</span>
                            </label>
                        </div>
                    </div>

                    <!-- URL Method -->
                    <div id="url_method" style="<?php echo $video['video_file'] ? 'display: none;' : ''; ?>">
                        <div class="form-group">
                            <label for="video_type">نوع الفيديو</label>
                            <select id="video_type" name="video_type" onchange="updateVideoHelp()">
                                <option value="youtube" <?php echo ($video['video_type'] ?? 'youtube') === 'youtube' ? 'selected' : ''; ?>>YouTube</option>
                                <option value="vimeo" <?php echo ($video['video_type'] ?? '') === 'vimeo' ? 'selected' : ''; ?>>Vimeo</option>
                                <option value="direct" <?php echo ($video['video_type'] ?? '') === 'direct' ? 'selected' : ''; ?>>رابط مباشر (MP4)</option>
                                <option value="embed" <?php echo ($video['video_type'] ?? '') === 'embed' ? 'selected' : ''; ?>>كود Embed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="video_url">رابط الفيديو</label>
                            <input type="text" id="video_url" name="video_url" value="<?php echo htmlspecialchars($video['video_url'] ?? ''); ?>">
                            <div class="help-text" id="video_help">مثال: https://www.youtube.com/watch?v=dQw4w9WgXcQ</div>
                        </div>
                    </div>

                    <!-- File Upload Method -->
                    <div id="file_method" style="<?php echo $video['video_file'] ? '' : 'display: none;'; ?>">
                        <div class="form-group">
                            <label for="video_file">رفع ملف فيديو جديد</label>
                            <input type="file" id="video_file" name="video_file" accept="video/*">
                            <div class="help-text">
                                أنواع الملفات المسموحة: MP4, WebM, OGG, MOV, AVI, WMV, FLV<br>
                                الحد الأقصى لحجم الملف: 100MB
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail_file">رفع صورة مصغرة جديدة</label>
                        <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
                        <?php if ($video['thumbnail_url']): ?>
                            <div style="margin-top: 0.5rem;">
                                <img src="../<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="Thumbnail" style="max-width: 200px; border-radius: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail_url">رابط صورة مصغرة (Thumbnail)</label>
                        <input type="text" id="thumbnail_url" name="thumbnail_url" value="<?php echo htmlspecialchars($video['thumbnail_url'] ?? ''); ?>">
                    </div>

                    <div id="video_preview" class="video-preview"></div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> الإعدادات</h3>
                    
                    <div class="form-group">
                        <label for="duration">المدة (بالثواني)</label>
                        <input type="number" id="duration" name="duration" value="<?php echo $video['duration']; ?>" min="0">
                    </div>

                    <div class="form-group">
                        <label for="sort_order">ترتيب العرض</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?php echo $video['sort_order']; ?>" min="0">
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo $video['is_active'] ? 'checked' : ''; ?>>
                        <label for="is_active">فيديو نشط</label>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" <?php echo $video['is_featured'] ? 'checked' : ''; ?>>
                        <label for="is_featured">فيديو مميز</label>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> تحديث الفيديو
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleUploadMethod() {
            const method = document.querySelector('input[name="upload_method"]:checked').value;
            const urlMethod = document.getElementById('url_method');
            const fileMethod = document.getElementById('file_method');
            
            if (method === 'url') {
                urlMethod.style.display = 'block';
                fileMethod.style.display = 'none';
            } else {
                urlMethod.style.display = 'none';
                fileMethod.style.display = 'block';
            }
        }

        function updateVideoHelp() {
            const type = document.getElementById('video_type').value;
            const help = document.getElementById('video_help');
            
            switch(type) {
                case 'youtube':
                    help.textContent = 'مثال: https://www.youtube.com/watch?v=dQw4w9WgXcQ';
                    break;
                case 'vimeo':
                    help.textContent = 'مثال: https://vimeo.com/123456789';
                    break;
                case 'direct':
                    help.textContent = 'مثال: https://example.com/video.mp4';
                    break;
                case 'embed':
                    help.textContent = 'الصق كود iframe الكامل هنا';
                    break;
            }
        }

        // Show preview on load
        document.addEventListener('DOMContentLoaded', function() {
            const url = document.getElementById('video_url').value;
            const type = document.getElementById('video_type').value;
            updatePreview(url, type);
        });

        document.getElementById('video_url').addEventListener('input', function() {
            const url = this.value;
            const type = document.getElementById('video_type').value;
            updatePreview(url, type);
        });

        function updatePreview(url, type) {
            const preview = document.getElementById('video_preview');
            
            if (url && type === 'youtube') {
                let videoId = '';
                if (url.includes('youtube.com/watch?v=')) {
                    videoId = url.split('v=')[1].split('&')[0];
                } else if (url.includes('youtu.be/')) {
                    videoId = url.split('youtu.be/')[1].split('?')[0];
                }
                
                if (videoId) {
                    preview.innerHTML = '<iframe src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allowfullscreen></iframe>';
                    preview.style.display = 'block';
                }
            } else if (url && type === 'vimeo') {
                const match = url.match(/vimeo.com\/(\d+)/);
                if (match) {
                    preview.innerHTML = '<iframe src="https://player.vimeo.com/video/' + match[1] + '" frameborder="0" allowfullscreen></iframe>';
                    preview.style.display = 'block';
                }
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>

