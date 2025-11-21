<?php
// api/normalize-phone.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$input = json_decode(file_get_contents('php://input'), true);
$phone = $input['phone'] ?? '';

// Remove any non-numeric characters
$phone = preg_replace('/[^0-9]/', '', $phone);

// Logic to convert 07xx or 01xx to 2547xx
if (substr($phone, 0, 1) == '0') {
    $formatted = '254' . substr($phone, 1);
} elseif (substr($phone, 0, 3) == '254') {
    $formatted = $phone;
} else {
    $formatted = $phone; // Fallback
}

echo json_encode([
    'normalized_phone' => $formatted
]);
?>