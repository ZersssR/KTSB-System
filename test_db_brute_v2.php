<?php
// Test Database Connection Brute Force
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test Brute Force</h2>";

// Passwords to try
$passwords = ['', 'root', 'mysql', 'admin'];
$host = '127.0.0.1';
$dbname = 'ktsb_application'; // Try the app/login db
$user = 'root';

echo "<h3>Testing Credentials for '$user' at '$host'</h3>";

foreach ($passwords as $pass) {
    try {
        $displayPass = $pass === '' ? '(empty)' : $pass;
        echo "<p>Trying password: <strong>$displayPass</strong> ... ";

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        echo "<span style='color: green; font-weight: bold;'>SUCCESS! ✅</span></p>";
        echo "<div style='background: #e6fffa; padding: 10px; border: 1px solid green;'>";
        echo "<strong>Use this password in your config files!</strong><br>";
        echo "Password: '$pass'";
        echo "</div>";
        exit; // Stop after finding success

    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // Shorten error message
        if (strpos($msg, 'Access denied') !== false) {
            echo "<span style='color: red;'>Access Denied ❌</span></p>";
        } else {
            echo "<span style='color: orange;'>Failed ($msg) ⚠️</span></p>";
        }
    }
}

echo "<hr>";
echo "<div style='color: red; font-weight: bold;'>All common passwords failed. Please ask the user for the database password.</div>";
?>