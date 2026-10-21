<?php
// Application configuration
class AppConfig {
    private static $config = null;
    
    public static function get($key, $default = null) {
        if (self::$config === null) {
            self::loadConfig();
        }
        
        return isset(self::$config[$key]) ? self::$config[$key] : $default;
    }
    
    private static function loadConfig() {
        // Detect environment
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $isLocal = (
            $host === 'localhost' || 
            strpos($host, '127.0.0.1') !== false ||
            strpos($host, '192.168.') !== false
        );
        
        if ($isLocal) {
            // Local development (XAMPP)
            self::$config = [
                'base_url' => 'http://localhost/sosvin',
                'uploads_url' => 'http://localhost/sosvin/uploads',
                'css_url' => 'styles.css',
                'js_url' => 'script.js',
                'admin_url' => 'admin',
                'collections_url' => 'collections'
            ];
        } else {
            // Production environment
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            
            // Calculate the base path by removing the script filename and any subdirectories
            // This ensures we get the root application path regardless of script location
            $path = '';
            if ($scriptName) {
                // Get the directory path
                $dirPath = dirname($scriptName);
                // If we're in a subdirectory (like /collections), go up to the parent
                if (basename($dirPath) === 'collections' || basename($dirPath) === 'admin') {
                    $path = dirname($dirPath);
                } else {
                    $path = $dirPath;
                }
            }
            
            // For production, use absolute URLs for images
            self::$config = [
                'base_url' => $protocol . '://' . $host . $path,
                'uploads_url' => $protocol . '://' . $host . $path . '/uploads',
                'css_url' => 'styles.css',
                'js_url' => 'script.js',
                'admin_url' => 'admin',
                'collections_url' => 'collections'
            ];
        }
    }
    
    public static function getBaseUrl() {
        return self::get('base_url');
    }
    
    public static function getUploadsUrl() {
        return self::get('uploads_url');
    }
    
    public static function getCssUrl() {
        return self::get('css_url');
    }
    
    public static function getJsUrl() {
        return self::get('js_url');
    }
    
    public static function getAdminUrl() {
        return self::get('admin_url');
    }
    
    public static function getCollectionsUrl() {
        return self::get('collections_url');
    }
    
    public static function getImageUrl($imagePath) {
        if (empty($imagePath)) {
            return '';
        }
        
        // If it's already a full URL, return as is
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        
        // For local development, use relative paths
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $isLocal = (
            $host === 'localhost' || 
            strpos($host, '127.0.0.1') !== false ||
            strpos($host, '192.168.') !== false
        );
        
        if ($isLocal) {
            // For local development, always return path relative to the sosvin directory
            // This ensures images work from any subdirectory
            return '/sosvin/' . ltrim($imagePath, '/');
        } else {
            // For production, use absolute URL based on base URL from config
            // This ensures consistent URLs regardless of script location
            $baseUrl = self::getBaseUrl();
            return rtrim($baseUrl, '/') . '/' . ltrim($imagePath, '/');
        }
    }
}
?>
