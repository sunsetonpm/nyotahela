<?php
session_save_path('/tmp');
// Start a session to store user data across pages
session_start();

// 1. Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Retrieve and sanitize form data
    $full_name = trim(htmlspecialchars($_POST['full_name']));
    $phone_number = trim(htmlspecialchars($_POST['phone_number']));
    $id_number = trim(htmlspecialchars($_POST['id_number']));
    $loan_type = trim(htmlspecialchars($_POST['loan_type']));

    // 3. Server-side validation
    $errors = [];

    if (empty($full_name)) {
        $errors[] = "Full Name is required.";
    }

    // Validate Kenyan phone number (e.g., 07... or 01... with 10 digits total)
    if (!preg_match("/^(07|01)\d{8}$/", $phone_number)) {
        $errors[] = "Please enter a valid M-Pesa phone number (e.g., 0712345678).";
    }

    // Validate ID number (basic check for 7 or 8 digits)
    if (!preg_match("/^\d{7,8}$/", $id_number)) {
        $errors[] = "Please enter a valid National ID number.";
    }

    if (empty($loan_type)) {
        $errors[] = "Please select a Loan Type.";
    }

    // 4. Process the data
    if (count($errors) > 0) {
        // If there are errors, redirect back to the eligibility form with the first error message
        $error_message = urlencode($errors[0]);
        header("Location: eligibility.php?error=$error_message");
        exit;

    } else {
        define('ENCRYPTION_KEY', 'CHANGE_THIS_TO_A_32_CHAR_SECRET_KEY');
        define('ENCRYPTION_CIPHER', 'AES-256-CBC');

        // 1. Bundle the data
        $user_data = [
            'full_name' => $full_name,
            'phone_number' => $phone_number,
            'id_number' => $id_number,
            'loan_type' => $loan_type,
            'timestamp' => time() // Add a timestamp for security
        ];

        $json_data = json_encode($user_data);

        // 2. Encrypt the data
        $iv_length = openssl_cipher_iv_length(ENCRYPTION_CIPHER);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $key = ENCRYPTION_KEY;

        $ciphertext = openssl_encrypt($json_data, ENCRYPTION_CIPHER, $key, 0, $iv);

        // Prepend the IV to the ciphertext, then base64 encode
        $token = base64_encode($iv . $ciphertext);

        // 3. Redirect to the loan options page with the secure token
        header("Location: apply?token=" . urlencode($token));
        exit;
    }

} else {
    // If not a POST request, redirect to the homepage
    header("Location: index.php");
    exit;
}
?>