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
	
	$conn = getDbConnection();

	// Upcoming (Confirmed only)
	$sqlUpcoming = "SELECT 
		b.bookingID, b.bookingRef, b.bookingDate, b.bookingTimeslot,
		b.numPlayers, b.totalPrice, b.bookingStatus, b.created_at,
		r.roomName, r.roomLocation
	FROM Bookings b
	JOIN Rooms r ON b.Rooms_roomID = r.roomID
	WHERE b.Users_userID = ?
	AND b.bookingStatus = 'Confirmed'
	AND TIMESTAMP(b.bookingDate, b.bookingTimeslot) >= NOW()
	ORDER BY b.bookingDate ASC, b.bookingTimeslot ASC";

	// Past (includes Confirmed + Cancelled, you can change if you want)
	$sqlPast = "SELECT 
		b.bookingID, b.bookingRef, b.bookingDate, b.bookingTimeslot,
		b.numPlayers, b.totalPrice, b.bookingStatus, b.created_at,
		r.roomName, r.roomLocation
	FROM Bookings b
	JOIN Rooms r ON b.Rooms_roomID = r.roomID
	WHERE b.Users_userID = ?
	AND TIMESTAMP(b.bookingDate, b.bookingTimeslot) < NOW()
	ORDER BY b.bookingDate DESC, b.bookingTimeslot DESC";

	$upcomingBookings = [];
	$pastBookings = [];

	// prepared statements (safer)
	$stmt = $conn->prepare($sqlUpcoming);
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$res = $stmt->get_result();
	if ($res) $upcomingBookings = $res->fetch_all(MYSQLI_ASSOC);
	$stmt->close();

	$stmt = $conn->prepare($sqlPast);
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$res = $stmt->get_result();
	if ($res) $pastBookings = $res->fetch_all(MYSQLI_ASSOC);
	$stmt->close();

	$conn->close();



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
	
<main class="container py-5">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h1 class="h3 mb-1">Account settings</h1>
      <p class="text-muted mb-0">Manage your profile details and security preferences.</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Back to home
    </a>
  </div>

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
    <div class="row g-4">
      <!-- Left column: Profile -->
      <div class="col-lg-7">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <h2 class="h5 mb-1">Profile</h2>
                <p class="text-muted mb-0">Update your username and email address.</p>
              </div>
              <span class="badge bg-light text-dark border">
                Member since <?= htmlspecialchars(date('M Y', strtotime($userData['created_at']))) ?>
              </span>
            </div>

            <hr class="my-4">

            <!-- Username -->
            <div class="mb-4">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted small">Username</div>
                  <div class="fw-semibold"><?= htmlspecialchars($userData['username']) ?></div>
                </div>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#usernameForm">
                  Edit
                </button>
              </div>

              <div class="collapse mt-3" id="usernameForm">
                <form method="POST" class="border rounded-3 p-3 bg-light">
                  <div class="mb-2">
                    <label for="username" class="form-label small mb-1">New username</label>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?= htmlspecialchars($userData['username']) ?>" required>
                    <div class="form-text">3–30 characters. Letters, numbers, underscores, hyphens.</div>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" name="update_username" class="btn btn-success">
                      Save changes
                    </button>
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-toggle="collapse" data-bs-target="#usernameForm">
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Email -->
            <div class="mb-0">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted small">Email</div>
                  <div class="fw-semibold"><?= htmlspecialchars($userData['email']) ?></div>
                </div>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#emailForm">
                  Edit
                </button>
              </div>

              <div class="collapse mt-3" id="emailForm">
                <form method="POST" class="border rounded-3 p-3 bg-light">
                  <div class="mb-2">
                    <label for="email" class="form-label small mb-1">New email address</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($userData['email']) ?>" required>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" name="update_email" class="btn btn-success">
                      Save changes
                    </button>
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-toggle="collapse" data-bs-target="#emailForm">
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            </div>

          </div>
        </div>

        <!-- Account Info -->
        <div class="card shadow-sm border-0 mt-4">
          <div class="card-body p-4">
            <h2 class="h5 mb-1">Account information</h2>
            <p class="text-muted mb-3">Basic information about your account.</p>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 border rounded-3">
                  <div class="text-muted small">User ID</div>
                  <div class="fw-semibold"><?= htmlspecialchars((string)$user_id) ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 border rounded-3">
                  <div class="text-muted small">Created</div>
                  <div class="fw-semibold"><?= htmlspecialchars(date('F j, Y', strtotime($userData['created_at']))) ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right column: Security + Danger -->
      <div class="col-lg-5">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <h2 class="h5 mb-1">Security</h2>
            <p class="text-muted mb-0">Change your password regularly to keep your account safe.</p>

            <hr class="my-4">

            <button class="btn btn-primary w-100" data-bs-toggle="collapse" data-bs-target="#passwordForm">
              Change password
            </button>

            <div class="collapse mt-3" id="passwordForm">
              <form method="POST" class="border rounded-3 p-3 bg-light">
                <div class="mb-2">
                  <label for="current_password" class="form-label small mb-1">Current password</label>
                  <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-2">
                  <label for="new_password" class="form-label small mb-1">New password</label>
                  <input type="password" class="form-control" id="new_password" name="new_password" required>
                  <div class="form-text">8+ chars with upper, lower, number, special.</div>
                </div>
                <div class="mb-3">
                  <label for="confirm_password" class="form-label small mb-1">Confirm new password</label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="d-flex gap-2">
                  <button type="submit" name="update_password" class="btn btn-success">
                    Save
                  </button>
                  <button type="button" class="btn btn-outline-secondary"
                          data-bs-toggle="collapse" data-bs-target="#passwordForm">
                    Cancel
                  </button>
                </div>
              </form>
            </div>

          </div>
        </div>

        <div class="card shadow-sm border-0 mt-4 border-danger-subtle">
          <div class="card-body p-4">
            <h2 class="h5 text-danger mb-1">Danger zone</h2>
            <p class="text-muted mb-3">Delete your account and permanently remove your data. This cannot be undone.</p>

            <button class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
              Delete account
            </button>
          </div>
        </div>
      </div>
    </div>

