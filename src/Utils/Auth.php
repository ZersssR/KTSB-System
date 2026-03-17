<?php
// Authentication functions for KTSB system
require_once __DIR__ . '/../../config/app.php';

/**
 * Authenticate user with username and password
 */
/**
 * Authenticate user with username and password
 */
function authenticate($username, $password)
{
    $conn = getDBConnection();

    try {
        // 1. Check Users Table (The "User" role)
        $stmt = $conn->prepare("SELECT user_id, username, email, password_hash, status, full_name, company_name, customer_code FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            // Check concurrent session limit (10 sessions max)
            if (!checkAndEnforceSessionLimit($user['user_id'], 'user')) {
                return false; // Session limit exceeded
            }

            // Update last login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$user['user_id']]);

            // Set session data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['full_name'];
            $_SESSION['company'] = $user['company_name'];
            $_SESSION['customer_code'] = $user['customer_code'];
            $_SESSION['role'] = 'user'; // Always 'user' for this table
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'user'; // Distinguish between user and agent
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            // Store tab_id in session for consistency
            $tabId = $_POST['tab_id'] ?? null;
            if ($tabId) {
                $_SESSION['tab_id'] = $tabId;
            }

            // Track this session in database
            trackUserSession($user['user_id'], session_id(), 'user');

            return true;
        }

        // 2. Check Agents Table (The "Agent" role)
        $stmt = $conn->prepare("SELECT agent_id, username, email, password_hash, status, full_name, company_name, customer_code FROM agents WHERE username = ?");
        $stmt->execute([$username]);
        $agent = $stmt->fetch();

        if ($agent && $agent['status'] === 'active' && password_verify($password, $agent['password_hash'])) {
            // Check concurrent session limit (10 sessions max)
            if (!checkAndEnforceSessionLimit($agent['agent_id'], 'agent')) {
                return false; // Session limit exceeded
            }

            // Update last login
            $updateStmt = $conn->prepare("UPDATE agents SET last_login = NOW() WHERE agent_id = ?");
            $updateStmt->execute([$agent['agent_id']]);

            // Set session data
            $_SESSION['agent_id'] = $agent['agent_id']; // Use agent_id for agents
            $_SESSION['user_id'] = $agent['agent_id']; // Keep user_id for compatibility, or handle separately
            $_SESSION['username'] = $agent['username'];
            $_SESSION['email'] = $agent['email'];
            $_SESSION['name'] = $agent['full_name'];
            $_SESSION['company'] = $agent['company_name'];
            $_SESSION['customer_code'] = $agent['customer_code'];
            $_SESSION['role'] = 'agent';
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'agent';
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            // Store tab_id in session for consistency
            $tabId = $_POST['tab_id'] ?? null;
            if ($tabId) {
                $_SESSION['tab_id'] = $tabId;
            }

            // Track this session in database
            trackUserSession($agent['agent_id'], session_id(), 'agent');

            return true;
        }

    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
    }

    return false;
}

/**
 * Check if user is currently logged in
 * Returns true for both main app users and admin users
 */
function isLoggedIn()
{
    return (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) ||
        isset($_SESSION['admin_logged_in']);
}

/**
 * Get current user data
 * Returns data for both main app users and admin users
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    // If admin is logged in but not main app, return admin data
    if (isset($_SESSION['admin_logged_in']) && !isset($_SESSION['logged_in'])) {
        return [
            'id' => $_SESSION['admin_id'] ?? 1,
            'username' => 'Admin',
            'email' => null,
            'name' => 'Admin User',
            'company' => 'Admin',
            'role' => 'admin',
            'user_type' => 'admin'
        ];
    }

    // Return main app user/agent data
    $userData = [
        'id' => $_SESSION['user_id'], // This holds agent_id for agents
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'name' => $_SESSION['name'] ?? null,
        'company' => $_SESSION['company'] ?? 'Marine Co.',
        'customer_code' => $_SESSION['customer_code'] ?? null,
        'role' => $_SESSION['role'] ?? 'user',
        'user_type' => $_SESSION['user_type'] ?? 'user'
    ];

    if (isset($_SESSION['agent_id'])) {
        $userData['agent_id'] = $_SESSION['agent_id'];
    }

    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'user') {
        $userData['user_id'] = $_SESSION['user_id'];
    }

    return $userData;
}

/**
 * Logout user and destroy session
 */
function logout()
{
    // Remove session from database tracking
    if (isset($_SESSION['user_id'])) {
        $conn = getDBConnection();
        try {
            $stmt = $conn->prepare("DELETE FROM user_sessions WHERE session_id = ?");
            $stmt->execute([session_id()]);
        } catch (PDOException $e) {
            error_log("Session cleanup on logout error: " . $e->getMessage());
        }
    }

    // Clear all session data
    $_SESSION = [];

    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy session
    session_destroy();
}

/**
 * Require authentication - redirect to login if not authenticated
 * Allows both main app users and admin users
 */
function requireAuth()
{
    if (!isLoggedIn() && !isset($_SESSION['admin_logged_in'])) {
        redirect('login.php', 'Please login to access this page.');
    }
}

/**
 * Redirect authenticated users away from login page
 */
function redirectIfAuthenticated($redirectTo = 'index.php')
{
    if (isLoggedIn()) {
        redirect($redirectTo);
    }
}

/**
 * Create new user (for future user registration)
 */
