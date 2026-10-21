<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Checkout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div style="padding: 50px; text-align: center;">
        <h1>Test Checkout Popup</h1>
        <p>Click the button below to test the checkout popup:</p>
        <button onclick="testCheckout()" style="
            background: #ff6b9d;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 18px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
        ">Test Checkout</button>
    </div>

    <script>
        // Add some test items to cart
        let cart = [
            {
                name: 'Test Product',
                price: '$99.99',
                quantity: 1,
                id: 1
            }
        ];

        function getCartTotal() {
            return 99.99;
        }

        function getCartItemCount() {
            return 1;
        }

        function closeStorePopup() {
            const popup = document.querySelector('.store-popup');
            if (popup) {
                popup.remove();
            }
        }

        function completeCheckout() {
            alert('Checkout completed!');
            closeStorePopup();
        }

        function testCheckout() {
            const total = getCartTotal();
            const itemCount = getCartItemCount();
            
            // Store information
            const storeInfo = {
                ar: {
                    title: 'معلومات الاتصال',
                    location: 'القامشلي مجمع السلام (سوق المركزي) او شارع البلدية\nفرع 1 المدخل 3\nفرع 2 المدخل 2',
                    phone: '0999496629',
                    hours: 'من الساعة 9 الصبح ل 8 المسا\nكل الأسبوع ما عاد يوم الجمعة',
                    message: `شكراً لك على طلبك المكون من ${itemCount} عنصر(ات) مقابل ${total.toFixed(2)}. يرجى زيارة متجرنا لإتمام عملية الشراء.`
                }
            };
            
            const currentStoreInfo = storeInfo['ar'];
            
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
                    transform: scale(1);
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
                            <button onclick="closeStorePopup()" style="
                                flex: 1;
                                background: #6c757d;
                                color: white;
                                border: none;
                                padding: 12px 24px;
                                border-radius: 25px;
                                cursor: pointer;
                                font-family: 'Cairo', sans-serif;
                            ">إغلاق</button>
                            <button onclick="completeCheckout()" style="
                                flex: 1;
                                background: #ff6b9d;
                                color: white;
                                border: none;
                                padding: 12px 24px;
                                border-radius: 25px;
                                cursor: pointer;
                                font-family: 'Cairo', sans-serif;
                            ">إتمام الطلب</button>
                        </div>
                    </div>
                </div>
            `;
            
            popup.classList.add('store-popup');
            document.body.appendChild(popup);
            
            // Close on background click
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    closeStorePopup();
                }
            });
        }
    </script>
</body>
</html>
