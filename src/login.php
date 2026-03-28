<?php
ob_start();
require_once __DIR__ . "/inc/secure_session_start.php";
require_once __DIR__ . "/login_functions.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: manage_account.php");
    exit;
}

// Generate CSRF token for form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$email = "";
$errorMsg = "";
$success = null;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errorMsg = "Security token validation failed. Please try again.<br>";
        $success = false;
    } else {
        $success = true;
        
        // Validate email
        if (empty($_POST["email"])) {
            $errorMsg .= "Email is required.<br>";
            $success = false;
        } else {
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
        } else {
            $password = $_POST["password"];
        }

        if ($success) {
            // Check rate limiting before attempting authentication
            $attemptCheck = checkLoginAttempts($email);
            
            if (!$attemptCheck['allowed']) {
                $errorMsg .= $attemptCheck['message'];
                $success = false;
            } else {
                $user = authenticateUser($email, $password);
                
                if ($user === false) {
                    // Record failed login attempt
                    recordFailedLogin($email);
                    
                    // Generic error message to prevent user enumeration
                    $errorMsg .= "Incorrect email or password.<br>";
                    $success = false;
                } else {
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
                    ob_end_clean();
                    header("Location: index.php");
                    exit;
                }
            }
        }
    }
}
ob_end_flush();
?>

<!doctype html>
<html lang="en">

<head>
    <meta name="description" content="Member Login">
    
    <title>Member Login</title>
    
    <?php include "inc/head.inc.php"; ?>
    <link href="css/sign-in.css" rel="stylesheet">
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>
    
    <main>
        <form class="form-signin" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <img class="mb-4" src="../images/home.png" alt="Logo" width="72" height="57">
            
            <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
            
            <?php if ($success === false && !empty($errorMsg)): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Login Failed</strong><br>
                <?= $errorMsg ?>
            </div>
            <?php endif; ?>
            
            <div class="form-floating mb-2">
                <input type="email" class="form-control" name="email" id="floatingInput" 
                       value="<?= htmlspecialchars($email) ?>" required placeholder=" " 
                       autocomplete="email">
                <label for="floatingInput">Email address</label>
            </div>
            
           <div class="form-floating mb-3 password-wrapper">
                <input type="password" class="form-control" name="password" id="floatingPassword" 
                    required placeholder=" " autocomplete="current-password">
                <label for="floatingPassword">Password</label>
                <button type="button" class="password-toggle" onclick="togglePassword('floatingPassword', this)" aria-label="Toggle password visibility">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            
            <button class="btn btn-primary w-100 py-2" type="submit">
                Sign in
            </button>
        </form>
    </main>
    
    <div class="text-center p-3">
        <p class="mb-0">
            Don't have an account yet?
            <a href="register.php">Create one here</a>.
        </p>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Client-side validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.form-signin');
        const email = document.getElementById('floatingInput');
        const password = document.getElementById('floatingPassword');
        
        // Email validation
        email.addEventListener('blur', function() {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const isValid = emailPattern.test(this.value);
            this.classList.toggle('is-invalid', !isValid && this.value);
            this.classList.toggle('is-valid', isValid && this.value);
        });
        
        // Password validation (just check if not empty)
        password.addEventListener('blur', function() {
            this.classList.toggle('is-invalid', !this.value);
            this.classList.toggle('is-valid', this.value.length > 0);
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            let isValid = true;
            
            if (!emailPattern.test(email.value)) {
                shakeField(email);
                isValid = false;
            }

            if (!password.value) {
                shakeField(password);
                isValid = false;
}
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Auto-focus email field if empty, otherwise focus password
        <?php if (empty($email)): ?>
        email.focus();
        <?php else: ?>
        password.focus();
        <?php endif; ?>
    });

    function shakeField(field) {
    field.classList.remove('is-invalid');
    void field.offsetWidth;
    field.classList.add('is-invalid');
}
    
    const eyeOpen = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>`;

    const eyeClosed = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
    </svg>`;

function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        btn.innerHTML = eyeClosed;
    } else {
        field.type = 'password';
        btn.innerHTML = eyeOpen;
    }
}

    </script>
</body>

</html>