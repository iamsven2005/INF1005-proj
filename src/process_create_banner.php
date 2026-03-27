<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_banners.php");
    exit();
}

$title = sanitize_input($_POST['title'] ?? '');
$subtitle = sanitize_input($_POST['subtitle'] ?? '');
$imagePath = sanitize_input($_POST['imagePath'] ?? '');
$ctaText = sanitize_input($_POST['ctaText'] ?? '');
$ctaUrl = sanitize_input($_POST['ctaUrl'] ?? '');
$startDate = !empty($_POST['startDate']) ? $_POST['startDate'] : null;
$endDate = !empty($_POST['endDate']) ? $_POST['endDate'] : null;
$isActive = isset($_POST['is_active']) ? 1 : 0;

if ($title === '') {
    header("Location: manage_banners.php");
    exit();
}

if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
    header("Location: manage_banners.php?error=invalid_dates");
    exit();
}

$conn = getDbConnection();
$tableCheck = $conn->query("SHOW TABLES LIKE 'PromotionalBanners'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    $conn->close();
    header("Location: manage_banners.php?error=missing_table");
    exit();
}

$sql = "INSERT INTO PromotionalBanners (title, subtitle, imagePath, ctaText, ctaUrl, startDate, endDate, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", $title, $subtitle, $imagePath, $ctaText, $ctaUrl, $startDate, $endDate, $isActive);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: manage_banners.php?msg=created");
exit();
