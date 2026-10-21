<?php
/**
 * Create Admin User Script
 * Run this file ONCE to create the admin user account
 * 
 * Visit: https://yourdomain.com/create_admin_user.php
 * 
 * Default credentials will be:
 * Username: admin
 * Password: admin123
 */

require_once 'config/database.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - Sozvin</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #ff6b9d;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #c3e6cb;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #f5c6cb;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #bee5eb;
            margin: 20px 0;
        }
        .credentials {
            background: #fff3cd;
            color: #856404;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ffeaa7;
            margin: 20px 0;
            font-size: 1.1em;
        }
        .credentials strong {
            color: #333;
        }
        a.btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 10px;
        }
        a.btn:hover {
            background: #5568d3;
        }
        code {
            background: #f4f4f4;
            padding: 3px 8px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }
        .step {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Create Admin User</h1>

<?php

try {
    $pdo = getDBConnection();
    
    echo "<div class='info'>";
    echo "<strong>Step 1:</strong> Creating users table...";
    echo "</div>";
    
    // Create users table
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_email (email),
        KEY idx_username (username),
        KEY idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "<div class='success'>✓ Users table created successfully!</div>";
    
    echo "<div class='info'>";
    echo "<strong>Step 2:</strong> Creating admin user...";
    echo "</div>";
    
    // Delete existing admin if exists
    $pdo->exec("DELETE FROM users WHERE username = 'admin'");
    
    // Insert admin user
    // Password: admin123 (hashed with bcrypt)
    $sql = "
    INSERT INTO users (username, email, password_hash, role) VALUES 
    ('admin', 'admin@sozvin.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
    ";
    
    $pdo->exec($sql);
    echo "<div class='success'>✓ Admin user created successfully!</div>";
    
    // Verify user exists
    echo "<div class='info'>";
    echo "<strong>Step 3:</strong> Verifying user...";
    echo "</div>";
    
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<div class='success'>";
        echo "<h3>✓ Verification Successful!</h3>";
        echo "<p><strong>User ID:</strong> " . $admin['id'] . "</p>";
        echo "<p><strong>Username:</strong> " . $admin['username'] . "</p>";
        echo "<p><strong>Email:</strong> " . $admin['email'] . "</p>";
        echo "<p><strong>Role:</strong> " . $admin['role'] . "</p>";
        echo "<p><strong>Created:</strong> " . $admin['created_at'] . "</p>";
        echo "</div>";
        
        echo "<div class='credentials'>";
        echo "<h3>🔑 Your Login Credentials:</h3>";
        echo "<p><strong>Username:</strong> <code>admin</code></p>";
        echo "<p><strong>Password:</strong> <code>admin123</code></p>";
        echo "<p><strong>Login URL:</strong> <a href='admin/login.php'>admin/login.php</a></p>";
        echo "</div>";
        
        echo "<div class='info'>";
        echo "<h3>⚠️ Important Security Steps:</h3>";
        echo "<ol>";
        echo "<li>Login to the admin panel now</li>";
        echo "<li><strong>Change the default password immediately!</strong></li>";
        echo "<li>Delete this file (<code>create_admin_user.php</code>) from your server</li>";
        echo "<li>Delete <code>create_users_table.sql</code> as well</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<div style='text-align: center; margin-top: 30px;'>";
        echo "<a href='admin/login.php' class='btn'>Go to Admin Login →</a>";
        echo "</div>";
        
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Verification Failed</h3>";
        echo "<p>User was created but could not be verified. Try logging in anyway.</p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Code:</strong> " . $e->getCode() . "</p>";
    
    echo "<h4>Troubleshooting:</h4>";
    echo "<div class='step'>";
    echo "<p><strong>1. Check Database Connection</strong></p>";
    echo "<p>Make sure <code>config/database.php</code> has the correct password:</p>";
    echo "<code>define('DB_PASS', 'your_actual_password');</code>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<p><strong>2. Verify Database Exists</strong></p>";
    echo "<p>In cPanel → phpMyAdmin, check if database <code>" . DB_NAME . "</code> exists</p>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<p><strong>3. Check Permissions</strong></p>";
    echo "<p>Make sure database user has CREATE and INSERT privileges</p>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<p><strong>4. Use Complete Setup</strong></p>";
    echo "<p>Try running: <a href='full_database_setup.php'>full_database_setup.php</a></p>";
    echo "</div>";
    
    echo "</div>";
}

?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee; text-align: center; color: #666;">
            <p><strong>Need Help?</strong> Check <code>DEPLOYMENT_GUIDE.md</code> for detailed instructions</p>
        </div>
    </div>
</body>
</html>

