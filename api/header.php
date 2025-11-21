<?php
session_save_path('/tmp');
// Start session on every page that includes this header
session_start()
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./favicons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="./favicons/favicon.svg" />
    <link rel="shortcut icon" href="/favicons/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="./favicons/apple-touch-icon.png" />
    <link rel="manifest" href="./favicons/site.webmanifest" />

    <!-- === Primary SEO Meta Tags === -->
    <title>Nyota Hela | Fast M-pesa Loans</title>

    <!-- === Stylesheets & Fonts === -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-gray-50">

    <!-- Header Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <!-- Logo -->
            <a href="index.php" class="text-3xl font-extrabold text-blue-700">
                DashPesa
            </a>

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex space-x-6 items-center">
                <a href="index.php" class="text-gray-600 hover:text-blue-700 font-medium">Home</a>
                <a href="index.php#features" class="text-gray-600 hover:text-blue-700 font-medium">Features</a>
                <a href="eligibility.php"
                    class="bg-blue-600 text-white px-5 py-2 rounded-full font-medium hover:bg-blue-700 transition duration-300">
                    Apply Now
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <a href="eligibility.php"
                    class="bg-blue-600 text-white px-5 py-2 rounded-full font-medium hover:bg-blue-700 transition duration-300">
                    Apply Now
                </a>
            </div>
        </nav>
    </header>