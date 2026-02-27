<?php
require_once __DIR__ . "/inc/secure_session_start.php";
require_once __DIR__ . "/inc/functions.php";

$stats = [
    'rooms' => 0,
    'bookings' => 0,
    'reviews' => 0,
    'avg_rating' => 'N/A',
];

$conn = getDBconnection();

if ($conn) {
    $roomCountResult = $conn->query("SELECT COUNT(*) AS total FROM Rooms");
    if ($roomCountResult) {
        $stats['rooms'] = (int)$roomCountResult->fetch_assoc()['total'];
        $roomCountResult->free();
    }

    $bookingCountResult = $conn->query("SELECT COUNT(*) AS total FROM Bookings");
    if ($bookingCountResult) {
        $stats['bookings'] = (int)$bookingCountResult->fetch_assoc()['total'];
        $bookingCountResult->free();
    }

    $reviewResult = $conn->query("SELECT COUNT(*) AS total, AVG(rating) AS average FROM Reviews");
    if ($reviewResult) {
        $reviewData = $reviewResult->fetch_assoc();
        $stats['reviews'] = (int)$reviewData['total'];
        $stats['avg_rating'] = $reviewData['average'] !== null ? number_format($reviewData['average'], 1) : 'N/A';
        $reviewResult->free();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>About Escapy</title>
    <?php include "inc/head.inc.php"; ?>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <main class="container py-5">
        <section class="text-center py-5">
            <p class="text-uppercase text-secondary mb-2">Our Story</p>
            <h1 class="display-4 fw-bold mb-3">Escapy is more than an escape room</h1>
            <p class="lead mx-auto" style="max-width: 720px;">
                Since 2014 we have crafted immersive adventures, invested in live storytelling, and built a dependable
                back-end that keeps every booking, membership upgrade, and review firmly rooted to a secure database.
            </p>
            <a class="btn btn-primary mt-3" href="index.php#Rooms">Explore Rooms</a>
        </section>

        <section class="row text-center gy-4 mb-5">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Curated Experiences</p>
                        <p class="display-6 mb-1"><?= number_format($stats['rooms']) ?></p>
                        <p class="text-muted mb-0">rooms engineered with cinematic sets and collaborative puzzles.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Bookings Logged</p>
                        <p class="display-6 mb-1"><?= number_format($stats['bookings']) ?></p>
                        <p class="text-muted mb-0">secure reservations powered by the same back-end that drives availability.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Community Reviews</p>
                        <p class="display-6 mb-1"><?= number_format($stats['reviews']) ?></p>
                        <p class="text-muted mb-0">averaging <?= htmlspecialchars($stats['avg_rating']) ?>/5 stars across every experience.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="services" class="row align-items-center gx-5 mb-5">
            <div class="col-lg-6">
                <h2 class="h3 mb-3">We deliver services that excite and comfort</h2>
                <p class="text-muted mb-4">
                    Our team of creative directors, developers, and game masters collaborate daily to make sure every
                    escape room stays fresh, safe, and responsive to player feedback. We lean on data from our booking and
                    reviews tables to tune stories, cast new live actors, and adjust difficulty levels without interrupting your fun.
                </p>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Live support:</strong> Host-guided sessions monitored through our secure admin workflows.</li>
                    <li class="mb-2"><strong>Curated adventures:</strong> Rotate rooms with fresh scripts while preserving guest data.</li>
                    <li class="mb-2"><strong>Member perks:</strong> Loyalty pricing, account updates, and review prompts available in your dashboard.</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="bg-light rounded-4 shadow-sm p-4">
                    <h3 class="h5 mb-3">Our methodology</h3>
                    <p class="text-muted mb-3">
                        Every action you take — registering an account, booking a slot, rating a room — hits a
                        transactional PHP endpoint. The same server-side bootstrapping lets our staff dispatch confirmations,
                        log every booking, adjust availability, and welcome you back with personalized suggestions.
                    </p>
                    <p class="text-muted mb-0">
                        We also maintain detailed review data so we can celebrate what you love and iterate on what can improve.
                    </p>
                </div>
            </div>
        </section>

        <section class="row gy-4">
            <article class="col-lg-6">
                <div class="border rounded-4 p-4 h-100">
                    <h3 class="h5 mb-3">Our commitment</h3>
                    <p class="text-muted">
                        We commit to transparent communication, thoughtful safety policies, and accessible online tools. You
                        can manage your bookings, update payment details, and submit reviews without having to call in a
                        support ticket.
                    </p>
                </div>
            </article>
            <article class="col-lg-6">
                <div class="border rounded-4 p-4 h-100">
                    <h3 class="h5 mb-3">Looking forward</h3>
                    <p class="text-muted">
                        Expect seasonal rooms, richer member dashboards, and even smoother backend automations that keep every
                        reservation consistent and every story immersive.
                    </p>
                    <a class="btn btn-outline-primary" href="index.php#Rooms">See upcoming rooms</a>
                </div>
            </article>
        </section>
    </main>

    <?php include "inc/footer.inc.php"; ?>
</body>

</html>
