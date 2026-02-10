<?php
	require_once __DIR__ . "/inc/secure_session_start.php";
	require_once __DIR__ . "/login_functions.php";

	// Redirect if not logged in
	if (!isset($_SESSION['user_id'])) {
		header("Location: login.php");
		exit();
	}

	$user_id = $_SESSION['user_id'];
	$errorMsg = "";
	$successMsg = "";
	
	// Fetch current user data
	$userData = getUserData($user_id);
	
	if (!$userData) {
		$errorMsg = "Unable to load user data.";
	}

	// Handle form submission
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$updateSuccess = true;
		
		// Update Username
		if (isset($_POST['update_username'])) {
			$new_username = sanitize_input($_POST['username']);
			
			$usernameValidation = validateUsername($new_username);
			if (!$usernameValidation['valid']) {
				$errorMsg .= implode("<br>", $usernameValidation['errors']) . "<br>";
				$updateSuccess = false;
			}
			elseif ($new_username !== $userData['username'] && usernameExists($new_username)) {
				$errorMsg .= "Username already exists. Please choose another.<br>";
				$updateSuccess = false;
			}
			
			if ($updateSuccess) {
				$result = updateUsername($user_id, $new_username);
				if ($result['success']) {
					$successMsg = "Username updated successfully!";
					$_SESSION['username'] = $new_username;
					$userData['username'] = $new_username;
				} else {
					$errorMsg .= $result['message'];
				}
			}
		}
		
		// Update Email
		if (isset($_POST['update_email'])) {
			$new_email = sanitize_input($_POST['email']);
			
			if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
				$errorMsg .= "Invalid email format.<br>";
				$updateSuccess = false;
			}
			elseif ($new_email !== $userData['email'] && emailExists($new_email)) {
				$errorMsg .= "Email already registered.<br>";
				$updateSuccess = false;
			}
			
			if ($updateSuccess) {
				$result = updateEmail($user_id, $new_email);
				if ($result['success']) {
					$successMsg = "Email updated successfully!";
					$_SESSION['email'] = $new_email;
					$userData['email'] = $new_email;
				} else {
					$errorMsg .= $result['message'];
				}
			}
		}
		
		// Update Password
		if (isset($_POST['update_password'])) {
			$current_password = $_POST['current_password'];
			$new_password = $_POST['new_password'];
			$confirm_password = $_POST['confirm_password'];
			
			// Verify current password
			if (!verifyCurrentPassword($user_id, $current_password)) {
				$errorMsg .= "Current password is incorrect.<br>";
				$updateSuccess = false;
			}
			
			// Validate new password
			$passwordValidation = validatePasswordStrength($new_password);
			if (!$passwordValidation['valid']) {
				$errorMsg .= implode("<br>", $passwordValidation['errors']) . "<br>";
				$updateSuccess = false;
			}
			
			// Check password confirmation
			if ($new_password !== $confirm_password) {
				$errorMsg .= "New passwords do not match.<br>";
				$updateSuccess = false;
			}
			
			if ($updateSuccess) {
				$password_hash = password_hash($new_password, PASSWORD_DEFAULT);
				$result = updatePassword($user_id, $password_hash);
				if ($result['success']) {
					$successMsg = "Password updated successfully!";
				} else {
					$errorMsg .= $result['message'];
				}
			}
		}
		
		// Delete Account
		if (isset($_POST['delete_account'])) {
			$confirm_password = $_POST['delete_password'];
			
			if (!verifyCurrentPassword($user_id, $confirm_password)) {
				$errorMsg .= "Password is incorrect. Account deletion cancelled.<br>";
			} else {
				$result = deleteUserAccount($user_id);
				if ($result['success']) {
					session_destroy();
					header("Location: index.php?account_deleted=1");
					exit();
				} else {
					$errorMsg .= $result['message'];
				}
			}
		}
	}
?>

