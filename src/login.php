<?php
ob_start();
require_once __DIR__ . "/inc/secure_session_start.php";
require_once __DIR__ . "/login_functions.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: manage_account.php");
    exit;
}

// Initialize variables
$email = "";
$errorMsg = "";
$success = null;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
ob_end_flush();
?>

<!doctype html>
<html lang="en">

<head>
    <meta name="description" content="Member Login">
    
    <title>Member Login</title>
    
    <?php include "inc/head.inc.php"; ?>
    <link href="css/sign-in.css" rel="stylesheet">
    
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: #f5f5f5;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .form-signin {
            max-width: 330px;
            width: 100%;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .form-control.is-valid {
            border-color: #198754;
        }
    </style>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>
    
    <main>
        <form class="form-signin" method="POST" novalidate>
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
                       value="<?= htmlspecialchars($email) ?>" required placeholder="name@example.com" 
                       autocomplete="email">
                <label for="floatingInput">Email address</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="password" class="form-control" name="password" id="floatingPassword" 
                       required placeholder="Password" autocomplete="current-password">
                <label for="floatingPassword">Password</label>
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
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!password.value) {
                password.classList.add('is-invalid');
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
    </script>
</body>

</html>