<?php
// Database configuration for KTSB Authentication System

// Database settings
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ktsb_application');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Kuala Terengganu Support Base');
define('SITE_URL', 'http://localhost/ktsb');

// Start session first if not already started, then set ini settings only if session not active
$sessionWasNone = session_status() == PHP_SESSION_NONE;
if ($sessionWasNone) {
    // Check for tab_id from POST/GET, cookie, or existing session for multi-tab support
    $tabId = $_POST['tab_id'] ?? $_GET['tab_id'] ?? null;

    // If no tab_id in request, check cookie
    if (!$tabId && isset($_COOKIE['ktsb_tab_id'])) {
        $tabId = $_COOKIE['ktsb_tab_id'];
    }

    // If still no tab_id, check if we have one in an existing session
    if (!$tabId && function_exists('session_name')) {
        // Try to start a temporary session to check for stored tab_id
        $tempSessionStarted = false;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $tempSessionStarted = true;
        }

        if (isset($_SESSION['tab_id'])) {
            $tabId = $_SESSION['tab_id'];
            if ($tempSessionStarted) {
                session_write_close();
                $sessionWasNone = true; // Reset since we closed it
            }
        } elseif ($tempSessionStarted) {
            session_write_close();
            $sessionWasNone = true; // Reset since we closed it
        }
    }

    // Set session name for tab-specific sessions
    if ($tabId && preg_match('/^[a-zA-Z0-9_-]+$/', $tabId)) {
        session_name('PHPSESSID_' . $tabId);
    }

    // Session configuration - only set if session not yet started
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_lifetime', 0); // 0 means until browser is closed
    ini_set('session.gc_maxlifetime', 2592000); // 30 days garbage collection

    if ($sessionWasNone) {
        session_start();
    }
}

// Database connection function
function getDBConnection()
{
    static $conn = null;

    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $conn;
}

// Utility function to get base URL
function base_url($path = '')
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $base = $protocol . $host . '/ktsb';
    return $base . '/' . ltrim($path, '/');
}

// Function to redirect with optional message
function redirect($url, $message = null)
{
    if ($message) {
        $_SESSION['flash_message'] = $message;
    }
    header("Location: " . base_url($url));
    exit();
}


// Function to display flash messages
function displayFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return "<div class='alert alert-info'>{$message}</div>";
    }
    return '';
}

// Function to sanitize input
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to log activity
function logActivity($user_id, $activity_type, $description, $reference_id = null)
{
    $pdo = getDBConnection();
    
    // Attempt to log activity
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activities (user_id, activity_type, description, reference_id, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $activity_type,
            $description,
            $reference_id,
            $_SERVER['REMOTE_ADDR'] ?? 'system'
        ]);
    } catch (PDOException $e) {
        // Silently fail if logging fails to avoids breaking the main action
        error_log("Activity logging failed: " . $e->getMessage());
    }
}
