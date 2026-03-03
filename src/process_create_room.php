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

    // image upload handling for multiple images
    $target_dir = "images/";
    $imagePath = "images/placeholder.png"; // Default fallback
    $uploadedImages = []; // Store paths of successfully uploaded images
    
    // Handle multiple file uploads if files are provided
    if (isset($_FILES["roomImages"]) && is_array($_FILES["roomImages"]["name"])) {
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
                    $errorMsg = "File $fileName is not a valid image.";
                    $uploadOk = 0;
                }

                // check file size (Limit to 5MB)
                if ($_FILES["roomImages"]["size"][$i] > 5000000) {
                    $errorMsg = "File $fileName is too large. Max size: 5MB.";
                    $uploadOk = 0;
                }

                // allow certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                    $errorMsg = "File $fileName: Only JPG and PNG files are allowed.";
                    $uploadOk = 0;
                }

                // Try to upload
                if ($uploadOk == 1) {
                    if (move_uploaded_file($_FILES["roomImages"]["tmp_name"][$i], $target_file)) {
                        $uploadedImages[] = $target_file;
                        // Set first image as featured/primary image
                        if (count($uploadedImages) == 1) {
                            $imagePath = $target_file;
                        }
                    } else {
                        $errorMsg = "Error uploading file $fileName. Please try again.";
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }
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
                // Get the inserted room ID
                $newID = $stmt->insert_id;
                
                // Insert uploaded images into RoomImages table
                if (!empty($uploadedImages)) {
                    $imgSql = "INSERT INTO RoomImages (Rooms_roomID, imagePath, is_featured) VALUES (?, ?, ?)";
                    $imgStmt = $conn->prepare($imgSql);
                    
                    if ($imgStmt) {
                        foreach ($uploadedImages as $index => $imgPath) {
                            $isFeatured = ($index === 0) ? 1 : 0; // First image is featured
                            $imgStmt->bind_param("isi", $newID, $imgPath, $isFeatured);
                            $imgStmt->execute();
                        }
                        $imgStmt->close();
                    }
                }
                
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
