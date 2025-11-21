<?php
// api/verify-payment.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    echo json_encode(['success' => false, 'status' => 'INVALID']);
    exit;
}

$filePath = "transactions/" . $reference . ".json";

if (file_exists($filePath)) {
    $data = json_decode(file_get_contents($filePath), true);
    
    // Return exactly what your JS expects
    echo json_encode([
        'success' => true,
        'status' => $data['status'] // returns COMPLETED, FAILED, or PENDING
    ]);
} else {
    // File doesn't exist yet (Callback hasn't arrived)
    echo json_encode([
        'success' => true,
        'status' => 'PENDING'
    ]);
}
?>