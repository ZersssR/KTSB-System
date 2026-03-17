<?php
// Test Database Connection Port 3306
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test (Port 3306)</h2>";

$host = '127.0.0.1';
$port = '3306';
$dbname = 'ktsb_application';
$user = 'root';
$pass = ''; // Default empty password as per screenshot

echo "<h3>Testing Credentials for '$user' at '$host:$port'</h3>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    echo "<p>Connecting to: <strong>$dsn</strong> ... ";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "<span style='color: green; font-weight: bold;'>SUCCESS! ✅</span></p>";
    echo "<div style='background: #e6fffa; padding: 10px; border: 1px solid green;'>";
    echo "<strong>We found the correct configuration!</strong><br>";
    echo "Port: $port<br>";
    echo "Password: (empty)";
    echo "</div>";

} catch (PDOException $e) {
    $msg = $e->getMessage();
    echo "<span style='color: red;'>Failed ❌</span></p>";
    echo "<pre>$msg</pre>";
}
?>
