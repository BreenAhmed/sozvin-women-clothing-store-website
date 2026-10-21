<?php
session_start();
require_once '../config/database.php';

// Check if user is already logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Require admin authentication
function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Login function
function login($username, $password) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Logout function
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}
?>
