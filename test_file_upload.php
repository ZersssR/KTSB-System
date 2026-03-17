<?php
require_once 'config/app.php';
require_once 'src/Utils/Auth.php';

// Test file upload functionality
echo "Testing file upload API...\n";

// Simulate POST data
$_POST['request_id'] = 'B25000001';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'multipart/form-data';

// Create a temporary test file
$tempFile = tempnam(sys_get_temp_dir(), 'test_upload');
file_put_contents($tempFile, 'This is a test file content.');

// Simulate FILES array
$_FILES['file'] = [
    'name' => 'test_file.pdf',
    'type' => 'application/pdf',
    'tmp_name' => $tempFile,
    'error' => 0,
    'size' => filesize($tempFile)
];

// Simulate authentication (mock user)
$_SESSION['user'] = [
    'user_id' => 1,
    'username' => 'testuser',
    'role' => 'user'
];

// Include the upload script
ob_start();
include 'api/upload_file.php';
$response = ob_get_clean();

// Clean up
unlink($tempFile);

echo "Response: " . $response . "\n";

// Check if file was created
$expectedPath = 'assets/uploads/Marine/B25000001/test_file.pdf';
if (file_exists($expectedPath)) {
    echo "File uploaded successfully to: " . $expectedPath . "\n";
    unlink($expectedPath); // Clean up
} else {
    echo "File was not created at expected path.\n";
}

// Check database
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM request_documents WHERE request_id = ? AND file_name = ?");
    $stmt->execute(['B25000001', 'test_file.pdf']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "Database record created successfully.\n";
        // Clean up DB record
        $stmt = $conn->prepare("DELETE FROM request_documents WHERE id = ?");
        $stmt->execute([$result['id']]);
    } else {
        echo "Database record was not created.\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
?>