<!-- My Bookings -->
<div class="card shadow-sm border-0 mt-4">
  <div class="card-body p-4">
    <h2 class="h5 mb-1">My bookings</h2>
    <p class="text-muted mb-3">View upcoming and past bookings.</p>

    <ul class="nav nav-pills mb-3" id="bookingTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="upcoming-tab" data-bs-toggle="pill" data-bs-target="#upcoming"
                type="button" role="tab" aria-controls="upcoming" aria-selected="true">
          Upcoming
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="past-tab" data-bs-toggle="pill" data-bs-target="#past"
                type="button" role="tab" aria-controls="past" aria-selected="false">
          Past
        </button>
      </li>
    </ul>

    <div class="tab-content" id="bookingTabsContent">
      <!-- Upcoming -->
      <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
        <?php if (empty($upcomingBookings)): ?>
          <div class="text-muted">No upcoming bookings.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>Room</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Players</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($upcomingBookings as $b): ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($b['bookingRef']); ?></td>
                    <td>
                      <?= htmlspecialchars($b['roomName']); ?>
                      <div class="text-muted small"><?= htmlspecialchars($b['roomLocation']); ?></div>
                    </td>
                    <td><?= htmlspecialchars($b['bookingDate']); ?></td>
                    <td><?= htmlspecialchars(substr($b['bookingTimeslot'], 0, 5)); ?></td>
                    <td><?= (int)$b['numPlayers']; ?></td>
                    <td>$<?= htmlspecialchars($b['totalPrice']); ?></td>
                    <td><span class="badge bg-success">Confirmed</span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Past -->
      <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
        <?php if (empty($pastBookings)): ?>
          <div class="text-muted">No past bookings.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>Room</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Players</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pastBookings as $b): ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($b['bookingRef']); ?></td>
                    <td>
                      <?= htmlspecialchars($b['roomName']); ?>
                      <div class="text-muted small"><?= htmlspecialchars($b['roomLocation']); ?></div>
                    </td>
                    <td><?= htmlspecialchars($b['bookingDate']); ?></td>
                    <td><?= htmlspecialchars(substr($b['bookingTimeslot'], 0, 5)); ?></td>
                    <td><?= (int)$b['numPlayers']; ?></td>
                    <td>$<?= htmlspecialchars($b['totalPrice']); ?></td>
                    <td>
                      <?php if ($b['bookingStatus'] === 'Cancelled'): ?>
                        <span class="badge bg-danger">Cancelled</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Completed</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
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