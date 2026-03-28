<?php
require_once __DIR__ . "/inc/secure_session_start.php";
require_once __DIR__ . "/inc/functions.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Contact Us - Escapy</title>
    <?php include "inc/head.inc.php"; ?>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <main class="container py-5 text-white">
        <section class="text-center py-5">
            <p class="text-uppercase text-white mb-2">Get In Touch</p>
            <h1 class="display-4 fw-bold mb-3">Contact Escapy</h1>
            <p class="lead mx-auto" style="max-width: 720px;">
                Have questions or need help with your next adventure? Reach out to us using the information below!
            </p>
        </section>

        <section class="row text-center gy-4 mb-5 justify-content-center">
            <div class="col-md-4">
                <div class="card h-100 border-danger shadow-sm p-4 bg-dark text-white">
                    <div class="card-body">
                        <h3 class="h5 mb-3 fw-bold">Address</h3>
                        <p class="text-white mb-0">
                            <strong>Escapy</strong><br>
                            435 Orchard Road<br>
                            04-27 Wisma Atria<br>
                            SINGAPORE 238877
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-danger shadow-sm p-4 bg-dark text-white">
                    <div class="card-body">
                        <h3 class="h5 mb-3 fw-bold">Contact No.</h3>
                        <p class="display-6 mb-2 fs-4">
                            <a href="tel:+6567346322" class="text-white text-decoration-none">+65 67346322</a>
                        </p>
                        <p class="text-white mb-0">Give us a call during our operating hours.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-danger shadow-sm p-4 bg-dark text-white">
                    <div class="card-body">
                        <h3 class="h5 mb-3 fw-bold">Operating Hours</h3>
                        <p class="text-white mb-2">
                            <strong>Monday to Thursday</strong><br>
                            12:00pm to 10:00pm
                        </p>
                        <p class="text-white mb-0">
                            <strong>Friday to Sunday</strong><br>
                            12:00pm to 12:00am
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include "inc/footer.inc.php"; ?>
</body>

</html>