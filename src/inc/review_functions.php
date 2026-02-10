<?php
// get average rating for a specific room 
function getAverageRating($room_id) {
    require_once __DIR__ . "/functions.php";
    $conn = getDBconnection();
    
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM Reviews WHERE Rooms_roomID = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $avg = $row['avg_rating'] ?? 0;
        $stmt->close();
        $conn->close();
        return round($avg, 1);
    }
    
    $stmt->close();
    $conn->close();
    return 0;
}

// Get number of reviews for a specific room
function getReviewCount($room_id) {
    require_once __DIR__ . "/functions.php";
    $conn = getDBconnection();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM Reviews WHERE Rooms_roomID = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $count = $row['review_count'] ?? 0;
        $stmt->close();
        $conn->close();
        return $count;
    }
    
    $stmt->close();
    $conn->close();
    return 0;
}

// Get all reviews for a specific room
function getRoomReviews($room_id) {
    require_once __DIR__ . "/functions.php";
    $conn = getDBconnection();
    
    $stmt = $conn->prepare("SELECT r.*, u.username, u.email 
            FROM Reviews r 
            JOIN Users u ON r.Users_userID = u.userID 
            WHERE r.Rooms_roomID = ? 
            ORDER BY r.created_at DESC");

    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $reviews;
}

//Generate star rating HTML
function displayStarRating($rating) {
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5 ? 1 : 0;
    $empty_stars = 5 - $full_stars - $half_star;
    
    $html = '';
    
    // Full stars
    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '★';
    }
    
    // Half star
    if ($half_star) {
        $html .= '<span style="position:relative;display:inline-block;"><span style="position:absolute;overflow:hidden;width:50%;">★</span>☆</span>';
    }
    
    // Empty stars
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '☆';
    }
    
    return $html;
}
?>
