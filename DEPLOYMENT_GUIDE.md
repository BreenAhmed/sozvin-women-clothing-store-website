# Deployment Guide for Sozvin Website

## Issue You're Facing
```
Error: #1044 - Access denied for user 'u100517965_sozvin'@'127.0.0.1' to database 'sozvin'
```

This happens because:
- Your production database has different credentials than localhost
- The database name is different (u100517965_sozvin, not sozvin)
- You need to configure the production database settings

## Step-by-Step Fix

### Step 1: Get Your Database Credentials

Login to your **hosting control panel** (cPanel, Plesk, or similar) and find:

1. **Database Name**: Usually looks like `u100517965_sozvin` or similar
2. **Database Username**: Usually the same as database name: `u100517965_sozvin`
3. **Database Password**: The password you set when creating the database
4. **Database Host**: Usually `localhost` or `127.0.0.1`

📝 **Where to find these:**
- cPanel: Go to **MySQL Databases** section
- Look for "Current Databases" and "Current Users"
- The database name will have your account prefix (u100517965_)

### Step 2: Update Database Configuration

Edit the file: `config/database.php`

Find the **PRODUCTION CONFIGURATION** section (around line 12) and update:

```php
// PRODUCTION CONFIGURATION
if ($isProduction) {
    define('DB_HOST', 'localhost');                    // Usually 'localhost'
    define('DB_NAME', 'u100517965_sozvin');           // Your actual database name
    define('DB_USER', 'u100517965_sozvin');           // Your actual username
    define('DB_PASS', 'YOUR_ACTUAL_PASSWORD');        // ⚠️ PUT YOUR PASSWORD HERE
    define('DB_CHARSET', 'utf8mb4');
    define('DB_PORT', '3306');
}
```

**Replace:**
- `YOUR_ACTUAL_PASSWORD` with your actual database password from Step 1

### Step 3: Ensure Database is Created

In your hosting control panel:

1. Go to **MySQL Databases** or **phpMyAdmin**
2. Check if database `u100517965_sozvin` exists
3. If not, create it:
   - Database name: `u100517965_sozvin` (or whatever your host assigned)
   - Charset: `utf8mb4_unicode_ci`

### Step 4: Grant User Permissions

In **cPanel MySQL Databases**:

1. Scroll to **"Add User To Database"**
2. Select:
   - User: `u100517965_sozvin`
   - Database: `u100517965_sozvin`
3. Click "Add"
4. On privileges page, select **"ALL PRIVILEGES"**
5. Click "Make Changes"

### Step 5: Run Setup Script on Production

Upload all files to your server, then visit:

```
https://your-domain.com/full_database_setup.php
```

This will:
- Create all necessary tables
- Set up collections
- Create admin account
- Set up product images table

**Expected Output:**
```
✓ Collections table created!
✓ Products table created!
✓ Product images table created!
✓ Users table created!
✅ Database Setup Complete!
```

### Step 6: Verify Connection

Visit: `https://your-domain.com/config/test_connection.php`

Create this test file if needed to verify:

```php
<?php
require_once 'database.php';
testConnection();
?>
```

### Step 7: Delete Setup Files (Security)

After setup is complete, **DELETE** these files from production:

```
- full_database_setup.php
- setup_database_complete.php
- update_database_images.php
- check_database_structure.php
- config/test_connection.php (if you created it)
```

⚠️ **Important:** These files expose database structure and should not be public!

### Step 8: Test Your Website

1. Visit: `https://your-domain.com`
2. Login to admin: `https://your-domain.com/admin/login.php`
   - Username: `admin`
   - Password: `admin123`
3. Add products with images
4. Test the gallery feature

## Common Issues & Solutions

### Issue: "Access denied for user"
**Solution:** 
- Double-check username and password in `config/database.php`
- Ensure user has ALL PRIVILEGES on the database
- Verify the database name includes your account prefix

### Issue: "Unknown database"
**Solution:**
- Create the database in cPanel MySQL Databases
- Use the exact name shown in cPanel (with prefix)

### Issue: "Can't connect to MySQL server"
**Solution:**
- Try `DB_HOST = '127.0.0.1'` instead of `localhost`
- Or try `DB_HOST = 'localhost'`
- Some hosts use specific hostnames like `mysql.yourdomain.com`

### Issue: Tables don't exist
**Solution:**
- Make sure you ran `full_database_setup.php` after fixing credentials
- Check phpMyAdmin to see if tables were created
- Run the setup script again if needed

### Issue: "Foreign key constraint error" on production
**Solution:**
The setup script handles this automatically. If you still get errors:
1. Drop all tables in phpMyAdmin
2. Run `full_database_setup.php` again (it creates tables in correct order)

## Environment Detection

The `config/database.php` automatically detects:
- **Local (Development)**: Uses `sozvin` database with `root` user
- **Production**: Uses `u100517965_sozvin` database with proper credentials

Detection is based on hostname:
- localhost = Development
- Your domain = Production

## Security Best Practices

### 1. Change Admin Password
After deployment, login and change the default password:
```
Default: admin123
Change to: A strong unique password
```

### 2. Secure Database Password
- Use a strong password (16+ characters)
- Mix letters, numbers, symbols
- Don't reuse passwords

### 3. File Permissions
Set proper permissions on production:
```bash
# Files: 644
chmod 644 config/database.php

# Directories: 755
chmod 755 uploads/products/
```

### 4. Remove Debug Files
Delete or rename:
- `TESTING_GUIDE.md`
- `DEPLOYMENT_GUIDE.md` (this file)
- `MULTIPLE_IMAGES_FEATURE.md`
- All `*.sql` files from public access

### 5. Enable HTTPS
Ensure your site uses HTTPS:
- Get SSL certificate (usually free with hosting)
- Force HTTPS in .htaccess

## Quick Checklist

Before going live:

- [ ] Database created with correct name
- [ ] Database user has ALL PRIVILEGES
- [ ] config/database.php updated with production credentials
- [ ] Ran full_database_setup.php successfully
- [ ] Deleted setup scripts from production
- [ ] Changed admin password
- [ ] Tested adding products with images
- [ ] Tested image gallery on frontend
- [ ] Verified all pages load correctly
- [ ] Enabled HTTPS/SSL
- [ ] Set proper file permissions

## Support

If you still have issues:

1. Check your hosting provider's documentation for:
   - Database connection settings
   - MySQL hostname
   - Required PHP version (7.4+)

2. Contact your hosting support with:
   - Screenshot of the error
   - Database name and username you're using
   - Let them verify user permissions

3. Check PHP error logs:
   - cPanel: Error Log viewer
   - Or check: `public_html/error_log`

## Success!

Once everything works:
- ✅ Website loads at your domain
- ✅ Admin panel accessible
- ✅ Can add products with multiple images
- ✅ Image gallery works on frontend
- ✅ No database connection errors

Congratulations! Your Sozvin website is live! 🎉

---

**Need Help?** 
- Check error logs in cPanel
- Verify database credentials
- Ensure PHP version is 7.4 or higher
- Make sure MySQL/MariaDB is 5.7 or higher

