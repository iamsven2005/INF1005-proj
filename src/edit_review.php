<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$review = null;

if (isset($_GET['id'])) {
    $conn = getDBconnection();
    
    // Sanitize input
    $review_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Fetch review AND ensure it belongs to the logged-in user
    $stmt = $conn->prepare("SELECT rv.*, r.roomName FROM Reviews rv JOIN Rooms r ON rv.Rooms_roomID = r.roomID WHERE rv.reviewID = ? AND rv.Users_userID = ?");
    $stmt->bind_param("ii", $review_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $review = $result->fetch_assoc();
    }
    $stmt->close();
    $conn->close();
}

// Simple check if review exists/belongs to user
if (!$review) {
    echo "<h2 style='color: white; text-align: center; margin-top: 50px;'>Review not found or unauthorized.</h2>";
    echo "<div style='text-align: center;'><a href='manage_account.php' style='color: #f59f00;'>Back to Account</a></div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Review - Escapy</title>
    <?php include "inc/head.inc.php" ?>
    <link rel="stylesheet" href="css/rooms.css">
    <link href="css/rating.css?v=<?php echo time(); ?>" rel="stylesheet" />
</head>

<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="page-content section-gap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="pricing-card text-start p-5 bg-dark border border-danger rounded-4">
                        <h2 class="text-center mb-4 text-warning">Edit Rating: <?php echo htmlspecialchars($review['roomName']); ?></h2>

                        <p class="text-center text-white mb-4" style="font-size: 0.9rem;">
                            Fields marked with <span class="text-danger">*</span> are required.
                        </p>

                        <form action="process_edit_review.php" method="POST">

                            <input type="hidden" name="reviewID" value="<?php echo $review['reviewID']; ?>">

                            <div class="mb-4 text-center">
                                <label class="form-label text-white d-block mb-2">Rating <span class="text-danger">*</span></label>
                                <div class="star-rating">
                                    <input type="radio" id="star5" name="rating" value="5" <?= ($review['rating'] == 5) ? 'checked' : '' ?> required />
                                    <label for="star5" title="5 stars">★</label>
                                    
                                    <input type="radio" id="star4" name="rating" value="4" <?= ($review['rating'] == 4) ? 'checked' : '' ?> />
                                    <label for="star4" title="4 stars">★</label>
                                    
                                    <input type="radio" id="star3" name="rating" value="3" <?= ($review['rating'] == 3) ? 'checked' : '' ?> />
                                    <label for="star3" title="3 stars">★</label>
                                    
                                    <input type="radio" id="star2" name="rating" value="2" <?= ($review['rating'] == 2) ? 'checked' : '' ?> />
                                    <label for="star2" title="2 stars">★</label>
                                    
                                    <input type="radio" id="star1" name="rating" value="1" <?= ($review['rating'] == 1) ? 'checked' : '' ?> />
                                    <label for="star1" title="1 star">★</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-white">Comment</label>
                                <textarea class="form-control bg-dark text-white border-secondary" name="comment" rows="4" style="border-radius: 5px;"><?php echo htmlspecialchars($review['comment']); ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="book-btn w-100 mb-2">Save Changes</button>
                                <a href="manage_account.php" class="btn btn-outline-light w-100">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include "inc/footer.inc.php" ?>
</body>

</html>