<?php
// Dynamic collection display page - works for any collection
require_once '../config/database.php';
require_once '../config/app.php';

// Get collection slug from URL parameter
$collectionSlug = isset($_GET['collection']) ? $_GET['collection'] : '';

if (empty($collectionSlug)) {
    // No collection specified, redirect to main page
    header('Location: ../index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get collection details by slug
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE slug = ? AND is_active = 1");
    $stmt->execute([$collectionSlug]);
    $collection = $stmt->fetch();
    
    if (!$collection) {
        // Collection not found, redirect to main page
        header('Location: ../index.php');
        exit;
    }
    
    // Get products for this collection using JOIN to ensure relationship
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as collection_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p 
        INNER JOIN collections c ON p.collection_id = c.id 
        WHERE c.slug = ? AND p.is_active = 1 AND c.is_active = 1 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$collectionSlug]);
    $products = $stmt->fetchAll();
    
} catch (Exception $e) {
    // Database error, redirect to main page
    header('Location: ../index.php');
    exit;
}

// Get appropriate icon for the collection
function getCollectionIcon($slug) {
    $icons = [
        'hijabs' => 'fas fa-hijab',
        'underwears' => 'fas fa-venus',
        'lingerie' => 'fas fa-gem',
        'sportswear' => 'fas fa-dumbbell',
        'sleeping-sets' => 'fas fa-bed',
        'dresses' => 'fas fa-female',
        'shoes' => 'fas fa-shoe-prints',
        'accessories' => 'fas fa-gem',
        'jewelry' => 'fas fa-ring',
        'bags' => 'fas fa-shopping-bag',
        'cosmetics' => 'fas fa-palette',
        'perfumes' => 'fas fa-spray-can',
        'shoes' => 'fas fa-shoe-prints',
        'winter' => 'fas fa-snowflake',
        'summer' => 'fas fa-sun',
        'formal' => 'fas fa-user-tie',
        'casual' => 'fas fa-tshirt',
        'evening' => 'fas fa-moon',
        'bridal' => 'fas fa-heart',
        'maternity' => 'fas fa-baby'
    ];
    return isset($icons[$slug]) ? $icons[$slug] : 'fas fa-star';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($collection['name']); ?> - سوزفين</title>
    <meta name="description" content="<?php echo htmlspecialchars($collection['description'] ?? ''); ?>">
    <link rel="stylesheet" href="../styles.css">
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
                    <a href="../index.php#home" class="nav-link">الرئيسية</a>
                </li>
                <li class="nav-item">
                    <a href="../index.php#collections" class="nav-link">المجموعات</a>
                </li>
                <li class="nav-item">
                    <a href="../index.php#about" class="nav-link">حول</a>
                </li>
                <li class="nav-item">
                    <a href="../index.php#contact" class="nav-link">اتصل بنا</a>
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

    <!-- Collection Header -->
    <section class="collection-header">
        <div class="collection-floating-elements">
            <div class="collection-floating-element"></div>
            <div class="collection-floating-element"></div>
            <div class="collection-floating-element"></div>
            <div class="collection-floating-element"></div>
        </div>
        <div class="container">
            <div class="collection-header-content">
                <div class="breadcrumb-nav">
                    <a href="../index.php" class="breadcrumb-link">الرئيسية</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="../index.php#collections" class="breadcrumb-link">المجموعات</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current"><?php echo htmlspecialchars($collection['name']); ?></span>
                </div>
                
                <div class="collection-icon-large">
                    <i class="<?php echo getCollectionIcon($collection['slug']); ?>"></i>
                </div>
                <h1 class="collection-title"><?php echo htmlspecialchars($collection['name']); ?></h1>
                <p class="collection-subtitle"><?php echo htmlspecialchars($collection['description'] ?? ''); ?></p>
                <a href="../index.php#collections" class="back-to-collections">
                    <i class="fas fa-arrow-right"></i>
                    العودة للمجموعات
                </a>
            </div>
        </div>
    </section>

    <!-- Collection Products -->
    <section class="collection-products">
        <div class="container">
            <div class="collection-info">
                <h2>منتجات المجموعة</h2>
                <p>اكتشفي مجموعتنا المختارة من <?php echo htmlspecialchars($collection['name']); ?> المصممة بأحدث الصيحات</p>
                
                <div class="collection-stats">
                    <div class="collection-stat">
                        <span class="collection-stat-number"><?php echo count($products); ?></span>
                        <span class="collection-stat-label">منتج</span>
                    </div>
                    <div class="collection-stat">
                        <span class="collection-stat-number"><?php echo count(array_unique(array_column($products, 'collection_id'))); ?></span>
                        <span class="collection-stat-label">مجموعة</span>
                    </div>
                    <div class="collection-stat">
                        <span class="collection-stat-number"><?php echo array_sum(array_column($products, 'price')) > 0 ? number_format(array_sum(array_column($products, 'price')), 0) : '0'; ?></span>
                        <span class="collection-stat-label">إجمالي القيمة</span>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($products)): ?>
                <div class="products-grid" id="collectionProductsGrid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <a href="../product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
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
                                <a href="../product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
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
                                <a href="../product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">عرض التفاصيل</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <h3>لا توجد منتجات في هذه المجموعة حالياً</h3>
                    <p>تفقدي قريباً لمنتجاتنا!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>سوزفين</h3>
                    <p>نحتفل بجمال وثقة كل امرأة من خلال تصميم الملابس الداخلية الأنيقة.</p>
                </div>
                
                <div class="footer-section">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <li><a href="../index.php#home">الرئيسية</a></li>
                        <li><a href="../index.php#collections">المجموعات</a></li>
                        <li><a href="../index.php#about">حول</a></li>
                        <li><a href="../index.php#contact">اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>معلومات الاتصال</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> <span>0999496629</span></li>
                        <li><i class="fas fa-map-marker-alt"></i> <span>القامشلي مجمع السلام (سوق المركزي) او شارع البلدية<br>فرع 1 المدخل 3<br>فرع 2 المدخل 2</span></li>
                        <li><i class="fas fa-clock"></i> <span>من الساعة 9 الصبح ل 8 المسا، كل الأسبوع ما عاد يوم الجمعة</span></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>تواصلي معنا</h4>
                    <div class="social-links">
                        <a href="https://www.instagram.com/sozvin_lingerie2024?igsh=MWVoMmJiNGt4enc4bQ%3D%3D&utm_source=qr" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/share/16VhHYLpXU/?mibextid=LQQJ4d" target="_blank"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 <a href="http://www.brezzo.co" target="_blank" style="color: #ff6b9d; text-decoration: none;">بريزو</a>. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <script src="../script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
