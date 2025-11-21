<?php 
// This page doesn't need a session, it just includes the header.
include 'header.php'; 

// Get the phone number from the URL to pass it to the next page
$phone_for_js = '';
if (isset($_GET['phone'])) {
    $phone_for_js = htmlspecialchars($_GET['phone']);
}
?>

<!-- Tailwind CSS for the spinner animation -->
<style>
    .spinner {
        border-top-color: #3498db;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<main class="py-20 bg-blue-50" style="min-height: 60vh;">
    <div class="container mx-auto px-6">
        <div class="max-w-xl mx-auto bg-white p-8 md:p-12 rounded-2xl shadow-xl border border-gray-200 text-center">
            
            <!-- Spinner -->
            <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-600 rounded-full spinner mx-auto mb-6"></div>

            <h2 class="text-3xl font-bold text-center text-gray-800 mb-4">
                Awaiting Payment
            </h2>
            <p class="text-center text-gray-600 mb-8 text-lg">
                Please check your M-Pesa on phone number <strong><?php echo $phone_for_js; ?></strong> and enter your PIN to complete the payment.
            </p>
            <p class="text-center text-gray-500 text-sm">
                This page will automatically check your status in 30 seconds...
            </p>
        </div>
    </div>
</main>

<script>
    // Wait for 30 seconds, then redirect to the record transaction page
    window.setTimeout(function() {
        // Pass the phone number along to the next page
        window.location.href = 'recording?phone=<?php echo urlencode($phone_for_js); ?>';
    }, 30000); // 30,000 milliseconds = 30 seconds
</script>

<?php include 'footer.php'; ?>
