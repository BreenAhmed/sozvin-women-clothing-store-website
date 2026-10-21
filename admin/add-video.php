<?php
require_once 'auth.php';
requireAdmin();

$success = $error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        $video_url = $_POST['video_url'] ?: null;
        $video_file = null;
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
            
            $file_info = pathinfo($_FILES['video_file']['name']);
            $file_extension = strtolower($file_info['extension']);
            
            // Validate file type (video formats)
            $allowed_types = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv', 'flv'];
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception("نوع الملف غير مسموح به. الأنواع المسموحة: MP4, WebM, OGG, MOV, AVI, WMV, FLV");
            }
            
            // Validate file size (100MB max)
            $max_size = 100 * 1024 * 1024; // 100MB
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
                $video_url = null; // Clear URL if file uploaded
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
        }
        
        // Validate: either video_url or video_file must be provided
        if (!$video_url && !$video_file) {
            // Check if URL method was selected
            $upload_method = $_POST['upload_method'] ?? 'url';
            if ($upload_method === 'url' && empty($_POST['video_url'])) {
                throw new Exception("يجب إدخال رابط فيديو أو رفع ملف فيديو");
            } elseif ($upload_method === 'file' && !isset($_FILES['video_file'])) {
                throw new Exception("يجب رفع ملف فيديو أو إدخال رابط فيديو");
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO videos (
                title, title_ar, description, description_ar,
                video_url, video_file, thumbnail_url, video_type,
                duration, is_active, is_featured, sort_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $_POST['sort_order'] ?: 0
        ]);
        
        $success = "تم إضافة الفيديو بنجاح!";
        $_POST = array(); // Clear form
        
    } catch (Exception $e) {
        $error = "خطأ في إضافة الفيديو: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة فيديو جديد - إدارة سوزفين</title>
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
            background: #28a745;
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
            background: #218838;
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
            <h2>إضافة فيديو جديد</h2>
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
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="title_ar">العنوان بالعربية</label>
                        <input type="text" id="title_ar" name="title_ar" value="<?php echo htmlspecialchars($_POST['title_ar'] ?? ''); ?>">
                        <div class="help-text">إذا تركت فارغاً، سيتم استخدام العنوان الأساسي</div>
                    </div>

                    <div class="form-group">
                        <label for="description">الوصف</label>
                        <textarea id="description" name="description" placeholder="وصف الفيديو..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description_ar">الوصف بالعربية</label>
                        <textarea id="description_ar" name="description_ar" placeholder="وصف الفيديو بالعربية..."><?php echo htmlspecialchars($_POST['description_ar'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-video"></i> الفيديو</h3>
                    
                    <div class="form-group">
                        <label>اختر طريقة إضافة الفيديو</label>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="upload_method" value="url" checked onchange="toggleUploadMethod()">
                                <span>رابط فيديو (YouTube, Vimeo, إلخ)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="upload_method" value="file" onchange="toggleUploadMethod()">
                                <span>رفع ملف فيديو</span>
                            </label>
                        </div>
                    </div>

                    <!-- URL Method -->
                    <div id="url_method">
                        <div class="form-group">
                            <label for="video_type">نوع الفيديو</label>
                            <select id="video_type" name="video_type" onchange="updateVideoHelp()">
                                <option value="youtube" <?php echo ($_POST['video_type'] ?? 'youtube') === 'youtube' ? 'selected' : ''; ?>>YouTube</option>
                                <option value="vimeo" <?php echo ($_POST['video_type'] ?? '') === 'vimeo' ? 'selected' : ''; ?>>Vimeo</option>
                                <option value="direct" <?php echo ($_POST['video_type'] ?? '') === 'direct' ? 'selected' : ''; ?>>رابط مباشر (MP4)</option>
                                <option value="embed" <?php echo ($_POST['video_type'] ?? '') === 'embed' ? 'selected' : ''; ?>>كود Embed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="video_url">رابط الفيديو</label>
                            <input type="text" id="video_url" name="video_url" value="<?php echo htmlspecialchars($_POST['video_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                            <div class="help-text" id="video_help">
                                مثال: https://www.youtube.com/watch?v=dQw4w9WgXcQ
                            </div>
                        </div>
                    </div>

                    <!-- File Upload Method -->
                    <div id="file_method" style="display: none;">
                        <div class="form-group">
                            <label for="video_file">رفع ملف فيديو <span style="color: red;">*</span></label>
                            <input type="file" id="video_file" name="video_file" accept="video/*">
                            <div class="help-text">
                                أنواع الملفات المسموحة: MP4, WebM, OGG, MOV, AVI, WMV, FLV<br>
                                الحد الأقصى لحجم الملف: 100MB
                            </div>
                            <div id="file_info" style="margin-top: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 5px; display: none;">
                                <span id="file_name"></span> (<span id="file_size"></span>)
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail_file">رفع صورة مصغرة</label>
                        <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
                        <div class="help-text">أو استخدمي رابط صورة مصغرة أدناه</div>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail_url">رابط صورة مصغرة (Thumbnail)</label>
                        <input type="text" id="thumbnail_url" name="thumbnail_url" value="<?php echo htmlspecialchars($_POST['thumbnail_url'] ?? ''); ?>" placeholder="https://example.com/thumbnail.jpg">
                        <div class="help-text">رابط صورة مصغرة للفيديو (اختياري)</div>
                    </div>

                    <div id="video_preview" class="video-preview" style="display: none;"></div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> الإعدادات</h3>
                    
                    <div class="form-group">
                        <label for="duration">المدة (بالثواني)</label>
                        <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($_POST['duration'] ?? '0'); ?>" min="0">
                        <div class="help-text">مدة الفيديو بالثواني (اختياري)</div>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">ترتيب العرض</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($_POST['sort_order'] ?? '0'); ?>" min="0">
                        <div class="help-text">الأرقام الأصغر تظهر أولاً</div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>>
                        <label for="is_active">فيديو نشط</label>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                        <label for="is_featured">فيديو مميز</label>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus"></i> إضافة الفيديو
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleUploadMethod() {
            const method = document.querySelector('input[name="upload_method"]:checked').value;
            const urlMethod = document.getElementById('url_method');
            const fileMethod = document.getElementById('file_method');
            const videoUrl = document.getElementById('video_url');
            const videoFile = document.getElementById('video_file');
            
            if (method === 'url') {
                urlMethod.style.display = 'block';
                fileMethod.style.display = 'none';
                videoUrl.removeAttribute('required');
                videoFile.removeAttribute('required');
            } else {
                urlMethod.style.display = 'none';
                fileMethod.style.display = 'block';
                videoUrl.removeAttribute('required');
                videoFile.setAttribute('required', 'required');
            }
        }

        function updateVideoHelp() {
            const type = document.getElementById('video_type').value;
            const help = document.getElementById('video_help');
            const urlInput = document.getElementById('video_url');
            
            switch(type) {
                case 'youtube':
                    help.textContent = 'مثال: https://www.youtube.com/watch?v=dQw4w9WgXcQ أو https://youtu.be/dQw4w9WgXcQ';
                    urlInput.placeholder = 'https://www.youtube.com/watch?v=...';
                    break;
                case 'vimeo':
                    help.textContent = 'مثال: https://vimeo.com/123456789';
                    urlInput.placeholder = 'https://vimeo.com/...';
                    break;
                case 'direct':
                    help.textContent = 'مثال: https://example.com/video.mp4';
                    urlInput.placeholder = 'https://example.com/video.mp4';
                    break;
                case 'embed':
                    help.textContent = 'الصق كود iframe الكامل هنا';
                    urlInput.placeholder = '<iframe src="..."></iframe>';
                    break;
            }
        }

        // Show file info when selected
        document.getElementById('video_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const fileInfo = document.getElementById('file_info');
            const fileName = document.getElementById('file_name');
            const fileSize = document.getElementById('file_size');
            
            if (file) {
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                fileInfo.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
            }
        });

        // Preview video on URL change
        document.getElementById('video_url').addEventListener('input', function() {
            const url = this.value;
            const type = document.getElementById('video_type').value;
            const preview = document.getElementById('video_preview');
            
            if (url && type === 'youtube') {
                // Extract YouTube video ID
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
                // Extract Vimeo video ID
                const match = url.match(/vimeo.com\/(\d+)/);
                if (match) {
                    preview.innerHTML = '<iframe src="https://player.vimeo.com/video/' + match[1] + '" frameborder="0" allowfullscreen></iframe>';
                    preview.style.display = 'block';
                }
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>

