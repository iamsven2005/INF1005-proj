<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/functions.php';

list($db_host, $db_user, $db_pass, $db_name, $stripekey) = getDBEnvVar();
date_default_timezone_set('Asia/Singapore');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['date'])) {
    $date = $_GET['date'];
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid date']);
        exit;
    }

    $all_timeslots = ["09:00:00", "10:30:00", "12:00:00", "13:30:00", "15:00:00", "16:30:00", "18:00:00", "19:30:00", "21:00:00"];
    
    if ($date === date("Y-m-d")) {
        $current_time = date("H:i:s");
        $all_timeslots = array_filter($all_timeslots, function($slot) use ($current_time) {
            return $slot > $current_time;
        });
    }

    if (empty($all_timeslots)) {
        // Today and all slots passed
        echo json_encode(['success' => true, 'available_rooms' => []]);
        exit;
    }

    $total_slots_per_room = count($all_timeslots);

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        // Clean up expired holds
        $conn->query("DELETE FROM BookingHolding WHERE expires_at < NOW()");
        
        // Count booked and held slots per room for this date
        $slots_list = "'" . implode("','", $all_timeslots) . "'";
        $sql = "
            SELECT r.roomID, 
                   (SELECT COUNT(*) FROM Bookings b WHERE b.Rooms_roomID = r.roomID AND b.bookingDate = ? AND b.bookingStatus IN ('Confirmed', 'Completed') AND b.bookingTimeslot IN ($slots_list)) as booked,
                   (SELECT COUNT(*) FROM BookingHolding h WHERE h.Rooms_roomID = r.roomID AND h.holdDate = ? AND h.expires_at > NOW() AND h.holdTimeslot IN ($slots_list)) as held
            FROM Rooms r
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $date, $date);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $available_rooms = [];
        while ($row = $res->fetch_assoc()) {
            $taken = $row['booked'] + $row['held'];
            if ($total_slots_per_room > $taken) {
                $available_rooms[] = $row['roomID'];
            }
        }
        
        echo json_encode(['success' => true, 'available_rooms' => $available_rooms]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}