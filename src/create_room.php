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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Create New Room - Escape Quest</title>
    <?php include "inc/head.inc.php" ?>
    <link rel="stylesheet" href="css/rooms.css">
</head>

<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="page-content section-gap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="pricing-card text-start p-5">
                        <h2 class="text-center mb-4 text-warning">Create a New Room</h2>
                        <p class="text-center text-light mb-4" style="font-size: 0.9rem;">
                            Fields marked with <span class="text-danger">*</span> are required.
                        </p>

                        <form action="process_create_room.php" method="POST" enctype="multipart/form-data">

                            <!-- roomName -->
                            <div class="mb-3">
                                <label for="roomName" class="form-label text-light">Room Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="roomName" name="roomName" required>
                            </div>

                            <!-- roomDescription -->
                            <div class="mb-3">
                                <label for="roomDescription" class="form-label text-light">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="roomDescription" name="roomDescription" rows="4" required></textarea>
                            </div>

                            <!-- roomLocation -->
                            <div class="mb-3">
                                <label for="roomLocation" class="form-label text-light">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="roomLocation" name="roomLocation" placeholder="e.g. Main Street Branch" required>
                            </div>

                            <!-- roomDuration -->
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="roomDuration" class="form-label text-light">Duration (mins) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="roomDuration" name="roomDuration" min="30" max="180" required>
                                </div>

                                <!-- roomMin -->
                                <div class="col-md-4 mb-3">
                                    <label for="roomMin" class="form-label text-light">Min Players <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="roomMin" name="roomMin" min="1" required>
                                </div>

                                <!-- roomMax -->
                                <div class="col-md-4 mb-3">
                                    <label for="roomMax" class="form-label text-light">Max Players <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="roomMax" name="roomMax" min="1" required>
                                </div>
                            </div>

                            <!-- roomPriceOffPeak and roomPricePeak -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="roomPriceOffpeak" class="form-label text-light">Off-Peak Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="roomPriceOffpeak" name="roomPriceOffpeak" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="roomPricePeak" class="form-label text-light">Peak Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="roomPricePeak" name="roomPricePeak" required>
                                </div>
                            </div>

                            <hr class="border-secondary my-4">

                            <!-- roomDifficulty, roomFearLevel, roomGenre, roomExperienceType -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Difficulty <span class="text-danger">*</span></label>
                                    <select class="form-select" name="roomDifficulty" required>
                                        <option value="Easy">Easy</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Hard">Hard</option>
                                        <option value="Very Hard">Very Hard</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Fear Level <span class="text-danger">*</span></label>
                                    <select class="form-select" name="roomFearLevel" required>
                                        <option value="Not Scary">Not Scary</option>
                                        <option value="Mildly Scary">Mildly Scary</option>
                                        <option value="Scary">Scary</option>
                                        <option value="Very Scary">Very Scary</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Genre <span class="text-danger">*</span></label>
                                    <select class="form-select" name="roomGenre" required>
                                        <option value="Horror">Horror</option>
                                        <option value="Thriller">Thriller</option>
                                        <option value="Fantasy">Fantasy</option>
                                        <option value="Adventure">Adventure</option>
                                        <option value="Mystery">Mystery</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-light">Experience Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="roomExperienceType" required>
                                        <option value="No Live Actor">No Live Actor</option>
                                        <option value="Live Actor">Live Actor</option>
                                    </select>
                                </div>
                            </div>

                            <!-- image upload -->
                            <div class="mb-4">
                                <label for="roomImage" class="form-label text-light">Room Image (Optional)</label>
                                <!-- restrict to PNG and JPG only -->
                                <input class="form-control" type="file" id="roomImage" name="roomImage" accept=".jpg, .png">
                                <div class="form-text text-light">Accepted formats: JPG, PNG. Max size: 5MB.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="book-btn">Create Room</button>
                                <a href="index.php" class="btn btn-outline-light">Cancel</a>
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