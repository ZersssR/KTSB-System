<?php
require_once 'config/database.php';

try {
    $pdo = getDB();

    // Check recent marine requests and their service data counts
    $stmt = $pdo->query("
        SELECT mr.marine_id, mr.vessel_name,
               COUNT(DISTINCT fw.id) as fuel_water_count,
               COUNT(DISTINCT gw.id) as general_works_count,
               COUNT(DISTINCT os.id) as other_services_count
        FROM marine_requests mr
        LEFT JOIN marine_fuel_water_services fw ON mr.marine_id = fw.marine_id
        LEFT JOIN marine_general_works gw ON mr.marine_id = gw.marine_id
        LEFT JOIN marine_other_services os ON mr.marine_id = os.marine_id
        GROUP BY mr.marine_id, mr.vessel_name
        ORDER BY mr.created_at DESC
        LIMIT 10
    ");

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Recent Marine Requests and Service Data Counts:\n";
    echo "================================================\n";

    if (empty($results)) {
        echo "No marine requests found in the database.\n";
    } else {
        foreach ($results as $row) {
            echo "Marine ID: {$row['marine_id']} - Vessel: {$row['vessel_name']}\n";
            echo "  - Fuel/Water Services: {$row['fuel_water_count']}\n";
            echo "  - General Works: {$row['general_works_count']}\n";
            echo "  - Other Services: {$row['other_services_count']}\n";
            echo "\n";
        }
    }

    // Check if tables exist and have data
    $tables = ['marine_fuel_water_services', 'marine_general_works', 'marine_other_services'];
    echo "Table Data Summary:\n";
    echo "==================\n";

    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "$table: $count records\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
