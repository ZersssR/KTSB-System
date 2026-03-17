<?php
// Database configuration for KTSB Port Authority Management System

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ktsb_application');
define('DB_USER', 'root'); // Default XAMPP MySQL user
define('DB_PASS', ''); // Default XAMPP password is empty
define('DB_CHARSET', 'utf8mb4');

// User application database configuration
define('AUTH_DB_NAME', 'ktsb_application');

// Create PDO connection for admin database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Admin Database connection failed: " . $e->getMessage());
}

// Create PDO connection for user authentication database
$auth_pdo = null;
try {
    $auth_pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=3306;dbname=" . AUTH_DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Log error but don't die, as admin app might still function without auth DB connection
    error_log("User Auth Database connection failed: " . $e->getMessage());
    $auth_pdo = null; // Ensure $auth_pdo is null on failure
}


// Function to get admin database connection
function getDB()
{
    global $pdo;
    return $pdo;
}

// Function to get user authentication database connection
function getAuthDB()
{
    global $auth_pdo;
    return $auth_pdo;
}

// Function to sanitize input
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to log activity (for dashboard tracking)
function logActivity($user_id, $activity_type, $description, $reference_id = null)
{
    $pdo = getDB();

    // Check if the user_id exists in the ktsb_port_management.users table
    $stmt_check_user = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt_check_user->execute([$user_id]);
    $user_exists_in_admin_db = $stmt_check_user->fetch();

    // If user_id does not exist in the admin database, set it to NULL for activity logging
    $log_user_id = $user_exists_in_admin_db ? $user_id : null;

    $stmt = $pdo->prepare("
        INSERT INTO activities (user_id, activity_type, description, reference_id, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $log_user_id, // Use the checked user_id
        $activity_type,
        $description,
        $reference_id,
        $_SERVER['REMOTE_ADDR'] ?? 'system'
    ]);
}

// Function to get dashboard statistics
function getDashboardStats()
{
    $pdo = getDB();

    $stats = [];

    // Active vessels count
    $stmt = $pdo->query("
        SELECT COUNT(*) as active_vessels
        FROM bod_details
        WHERE status IN ('scheduled', 'arrived', 'berthed')
        AND arrival_date <= NOW()
        AND (departure_date IS NULL OR departure_date >= NOW())
    ");
    $stats['active_vessels'] = $stmt->fetch()['active_vessels'];

    // Pending BODs
    $stmt = $pdo->query("
        SELECT COUNT(*) as pending_bods
        FROM bod_details
        WHERE status = 'scheduled'
    ");
    $stats['pending_bods'] = $stmt->fetch()['pending_bods'];

    // Crew transfers today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as crew_transfers_today
        FROM crew_requests
        WHERE DATE(scheduled_date) = CURDATE()
        AND status IN ('approved', 'endorsed', 'completed')
    ");
    $stmt->execute();
    $stats['crew_transfers_today'] = $stmt->fetch()['crew_transfers_today'];

    // Fuel requests
    $stmt = $pdo->query("
        SELECT COUNT(*) as fuel_requests
        FROM fuel_water_requests
        WHERE status IN ('requested', 'approved')
        AND fuel_quantity_litres > 0
    ");
    $stats['fuel_requests'] = $stmt->fetch()['fuel_requests'];

    return $stats;
}

// Function to get recent activities
function getRecentActivities($limit = 10)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT a.*, u.full_name
        FROM activities a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT ?
    ");
    $stmt->bindParam(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Function to get all services
function getAllServices()
{
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT s.*, COUNT(r.id) as custom_rates_count
        FROM services s
        LEFT JOIN rates r ON s.id = r.service_id
        WHERE s.is_active = TRUE
        GROUP BY s.id
        ORDER BY s.category, s.service_name
    ");
    return $stmt->fetchAll();
}

// Function to get calendar events (BOD details with vessel info)
function getCalendarEvents($start_date = null, $end_date = null)
{
    $pdo = getDB();
    $params = [];
    $sql = "
        SELECT b.*, v.vessel_name, v.flag, bt.berth_code
        FROM bod_details b
        LEFT JOIN vessels v ON b.vessel_id = v.id
        LEFT JOIN berths bt ON b.berth_id = bt.id
        WHERE b.arrival_date IS NOT NULL
    ";
    if ($start_date && $end_date) {
        $sql .= " AND ((b.arrival_date BETWEEN ? AND ?) OR (b.departure_date BETWEEN ? AND ?))";
        $params = [$start_date, $end_date, $start_date, $end_date];
    }
    $sql .= " ORDER BY b.arrival_date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Function to get BOD details for planning
function getBodDetails()
{
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT b.*, v.vessel_name, v.imo_number, v.loa_meters, v.draft_meters,
               bt.berth_code, bt.berth_type
        FROM bod_details b
        LEFT JOIN vessels v ON b.vessel_id = v.id
        LEFT JOIN berths bt ON b.berth_id = bt.id
        ORDER BY b.created_at DESC
    ");
    return $stmt->fetchAll();
}

// Function to get endorsed BOD (approved status)
function getEndorsedBod()
{
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT b.*, v.vessel_name, v.imo_number, bt.berth_code,
               b.pilot_boarding_time, cr.status as endorsement_status
        FROM bod_details b
        LEFT JOIN vessels v ON b.vessel_id = v.id
        LEFT JOIN berths bt ON b.berth_id = bt.id
        LEFT JOIN crew_requests cr ON b.vessel_id = cr.vessel_id AND cr.status = 'endorsed'
        WHERE b.status IN ('scheduled', 'arrived', 'berthed')
        ORDER BY b.arrival_date DESC
    ");
    return $stmt->fetchAll();
}

// Function to get crew requests for manual sign off
function getCrewRequestsForSignOff()
{
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT cr.*, v.vessel_name, c.name as customer_name, a.name as agent_name
        FROM crew_requests cr
        LEFT JOIN vessels v ON cr.vessel_id = v.id
        LEFT JOIN customers c ON cr.customer_id = c.id
        LEFT JOIN agents a ON cr.agent_id = a.id
        WHERE cr.status IN ('approved', 'endorsed')
        ORDER BY cr.scheduled_date DESC
    ");
    return $stmt->fetchAll();
}

// Function to get documents
function getAllDocuments()
{
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT d.*, u.full_name as uploaded_by_name, v.vessel_name, c.name as customer_name
        FROM documents d
        LEFT JOIN users u ON d.uploaded_by = u.id
        LEFT JOIN vessels v ON d.reference_type = 'vessel' AND d.reference_id = v.id
        LEFT JOIN customers c ON d.reference_type = 'customer' AND d.reference_id = c.id
        ORDER BY d.upload_date DESC
    ");
    return $stmt->fetchAll();
}

// Function to get invoices
function getAllInvoices()
{
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT i.*, v.vessel_name, c.name as customer_name, s.service_name
        FROM invoices i
        LEFT JOIN vessels v ON i.vessel_id = v.id
        LEFT JOIN customers c ON i.customer_id = c.id
        LEFT JOIN services s ON i.service_id = s.id
        ORDER BY i.invoice_date DESC, i.created_at DESC
    ");
    return $stmt->fetchAll();
}

// Function to get rates
function getAllRates()
{
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT r.*, s.service_name, c.name as customer_name,
               u.full_name as approved_by_name
        FROM rates r
        LEFT JOIN services s ON r.service_id = s.id
        LEFT JOIN customers c ON r.customer_id = c.id
        LEFT JOIN users u ON r.approved_by = u.id
        ORDER BY r.start_date DESC, r.created_at DESC
    ");
    return $stmt->fetchAll();
}
?>
