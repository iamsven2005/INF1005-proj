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

    // Handle multiple new image uploads
    if (isset($_FILES["roomImages"]) && is_array($_FILES["roomImages"]["name"])) {
        $target_dir = "images/";
        $uploadedImages = [];
        
        $fileCount = count($_FILES["roomImages"]["name"]);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES["roomImages"]["error"][$i] == 0) {
                $fileName = basename($_FILES["roomImages"]["name"][$i]);
                $newFileName = "room_" . time() . "_" . uniqid() . "_" . $fileName;
                $target_file = $target_dir . $newFileName;
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // check if image is an actual image
                $check = getimagesize($_FILES["roomImages"]["tmp_name"][$i]);
                if ($check === false) {
                    continue;
                }

                // check file size (Limit to 5MB)
                if ($_FILES["roomImages"]["size"][$i] > 5000000) {
                    continue;
                }

                // allow certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                    continue;
                }

                // Try to upload
                if (move_uploaded_file($_FILES["roomImages"]["tmp_name"][$i], $target_file)) {
                    $uploadedImages[] = $target_file;
                }
            }
        }
        
        // Insert new images into RoomImages table
        if (!empty($uploadedImages)) {
            $imgSql = "INSERT INTO RoomImages (Rooms_roomID, imagePath, is_featured) VALUES (?, ?, ?)";
            $imgStmt = $conn->prepare($imgSql);
            
            if ($imgStmt) {
                foreach ($uploadedImages as $imgPath) {
                    $isFeatured = 0; // New images are not featured by default
                    $imgStmt->bind_param("isi", $id, $imgPath, $isFeatured);
                    $imgStmt->execute();
                }
                $imgStmt->close();
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