<?php 
session_start();

$user_id = $_SESSION['user_id'];
$room_id = $_SESSION['room_id'];
header('Content-Type: application/json');

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../inc/functions.php";

list($db_host, $db_user, $db_pass, $db_name, $stripekey) = getDBEnvVar();

$success = true;
$messages = '';

if (!$user_id || $user_id <= 0) {
    $success = false;
    $messages .= 'Invalid user ID. ';
}

if (!$room_id || $room_id <= 0) {
    $success = false;
    $messages .= "Invalid room ID. ";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ===========================
    // 1. VALIDATE INPUT DATA
    // ===========================
    
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $success = false;
        $messages .= "Invalid date format. ";
    }

    $time = isset($_POST['time']) ? $_POST['time'] : null;
    if (!$time || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
        $success = false;
        $messages .= 'Invalid time format. ';
    }

    if (!$success) {
        echo json_encode(array(
            'success' => false,
            'message' => $messages
        ));
        exit();
    }

    // ===========================
    // 2. HOLD THE SLOT
    // ===========================
    
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Start transaction with proper isolation
        $conn->begin_transaction();

        // Clean up expired holds
        $cleanup_stmt = $conn->prepare("
            DELETE FROM BookingHolding
            WHERE expires_at < NOW()
        ");
        $cleanup_stmt->execute();
        $cleanup_stmt->close();

        // Check if slot is already booked with row-level locking to prevent race conditions
        $check_booking = $conn->prepare("
            SELECT bookingID FROM Bookings 
            WHERE bookingDate = ? 
            AND bookingTimeslot = ? 
            AND Rooms_roomID = ? 
            AND bookingStatus IN ('Confirmed', 'Completed')
            FOR UPDATE
        ");
        $check_booking->bind_param("ssi", $date, $time, $room_id);
        $check_booking->execute();
        $booking_result = $check_booking->get_result();
        $booking_row = $booking_result->fetch_assoc();
        $check_booking->close();
        
        if ($booking_row) {
            $conn->rollback();
            error_log("Hold attempt failed - slot already booked: date=$date, time=$time, room=$room_id, user=$user_id");
            echo json_encode(array(
                'success' => false,
                'message' => 'This time slot is already booked. Please select another time.',
                'conflict_type' => 'booked'
            ));
            exit();
        }

        // Check if this user already holds THIS specific slot with locking
        $check_own_hold = $conn->prepare("
            SELECT holdID, expires_at 
            FROM BookingHolding 
            WHERE holdDate = ? 
            AND holdTimeslot = ? 
            AND Rooms_roomID = ? 
            AND Users_userID = ?
            AND expires_at > NOW()
            FOR UPDATE
        ");
        $check_own_hold->bind_param("ssii", $date, $time, $room_id, $user_id);
        $check_own_hold->execute();
        $own_hold_result = $check_own_hold->get_result();
        $own_hold = $own_hold_result->fetch_assoc();
        $check_own_hold->close();

        // If user already holds this slot, extend the hold to 5 minutes from now
        if ($own_hold) {
            // Update the expiration time
            $new_expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $update_hold = $conn->prepare("
                UPDATE BookingHolding 
                SET expires_at = ? 
                WHERE holdID = ?
            ");
            $update_hold->bind_param("si", $new_expires_at, $own_hold['holdID']);
            $update_hold->execute();
            $update_hold->close();
            
            $conn->commit();
            $conn->close();

            error_log("Hold refreshed for existing hold: hold_id={$own_hold['holdID']}, user=$user_id");
            
            echo json_encode(array(
                'success' => true,
                'message' => 'Your hold has been refreshed for another 5 minutes',
                'hold_id' => $own_hold['holdID'],
                'expires_at' => $new_expires_at,
                'expires_in_seconds' => 300,
                'is_existing_hold' => true,
                'was_refreshed' => true
            ));
            exit();
        }

        // Check if slot is currently held by another user with locking
        $check_hold = $conn->prepare("
            SELECT holdID, Users_userID, expires_at 
            FROM BookingHolding 
            WHERE holdDate = ? 
            AND holdTimeslot = ? 
            AND Rooms_roomID = ? 
            AND expires_at > NOW()
            AND Users_userID != ?
            FOR UPDATE
        ");
        $check_hold->bind_param("ssii", $date, $time, $room_id, $user_id);
        $check_hold->execute();
        $hold_result = $check_hold->get_result();
        $hold_row = $hold_result->fetch_assoc();
        $check_hold->close();
        
        if ($hold_row) {
            $conn->rollback();
            $expires_at = new DateTime($hold_row['expires_at']);
            $now = new DateTime();
            $remaining_seconds = max(0, $expires_at->getTimestamp() - $now->getTimestamp());
            
            error_log("Hold attempt failed - slot held by another user: date=$date, time=$time, room=$room_id, requesting_user=$user_id, holding_user={$hold_row['Users_userID']}");
            
            echo json_encode(array(
                'success' => false,
                'message' => 'This time slot is currently being booked by another user.',
                'conflict_type' => 'held',
                'retry_in_seconds' => $remaining_seconds,
                'suggested_action' => 'Please select a different time slot or wait ' . ceil($remaining_seconds / 60) . ' minute(s) and try again.'
            ));
            exit();
        }

        // Delete any existing holds by this user (for OTHER slots)
        $delete_old_hold = $conn->prepare("
            DELETE FROM BookingHolding 
            WHERE Users_userID = ?
        ");
        $delete_old_hold->bind_param("i", $user_id);
        $delete_old_hold->execute();
        $delete_old_hold->close();

        // Create new hold (expires in 5 minutes)
        $hold_expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        
        $insert_hold = $conn->prepare("
            INSERT INTO BookingHolding
            (holdDate, holdTimeslot, expires_at, Rooms_roomID, Users_userID) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert_hold->bind_param("sssii", $date, $time, $hold_expires_at, $room_id, $user_id);
        
        if (!$insert_hold->execute()) {
            throw new Exception("Failed to create hold: " . $insert_hold->error);
        }

        $hold_id = $conn->insert_id;
        $insert_hold->close();

        // Commit transaction
        $conn->commit();
        $conn->close();

        // ===========================
        // 3. RETURN SUCCESS WITH HOLD ID
        // ===========================
        
        echo json_encode(array(
            'success' => true,
            'message' => 'Slot held successfully for 5 minutes',
            'hold_id' => $hold_id,
            'expires_at' => $hold_expires_at,
            'expires_in_seconds' => 300,
            'is_existing_hold' => false
        ));

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
            $conn->close();
        }
        echo json_encode(array(
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ));
    }

} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ));
}
?>