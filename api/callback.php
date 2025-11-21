<?php
// api/callback.php
header("Content-Type: application/json");

// 1. Receive Data from Safaricom
$callbackJSONData = file_get_contents('php://input');
$callbackData = json_decode($callbackJSONData);

if (isset($callbackData->Body->stkCallback)) {
    $stkCallback = $callbackData->Body->stkCallback;
    $checkoutRequestID = $stkCallback->CheckoutRequestID;
    $resultCode = $stkCallback->ResultCode; // 0 = Success, others = Fail

    // Determine status
    $status = ($resultCode == 0) ? 'COMPLETED' : 'FAILED';

    // 2. Save status to file/database so verify-payment.php can read it
    // We use the CheckoutRequestID as the filename/key
    $filePath = "transactions/" . $checkoutRequestID . ".json";
    
    $record = [
        'status' => $status,
        'result_desc' => $stkCallback->ResultDesc,
        'amount' => '', // Extract specific metadata if needed
        'mpesa_receipt' => ''
    ];

    // If successful, extract receipt number
    if ($resultCode == 0 && isset($stkCallback->CallbackMetadata)) {
        foreach ($stkCallback->CallbackMetadata->Item as $item) {
            if ($item->Name == 'MpesaReceiptNumber') {
                $record['mpesa_receipt'] = $item->Value;
            }
        }
    }

    file_put_contents($filePath, json_encode($record));
}

// Acknowledge receipt to Safaricom
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
?>