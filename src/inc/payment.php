<!-- Enhanced Checkout Form Section -->
<section class="checkout-form" style="display:none;">
    <?php
    // Get user details from session
    $user_name = $_SESSION['username'] ?? '';
    $user_email = $_SESSION['email'] ?? '';

    if (!$user_email || !$user_name) {
        header("Location: login.php");
        die("User not logged in");
    }
    
    // Stripe publishable key
    $stripe_publishable_key = 'pk_test_51STfLcAksjEcZwsYPOGI0xqUKScqT1AS4GFHnubNNqd3e0YVWomPXk9cABvxKyuOc4yokyT8VtlvzXd6LkWHQiTG0003O6qTzj';
    ?>
    
    <!-- Stripe Payment Processing Overlay -->
    <div id="payment-processing-overlay" style="display: none;">
        <div class="processing-modal">
            <div class="stripe-logo-container">
                <svg width="60" height="26" viewBox="0 0 60 26" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#635BFF" d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.93 0 1.85 6.29.97 6.29 5.88z"/>
                </svg>
            </div>
            <div class="processing-spinner"></div>
            <h4>Processing Payment</h4>
            <p>Processing your payment through Stripe...</p>
            <p class="processing-note">Please do not close this window or press back</p>
        </div>
    </div>
    
    <section class="payment-section">
        <h2 class="section-title">Checkout</h2>
        
        <!-- Booking Summary -->
        <div class="checkout-summary mb-4">
            <h5 class="mb-3 fw-bold">Booking Details</h5>
            
            <div class="summary-row">
                <span class="summary-label">Name:</span>
                <span class="summary-value"><?php echo htmlspecialchars($user_name); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Email:</span>
                <span class="summary-value"><?php echo htmlspecialchars($user_email); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Room:</span>
                <span class="summary-value" id="checkout-room">--</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Date:</span>
                <span class="summary-value" id="checkout-date">--</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Time:</span>
                <span class="summary-value" id="checkout-time">--</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Players:</span>
                <span class="summary-value" id="checkout-players">--</span>
            </div>
            <div class="summary-row total-row">
                <span class="summary-label">Total:</span>
                <span class="summary-value">$<span id="checkout-total">0</span></span>
            </div>
        </div>
        
        <form id="payment-form">
            <!-- Billing Address -->
            <h5 class="mb-3 fw-bold">Billing Address</h5>
            
            <div class="mb-3">
                <label for="billing-address" class="form-label">Street Address</label>
                <input type="text" class="form-control" id="billing-address" 
                       placeholder="123 Main St" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="billing-city" class="form-label">City</label>
                    <input type="text" class="form-control" id="billing-city" 
                           placeholder="Singapore" required>
                </div>
                <div class="col-md-6">
                    <label for="billing-postal" class="form-label">Postal Code</label>
                    <input type="text" class="form-control" id="billing-postal" 
                           placeholder="123456" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="billing-country" class="form-label">Country</label>
                <select class="form-control" id="billing-country" required>
                    <option value="SG" selected>Singapore</option>
                </select>
            </div>
            
            <!-- Payment Method -->
            <h5 class="mb-3 fw-bold">Payment Method</h5>
            
            <!-- Apple Pay / Google Pay Button -->
            <div id="payment-request-button" class="payment-request-button">
                <!-- Stripe will inject Apple Pay/Google Pay button here if available -->
            </div>
            
            <div class="payment-divider" id="payment-divider" style="display: none;">
                <span>OR PAY WITH CARD</span>
            </div>
            
            <!-- Stripe Payment Element (Credit Card) -->
            <div id="payment-element" class="mb-3">
                <!-- Stripe injects the Payment Element here -->
            </div>
            
            <div id="payment-error-message" class="error-message" style="display: none;"></div>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" id="back-to-booking-btn">
                    ← Back
                </button>
                <button type="submit" class="btn btn-primary flex-fill" id="payment-submit-button">
                    <span id="payment-button-text">Pay Now</span>
                    <span id="payment-spinner" class="payment-spinner" style="display: none;"></span>
                </button>
            </div>
            
            <!-- Security Badges -->
            <div class="security-badges mt-4">
                <div class="secure-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>SSL Encrypted</span>
                </div>
                <div class="stripe-powered">
                    <span>Powered by</span>
                    <svg width="45" height="20" viewBox="0 0 60 26" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#635BFF" d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.93 0 1.85 6.29.97 6.29 5.88z"/>
                    </svg>
                </div>
                <div class="pci-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm3.97 11.03a.75.75 0 1 1-1.06 1.06L8 9.06l-2.97 2.97a.75.75 0 0 1-1.06-1.06l2.97-2.97-2.97-2.97a.75.75 0 0 1 1.06-1.06L8 6.94l2.97-2.97a.75.75 0 1 1 1.06 1.06L9.06 8l2.97 2.97z"/>
                        <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                    </svg>
                    <span>PCI DSS Compliant</span>
                </div>
            </div>
        </form>
    </section>
    
    <style>
        /* Payment Spinner */
        #payment-processing-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        
        .processing-modal {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stripe-logo-container {
            margin-bottom: 24px;
            display: flex;
            justify-content: center;
        }
        
        .processing-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f0f0f0;
            border-top: 4px solid #635BFF;
            border-radius: 50%;
            margin: 0 auto 24px;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .processing-modal h4 {
            color: #1a1a1a;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .processing-modal p {
            color: #666;
            font-size: 15px;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .processing-note {
            color: #999;
            font-size: 13px;
            font-style: italic;
        }
        
        /* Security Badges */
        .security-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .secure-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #28a745;
            font-size: 13px;
            font-weight: 500;
        }
        
        .secure-badge svg {
            width: 18px;
            height: 18px;
        }
        
        .stripe-powered {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 12px;
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
        }
        
        .stripe-powered span {
            color: #666;
            font-size: 12px;
        }
        
        .pci-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #666;
            font-size: 12px;
        }
        
        .pci-badge svg {
            color: #28a745;
        }
        
        /* Disable payment button */
        #payment-submit-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
    
    <script>
        // Stripe configuration
        const stripeKey = '<?php echo $stripe_publishable_key; ?>';
        const stripe = Stripe(stripeKey);

        let elements;
        let paymentRequest;
        let clientSecret;

        // Initialize payment when checkout form is shown
        function initializePayment() {
            const amount = parseFloat($("#checkout-total").text()) * 100; // Stripe takes amt in cents

            if (amount <= 0) {
                showPaymentError('Invalid amount');
                return;
            }

            // Create Payment Intent
            fetch('api/create_payment_intent.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    amount: amount,
                    currency: 'sgd'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                clientSecret = data.clientSecret;
                setupStripeElements(clientSecret, amount);
            })
            .catch(error => {
                console.error('Error:', error);
                showPaymentError('Failed to initialize payment. Please try again.');
            });
        }

        function setupStripeElements(clientSecret, amount) {
            // Create Payment Element
            const appearance = {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#A855F7',
                    colorBackground: '#ffffff',
                    colorText: '#1a1a1a',
                    borderRadius: '8px',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                }
            };

            elements = stripe.elements({ clientSecret, appearance });
            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');

            paymentElement.on('change', function(event) {
                const type = event.value.type;

                if (type === "card") {
                    enableBillingRequired(true);
                } else {
                    enableBillingRequired(false);
                }
            });

            // Setup Apple Pay / Google Pay
            setupPaymentRequest(amount);
        }

        function setupPaymentRequest(amount) {
            paymentRequest = stripe.paymentRequest({
                country: 'SG',
                currency: 'sgd',
                total: {
                    label: 'Escape Room Booking',
                    amount: amount,
                },
                requestPayerName: true,
                requestPayerEmail: true,
            });

            const prButton = elements.create('paymentRequestButton', {
                paymentRequest: paymentRequest,
            });

            // Check if Apple Pay / Google Pay is available (not avail in testing)
            paymentRequest.canMakePayment().then(function(result) {
                if (result) {
                    prButton.mount('#payment-request-button');
                    document.getElementById('payment-divider').style.display = 'block';
                }
            });

            paymentRequest.on('paymentmethod', async (ev) => {
                showProcessingOverlay();
                
                const billingDetails = getBillingDetails();

                const {error: confirmError} = await stripe.confirmCardPayment(
                    clientSecret,
                    {
                        payment_method: ev.paymentMethod.id,
                        payment_method_options: {
                            card: {
                                billing_details: billingDetails
                            }
                        }
                    },
                    {handleActions: false}
                );

                if (confirmError) {
                    ev.complete('fail');
                    hideProcessingOverlay();
                    showPaymentError(confirmError.message);
                } else {
                    ev.complete('success');
                    handlePaymentSuccess();
                }
            });
        }

        function enableBillingRequired(required) {
            const fields = [
                'billing-address',
                'billing-city',
                'billing-postal',
                'billing-country'
            ];
        
            fields.forEach(id => {
                const field = document.getElementById(id);
                if (required) {
                    field.setAttribute("required", "");
                } else {
                    field.removeAttribute("required");
                }
            });
        }

        // Handle form submission
        document.getElementById('payment-form').addEventListener('submit', async (event) => {
            event.preventDefault();

            // Validate billing address
            if (!validateBillingAddress()) {
                return;
            }

            setPaymentLoading(true);
            showProcessingOverlay();

            const billingDetails = getBillingDetails();

            const {error} = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    payment_method_data: {
                        billing_details: billingDetails
                    },
                    receipt_email: '<?php echo $user_email; ?>',
                },
                redirect: 'if_required'
            });

            if (error) {
                hideProcessingOverlay();
                showPaymentError(error.message);
                setPaymentLoading(false);
            } else {
                handlePaymentSuccess();
            }
        });

        function showProcessingOverlay() {
            document.getElementById('payment-processing-overlay').style.display = 'flex';
            // Prevent body scrolling
            document.body.style.overflow = 'hidden';
        }

        function hideProcessingOverlay() {
            document.getElementById('payment-processing-overlay').style.display = 'none';
            document.body.style.overflow = '';
        }

        function getBillingDetails() {
            return {
                name: '<?php echo addslashes($user_name); ?>',
                email: '<?php echo addslashes($user_email); ?>',
                address: {
                    line1: document.getElementById('billing-address').value,
                    city: document.getElementById('billing-city').value,
                    postal_code: document.getElementById('billing-postal').value,
                    country: document.getElementById('billing-country').value
                }
            };
        }

        function validateBillingAddress() {
            const isRequired = document.getElementById('billing-address').hasAttribute('required');

            if (!isRequired) {
                return true; // Skip validation for Apple Pay / Google Pay
            }

            const address = document.getElementById('billing-address').value.trim();
            const city = document.getElementById('billing-city').value.trim();
            const postal = document.getElementById('billing-postal').value.trim();

            if (!address || !city || !postal) {
                showPaymentError('Please fill in all billing address fields');
                return false;
            }
            return true;
        }

        function handlePaymentSuccess() {
            // Keep overlay visible during success handling
            const bookingData = {
                date: $("#checkout-date").text(),
                time: $("#checkout-time").text(),
                room: $("#checkout-room").text(),
                players: $("#checkout-players").text(),
                total: $("#checkout-total").text(),
            };

            // Show success message in overlay
            const modal = document.querySelector('.processing-modal');
            modal.innerHTML = `
                <div style="color: #28a745; margin-bottom: 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                </div>
                <h4 style="color: #28a745;">Payment Successful!</h4>
                <p>Your booking has been confirmed.</p>
                <p class="processing-note">Redirecting to confirmation page...</p>
            `;

            // Redirect after a short delay
            setTimeout(() => {
                window.location.href = 'inc/booking_success.php';
                //hideProcessingOverlay(); // Remove this line in production
            }, 2000);

        }

        function setPaymentLoading(isLoading) {
            const button = document.getElementById('payment-submit-button');
            const buttonText = document.getElementById('payment-button-text');
            const spinner = document.getElementById('payment-spinner');

            if (isLoading) {
                button.disabled = true;
                buttonText.style.display = 'none';
                spinner.style.display = 'inline-block';
            } else {
                button.disabled = false;
                buttonText.style.display = 'inline';
                spinner.style.display = 'none';
            }
        }

        function showPaymentError(message) {
            const errorDiv = document.getElementById('payment-error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';

            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        // Back button
        document.getElementById("back-to-booking-btn").addEventListener("click", function() {
            $(".checkout-form").hide(250);
            $(".booking-form").show(250);
            $(".timeslots-container").show(250);
        });
              
    </script>
</section>