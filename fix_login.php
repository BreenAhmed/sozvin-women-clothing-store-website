<?php
/**
 * COMPLETE LOGIN FIX SCRIPT
 * This script will diagnose and fix login issues
 * 
 * Visit: https://yourdomain.com/fix_login.php
 * 
 * What it does:
 * 1. Tests database connection
 * 2. Checks if users table exists
 * 3. Creates users table if needed
 * 4. Creates/updates admin user
 * 5. Tests the credentials
 */

require_once 'config/database.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Login Issues - Sozvin</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
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
        .step {
            margin: 20px 0;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .step-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        code {
            background: #f4f4f4;
            padding: 3px 8px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
            font-size: 0.9em;
        }
        .credentials-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .credentials-box h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        .credential-item {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .credential-item strong {
            color: #333;
            font-size: 1.3em;
        }
        a.btn {
            display: inline-block;
            padding: 15px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }
        a.btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        a.btn-secondary {
            background: #667eea;
        }
        a.btn-secondary:hover {
            background: #5568d3;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 0.9em;
        }
        .delete-warning {
            background: #f8d7da;
            border: 2px solid #dc3545;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        .delete-warning h3 {
            color: #721c24;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Login Issues</h1>

<?php

$allSuccess = true;

// STEP 1: Test Database Connection
echo "<div class='step info'>";
echo "<div class='step-title'>📡 Step 1: Testing Database Connection</div>";

try {
    $pdo = getDBConnection();
    echo "<p>✅ <strong>Connected to database:</strong> <code>" . DB_NAME . "</code></p>";
    echo "<p>✅ <strong>Host:</strong> <code>" . DB_HOST . "</code></p>";
    echo "<p>✅ <strong>User:</strong> <code>" . DB_USER . "</code></p>";
} catch (Exception $e) {
    echo "<p>❌ <strong>Database connection failed!</strong></p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<div class='error' style='margin-top: 15px;'>";
    echo "<h4>Fix This:</h4>";
    echo "<ol>";
    echo "<li>Check <code>config/database.php</code> has correct password</li>";
    echo "<li>Verify database <code>" . DB_NAME . "</code> exists in cPanel</li>";
    echo "<li>Ensure database user has proper permissions</li>";
    echo "</ol>";
    echo "</div>";
    $allSuccess = false;
    echo "</div></div></body></html>";
    exit;
}
echo "</div>";

// STEP 2: Check if users table exists
echo "<div class='step info'>";
echo "<div class='step-title'>🔍 Step 2: Checking Users Table</div>";

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p>✅ <strong>Users table exists</strong></p>";
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "<p>📊 <strong>Users in database:</strong> $count</p>";
        
        // Check if admin exists
        $stmt = $pdo->query("SELECT username, email, role FROM users WHERE username = 'admin'");
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<p>✅ <strong>Admin user found:</strong></p>";
            echo "<ul>";
            echo "<li>Username: <code>" . htmlspecialchars($admin['username']) . "</code></li>";
            echo "<li>Email: <code>" . htmlspecialchars($admin['email']) . "</code></li>";
            echo "<li>Role: <code>" . htmlspecialchars($admin['role']) . "</code></li>";
            echo "</ul>";
        } else {
            echo "<p>⚠️ <strong>Admin user NOT found</strong></p>";
            $allSuccess = false;
        }
        
    } else {
        echo "<p>❌ <strong>Users table does NOT exist</strong></p>";
        $allSuccess = false;
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error checking table:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    $allSuccess = false;
}
echo "</div>";

// STEP 3: Create/Fix users table and admin user
if (!$allSuccess) {
    echo "<div class='step warning'>";
    echo "<div class='step-title'>🔧 Step 3: Creating/Fixing Users Table and Admin User</div>";
    
    try {
        // Create users table
        echo "<p>Creating users table...</p>";
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
        echo "<p>✅ Users table created/verified</p>";
        
        // Delete existing admin to avoid conflicts
        echo "<p>Removing old admin user (if exists)...</p>";
        $pdo->exec("DELETE FROM users WHERE username = 'admin'");
        
        // Insert admin user
        echo "<p>Creating admin user...</p>";
        $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // admin123
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute(['admin', 'admin@sozvin.com', $passwordHash, 'admin']);
        
        echo "<p>✅ <strong>Admin user created successfully!</strong></p>";
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
}

// STEP 4: Verify password works
echo "<div class='step info'>";
echo "<div class='step-title'>🔐 Step 4: Testing Password Verification</div>";

try {
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        $storedHash = $result['password_hash'];
        $testPassword = 'admin123';
        
        echo "<p>Stored hash: <code>" . substr($storedHash, 0, 30) . "...</code></p>";
        
        if (password_verify($testPassword, $storedHash)) {
            echo "<p>✅ <strong>Password verification WORKS!</strong></p>";
            echo "<p>Password '<code>admin123</code>' matches the stored hash.</p>";
        } else {
            echo "<p>❌ <strong>Password verification FAILED!</strong></p>";
            echo "<p>This means the password hash might be corrupted.</p>";
            
            // Regenerate hash
            echo "<p>Regenerating password hash...</p>";
            $newHash = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
            $stmt->execute([$newHash]);
            echo "<p>✅ Password hash regenerated!</p>";
        }
    } else {
        echo "<p>❌ Could not find admin user</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// STEP 5: Final Status
echo "<div class='step success'>";
echo "<div class='step-title'>✅ Step 5: Final Status</div>";

try {
    // Get admin user details
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<h3>🎉 SUCCESS! Everything is set up correctly!</h3>";
        
        echo "<div class='credentials-box'>";
        echo "<h3>🔑 Your Login Credentials</h3>";
        echo "<div class='credential-item'>";
        echo "<strong>Username:</strong> <code style='font-size: 1.2em;'>admin</code>";
        echo "</div>";
        echo "<div class='credential-item'>";
        echo "<strong>Password:</strong> <code style='font-size: 1.2em;'>admin123</code>";
        echo "</div>";
        echo "<div class='credential-item'>";
        echo "<strong>Email:</strong> <code>" . htmlspecialchars($admin['email']) . "</code>";
        echo "</div>";
        echo "</div>";
        
        echo "<h4>📊 Admin User Details:</h4>";
        echo "<pre>";
        echo "ID: " . $admin['id'] . "\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        echo "Created: " . $admin['created_at'];
        echo "</pre>";
        
        echo "<div style='text-align: center; margin-top: 30px;'>";
        echo "<a href='admin/login.php' class='btn'>🚀 Go to Login Page</a>";
        echo "<a href='admin/dashboard.php' class='btn btn-secondary'>📊 Go to Dashboard</a>";
        echo "</div>";
        
    } else {
        echo "<p>❌ Admin user still not found. Please contact support.</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Security Warning
echo "<div class='delete-warning'>";
echo "<h3>⚠️ IMPORTANT SECURITY STEP!</h3>";
echo "<p><strong>After successful login, DELETE these files from your server:</strong></p>";
echo "<ul>";
echo "<li><code>fix_login.php</code> (this file)</li>";
echo "<li><code>create_admin_user.php</code></li>";
echo "<li><code>create_users_table.sql</code></li>";
echo "<li><code>full_database_setup.php</code></li>";
echo "<li><code>update_database_images.php</code></li>";
echo "<li><code>check_database_structure.php</code></li>";
echo "</ul>";
echo "<p>These files expose database structure and should not be publicly accessible!</p>";
echo "</div>";

?>

    </div>
</body>
</html>

