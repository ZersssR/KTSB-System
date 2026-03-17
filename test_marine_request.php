<?php
// Test script to verify marine request submission works
require_once 'config/app.php';
require_once 'src/Utils/Auth.php';

try {
    $conn = getDBConnection();
    echo "Database connection successful.\n";

    // Test data for marine request
    $testData = [
        "vesselName" => "Test Vessel",
        "poNumber" => "TEST001",
        "eta" => "2025-01-15T10:00:00",
        "etd" => "2025-01-16T10:00:00",
        "company" => "Test Company",
        "remarks" => "Test marine request",
        "assignedAgentId" => null,
        "crewTransferType" => "sign_on",
        "crewData" => [
            [
                "name" => "John Doe",
                "ic" => "123456789",
                "nationality" => "Malaysia",
                "passportExpiry" => "2026-01-01",
                "mobile" => "0123456789",
                "company" => "Test Company",
                "destination" => "Port Klang"
            ]
        ],
        "otherServicesData" => [
            [
                "service" => "packed_meals",
                "quantity" => 5
            ]
        ],
        "fuelWaterData" => [
            [
                "type" => "fuel",
                "quantity" => 100,
                "bookingTime" => "14:00",
                "remarks" => "Test fuel request"
            ]
        ],
        "generalWorksData" => [
            [
                "work" => "Discharge",
                "remarks" => "Test discharge work"
            ]
        ]
    ];

    // Simulate API request
    $_SERVER["REQUEST_METHOD"] = "POST";
    $input = json_encode($testData);

    // Mock current user
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';
    $_SESSION['company'] = 'Test Company';

    // Include the API file
    ob_start();
    include 'api/marine_request.php';
    $output = ob_get_clean();

    $response = json_decode($output, true);

    if ($response && $response['success']) {
        echo "✅ Marine request submission test PASSED\n";
        echo "Generated marine_id: " . $response['id'] . "\n";

        // Verify data was inserted correctly
        $marineId = $response['id'];

        // Check marine_requests table
        $stmt = $conn->prepare("SELECT * FROM marine_requests WHERE marine_id = ?");
        $stmt->execute([$marineId]);
        $marineRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($marineRequest) {
            echo "✅ Marine request inserted successfully\n";

            // Check crew details
            $stmt = $conn->prepare("SELECT COUNT(*) as crew_count FROM marine_crew_details WHERE marine_id = ?");
            $stmt->execute([$marineId]);
            $crewCount = $stmt->fetch()['crew_count'];
            echo "✅ Crew details inserted: $crewCount records\n";

            // Check other services
            $stmt = $conn->prepare("SELECT COUNT(*) as services_count FROM marine_other_services WHERE marine_id = ?");
            $stmt->execute([$marineId]);
            $servicesCount = $stmt->fetch()['services_count'];
            echo "✅ Other services inserted: $servicesCount records\n";

            // Check fuel/water services
            $stmt = $conn->prepare("SELECT COUNT(*) as fuel_count FROM marine_fuel_water_services WHERE marine_id = ?");
            $stmt->execute([$marineId]);
            $fuelCount = $stmt->fetch()['fuel_count'];
            echo "✅ Fuel/water services inserted: $fuelCount records\n";

            // Check general works
            $stmt = $conn->prepare("SELECT COUNT(*) as works_count FROM marine_general_works WHERE marine_id = ?");
            $stmt->execute([$marineId]);
            $worksCount = $stmt->fetch()['works_count'];
            echo "✅ General works inserted: $works_count records\n";

        } else {
            echo "❌ Marine request not found in database\n";
        }

    } else {
        echo "❌ Marine request submission test FAILED\n";
        echo "Error: " . ($response['message'] ?? 'Unknown error') . "\n";
    }

} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
}
?>
