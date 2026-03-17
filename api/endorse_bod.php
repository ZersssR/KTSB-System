<?php
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['bod_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing BOD ID']);
    exit;
}

$bodId = $input['bod_id'];
$userId = $_SESSION['user_id'];

try {
    $conn = getDBConnection();

    // Security Check: Ensure BOD belongs to this user/company
    $stmt = $conn->prepare("SELECT id, status FROM bods WHERE id = ? AND company_id = ?");
    $stmt->execute([$bodId, $userId]);
    $bod = $stmt->fetch();

    if (!$bod) {
        echo json_encode(['success' => false, 'error' => 'BOD not found or access denied']);
        exit;
    }

    if ($bod['status'] !== 'pending_endorsement') {
        echo json_encode(['success' => false, 'error' => 'BOD is not pending endorsement']);
        exit;
    }

    // Update Status
    $update = $conn->prepare("UPDATE bods SET status = 'endorsed', updated_at = NOW() WHERE id = ?");
    $update->execute([$bodId]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>