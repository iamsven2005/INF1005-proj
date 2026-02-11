<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . "/login_functions.php";
require_once __DIR__ . "/inc/secure_session_start.php";
require_once __DIR__ . "/api/api_send_email.php";

$username = "";
$email = "";
$password = "";
$errorMsg = "";
$successMsg = "";
$success = true;

// ---------------------------
// Username
// ---------------------------
if (empty($_POST["username"])) {
    $errorMsg .= "Username is required.<br>";
    $success = false;
} else {
    $username = sanitize_input($_POST['username']);
    
    // Validate username format
    $usernameValidation = validateUsername($username);
    if (!$usernameValidation['valid']) {
        $errorMsg .= implode("<br>", $usernameValidation['errors']) . "<br>";
        $success = false;
    }
    
    // Check if username already exists
    if ($success && usernameExists($username)) {
        $errorMsg .= "Username already exists. Please choose another.<br>";
        $success = false;
    }
}

// ---------------------------
// Email
// ---------------------------
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
    
    // Check if email already exists
    if ($success && emailExists($email)) {
        $errorMsg .= "Email already registered. Please <a href='login.php'>login</a> or use a different email.<br>";
        $success = false;
    }
}

// ---------------------------
// Password
// ---------------------------
if (empty($_POST["password"])) {
    $errorMsg .= "Password is required.<br>";
    $success = false;
} else {
    $password = $_POST["password"];
    
    // Validate password strength
    $passwordValidation = validatePasswordStrength($password);
    if (!$passwordValidation['valid']) {
        $errorMsg .= implode("<br>", $passwordValidation['errors']) . "<br>";
        $success = false;
    }
}

if (empty($_POST["password_confirm"])) {
    $errorMsg .= "Please confirm your password.<br>";
    $success = false;
} else {
    $password_confirm = $_POST["password_confirm"];
    if ($password !== $password_confirm) {
        $errorMsg .= "Passwords do not match.<br>";
        $success = false;
    }
}

// ---------------------------
// If validation passed
// ---------------------------
if ($success) {

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Save user
    $result = saveUserToDB($username, $email, $password_hash);

    if ($result['success']) {

        // Email Artifact
        $message = 'Registration Successful.\n\n';
        $message = 'Hello ' . $username . ',\n';
        $message .= "Your account has successfully been created with Escapy\n";
        $message .= 'We hope you enjoy your time here!';

        $html_message = '
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Registration Successful</title>
                <style>
                    body, html {
                        margin: 0;
                        padding: 0;
                        font-family: Arial, sans-serif;
                        background-color: #f7f7f7;
                        color: #333;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        background-color: #ffffff;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                    }
                    .header {
                        background-color: #ff4444; /* brand color */
                        color: #fff;
                        text-align: center;
                        padding: 20px;
                    }
                    .header h1 {
                        margin: 0;
                        font-size: 24px;
                    }
                    .content {
                        padding: 30px 20px;
                    }
                    .content h2 {
                        color: #ff4444;
                        font-size: 20px;
                    }
                    .content p {
                        font-size: 16px;
                        line-height: 1.5;
                    }
                    .footer {
                        font-size: 12px;
                        color: #777;
                        text-align: center;
                        padding: 15px;
                    }
                    @media only screen and (max-width: 600px) {
                        .container {
                            width: 95% !important;
                        }
                        .content {
                            padding: 20px 15px;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Welcome to Escapy!</h1>
                    </div>
                    <div class="content">
                        <h2>Hello ' . $username . ',</h2>
                        <p>Your account has been successfully created on <strong>Escapy</strong>.</p>
                        <p>You can now log in and start using our services.</p>
                        <p>Thank you for registering!</p>
                    </div>
                </div>
            </body>
            </html>';

        send_email($message, $html_message, $email);
        
        $successMsg = "Registration successful! A confirmation email has been sent to $email.";
        $success = true;

    } else {
        $errorMsg = $result['message'];
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Registration Results</title>
    <?php include "inc/head.inc.php"; ?>
</head>
	
<body>
	<?php include "inc/nav.inc.php"; ?>

	<?php if ($success && !empty($successMsg)): ?>
	<div class="container justify-content-center mb-3">
		<h2>Success!</h2>
		<h4>Your account has been created.</h4>
		<p><?= $successMsg ?></p>
		<a href="login.php" class="btn btn-success">Go to Login</a>
	</div>
	<?php elseif (!$success): ?>
	<div class="container justify-content-center mb-3">
		<h2>Oops!</h2>
		<h4>The following input errors were detected:</h4>
		<p><?= $errorMsg ?></p>
		<a href="register.php" class="btn btn-danger">Return to Sign Up</a>
	</div>
	<?php endif; ?>

	<?php include "inc/footer.inc.php"; ?>
</body>
	
</html>