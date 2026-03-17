<?php
// AJAX endpoint to update user activity
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Utils/Auth.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Update last activity timestamp
// Note: Auth.php's updateSessionActivity() already does DB update
// but we should also update $_SESSION['last_activity'] as seen in CheckAuth.php
$_SESSION['last_activity'] = time();

// Update database activity
updateSessionActivity();

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
