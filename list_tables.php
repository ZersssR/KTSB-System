<?php
require_once 'config/database.php';
$pdo = getDB();
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

// Also get schema for 'crew_requests' or similar to copy structure
if (in_array('crew_requests', $tables)) {
    $stmt = $pdo->query("DESCRIBE crew_requests");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>