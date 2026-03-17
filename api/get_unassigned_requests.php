<?php
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');

if (!isset($_GET['company_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing Company ID']);
    exit;
}

$companyId = $_GET['company_id'];

try {
    $conn = getDBConnection();

    // We need to fetch from multiple tables (marine_requests, crew_sign_on, etc.)
    // where user_id matches (assuming user_id IS the company link in current schema, 
    // or strictly company_id if we migrated. The Prompt says "instead of using actual names, use company name". 
    // The `users` table likely represents the company. So `user_id` in request tables = `company_id`.

    $queries = [
        "SELECT marine_id as id, vessel_name, created_at, 'marine_requests' as source_table FROM marine_requests WHERE bod_id IS NULL AND user_id = ?",
        "SELECT crew_signon_id as id, vessel_name, created_at, 'crew_sign_on_requests' as source_table FROM crew_sign_on_requests WHERE bod_id IS NULL AND user_id = ?",
        "SELECT crew_signoff_id as id, vessel_name, created_at, 'crew_sign_off_requests' as source_table FROM crew_sign_off_requests WHERE bod_id IS NULL AND user_id = ?",
        "SELECT fuelwater_id as id, vessel_name, created_at, 'fuel_water_requests' as source_table FROM fuel_water_requests WHERE bod_id IS NULL AND user_id = ?",
        "SELECT lightport_id as id, vessel_name, created_at, 'light_port_requests' as source_table FROM light_port_requests WHERE bod_id IS NULL AND user_id = ?"
    ];

    $allRequests = [];

    foreach ($queries as $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $row['original_id'] = $row['id'];
            $row['unique_id'] = $row['source_table'] . '_' . $row['id']; // For frontend key
            $allRequests[] = $row;
        }
    }

    // Sort by Date DESC
    usort($allRequests, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    echo json_encode(['success' => true, 'requests' => $allRequests]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>