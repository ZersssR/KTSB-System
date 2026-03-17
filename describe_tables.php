<?php
require_once 'config/database.php';
$pdo = getDB();

$tables_to_check = ['companies', 'light_port_requests', 'marine_requests', 'fuel_water_requests'];

foreach ($tables_to_check as $table) {
    echo "<h1>Table: $table</h1>";
    $stmt = $pdo->query("DESCRIBE $table");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    echo "<hr>";
}
?>