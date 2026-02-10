<?php
session_start();
require_once __DIR__ . "/inc/functions.php";
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/api/api_send_email.php";

list($db_host, $db_user, $db_pass, $db_name, $stripekey) = getDBEnvVar();

\Stripe\Stripe::setApiKey($stripekey);

$token = $_GET['token'] ?? null;
$message = '';
$error = '';
$booking = null;
$refund_amt = 0;

if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    $error = "Invalid cancellation link.";
} else {
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed");
        }

        // Handle cancellation submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
            
            // Get booking and verify it can be cancelled
            $stmt = $conn->prepare("
                SELECT b.bookingRef, b.bookingDate, b.bookingTimeslot, b.bookingStatus,
                       b.stripe_payment_id, b.cancel_token, b.totalPrice,
                       r.roomName,
                       u.userID, u.email, u.username
                FROM Bookings b
                JOIN Rooms r ON b.Rooms_roomID = r.roomID
                JOIN Users u ON b.Users_userID = u.userID
                WHERE b.cancel_token = ? 
                AND b.bookingStatus = 'Confirmed'
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
            $stmt->close();

            if (!$booking) {
                $error = "This booking cannot be cancelled or has already been cancelled.";
            } else {
                $username = $booking['username'];
                // Check if booking time has passed
                $booking_datetime = $booking['bookingDate'] . ' ' . $booking['bookingTimeslot'];
                $booking_timestamp = strtotime($booking_datetime);
                
                if ($booking_timestamp <= time()) {
                    $error = "Cannot cancel past or ongoing bookings.";
                } else {
                    // Calculate refund based on notice period
                    $hours_until_booking = ($booking_timestamp - time()) / 3600;
                    
                    if ($hours_until_booking >= 24) {
                        $refund_percentage = 100;
                        $refund_amt = $booking['totalPrice'];
                    } else {
                        $refund_percentage = 0;
                        $refund_amt = 0;
                    }

                    $conn->begin_transaction();

                    try {
                        $refund_id = null;
                        
                        // Only process Stripe refund if refund amount > 0
                        if ($refund_amt > 0 && !empty($booking['stripe_payment_id'])) {
                            $refund = \Stripe\Refund::create([
                                'payment_intent' => $booking['stripe_payment_id'],
                                'amount' => (int)($refund_amt * 100), // Convert to cents
                            ]);
                            $refund_id = $refund->id;
                        }

                        // Update booking status (regardless of refund)
                        $update = $conn->prepare("
                            UPDATE Bookings 
                            SET bookingStatus = 'Cancelled', 
                                cancel_token = NULL,
                                refund_id = ?,
                                refund_amt = ?,
                                cancelled_at = NOW()
                            WHERE cancel_token = ?
                        ");
                        $update->bind_param("sds", $refund_id, $refund_amt, $token);
                        
                        if (!$update->execute()) {
                            throw new Exception("Failed to update booking status");
                        }
                        $update->close();

                        $conn->commit();

                        // Format date and time
                        $formatted_time = formatTime($booking['bookingTimeslot']);
                        $formatted_date = date('l, F j, Y', strtotime($booking['bookingDate']));

                        // Prepare refund text for plain text and HTML
                        if ($refund_amt > 0) {
                            $refund_text = "Refund Amount: $" . number_format($refund_amt, 2) . " (Full refund)\n"
                                         . "Your refund will be processed within 5-10 business days and credited to your original payment method.\n";
                            $refund_text_html = "<p><strong>Refund Amount:</strong> $" . number_format($refund_amt, 2) . " (Full refund)</p>
                                                 <p>Your refund will be processed within 5-10 business days and credited to your original payment method.</p>";
                        } else {
                            $refund_text = "As this cancellation was made less than 24 hours before the booking, no refund will be issued per our cancellation policy.\n";
                            $refund_text_html = "<p>As this cancellation was made less than 24 hours before the booking, no refund will be issued per our cancellation policy.</p>";
                        }

                        // ---------------------------
                        // Plain text message
                        // ---------------------------
                        $message = "Booking Cancelled\n\n";
                        $message .= "Hi {$username},\n\n";
                        $message .= "Your booking has been successfully cancelled. Here are the details:\n\n";
                        $message .= "Room: {$booking['roomName']}\n";
                        $message .= "Date: {$formatted_date}\n";
                        $message .= "Time: {$formatted_time}\n\n";
                        $message .= $refund_text . "\n";
                        $message .= "If you have any questions, please don't hesitate to contact us.\n";
                        $message .= "We hope to see you again soon!\n\n";
                        $message .= "This is an automated message. Please do not reply to this email.";

                        // ---------------------------
                        // HTML message
                        // ---------------------------
                        $html_message = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
                                .content { padding: 20px; background-color: #f9f9f9; }
                                .booking-details { background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Booking Cancelled</h1>
                                </div>
                                <div class='content'>
                                    <p>Hi {$username},</p>
                                    <p>Your booking has been successfully cancelled. Here are the details:</p>
                                    <div class='booking-details'>
                                        <p><strong>Room:</strong> {$booking['roomName']}</p>
                                        <p><strong>Date:</strong> {$formatted_date}</p>
                                        <p><strong>Time:</strong> {$formatted_time}</p>
                                    </div>
                                    {$refund_text_html}
                                    <p>If you have any questions, please don't hesitate to contact us.</p>
                                    <p>We hope to see you again soon!</p>
                                </div>
                                <div class='footer'>
                                    <p>This is an automated message. Please do not reply to this email.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                        ";

                        // Send cancellation confirmation email
                        send_email($message, $html_message, $booking['email']);

                        // Set success message
                        $message = "Your booking has been successfully cancelled.";
                        if ($refund_amt > 0) {
                            $message .= " A refund of $" . number_format($refund_amt, 2) . " will be processed within 5-10 business days.";
                        } else {
                            $message .= " As per our cancellation policy, no refund will be issued for cancellations within 24 hours of the booking time.";
                        }

                    } catch (\Stripe\Exception\ApiErrorException $e) {
                        $conn->rollback();
                        $error = "Failed to process refund. Please contact support.";
                        error_log("Stripe refund error: " . $e->getMessage());
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error = "Failed to cancel booking. Please try again.";
                        error_log("Cancellation error: " . $e->getMessage());
                    }
                }
            }
        } else {
            // Display booking details for confirmation
            $stmt = $conn->prepare("
                SELECT b.bookingRef, b.bookingDate, b.bookingTimeslot, b.bookingStatus, b.totalPrice,
                       r.roomName
                FROM Bookings b
                JOIN Rooms r ON b.Rooms_roomID = r.roomID
                WHERE b.cancel_token = ?
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
                throw new Exception("Failed to fetch booking details");
            }
            
            $booking = $result->fetch_assoc();
            $stmt->close();

            if (!$booking) {
                $error = "Booking not found or cancellation link has expired.";
            } elseif ($booking['bookingStatus'] !== 'Confirmed') {
                $error = "This booking has already been " . strtolower($booking['bookingStatus']) . ".";
                $booking = null;
            } else {
                date_default_timezone_set('Asia/Singapore');
                // Calculate potential refund for display
                $booking_datetime = $booking['bookingDate'] . ' ' . $booking['bookingTimeslot'];
                $booking_timestamp = new DateTime($booking_datetime);
                $now = new DateTime();
                $interval = $booking_timestamp->diff($now);
                $hours_until_booking = $interval->days * 24 + $interval->h + ($interval->i / 60) + ($interval->s / 3600); 

                if ($hours_until_booking >= 24) {
                    $booking['refund_percentage'] = 100;
                } else {
                    $booking['refund_percentage'] = 0;
                }
                $booking['refund_amt'] = ($booking['totalPrice'] * $booking['refund_percentage']) / 100;
            }
        }

        $conn->close();

    } catch (Exception $e) {
        $error = "An error occurred. Please try again later.";
        error_log("Cancel booking error: " . $e->getMessage());
    }
}

// Format time for display
function formatTime($time) {
    $dt = DateTime::createFromFormat('H:i:s', $time);
    return $dt ? $dt->format('g:i A') : $time;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cancel Booking</title>    
    <?php include "inc/head.inc.php"?>
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/cancel_booking.css">
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="cancel-card card shadow">
            <div class="card-body p-4">
                
                <?php if ($message): ?>
                    <div class="text-center">
                        <div class="text-success mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        </div>
                        <h4 class="text-success">Booking Cancelled</h4>
                        <p class="text-muted"><?= htmlspecialchars($message) ?></p>
                        <a href="/" class="btn btn-primary mt-3">Return to Home</a>
                    </div>

                <?php elseif ($error): ?>
                    <div class="text-center">
                        <div class="text-danger mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                            </svg>
                        </div>
                        <h4 class="text-danger">Unable to Cancel</h4>
                        <p class="text-muted"><?= htmlspecialchars($error) ?></p>
                        <a href="/" class="btn btn-primary mt-3">Return to Home</a>
                    </div>

                <?php elseif ($booking): ?>
                    <h4 class="card-title text-center mb-4">Cancel Booking</h4>
                    
                    <div class="alert alert-warning">
                        <strong>Are you sure you want to cancel this booking?</strong>
                    </div>

                    <div class="mb-3">
                        <p class="mb-2"><strong>Booking Reference:</strong> <?= htmlspecialchars($booking['bookingRef']) ?></p>
                        <p class="mb-2"><strong>Room:</strong> <?= htmlspecialchars($booking['roomName']) ?></p>
                        <p class="mb-2"><strong>Date:</strong> <?= htmlspecialchars($booking['bookingDate']) ?></p>
                        <p class="mb-2"><strong>Time:</strong> <?= formatTime($booking['bookingTimeslot']) ?></p>
                        <p class="mb-2"><strong>Amount Paid:</strong> $<?= number_format($booking['totalPrice'], 2) ?></p>
                    </div>

                    <?php if ($booking['refund_percentage'] > 0): ?>
                        <div class="refund-info mb-4">
                            <strong>Refund: $<?= number_format($booking['refund_amt'], 2) ?></strong>
                            <small class="d-block">(Full refund - cancelling 24+ hours before booking)</small>
                        </div>
                    <?php else: ?>
                        <div class="refund-info no-refund mb-4">
                            <strong>No refund available</strong>
                            <small class="d-block">Cancellations within 24 hours are non-refundable, but you can still cancel to free up the slot.</small>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="d-grid gap-2">
                            <button type="submit" name="confirm_cancel" value="1" class="btn btn-danger">
                                Yes, Cancel My Booking
                            </button>
                            <a href="/" class="btn btn-secondary">No, Keep My Booking</a>
                        </div>
                    </form>

                    <div class="mt-4">
                        <small class="text-muted">
                            <strong>Cancellation Policy:</strong><br>
                            • 24+ hours before: Full refund<br>
                            • Less than 24 hours: No refund (can still cancel)
                        </small>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>