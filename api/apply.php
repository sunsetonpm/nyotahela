<?php
// --- STATELESS VERCEL FIX ---
// We cannot use sessions. We will decrypt data passed in the URL.

// !! IMPORTANT: This key MUST be the *exact same* 32-character string
// used in your 'process_eligibility.php' file.
define('ENCRYPTION_KEY', 'CHANGE_THIS_TO_A_32_CHAR_SECRET_KEY');
define('ENCRYPTION_CIPHER', 'AES-256-CBC');

/**
 * Decrypts data passed from the eligibility form.
 */
function decrypt_data($token) {
    try {
        $data = base64_decode($token);
        $iv_length = openssl_cipher_iv_length(ENCRYPTION_CIPHER);
        
        if (strlen($data) <= $iv_length) {
            return null;
        }

        $iv = substr($data, 0, $iv_length);
        $ciphertext = substr($data, $iv_length);
        $key = ENCRYPTION_KEY;

        $json_data = openssl_decrypt($ciphertext, ENCRYPTION_CIPHER, $key, 0, $iv);

        if ($json_data === false) {
            return null;
        }

        $user_data = json_decode($json_data, true);
        return $user_data;

    } catch (Exception $e) {
        return null;
    }
}
// --- End of Functions ---

$user_data = null;
if (isset($_GET['token'])) {
    $user_data = decrypt_data($_GET['token']);
}

// Security check
if ($user_data === null || !isset($user_data['full_name']) || !isset($user_data['phone_number'])) {
    header("Location: eligibility.php?error=Invalid+data.+Please+fill+out+your+details+first.");
    exit;
}

include 'header.php';

$full_name = htmlspecialchars($user_data['full_name']);
$first_name = explode(' ', $full_name)[0]; 
$phone_number_for_js = htmlspecialchars($user_data['phone_number']);
?>

<main class="py-20 bg-gray-50">
    <div class="container mx-auto px-6">
        
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-4">
            Welcome, <?php echo $first_name; ?>!
        </h2>
        <p class="text-center text-gray-600 mb-10 text-lg">
            You are eligible for the following loan options. Please select one to proceed.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-7xl mx-auto">
            
            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 5,500</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 50</span></p>
                </div>
                <button onclick="showPaymentPopup(5500, 50, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 6,800</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 80</span></p>
                </div>
                <button onclick="showPaymentPopup(6800, 80, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 7,800</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 120</span></p>
                </div>
                <button onclick="showPaymentPopup(7800, 120, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 9,800</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 140</span></p>
                </div>
                <button onclick="showPaymentPopup(9800, 140, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 11,200</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 180</span></p>
                </div>
                <button onclick="showPaymentPopup(11200, 180, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-blue-700 text-white p-6 rounded-2xl shadow-2xl border border-blue-800 text-center flex flex-col justify-between transform md:scale-105 relative z-10">
                <span class="absolute top-0 right-4 -mt-3 bg-yellow-400 text-blue-900 text-xs font-bold px-3 py-1 rounded-full">POPULAR</span>
                <div>
                    <div class="text-4xl font-extrabold text-white mb-2">Ksh 16,800</div>
                    <p class="text-blue-100 mb-4 text-sm">Service Fee: <span class="font-bold text-white">Ksh 200</span></p>
                </div>
                <button onclick="showPaymentPopup(16800, 200, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-white text-blue-700 px-6 py-2 rounded-lg font-bold hover:bg-gray-100 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 21,200</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 220</span></p>
                </div>
                <button onclick="showPaymentPopup(21200, 220, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 25,600</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 350</span></p>
                </div>
                <button onclick="showPaymentPopup(25600, 350, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 30,000</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 420</span></p>
                </div>
                <button onclick="showPaymentPopup(30000, 420, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 35,400</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 540</span></p>
                </div>
                <button onclick="showPaymentPopup(35400, 540, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 39,800</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 680</span></p>
                </div>
                <button onclick="showPaymentPopup(39800, 680, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 44,200</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 960</span></p>
                </div>
                <button onclick="showPaymentPopup(44200, 960, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 48,600</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 1,550</span></p>
                </div>
                <button onclick="showPaymentPopup(48600, 1550, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 text-center flex flex-col justify-between">
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-2">Ksh 60,600</div>
                    <p class="text-gray-500 mb-4 text-sm">Service Fee: <span class="font-bold text-gray-700">Ksh 2,000</span></p>
                </div>
                <button onclick="showPaymentPopup(60600, 2000, '<?php echo $phone_number_for_js; ?>')"
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition duration-300">
                    Apply Now
                </button>
            </div>

        </div> </div>
</main>

<div id="payment-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-6 hidden z-50">
    <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-md w-full relative animate-zoom-in">
        
        <button onclick="closePaymentPopup()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <h3 class="text-2xl font-bold text-gray-800 text-center mb-4">Confirm Payment</h3>
        <p class="text-center text-gray-600 mb-6">
            To receive your loan of <span id="popup-loan-amount-text" class="font-bold text-gray-900"></span>, please pay the 
            <span id="popup-fee-text" class="font-bold text-gray-900"></span> service fee.
        </p>
        <p class="text-center text-gray-600 mb-8">
            A payment request will be sent to your M-Pesa number:
            <br>
            <strong id="popup-phone-text" class="text-lg text-gray-900"></strong>
        </p>

        <form id="payment-form" action="process_payment" method="POST">
            <input type="hidden" name="loan_amount" id="popup-loan-amount-input">
            <input type="hidden" name="service_fee" id="popup-service-fee-input">
            
            <input type="hidden" name="phone_number" value="<?php echo $phone_number_for_js; ?>">
            
            <button type="submit" id="stk-push-button"
                    class="w-full bg-green-600 text-white px-8 py-3 rounded-lg font-bold text-lg hover:bg-green-700 shadow-lg transition duration-300">
                Pay with M-Pesa
            </button>
            <p id="stk-loading-text" class="text-center text-gray-600 mt-4 hidden">
                Sending request to your phone...
            </p>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('payment-modal');
    const loanAmountText = document.getElementById('popup-loan-amount-text');
    const feeText = document.getElementById('popup-fee-text');
    const phoneText = document.getElementById('popup-phone-text');
    const loanAmountInput = document.getElementById('popup-loan-amount-input');
    const serviceFeeInput = document.getElementById('popup-service-fee-input');
    const payButton = document.getElementById('stk-push-button');
    const loadingText = document.getElementById('stk-loading-text');

    function showPaymentPopup(amount, fee, phone) {
        // Update text
        loanAmountText.textContent = 'Ksh. ' + amount.toLocaleString();
        feeText.textContent = 'Ksh. ' + fee.toLocaleString();
        phoneText.textContent = phone;

        // Update hidden form inputs
        loanAmountInput.value = amount;
        serviceFeeInput.value = fee;

        // Show the modal
        modal.classList.remove('hidden');
    }

    function closePaymentPopup() {
        // Hide the modal
        modal.classList.add('hidden');
        // Reset button state
        payButton.disabled = false;
        payButton.textContent = 'Pay with M-Pesa';
        loadingText.classList.add('hidden');
    }

    // Show loading state on form submit
    document.getElementById('payment-form').addEventListener('submit', function() {
        payButton.disabled = true;
        payButton.textContent = 'Sending...';
        loadingText.classList.remove('hidden');
    });

</script>

<?php include 'footer.php'; ?>