<?php
// this page is also the main managing page for admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

// security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$conn = getDbConnection();
$rooms = [];

// sorting logic (default: newest first)
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$sql = "SELECT roomID, roomName, roomDifficulty, imagePath FROM Rooms";

// appended when the option is selected
switch ($sort) {
    case 'name_asc':
        $sql .= " ORDER BY roomName ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY roomName DESC";
        break;
    case 'difficulty':
        $sql .= " ORDER BY roomDifficulty ASC";
        break;
    case 'oldest':
        $sql .= " ORDER BY roomID ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY roomID DESC"; // default
        break;
}

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
}

$sql = "SELECT 
            b.bookingID,
            b.bookingRef,
            b.bookingDate,
            b.bookingTimeslot,
            b.numPlayers,
            b.totalPrice,
            b.bookingStatus,
            b.created_at,
            r.roomName,
            u.username,
            u.email
        FROM Bookings b
        JOIN Rooms r ON b.Rooms_roomID = r.roomID
        JOIN Users u ON b.Users_userID = u.userID
        ORDER BY b.created_at DESC";

$result = $conn->query($sql);
$bookings = [];

if ($result && $result->num_rows > 0) {
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Manage Rooms - Escape Quest</title>
    <?php include "inc/head.inc.php" ?>
    <link rel="stylesheet" href="css/rooms.css">
</head>

<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="page-content section-gap">
        <div class="container">

            <!-- success messages -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> The room has been deleted.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> The room details have been updated.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h2 class="text-center mb-4 text-warning">Manage Rooms</h2>

            <!-- search and sort! -->
            <div class="row g-3 justify-content-center mb-4">

                <!-- search -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-light border-secondary">Search:</span>
                        <input type="text" id="adminSearchInput" class="form-control bg-dark text-light border-secondary" onkeyup="filterTable()" placeholder="Search by name or ID..." aria-label="Search">
                    </div>
                </div>

                <!-- sorting -->
                <div class="col-md-3">
                    <form action="delete_room.php" method="GET">
                        <select name="sort" class="form-select bg-dark text-light border-secondary" onchange="this.form.submit()">
                            <option value="newest" <?php if ($sort == 'newest') echo 'selected'; ?>>Newest First</option>
                            <option value="oldest" <?php if ($sort == 'oldest') echo 'selected'; ?>>Oldest First</option>
                            <option value="name_asc" <?php if ($sort == 'name_asc') echo 'selected'; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php if ($sort == 'name_desc') echo 'selected'; ?>>Name (Z-A)</option>
                            <option value="difficulty" <?php if ($sort == 'difficulty') echo 'selected'; ?>>Difficulty</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- list of rooms -->
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle" id="roomsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Room Name</th>
                            <th>Difficulty</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rooms)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">No rooms found in database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rooms as $room): ?>
                                <!-- class 'searchable-row' enables the JS search -->
                                <tr class="searchable-row">
                                    <td class="fw-bold text-warning"><?php echo $room['roomID']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars(str_replace(' ', '%20', $room['imagePath'] ?? 'images/placeholder.png')); ?>" alt="Thumbnail" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($room['roomName']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getDifficultyColor($room['roomDifficulty']); ?>">
                                            <?php echo htmlspecialchars($room['roomDifficulty']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <!-- edit and delete buttons -->
                                        <a href="edit_room.php?name=<?php echo urlencode($room['roomName']); ?>"
                                            class="btn btn-primary btn-sm me-2">Edit</a>

                                        <form action="process_delete_room.php" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete \'<?php echo htmlspecialchars($room['roomName']); ?>\'? This cannot be undone.');"
                                            style="display: inline-block;">
                                            <input type="hidden" name="roomID" value="<?php echo $room['roomID']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- no results message -->
                <div id="noAdminResults" class="text-center py-5" style="display: none;">
                    <h4 class="text-muted">No rooms match your search.</h4>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="create_room.php" class="btn btn-success me-2">Create New Room</a>
                <a href="index.php" class="btn btn-outline-light">Back to Home</a>
            </div>
        </div>
        <h2 class="text-center mb-4 text-warning">Manage Bookings</h2>

<div class="table-responsive container">
    <table class="table table-dark table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Reference</th>
                <th>Room</th>
                <th>User</th>
                <th>Date</th>
                <th>Time</th>
                <th>Players</th>
                <th>Total ($)</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="10" class="text-center py-4">
                        No bookings found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $booking['bookingID']; ?></td>
                        <td><?= htmlspecialchars($booking['bookingRef']); ?></td>
                        <td><?= htmlspecialchars($booking['roomName']); ?></td>
                        <td>
                            <?= htmlspecialchars($booking['username']); ?><br>
                            <small><?= htmlspecialchars($booking['email']); ?></small>
                        </td>
                        <td><?= $booking['bookingDate']; ?></td>
                        <td><?= $booking['bookingTimeslot']; ?></td>
                        <td><?= $booking['numPlayers']; ?></td>
                        <td>$<?= $booking['totalPrice']; ?></td>
                        <td>
                            <?php if ($booking['bookingStatus'] == 'Confirmed'): ?>
                                <span class="badge bg-success">Confirmed</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $booking['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

    </main>

    <?php include "inc/footer.inc.php" ?>
</body>

</html>