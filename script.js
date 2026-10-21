// Set Arabic as default language
let currentLanguage = 'ar';

// Language data
const languages = {
    en: {
        name: 'English',
        code: 'en',
        direction: 'ltr'
    },
    ar: {
        name: 'العربية',
        code: 'ar',
        direction: 'rtl'
    }
};

// Mobile scroll functionality
function initMobileScroll() {
    // Ensure mobile scrolling works
    const productsGrids = document.querySelectorAll('.products-grid');
    
    productsGrids.forEach(grid => {
        // Force mobile scroll styles
        if (window.innerWidth <= 768) {
            grid.style.display = 'flex';
            grid.style.flexDirection = 'row';
            grid.style.flexWrap = 'nowrap';
            grid.style.overflowX = 'auto';
            grid.style.overflowY = 'hidden';
            grid.style.scrollSnapType = 'x mandatory';
            grid.style.webkitOverflowScrolling = 'touch';
            grid.style.width = '100%';
            grid.style.maxWidth = 'none';
            grid.style.gap = '1.5rem';
            
            // Force product cards to be inline
            const cards = grid.querySelectorAll('.product-card');
            cards.forEach(card => {
                card.style.flex = '0 0 280px';
                card.style.minWidth = '280px';
                card.style.maxWidth = '280px';
                card.style.width = '280px';
                card.style.display = 'block';
                card.style.margin = '0';
            });
            
            // Add touch event handling for better mobile experience
            let isDown = false;
            let startX;
            let scrollLeft;
            
            grid.addEventListener('touchstart', (e) => {
                isDown = true;
                startX = e.touches[0].pageX - grid.offsetLeft;
                scrollLeft = grid.scrollLeft;
            });
            
            grid.addEventListener('touchend', () => {
                isDown = false;
            });
            
            grid.addEventListener('touchmove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.touches[0].pageX - grid.offsetLeft;
                const walk = (x - startX) * 2;
                grid.scrollLeft = scrollLeft - walk;
            });
        }
    });
}

// Initialize Arabic language
function initLanguageSwitch() {
    // Set Arabic as default
    setLanguage('ar');
}

// Set language function (Arabic only)
function setLanguage(lang) {
    const body = document.body;
    
    // Always set to Arabic
    body.setAttribute('data-lang', 'ar');
    body.setAttribute('dir', 'rtl');
    document.documentElement.setAttribute('dir', 'rtl');
    document.documentElement.setAttribute('lang', 'ar');
    
    // Update document title and meta description for Arabic
    updateDocumentMeta('ar');
}

// Text content is now hardcoded in Arabic in HTML

// Language switch button removed

// Update document meta information (Arabic only)
function updateDocumentMeta(lang) {
    const title = document.querySelector('title');
    const metaDescription = document.querySelector('meta[name="description"]');
    
    if (title) {
        title.textContent = 'سوزفين الملابس الداخلية - عصرية ومتطورة';
    }
    if (metaDescription) {
        metaDescription.setAttribute('content', 'اكتشفي مجموعتنا من الملابس الداخلية الأنيقة والعصرية المصممة للمرأة الواثقة. جودة عالية وأساليب معاصرة.');
    }
}

// Load saved language preference
function loadSavedLanguage() {
    const savedLang = localStorage.getItem('preferred-language');
    if (savedLang && languages[savedLang]) {
        currentLanguage = savedLang;
    }
}

// Mobile Navigation Toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
}));

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Navbar background change on scroll
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
        navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
    } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = 'none';
    }
});

// Products layout switching for mobile
function handleProductsLayout() {
    const productsGrid = document.getElementById('productsGrid');
    if (!productsGrid) return;
    
    if (window.innerWidth <= 768) {
        productsGrid.classList.add('mobile-scroll');
        productsGrid.classList.remove('products-grid');
    } else {
        productsGrid.classList.remove('mobile-scroll');
        productsGrid.classList.add('products-grid');
    }
}

// Initialize products layout
handleProductsLayout();

// Handle resize events
window.addEventListener('resize', handleProductsLayout);

// Newsletter form submission
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = e.target.querySelector('input[type="email"]').value;
        
        // Show success message
        const successMessage = document.createElement('div');
        successMessage.textContent = currentLanguage === 'en' ? 'Thank you for subscribing!' : 'شكراً لك على الاشتراك!';
        successMessage.style.cssText = `
            color: white;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 25px;
            margin-top: 1rem;
            font-weight: 500;
        `;
        
        e.target.appendChild(successMessage);
        e.target.querySelector('input[type="email"]').value = '';
        
        // Remove message after 3 seconds
        setTimeout(() => {
            successMessage.remove();
        }, 3000);
    });
}

// Cart functionality
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Collection navigation functionality is now handled by direct links to collection pages

