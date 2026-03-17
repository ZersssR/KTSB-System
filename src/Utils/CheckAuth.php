<?php
// Authentication check middleware
// Include this at the top of all protected pages

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/Auth.php';

// Check if user is logged in
requireAuth();

// Reset session timeout timer on activity
if (isLoggedIn()) {
    $_SESSION['last_activity'] = time();
}

// Update session activity timestamp for tracking
updateSessionActivity();

// Check for session timeout (60 minutes)
checkSessionTimeout(60);

// User is authenticated, continue with page rendering
