<?php
// AJAX endpoint to update user activity
require_once 'config.php';
require_once 'auth.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    exit('Unauthorized');
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Update database activity
updateSessionActivity();

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
