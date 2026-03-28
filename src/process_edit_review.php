<?php
session_start();
require_once "inc/functions.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDBconnection();
    
    // Sanitize inputs
    $review_id = (int)$_POST['reviewID'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    // Ensure rating is valid
    if ($rating >= 1 && $rating <= 5) {
        
        // Update review securely (must match the user's ID)
        $stmt = $conn->prepare("UPDATE Reviews SET rating = ?, comment = ? WHERE reviewID = ? AND Users_userID = ?");
        $stmt->bind_param("isii", $rating, $comment, $review_id, $user_id);
        
        if ($stmt->execute()) {
            // Success
            header("Location: manage_account.php?msg=review_updated");
            exit();
        } else {
            echo "Error updating review: " . $conn->error;
        }
        $stmt->close();
        
    } else {
        echo "Invalid rating submitted.";
    }
    
    $conn->close();
} else {
    // If someone tries to visit this page directly without submitting a form
    header("Location: manage_account.php");
    exit();
}
?>