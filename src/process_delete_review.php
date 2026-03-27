<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

// Admin-only endpoint
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reviewID'])) {
    $conn = getDbConnection();
    $reviewID = (int)$_POST['reviewID'];

    $sqlDelete = "DELETE FROM Reviews WHERE reviewID = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $reviewID);

    if ($stmtDelete->execute()) {
        header("Location: delete_room.php?msg=review_deleted");
    } else {
        echo "Error deleting review: " . $conn->error;
    }

    $stmtDelete->close();
    $conn->close();
} else {
    header("Location: delete_room.php");
}

ob_end_flush();
