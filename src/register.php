<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . "/login_functions.php";
require_once __DIR__ . "/inc/secure_session_start.php";
require_once __DIR__ . "/api/api_send_email.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: manage_account.php");
    exit;
}

// Generate CSRF token for form
if (empty($_SESSION['csrf_token_register'])) {
    $_SESSION['csrf_token_register'] = bin2hex(random_bytes(32));
}

// Initialize variables
$username = "";
$email = "";
$errorMsg = "";
$successMsg = "";
$success = null;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token_register'], $csrf_token)) {
        $errorMsg = "Security token validation failed. Please try again.<br>";
        $success = false;
    } else {
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
            if (!isset($password) || $password !== $password_confirm) {
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
                                background-color: #ff4444;
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
                                <h2>Hello ' . htmlspecialchars($username) . ',</h2>
                                <p>Your account has been successfully created on <strong>Escapy</strong>.</p>
                                <p>You can now log in and start using our services.</p>
                                <p>Thank you for registering!</p>
                            </div>
                        </div>
                    </body>
                </html>';

                send_email($message, $html_message, $email);
                
                $successMsg = "Registration successful! A confirmation email has been sent to " . htmlspecialchars($email) . ".";
                
                // Clear form data after success
                $username = "";
                $email = "";

            } else {
                $errorMsg = $result['message'];
                $success = false;
            }
        }
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/x-icon" href="images/home.ico">
    <meta name="description" content="Member Registration">
    <title>Member Registration</title>
    <?php include "inc/head.inc.php"; ?>
    <link href="css/sign-in.css" rel="stylesheet">
    <style>
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        .form-control.is-valid {
            border-color: #198754;
        }
        .requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
            margin-bottom: 0.5rem;
            padding-left: 0.5rem;
        }
        .requirements ul {
            margin: 0.25rem 0;
            padding-left: 1.25rem;
        }
        .requirements li {
            margin: 0.15rem 0;
        }
        .requirement-met {
            color: #198754;
            font-weight: 500;
        }
        .requirement-unmet {
            color: #6c757d;
        }
        .info-icon {
            color: #0d6efd;
            font-size: 0.9rem;
            margin-left: 0.25rem;
        }
    </style>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>
    <main>
        <form class="form-signin" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token_register']) ?>">
            
            <img class="mb-4" src="../images/home.png" alt="Logo" width="72" height="57">
            <h1 class="h3 mb-3 fw-normal">Member Registration</h1>

            <?php if ($success === true): ?>
            <div class="alert alert-success" role="alert">
                <strong>Success!</strong><br>
                <?= $successMsg ?>
                <br><br>
                <a href="login.php" class="btn btn-success">Go to Login</a>
            </div>
            <?php endif; ?>

            <?php if ($success === false && !empty($errorMsg)): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Please fix the following errors:</strong><br>
                <?= $errorMsg ?>
            </div>
            <?php endif; ?>

            <?php if ($success !== true): ?>
            <div class="form-floating mb-2">
                <input type="text" name="username" class="form-control" id="floatingUsername" 
                       placeholder="Username" value="<?= htmlspecialchars($username) ?>" required>
                <label for="floatingUsername">Username</label>
            </div>
            <div class="requirements">
                <div class="text-muted small">
                    <strong>Username must:</strong>
                    <ul>
                        <li>Be 3-20 characters long</li>
                        <li>Contain only letters, numbers, and underscores</li>
                    </ul>
                </div>
            </div>

            <div class="form-floating mb-2">
                <input type="email" name="email" class="form-control" id="floatingEmail" 
                       placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
                <label for="floatingEmail">Email address</label>
            </div>
            <div class="requirements mb-3">
                <small>Enter a valid email address</small>
            </div>

            <div class="form-floating mb-2">
                <input type="password" name="password" class="form-control" id="floatingPassword" 
                       placeholder="Password" required>
                <label for="floatingPassword">Password</label>
            </div>
            <div class="requirements" id="passwordRequirements">
                <div class="text-muted small">
                    <strong>Password must contain:</strong>
                    <ul>
                        <li id="req-length" class="requirement-unmet">At least 8 characters</li>
                        <li id="req-uppercase" class="requirement-unmet">One uppercase letter (A-Z)</li>
                        <li id="req-lowercase" class="requirement-unmet">One lowercase letter (a-z)</li>
                        <li id="req-number" class="requirement-unmet">One number (0-9)</li>
                        <li id="req-special" class="requirement-unmet">One special character (!@#$%^&*...)</li>
                    </ul>
                </div>
            </div>

            <div class="form-floating mb-3">
                <input type="password" name="password_confirm" class="form-control" id="floatingPasswordConfirm" 
                       placeholder="Confirm Password" required>
                <label for="floatingPasswordConfirm">Confirm Password</label>
            </div>

            <div class="form-check text-start my-3">
                <input type="checkbox" name="agree" class="form-check-input" id="checkTerms" required>
                <label class="form-check-label" for="checkTerms">
                    I agree to terms and conditions.
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Register</button>
            <?php endif; ?>
        </form>
    </main>
    
    <?php if ($success !== true): ?>
    <div class="text-center p-3">
        <p class="mb-0">
            Already a member? Please go to the
            <a href="login.php">Sign In Page</a>.
        </p>
    </div>
    <?php endif; ?>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Client-side validation with real-time feedback
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($success !== true): ?>
        const form = document.querySelector('.form-signin');
        const username = document.getElementById('floatingUsername');
        const email = document.getElementById('floatingEmail');
        const password = document.getElementById('floatingPassword');
        const passwordConfirm = document.getElementById('floatingPasswordConfirm');
        
        // Password requirements elements
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqLowercase = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');
        
        function validateUsername(username) {
            const errors = [];
            if (username.length < 3) errors.push('Username must be at least 3 characters');
            if (username.length > 20) errors.push('Username must not exceed 20 characters');
            if (!/^[a-zA-Z0-9_]+$/.test(username)) errors.push('Username: letters, numbers, underscores only');
            return errors;
        }
        
        function checkPasswordRequirements(pass) {
            const checks = {
                length: pass.length >= 8,
                uppercase: /[A-Z]/.test(pass),
                lowercase: /[a-z]/.test(pass),
                number: /[0-9]/.test(pass),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(pass)
            };
            
            // Update visual feedback
            reqLength.className = checks.length ? 'requirement-met' : 'requirement-unmet';
            reqUppercase.className = checks.uppercase ? 'requirement-met' : 'requirement-unmet';
            reqLowercase.className = checks.lowercase ? 'requirement-met' : 'requirement-unmet';
            reqNumber.className = checks.number ? 'requirement-met' : 'requirement-unmet';
            reqSpecial.className = checks.special ? 'requirement-met' : 'requirement-unmet';
            
            return Object.values(checks).every(Boolean);
        }
        
        // Real-time username validation
        username.addEventListener('input', function() {
            const errors = validateUsername(this.value);
            if (this.value.length > 0) {
                this.classList.toggle('is-invalid', errors.length > 0);
                this.classList.toggle('is-valid', errors.length === 0);
            }
        });
        
        // Real-time password validation
        password.addEventListener('input', function() {
            const allValid = checkPasswordRequirements(this.value);
            if (this.value.length > 0) {
                this.classList.toggle('is-invalid', !allValid);
                this.classList.toggle('is-valid', allValid);
            }
            
            // Also check password confirmation match if filled
            if (passwordConfirm.value) {
                const match = this.value === passwordConfirm.value;
                passwordConfirm.classList.toggle('is-invalid', !match);
                passwordConfirm.classList.toggle('is-valid', match);
            }
        });
        
        // Real-time email validation
        email.addEventListener('blur', function() {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const isValid = emailPattern.test(this.value);
            if (this.value) {
                this.classList.toggle('is-invalid', !isValid);
                this.classList.toggle('is-valid', isValid);
            }
        });
        
        // Real-time password confirmation validation
        passwordConfirm.addEventListener('input', function() {
            const match = this.value === password.value;
            if (this.value.length > 0) {
                this.classList.toggle('is-invalid', !match);
                this.classList.toggle('is-valid', match && password.value);
            }
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            let errors = [];
            
            // Validate username
            const usernameErrors = validateUsername(username.value);
            errors = errors.concat(usernameErrors);
            
            // Validate email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value)) {
                errors.push('Please enter a valid email address');
            }
            
            // Validate password
            if (!checkPasswordRequirements(password.value)) {
                errors.push('Password does not meet all requirements');
            }
            
            // Validate password confirmation
            if (password.value !== passwordConfirm.value) {
                errors.push('Passwords do not match');
            }
            
            // If there are errors, prevent submission and show them
            if (errors.length > 0) {
                e.preventDefault();
                
                // Mark invalid fields
                if (usernameErrors.length > 0) username.classList.add('is-invalid');
                if (!emailPattern.test(email.value)) email.classList.add('is-invalid');
                if (!checkPasswordRequirements(password.value)) password.classList.add('is-invalid');
                if (password.value !== passwordConfirm.value) passwordConfirm.classList.add('is-invalid');
                
                // Scroll to first error
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        <?php endif; ?>
    });
    </script>
</body>
</html>