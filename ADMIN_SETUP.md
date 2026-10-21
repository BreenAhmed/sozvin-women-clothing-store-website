# Sozvin Admin Panel Setup Guide

This guide will help you set up the MySQL database and admin panel for your Sozvin website.

## Prerequisites

- MySQL server running on your machine
- PHP 7.4 or higher
- Web server (Apache/Nginx) or PHP built-in server

## Step 1: Set Up MySQL Database

### Option A: Using MySQL Command Line
```bash
# Connect to MySQL as root
mysql -u root -p

# Run the database setup script
source database_setup.sql
```

### Option B: Using phpMyAdmin
1. Open phpMyAdmin in your browser
2. Create a new database called `sozvin`
3. Import the `database_setup.sql` file

## Step 2: Configure Database Connection

Edit `config/database.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');     // Your MySQL host
define('DB_NAME', 'sozvin');        // Database name
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASS', '');              // Your MySQL password
```

## Step 3: Test Database Connection

1. Start your web server
2. Navigate to `test_connection.php` in your browser
3. You should see a success message with database statistics

## Step 4: Access Admin Panel

1. Navigate to `admin/login.php` in your browser
2. Use the default admin credentials:
   - **Username:** `admin`
   - **Password:** `admin123`

## Step 5: Change Default Password

**IMPORTANT:** Change the default admin password immediately after first login!

1. Log in to the admin panel
2. Go to User Management (if available)
3. Update the admin password

## Admin Panel Features

### Dashboard
- Overview of products, collections, and statistics
- Quick access to common actions
- Recent products list

### Products Management
- **View Products:** See all products in a table format
- **Add Product:** Comprehensive form for new products
- **Edit Product:** Modify existing product details
- **Delete Product:** Remove products from catalog
- **Toggle Status:** Activate/deactivate products

### Collections Management
- Organize products into categories
- Manage collection names and descriptions
- Set collection order and visibility

### Product Features
- **Multilingual Support:** English and Arabic names/descriptions
- **Image Management:** Main image and additional images
- **Inventory Control:** Stock quantities and SKU management
- **Pricing:** Regular and sale prices
- **Attributes:** Sizes, colors, and tags
- **SEO:** SKU and product organization

## File Structure

```
sosvin/
├── admin/                 # Admin panel files
│   ├── auth.php          # Authentication system
│   ├── login.php         # Admin login page
│   ├── dashboard.php     # Main admin dashboard
│   ├── products.php      # Products management
│   └── add-product.php   # Add new product form
├── config/
│   └── database.php      # Database configuration
├── database_setup.sql    # Database creation script
├── test_connection.php   # Database connection test
└── ADMIN_SETUP.md        # This setup guide
```

## Security Features

- **Session Management:** Secure admin sessions
- **Password Hashing:** Bcrypt password encryption
- **SQL Injection Protection:** Prepared statements
- **XSS Protection:** HTML escaping
- **Access Control:** Admin-only routes

## Troubleshooting

### Database Connection Issues
1. Verify MySQL is running
2. Check database credentials in `config/database.php`
3. Ensure database `sozvin` exists
4. Run `test_connection.php` to diagnose issues

### Admin Login Issues
1. Verify the database has the admin user
2. Check if the `users` table exists
3. Ensure PHP sessions are working

### Permission Issues
1. Check file permissions (should be readable by web server)
2. Verify PHP has MySQL extension enabled
3. Ensure web server can access the admin directory

## Customization

### Adding New Fields
1. Modify the database schema in `database_setup.sql`
2. Update the admin forms in `add-product.php`
3. Modify the products table in `products.php`

### Styling Changes
- All styling is inline CSS for easy modification
- Color scheme uses CSS variables for consistency
- Responsive design for mobile and desktop

### Adding New Collections
1. Use the admin panel to add collections
2. Or manually insert into the `collections` table
3. Update the main website navigation if needed

## Support

If you encounter issues:
1. Check the error messages in the admin panel
2. Verify database connectivity
3. Check PHP error logs
4. Ensure all required files are present

## Next Steps

After setup:
1. Add your first products
2. Create collections
3. Upload product images
4. Customize the admin panel styling
5. Set up regular backups of your database

---

**Note:** This admin panel is designed for the Sozvin lingerie website and includes features specific to fashion retail. Modify as needed for your specific requirements.
