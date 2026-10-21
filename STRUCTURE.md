# Sozvin Project Structure

## Overview
The Sozvin project has been separated into distinct components for better organization and user experience.

## File Structure

### Main Website
- **`index.php`** - Main Sozvin fashion store homepage
  - Displays collections and featured products from database
  - Includes navigation, hero section, collections, products, about, and newsletter
  - Links to admin panel via cog icon in navigation





### Admin Directory
- **`admin/`** - Contains all admin panel files
  - `login.php` - Admin authentication
  - `dashboard.php` - Main admin dashboard
  - `products.php` - Product management
  - `collections.php` - Collection management
  - `add-product.php` - Add new products
  - `add-collection.php` - Add new collections

### Collections
- **`collections/`** - Individual collection pages
  - `hijabs.html` - Hijab collection
  - `lingerie.html` - Lingerie collection
  - `sleeping-sets.html` - Sleepwear collection
  - `sportswear.html` - Sportswear collection
  - `underwears.html` - Underwear collection

### Configuration
- **`config/database.php`** - Database connection configuration
- **`database_setup.sql`** - Database schema and sample data
- **`test_connection.php` - Database connection testing

### Assets
- **`styles.css`** - Main website styling
- **`script.js` - JavaScript functionality
- **`index.html`** - Legacy HTML version (can be removed)

## Access Points

### For Customers
1. **Main Website**: `http://localhost/sosvin/` or `http://localhost/sosvin/index.php`
2. **Collections**: Direct links from main website or via `collections/[collection-name].html`

### For Administrators
1. **Admin Login**: `http://localhost/sosvin/admin/login.php`
2. **Admin Dashboard**: `http://localhost/sosvin/admin/dashboard.php` (after login)

### For Developers
1. **Database Test**: `http://localhost/sosvin/test_connection.php`

## Database
- **Database Name**: `sozvin`
- **Tables**: `users`, `collections`, `products`
- **Default Admin**: username: `admin`, password: `admin123`

## Features

### Main Website
- Responsive design with modern UI
- Dynamic content from database
- Shopping cart functionality
- Wishlist features
- Newsletter subscription
- Search functionality

### Admin Panel
- User authentication
- Product management (CRUD operations)
- Collection management
- Database monitoring
- Quick navigation between functions

## Benefits of Separation

1. **Clear User Experience**: Customers see only the store, admins access admin via navigation
2. **Better Security**: Admin functions are isolated and require authentication
3. **Easier Maintenance**: Simplified structure with direct access
4. **Professional Appearance**: Clean integration of admin access in main navigation
5. **Scalability**: Easy to add new features to either section
6. **Streamlined Access**: Direct admin login from main website navigation

## Getting Started

1. Ensure XAMPP is running (Apache + MySQL)
2. Visit `http://localhost/sosvin/` for the main website
3. Click the cog icon (⚙️) in the navigation to access admin login
4. For admin login, use the credentials above
5. Start managing your content!

## Notes
- The main website (`index.php`) is now a static HTML page for faster performance
- All admin functions require authentication
- Admin access is integrated into the main website navigation (cog icon)
- Both sections maintain consistent branding and design language
