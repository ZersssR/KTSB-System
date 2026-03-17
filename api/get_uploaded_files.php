<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Utils/Auth.php';

header('Content-Type: application/json');

$currentUser = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_GET['request_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$requestId = $_GET['request_id']; // String ID

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT rd.*,
               CASE
                   WHEN rd.uploader_type = 'user' THEN u.username
                   WHEN rd.uploader_type = 'agent' THEN a.username
                   WHEN rd.uploader_type = 'admin' THEN ad.username
                   ELSE 'Unknown'
               END as uploader_name
        FROM request_documents rd
        LEFT JOIN users u ON rd.uploaded_by = u.user_id AND rd.uploader_type = 'user'
        LEFT JOIN agents a ON rd.uploaded_by = a.agent_id AND rd.uploader_type = 'agent'
        LEFT JOIN admins ad ON rd.uploaded_by = ad.id AND rd.uploader_type = 'admin'
        WHERE rd.request_id = ?
        ORDER BY rd.created_at DESC
    ");
    $stmt->execute([$requestId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'documents' => $documents]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
