<?php
// admin/manageContact.php
session_start();
include '../db.php';

// 1. HANDLE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // We only update the Page Title and Map links now. 
    // Phone/Email/Address are handled in Global Settings.
    $stmt = $conn->prepare("UPDATE page_contact SET 
        page_title=?, 
        map_direction_url=?, 
        map_embed_url=? 
        WHERE id=1");

    $stmt->bind_param("sss", 
        $_POST['page_title'], 
        $_POST['map_direction_url'], 
        $_POST['map_embed_url']
    );
    
    if($stmt->execute()) {
        $_SESSION['flash_msg'] = "Contact Page Updated Successfully!";
        header("Location: manageContact.php");
        exit;
    } else {
        $error = "Database Error: " . $conn->error;
    }
}

// 2. FETCH DATA
$data = $conn->query("SELECT * FROM page_contact WHERE id=1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include 'includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main class="container-fluid px-4 mt-4">
            <h2>Manage Contact Page</h2>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> Phone, Email, and Address are now managed in <a href="manageSettings.php">Global Settings</a> to keep them consistent across the website.
            </div>
            
            <?php if (isset($_SESSION['flash_msg'])): ?>
                <div class="alert alert-success"><?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">Page Configuration</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="fw-bold">Page Main Heading</label>
                                    <input name="page_title" class="form-control" value="<?= htmlspecialchars($data['page_title']) ?>">
                                    <small class="text-muted">The big title shown on the Contact Us page.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">Google Map Settings</div>
                            <div class="card-body">
                                
                                <div class="mb-4">
                                    <label class="fw-bold">1. Get Directions Link (Normal URL)</label>
                                    <input name="map_direction_url" class="form-control" placeholder="https://goo.gl/maps/..." value="<?= htmlspecialchars($data['map_direction_url'] ?? '') ?>">
                                    <small class="text-muted">
                                        This is the link used for the "Get Direction" button.
                                    </small>
                                </div>

                                <hr>

                                <div class="mb-3">
                                    <label class="fw-bold">2. Map Embed Link (Iframe SRC)</label>
                                    <textarea name="map_embed_url" class="form-control" rows="3" placeholder="https://www.google.com/maps/embed?pb=..."><?= htmlspecialchars($data['map_embed_url']) ?></textarea>
                                    <small class="text-muted">
                                        Paste only the <code>src="..."</code> link from the Google Maps Embed code.
                                    </small>
                                </div>

                                <div class="mt-3">
                                    <label class="fw-bold">Preview:</label>
                                    <div class="border p-1">
                                        <iframe src="<?= htmlspecialchars($data['map_embed_url']) ?>" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-success btn-lg mb-5"><i class="fas fa-save"></i> Update Contact Page</button>
            </form>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
</body>
</html>