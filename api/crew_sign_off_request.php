<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../src/Utils/Auth.php";
require_once __DIR__ . "/notification_helper.php";
require_once __DIR__ . "/helpers.php";

file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " Request received: " . file_get_contents("php://input") . "\n", FILE_APPEND);

header("Content-Type: application/json");

$currentUser = getCurrentUser();
if (!$currentUser) {
    echo json_encode(["success" => false, "message" => "Authentication required."]);
    exit();
}


file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " User: " . json_encode($currentUser) . "\n", FILE_APPEND);

file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Connecting to DB...\n", FILE_APPEND);
$conn = getDBConnection();
file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Connected to DB\n", FILE_APPEND);


$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $generalInfo = $input["generalInfo"] ?? [];
    $otherServices = $input["otherServices"] ?? [];
    $crewDetails = $input["crewDetails"] ?? [];
    $userId = $currentUser["user_id"] ?? $currentUser["id"];


    if (!$userId) {
        echo json_encode(["success" => false, "message" => "User not identified."]);
        exit();
    }

    try {
        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Starting Transaction\n", FILE_APPEND);
        $conn->beginTransaction();

        // Insert into crew_sign_off_requests table
        $vesselName = $generalInfo["vesselName"] ?? null;
        $poNumber = $generalInfo["poNumber"] ?? null;
        $requestDate = $generalInfo["requestDate"] ?? null;
        $requestTime = $generalInfo["requestTime"] ?? null;
        $remarks = $generalInfo["remarks"] ?? null;
        $assignedAgentId = $generalInfo["assignedAgentId"] ?? null;

        // Other services data
        $takeawayQuantity = $otherServices["takeawayQuantity"] ?? null;
        $baggageHandlingQuantity = $otherServices["baggageHandlingQuantity"] ?? null;

        if (!$vesselName || !$requestDate || !$requestTime) {
            throw new Exception("Missing required fields: vessel name, date, and time.");
        }

        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Generating Request ID\n", FILE_APPEND);
        // Generate crew_signoff_id (e.g., CRF25000001)
        $requestId = generateRequestID($conn, 'crew_sign_off_requests', 'crew_signoff_id', 'CRF');
        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Request ID Generated: $requestId\n", FILE_APPEND);

        // Insert main request
        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Inserting main request\n", FILE_APPEND);
        $stmt = $conn->prepare("INSERT INTO crew_sign_off_requests (crew_signoff_id, user_id, vessel_name, po_number, request_date, request_time, remarks, assigned_agent_id, takeaway_quantity, baggage_handling_quantity, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$requestId, $userId, $vesselName, $poNumber, $requestDate, $requestTime, $remarks, $assignedAgentId, $takeawayQuantity, $baggageHandlingQuantity]);
        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Main request inserted\n", FILE_APPEND);

        // Insert crew details
        if (!empty($crewDetails)) {
            file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Preparing crew stmt " . count($crewDetails) . " items\n", FILE_APPEND);
            $crewStmt = $conn->prepare("INSERT INTO crew_sign_off_details (crew_signoff_id, crew_name, ic_passport, mobile_number, nationality, passport_expiry, company, destination) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($crewDetails as $index => $crew) {
                file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Inserting crew #$index\n", FILE_APPEND);
                try {
                $crewStmt->execute([
                    $requestId,
                    $crew["name"] ?? "",
                    $crew["ic"] ?? "",
                    $crew["mobile"] ?? null,
                    $crew["nationality"] ?? null,
                    $crew["passportExpiry"] ?: null,
                    $crew["company"] ?? null,
                    $crew["destination"] ?? null
                ]);
                } catch (Throwable $t) {
                     file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Error inserting crew #$index: " . $t->getMessage() . "\n", FILE_APPEND);
                     throw $t;
                }
            }
            file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: All crew inserted\n", FILE_APPEND);
        }

        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Creating notification\n", FILE_APPEND);
        createNotification('crew_sign_off', "New Crew Sign Off Request from " . $currentUser['username'], "sign-off-detail.php?id=" . $requestId);
        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Notification created\n", FILE_APPEND);

        $conn->commit();
        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " CP: Committed\n", FILE_APPEND);
        echo json_encode(["success" => true, "message" => "Crew sign-off request submitted successfully.", "requestId" => $requestId]);

    } catch (PDOException $e) {
        $conn->rollBack();
        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " PDOException: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
        error_log("Crew sign-off request submission error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " Throwable: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
        error_log("Crew sign-off request submission error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Application error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method. Use POST."]);
}
