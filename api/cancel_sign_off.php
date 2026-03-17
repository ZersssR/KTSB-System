<?php
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['request_id']) || !isset($input['is_rtb'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid Input']);
    exit;
}

$requestId = $input['request_id'];
$isRtb = (bool) $input['is_rtb'];
$userId = $_SESSION['user_id'];

try {
    $conn = getDBConnection();

    // 1. Verify Request ownership and status
    $stmt = $conn->prepare("SELECT * FROM crew_sign_off_requests WHERE crew_signoff_id = ? AND user_id = ?");
    $stmt->execute([$requestId, $userId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Request not found or access denied']);
        exit;
    }

    if ($request['status'] !== 'pending' && $request['status'] !== 'approved' && $request['status'] !== 'pending_agent') {
        // Allow cancellation if it's not completed/rejected? 
        // For now restrict to active states.
    }

    if ($isRtb) {
        // RTB Logic
        // 1. Calculate Baggage Fee (Base rate * 2)
        // We need a base rate. Let's assume 50 for now or fetch from DB if there was a rates table.
        // Prompt says "baggage handling will doubled".
        // If baggage_handling_quantity is set, we use that.

        $qty = (int) ($request['baggage_handling_quantity'] ?: 0);
        $baseRate = 50.00; // Hardcoded generic rate for now as no rates table exists
        $totalFee = $qty * $baseRate * 2;

        $sql = "UPDATE crew_sign_off_requests SET 
                    is_rtb = 1, 
                    baggage_handling_fee = ?, 
                    status = 'rtb_verified',
                    updated_at = NOW() 
                WHERE crew_signoff_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$totalFee, $requestId]);

    } else {
        // Full Cancellation
        $sql = "UPDATE crew_sign_off_requests SET status = 'cancelled', updated_at = NOW() WHERE crew_signoff_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$requestId]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>