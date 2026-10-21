<?php
require_once 'config/database.php';
require_once 'config/app.php';

try {
    $pdo = getDBConnection();
    
    // Get featured videos first, then others
    $stmt = $pdo->prepare("
        SELECT * FROM videos 
        WHERE is_active = 1 
        ORDER BY is_featured DESC, sort_order ASC, created_at DESC
    ");
    $stmt->execute();
    $videos = $stmt->fetchAll();
    
} catch (Exception $e) {
    $videos = [];
    error_log("Videos page error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفيديوهات - سوزفين</title>
    <meta name="description" content="شاهدي فيديوهاتنا المميزة من أحدث المجموعات والتصاميم">
    <link rel="stylesheet" href="<?php echo AppConfig::getCssUrl(); ?>?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .videos-page {
            padding: 6rem 0 3rem;
            background: #f8f9fa;
        }
        
        .videos-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            color: #2c2c2c;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            font-size: 1.2rem;
            color: #666;
        }
        
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .video-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .video-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .video-thumbnail {
            width: 100%;
            height: 250px;
            background: #f0f0f0;
            position: relative;
            overflow: hidden;
        }
        
        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .video-thumbnail .play-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .video-card:hover .play-overlay {
            background: rgba(0,0,0,0.5);
        }
        
        .play-icon {
            font-size: 4rem;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }
        
        .video-card:hover .play-icon {
            transform: scale(1.2);
        }
        
        .video-info {
            padding: 1.5rem;
        }
        
        .video-info h3 {
            font-size: 1.3rem;
            color: #2c2c2c;
            margin-bottom: 0.5rem;
        }
        
        .video-info p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #999;
        }
        
        .featured-badge {
            background: linear-gradient(135deg, #ff6b9d, #ff8fab);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .no-videos {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .no-videos i {
            font-size: 5rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .no-videos h3 {
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        /* Video Modal */
        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .video-modal.active {
            display: flex;
        }
        
        .modal-content {
            max-width: 90%;
            max-height: 90%;
            width: 1000px;
            position: relative;
        }
        
        .modal-content iframe {
            width: 100%;
            height: 600px;
            border-radius: 10px;
        }
        
        .modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 3rem;
            cursor: pointer;
            z-index: 2001;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }
        
        .modal-info {
            color: white;
            margin-top: 1rem;
        }
        
        .modal-info h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .modal-info p {
            color: rgba(255,255,255,0.8);
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .videos-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .modal-content {
                max-width: 95%;
            }
            
            .modal-content iframe {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>سوزفين</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php#home" class="nav-link">الرئيسية</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#collections" class="nav-link">المجموعات</a>
                </li>
                <li class="nav-item">
                    <a href="videos.php" class="nav-link" style="color: #ff6b9d; font-weight: 600;">الفيديوهات</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#about" class="nav-link">حول</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#contact" class="nav-link">اتصل بنا</a>
                </li>
            </ul>
            <div class="nav-icons">
                <a href="#" class="nav-icon"><i class="fas fa-shopping-bag"></i></a>
            </div>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <section class="videos-page">
        <div class="videos-container">
            <div class="page-header">
                <h1>الفيديوهات</h1>
                <p>شاهدي أحدث فيديوهاتنا المميزة</p>
            </div>
            
            <?php if (empty($videos)): ?>
                <div class="no-videos">
                    <i class="fas fa-video"></i>
                    <h3>لا توجد فيديوهات متاحة حالياً</h3>
                    <p>تفقدي قريباً لفيديوهاتنا الجديدة!</p>
                </div>
            <?php else: ?>
                <div class="videos-grid">
                    <?php foreach ($videos as $video): ?>
                        <div class="video-card" onclick="openVideo(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['video_file'] ?? $video['video_url'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($video['video_type']); ?>', '<?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($video['description'] ?? '', ENT_QUOTES); ?>')">
                            <div class="video-thumbnail">
                                <?php if ($video['thumbnail_url']): ?>
                                    <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <?php endif; ?>
                                <?php if (!$video['thumbnail_url'] || $video['video_type'] === 'upload'): ?>
                                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: <?php echo $video['thumbnail_url'] ? 'none' : 'block'; ?>;"></div>
                                <?php endif; ?>
                                <div class="play-overlay">
                                    <i class="fas fa-play-circle play-icon"></i>
                                </div>
                                <?php if ($video['video_type'] === 'upload'): ?>
                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.9); color: #333; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="fas fa-upload"></i> مرفوع
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="video-info">
                                <?php if ($video['is_featured']): ?>
                                    <span class="featured-badge">مميز</span>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                                <?php if ($video['description']): ?>
                                    <p><?php echo htmlspecialchars($video['description']); ?></p>
                                <?php endif; ?>
                                <div class="video-meta">
                                    <span><i class="fas fa-eye"></i> <?php echo number_format($video['views']); ?> مشاهدة</span>
                                    <?php if ($video['duration'] > 0): ?>
                                        <span><i class="fas fa-clock"></i> <?php echo gmdate("i:s", $video['duration']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Video Modal -->
    <div class="video-modal" id="videoModal" onclick="closeVideoModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <span class="modal-close" onclick="closeVideoModal()">&times;</span>
            <div id="videoPlayer"></div>
            <div class="modal-info">
                <h3 id="modalTitle"></h3>
                <p id="modalDescription"></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>سوزفين</h3>
                    <p>نحتفل بجمال وثقة كل امرأة من خلال تصميم الملابس الداخلية الأنيقة والعصرية.</p>
                    <div class="social-links">
                        <a href="https://www.instagram.com/sozvin_lingerie2024?igsh=MWVoMmJiNGt4enc4bQ%3D%3D&utm_source=qr" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/share/16VhHYLpXU/?mibextid=LQQJ4d" target="_blank"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                        <li><a href="index.php#collections"><i class="fas fa-tshirt"></i> المجموعات</a></li>
                        <li><a href="videos.php"><i class="fas fa-video"></i> الفيديوهات</a></li>
                        <li><a href="index.php#about"><i class="fas fa-info-circle"></i> حول</a></li>
                        <li><a href="index.php#contact"><i class="fas fa-envelope"></i> اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>معلومات الاتصال</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> <span>0999496629</span></li>
                        <li><i class="fas fa-map-marker-alt"></i> <span>القامشلي مجمع السلام (سوق المركزي)</span></li>
                        <li><i class="fas fa-clock"></i> <span>من الساعة 9 صباحاً إلى 8 مساءً</span></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 <a href="http://www.brezzo.co" target="_blank" style="color: #ff6b9d; text-decoration: none;">بريزو</a>. جميع الحقوق محفوظة</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo AppConfig::getJsUrl(); ?>?v=<?php echo time(); ?>"></script>
    <script>
        function openVideo(id, url, type, title, description) {
            const modal = document.getElementById('videoModal');
            const player = document.getElementById('videoPlayer');
            const modalTitle = document.getElementById('modalTitle');
            const modalDescription = document.getElementById('modalDescription');
            
            let embedUrl = '';
            let videoHtml = '';
            
            if (type === 'upload') {
                // Uploaded video file - use HTML5 video player
                videoHtml = '<video controls autoplay style="width: 100%; height: 600px; border-radius: 10px;"><source src="' + url + '" type="video/mp4">Your browser does not support the video tag.</video>';
                player.innerHTML = videoHtml;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                modalTitle.textContent = title;
                modalDescription.textContent = description;
                return;
            } else if (type === 'youtube') {
                // Extract YouTube video ID
                let videoId = '';
                if (url.includes('youtube.com/watch?v=')) {
                    videoId = url.split('v=')[1].split('&')[0];
                } else if (url.includes('youtu.be/')) {
                    videoId = url.split('youtu.be/')[1].split('?')[0];
                }
                if (videoId) {
                    embedUrl = 'https://www.youtube.com/embed/' + videoId;
                }
            } else if (type === 'vimeo') {
                const match = url.match(/vimeo.com\/(\d+)/);
                if (match) {
                    embedUrl = 'https://player.vimeo.com/video/' + match[1];
                }
            } else if (type === 'direct') {
                // Direct MP4 link - use HTML5 video player
                videoHtml = '<video controls autoplay style="width: 100%; height: 600px; border-radius: 10px;"><source src="' + url + '" type="video/mp4">Your browser does not support the video tag.</video>';
                player.innerHTML = videoHtml;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                modalTitle.textContent = title;
                modalDescription.textContent = description;
                return;
            } else if (type === 'embed') {
                player.innerHTML = url;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                modalTitle.textContent = title;
                modalDescription.textContent = description;
                return;
            }
            
            if (embedUrl) {
                player.innerHTML = '<iframe src="' + embedUrl + '" frameborder="0" allowfullscreen style="width: 100%; height: 600px; border-radius: 10px;"></iframe>';
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                modalTitle.textContent = title;
                modalDescription.textContent = description;
            }
        }
        
        function closeVideoModal(event) {
            if (!event || event.target.classList.contains('video-modal') || event.target.classList.contains('modal-close')) {
                const modal = document.getElementById('videoModal');
                const player = document.getElementById('videoPlayer');
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
                player.innerHTML = '';
            }
        }
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeVideoModal();
            }
        });
    </script>
</body>
</html>

