<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// ---------------- CONFIGURATION ---------------- //
$consumerKey = "YOUR_CONSUMER_KEY_HERE"; 
$consumerSecret = "YOUR_CONSUMER_SECRET_HERE";
$BusinessShortCode = "174379"; // Test Paybill (Change to yours)
$Passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919"; // Test Passkey (Change to yours)
$PartyB = "174379"; // Same as Shortcode
$AccountReference = "DooChapChap";
$TransactionDesc = "Loan Processing Fee";
$CallBackURL = "https://your-website.com/callback.php"; // Safaricom requires a valid URL, even if you don't use it
// ----------------------------------------------- //

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->amount) || !isset($data->phone)) {
    echo json_encode(["ResponseCode" => "1", "ResponseDescription" => "Invalid input"]);
    exit();
}

$amount = (int)$data->amount;
$phone = $data->phone;

// 1. Format Phone to 2547...
$phone = preg_replace("/^(?:254|\+254|0)?((?:1|7)[0-9]{8})$/", "254$1", $phone);

// 2. Generate Access Token
$headers = ['Content-Type:application/json; charset=utf8'];
$url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'; // Use 'api.safaricom.co.ke' for live
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_HEADER, FALSE);
curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
$result = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$result = json_decode($result);
if ($status != 200) {
    echo json_encode(["ResponseCode" => "1", "ResponseDescription" => "Failed to generate token"]);
    exit();
}
$access_token = $result->access_token;

// 3. Generate Password
$Timestamp = date('YmdHis');
$Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);

// 4. Initiate STK Push
$stk_url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'; // Use 'api.safaricom.co.ke' for live
$curl_post_data = array(
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $PartyB,
    'PhoneNumber' => $phone,
    'CallBackURL' => $CallBackURL,
    'AccountReference' => $AccountReference,
    'TransactionDesc' => $TransactionDesc
);

$data_string = json_encode($curl_post_data);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $stk_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

$curl_response = curl_exec($curl);
curl_close($curl);

echo $curl_response;
?>