// Add to cart functionality
document.querySelectorAll('.btn-primary').forEach(button => {
    if (button.textContent === 'Add to Cart' || button.textContent === 'أضيفي للسلة') {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card, .product-item');
            const productName = productCard.querySelector('h3').textContent;
            const productPrice = productCard.querySelector('.product-price').textContent;
            
            // Add item to cart
            addToCart(productName, productPrice);
            
            // Show cart notification
            showCartNotification(productName, productPrice);
            
            // Update cart icon
            updateCartIcon();
        });
    }
});

// Cart management functions
function addToCart(productName, productPrice) {
    // Check if item already exists in cart
    const existingItem = cart.find(item => item.name === productName);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            name: productName,
            price: productPrice,
            quantity: 1,
            id: Date.now() + Math.random() // Unique ID
        });
    }
    
    // Save to localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
}

function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartIcon();
    
    // Refresh cart modal if it's open
    const cartModal = document.querySelector('.cart-modal');
    if (cartModal) {
        showCartModal();
        cartModal.remove();
    }
}

function updateItemQuantity(itemId, newQuantity) {
    const item = cart.find(item => item.id === itemId);
    if (item) {
        if (newQuantity <= 0) {
            removeFromCart(itemId);
        } else {
            item.quantity = newQuantity;
            localStorage.setItem('cart', JSON.stringify(cart));
        }
        
        // Refresh cart modal if it's open
        const cartModal = document.querySelector('.cart-modal');
        if (cartModal) {
            showCartModal();
            cartModal.remove();
        }
    }
}

function getCartTotal() {
    return cart.reduce((total, item) => {
        const price = parseFloat(item.price.replace('$', ''));
        return total + (price * item.quantity);
    }, 0);
}

function getCartItemCount() {
    return cart.reduce((total, item) => total + item.quantity, 0);
}

function updateCartIcon() {
    const cartIcon = document.querySelector('.fa-shopping-bag');
    if (cartIcon) {
        const itemCount = getCartItemCount();
        if (itemCount > 0) {
            // Remove existing badge if any
            const existingBadge = cartIcon.parentElement.querySelector('.cart-badge');
            if (existingBadge) {
                existingBadge.remove();
            }
            
            // Add new badge
            const badge = document.createElement('span');
            badge.className = 'cart-badge';
            badge.textContent = itemCount > 99 ? '99+' : itemCount;
            badge.style.cssText = `
                position: absolute;
                top: -8px;
                right: -8px;
                background: #ff6b9d;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                font-size: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
            `;
            
            cartIcon.parentElement.style.position = 'relative';
            cartIcon.parentElement.appendChild(badge);
        } else {
            // Remove badge if cart is empty
            const existingBadge = cartIcon.parentElement.querySelector('.cart-badge');
            if (existingBadge) {
                existingBadge.remove();
            }
        }
    }
}

function showCartNotification(productName, productPrice) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: white;
        color: #333;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        max-width: 300px;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    `;
    
    const addedText = currentLanguage === 'en' ? 'added to cart' : 'أضيف للسلة';
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle" style="color: #28a745; font-size: 1.2rem;"></i>
            <div>
                <strong>${productName}</strong> ${addedText}
                <div style="color: #ff6b9d; font-weight: 600; margin-top: 5px;">${productPrice}</div>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.collection-card, .product-card');
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
    
    // Initialize language switch
    loadSavedLanguage();
    initLanguageSwitch();
    
    // Initialize mobile scroll
    initMobileScroll();
    
    // Re-initialize mobile scroll on window resize
    window.addEventListener('resize', initMobileScroll);
    
    // Initialize cart icon
    updateCartIcon();
    
    
});






// Shopping bag functionality
document.querySelectorAll('.fa-shopping-bag').forEach(bag => {
    bag.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Shopping bag clicked'); // Debug log
        showCartModal();
    });
});



