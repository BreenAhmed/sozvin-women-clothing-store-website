<?php
// Test database connection for Sozvin
require_once 'config/database.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Sozvin Database Connection Test</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #667eea; text-align: center; margin-bottom: 30px; }";
echo ".status { padding: 15px; border-radius: 5px; margin: 20px 0; }";
echo ".success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }";
echo ".error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }";
echo ".info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }";
echo ".next-step { text-align: center; margin-top: 30px; }";
echo ".next-step a { background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }";
echo ".next-step a:hover { background: #5a6fd8; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔗 Sozvin Database Connection Test</h1>";

// Check database status first
echo "<div class='status info'>";
echo "<strong>Database Status:</strong> " . getDatabaseStatus();
echo "</div>";

// Run the full connection test
testConnection();

echo "</div>";
echo "</body>";
echo "</html>";
?>
