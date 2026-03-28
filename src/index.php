<?php

include "inc/secure_session_start.php";

include "inc/functions.php";
$conn = getDbConnection();

//variable to hold list of rooms and number of rooms
$rooms = [];
$roomCount = 0;
$promoBanners = [];

$sql = "SELECT 
            r.roomID, 
            r.roomName, 
            r.roomFearLevel, 
            r.roomDifficulty, 
            r.roomExperienceType, 
            r.roomGenre, 
            COALESCE(
                (SELECT imagePath FROM RoomImages 
                 WHERE Rooms_roomID = r.roomID AND is_featured = 1 LIMIT 1),
                (SELECT imagePath FROM RoomImages 
                 WHERE Rooms_roomID = r.roomID LIMIT 1),
                r.imagePath
            ) as imagePath,
            COALESCE((SELECT COUNT(*) FROM Reviews WHERE Rooms_roomID = r.roomID), 0) as review_count,
            COALESCE((SELECT AVG(rating) FROM Reviews WHERE Rooms_roomID = r.roomID), 0) as avg_rating
        FROM Rooms r
        ORDER BY r.roomID";
$result = $conn->query($sql);

//check for results
if ($result && $result->num_rows > 0) {
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
    $roomCount = $result->num_rows;
}

// Defensive check so homepage still loads if migrations are not applied yet.
$bannerTableExists = $conn->query("SHOW TABLES LIKE 'PromotionalBanners'");
if ($bannerTableExists && $bannerTableExists->num_rows > 0) {
        $bannerSql = "SELECT bannerID, title, subtitle, imagePath, ctaText, ctaUrl, startDate, endDate
                                    FROM PromotionalBanners
                                    WHERE is_active = 1
                                        AND (startDate IS NULL OR startDate <= CURDATE())
                                        AND (endDate IS NULL OR endDate >= CURDATE())
                                    ORDER BY created_at DESC";
        $bannerResult = $conn->query($bannerSql);
        if ($bannerResult && $bannerResult->num_rows > 0) {
                $promoBanners = $bannerResult->fetch_all(MYSQLI_ASSOC);
        }
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

    <?php if (!empty($promoBanners)): ?>
    <section class="container mt-4 mb-2" aria-label="Special Event Promotions">
        <?php foreach ($promoBanners as $banner): ?>
            <?php
                $bannerStyle = "";
                if (!empty($banner['imagePath'])) {
                    $safeImage = htmlspecialchars(str_replace(' ', '%20', $banner['imagePath']));
                    $bannerStyle = "background-image: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('" . $safeImage . "');";
                }
            ?>
            <article class="promo-banner mb-3" style="<?php echo $bannerStyle; ?>">
                <div class="promo-banner-content">
                    <p class="promo-banner-tag mb-2">Special Event</p>
                    <h2 class="h4 mb-2"><?php echo htmlspecialchars($banner['title']); ?></h2>
                    <?php if (!empty($banner['subtitle'])): ?>
                        <p class="mb-3"><?php echo htmlspecialchars($banner['subtitle']); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($banner['ctaText']) && !empty($banner['ctaUrl'])): ?>
                        <a href="<?php echo htmlspecialchars($banner['ctaUrl']); ?>" class="btn btn-danger">
                            <?php echo htmlspecialchars($banner['ctaText']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

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
                        aria-expanded="false"
                        aria-controls="filterCollapse"
                        id="filterToggleBtn">
                    Show filters
                </button>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="filter-box">
                
                <div class="mb-3">
                    <h3>Date</h3>
                    <input type="date" id="dateFilter" class="form-control date-filter-input">
                </div>

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
                    <p class="text-muted" id="roomCountText">Showing <?php echo $roomCount; ?> rooms</p>
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
                                data-room-id="<?php echo $room['roomID']; ?>"
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

                                        <h5 class="card-title mt-2 mb-1"><?php echo htmlspecialchars($room['roomName']); ?></h5>
                                        
                                        <div class="mb-2 d-flex align-items-center">
                                            <span class="fw-bold me-2">
                                                <?php echo number_format((float)($room['avg_rating'] ?? 0), 1); ?>
                                            </span>
                                            
                                            <span class="text-warning me-1">
                                                <?php
                                                $avgRating = (float)($room['avg_rating'] ?? 0);
                                                
                                                // Get the whole number for full stars
                                                $fullStars = (int)floor($avgRating);
                                                
                                                // If there's any decimal remaining, it counts as a half star
                                                $hasHalfStar = ($avgRating - $fullStars) > 0;
                                                
                                                // Calculate remaining empty stars (total 5)
                                                $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                                
                                                // Print full stars
                                                echo str_repeat('&#9733;', $fullStars);
                                                
                                                // Print exact half star using CSS overlay
                                                if ($hasHalfStar) {
                                                    echo '<span style="position: relative; display: inline-block;">';
                                                    echo '&#9734;'; // Background empty star
                                                    echo '<span style="position: absolute; left: 0; top: 0; width: 50%; overflow: hidden;">&#9733;</span>'; // Overlay exact half full star
                                                    echo '</span>';
                                                }
                                                
                                                // Print empty stars
                                                echo str_repeat('&#9734;', $emptyStars); 
                                                ?>
                                            </span>
                                            
                                            <span class="small">
                                                (<?php echo (int)($room['review_count'] ?? 0); ?>)
                                            </span>
                                        </div>

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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        let filterCollapse = document.getElementById('filterCollapse');
        let filterToggleBtn = document.getElementById('filterToggleBtn');
        
        if (filterCollapse && filterToggleBtn) {
            filterCollapse.addEventListener('show.bs.collapse', function () {
                filterToggleBtn.textContent = 'Hide filters';
            });
            filterCollapse.addEventListener('hide.bs.collapse', function () {
                filterToggleBtn.textContent = 'Show filters';
            });
        }
        
        
        let dateInput = document.getElementById('dateFilter');
        if(dateInput) {
            let now = new Date();
            let effectiveDate = new Date(now);
            
            // Check if it's 9 PM (21:00) or later
            if (now.getHours() >= 21) {
                // Add 1 day to make the effective date tomorrow
                effectiveDate.setDate(effectiveDate.getDate() + 1);
            }
            
            let maxDate = new Date(effectiveDate);
            maxDate.setMonth(effectiveDate.getMonth() + 3); // Up to 3 months in future
            
            let tzoffset = effectiveDate.getTimezoneOffset() * 60000; 
            let effectiveISO = (new Date(effectiveDate.getTime() - tzoffset)).toISOString().split('T')[0];
            let maxISO = (new Date(maxDate.getTime() - tzoffset)).toISOString().split('T')[0];
            
            dateInput.min = effectiveISO;
            dateInput.max = maxISO;
            dateInput.value = effectiveISO;
            
            dateInput.addEventListener('change', function() {
                checkRoomAvailability(this.value);
            });
            
            // Check immediately on load using the calculated effective date
            checkRoomAvailability(effectiveISO);
        }

        // Hook into your existing filters and search bar to update the count dynamically
        document.querySelectorAll('input[name="fear"], input[name="actor"], input[name="difficulty"], input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', () => setTimeout(updateRoomCount, 50));
        });
        
        let searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('keyup', () => setTimeout(updateRoomCount, 50));
        }
    });

    function checkRoomAvailability(dateStr) {
        // Append the date parameter to the room links
        document.querySelectorAll('.room-card a.stretched-link').forEach(link => {
            let url = new URL(link.href, window.location.origin);
            url.searchParams.set('date', dateStr);
            link.href = url.toString();
        });

        // Query the API
        fetch('api/api_available_rooms.php?date=' + dateStr)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    let availableRooms = data.available_rooms.map(String);
                    document.querySelectorAll('.room-card').forEach(card => {
                        let roomId = card.getAttribute('data-room-id');
                        if(availableRooms.includes(roomId)) {
                            card.classList.remove('date-filtered-out');
                        } else {
                            card.classList.add('date-filtered-out');
                        }
                    });
                    
                    // Rerun your existing filter logic (if it exists) to make sure combinations work
                    if(typeof filterRooms === 'function') {
                        filterRooms();
                    }
                    
                    // Update the text count after applying the date filter
                    updateRoomCount();
                }
            })
            .catch(error => console.error('Error fetching availability:', error));
    }

    // New function to accurately count visible rooms
    function updateRoomCount() {
        let visibleCount = 0;
        
        document.querySelectorAll('.room-card').forEach(card => {
            // Check if card is hidden by our date filter class OR by your existing filterRooms() display styles
            const isDateHidden = card.classList.contains('date-filtered-out');
            const isStyleHidden = window.getComputedStyle(card).display === 'none';
            
            if (!isDateHidden && !isStyleHidden) {
                visibleCount++;
            }
        });
        
        // Update the text
        let countElement = document.getElementById('roomCountText');
        if (countElement) {
            countElement.textContent = `Showing ${visibleCount} rooms`;
        }
        
        // Toggle the "No results found" message if exactly 0 rooms are visible
        let noResultsMsg = document.getElementById('noResultsMessage');
        if (noResultsMsg) {
            noResultsMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }
    </script>
</body>

</html>