function showCartModal() {
    console.log('showCartModal called'); // Debug log
    console.log('Cart items:', cart); // Debug log
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 1002;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    const cartTitle = currentLanguage === 'en' ? 'Shopping Cart' : 'سلة التسوق';
    const emptyCart = currentLanguage === 'en' ? 'Your cart is currently empty' : 'سلة التسوق فارغة حالياً';
    const continueBtn = currentLanguage === 'en' ? 'Continue Shopping' : 'مواصلة التسوق';
    const checkoutBtn = currentLanguage === 'en' ? 'Checkout' : 'إتمام الشراء';
    const removeText = currentLanguage === 'en' ? 'Remove' : 'إزالة';
    const totalText = currentLanguage === 'en' ? 'Total' : 'المجموع';
    
    let modalContent;
    
    if (cart.length === 0) {
        modalContent = `
            <div style="
                background: white;
                padding: 2rem;
                border-radius: 20px;
                width: 90%;
                max-width: 400px;
                text-align: center;
                transform: scale(0.8);
                transition: transform 0.3s ease;
            ">
                <h3 style="margin-bottom: 1rem; color: #2c2c2c;">${cartTitle}</h3>
                <p style="color: #666; margin-bottom: 1.5rem;">${emptyCart}</p>
                <button class="btn btn-primary" onclick="this.closest('.cart-modal').remove()">${continueBtn}</button>
            </div>
        `;
    } else {
        const cartItems = cart.map(item => `
            <div style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem;
                border-bottom: 1px solid #f0f0f0;
                gap: 1rem;
            ">
                <div style="flex: 1; text-align: left;">
                    <div style="font-weight: 600; color: #2c2c2c; margin-bottom: 0.5rem;">${item.name}</div>
                    <div style="color: #ff6b9d; font-weight: 600;">${item.price}</div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <button onclick="updateItemQuantity(${item.id}, ${item.quantity - 1})" style="
                        background: #f8f9fa;
                        border: 1px solid #e9ecef;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        font-weight: bold;
                    ">-</button>
                    <span style="min-width: 30px; text-align: center; font-weight: 600;">${item.quantity}</span>
                    <button onclick="updateItemQuantity(${item.id}, ${item.quantity + 1})" style="
                        background: #f8f9fa;
                        border: 1px solid #e9ecef;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        font-weight: bold;
                    ">+</button>
                </div>
                <button onclick="removeFromCart(${item.id})" style="
                    background: none;
                    border: none;
                    color: #dc3545;
                    cursor: pointer;
                        padding: 0.5rem;
                    font-size: 1.2rem;
                " title="${removeText}">&times;</button>
            </div>
        `).join('');
        
        const total = getCartTotal();
        
        modalContent = `
            <div style="
                background: white;
                border-radius: 20px;
                width: 90%;
                max-width: 500px;
                max-height: 80vh;
                overflow-y: auto;
                transform: scale(0.8);
                transition: transform 0.3s ease;
            ">
                <div style="
                    padding: 1.5rem;
                    border-bottom: 1px solid #f0f0f0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <h3 style="color: #2c2c2c; margin: 0;">${cartTitle}</h3>
                    <button onclick="this.closest('.cart-modal').remove()" style="
                        background: none;
                        border: none;
                        font-size: 1.5rem;
                        color: #666;
                        cursor: pointer;
                        padding: 0;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">&times;</button>
                </div>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    ${cartItems}
                </div>
                
                <div style="
                    padding: 1.5rem;
                    border-top: 1px solid #f0f0f0;
                    background: #f8f9fa;
                    border-radius: 0 0 20px 20px;
                ">
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 1rem;
                        font-size: 1.2rem;
                        font-weight: 600;
                    ">
                        <span>${totalText}:</span>
                        <span style="color: #ff6b9d;">${total.toFixed(2)}</span>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button class="btn btn-secondary" onclick="this.closest('.cart-modal').remove()" style="flex: 1;">
                            ${continueBtn}
                        </button>
                        <button class="btn btn-primary" onclick="checkout()" style="flex: 1;">
                            ${checkoutBtn}
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    modal.innerHTML = modalContent;
    modal.classList.add('cart-modal');
    document.body.appendChild(modal);
    
    // Animate in
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.querySelector('div').style.transform = 'scale(1)';
    }, 100);
    
    // Close on background click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function closeOnEscape(e) {
        if (e.key === 'Escape') {
            modal.remove();
            document.removeEventListener('keydown', closeOnEscape);
        }
    });
}



// Checkout function
function checkout() {
    console.log('Checkout function called'); // Debug log
    const total = getCartTotal();
    const itemCount = getCartItemCount();
    console.log('Total:', total, 'Items:', itemCount); // Debug log
    
    // Store information
    const storeInfo = {
        en: {
            title: 'Store Information',
            location: 'Al-Qamishli, Salam Complex (Central Market) or Municipality Street\nBranch 1 Entrance 3\nBranch 2 Entrance 2',
            phone: '0999496629',
            hours: 'From 9 AM to 8 PM\nEvery day of the week except Friday',
            message: `Thank you for your order of ${itemCount} item(s) for ${total.toFixed(2)}. Please visit our store to complete your purchase.`
        },
        ar: {
            title: 'معلومات الاتصال',
            location: 'القامشلي مجمع السلام (سوق المركزي) او شارع البلدية\nفرع 1 المدخل 3\nفرع 2 المدخل 2',
            phone: '0999496629',
            hours: 'من الساعة 9 الصبح ل 8 المسا\nكل الأسبوع ما عاد يوم الجمعة',
            message: `شكراً لك على طلبك المكون من ${itemCount} عنصر(ات) مقابل ${total.toFixed(2)}. يرجى زيارة متجرنا لإتمام عملية الشراء.`
        }
    };
    
    // Force Arabic language for store info
    const currentStoreInfo = storeInfo['ar'];
    console.log('Store info:', currentStoreInfo); // Debug log
    
    // Show store information popup
    const popup = document.createElement('div');
    popup.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 1003;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 1;
    `;
    
    popup.innerHTML = `
        <div style="
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(0.8);
            transition: transform 0.3s ease;
            padding: 0;
        ">
            <div style="
                background: linear-gradient(135deg, #ff6b9d, #ff8fab);
                color: white;
                padding: 2rem;
                border-radius: 20px 20px 0 0;
                text-align: center;
            ">
                <i class="fas fa-store" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.9;"></i>
                <h2 style="margin: 0; font-size: 1.8rem; font-weight: 600;">${currentStoreInfo.title}</h2>
            </div>
            
            <div style="padding: 2rem;">
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #2c2c2c; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-map-marker-alt" style="color: #ff6b9d;"></i>
                        الموقع
                    </h3>
                    <p style="color: #666; line-height: 1.6; margin: 0; font-size: 1.1rem;">
                        ${currentStoreInfo.location}
                    </p>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #2c2c2c; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-phone" style="color: #ff6b9d;"></i>
                        الهاتف
                    </h3>
                    <p style="color: #666; line-height: 1.6; margin: 0; font-size: 1.1rem;">
                        <a href="tel:0999496629" style="color: #ff6b9d; text-decoration: none; font-weight: 600;">
                            ${currentStoreInfo.phone}
                        </a>
                    </p>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #2c2c2c; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-clock" style="color: #ff6b9d;"></i>
                        ساعات العمل
                    </h3>
                    <p style="color: #666; line-height: 1.8; margin: 0; font-size: 1rem; white-space: pre-line;">
                        ${currentStoreInfo.hours}
                    </p>
                </div>
                
                <div style="
                    background: #f8f9fa;
                    padding: 1.5rem;
                    border-radius: 15px;
                    border-left: 4px solid #ff6b9d;
                    margin-bottom: 2rem;
                ">
                    <p style="color: #2c2c2c; line-height: 1.6; margin: 0; font-size: 1rem;">
                        ${currentStoreInfo.message}
                    </p>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-secondary" onclick="closeStorePopup()" style="flex: 1;">
                        إغلاق
                    </button>
                    <button class="btn btn-primary" onclick="completeCheckout()" style="flex: 1;">
                        إتمام الطلب
                    </button>
                </div>
            </div>
        </div>
    `;
    
    popup.classList.add('store-popup');
    document.body.appendChild(popup);
    
    // Make popup immediately visible
    popup.querySelector('div').style.transform = 'scale(1)';
    
    // Close on background click
    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            closeStorePopup();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function closeOnEscape(e) {
        if (e.key === 'Escape') {
            closeStorePopup();
            document.removeEventListener('keydown', closeOnEscape);
        }
    });
    
    // Close cart modal
    const cartModal = document.querySelector('.cart-modal');
    if (cartModal) {
        cartModal.remove();
    }
}

