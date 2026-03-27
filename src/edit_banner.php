<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_banners.php");
    exit();
}

$bannerID = (int)$_GET['id'];
$conn = getDbConnection();
$tableCheck = $conn->query("SHOW TABLES LIKE 'PromotionalBanners'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    $conn->close();
    header("Location: manage_banners.php?error=missing_table");
    exit();
}

$sql = "SELECT bannerID, title, subtitle, imagePath, ctaText, ctaUrl, startDate, endDate, is_active
        FROM PromotionalBanners WHERE bannerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bannerID);
$stmt->execute();
$result = $stmt->get_result();
$banner = $result ? $result->fetch_assoc() : null;
$stmt->close();
$conn->close();

if (!$banner) {
    header("Location: manage_banners.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Banner - Escape Quest</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/rooms.css">
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>

    <main class="page-content section-gap">
        <div class="container">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_dates'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> Start date must not be later than end date.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="pricing-card text-start p-5">
                        <h2 class="text-center mb-4 text-warning">Edit Promotional Banner</h2>

                        <form action="process_edit_banner.php" method="POST">
                            <input type="hidden" name="bannerID" value="<?= (int)$banner['bannerID']; ?>">

                            <div class="mb-3">
                                <label for="title" class="form-label text-light">Title <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" class="form-control" maxlength="120" required value="<?= htmlspecialchars($banner['title']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="subtitle" class="form-label text-light">Subtitle</label>
                                <input type="text" id="subtitle" name="subtitle" class="form-control" maxlength="255" value="<?= htmlspecialchars($banner['subtitle'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="imagePath" class="form-label text-light">Image Path (optional)</label>
                                <input type="text" id="imagePath" name="imagePath" class="form-control" value="<?= htmlspecialchars($banner['imagePath'] ?? ''); ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ctaText" class="form-label text-light">Button Text</label>
                                    <input type="text" id="ctaText" name="ctaText" class="form-control" maxlength="60" value="<?= htmlspecialchars($banner['ctaText'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ctaUrl" class="form-label text-light">Button URL</label>
                                    <input type="text" id="ctaUrl" name="ctaUrl" class="form-control" value="<?= htmlspecialchars($banner['ctaUrl'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="startDate" class="form-label text-light">Start Date</label>
                                    <input type="date" id="startDate" name="startDate" class="form-control" value="<?= htmlspecialchars($banner['startDate'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="endDate" class="form-label text-light">End Date</label>
                                    <input type="date" id="endDate" name="endDate" class="form-control" value="<?= htmlspecialchars($banner['endDate'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= ((int)$banner['is_active'] === 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label text-light" for="is_active">Active</label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="book-btn">Save Changes</button>
                                <a href="manage_banners.php" class="btn btn-outline-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>
</body>
</html>
