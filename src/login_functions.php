<?php
require_once __DIR__ . "/vendor/autoload.php";

// Helper function that checks input for malicious or unwanted content.
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


function getDBconnection()
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}



// Function to create DB connection using credentials in env var
function getDBEnvVar()
{
    // Create DB connection
    $db_host = getenv('DB_HOST') ?: "db";
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');
    $db_name = getenv('DB_NAME');
    $stripekey = getenv('STRIPESECRETKEY');
    return array($db_host, $db_user, $db_pass, $db_name, $stripekey);
}

// Helper function to validate password strength
function validatePasswordStrength($password): array
{
    $errors = [];
    
    // Minimum 8 characters (you already have this)
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    
    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    
    // Check for at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    
    // Check for at least one special character
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }
    
    // Check against common passwords
    $commonPasswords = [
        'Password1!', 'Password123!', 'Welcome1!', 'Welcome123!',
        'Qwerty123!', 'Admin123!', 'User1234!', 'Test1234!',
        'Abcd1234!', 'Abc123!@', 'Abcdef1!', 'MyPassword1!',
        'Secret123!', 'Pass123!@', 'PassWord1!', 'Qwerty1!',
        'Passw0rd!', 'P@ssword1', 'P@ssw0rd', 'Iloveyou3000'
    ];
    
    if (in_array($password, $commonPasswords, true)) {
        $errors[] = "This password is too simple/common. Please choose a stronger password.";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// Helper function to validate username
function validateUsername($username): array
{
    $errors = [];
    
    // Length check
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }
    
    if (strlen($username) > 30) {
        $errors[] = "Username must not exceed 30 characters.";
    }
    
    // Only allow alphanumeric characters, underscores, and hyphens
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, underscores, and hyphens.";
    }
    
    // Don't allow usernames that start with numbers
    if (preg_match('/^[0-9]/', $username)) {
        $errors[] = "Username cannot start with a number.";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// Helper function to check if username already exists
function usernameExists($username): bool
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return false;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("SELECT userID FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $exists = $result->num_rows > 0;

        $stmt->close();
        $conn->close();

        return $exists;
    } catch (Exception $e) {
        return false;
    }
}

// Helper function to check if email already exists
function emailExists($email): bool
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return false;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("SELECT userID FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $exists = $result->num_rows > 0;

        $stmt->close();
        $conn->close();

        return $exists;
    } catch (Exception $e) {
        return false;
    }
}

// Helper function to implement rate limiting for login attempts
function checkLoginAttempts($email): array
{
    // Store attempts in session (or better: in database with timestamp)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    $current_time = time();
    $lockout_time = 900; // 15 minutes
    $max_attempts = 5;
    
    // Clean up old attempts (older than lockout time)
    if (isset($_SESSION['login_attempts'][$email])) {
        $_SESSION['login_attempts'][$email] = array_filter(
            $_SESSION['login_attempts'][$email],
            function($timestamp) use ($current_time, $lockout_time) {
                return ($current_time - $timestamp) < $lockout_time;
            }
        );
    }
    
    // Check if user is locked out
    if (isset($_SESSION['login_attempts'][$email]) && 
        count($_SESSION['login_attempts'][$email]) >= $max_attempts) {
        
        $oldest_attempt = min($_SESSION['login_attempts'][$email]);
        $time_remaining = $lockout_time - ($current_time - $oldest_attempt);
        
        return [
            'allowed' => false,
            'message' => "Too many failed login attempts. Please try again in " . 
                        ceil($time_remaining / 60) . " minutes."
        ];
    }
    
    return ['allowed' => true];
}

// Helper function to record failed login attempt
function recordFailedLogin($email)
{
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    if (!isset($_SESSION['login_attempts'][$email])) {
        $_SESSION['login_attempts'][$email] = [];
    }
    
    $_SESSION['login_attempts'][$email][] = time();
}

// Helper function to clear login attempts on successful login
function clearLoginAttempts($email)
{
    if (isset($_SESSION['login_attempts'][$email])) {
        unset($_SESSION['login_attempts'][$email]);
    }
}

