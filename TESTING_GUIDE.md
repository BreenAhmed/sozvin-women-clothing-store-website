# Quick Testing Guide - Multiple Images Feature

## Step 1: Access Your Site
Open your browser and navigate to:
```
http://localhost/sosvin
```

## Step 2: Test Admin Features

### Login to Admin Panel
1. Go to: `http://localhost/sosvin/admin/login.php`
2. Default credentials:
   - Username: `admin`
   - Email: `admin@sozvin.com`
   - Password: `admin123`

### Add a Product with Multiple Images
1. Go to: Admin > Products > Add Product
2. Fill in product details:
   - Name: "Test Product"
   - Price: 100
   - Choose a collection
3. Click the "Choose Files" button under images
4. **Select 3-5 images** (hold Ctrl/Cmd to select multiple)
5. You should see preview of all images
6. Click "Add Product"
7. Success message should show: "تم إضافة المنتج بنجاح مع X صورة!"

### Edit Product Images
1. Go to: Admin > Products
2. Click "Edit" on any product
3. You should see a grid of existing images
4. Try these actions:
   - **Change Primary Image**: Click a different radio button
   - **Delete an Image**: Check the delete checkbox on an image
   - **Add New Images**: Select new files in "Add new images" section
5. Click "Update Product"

## Step 3: Test Frontend Features

### View Product Detail Page
1. Go to homepage: `http://localhost/sosvin`
2. Click on any product card OR click "View Details" button
3. You should see:
   - Large main product image
   - Thumbnail gallery below (if multiple images)
   - Product information on the right

### Test Image Gallery
1. On product detail page:
   - **Click a thumbnail** → Main image should change
   - **Click the main image** → Full-screen modal opens
   - **In modal**: Click left/right arrows to navigate
   - **Press Escape** → Modal closes
   - **Press Arrow Keys** → Navigate between images

### Test From Collections
1. Go to: `http://localhost/sosvin/index.php#collections`
2. Click on any collection (e.g., "الملابس الداخلية")
3. Click "View Details" on any product
4. Test image gallery as above

## Step 4: Test Mobile View

### Using Chrome DevTools
1. Press F12 to open DevTools
2. Click the device toggle icon (or press Ctrl+Shift+M)
3. Select a mobile device (e.g., iPhone 12)
4. Navigate through:
   - Homepage (products should scroll horizontally on mobile)
   - Product detail page (should stack vertically)
   - Image gallery (should work with touch gestures)

## Step 5: Verify Database

### Check Product Images Table
Open phpMyAdmin or MySQL command line:
```sql
-- View all product images
SELECT * FROM product_images;

-- Check which products have multiple images
SELECT product_id, COUNT(*) as image_count 
FROM product_images 
GROUP BY product_id;

-- See primary images
SELECT * FROM product_images WHERE is_primary = 1;
```

## Expected Results

### ✅ Admin Panel Should:
- Upload multiple images at once
- Show preview of selected images
- Display existing images in grid layout
- Allow setting any image as primary
- Allow deleting individual images
- Show green border/badge on primary image

### ✅ Product Detail Page Should:
- Display large main image
- Show thumbnail gallery (if multiple images)
- Change main image when clicking thumbnails
- Open full-screen modal on main image click
- Navigate with arrows and keyboard
- Show related products at bottom
- Have breadcrumb navigation

### ✅ Homepage & Collections Should:
- Display primary product image
- Link to product detail page
- Show "View Details" button
- Display product information

## Common Issues & Solutions

### Issue: Images not uploading
**Solution:** Check file permissions
```bash
chmod 755 /Applications/XAMPP/xamppfiles/htdocs/sosvin/uploads/products/
```

### Issue: Images not displaying
**Solution:** Check image paths in database
```sql
SELECT id, name, image_url FROM products LIMIT 5;
SELECT * FROM product_images LIMIT 5;
```

### Issue: Modal not opening
**Solution:** Check browser console for JavaScript errors (F12 > Console)

### Issue: Primary image not updating
**Solution:** Make sure to select a radio button before submitting

## Test Checklist

- [ ] Can upload multiple images when adding product
- [ ] Image preview shows before upload
- [ ] Can edit product and see existing images
- [ ] Can change primary image
- [ ] Can delete individual images
- [ ] Can add new images to existing product
- [ ] Product detail page opens from homepage
- [ ] Thumbnail gallery displays all images
- [ ] Clicking thumbnail changes main image
- [ ] Clicking main image opens modal
- [ ] Modal navigation works (arrows and keyboard)
- [ ] Related products section shows
- [ ] Mobile view is responsive
- [ ] Breadcrumb navigation works
- [ ] Links between pages work correctly

## Demo Scenarios

### Scenario 1: Create a Complete Product
1. Login to admin
2. Add new product with 4 images
3. View the product on frontend
4. Test image gallery navigation
5. Edit product and change primary image
6. View changes on frontend

### Scenario 2: Update Existing Product
1. Find existing product with 1 image
2. Edit product
3. Add 3 more images
4. Set a different primary image
5. Remove one image
6. Save and verify on frontend

### Scenario 3: Customer Journey
1. Visit homepage
2. Browse products
3. Click "View Details" on a product
4. Navigate through product images
5. View related products
6. Click on related product
7. Add to cart from detail page

## Performance Testing

### Check Page Load Times
1. Open browser DevTools (F12)
2. Go to Network tab
3. Navigate to product detail page
4. Check:
   - Total page load time (should be < 2 seconds)
   - Image load times (should be < 500ms each)
   - Number of HTTP requests

### Test With Multiple Products
1. Add 10+ products with 3-5 images each
2. Navigate through collections
3. Check if performance remains smooth
4. Monitor memory usage in browser

## Next Steps After Testing

If everything works:
1. ✅ Feature is ready for production
2. Add more products with multiple images
3. Consider adding image optimization
4. Optional: Implement lazy loading for better performance

If issues found:
1. Check error logs: `/Applications/XAMPP/xamppfiles/logs/error_log`
2. Check PHP errors: Enable display_errors in php.ini
3. Check database: Verify table structure matches schema
4. Review implementation in MULTIPLE_IMAGES_FEATURE.md

## Quick Demo Data

Want to quickly test? Here's SQL to insert a test product with sample data:

```sql
-- Insert test product
INSERT INTO products (name, name_ar, description, price, is_active, collection_id)
VALUES ('Test Gallery Product', 'منتج معرض اختباري', 'Product with multiple images for testing', 150.00, 1, 1);

-- Get the product ID (assume it's 100)
SET @product_id = LAST_INSERT_ID();

-- Note: You still need to manually upload images through admin panel
-- Or copy test images to uploads/products/ and insert records
```

## Support & Feedback

After testing, note:
- Features that work well ✅
- Features that need improvement 🔧
- New features you'd like to see 💡
- Any bugs or issues 🐛

Happy Testing! 🚀

