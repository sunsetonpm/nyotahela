<?php
// --- CONFIGURATION & SETUP ---
session_start();
header("Access-Control-Allow-Origin: *"); // Allow CORS

// Helper: File-based "Database" for transaction status
$DB_FILE = 'transactions.json';

function updateTransaction($checkoutRequestId, $status, $data = []) {
    global $DB_FILE;
    $db = file_exists($DB_FILE) ? json_decode(file_get_contents($DB_FILE), true) : [];
    
    if (!isset($db[$checkoutRequestId])) {
        $db[$checkoutRequestId] = [];
    }
    
    $db[$checkoutRequestId]['status'] = $status;
    $db[$checkoutRequestId]['updated_at'] = date('Y-m-d H:i:s');
    if (!empty($data)) {
        $db[$checkoutRequestId] = array_merge($db[$checkoutRequestId], $data);
    }
    
    file_put_contents($DB_FILE, json_encode($db, JSON_PRETTY_PRINT));
}

function getTransaction($checkoutRequestId) {
    global $DB_FILE;
    $db = file_exists($DB_FILE) ? json_decode(file_get_contents($DB_FILE), true) : [];
    return $db[$checkoutRequestId] ?? null;
}

// --- API ROUTER ---
$action = $_GET['action'] ?? '';

if ($action == 'normalize_phone') {
    $input = json_decode(file_get_contents('php://input'), true);
    $phone = $input['phone'] ?? '';
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) == '0') {
        $phone = '254' . substr($phone, 1);
    } elseif (substr($phone, 0, 3) != '254') {
        $phone = '254' . $phone;
    }
    echo json_encode(['normalized_phone' => $phone]);
    exit;
}

if ($action == 'initiate_payment') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $consumerKey = getenv('MPESA_CONSUMER_KEY');
    $consumerSecret = getenv('MPESA_CONSUMER_SECRET');
    $mpesaShortCode = getenv('MPESA_SHORTCODE');
    $mpesaPasskey = getenv('MPESA_PASSKEY');
    $callbackUrl = getenv('MPESA_CALLBACK_URL');
    $environment = "live"; // Kept your setting

    // Set API URLs
    $authUrl = ($environment == 'live') ? "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials" : "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
    $stkPushUrl = ($environment == 'live') ? "https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest" : "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

    // Security check - removed redirect, return JSON error
    if (!isset($_SESSION['phone_number']) && !isset($input['phone_number'])) { // Adjusted to check input too
         echo json_encode(['error' => 'Session expired or missing phone number']);
         exit;
    }

    $phone_number = $input['phone_number'];
    $service_fee = (int)$input['amount'];

    // Reformat phone
    $formattedPhone = (substr($phone_number, 0, 1) == "0") ? "254" . substr($phone_number, 1) : $phone_number;

    // Use '1' for sandbox testing, real amount for live
    $stkAmount = ($environment == 'live') ? $service_fee : 1;

    // Get Access Token
    $ch = curl_init($authUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        // FIX: Echo JSON instead of Header Location
        echo json_encode(['error' => "Connection Error: " . curl_error($ch)]);
        exit;
    }
    curl_close($ch);
    $authData = json_decode($response);
    if (!isset($authData->access_token)) {
        // FIX: Echo JSON instead of Header Location
        echo json_encode(['error' => "Unable to get API access token. Check Keys."]);
        exit;
    }
    $accessToken = $authData->access_token;

    // Initiate STK Push
    $timestamp = date('YmdHis');
    $password = base64_encode($mpesaShortCode . $mpesaPasskey . $timestamp);

    $stkPayload = [
        'BusinessShortCode' => $mpesaShortCode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $stkAmount,
        'PartyA' => $formattedPhone,
        'PartyB' => "9294061", // Kept your hardcoded PartyB
        'PhoneNumber' => $formattedPhone,
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'Nyota',
        'TransactionDesc' => "LoanApp"
    ];

    $stkData = json_encode($stkPayload);

    $ch = curl_init($stkPushUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $stkData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $accessToken]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        // FIX: Echo JSON instead of Header Location
        echo json_encode(['error' => "Connection Error: " . curl_error($ch)]);
        exit;
    }
    curl_close($ch);

    $stkResponse = json_decode($response);

    if (isset($stkResponse->ResponseCode) && $stkResponse->ResponseCode == "0") {
        // FIX: Save to DB file so verification works (Session isn't enough for polling)
        updateTransaction($stkResponse->CheckoutRequestID, 'PENDING', ['phone' => $formattedPhone]);
        
        // FIX: Return JSON success
        echo json_encode([
            'success' => true,
            'reference' => $stkResponse->CheckoutRequestID,
            'message' => $stkResponse->CustomerMessage
        ]);
        exit;
    } else {
        // FIX: Return JSON error
        $errorMessage = $stkResponse->errorMessage ?? $stkResponse->ResponseDescription ?? 'An unknown error occurred.';
        echo json_encode(['error' => $errorMessage]);
        exit;
    }
}