// Close store popup function
function closeStorePopup() {
    const popup = document.querySelector('.store-popup');
    if (popup) {
        popup.remove();
    }
}

// Complete checkout function
function completeCheckout() {
    const total = getCartTotal();
    const itemCount = getCartItemCount();
    
    const successMessage = currentLanguage === 'en' 
                    ? `Order completed successfully! Your order of ${itemCount} item(s) for ${total.toFixed(2)} has been processed.`
            : `تم إتمام الطلب بنجاح! طلبك المكون من ${itemCount} عنصر(ات) مقابل ${total.toFixed(2)} تمت معالجته.`;
    
    // Show success notification
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        color: #333;
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        z-index: 1004;
        max-width: 400px;
        text-align: center;
    `;
    
    notification.innerHTML = `
        <div style="margin-bottom: 1rem;">
            <i class="fas fa-check-circle" style="color: #28a745; font-size: 3rem; margin-bottom: 1rem;"></i>
            <h3 style="color: #2c2c2c; margin-bottom: 1rem;">${currentLanguage === 'en' ? 'Order Completed!' : 'تم إتمام الطلب!'}</h3>
            <p style="color: #666; line-height: 1.6;">${successMessage}</p>
        </div>
        <button class="btn btn-primary" onclick="this.parentElement.remove()" style="width: 100%;">
            ${currentLanguage === 'en' ? 'Continue Shopping' : 'مواصلة التسوق'}
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Close store popup
    closeStorePopup();
    
    // Clear cart after successful checkout
    setTimeout(() => {
        cart = [];
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartIcon();
        notification.remove();
    }, 5000);
}

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    if (hero) {
        const rate = scrolled * -0.5;
        hero.style.transform = `translateY(${rate}px)`;
    }
});

// Collection links are now functional and navigate to collection pages
// No tooltip needed as the links work properly
