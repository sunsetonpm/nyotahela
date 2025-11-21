<?php
session_start();

// If we have session data, pre-fill variables
$saved_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$saved_phone = isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Loan | Doo ChapChap</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        /* --- YOUR EXACT CSS --- */
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --white: #ffffff;
            --black: #1a1a1a;
            --gray: #6c757d;
            --light-gray: #f8f9fa;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --border-radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            min-height: 100vh;
            background-color: var(--light-gray);
            color: var(--black);
            padding: 16px;
            line-height: 1.5;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
        }

        .welcome-card,
        .loan-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.1);
            border: 1px solid rgba(67, 97, 238, 0.1);
        }

        .welcome-text {
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .user-name {
            background: var(--gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 600;
        }

        .card-title {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 16px;
            background: var(--gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        .loan-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .loan-option {
            background: var(--light-gray);
            border: 1px solid rgba(67, 97, 238, 0.2);
            border-radius: 12px;
            padding: 14px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .loan-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
        }

        .loan-option.selected {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.1);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        .loan-amount {
            font-size: 1rem;
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }

        .processing-fee {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .btn-apply {
            width: 100%;
            padding: 16px;
            background: var(--gradient);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            margin: 16px 0;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-apply:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-apply:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Form Inputs */
        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--gray);
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(67, 97, 238, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
            transition: border 0.3s;
        }

        .input-group input:focus {
            border-color: var(--primary);
        }

        /* Modal Styles */
        .mobile-confirm-modal,
        .mobile-stk-modal {
            border-radius: 20px !important;
            padding: 0 !important;
        }

        .mobile-confirm-header,
        .mobile-stk-header {
            background: var(--gradient);
            padding: 18px;
            text-align: center;
            color: white;
        }

        .mobile-confirm-icon,
        .mobile-stk-icon {
            font-size: 36px;
            color: white;
            margin-bottom: 8px;
        }

        .mobile-confirm-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .mobile-confirm-body,
        .mobile-stk-body {
            padding: 18px;
        }

        .mobile-loan-summary {
            background: #f8f9ff;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 16px;
        }

        .mobile-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px dashed #eee;
            padding-bottom: 10px;
        }

        .mobile-phone-display,
        .mobile-stk-phone {
            background: rgba(67, 97, 238, 0.08);
            padding: 12px;
            border-radius: 8px;
            margin: 14px 0;
            text-align: center;
            font-weight: 600;
            color: #4361ee;
            border: 1px solid rgba(67, 97, 238, 0.15);
        }

        .mobile-confirm-btn {
            width: 100%;
            padding: 14px;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .mobile-cancel-btn {
            width: 100%;
            padding: 14px;
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="welcome-card">
            <p class="welcome-text">
                Hi <span class="user-name"><?php echo htmlspecialchars($saved_name ?: 'Customer'); ?></span>, you
                qualify for these loan options based on your M-Pesa records.
            </p>
        </div>

        <div class="loan-card">
            <h3 class="card-title" style="font-size: 1rem; text-align:left;">Your Details</h3>
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" id="user_name" value="<?php echo htmlspecialchars($saved_name); ?>"
                    placeholder="Enter your name">
            </div>
            <div class="input-group">
                <label>M-Pesa Number</label>
                <input type="tel" id="user_phone" value="<?php echo htmlspecialchars($saved_phone); ?>"
                    placeholder="0712345678">
            </div>
        </div>

        <div class="loan-card">
            <h3 class="card-title">Select Your Loan Amount</h3>
            <div class="loan-grid">
                <div class="loan-option" onclick="selectLoanOption(this, 5500, 50)">
                    <div class="loan-amount">Ksh 5,500</div>
                    <div class="processing-fee">Fee: Ksh 50</div>
                </div>
                <div class="loan-option" onclick="selectLoanOption(this, 7800, 80)">
                    <div class="loan-amount">Ksh 7,800</div>
                    <div class="processing-fee">Fee: Ksh 80</div>
                </div>
                <div class="loan-option" onclick="selectLoanOption(this, 9800, 140)">
                    <div class="loan-amount">Ksh 9,800</div>
                    <div class="processing-fee">Fee: Ksh 140</div>
                </div>
                <div class="loan-option" onclick="selectLoanOption(this, 11200, 180)">
                    <div class="loan-amount">Ksh 11,200</div>
                    <div class="processing-fee">Fee: Ksh 180</div>
                </div>
                <div class="loan-option" onclick="selectLoanOption(this, 25600, 350)">
                    <div class="loan-amount">Ksh 25,600</div>
                    <div class="processing-fee">Fee: Ksh 350</div>
                </div>
                <div class="loan-option" onclick="selectLoanOption(this, 48600, 1550)">
                    <div class="loan-amount">Ksh 48,600</div>
                    <div class="processing-fee">Fee: Ksh 1550</div>
                </div>
            </div>
        </div>

        <button id="apply-btn" class="btn-apply" disabled>
            Get Loan Now <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

    <script>
        let selectedLoan = null;

        function selectLoanOption(element, amount, fee) {
            // Deselect all
            document.querySelectorAll('.loan-option').forEach(opt => opt.classList.remove('selected'));
            // Select clicked
            element.classList.add('selected');
            // Update State
            selectedLoan = { amount, fee };
            // Enable Button
            const btn = document.getElementById('apply-btn');
            btn.disabled = false;
            btn.innerHTML = `Get Ksh ${amount.toLocaleString()} Now <i class="fas fa-arrow-right"></i>`;
        }

        document.getElementById('apply-btn').addEventListener('click', async function () {
            // 1. Validate Selection
            if (!selectedLoan) {
                Swal.fire('Error', 'Please select a loan amount.', 'warning');
                return;
            }

            // 2. Validate Inputs
            const name = document.getElementById('user_name').value;
            const phone = document.getElementById('user_phone').value;

            if (!name || !phone) {
                Swal.fire('Missing Details', 'Please enter your Name and Phone Number.', 'warning');
                return;
            }

            // 3. Confirmation Modal
            const { value: confirmed } = await Swal.fire({
                title: '<div class="mobile-confirm-header"><div class="mobile-confirm-icon"><i class="fas fa-check-circle"></i></div><div class="mobile-confirm-title">Confirm Loan</div></div>',
                html: `
                    <div class="mobile-confirm-modal">
                        <div class="mobile-confirm-body">
                            <div class="mobile-loan-summary">
                                <div class="mobile-summary-item"><span>Loan Amount:</span> <b>Ksh ${selectedLoan.amount.toLocaleString()}</b></div>
                                <div class="mobile-summary-item"><span>Processing Fee:</span> <b>Ksh ${selectedLoan.fee}</b></div>
                            </div>
                            <div class="mobile-phone-display"><i class="fas fa-mobile-alt"></i> ${phone}</div>
                        </div>
                        <div class="mobile-confirm-footer">
                            <div class="mobile-confirm-buttons">
                                <button class="mobile-confirm-btn" onclick="Swal.clickConfirm()"><i class="fas fa-check-circle"></i> Pay Ksh ${selectedLoan.fee}</button>
                                <button class="mobile-cancel-btn" onclick="Swal.close()"><i class="fas fa-times"></i> Cancel</button>
                            </div>
                        </div>
                    </div>`,
                showConfirmButton: false,
                customClass: { popup: 'mobile-confirm-modal' }
            });

            if (confirmed) {
                // 4. Show Loading
                Swal.fire({
                    title: 'Initiating Payment',
                    html: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // 5. Send to Backend (PHP)
                try {
                    const formData = new FormData();
                    formData.append('name', name);
                    formData.append('phone', phone);
                    formData.append('amount', selectedLoan.fee); // Pay the Fee
                    formData.append('loan_amount', selectedLoan.amount);

                    // We use fetch but target the PHP file in the SAME directory
                    const response = await fetch('stk_initiate.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        // 6. Success Instruction Modal
                        Swal.fire({
                            title: '<div class="mobile-stk-header">Payment Request Sent</div>',
                            html: `
                                <div class="mobile-stk-modal">
                                    <div class="mobile-stk-body">
                                        <i class="fas fa-mobile-alt mobile-stk-icon" style="color:#4361ee"></i>
                                        <h3>Check Your Phone</h3>
                                        <p>Enter M-Pesa PIN to pay <b>Ksh ${selectedLoan.fee}</b></p>
                                        <div class="mobile-stk-phone">${phone}</div>
                                    </div>
                                </div>`,
                            showConfirmButton: false,
                            customClass: { popup: 'mobile-stk-modal' }
                        });

                        // Auto redirect after delay
                        setTimeout(() => { window.location.href = 'dash.html'; }, 8000);
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    Swal.fire('Failed', error.message, 'error');
                }
            }
        });
    </script>
</body>

</html>