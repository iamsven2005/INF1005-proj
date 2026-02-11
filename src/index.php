<?php

include "inc/secure_session_start.php";

include "inc/functions.php";
$conn = getDbConnection();

//variable to hold list of rooms and number of rooms
$rooms = [];
$roomCount = 0;

$sql = "SELECT roomID, roomName, roomFearLevel, roomDifficulty, roomExperienceType, roomGenre, imagePath FROM Rooms";
$result = $conn->query($sql);

//check for results
if ($result && $result->num_rows > 0) {
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
    $roomCount = $result->num_rows;
}

//close connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Escapy</title>
    <?php include "inc/head.inc.php" ?>
</head>

<body>
    <?php include "inc/nav.inc.php" ?>
    <?php include "inc/header.inc.php" ?>

    <!-- search bar -->
    <div class="search-section section-gap text-center">
        <div class="input-group rounded mx-auto w-100 w-sm-75 w-md-50" style="max-width:480px; padding-top:20px; padding-bottom:20px;">
            <input type="search" id="search" class="form-control rounded" onkeyup="filterRooms()" placeholder="Search for rooms..." aria-label="Search"
                aria-describedby="search-addon">
            <button type="button" class="btn btn-outline-primary" id="search-addon">Search</button>
        </div>
    </div>

    <main class="page-content section-gap">
        <div class="container">

            <!-- filter -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="mb-3">Find Your Perfect Challenge</h2>
                <button class="btn btn-outline-secondary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse"
                        aria-expanded="true"
                        aria-controls="filterCollapse"
                        id="filterToggleBtn">
                    Hide filters
                </button>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="filter-box">
                <div class="mb-3">
                    <h3>Fear Factor</h3>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fear" value="all" id="fearAll" checked>
                        <label class="form-check-label" for="fearAll">All</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fear" value="very-scary" id="fearVeryScary">
                        <label class="form-check-label" for="fearVeryScary">Very Scary</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fear" value="scary" id="fearScary">
                        <label class="form-check-label" for="fearScary">Scary</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fear" value="mildly-scary"
                            id="fearMildlyScary">
                        <label class="form-check-label" for="fearMildlyScary">Mildly Scary</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fear" value="not-scary" id="fearNotScary">
                        <label class="form-check-label" for="fearNotScary">Not Scary</label>
                    </div>
                </div>



                <div class="mb-3">
                    <h3>Experience Type</h3>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="actor" value="all" id="actorAll" checked>
                        <label class="form-check-label" for="actorAll">All</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="actor" value="live-actor" id="actorLive">
                        <label class="form-check-label" for="actorLive">Live Actor</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="actor" value="no-live-actor" id="actorNoLive">
                        <label class="form-check-label" for="actorNoLive">No Live Actor</label>
                    </div>
                </div>

                <div class="mb-3">
                    <h3>Difficulty</h3>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="difficulty" value="all" id="all-difficulty" checked>
                        <label class="form-check-label" for="all-difficulty">All</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="difficulty" value="easy" id="easy-difficulty">
                        <label class="form-check-label" for="easy-difficulty">Easy</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="difficulty" value="medium" id="medium-difficulty">
                        <label class="form-check-label" for="medium-difficulty">Medium</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="difficulty" value="hard" id="hard-difficulty">
                        <label class="form-check-label" for="hard-difficulty">Hard</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="difficulty" value="very-hard" id="very-hard-difficulty">
                        <label class="form-check-label" for="very-hard-difficulty">Very-Hard</label>
                    </div>
                </div>

                <div class="mb-3">
                    <h3>Genre</h3>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" value="horror" id="genreHorror">
                        <label class="form-check-label" for="genreHorror">Horror</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" value="thriller" id="genreThriller">
                        <label class="form-check-label" for="genreThriller">Thriller</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" value="fantasy" id="genreFantasy">
                        <label class="form-check-label" for="genreFantasy">Fantasy</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" value="adventure" id="genreAdventure">
                        <label class="form-check-label" for="genreAdventure">Adventure</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" value="mystery" id="Mystery">
                        <label class="form-check-label" for="Mystery">Mystery</label>
                    </div>
                </div>
            </div>
  </div>
            <!-- replaced static room count with dynamic allocation -->
            <section id="Rooms">
                <h4 class="mb-3" style="padding-top: 40px;">Rooms</h4>
                <div class="text-center my-4">
                    <p class="text-muted">Showing <?php echo $roomCount; ?> rooms</p>
                </div>

                <div class="row g-4" id="roomContainer">

                    <!-- rooms (no mroe hard coding) -->
                    <!-- if filter derives no results -->

                    <?php if (!empty($rooms)): ?>

                        <?php foreach ($rooms as $room):
                            //sanitise data attributes for filter script
                            $dataFear = slugify($room['roomFearLevel']);
                            $dataActor = slugify($room['roomExperienceType']);
                            $dataGenre = slugify($room['roomGenre']);
                            $dataDifficulty = slugify($room['roomDifficulty']);
                        ?>

                            <!-- in built data to make filter easier -->
                            <div class="col-md-4 room-card"
                                data-fear="<?php echo $dataFear; ?>"
                                data-actor="<?php echo $dataActor; ?>"
                                data-genre="<?php echo $dataGenre; ?>"
                                data-difficulty="<?php echo $dataDifficulty ?>"
                                data-title="<?php echo htmlspecialchars($room['roomName']); ?>">

                                <div class="card">
                                    <img src="<?php echo htmlspecialchars(str_replace(' ', '%20', $room['imagePath'] ?? '/images/placeholder.png')); ?>"
                                        class="card-img-top" alt="<?php echo htmlspecialchars($room['roomName']); ?>">


                                    <div class="card-body">
                                        <span class="badge <?php echo getBadgeColor($room['roomFearLevel']); ?>">
                                            <?php echo htmlspecialchars($room['roomFearLevel']); ?>
                                        </span>

                                        <span class="badge <?php echo getDifficultyColor($room['roomDifficulty']); ?>">
                                            <?php echo htmlspecialchars($room['roomDifficulty']); ?>
                                        </span>

                                        <span class="badge <?php echo getExperienceColor($room['roomExperienceType']); ?>">
                                            <?php echo htmlspecialchars($room['roomExperienceType']); ?>
                                        </span>

                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($room['roomGenre']); ?>
                                        </span>

                                        <h5 class="card-title mt-2"><?php echo htmlspecialchars($room['roomName']); ?></h5>

                                         <a href="room.php?name=<?php echo urlencode($room['roomName']); ?>" 
                                           class="stretched-link" 
                                           aria-label="Go to <?php echo htmlspecialchars($room['roomName']); ?>" 
                                           title=""></a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="noResultsMessage" class="col-12 text-center" style="display: none; padding-top: 50px; padding-bottom: 50px;">
                    <p class="h5">No rooms matching your criteria were found.</p>
                </div>
            </section>
        </div>
    </main>
    <?php include "inc/footer.inc.php" ?>
</div>

</body>

</html>
