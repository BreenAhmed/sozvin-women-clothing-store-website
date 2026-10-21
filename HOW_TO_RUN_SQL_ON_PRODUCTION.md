# How to Run SQL Script on Production Server

## The Problem
Your SQL file had `USE sozvin;` but your production database is `u100517965_sozvin`

## Solution - Run SQL in phpMyAdmin

### Method 1: Using phpMyAdmin (Recommended) ✅

#### Step 1: Login to cPanel
1. Go to your hosting cPanel
2. Find **phpMyAdmin** and click it

#### Step 2: Select Your Database
1. In phpMyAdmin, look at the left sidebar
2. Click on: **`u100517965_sozvin`** (your database name)
3. The database name should now be highlighted

#### Step 3: Import SQL File
1. Click the **"SQL"** tab at the top
2. You'll see a text area to enter SQL queries
3. Click **"Browse your computer"** or paste the SQL code
4. Use file: `database_update_images_production.sql` 
   (This has the correct database name)

#### Step 4: Execute
1. Click **"Go"** button at the bottom
2. Wait for completion

#### Expected Success Messages:
```
✓ ALTER TABLE products ENGINE=InnoDB (1 row affected)
✓ DROP TABLE IF EXISTS product_images (0 rows)
✓ CREATE TABLE product_images (0 rows)
✓ INSERT INTO product_images... (X rows inserted)
✓ CREATE VIEW products_with_images (0 rows)
```

### Method 2: Using Import Tab (Alternative)

#### Step 1: Login to phpMyAdmin
Same as Method 1

#### Step 2: Select Database
Click on `u100517965_sozvin` in left sidebar

#### Step 3: Import
1. Click **"Import"** tab at the top
2. Click **"Choose File"**
3. Select: `database_update_images_production.sql`
4. Scroll down and click **"Go"**

### Method 3: Using PHP Script (Easiest) 🚀

**I already created this for you!**

Just visit:
```
https://yourdomain.com/full_database_setup.php
```

This script:
- ✅ Uses correct database credentials automatically
- ✅ Creates all tables in correct order
- ✅ Handles errors gracefully
- ✅ Shows success/error messages
- ✅ No manual SQL needed!

**This is the EASIEST method!**

## Which SQL File to Use?

### For LOCAL (XAMPP):
Use: `database_update_images.sql`
- No `USE` statement
- Works with `sozvin` database

### For PRODUCTION:
**Option A:** Use: `database_update_images_production.sql`
- Has `USE u100517965_sozvin;`
- Ready to import directly

**Option B:** Use the PHP script (RECOMMENDED)
- Visit: `https://yourdomain.com/full_database_setup.php`
- No SQL file needed!

## Troubleshooting

### Error: "Access denied for user..."
**Cause:** Wrong database name in SQL file
**Fix:** 
- Make sure you're using `database_update_images_production.sql` (not the local version)
- OR use the PHP script instead

### Error: "Unknown database 'sozvin'"
**Cause:** Using local SQL file on production
**Fix:** 
- Use `database_update_images_production.sql` 
- OR remove `USE sozvin;` line before running

### Error: "Can't create table (errno: 150)"
**Cause:** Foreign key constraint issue
**Fix:**
1. Drop existing tables first:
   ```sql
   DROP TABLE IF EXISTS product_images;
   DROP TABLE IF EXISTS products;
   ```
2. Run `full_database_setup.php` instead (it handles this)

### Error: "Table 'products' doesn't exist"
**Cause:** Products table not created yet
**Fix:**
1. Run `full_database_setup.php` first
2. This creates ALL tables in correct order

## Recommended Approach 🎯

**Don't manually run SQL files!**

Instead:

1. **Update `config/database.php` with your password** (most important!)
   ```php
   define('DB_PASS', 'your_actual_password');
   ```

2. **Visit the setup script:**
   ```
   https://yourdomain.com/full_database_setup.php
   ```

3. **Delete setup files after success:**
   - `full_database_setup.php`
   - `database_update_images_production.sql`
   - `setup_database_complete.php`

This is:
- ✅ Easier
- ✅ Safer
- ✅ Handles errors automatically
- ✅ Creates everything in correct order

## Quick Command Reference

### To Run SQL Manually in phpMyAdmin:

```sql
-- 1. Select your database first
USE u100517965_sozvin;

-- 2. Then paste the rest of the SQL
ALTER TABLE products ENGINE=InnoDB;
DROP TABLE IF EXISTS product_images;
-- ... etc
```

### Or Copy-Paste This Complete Script:

Open `database_update_images_production.sql` and copy ALL the contents, then paste into phpMyAdmin SQL tab.

## Verification

After running successfully, check:

1. **In phpMyAdmin:**
   - Click on `u100517965_sozvin` database
   - You should see tables:
     - ✓ collections
     - ✓ products
     - ✓ **product_images** (NEW!)
     - ✓ users
     - ✓ products_with_images (VIEW)

2. **Click on `product_images` table:**
   - Should show structure with columns:
     - id
     - product_id
     - image_url
     - image_order
     - is_primary
     - alt_text
     - created_at

3. **Test on website:**
   - Login to admin
   - Add a product with multiple images
   - View product on frontend
   - Image gallery should work!

## Need Help?

If you're still stuck:

1. **Check database credentials** in `config/database.php`
2. **Use the PHP setup script** (easiest way!)
3. **Check cPanel error logs** for details
4. **Contact your hosting support** if database permissions are wrong

---

**Bottom Line:** 
Use `full_database_setup.php` instead of manually running SQL! 
It's easier and handles everything automatically. 🚀

