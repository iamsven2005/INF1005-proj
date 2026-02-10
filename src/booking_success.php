<?php
session_start();

// Get booking details from session
$booking = $_SESSION['bookingSuccess'] ?? false;

if (!$booking) {
    // Redirect if no booking found
    header('Location: ../index.php');
    exit();
}

$username = $_SESSION['username'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$ref = $_GET['booking_ref'] ?? '';
$date = $_SESSION['date'] ?? '';
$time = $_SESSION['time'] ?? '';
$username = $_SESSION['username'] ?? '';
$room_name = $_SESSION['room_name'] ?? '';
$pax = $_SESSION['pax'] ?? '';
$total = $_SESSION['total'] ?? '';

// Clear the booking data
unset($_SESSION['bookingSuccess']);
unset($_SESSION['bookingRef']);
unset($_SESSION['date']);
unset($_SESSION['time']);
unset($_SESSION['room_name']);
unset($_SESSION['pax']);
unset($_SESSION['total']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    
    <?php include "inc/head.inc.php" ?>
    <link rel="stylesheet" href="css/booking_success.css">
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h1>Payment Successful!</h1>
        <p class="subtitle">Your booking has been confirmed</p>
        
        <div class="booking-id">
            Booking Reference: <strong>#<?php echo $ref; ?></strong>
        </div>
        
        <div class="booking-details">
            <div class="detail-row">
                <span class="detail-label">Room:</span>
                <span class="detail-value"><?php echo $room_name; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value"><?php echo $date; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Time:</span>
                <span class="detail-value"><?php echo $time; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Players:</span>
                <span class="detail-value"><?php echo $pax; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Paid:</span>
                <span class="detail-value">$<?php echo number_format($total, 2); ?></span>
            </div>
        </div>
        
        <!--<button class="btn btn-primary">Back to Home</button> -->
        
        <p class="email-notice">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            A confirmation email has been sent to<br>
            <strong><?php echo htmlspecialchars($email, ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></strong> <!-- redact this-->
        </p>
    </div>
</body>
</html>