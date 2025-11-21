<?php
// api/initiate-payment.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for production security
header("Access-Control-Allow-Methods: POST");

// 1. CONFIGURATION
$consumerKey = getenv('MPESA_CONSUMER_KEY'); 
$consumerSecret = getenv('MPESA_CONSUMER_SECRET');
$mpesaShortCode = getenv('MPESA_SHORTCODE');
$mpesaPasskey = getenv('MPESA_PASSKEY');
$callbackUrl = getenv('MPESA_CALLBACK_URL'); // Must be https://yourdomain.com/api/callback.php
$partyB = "4096483"; // Your Paybill/Till Number

// 2. READ JSON INPUT (Since JS uses fetch body: JSON.stringify)
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['phone_number']) || !isset($input['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing phone number or amount']);
    exit;
}

$phone_number = $input['phone_number'];
$amount = $input['amount']; // This is the processing fee
$loan_amount = $input['loan_amount'] ?? 0;

// 3. GET ACCESS TOKEN
$authUrl = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
$ch = curl_init($authUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Handle SSL in dev
$response = curl_exec($ch);
$authData = json_decode($response);
curl_close($ch);

if (!isset($authData->access_token)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate M-Pesa Token']);
    exit;
}
$accessToken = $authData->access_token;

// 4. INITIATE STK PUSH
$timestamp = date('YmdHis');
$password = base64_encode($mpesaShortCode . $mpesaPasskey . $timestamp);
$stkPushUrl = "https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

$stkPayload = [
    'BusinessShortCode' => $mpesaShortCode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerBuyGoodsOnline', 
    'Amount' => $amount, 
    'PartyA' => $phone_number, // Logic handles 254 formatting in normalize endpoint usually, but ensure it's 254 here
    'PartyB' => '9294061',
    'PhoneNumber' => $phone_number,
    'CallBackURL' => $callbackUrl,
    'AccountReference' => 'LoanFee', 
    'TransactionDesc' => "Processing fee for Loan"
];

$ch = curl_init($stkPushUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$stkResponseRaw = curl_exec($ch);
curl_close($ch);

$stkResponse = json_decode($stkResponseRaw);

// 5. RETURN JSON RESPONSE TO FRONTEND
if (isset($stkResponse->ResponseCode) && $stkResponse->ResponseCode == "0") {
    // SUCCESS: Return the CheckoutRequestID as the reference
    echo json_encode([
        'success' => true,
        'message' => 'STK Push initiated',
        'reference' => $stkResponse->CheckoutRequestID
    ]);
    
    // OPTIONAL: Create a pending record in file/db for the verification script to find later
    $ref = $stkResponse->CheckoutRequestID;
    file_put_contents("transactions/$ref.json", json_encode(['status' => 'PENDING']));
    
} else {
    // FAIL
    http_response_code(500);
    echo json_encode([
        'error' => $stkResponse->errorMessage ?? 'STK Push Failed'
    ]);
}
?>