if ($action == 'callback') {
    $data = file_get_contents('php://input');
    file_put_contents('callback_log.txt', $data . PHP_EOL, FILE_APPEND);
    $json = json_decode($data);
    
    if ($json && isset($json->Body->stkCallback)) {
        $callback = $json->Body->stkCallback;
        $checkoutRequestId = $callback->CheckoutRequestID;
        $resultCode = $callback->ResultCode;
        
        if ($resultCode == 0) {
            updateTransaction($checkoutRequestId, 'COMPLETED');
        } else {
            updateTransaction($checkoutRequestId, 'FAILED');
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit;
}

if ($action == 'verify_payment') {
    header('Content-Type: application/json');
    $ref = $_GET['reference'] ?? '';
    $trx = getTransaction($ref);
    
    if ($trx) {
        // --- START SIMULATION LOGIC (FOR TESTING ONLY) ---
        // Since we can't receive real callbacks on localhost/webhook.site,
        // we check if 5 seconds have passed, then force it to COMPLETED.
        
        $timeCreated = strtotime($trx['updated_at']); // When transaction started
        $timeNow = time();
        $secondsWaited = $timeNow - $timeCreated;

        // If it's still PENDING and we've waited more than 5 seconds...
        if ($trx['status'] == 'PENDING' && $secondsWaited > 5) {
            // Fake the success!
            updateTransaction($ref, 'COMPLETED');
            $trx['status'] = 'COMPLETED'; // Update local variable to send back
        }
        // --- END SIMULATION LOGIC ---

        echo json_encode(['success' => true, 'status' => $trx['status']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    }
    exit;
}

// --- FRONTEND RENDER ---
// This is your new Landing Page HTML
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nyota Hela | Fast M-Pesa Loans</title>

  <!-- Favicons -->
  <link rel="apple-touch-icon" sizes="192x192" href="icons/icon.192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="icons/icon.512.png">

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
      --primary: #2563eb;
      --primary-light: #3b82f6;
      --secondary: #1e40af;
      --white: #ffffff;
      --gray: #6b7280;
      --light: #f8fafc;
      --dark: #1e293b;
      --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
      --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --radius: 12px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: var(--light);
      color: var(--dark);
      line-height: 1.6;
    }

    /* Header */
    .header {
      background: var(--white);
      padding: 18px 20px;
      box-shadow: var(--shadow);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      font-size: 1.4rem;
      color: var(--primary);
    }

    .logo i {
      font-size: 1.6rem;
    }

    /* Hero Section */
    .hero {
      background: var(--gradient);
      color: var(--white);
      padding: 80px 20px 120px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: "";
      position: absolute;
      top: -50%;
      right: -20%;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
    }

    .hero::after {
      content: "";
      position: absolute;
      bottom: -30%;
      left: -10%;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.05);
    }

    .hero-content {
      max-width: 600px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }

    .hero h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 16px;
      line-height: 1.2;
    }

    .hero p {
      font-size: 1.1rem;
      opacity: 0.9;
      margin-bottom: 32px;
    }

    .btn-apply {
      padding: 14px 32px;
      border: none;
      border-radius: 50px;
      background: var(--white);
      color: var(--primary);
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: var(--shadow);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-apply:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    /* Main Content */
    .main-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    /* Loan Highlight */
    .loan-highlight {
      background: var(--white);
      margin: -60px auto 50px;
      padding: 30px;
      border-radius: var(--radius);
      text-align: center;
      box-shadow: var(--shadow);
      max-width: 600px;
      position: relative;
      z-index: 2;
    }

    .loan-highlight h2 {
      font-size: 1.8rem;
      color: var(--primary);
      margin-bottom: 10px;
    }

    .loan-highlight p {
      color: var(--gray);
      font-size: 1rem;
    }

    /* Features Section */
    .features-section {
      margin: 60px 0;
    }

    .section-title {
      text-align: center;
      margin-bottom: 40px;
    }

    .section-title h2 {
      font-size: 1.8rem;
      color: var(--dark);
      margin-bottom: 10px;
    }

    .section-title p {
      color: var(--gray);
      max-width: 600px;
      margin: 0 auto;
    }

    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 24px;
      max-width: 1000px;
      margin: 0 auto;
    }

    .feature {
      background: var(--white);
      border-radius: var(--radius);
      padding: 30px 20px;
      text-align: center;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      border-top: 4px solid var(--primary);
    }

    .feature:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .feature i {
      font-size: 2.2rem;
      color: var(--primary);
      margin-bottom: 16px;
    }

    .feature p {
      font-weight: 600;
      font-size: 1rem;
      color: var(--dark);
    }

    /* Trust Section */
    .trust-section {
      margin: 60px 0;
    }

    .trust-badges {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin: 30px auto;
      flex-wrap: wrap;
      max-width: 700px;
    }

    .trust-badge {
      background: var(--white);
      border: 1px solid rgba(37, 99, 235, 0.2);
      padding: 12px 20px;
      border-radius: 50px;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: var(--shadow);
    }

    .trust-badge i {
      color: var(--primary);
    }

    .trust-note {
      text-align: center;
      color: var(--gray);
      font-size: 0.95rem;
      margin: 40px 0 60px;
    }

    /* Footer */
    .footer {
      background: var(--white);
      padding: 30px 20px;
      text-align: center;
      color: var(--gray);
      font-size: 0.9rem;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      margin-top: 40px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }
      
      .features {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .trust-badges {
        gap: 12px;
      }
      
      .trust-badge {
        padding: 10px 16px;
        font-size: 0.85rem;
      }
    }
  </style>
</head>

<body>
  <header class="header">
    <div class="header-content">
      <div class="logo">
        <i class="fas fa-bolt"></i>
        <span>Nyota Hela</span>
      </div>
    </div>
  </header>

  <section class="hero">
    <div class="hero-content">
      <h1>Instant M-Pesa Loans</h1>
      <p>Get funds directly to your M-Pesa in minutes. Simple, fast, and secure when you need it most.</p>
      <button class="btn-apply">Apply Now <i class="fas fa-arrow-right"></i></button>
    </div>
  </section>

  <main class="main-content">
    <div class="loan-highlight">
      <h2>Borrow Ksh. 1,000 - 50,000</h2>
      <p>New customers qualify for up to Ksh. 10,000 instantly.</p>
    </div>

    <section class="features-section">
      <div class="section-title">
        <h2>Why Choose Doo ChapChap?</h2>
        <p>We make borrowing simple, fast, and secure</p>
      </div>
      
      <div class="features">
        <div class="feature">
          <i class="fas fa-bolt"></i>
          <p>5-Minute Approval</p>
        </div>
        <div class="feature">
          <i class="fas fa-file-alt"></i>
          <p>No Paperwork</p>
        </div>
        <div class="feature">
          <i class="fas fa-shield-alt"></i>
          <p>Bank-Level Security</p>
        </div>
        <div class="feature">
          <i class="fas fa-user-friends"></i>
          <p>No Guarantors</p>
        </div>
      </div>
    </section>

    <section class="trust-section">
      <div class="section-title">
        <h2>Trusted & Secure</h2>
        <p>Your security and trust are our top priorities</p>
      </div>
      
      <div class="trust-badges">
        <div class="trust-badge">
          <i class="fas fa-lock"></i> SSL Secured
        </div>
        <div class="trust-badge">
          <i class="fas fa-certificate"></i> CBK Licensed
        </div>
        <div class="trust-badge">
          <i class="fas fa-check-circle"></i> Verified Service
        </div>
      </div>

      <p class="trust-note">Trusted by over 50,000 Kenyans nationwide</p>
    </section>
  </main>

  <footer class="footer">
    <p>&copy; 2025 Nyota. All rights reserved.</p>
  </footer>

  <script>
    document.querySelector('.btn-apply').addEventListener('click', () => {
      // Redirect to the eligibility page (ensure you have eligibility.html or eligibility.php)
      window.location.href = 'eligibility.html';
    });
  </script>
</body>

</html>
