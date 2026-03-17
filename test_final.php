<?php
// Final Database Connection Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Final Configuration Test</h2>";

try {
    require_once __DIR__ . '/config/database.php';
    echo "<p>Included <code>config/database.php</code></p>";

    $pdo = getDB();
    if ($pdo) {
        echo "<div style='color: green; font-weight: bold;'>✅ Admin Database Connection Successful!</div>";
        $stmt = $pdo->query("SELECT VERSION()");
        echo "<p>MySQL Version: " . $stmt->fetchColumn() . "</p>";
    } else {
        echo "<div style='color: red;'>❌ getDB() returned null</div>";
    }

    $auth_pdo = getAuthDB();
    if ($auth_pdo) {
        echo "<div style='color: green; font-weight: bold;'>✅ Auth Database Connection Successful!</div>";
    } else {
        echo "<div style='color: orange;'>⚠️ Auth Database connection is null (check logs)</div>";
    }

} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ Config Test Failed!</div>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>