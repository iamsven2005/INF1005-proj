<?php
ob_start();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../inc/functions.php";

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Check if request is POST and get JSON data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['imageID'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Image ID is required']);
    exit();
}

$imageID = (int)$input['imageID'];
$conn = getDbConnection();

// Get image path before deleting
$stmt = $conn->prepare("SELECT imagePath, Rooms_roomID FROM RoomImages WHERE imageID = ?");
$stmt->bind_param("i", $imageID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Image not found']);
    $conn->close();
    exit();
}

$row = $result->fetch_assoc();
$imagePath = $row['imagePath'];
$roomID = $row['Rooms_roomID'];
$stmt->close();

// Delete the image record from database
$deleteStmt = $conn->prepare("DELETE FROM RoomImages WHERE imageID = ?");
$deleteStmt->bind_param("i", $imageID);

if ($deleteStmt->execute()) {
    $deleteStmt->close();
    
    // Delete the physical file
    if ($imagePath && file_exists($imagePath)) {
        unlink($imagePath);
    }
    
    // If this was the featured image, set the first remaining image as featured
    $checkStmt = $conn->prepare("
        SELECT imageID FROM RoomImages 
        WHERE Rooms_roomID = ? 
        ORDER BY created_at ASC 
        LIMIT 1
    ");
    $checkStmt->bind_param("i", $roomID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $firstImg = $checkResult->fetch_assoc();
        $updateStmt = $conn->prepare("UPDATE RoomImages SET is_featured = 1 WHERE imageID = ?");
        $updateStmt->bind_param("i", $firstImg['imageID']);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $checkStmt->close();
    
    $conn->close();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to delete image']);
    $conn->close();
}

ob_end_flush();
?>
