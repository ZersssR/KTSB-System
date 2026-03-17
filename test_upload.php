<?php
require_once 'config/app.php';
require_once 'src/Utils/Auth.php';

// Simulate a file upload test
header('Content-Type: application/json');

echo json_encode(['message' => 'Testing file upload functionality']);

// Test directory creation and permissions
$testRequestId = 'B25000001';
$serviceName = 'Marine';
$uploadDir = 'assets/uploads/' . $serviceName . '/' . $testRequestId . '/';

if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['error' => 'Failed to create upload directory']);
        exit;
    }
}

$testFile = $uploadDir . 'test_write.tmp';
if (file_put_contents($testFile, 'test') === false) {
    echo json_encode(['error' => 'Upload directory is not writable']);
    exit;
}
unlink($testFile);

echo json_encode(['success' => 'Directory setup successful']);
?>
