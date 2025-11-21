<?php

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// --- 2. SET UP LOGGING ---
$logFile = "callback_log.txt";
$stkCallbackResponse = file_get_contents('php://input');

$log = fopen($logFile, "a");
fwrite($log, $stkCallbackResponse . "\n");
fclose($log);

// --- 3. DECODE THE RESPONSE ---
$data = json_decode($stkCallbackResponse);

if ($data === null) {
    $log = fopen($logFile, "a");
    fwrite($log, "Invalid JSON received\n");
    fclose($log);
    
    header('Content-Type: application/json');
    echo '{"ResultCode": 1, "ResultDesc": "Failed to parse JSON"}';
    exit;
}

// --- 4. PROCESS THE CALLBACK ---
$resultCode = $data->Body->stkCallback->ResultCode;
$checkoutRequestID = $data->Body->stkCallback->CheckoutRequestID;
$resultDesc = $data->Body->stkCallback->ResultDesc;

if ($resultCode == 0) {
    // --- PAYMENT SUCCESSFUL ---
    $callbackMetadata = $data->Body->stkCallback->CallbackMetadata;
    $amount = null;
    $mpesaReceiptNumber = null;
    $transactionDate = null;
    $phoneNumber = null;

    foreach ($callbackMetadata->Item as $item) {
        if ($item->Name == 'Amount') {
            $amount = $item->Value;
        } elseif ($item->Name == 'MpesaReceiptNumber') {
            $mpesaReceiptNumber = $item->Value;
        } elseif ($item->Name == 'TransactionDate') {
            $transactionDate = $item->Value;
        } elseif ($item->Name == 'PhoneNumber') {
            $phoneNumber = $item->Value;
        }
    }

    $logMessage = "SUCCESS: CheckoutID: $checkoutRequestID, Receipt: $mpesaReceiptNumber, Phone: $phoneNumber, Amount: $amount, Date: $transactionDate\n";
    $log = fopen($logFile, "a");
    fwrite($log, $logMessage);
    fclose($log);

} else {
    // --- PAYMENT FAILED OR CANCELED ---
    $logMessage = "FAILED: CheckoutID: $checkoutRequestID, ResultCode: $resultCode, Desc: $resultDesc\n";
    $log = fopen($logFile, "a");
    fwrite($log, $logMessage);
    fclose($log);
}

// --- 6. SEND ACKNOWLEDGEMENT TO SAFARICOM ---
header('Content-Type: application/json');
echo '{"ResultCode": 0, "ResultDesc": "Accepted"}';

exit;
?>
