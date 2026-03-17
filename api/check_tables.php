<?php
require_once __DIR__ . '/../config/app.php';

try {
    $conn = getDBConnection();

    $tables = ['marine_requests', 'crew_sign_on_requests', 'crew_sign_off_requests', 'fuel_water_requests', 'light_port_requests', 'bods', 'users'];

    foreach ($tables as $table) {
        echo "Table: $table<br>";
        try {
            $stmt = $conn->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "Columns: " . implode(", ", $columns) . "<br><br>";
        } catch (PDOException $e) {
            echo "Table does not exist or error: " . $e->getMessage() . "<br><br>";
        }
    }

} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage();
}
?>