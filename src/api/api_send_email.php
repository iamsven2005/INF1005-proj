<?php 
function send_email($message, $html_message, $user_email){
    $API_KEY = getenv("SENDGRIDKEY");


    // Send with SendGrid
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("noreply@escapify.net", "Escapify");
    $email->setReplyTo("support@escapify.net", "Escapify Support");
    $email->setSubject("Booking Confirmation - Escapify");
    $email->addTo($user_email);
    $email->addContent("text/plain", $message);
    $email->addContent("text/html", $html_message);

    $sendgrid = new \SendGrid(trim($API_KEY));

    try {
        $response = $sendgrid->send($email);

        if ($response->statusCode() == 202) {
            // Email sent successfully
            //echo "Confirmation email sent!";
        } else {
            // Log the error
            error_log("SendGrid error: " . $response->statusCode() . " - " . $response->body());
        }
    } catch (Exception $e) {
        error_log("SendGrid exception: " . $e->getMessage());
    }
}
?>