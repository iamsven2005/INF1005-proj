<?php
ob_start();
// 1. Start the session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

// security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit(); 
}


// check if form is submitted or not
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $conn = getDbConnection();
    $success = true;
    $errorMsg = "";

    // sanitise inputs
    $name = sanitize_input($_POST['roomName']);
    $desc = sanitize_input($_POST['roomDescription']);
    $location = sanitize_input($_POST['roomLocation']);

    // int
    $duration = (int)$_POST['roomDuration'];
    $min = (int)$_POST['roomMin'];
    $max = (int)$_POST['roomMax'];

    // float
    $priceOff = (float)$_POST['roomPriceOffpeak'];
    $pricePeak = (float)$_POST['roomPricePeak'];

    // drop down menu so can select from these 
    $difficulty = $_POST['roomDifficulty'];
    $fear = $_POST['roomFearLevel'];
    $genre = $_POST['roomGenre'];
    $expType = $_POST['roomExperienceType'];

    // image upload
    $target_dir = "images/";
    $imagePath = "images/placeholder.png"; // Default fallback
    
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


    if (isset($_FILES["roomImage"]) && $_FILES["roomImage"]["error"] == 0) {

        $fileName = basename($_FILES["roomImage"]["name"]);
        // unique filename to avoid overwrites (e.g., room_timestamp_filename.jpg)
        $newFileName = "room_" . time() . "_" . $fileName;
        $target_file = $target_dir . $newFileName;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // check if image is an actual image
        $check = getimagesize($_FILES["roomImage"]["tmp_name"]);
        if ($check === false) {
            $errorMsg = "File is not an image.";
            $uploadOk = 0;
        }

        // check file size (Limit to 5MB)
        if ($_FILES["roomImage"]["size"] > 5000000) {
            $errorMsg = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png") {
            $errorMsg = "Sorry, only JPG, & PNG files are allowed.";
            $uploadOk = 0;
        }

        // Try to upload
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["roomImage"]["tmp_name"], $target_file)) {
                // Success! Store the path relative to root
                $imagePath = $target_file;
            } else {
                $errorMsg = "Sorry, there was an error uploading your file.";
            }
        } else {
            $success = false;
        }
    }

    // prepare statement to insert into db
    if ($success) {
        $sql = "INSERT INTO Rooms (roomName, roomDescription, roomMax, roomMin, roomDuration, roomDifficulty, roomLocation, roomFearLevel, roomExperienceType, roomGenre, roomPricePeak, roomPriceOffpeak, imagePath) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param(
                "ssiiisssssdds",
                $name,
                $desc,
                $max,
                $min,
                $duration,
                $difficulty,
                $location,
                $fear,
                $expType,
                $genre,
                $pricePeak,
                $priceOff,
                $imagePath
            );

            if ($stmt->execute()) {
                // redirect to created room
                $newID = $stmt->insert_id;
                header("Location: room.php?name=" . urlencode($name));
                exit();
            } else {
                $errorMsg = "Database execute failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errorMsg = "Database prepare failed: " . $conn->error;
        }
    }

    $conn->close();

    // error message
    echo "<div style='background-color: #333; color: white; padding: 20px; text-align: center;'>";
    echo "<h3>Error Creating Room</h3>";
    echo "<p>$errorMsg</p>";
    echo "<a href='create_room.php' style='color: #f59f00;'>Go Back</a>";
    echo "</div>";
} else {
    header("Location: create_room.php");
    exit();
}
ob_end_flush();
