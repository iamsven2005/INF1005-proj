<?php
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../inc/functions.php';

list($db_host, $db_user, $db_pass, $db_name, $stripekey) = getDBEnvVar();

date_default_timezone_set('Asia/Singapore');

// Define all available timeslots (24-hour format for database)
$all_timeslots = array(
    "09:00:00", "10:30:00", "12:00:00", 
    "13:30:00", "15:00:00", "16:30:00", 
    "18:00:00", "19:30:00", "21:00:00"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $date = isset($_POST['date']) ? $_POST['date'] : null;
    $room_id = $_SESSION['room_id'] ?? null;

    // Validate date format (YYYY-MM-DD)
    if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {

        // remove past timings if date is current date
        if ($date === date("Y-m-d")) {
            $current_time = date("H:i:s");

            $all_timeslots = array_filter($all_timeslots, function($slot) use ($current_time) {
                return $slot > $current_time;
            });
        }

        try {

            if (!$room_id) {
                throw new Exception("No room ID provided.");
            }

            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Clean up expired holds first
            $cleanup = $conn->prepare("DELETE FROM BookingHolding WHERE expires_at < NOW()");
            if (!$cleanup->execute()) {
                error_log("Failed to remove expired holds: " . $cleanup->error);
            }
            $cleanup->close();

            // Get booked timeslots
            $stmt = $conn->prepare("
                SELECT bookingTimeslot FROM Bookings 
                WHERE bookingDate = ? 
                AND Rooms_roomID = ?
                AND bookingStatus IN ('Confirmed', 'Completed')
            ");
            $stmt->bind_param("si", $date, $room_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to get booked time slots: " . $stmt->error);
            }
            $result = $stmt->get_result();

            $booked_slots = array();
            while ($row = $result->fetch_assoc()) {
                $booked_slots[] = $row['bookingTimeslot'];
            }
            $stmt->close();

            // Get held slots (by other users)
            $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
            if (!$current_user_id) {
                throw new Exception("User not logged in.");
            }
            
            $held_stmt = $conn->prepare("
                SELECT holdTimeslot 
                FROM BookingHolding 
                WHERE holdDate = ? 
                AND Rooms_roomID = ? 
                AND expires_at > NOW()
                AND Users_userID != ?
            ");

            $held_stmt->bind_param("sii", $date, $room_id, $current_user_id); 
            $held_stmt->execute();
            $held_result = $held_stmt->get_result();
            
            $held_slots = array();
            while ($row = $held_result->fetch_assoc()) {
                $held_slots[] = $row['holdTimeslot'];
            }
            $held_stmt->close();

            $conn->close();

            // Combine booked and held slots
            $unavailable_slots = array_merge($booked_slots, $held_slots);

            // Calculate available timeslots
            $available_slots = array_diff($all_timeslots, $unavailable_slots);

            // Convert to 12-hour format for display
            $display_slots = array();
            foreach ($available_slots as $slot) {
                $time = DateTime::createFromFormat('H:i:s', $slot);
                if ($time) {
                    $display_slots[] = $time->format('g:i A');
                }
            }

            // Re-index array
            $display_slots = array_values($display_slots);

            if (count($display_slots) === 0) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'No Available Slots.'
                ));
                exit();
            }

            // Return success response
            echo json_encode(array(
                'success' => true,
                'date' => $date,
                'available_slots' => $display_slots,
                'total_available' => count($display_slots),
                'message' => 'Timeslots retrieved successfully'
            ));

        } catch (Exception $e) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ));
        }

    } else {
        echo json_encode(array(
            'success' => false,
            'message' => 'Invalid date format. Expected YYYY-MM-DD'
        ));
    }

} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ));
}
?>