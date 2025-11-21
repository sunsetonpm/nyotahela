<?php
session_start();
header("Access-Control-Allow-Origin: *"); // Allow CORS
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
