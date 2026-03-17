<?php
require_once __DIR__ . '/config/app.php';

try {
    $conn = getDBConnection();

    $username = 'admin_new';
    $password = 'password123';
    $email = 'admin_new@ktsb.com';

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        echo "User '$username' already exists. resetting password...\n";
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
        $stmt->execute([$hash, $username]);
        echo "Password updated to '$password'.\n";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, email, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$username, $hash, $email]);
        echo "Admin user '$username' created with password '$password'.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
