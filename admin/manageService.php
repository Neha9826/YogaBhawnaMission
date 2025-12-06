<?php
// admin/manageService.php
session_start();
include '../db.php';

// A. UPDATE STATIC ("Why Us" section)
if (isset($_POST['update_service_static'])) {
    $img = $_POST['old_why_us_image'];
    if (!empty($_FILES['why_us_image']['name'])) {
        $target = "../img/" . basename($_FILES['why_us_image']['name']);
        move_uploaded_file($_FILES['why_us_image']['tmp_name'], $target);
        $img = "img/" . basename($_FILES['why_us_image']['name']);
    }

    $stmt = $conn->prepare("UPDATE page_service SET 
        header_title=?, header_subtitle=?, 
        why_us_title=?, why_us_subtitle=?, why_us_text=?, why_us_image=? 
        WHERE id=1");
    $stmt->bind_param("ssssss", 
        $_POST['header_title'], $_POST['header_subtitle'],
        $_POST['why_us_title'], $_POST['why_us_subtitle'], $_POST['why_us_text'], $img
    );
    $stmt->execute();
    header("Location: manageService.php?tab=static");
    exit;
}

// B. ADD OFFERING (200hr, 300hr, etc)
if (isset($_POST['add_offering'])) {
    $stmt = $conn->prepare("INSERT INTO service_offerings (title, description, icon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['title'], $_POST['description'], $_POST['icon']);
    $stmt->execute();
    header("Location: manageService.php?tab=offerings");
    exit;
}

// C. DELETE OFFERING
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM service_offerings WHERE id=$id");
    header("Location: manageService.php?tab=offerings");
    exit;
}

$static = $conn->query("SELECT * FROM page_service WHERE id=1")->fetch_assoc();
$offerings = $conn->query("SELECT * FROM service_offerings ORDER BY id ASC");
$activeTab = $_GET['tab'] ?? 'static';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .nav-tabs .nav-link.active { background-color: #0d6efd; color: white; }
    .nav-tabs .nav-link { color: #495057; font-weight: bold; }
    .tab-content { border: 1px solid #dee2e6; border-top: none; padding: 20px; background: #fff; }
</style>

<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include 'includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main class="container-fluid px-4 mt-4">
            <h2>Manage Service Page</h2>

            <ul class="nav nav-tabs" id="serviceTab">
                <li class="nav-item"><a class="nav-link <?= $activeTab=='static'?'active':'' ?>" href="?tab=static">Content (Why Us)</a></li>
                <li class="nav-item"><a class="nav-link <?= $activeTab=='offerings'?'active':'' ?>" href="?tab=offerings">Core Offerings</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade <?= $activeTab=='static'?'show active':'' ?>">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="update_service_static" value="1">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Page Header Title</label>
                                <input type="text" name="header_title" class="form-control" value="<?= htmlspecialchars($static['header_title']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Page Header Subtitle</label>
                                <input type="text" name="header_subtitle" class="form-control" value="<?= htmlspecialchars($static['header_subtitle']) ?>">
                            </div>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-header fw-bold">"Why Choose Us" Section</div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>Title</label>
                                        <input type="text" name="why_us_title" class="form-control" value="<?= htmlspecialchars($static['why_us_title']) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Subtitle</label>
                                        <input type="text" name="why_us_subtitle" class="form-control" value="<?= htmlspecialchars($static['why_us_subtitle']) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Text Content (Bullet points etc)</label>
                                    <textarea name="why_us_text" class="summernote"><?= htmlspecialchars($static['why_us_text']) ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Side Image</label>
                                        <input type="file" name="why_us_image" class="form-control">
                                        <input type="hidden" name="old_why_us_image" value="<?= $static['why_us_image'] ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <img src="../<?= $static['why_us_image'] ?>" style="height:100px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary">Save Content</button>
                    </form>
                </div>

                <div class="tab-pane fade <?= $activeTab=='offerings'?'show active':'' ?>">
                    <form method="post" class="mb-4 bg-light p-3 border rounded">
                        <input type="hidden" name="add_offering" value="1">
                        <h6>Add Core Offering</h6>
                        <div class="row">
                            <div class="col-md-4"><input type="text" name="title" class="form-control" placeholder="Title (e.g. 200 Hour TTC)" required></div>
                            <div class="col-md-3"><input type="text" name="icon" class="form-control" placeholder="Icon Class"></div>
                            <div class="col-md-4"><input type="text" name="description" class="form-control" placeholder="Description" required></div>
                            <div class="col-md-1"><button class="btn btn-success w-100">Add</button></div>
                        </div>
                    </form>

                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Icon</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $offerings->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['icon'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Delete</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script> $('.summernote').summernote({ height: 200 }); </script>
</body>
</html>