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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bannerID'])) {
    $bannerID = (int)$_POST['bannerID'];

    $conn = getDbConnection();
    $tableCheck = $conn->query("SHOW TABLES LIKE 'PromotionalBanners'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        $conn->close();
        header("Location: manage_banners.php?error=missing_table");
        exit();
    }

    $sql = "DELETE FROM PromotionalBanners WHERE bannerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bannerID);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: manage_banners.php?msg=deleted");
    exit();
}

header("Location: manage_banners.php");
exit();
