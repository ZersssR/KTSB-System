<?php
require_once __DIR__ . '/../../config/app.php';

// Load Dompdf
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

// Get marine_id from URL
$marine_id = $_GET['marine_id'] ?? '';

if (!$marine_id) {
    die('Marine ID is required');
}

$conn = getDBConnection();

// Function to generate Job Ticket number
function generateJobTicket($conn, $marine_id) {
    // Check if job ticket already exists
    $stmt = $conn->prepare("SELECT job_ticket FROM marine_requests WHERE marine_id = ?");
    $stmt->execute([$marine_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!empty($result['job_ticket'])) {
        return $result['job_ticket'];
    }
    
    // Generate new job ticket format: FW0842ECFA/001
    $prefix = 'FW';
    $year = date('y'); // 24 for 2024
    $month = date('m'); // 08 for August
    $random = strtoupper(substr(uniqid(), -4)); // 4 random chars
    
    // Get last sequence number
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM marine_requests 
        WHERE job_ticket LIKE ? 
    ");
    $stmt->execute([$prefix . '%']);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;
    
    $job_ticket = sprintf("%s%s%s%s/%03d", $prefix, $year, $month, $random, $count);
    
    // Save to database
    $stmt = $conn->prepare("UPDATE marine_requests SET job_ticket = ? WHERE marine_id = ?");
    $stmt->execute([$job_ticket, $marine_id]);
    
    return $job_ticket;
}

// Fetch marine request details
$stmt = $conn->prepare("
    SELECT mr.*, 
           u.company_name as user_company,
           u.username as requester_name,
           u.full_name as requester_full_name,
           a.full_name as agent_name,
           v.vessel_name
    FROM marine_requests mr
    LEFT JOIN users u ON mr.user_id = u.user_id
    LEFT JOIN agents a ON mr.assigned_agent_id = a.agent_id
    LEFT JOIN vessels v ON mr.vessel_name = v.vessel_name
    WHERE mr.marine_id = ?
");
$stmt->execute([$marine_id]);
$marine_request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$marine_request) {
    die('Marine request not found');
}

// Generate or get job ticket
$job_ticket = generateJobTicket($conn, $marine_id);

// Fetch fuel & water services
$stmt = $conn->prepare("
    SELECT * FROM marine_fuel_water_services 
    WHERE marine_id = ? 
    ORDER BY service_type, id
");
$stmt->execute([$marine_id]);
$fuel_water_services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$totalFuel = 0;
$totalWater = 0;
$actualFuel = 0;
$actualWater = 0;

foreach ($fuel_water_services as $service) {
    if ($service['service_type'] == 'fuel') {
        $totalFuel += $service['quantity'];
        $actualFuel += $service['actual_quantity'] ?? $service['quantity'];
    } else {
        $totalWater += $service['quantity'];
        $actualWater += $service['actual_quantity'] ?? $service['quantity'];
    }
}

// Create HTML content for PDF
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel & Water Request - ' . $marine_id . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 20px;
            margin: 5px 0;
            color: #000;
        }
        .header h2 {
            font-size: 18px;
            margin: 5px 0;
            color: #444;
        }
        .request-info {
            margin: 20px 0;
            overflow: hidden;
        }
        .left-col {
            float: left;
            width: 48%;
        }
        .right-col {
            float: right;
            width: 48%;
        }
        .info-row {
            margin: 8px 0;
            font-size: 12px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
            color: #555;
        }
        .value {
            font-weight: normal;
        }
        .job-ticket {
            color: #0066cc;
            font-weight: bold;
            font-size: 14px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #333;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11px;
        }
        th {
            background-color: #343a40;
            color: white;
            padding: 10px 5px;
            text-align: center;
            font-weight: bold;
        }
        td {
            padding: 8px 5px;
            border: 1px solid #ddd;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-box {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .summary-row {
            margin: 5px 0;
            font-size: 12px;
        }
        .summary-label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }
        .signature-section {
            margin-top: 40px;
            overflow: hidden;
        }
        .signature-box {
            float: left;
            width: 45%;
            margin-right: 5%;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 80%;
            margin: 5px 0;
            height: 20px;
        }
        .signature-text {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .clearfix {
            clear: both;
        }
        .watermark {
            position: fixed;
            bottom: 10px;
            right: 10px;
            opacity: 0.1;
            font-size: 60px;
            color: #000;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">KTSB</div>
    
    <div class="header">
        <h1>KUALA TERENGGANU SUPPORT BASE</h1>
        <h2>FUEL & WATER REQUEST FORM</h2>
    </div>
    
    <!-- REQUEST INFORMATION -->
    <div class="section-title">REQUEST INFORMATION</div>
    <div class="request-info">
        <div class="left-col">
            <div class="info-row">
                <span class="label">Company:</span>
                <span class="value">' . htmlspecialchars($marine_request['company'] ?? 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="label">Vessel Name:</span>
                <span class="value">' . htmlspecialchars($marine_request['vessel_name'] ?? 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="label">Location:</span>
                <span class="value">KTSB</span>
            </div>
            <div class="info-row">
                <span class="label">BOD No:</span>
                <span class="value">' . htmlspecialchars($marine_request['bod_no'] ?? 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="label">Log No:</span>
                <span class="value">' . htmlspecialchars($marine_request['log_no'] ?? 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="value">' . ucfirst($marine_request['status'] ?? 'N/A') . '</span>
            </div>
        </div>
        <div class="right-col">
            <div class="info-row">
                <span class="label">Job Ticket:</span>
                <span class="value job-ticket">' . $job_ticket . '</span>
            </div>
            <div class="info-row">
                <span class="label">Booking Date:</span>
                <span class="value">' . date('d/m/Y', strtotime($marine_request['created_at'])) . '</span>
            </div>
            <div class="info-row">
                <span class="label">Request By:</span>
                <span class="value">' . htmlspecialchars($marine_request['requester_name'] ?? 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="label">Agent:</span>
                <span class="value">' . htmlspecialchars($marine_request['agent_name'] ?? 'Not Assigned') . '</span>
            </div>
            <div class="info-row">
                <span class="label">PO Number:</span>
                <span class="value">' . htmlspecialchars($marine_request['po_number'] ?? 'N/A') . '</span>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    
    <!-- FUEL & WATER DETAILS -->
    <div class="section-title">FUEL & WATER SUPPLY DETAILS</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Service Type</th>
                <th width="12%">Quantity (L)</th>
                <th width="12%">Actual Qty (L)</th>
                <th width="12%">Booking Time</th>
                <th width="12%">Actual Time</th>
                <th width="32%">Remarks</th>
            </tr>
        </thead>
        <tbody>';

if (count($fuel_water_services) > 0) {
    $no = 1;
    foreach ($fuel_water_services as $service) {
        $html .= '
            <tr>
                <td class="text-center">' . $no++ . '</td>
                <td>' . ucfirst($service['service_type']) . '</td>
                <td class="text-right">' . number_format($service['quantity'], 2) . '</td>
                <td class="text-right">' . ($service['actual_quantity'] ? number_format($service['actual_quantity'], 2) : '-') . '</td>
                <td class="text-center">' . ($service['booking_time'] ? date('H:i', strtotime($service['booking_time'])) : '-') . '</td>
                <td class="text-center">' . ($service['actual_booking_time'] ? date('H:i', strtotime($service['actual_booking_time'])) : '-') . '</td>
                <td>' . htmlspecialchars($service['remarks'] ?? '-') . '</td>
            </tr>';
    }
} else {
    $html .= '<tr><td colspan="7" class="text-center">No fuel or water services requested</td></tr>';
}

$html .= '
        </tbody>
    </table>
    
    <!-- SUMMARY -->
    <div class="section-title">SUMMARY</div>
    <div class="summary-box">
        <div class="summary-row">
            <span class="summary-label">Total Fuel Requested:</span>
            <span>' . number_format($totalFuel, 2) . ' Litres</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Water Requested:</span>
            <span>' . number_format($totalWater, 2) . ' Litres</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Actual Fuel Supplied:</span>
            <span>' . number_format($actualFuel, 2) . ' Litres</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Actual Water Supplied:</span>
            <span>' . number_format($actualWater, 2) . ' Litres</span>
        </div>
    </div>
    
    <!-- SCHEDULE INFORMATION -->
    <div class="section-title">SCHEDULE INFORMATION</div>
    <div class="request-info">
        <div class="left-col">
            <div class="info-row">
                <span class="label">ETA:</span>
                <span class="value">' . ($marine_request['eta'] ? date('d/m/Y H:i', strtotime($marine_request['eta'])) : 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="label">ETD:</span>
                <span class="value">' . ($marine_request['etd'] ? date('d/m/Y H:i', strtotime($marine_request['etd'])) : 'N/A') . '</span>
            </div>
        </div>
        <div class="right-col">
            <div class="info-row">
                <span class="label">Actual ETA:</span>
                <span class="value">' . ($marine_request['actual_eta'] ? date('d/m/Y H:i', strtotime($marine_request['actual_eta'])) : 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="label">Actual ETD:</span>
                <span class="value">' . ($marine_request['actual_etd'] ? date('d/m/Y H:i', strtotime($marine_request['actual_etd'])) : 'N/A') . '</span>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    
    <!-- SIGNATURES -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="info-row"><span class="label">Prepared by:</span></div>
            <div class="signature-line"></div>
            <div class="signature-text">Administrator</div>
            <div class="signature-text">Date: ' . date('d/m/Y') . '</div>
        </div>
        <div class="signature-box">
            <div class="info-row"><span class="label">Approved by:</span></div>
            <div class="signature-line"></div>
            <div class="signature-text">Authorized Signatory</div>
            <div class="signature-text">Date: ______________</div>
        </div>
        <div class="clearfix"></div>
    </div>
    
    <div class="footer">
        Document generated by KTSB System on ' . date('d/m/Y H:i:s') . ' | Job Ticket: ' . $job_ticket . '
    </div>
</body>
</html>';

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', false);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Generate filename
$filename = 'Fuel_Water_' . $marine_id . '_' . date('Ymd_His') . '.pdf';

// Save to file
$output_path = $_SERVER['DOCUMENT_ROOT'] . '/ktsb/uploads/generated_documents/' . $filename;
file_put_contents($output_path, $dompdf->output());

// Log document generation
$stmt = $conn->prepare("
    INSERT INTO generated_documents (request_id, request_type, document_type, file_name, file_path, job_ticket, generated_by) 
    VALUES (?, 'marine', 'fuel_water', ?, ?, ?, ?)
");
$file_path_db = '/uploads/generated_documents/' . $filename;
$stmt->execute([$marine_id, $filename, $file_path_db, $job_ticket, $_SESSION['admin_id']]);

// Output PDF to browser
$dompdf->stream($filename, array("Attachment" => true));
exit;