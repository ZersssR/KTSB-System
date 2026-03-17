<?php
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id']; // This is actually the Company ID in our simplified model

try {
    $conn = getDBConnection();

    // 1. Fetch Draft/Pending BODs for this company
    // Status 'pending_endorsement' only
    $stmt = $conn->prepare("SELECT * FROM bods WHERE company_id = ? AND status = 'pending_endorsement' ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $bods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Services for each BOD to display details
    // We can do this efficiently or loop. Given low volume, loop is okay for now.
    foreach ($bods as &$bod) {
        $bod['services'] = [];
        $bodId = $bod['id'];

        // Helper to fetch services from a table
        $tables = [
            'marine_requests' => 'marine_id',
            'crew_sign_on_requests' => 'crew_signon_id',
            'crew_sign_off_requests' => 'crew_signoff_id',
            'fuel_water_requests' => 'fuelwater_id',
            'light_port_requests' => 'lightport_id'
        ];

        foreach ($tables as $table => $pk) {
            $sql = "SELECT vessel_name, '$table' as type FROM $table WHERE bod_id = ?";
            $s = $conn->prepare($sql);
            $s->execute([$bodId]);
            $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                // Cleaner Type Name
                $r['type'] = ucwords(str_replace(['_requests', '_'], ['', ' '], $r['type']));
                $bod['services'][] = $r;
            }
        }
    }

    echo json_encode(['success' => true, 'bods' => $bods]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>