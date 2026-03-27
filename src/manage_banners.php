<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/functions.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$conn = getDbConnection();
$bannerRows = [];
$bannerFeatureReady = false;
$tableCheck = $conn->query("SHOW TABLES LIKE 'PromotionalBanners'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $bannerFeatureReady = true;
    $sql = "SELECT bannerID, title, subtitle, imagePath, ctaText, ctaUrl, startDate, endDate, is_active, created_at
            FROM PromotionalBanners
            ORDER BY created_at DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $bannerRows = $result->fetch_all(MYSQLI_ASSOC);
    }
}
$conn->close();

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Promotional Banners - Escape Quest</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/rooms.css">
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>

    <main class="page-content section-gap">
        <div class="container">
            <?php if (!$bannerFeatureReady): ?>
                <div class="alert alert-warning" role="alert">
                    Promotional banner table is missing. Run db/init/004-add-promotional-banners.sql first.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Promotional banner created.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Promotional banner updated.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Promotional banner deleted.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_dates'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> Start date must not be later than end date.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'missing_table'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> Banner table not found. Apply database migration first.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h2 class="text-center mb-4 text-warning">Manage Promotional Banners</h2>

            <div class="pricing-card text-start p-4 mb-5">
                <h3 class="text-warning mb-3">Create New Banner</h3>
                <form action="process_create_banner.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label text-light">Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" maxlength="120" required>
                        </div>
                        <div class="col-md-6">
                            <label for="subtitle" class="form-label text-light">Subtitle</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label for="imagePath" class="form-label text-light">Image Path (optional)</label>
                            <input type="text" id="imagePath" name="imagePath" class="form-control" placeholder="images/events/halloween.jpg">
                        </div>
                        <div class="col-md-3">
                            <label for="ctaText" class="form-label text-light">Button Text</label>
                            <input type="text" id="ctaText" name="ctaText" class="form-control" maxlength="60" placeholder="Book Now">
                        </div>
                        <div class="col-md-3">
                            <label for="ctaUrl" class="form-label text-light">Button URL</label>
                            <input type="text" id="ctaUrl" name="ctaUrl" class="form-control" placeholder="booking.php">
                        </div>
                        <div class="col-md-3">
                            <label for="startDate" class="form-label text-light">Start Date</label>
                            <input type="date" id="startDate" name="startDate" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label text-light">End Date</label>
                            <input type="date" id="endDate" name="endDate" class="form-control">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label text-light" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success" <?= $bannerFeatureReady ? '' : 'disabled'; ?>>Create Banner</button>
                        <a href="delete_room.php" class="btn btn-outline-light">Back to Admin Panel</a>
                    </div>
                </form>
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-dark text-light border-secondary">Search:</span>
                    <input type="text" id="bannerSearchInput" class="form-control bg-dark text-light border-secondary" onkeyup="filterBannerTable()" placeholder="Search by title, subtitle, CTA, or ID" aria-label="Search banners">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle" id="bannersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Dates</th>
                            <th>CTA</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bannerRows)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No promotional banners found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bannerRows as $banner): ?>
                                <?php
                                    $isInDateRange = (empty($banner['startDate']) || $banner['startDate'] <= $today) && (empty($banner['endDate']) || $banner['endDate'] >= $today);
                                    $isVisible = ((int)$banner['is_active'] === 1) && $isInDateRange;
                                ?>
                                <tr class="banner-searchable-row">
                                    <td><?= (int)$banner['bannerID']; ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($banner['title']); ?></div>
                                        <?php if (!empty($banner['subtitle'])): ?>
                                            <small class="text-light"><?= htmlspecialchars($banner['subtitle']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>Start: <?= !empty($banner['startDate']) ? htmlspecialchars($banner['startDate']) : 'Always'; ?></small><br>
                                        <small>End: <?= !empty($banner['endDate']) ? htmlspecialchars($banner['endDate']) : 'No end'; ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($banner['ctaText']) || !empty($banner['ctaUrl'])): ?>
                                            <small><?= htmlspecialchars($banner['ctaText'] ?? ''); ?></small><br>
                                            <small class="text-info"><?= htmlspecialchars($banner['ctaUrl'] ?? ''); ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">None</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isVisible): ?>
                                            <span class="badge bg-success">Visible</span>
                                        <?php elseif ((int)$banner['is_active'] === 0): ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Scheduled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($banner['created_at']); ?></td>
                                    <td class="text-end">
                                        <a href="edit_banner.php?id=<?= (int)$banner['bannerID']; ?>" class="btn btn-primary btn-sm me-2">Edit</a>
                                        <form action="process_delete_banner.php" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete this banner permanently?');">
                                            <input type="hidden" name="bannerID" value="<?= (int)$banner['bannerID']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div id="noBannerResults" class="text-center py-5" style="display: none;">
                    <h4 class="text-muted">No banners match your search.</h4>
                </div>
            </div>
        </div>
    </main>

    <script>
        function filterBannerTable() {
            const input = document.getElementById('bannerSearchInput');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#bannersTable tbody tr.banner-searchable-row');
            const noResults = document.getElementById('noBannerResults');

            let visibleCount = 0;
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noResults) {
                noResults.style.display = visibleCount === 0 && rows.length > 0 ? 'block' : 'none';
            }
        }
    </script>

    <?php include "inc/footer.inc.php"; ?>
</body>
</html>
