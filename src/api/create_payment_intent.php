<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: ../index.php");
    die("Not allowed");
}

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../inc/functions.php";

list($db_host, $db_user, $db_pass, $db_name, $stripekey) = getDBEnvVar();



try {
    // Get the JSON payload
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $amount = $data['amount'] ?? 0;
    $currency = $data['currency'] ?? 'sgd';
    
    // Validate amount
    if ($amount <= 0) {
        throw new Exception('Invalid amount');
    }
    
    // Initialize Stripe
    \Stripe\Stripe::setApiKey($stripekey);
    
    // Create a PaymentIntent
    // This reserves the payment but doesn't charge yet
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount, // Amount in cents
        'currency' => $currency,
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
        // Store metadata for reference (optional)
        'metadata' => [
            'integration_check' => 'accept_a_payment',
        ],
    ]);
    
    $_SESSION['stripe_payment_intent_id'] = $paymentIntent->id;

    // Return the client secret
    // The frontend will use this to complete the payment
    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret,
        'paymentIntentId' => $paymentIntent->id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>