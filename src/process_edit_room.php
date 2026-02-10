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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDBconnection();
    
    $id = (int)$_POST['roomID']; 
    
    // sanitise inputs
    $name = sanitize_input($_POST['roomName']);
    $desc = sanitize_input($_POST['roomDescription']);
    $location = sanitize_input($_POST['roomLocation']);
    $duration = (int)$_POST['roomDuration'];
    $min = (int)$_POST['roomMin'];
    $max = (int)$_POST['roomMax'];
    $priceOff = (float)$_POST['roomPriceOffpeak'];
    $pricePeak = (float)$_POST['roomPricePeak'];
    
    $difficulty = $_POST['roomDifficulty'];
    $fear = $_POST['roomFearLevel'];
    $genre = $_POST['roomGenre'];
    $expType = $_POST['roomExperienceType'];

    // logic checks
    if ($min > $max) {
        die("<h3 style='color:white; background:red; padding:20px; text-align:center;'>Error: Minimum players cannot be greater than maximum. <br><a href='javascript:history.back()' style='color:white;'>Go Back</a></h3>");
    }

    if ($priceOff > $pricePeak) {
        die("<h3 style='color:white; background:red; padding:20px; text-align:center;'>Error: Price off-peak cannot be greater than peak price. <br><a href='javascript:history.back()' style='color:white;'>Go Back</a></h3>");
    }

    if ($priceOff < 0 || $pricePeak < 0) {
        die("<h3 style='color:white; background:red; padding:20px; text-align:center;'>Error: Price cannot be negative. <br><a href='javascript:history.back()' style='color:white;'>Go Back</a></h3>");
    }

    $sql = "UPDATE Rooms SET 
            roomName=?, roomDescription=?, roomMax=?, roomMin=?, roomDuration=?, 
            roomDifficulty=?, roomLocation=?, roomFearLevel=?, roomExperienceType=?, 
            roomGenre=?, roomPricePeak=?, roomPriceOffpeak=? 
            WHERE roomID=?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiisssssddi", 
        $name, $desc, $max, $min, $duration, 
        $difficulty, $location, $fear, $expType, $genre, 
        $pricePeak, $priceOff, $id
    );
    
    if (!$stmt->execute()) {
        die("Error updating record: " . $stmt->error);
    }
    $stmt->close();

    // image handling (only runs when a file is uploaded)
    if (isset($_FILES["roomImage"]) && $_FILES["roomImage"]["error"] == 0) {
        
        $allowed = ['jpg', 'png'];
        $fileName = basename($_FILES["roomImage"]["name"]);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES["roomImage"]["tmp_name"]);
        
        if ($check !== false && in_array($extension, $allowed)) {
            // find OLD image path so we can delete it later
            $oldImgSql = "SELECT imagePath FROM Rooms WHERE roomID = ?";
            $oldStmt = $conn->prepare($oldImgSql);
            $oldStmt->bind_param("i", $id);
            $oldStmt->execute();
            $res = $oldStmt->get_result();
            $oldPath = ($res->fetch_assoc())['imagePath'];
            $oldStmt->close();

            // upload new image
            $target_dir = "images/";

            $newFileName = "room_" . uniqid() . "." . $extension; 
            $target_file = $target_dir . $newFileName;

            if (move_uploaded_file($_FILES["roomImage"]["tmp_name"], $target_file)) {
                
                // update db with new image path
                $updateImg = $conn->prepare("UPDATE Rooms SET imagePath=? WHERE roomID=?");
                $updateImg->bind_param("si", $target_file, $id);
                $updateImg->execute();
                $updateImg->close();

                // delete old image (only if exists and isnt placeholder)
                if ($oldPath && $oldPath !== 'images/placeholder.png' && file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        }
    }

    $conn->close();
    
    // success redirect
    header("Location: room.php?name=" . urlencode($name));
    exit();

} else {
    header("Location: delete_room.php");
    exit();
}
ob_end_flush();
?>