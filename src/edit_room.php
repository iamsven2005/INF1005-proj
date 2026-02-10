<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

// security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// get room details
$room = null;
if (isset($_GET['name'])) {
    $conn = getDBconnection();

    // sanitize input
    $name = sanitize_input($_GET['name']);

    $stmt = $conn->prepare("SELECT * FROM Rooms WHERE roomName = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
    }
    $conn->close();
}

// simple check if room exists
if (!$room) {
    echo "<h2 style='color: white; text-align: center; margin-top: 50px;'>Room not found.</h2>";
    echo "<div style='text-align: center;'><a href='delete_room.php' style='color: #f59f00;'>Back to Manager</a></div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Room - Escape Quest</title>
    <?php include "inc/head.inc.php" ?>
    <link rel="stylesheet" href="css/rooms.css">
    <script defer src="js/index.js"></script>
</head>

<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="page-content section-gap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="pricing-card text-start p-5">
                        <h2 class="text-center mb-4 text-warning">Edit Room: <?php echo htmlspecialchars($room['roomName']); ?></h2>

                        <p class="text-center text-light mb-4" style="font-size: 0.9rem;">
                            Fields marked with <span class="text-danger">*</span> are required.
                        </p>

                        <form action="process_edit_room.php" method="POST" enctype="multipart/form-data">

                            <input type="hidden" name="roomID" value="<?php echo $room['roomID']; ?>">

                            <!-- roomName -->
                            <div class="mb-3">
                                <label class="form-label text-light">Room Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="roomName" value="<?php echo htmlspecialchars($room['roomName']); ?>" required>
                            </div>

                            <!-- roomDescription -->
                            <div class="mb-3">
                                <label class="form-label text-light">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="roomDescription" rows="4" required><?php echo htmlspecialchars($room['roomDescription']); ?></textarea>
                            </div>

                            <!-- roomLocation -->
                            <div class="mb-3">
                                <label class="form-label text-light">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="roomLocation" value="<?php echo htmlspecialchars($room['roomLocation']); ?>" required>
                            </div>

                            <!-- roomDuration -->
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-light">Duration (mins) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="roomDuration" value="<?php echo $room['roomDuration']; ?>" min="30" max="180" required>
                                </div>

                                <!-- roomMin -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-light">Min Players <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="roomMin" value="<?php echo $room['roomMin']; ?>" min="1" required>
                                </div>

                                <!-- roomMax -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-light">Max Players <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="roomMax" value="<?php echo $room['roomMax']; ?>" min="1" required>
                                </div>
                            </div>

                            <!-- roomPriceOffPeak and roomPricePeak -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Off Peak Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="roomPriceOffpeak" value="<?php echo $room['roomPriceOffPeak']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Peak Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="roomPricePeak" value="<?php echo $room['roomPricePeak']; ?>" required>
                                </div>
                            </div>

                            <hr class="border-secondary my-4">

                            <!-- roomDifficulty, roomFearLevel, roomGenre, roomExperienceType -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Difficulty <span class="text-danger">*</span></label>
                                    <select class="form-select" name="roomDifficulty" required>
                                        <?php
                                        $options = ['Easy', 'Medium', 'Hard', 'Very Hard'];
                                        foreach ($options as $opt) {
                                            $selected = ($room['roomDifficulty'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $selected>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Fear Level <span class="text-danger">*</span></label>
                                    <select class="form-select" name="roomFearLevel" required>
                                        <?php
                                        $options = ['Not Scary', 'Mildly Scary', 'Scary', 'Very Scary'];
                                        foreach ($options as $opt) {
                                            $selected = ($room['roomFearLevel'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $selected>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Genre <span class="text-danger">*</span></label>
                                    <select class="form-select" name="roomGenre" required>
                                        <?php
                                        $options = ['Horror', 'Thriller', 'Fantasy', 'Adventure', 'Mystery'];
                                        foreach ($options as $opt) {
                                            $selected = ($room['roomGenre'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $selected>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Experience Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="roomExperienceType" required>
                                        <?php
                                        $options = ['No Live Actor', 'Live Actor'];
                                        foreach ($options as $opt) {
                                            $selected = ($room['roomExperienceType'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $selected>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- image update -->
                            <div class="mb-4">
                                <label class="form-label text-light">Update Image (Leave blank to keep current)</label>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($room['imagePath']); ?>" alt="Current Image" style="height: 100px; border-radius: 4px; border: 1px solid #555;">
                                    <small class="text-muted d-block mt-1">Current: <?php echo basename($room['imagePath']); ?></small>
                                </div>
                                <input class="form-control" type="file" name="roomImage" accept=".jpg, .png">
                                <div class="form-text text-light">Accepted formats: JPG, PNG. Max size: 5MB.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="book-btn">Save Changes</button>
                                <a href="delete_room.php" class="btn btn-outline-light">Cancel</a>
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