<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

// security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit(); 
}


// post request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['roomID'])) {

    $conn = getDbConnection();
    $roomID = (int)$_POST['roomID'];

    // get all image paths before deleting the room (so we can delete the images too)
    $sqlGetImages = "SELECT imagePath FROM RoomImages WHERE Rooms_roomID = ?";
    $stmtGet = $conn->prepare($sqlGetImages);
    $stmtGet->bind_param("i", $roomID);
    $stmtGet->execute();
    $result = $stmtGet->get_result();

    $imagesToDelete = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['imagePath'] && $row['imagePath'] !== 'images/placeholder.png' && file_exists($row['imagePath'])) {
            $imagesToDelete[] = $row['imagePath'];
        }
    }
    $stmtGet->close();
    
    // Also check the old imagePath column for backward compatibility
    $sqlGetOldImage = "SELECT imagePath FROM Rooms WHERE roomID = ?";
    $stmtOldGet = $conn->prepare($sqlGetOldImage);
    $stmtOldGet->bind_param("i", $roomID);
    $stmtOldGet->execute();
    $oldResult = $stmtOldGet->get_result();
    if ($oldRow = $oldResult->fetch_assoc()) {
        $oldImagePath = $oldRow['imagePath'];
        if ($oldImagePath && $oldImagePath !== 'images/placeholder.png' && file_exists($oldImagePath) && !in_array($oldImagePath, $imagesToDelete)) {
            $imagesToDelete[] = $oldImagePath;
        }
    }
    $stmtOldGet->close();


    // actual deletion (delete on cascade)
    $sqlDelete = "DELETE FROM Rooms WHERE roomID = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $roomID);

    if ($stmtDelete->execute()) {

        // delete all images from disk
        foreach ($imagesToDelete as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // redirection
        header("Location: delete_room.php?msg=deleted");
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmtDelete->close();
    $conn->close();
} else {
    // redirection
    header("Location: delete_room.php");
}
ob_end_flush();
