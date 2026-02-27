<?php
	require_once __DIR__ . "/inc/secure_session_start.php";
	require_once __DIR__ . "/login_functions.php";
	
	$errorMsg = "";
	$success = true;
	
	// Validate CSRF token
	$csrf_token = $_POST['csrf_token'] ?? '';
	if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
		$errorMsg .= "Security token validation failed. Please try again.<br>";
		$success = false;
	}
	
	if ($success) {
		// Validate email
		if (empty($_POST["email"])) {
			$errorMsg .= "Email is required.<br>";
			$success = false;
		}
		else {
			$email = sanitize_input($_POST["email"]);

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errorMsg .= "Invalid Email format.<br>";
				$success = false;
			}
		}

		// Validate password
		if (empty($_POST["password"])) {
			$errorMsg .= "Password is required.<br>";
			$success = false;
		}
		else {
			$password = $_POST["password"];
		}

		if ($success) {
			// Check rate limiting before attempting authentication
			$attemptCheck = checkLoginAttempts($email);
			
			if (!$attemptCheck['allowed']) {
				$errorMsg .= $attemptCheck['message'];
				$success = false;
			}
			else {
				$user = authenticateUser($email, $password);
				
				if ($user === false) {
					// Record failed login attempt
					recordFailedLogin($email);
					
					// Generic error message to prevent user enumeration
					$errorMsg .= "Incorrect email or password.<br>";
					$success = false;
				}
				else {
					// Clear failed login attempts on successful login
					clearLoginAttempts($email);
					
					// Login successful: start session
					$_SESSION['logged_in'] = true;
					$_SESSION["user_id"] = $user["userID"];
					$_SESSION['email'] = $user['email'];
					$_SESSION["username"] = $user["username"];
					$_SESSION["is_admin"] = $user["is_admin"];
					// Regenerate session ID to prevent session fixation attacks
					session_regenerate_id(true);

					// Redirect to homepage or account page
					header("Location: index.php");
					exit;
				}
			}
		}
	}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login Results</title>
    <?php include "inc/head.inc.php"; ?>
</head>
	
<body>
	<?php include "inc/nav.inc.php"; ?>

	<?php if (!$success): ?>
	<div class="container justify-content-center mb-3">
		<h2>Oops!</h2>
		<h4>The following errors were detected:</h4>
		<p><?= $errorMsg ?></p>
		<a href="login.php" class="btn btn-warning">Return to Login</a>
	</div>
	<?php endif; ?>

	<?php include "inc/footer.inc.php"; ?>
</body>
	
</html>