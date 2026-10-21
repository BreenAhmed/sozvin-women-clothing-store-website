<?php
// Connect to database and fetch featured products
require_once 'config/database.php';
require_once 'config/app.php';

try {
    $pdo = getDBConnection();
    
    // Fetch all active collections
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    $stmt->execute();
    $collections = $stmt->fetchAll();
    
    // Fetch all active products with their primary images
    $stmt = $pdo->prepare("
        SELECT p.*, 
               (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p 
        WHERE p.is_active = 1 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll();
    
} catch (Exception $e) {
    $collections = [];
    $featuredProducts = [];
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sozvin Lingerie - Modern & Sophisticated</title>
    <meta name="description" content="Discover our collection of elegant, modern lingerie designed for the confident woman. Premium quality, contemporary styles.">
    <link rel="stylesheet" href="<?php echo AppConfig::getCssUrl(); ?>?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <a href="#home" class="nav-link">الرئيسية</a>
                </li>
                <li class="nav-item">
                    <a href="#collections" class="nav-link">المجموعات</a>
                </li>
                <li class="nav-item">
                    <a href="videos.php" class="nav-link">الفيديوهات</a>
                </li>
                <li class="nav-item">
                    <a href="#about" class="nav-link">حول</a>
                </li>
                <li class="nav-item">
                    <a href="#contact" class="nav-link">اتصل بنا</a>
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

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1 class="hero-title">اعتنقي أناقتك</h1>
            <p class="hero-subtitle">اكتشفي مجموعتنا المختارة من الملابس الداخلية الأنيقة التي تحتفل بالمرأة العصرية</p>
            <div class="hero-buttons">
                <a href="#collections" class="btn btn-primary">تسوقي الآن</a>
                <a href="#about" class="btn btn-secondary">اعرفي المزيد</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-visual">
                <div class="hero-main-circle">
                    <i class="fas fa-venus"></i>
                </div>
                <div class="hero-floating-elements">
                    <div class="hero-floating-element"></div>
                    <div class="hero-floating-element"></div>
                    <div class="hero-floating-element"></div>
                    <div class="hero-floating-element"></div>
                    <div class="hero-floating-element"></div>
                    <div class="hero-floating-element"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Collections Section -->
    <section id="collections" class="collections">
        <div class="container">
            <h2 class="section-title">مجموعاتنا</h2>
            <p class="section-subtitle">سوزفين الخالدة تلتقي بالتصميم المعاصر</p>
            
            <?php if (!empty($collections)): ?>
                <div class="collections-grid">
                    <?php foreach ($collections as $collection): ?>
                        <div class="collection-card">
                            <div class="collection-image">
                                <?php if ($collection['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars(AppConfig::getImageUrl($collection['image_url'])); ?>" alt="<?php echo htmlspecialchars($collection['name']); ?>" class="collection-photo">
                                <?php else: ?>
                                    <div class="collection-icon">
                                        <?php 
                                        // Get appropriate icon for each collection as fallback
                                        $icon = 'fas fa-star';
                                        switch($collection['slug']) {
                                            case 'hijabs': $icon = 'fas fa-hijab'; break;
                                            case 'underwears': $icon = 'fas fa-venus'; break;
                                            case 'lingerie': $icon = 'fas fa-gem'; break;
                                            case 'sportswear': $icon = 'fas fa-dumbbell'; break;
                                            case 'sleeping-sets': $icon = 'fas fa-bed'; break;
                                        }
                                        ?>
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="collection-content">
                                <h3 class="collection-name"><?php echo htmlspecialchars($collection['name']); ?></h3>
                                <p class="collection-description"><?php echo htmlspecialchars($collection['description'] ?? ''); ?></p>
                                <div class="collection-stats">
                                    <?php
                                    // Count products in this collection
                                    try {
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE collection_id = ? AND is_active = 1");
                                        $stmt->execute([$collection['id']]);
                                        $productCount = $stmt->fetch()['count'];
                                    } catch (Exception $e) {
                                        $productCount = 0;
                                    }
                                    ?>
                                    <span class="product-count"><?php echo $productCount; ?> منتج</span>
                                </div>
                                
                                <a href="<?php echo AppConfig::getCollectionsUrl(); ?>/<?php echo htmlspecialchars($collection['slug']); ?>" class="explore-btn">
                                    <span class="explore-text">استكشفي المزيد</span>
                                    <i class="fas fa-arrow-left"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <h3>لا توجد مجموعات متاحة</h3>
                    <p>تفقدي قريباً لمجموعاتنا!</p>
                    <a href="<?php echo AppConfig::getAdminUrl(); ?>/login.php" class="btn btn-primary">إضافة مجموعات (الإدارة)</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <div class="products-section-header">
                <h2 class="section-title">منتجاتنا</h2>
                <p class="section-subtitle">اكتشفي مجموعتنا المختارة من الملابس الداخلية الأنيقة</p>
            </div>
            <?php if (!empty($featuredProducts)): ?>
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="product-card">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="product-image">
                                    <?php 
                                    $display_image = $product['primary_image'] ?? $product['image_url'];
                                    if ($display_image): 
                                    ?>
                                        <img src="<?php echo htmlspecialchars(AppConfig::getImageUrl($display_image)); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-venus"></i>
                                <?php endif; ?>
                            </div>
                            </a>
                            <div class="product-info">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                </a>
                                
                                <?php if (!empty($product['description'])): ?>
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <?php else: ?>
                                    <!-- Debug: No description found -->
                                    <p class="product-description" style="color: #999; font-style: italic;">لا يوجد وصف متاح</p>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['sizes']) || !empty($product['colors'])): ?>
                                    <div class="product-attributes">
                                        <?php if (!empty($product['sizes'])): ?>
                                            <div class="product-sizes">
                                                <span class="attribute-label">الأحجام:</span>
                                                <span class="attribute-values">
                                                    <?php 
                                                    $sizes = json_decode($product['sizes'], true);
                                                    if (is_array($sizes)) {
                                                        echo htmlspecialchars(implode(', ', $sizes));
                                                    } else {
                                                        echo htmlspecialchars($product['sizes']);
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($product['colors'])): ?>
                                            <div class="product-colors">
                                                <span class="attribute-label">الألوان:</span>
                                                <span class="attribute-values">
                                                    <?php 
                                                    $colors = json_decode($product['colors'], true);
                                                    if (is_array($colors)) {
                                                        echo htmlspecialchars(implode(', ', $colors));
                                                    } else {
                                                        echo htmlspecialchars($product['colors']);
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-price">
                                    <span class="price"><?php echo number_format($product['price'], 2); ?></span>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">عرض التفاصيل</a>
                                    <button class="btn btn-primary" style="flex: 1;">أضيفي للسلة</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <h3>لا توجد منتجات متاحة</h3>
                    <p>تفقدي قريباً لمنتجاتنا!</p>
                    <a href="<?php echo AppConfig::getAdminUrl(); ?>/login.php" class="btn btn-primary">إضافة منتجات (الإدارة)</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>حول سوزفين</h2>
                    <p>نحن نؤمن أن كل امرأة تستحق أن تشعر بالجمال والثقة والقوة. ملابسنا الداخلية مصنوعة بأفضل المواد مع الاهتمام بالتفاصيل، مما يضمن الراحة والأناقة معاً.</p>
                    <p>تأسست برؤية للاحتفال بالأنوثة، نصنع قطعاً تجعلك تشعرين بأفضل ما لديك، سواء كان للارتداء اليومي أو المناسبات الخاصة.</p>
                    
                    <div class="about-stats">
                        <div class="about-stat">
                            <span class="about-stat-number">15+</span>
                            <span class="about-stat-label">سنوات خبرة</span>
                        </div>
                        <div class="about-stat">
                            <span class="about-stat-number">50K+</span>
                            <span class="about-stat-label">عميلة سعيدة</span>
                        </div>
                        <div class="about-stat">
                            <span class="about-stat-number">200+</span>
                            <span class="about-stat-label">تصميم فريد</span>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <div class="about-visual">
                        <div class="about-main-circle">
                            <i class="fas fa-spa"></i>
                        </div>
                        <div class="about-floating-elements">
                            <div class="about-floating-element"></div>
                            <div class="about-floating-element"></div>
                            <div class="about-floating-element"></div>
                            <div class="about-floating-element"></div>
                            <div class="about-floating-element"></div>
                            <div class="about-floating-element"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="contact-info">
        <div class="container">
            <h2>معلومات الاتصال</h2>
            <p>تواصلي معنا أو زوري متجرنا</p>
            <div class="contact-grid">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h3>الهاتف</h3>
                        <p>0999496629</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h3>الموقع</h3>
                        <p>القامشلي مجمع السلام (سوق المركزي) او شارع البلدية<br>
                        فرع 1 المدخل 3<br>
                        فرع 2 المدخل 2</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-details">
                        <h3>ساعات العمل</h3>
                        <div class="working-hours">
                            <div class="hours-line">من الساعة 9 الصبح ل 8 المسا</div>
                            <div class="hours-line">كل الأسبوع ما عاد يوم الجمعة</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>سوزفين</h3>
                    <p>نحتفل بجمال وثقة كل امرأة من خلال تصميم الملابس الداخلية الأنيقة والعصرية. نؤمن بأن كل امرأة تستحق أن تشعر بالجمال والثقة.</p>
                    <div class="social-links">
                        <a href="https://www.instagram.com/sozvin_lingerie2024?igsh=MWVoMmJiNGt4enc4bQ%3D%3D&utm_source=qr" title="انستغرام" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/share/16VhHYLpXU/?mibextid=LQQJ4d" title="فيسبوك" target="_blank"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <li><a href="#home"><i class="fas fa-home"></i> الرئيسية</a></li>
                        <li><a href="#collections"><i class="fas fa-tshirt"></i> المجموعات</a></li>
                        <li><a href="#about"><i class="fas fa-info-circle"></i> حول</a></li>
                        <li><a href="#contact"><i class="fas fa-envelope"></i> اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>معلومات الاتصال</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> <span>0999496629</span></li>
                        <li><i class="fas fa-map-marker-alt"></i> <span>القامشلي مجمع السلام (سوق المركزي) او شارع البلدية<br>فرع 1 المدخل 3<br>فرع 2 المدخل 2</span></li>
                        <li><i class="fas fa-clock"></i> <span>من الساعة 9 الصبح ل 8 المسا</span></li>
                        <li><i class="fas fa-clock"></i> <span>كل الأسبوع ما عاد يوم الجمعة</span></li>
                    </ul>
                </div>
                
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 <a href="http://www.brezzo.co" target="_blank" style="color: #ff6b9d; text-decoration: none;">بريزو</a>. جميع الحقوق محفوظة</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo AppConfig::getJsUrl(); ?>?v=<?php echo time(); ?>"></script>
</body>
</html>
