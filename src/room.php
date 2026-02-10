<?php
session_start();

include "inc/functions.php";
include "inc/review_functions.php";
$conn = getDBconnection();
$room = null;

//check for 'NAME' in the url
if (isset($_GET['name'])) {

    //get the NAME from the url
    $room_name = sanitize_input($_GET['name']);

    //safe query for the room
    $stmt = $conn->prepare("SELECT * FROM Rooms WHERE roomName = ?");
    $stmt->bind_param("s", $room_name);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
        $room_id = $room['roomID'];

        // store in session for booking page
        $_SESSION['room_id'] = $room_id;
        $_SESSION['room_name'] = $room_name;
        $_SESSION['desc'] = $room['roomDescription'];
        $_SESSION['min'] = $room['roomMin'];
        $_SESSION['max'] = $room['roomMax'];
        $_SESSION['price'] = $room['roomPriceOffPeak'];

        // Get average rating for this room
        $rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM Reviews WHERE Rooms_roomID = ?");
        $rating_stmt->bind_param("i", $room_id);
        $rating_stmt->execute();
        $rating_result = $rating_stmt->get_result();
        $rating_data = $rating_result->fetch_assoc();
        $avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
        $review_count = $rating_data['review_count'] ?? 0;
        $rating_stmt->close();
    }
} else {
    echo "No room specified.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo $room ? htmlspecialchars($room['roomName']) : 'Room Not Found' ?></title>
    <?php include "inc/head.inc.php" ?>
    <link rel="stylesheet" href="css/rooms.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/rating.css?v=<?php echo time(); ?>">
    <link rel="preload" href="css/popup.css" as="style">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="js/popup.js"></script>
</head>

<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="page-content" title="Rooms">
        <div class="container">
            <a href="index.php" class="back-link">← Back to Home</a>

            <?php if ($room): ?>
                <img src="<?php echo htmlspecialchars(str_replace(' ', '%20', $room['imagePath'] ?? '/images/placeholder.png')); ?>"
                    alt="<?php echo htmlspecialchars($room['roomName']) ?>" class="room-hero">

                <div class="thumbnail-gallery">
                    <img src="<?php echo htmlspecialchars(str_replace(' ', '%20', $room['imagePath'] ?? '/images/placeholder.png')); ?>" alt="Thumbnail 1" class="active" onclick="changeHeroImage(this.src)">
                </div>

                <div class="room-content">

                    <div class="room-details">
                        <h1 class="room-title"><?php echo htmlspecialchars($room['roomName']) ?></h1>

                        <div class="room-badges">
                            <span class="badge <?php echo getBadgeColor($room['roomFearLevel']); ?>"><?php echo htmlspecialchars($room['roomFearLevel']); ?></span>
                            <span class="badge <?php echo getDifficultyColor($room['roomDifficulty']); ?>"><?php echo htmlspecialchars($room['roomDifficulty']); ?></span>
                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($room['roomGenre']) ?></span>
                        </div>

                        <h2>About This Room</h2>
                        <p><?php echo htmlspecialchars($room['roomDescription']) ?></p>
                        <hr>
                        <h3>What to Expect</h3>
                        <ul>
                            <li>Immersive storyline and detailed set design</li>
                            <li>Challenging puzzles that require teamwork</li>
                            <li>Professional game master guidance</li>
                            <li>Photo opportunities after completion</li>
                        </ul>

                        <h4>Important Information</h4>
                        <ul>
                            <li>Please arrive 10 minutes before your scheduled time</li>
                            <li>Late arrivals may result in reduced game time</li>
                            <li>Not recommended for children under 12 (unless specified)</li>
                            <li>Comfortable clothing and closed-toe shoes recommended</li>
                        </ul>
                    </div>


                    <div class="pricing-card">
                        <div class="price-label">From</div>
                        <div class="price"> $<?php echo $room['roomPriceOffPeak'] ?></div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">/person</div>

                        <ul class="price-details">
                            <li><?php echo $room['roomDuration'] ?> minutes </li>
                            <li><?php echo $room['roomMin'] . '-' . $room['roomMax']; ?> players</li>
                            <li>Rating: ★<?php echo $avg_rating; ?> (<?php echo $review_count; ?> reviews)</li>
                        </ul>

                        <!-- booking popup button -->
                        <button type="button" id="openPopup" name="openPopup" class="book-btn">Book Now</button>

                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                            <a href="reviews_page.php?name=<?php echo urlencode($room_name); ?>" class="rating-btn" style="text-decoration: none; display: block; text-align: center;">Rate Our Services</a>
                        <?php endif; ?>

                        <p class="cancellation-note">Free cancellation up to 24 hours before</p>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="reviews-section">
                    <h2>What Our Customers Say</h2>

                    <?php
                    $reviews = getRoomReviews($room_id);

                    if (count($reviews) > 0):
                    ?>
                        <div class="reviews-summary">
                            <div aria-hidden="true">
                                <?php echo displayStarRating($avg_rating); ?>
                            </div>
                            <div class="reviews-summary-rating">
                                <?php echo $avg_rating; ?> out of 5
                            </div>
                            <div class="reviews-summary-count">
                                Based on <?php echo $review_count; ?> <?php echo $review_count == 1 ? 'review' : 'reviews'; ?>
                            </div>
                        </div>

                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div>
                                            <div class="review-username">
                                                <?php echo htmlspecialchars($review['username']); ?>
                                            </div>
                                            <div class="review-stars">
                                                <?php echo displayStarRating($review['rating']); ?>
                                            </div>
                                        </div>
                                        <div class="review-date">
                                            <?php
                                            $date = new DateTime($review['created_at']);
                                            echo $date->format('M j, Y');
                                            ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($review['comment'])): ?>
                                        <div class="review-comment">
                                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="reviews-empty">
                            <p>No reviews yet. Be the first to review this room!</p>
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <a href="reviews_page.php?name=<?php echo urlencode($room_name); ?>" class="btn btn-primary">Write a Review</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
        </div>
        <!-- booking pop up -->
        <div id="modal" class="modal" tabindex="0">
            <div class="modal-content">
                <span class="close">&times;</span>
                <iframe id="popupFrame" src="about:blank" title="booking pop up"></iframe>
            </div>
        </div>

    <?php else: ?>
        <!-- ERROR: room Not Found -->
        <div class="text-center" style="padding: 100px 0; background: rgba(255,255,255,0.05); border-radius: 15px; margin-top: 2rem;">
            <h1 class="text-warning mb-3">Room Not Found</h1>
            <p class="lead text-light mb-4">
                Sorry, we couldn't find a room with that name.<br>
                It may have been removed or the link is incorrect.
            </p>

            <a href="index.php" class="book-btn" style="text-decoration: none; display: inline-block; max-width: 200px;">
                Browse All Rooms
            </a>
        </div>
    <?php endif; ?>
    </main>

    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>