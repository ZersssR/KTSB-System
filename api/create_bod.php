<?php
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');

// Check Admin Auth (Session based check logic should be here or handled by framework)
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['company_id']) || !isset($input['requests'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid Input']);
    exit;
}

try {
    $conn = getDBConnection();
    $conn->beginTransaction();

    // 1. Create BOD Record
    $companyId = $input['company_id'];
    $bodType = $input['bod_type'] ?? 'general';
    $createdBy = $_SESSION['admin_id'];

    $stmt = $conn->prepare("INSERT INTO bods (company_id, status, bod_type, created_by) VALUES (?, 'pending_endorsement', ?, ?)");
    $stmt->execute([$companyId, $bodType, $createdBy]);
    $bodId = $conn->lastInsertId();

    // 2. Update Requests
    $requests = $input['requests']; // Array of {id, table}

    foreach ($requests as $req) {
        $table = $req['table'];
        $id = $req['id'];

        // Validate table name to prevent SQL injection (allowlist)
        $allowedTables = [
            'marine_requests',
            'crew_sign_on_requests',
            'crew_sign_off_requests',
            'fuel_water_requests',
            'light_port_requests'
        ];

        if (!in_array($table, $allowedTables)) {
            throw new Exception("Invalid table name: $table");
        }

        // Determine ID column name based on table (naming convention varies)
        $idCol = '';
        switch ($table) {
            case 'marine_requests':
                $idCol = 'marine_id';
                break;
            case 'crew_sign_on_requests':
                $idCol = 'crew_signon_id';
                break;
            case 'crew_sign_off_requests':
                $idCol = 'crew_signoff_id';
                break;
            case 'fuel_water_requests':
                $idCol = 'fuelwater_id';
                break;
            case 'light_port_requests':
                $idCol = 'lightport_id';
                break;
        }

        $sql = "UPDATE $table SET bod_id = ? WHERE $idCol = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$bodId, $id]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'bod_id' => $bodId]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>