// Updated saveUserToDB with user_id return
function saveUserToDB(string $username, string $email, string $passwordHash): array
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return ['success' => false, 'message' => "DB Environment Variables not set."];
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            return ['success' => false, 'message' => "Connection Failed: " . $conn->connect_error];
        }

        // Use prepared statement to insert user data
        $stmt = $conn->prepare("INSERT INTO Users (username, email, passwordhash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $passwordHash);

        if (!$stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => "Execute failed: (" . $stmt->errno . ") " . $stmt->error];
        }

        $user_id = $conn->insert_id; // Get the inserted user ID

        $stmt->close();
        $conn->close();

        return [
            'success' => true, 
            'message' => 'User registered successfully.',
            'user_id' => $user_id
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Exception: " . $e->getMessage()];
    }
}

// Helper function to authenticate user
function authenticateUser($email, $password) {
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return false;
    }

    //mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        // Added is_admin to the SELECT statement
        $stmt = $conn->prepare("SELECT userID, username, email, passwordhash, is_admin FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
		
        if ($result->num_rows !== 1) {
			$user['message'] = "db";
            return false; // user not found
        }

        $user = $result->fetch_assoc();

        $stmt->close();
        $conn->close();
		
        if (password_verify($password, $user['passwordhash'])) {
			// Remove password hash before returning for security
			unset($user['passwordhash']);
		
        //if ($password === $user['passwordhash']) {
			return $user; // success, return user data
        }
		else {
            return false; // password mismatch
        }
    }
	catch (Exception $e) {
        // can log $e->getMessage() here
        return false; //$e->getMessage();
    }
}

// Helper function to get the user's email
function getUserEmail(int $user_id) {
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("SELECT email FROM Users WHERE userID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $email = null;
        if ($row = $result->fetch_assoc()) {
            $email = $row["email"];
        }

        $stmt->close();
        $conn->close();

        return $email;
    }
	catch (Exception $e) {
        echo 'login: ' . $e->getMessage();
    }
}

// Helper function to get user data
function getUserData(int $user_id): ?array
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return null;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("SELECT userID, username, email, created_at FROM Users WHERE userID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = null;
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
        }

        $stmt->close();
        $conn->close();

        return $user;
    } catch (Exception $e) {
        return null;
    }
}

// Helper function to update username
function updateUsername(int $user_id, string $new_username): array
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return ['success' => false, 'message' => "DB Environment Variables not set."];
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("UPDATE Users SET username = ? WHERE userID = ?");
        $stmt->bind_param("si", $new_username, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Username updated successfully.'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to update username.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Exception: " . $e->getMessage()];
    }
}

// Helper function to update email
function updateEmail(int $user_id, string $new_email): array
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return ['success' => false, 'message' => "DB Environment Variables not set."];
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("UPDATE Users SET email = ? WHERE userID = ?");
        $stmt->bind_param("si", $new_email, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Email updated successfully.'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to update email.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Exception: " . $e->getMessage()];
    }
}

// Helper function to update password
function updatePassword(int $user_id, string $password_hash): array
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return ['success' => false, 'message' => "DB Environment Variables not set."];
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("UPDATE Users SET passwordhash = ? WHERE userID = ?");
        $stmt->bind_param("si", $password_hash, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Password updated successfully.'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to update password.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Exception: " . $e->getMessage()];
    }
}

// Helper function to verify current password
function verifyCurrentPassword(int $user_id, string $password): bool
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return false;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("SELECT passwordhash FROM Users WHERE userID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $stmt->close();
            $conn->close();
            return password_verify($password, $user['passwordhash']);
        }

        $stmt->close();
        $conn->close();
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Helper function to delete user account
function deleteUserAccount(int $user_id): array
{
    list($db_host, $db_user, $db_pass, $db_name) = getDBEnvVar();

    if (!$db_user || !$db_pass || !$db_name) {
        return ['success' => false, 'message' => "DB Environment Variables not set."];
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        $stmt = $conn->prepare("DELETE FROM Users WHERE userID = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Account deleted successfully.'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to delete account.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Exception: " . $e->getMessage()];
    }
}


function sendConfirmationEmail($email, $username) {
	

}
?>
