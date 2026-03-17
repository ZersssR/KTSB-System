<?php
require_once 'config/database.php';
$pdo = getDB();

try {
    // Create marine_overtime_requests table
    echo "Creating marine_overtime_requests table...\n";
    $sql1 = "CREATE TABLE IF NOT EXISTS marine_overtime_requests (
        overtime_id VARCHAR(20) PRIMARY KEY,
        user_id INT(11) NOT NULL,
        company_name VARCHAR(100) NOT NULL,
        request_date DATE NOT NULL,
        request_time TIME NOT NULL,
        vessels_data LONGTEXT NOT NULL,
        receipt_no VARCHAR(50),
        receipt_files LONGTEXT,
        remarks TEXT,
        status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )";
    $pdo->exec($sql1);
    echo "marine_overtime_requests created successfully.\n";

    // Create port_clearance_requests table
    echo "Creating port_clearance_requests table...\n";
    $sql2 = "CREATE TABLE IF NOT EXISTS port_clearance_requests (
        clearance_id VARCHAR(20) PRIMARY KEY,
        user_id INT(11) NOT NULL,
        clearance_type ENUM('inward', 'outward') NOT NULL,
        vessel_name VARCHAR(255) NOT NULL,
        company_name VARCHAR(100) NOT NULL,
        request_date DATE NOT NULL,
        request_time TIME NOT NULL,
        receipt_no VARCHAR(50),
        receipt_file VARCHAR(255),
        remarks TEXT,
        status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )";
    $pdo->exec($sql2);
    echo "port_clearance_requests created successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
