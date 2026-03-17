<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../src/Utils/Auth.php";
require_once __DIR__ . "/notification_helper.php";
require_once __DIR__ . "/helpers.php";

header("Content-Type: application/json");

$currentUser = getCurrentUser();
if (!$currentUser) {
    echo json_encode(["success" => false, "message" => "Authentication required."]);
    exit();
}

$conn = getDBConnection();

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
        $conn->beginTransaction();

        // Insert into crew_sign_on_requests table
        $vesselName = $generalInfo["vesselName"] ?? null;
        $poNumber = $generalInfo["poNumber"] ?? null;
        $requestDate = $generalInfo["requestDate"] ?? null;
        $requestTime = $generalInfo["requestTime"] ?? null;
        $remarks = $generalInfo["remarks"] ?? null;
        $assignedAgentId = $generalInfo["assignedAgentId"] ?? null;

        // Other services data
        $packedMealsQuantity = $otherServices["packedMealsQuantity"] ?? null;
        $snackPackQuantity = $otherServices["snackPackQuantity"] ?? null;
        $baggageDetails = $otherServices["baggageDetails"] ?? null;
        $bagTaggingQuantity = $otherServices["bagTaggingQuantity"] ?? null;

        if (!$vesselName || !$requestDate || !$requestTime) {
            throw new Exception("Missing required fields: vessel name, date, and time.");
        }

        // Generate crew_signon_id (e.g., CRN25000001)
        $requestId = generateRequestID($conn, 'crew_sign_on_requests', 'crew_signon_id', 'CRN');

        // Insert main request
        $stmt = $conn->prepare("INSERT INTO crew_sign_on_requests (crew_signon_id, user_id, vessel_name, po_number, request_date, request_time, remarks, assigned_agent_id, packed_meals_quantity, snack_pack_quantity, baggage_details, bag_tagging_quantity, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$requestId, $userId, $vesselName, $poNumber, $requestDate, $requestTime, $remarks, $assignedAgentId, $packedMealsQuantity, $snackPackQuantity, $baggageDetails, $bagTaggingQuantity]);

        // Insert crew details
        if (!empty($crewDetails)) {
            $crewStmt = $conn->prepare("INSERT INTO crew_sign_on_details (crew_signon_id, crew_name, ic_passport, mobile_number, nationality, passport_expiry, company, destination) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($crewDetails as $crew) {
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
            }
        }

        createNotification('crew_sign_on', "New Crew Sign On Request from " . $currentUser['username'], "sign-on-detail.php?id=" . $requestId);

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Crew sign-on request submitted successfully.", "requestId" => $requestId]);
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Crew sign-on request submission error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Crew sign-on request submission error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Application error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method. Use POST."]);
}
?>
