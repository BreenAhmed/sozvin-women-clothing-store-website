<?php
require_once 'config/database.php';
require_once 'config/app.php';

// Get product slug or ID from URL
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: index.php');
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Get product data
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: index.php');
        exit();
    }
    
    // Get product images
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, image_order ASC");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll();
    
    // Get collection info
    $collection = null;
    if ($product['collection_id']) {
        $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ?");
        $stmt->execute([$product['collection_id']]);
        $collection = $stmt->fetch();
    }
    
    // Get related products from same collection
    $related_products = [];
    if ($product['collection_id']) {
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM products p 
            WHERE p.collection_id = ? AND p.id != ? AND p.is_active = 1 
            ORDER BY RAND() 
            LIMIT 4
        ");
        $stmt->execute([$product['collection_id'], $product_id]);
        $related_products = $stmt->fetchAll();
    }
    
} catch (Exception $e) {
    error_log("Product detail error: " . $e->getMessage());
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - سوزفين</title>
    <meta name="description" content="<?php echo htmlspecialchars($product['description'] ?? ''); ?>">
    <link rel="stylesheet" href="<?php echo AppConfig::getCssUrl(); ?>?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-detail {
            padding: 6rem 0 3rem;
            background: #f8f9fa;
        }
        
        .product-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .breadcrumb {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .product-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .product-gallery {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
            cursor: zoom-in;
            transition: transform 0.3s ease;
        }
        
        .main-image:hover {
            transform: scale(1.02);
        }
        
        .thumbnail-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.5rem;
        }
        
        .thumbnail {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .thumbnail:hover,
        .thumbnail.active {
            border-color: #ff6b9d;
            transform: scale(1.05);
        }
        
        .product-info h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #2c2c2c;
        }
        
        .product-price {
            font-size: 2rem;
            color: #ff6b9d;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .product-description {
            color: #666;
            line-height: 1.8;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .product-attributes {
            margin-bottom: 2rem;
        }
        
        .attribute-group {
            margin-bottom: 1.5rem;
        }
        
        .attribute-label {
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .attribute-options {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .attribute-option {
            padding: 0.5rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .attribute-option:hover,
        .attribute-option.selected {
            border-color: #ff6b9d;
            background: #fff0f5;
        }
        
        .collection-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-decoration: none;
        }
        
        .collection-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-add-to-cart {
            background: linear-gradient(135deg, #ff6b9d, #ff8fab);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-add-to-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 107, 157, 0.3);
        }
        
        .related-products {
            margin-top: 4rem;
        }
        
        .related-products h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c2c2c;
        }
        
        /* Image Modal */
        .image-modal {
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
        }
        
        .image-modal.active {
            display: flex;
        }
        
        .modal-content {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }
        
        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 3rem;
            cursor: pointer;
            z-index: 2001;
        }
        
        .modal-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 3rem;
            cursor: pointer;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-nav.prev {
            left: 20px;
        }
        
        .modal-nav.next {
            right: 20px;
        }
        
        .modal-nav:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        
        @media (max-width: 768px) {
            .product-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .product-gallery {
                position: relative;
                top: 0;
            }
            
            .main-image {
                height: 400px;
            }
            
            .product-info h1 {
                font-size: 1.5rem;
            }
            
            .product-price {
                font-size: 1.5rem;
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

    <section class="product-detail">
        <div class="product-detail-container">
            <div class="breadcrumb">
                <a href="index.php">الرئيسية</a>
                <i class="fas fa-chevron-left"></i>
                <?php if ($collection): ?>
                    <a href="collections/<?php echo htmlspecialchars($collection['slug']); ?>"><?php echo htmlspecialchars($collection['name']); ?></a>
                    <i class="fas fa-chevron-left"></i>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>
            
            <div class="product-content">
                <div class="product-gallery">
                    <?php 
                    $main_image = !empty($product_images) ? $product_images[0]['image_url'] : $product['image_url'];
                    if ($main_image):
                    ?>
                        <img src="<?php echo htmlspecialchars(AppConfig::getImageUrl($main_image)); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="main-image" 
                             id="mainImage"
                             onclick="openImageModal(0)">
                        
                        <?php if (count($product_images) > 1): ?>
                            <div class="thumbnail-gallery">
                                <?php foreach ($product_images as $index => $img): ?>
                                    <img src="<?php echo htmlspecialchars(AppConfig::getImageUrl($img['image_url'])); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                         onclick="changeMainImage('<?php echo htmlspecialchars(AppConfig::getImageUrl($img['image_url'])); ?>', this, <?php echo $index; ?>)">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="width: 100%; height: 500px; background: #f0f0f0; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-image" style="font-size: 4rem; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <?php if ($collection): ?>
                        <a href="collections/<?php echo htmlspecialchars($collection['slug']); ?>" class="collection-badge">
                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($collection['name']); ?>
                        </a>
                    <?php endif; ?>
                    
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-price">
                        <?php echo number_format($product['price'], 2); ?> ل.س
                    </div>
                    
                    <?php if (!empty($product['description'])): ?>
                        <p class="product-description">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="product-attributes">
                        <?php if (!empty($product['sizes'])): ?>
                            <div class="attribute-group">
                                <span class="attribute-label">المقاسات المتاحة:</span>
                                <div class="attribute-options">
                                    <?php 
                                    $sizes = json_decode($product['sizes'], true);
                                    if (is_array($sizes)):
                                        foreach ($sizes as $size): 
                                    ?>
                                        <div class="attribute-option" onclick="this.classList.toggle('selected')">
                                            <?php echo htmlspecialchars($size); ?>
                                        </div>
                                    <?php 
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['colors'])): ?>
                            <div class="attribute-group">
                                <span class="attribute-label">الألوان المتاحة:</span>
                                <div class="attribute-options">
                                    <?php 
                                    $colors = json_decode($product['colors'], true);
                                    if (is_array($colors)):
                                        foreach ($colors as $color): 
                                    ?>
                                        <div class="attribute-option" onclick="this.classList.toggle('selected')">
                                            <?php echo htmlspecialchars($color); ?>
                                        </div>
                                    <?php 
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button class="btn-add-to-cart" onclick="addToCartFromDetail()">
                        <i class="fas fa-shopping-cart"></i>
                        أضيفي للسلة
                    </button>
                </div>
            </div>
            
            <?php if (!empty($related_products)): ?>
                <div class="related-products">
                    <h2>منتجات مشابهة</h2>
                    <div class="products-grid">
                        <?php foreach ($related_products as $related): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if ($related['primary_image'] ?? $related['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars(AppConfig::getImageUrl($related['primary_image'] ?? $related['image_url'])); ?>" 
                                             alt="<?php echo htmlspecialchars($related['name']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-venus"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                                    <div class="product-price">
                                        <span class="price"><?php echo number_format($related['price'], 2); ?></span>
                                    </div>
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">عرض التفاصيل</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Image Modal -->
    <div class="image-modal" id="imageModal">
        <span class="modal-close" onclick="closeImageModal()">&times;</span>
        <span class="modal-nav prev" onclick="navigateImage(-1)">
            <i class="fas fa-chevron-left"></i>
        </span>
        <img src="" alt="" class="modal-content" id="modalImage">
        <span class="modal-nav next" onclick="navigateImage(1)">
            <i class="fas fa-chevron-right"></i>
        </span>
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
        // Image gallery functionality
        const productImages = <?php echo json_encode(array_map(function($img) { 
            return AppConfig::getImageUrl($img['image_url']); 
        }, $product_images)); ?>;
        let currentImageIndex = 0;
        
        function changeMainImage(src, thumbnail, index) {
            document.getElementById('mainImage').src = src;
            currentImageIndex = index;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
        
        function openImageModal(index) {
            currentImageIndex = index;
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.classList.add('active');
            modalImg.src = productImages[currentImageIndex];
            document.body.style.overflow = 'hidden';
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        function navigateImage(direction) {
            currentImageIndex += direction;
            if (currentImageIndex < 0) currentImageIndex = productImages.length - 1;
            if (currentImageIndex >= productImages.length) currentImageIndex = 0;
            
            document.getElementById('modalImage').src = productImages[currentImageIndex];
            document.getElementById('mainImage').src = productImages[currentImageIndex];
            
            // Update thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            if (thumbnails[currentImageIndex]) {
                thumbnails[currentImageIndex].classList.add('active');
            }
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            } else if (e.key === 'ArrowLeft') {
                navigateImage(1);
            } else if (e.key === 'ArrowRight') {
                navigateImage(-1);
            }
        });
        
        // Add to cart from detail page
        function addToCartFromDetail() {
            const productName = '<?php echo addslashes($product['name']); ?>';
            const productPrice = '<?php echo number_format($product['price'], 2); ?>';
            
            addToCart(productName, productPrice);
            showCartNotification(productName, productPrice);
            updateCartIcon();
        }
    </script>
</body>
</html>

