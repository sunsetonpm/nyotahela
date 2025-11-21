<?php 
// This page also just needs the header.
include 'header.php'; 
?>

<main class="py-20 bg-gray-50" style="min-height: 60vh;">
    <div class="container mx-auto px-6">
        <div class="max-w-xl mx-auto bg-white p-8 md:p-12 rounded-2xl shadow-xl border border-gray-200 text-center">
            
            <!-- Checkmark Icon -->
            <svg class="w-16 h-16 text-green-500 mx-auto mb-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            <h2 class="text-3xl font-bold text-center text-gray-800 mb-4">
                Payment Initiated
            </h2>
            <p class="text-center text-gray-600 mb-8 text-lg">
                We are processing your payment. If you have paid, please **save the M-Pesa transaction SMS** you receive for your records.
            </p>
            <p class="text-center text-gray-500 mb-8">
                Your loan will be disbursed as soon as we confirm the payment (this is usually instant).
            </p>

            <a href="index.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-300">
                Back to Home
            </a>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
