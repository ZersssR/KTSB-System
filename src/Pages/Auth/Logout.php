<?php
// Logout handler
require_once __DIR__ . '/../../Utils/Auth.php';

// Perform logout
logout();

// Check if this is an automatic logout from tab close
$autoLogout = isset($_GET['auto_logout']) && $_GET['auto_logout'] == '1';
if (!$autoLogout) {
    // Only redirect to login page if not an automatic logout
    redirect('login.php', 'You have been logged out successfully.');
} else {
    // For automatic logout, just output a simple response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Auto-logout successful']);
    exit;
}
?>