<!doctype html>
<html lang="en">
<link rel="icon" type="image/x-icon" href="../images/home.ico">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Account management" />

    <title>Your account details</title>
    <?php include "inc/head.inc.php"; ?>
	
	<style>
		.account-section {
			background: #f8f9fa;
			border-radius: 8px;
			padding: 20px;
			margin-bottom: 20px;
            color: #000;
		}
		.account-section h3 {
			margin-bottom: 15px;
			color: #333;
		}
		.info-display {
			background: white;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 15px;
            color: #000;
		}
		.danger-zone {
			background: #fff5f5;
			border: 2px solid #dc3545;
            color: #000;
		}
	</style>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>
	
	<main class="container my-5">
		<h1 class="mb-4">Account Management</h1>
		
		<?php if (!empty($successMsg)): ?>
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			<?= $successMsg ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
		<?php endif; ?>
		
		<?php if (!empty($errorMsg)): ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<?= $errorMsg ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
		<?php endif; ?>
		
		<?php if ($userData): ?>
		
		<!-- Username Section -->
		<div class="account-section">
			<h3>Username</h3>
			<div class="info-display">
				<strong>Current Username:</strong> <?= htmlspecialchars($userData['username']) ?>
			</div>
			<button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#usernameForm">
				Change Username
			</button>
			<div class="collapse mt-3" id="usernameForm">
				<form method="POST" class="card card-body">
					<div class="mb-3">
						<label for="username" class="form-label">New Username</label>
						<input type="text" class="form-control" id="username" name="username" 
							   value="<?= htmlspecialchars($userData['username']) ?>" required>
						<div class="form-text">3-30 characters, letters, numbers, underscores, and hyphens only</div>
					</div>
					<button type="submit" name="update_username" class="btn btn-success">Update Username</button>
				</form>
			</div>
		</div>
		
		<!-- Email Section -->
		<div class="account-section">
			<h3>Email Address</h3>
			<div class="info-display">
				<strong>Current Email:</strong> <?= htmlspecialchars($userData['email']) ?>
			</div>
			<button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#emailForm">
				Change Email
			</button>
			<div class="collapse mt-3" id="emailForm">
				<form method="POST" class="card card-body">
					<div class="mb-3">
						<label for="email" class="form-label">New Email Address</label>
						<input type="email" class="form-control" id="email" name="email" 
							   value="<?= htmlspecialchars($userData['email']) ?>" required>
					</div>
					<button type="submit" name="update_email" class="btn btn-success">Update Email</button>
				</form>
			</div>
		</div>
		
		<!-- Password Section -->
		<div class="account-section">
			<h3>Password</h3>
			<div class="info-display">
				<strong>Password:</strong> ••••••••
			</div>
			<button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#passwordForm">
				Change Password
			</button>
			<div class="collapse mt-3" id="passwordForm">
				<form method="POST" class="card card-body">
					<div class="mb-3">
						<label for="current_password" class="form-label">Current Password</label>
						<input type="password" class="form-control" id="current_password" 
							   name="current_password" required>
					</div>
					<div class="mb-3">
						<label for="new_password" class="form-label">New Password</label>
						<input type="password" class="form-control" id="new_password" 
							   name="new_password" required>
						<div class="form-text">
							Must be 8+ characters with uppercase, lowercase, number, and special character
						</div>
					</div>
					<div class="mb-3">
						<label for="confirm_password" class="form-label">Confirm New Password</label>
						<input type="password" class="form-control" id="confirm_password" 
							   name="confirm_password" required>
					</div>
					<button type="submit" name="update_password" class="btn btn-success">Update Password</button>
				</form>
			</div>
		</div>
		
		<!-- Account Creation Date -->
		<div class="account-section">
			<h3>Account Information</h3>
			<div class="info-display">
				<strong>Member Since:</strong> <?= date('F j, Y', strtotime($userData['created_at'])) ?>
			</div>
		</div>
		
		<!-- Danger Zone - Delete Account -->
		<div class="account-section danger-zone">
			<h3 class="text-danger">Danger Zone</h3>
			<p><strong>Delete Account:</strong> This action cannot be undone. All your data will be permanently deleted.</p>
			<button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
				Delete Account
			</button>
		</div>
		
		<?php endif; ?>
	</main>

	<!-- Delete Account Confirmation Modal -->
	<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header bg-danger text-white">
					<h5 class="modal-title" id="deleteModalLabel">Confirm Account Deletion</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
				</div>
				<form method="POST">
					<div class="modal-body">
						<p class="text-danger"><strong>Warning:</strong> This will permanently delete your account and all associated data.</p>
						<div class="mb-3">
							<label for="delete_password" style="color: #000;" class="form-label">Enter your password to confirm:</label>
							<input type="password" class="form-control" id="delete_password" 
								   name="delete_password" required>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" name="delete_account" class="btn btn-danger">Delete My Account</button>
					</div>
				</form>
			</div>
		</div>
	</div>

    <?php include "inc/footer.inc.php"; ?>
</body>

</html>