function createUser($username, $email, $password)
{
    $conn = getDBConnection();

    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $passwordHash]);

        return ['success' => true, 'message' => 'User created successfully', 'user_id' => $conn->lastInsertId()];

    } catch (PDOException $e) {
        error_log("User creation error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create user'];
    }
}

/**
 * Update user password
 */
function updatePassword($userId, $newPassword)
{
    $conn = getDBConnection();

    try {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$passwordHash, $userId]);

        return ['success' => true, 'message' => 'Password updated successfully'];
    } catch (PDOException $e) {
        error_log("Password update error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update password'];
    }
}

/**
 * Get user by ID
 */
function getUserById($userId)
{
    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("SELECT id, username, email, created_at, last_login, is_active FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check session timeout (optional security feature)
 * Logout after specified minutes of inactivity, resets on activity
 */
function checkSessionTimeout($timeoutMinutes = 60)
{
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];

        // Determine timeout based on remember_me preference
        $timeoutSeconds = $timeoutMinutes * 60;
        if (isset($_SESSION['remember_me']) && $_SESSION['remember_me'] === true) {
            $timeoutSeconds = 30 * 24 * 60 * 60; // 30 days
        }

        if ($elapsed > $timeoutSeconds) {
            logout();
            return false;
        }
    }
    return true;
}

/**
 * Check and enforce concurrent session limit (10 sessions max)
 */
/**
 * Check and enforce concurrent session limit (10 sessions max)
 */
function checkAndEnforceSessionLimit($userId, $userType = 'user', $maxSessions = 10)
{
    $conn = getDBConnection();

    try {
        // Clean up expired sessions first (older than 60 minutes)
        cleanupExpiredSessions();

        // Count current active sessions for this user
        $stmt = $conn->prepare("SELECT COUNT(*) as session_count FROM user_sessions WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $currentSessions = $result['session_count'];

        if ($currentSessions >= $maxSessions) {
            // Terminate the oldest session to make room for new one
            $stmt = $conn->prepare("
                SELECT session_id FROM user_sessions
                WHERE user_id = ?
                ORDER BY last_activity ASC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $oldestSession = $stmt->fetch();

            if ($oldestSession) {
                // Remove the oldest session from database
                $deleteStmt = $conn->prepare("DELETE FROM user_sessions WHERE session_id = ?");
                $deleteStmt->execute([$oldestSession['session_id']]);
            }
        }

        return true; // Allow login

    } catch (PDOException $e) {
        error_log("Session limit check error: " . $e->getMessage());
        return true; // Allow login on error to avoid blocking legitimate users
    }
}

/**
 * Track user session in database
 */
function trackUserSession($userId, $sessionId, $userType = 'user')
{
    $conn = getDBConnection();

    try {
        // Insert or update session tracking
        $stmt = $conn->prepare("
            INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, created_at, last_activity)
            VALUES (?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                last_activity = NOW(),
                ip_address = VALUES(ip_address),
                user_agent = VALUES(user_agent)
        ");

        $stmt->execute([
            $userId,
            $sessionId,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

    } catch (PDOException $e) {
        error_log("Session tracking error: " . $e->getMessage());
        // Don't fail authentication if session tracking fails
    }
}

/**
 * Update session activity timestamp
 */
function updateSessionActivity()
{
    if (!isLoggedIn()) {
        return;
    }

    // Check if database connection is available before attempting update
    static $dbTested = false;
    static $dbAvailable = true;

    try {
        // Only test database on first call per request
        if (!$dbTested) {
            $conn = getDBConnection();
            // Try a simple query to test connection
            $stmt = $conn->query("SELECT 1");
            $dbTested = true;
            $dbAvailable = true;
        }

        // Only update if database is available
        if ($dbAvailable) {
            $conn = getDBConnection();
            $stmt = $conn->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE session_id = ?");
            $result = $stmt->execute([session_id()]);
            if (!$result) {
                $dbAvailable = false; // Mark as unavailable for this request
            }
        }
    } catch (PDOException $e) {
        // Silently fail for session activity updates - don't block page loads
        // Log to a file to avoid display issues
        $dbAvailable = false;
        @file_put_contents('session_errors.log', date('Y-m-d H:i:s') . " Session activity update error: " . $e->getMessage() . "\n", FILE_APPEND);
        return;
    }
}

/**
 * Clean up expired sessions (older than 120 minutes - matches timeout check)
 */
function cleanupExpiredSessions()
{
    $conn = getDBConnection();

    try {
        // Clean up sessions older than 30 days (matches max remember me duration)
        $stmt = $conn->prepare("DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Session cleanup error: " . $e->getMessage());
    }
}

/**
 * Get active sessions for current user
 */
function getActiveSessions()
{
    if (!isLoggedIn()) {
        return [];
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("
            SELECT session_id, ip_address, user_agent, created_at, last_activity
            FROM user_sessions
            WHERE user_id = ?
            ORDER BY last_activity DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get active sessions error: " . $e->getMessage());
        return [];
    }
}

/**
 * Terminate a specific session
 */
function terminateSession($sessionId)
{
    if (!isLoggedIn()) {
        return false;
    }

    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("DELETE FROM user_sessions WHERE session_id = ? AND user_id = ?");
        $stmt->execute([$sessionId, $_SESSION['user_id']]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Terminate session error: " . $e->getMessage());
        return false;
    }
}
