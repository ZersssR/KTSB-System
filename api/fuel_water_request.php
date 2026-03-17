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
    $berthRequest = $input["berthRequest"] ?? [];
    $fuelWaterRequests = $input["fuelWaterRequests"] ?? [];
    $fuelWaterGeneral = $input["fuelWaterGeneral"] ?? [];
    $userId = $currentUser["user_id"] ?? $currentUser["id"];

    if (!$userId) {
        echo json_encode(["success" => false, "message" => "User not identified."]);
        exit();
    }

    try {
        $conn->beginTransaction();

        // Collect fuel and water requests separately
        $fuelRequest = null;
        $waterRequest = null;

        foreach ($fuelWaterRequests as $request) {
            if ($request["type"] === "fuel") {
                $fuelRequest = $request;
            } elseif ($request["type"] === "water") {
                $waterRequest = $request;
            }
        }

        // Insert into fuel_water_requests table (single row per vessel request)
        $vesselName = $berthRequest["vesselName"] ?? null;
        $poNumber = $berthRequest["poNumber"] ?? null;
        $requestDate = $fuelWaterGeneral["bookingDate"] ?? null;
        $requestTime = date("H:i:s"); // Use current time as main request time

        // Fuel details
        $fuelQuantity = $fuelRequest ? $fuelRequest["quantity"] : 0;
        $fuelBookingTime = $fuelRequest ? $fuelRequest["bookingTime"] : null;
        $fuelRemarks = $fuelRequest ? $fuelRequest["remarks"] : null;

        // Water details
        $waterQuantity = $waterRequest ? $waterRequest["quantity"] : 0;
        $waterBookingTime = $waterRequest ? $waterRequest["bookingTime"] : null;
        $waterRemarks = $waterRequest ? $waterRequest["remarks"] : null;

        $generalRemarks = $fuelWaterGeneral["remarks"] ?? null;
        $assignedAgentId = !empty($fuelWaterGeneral["assignedAgentId"]) ? $fuelWaterGeneral["assignedAgentId"] : null;
        $status = "pending";

        if (!$vesselName || !$requestDate) {
            throw new Exception("Missing required fields for fuel/water request.");
        }

        // Generate fuelwater_id (e.g., FW25000001)
        $requestId = generateRequestID($conn, 'fuel_water_requests', 'fuelwater_id', 'FW');

        $stmt = $conn->prepare("INSERT INTO fuel_water_requests (fuelwater_id, user_id, vessel_name, po_number, request_date, request_time, fuel_quantity, fuel_booking_time, fuel_remarks, water_quantity, water_booking_time, water_remarks, remarks, status, assigned_agent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$requestId, $userId, $vesselName, $poNumber, $requestDate, $requestTime, $fuelQuantity, $fuelBookingTime, $fuelRemarks, $waterQuantity, $waterBookingTime, $waterRemarks, $generalRemarks, $status, $assignedAgentId]);

        createNotification('fuel_water', "New Fuel & Water Request from " . $currentUser['username'], "fuel-water-detail.php?id=" . $requestId);

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Fuel & Water requests submitted successfully.", "requestId" => $requestId]);
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Fuel & Water request submission error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Fuel & Water request submission error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Application error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
