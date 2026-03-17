<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Utils/Auth.php';

header('Content-Type: application/json');

$currentUser = getCurrentUser();

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Only company (user) and admin can see agents to assign
if ($currentUser['role'] !== 'user' && $currentUser['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Fetch active agents from agents table
    $sql = "SELECT agent_id as id, username, full_name as name FROM agents WHERE status = 'active' ORDER BY full_name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'agents' => $agents]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
