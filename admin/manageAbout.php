<?php
// admin/manageAbout.php
session_start();
include '../db.php';

// --- HELPER FOR UPLOADS ---
function handleUpload($inputName, $existingPath) {
    if (!empty($_FILES[$inputName]['name'])) {
        $target = "../img/" . basename($_FILES[$inputName]['name']);
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $target)) {
            return "img/" . basename($_FILES[$inputName]['name']);
        }
    }
    return $existingPath;
}

// --- HANDLE SUBMISSIONS ---

// 1. UPDATE STATIC CONTENT
if (isset($_POST['update_static'])) {
    $s1_img = handleUpload('sec1_img', $_POST['old_sec1_img']);
    $s2_img = handleUpload('sec2_img', $_POST['old_sec2_img']);
    $s3_img = handleUpload('sec3_img', $_POST['old_sec3_img']);
    $s4_img = handleUpload('sec4_img', $_POST['old_sec4_img']);

    $stmt = $conn->prepare("UPDATE page_about SET sec1_title=?, sec1_text=?, sec1_img=?, sec2_title=?, sec2_text=?, sec2_img=?, sec3_title=?, sec3_text=?, sec3_img=?, sec4_title=?, sec4_text=?, sec4_img=? WHERE id=1");
    $stmt->bind_param("ssssssssssss", $_POST['sec1_title'], $_POST['sec1_text'], $s1_img, $_POST['sec2_title'], $_POST['sec2_text'], $s2_img, $_POST['sec3_title'], $_POST['sec3_text'], $s3_img, $_POST['sec4_title'], $_POST['sec4_text'], $s4_img);
    $stmt->execute();
    header("Location: manageAbout.php?tab=main"); exit;
}

// 2. UPDATE MISSION HEADER
if (isset($_POST['update_mission_header'])) {
    $stmt = $conn->prepare("UPDATE page_about SET mission_heading=?, mission_subheading=? WHERE id=1");
    $stmt->bind_param("ss", $_POST['mission_heading'], $_POST['mission_subheading']);
    $stmt->execute();
    header("Location: manageAbout.php?tab=mission"); exit;
}

// 3. ADD MISSION ITEM (FIXED: Now captures Icon selection)
if (isset($_POST['add_mission_item'])) {
    $stmt = $conn->prepare("INSERT INTO about_mission_list (title, description, icon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['title'], $_POST['description'], $_POST['icon']);
    $stmt->execute();
    header("Location: manageAbout.php?tab=mission"); exit;
}

// 4. DELETE MISSION ITEM
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM about_mission_list WHERE id=$id");
    header("Location: manageAbout.php?tab=mission"); exit;
}

// --- FETCH DATA ---
$data = $conn->query("SELECT * FROM page_about WHERE id=1")->fetch_assoc();
$missionList = $conn->query("SELECT * FROM about_mission_list ORDER BY id ASC");
$activeTab = $_GET['tab'] ?? 'main';
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
            <h2>Manage About Page</h2>
            
            <ul class="nav nav-tabs" id="aboutTabs">
                <li class="nav-item"><a class="nav-link <?= $activeTab=='main'?'active':'' ?>" href="?tab=main">Main Content</a></li>
                <li class="nav-item"><a class="nav-link <?= $activeTab=='mission'?'active':'' ?>" href="?tab=mission">Mission & Vision</a></li>
            </ul>

            <div class="tab-content">
                
                <div class="tab-pane fade <?= $activeTab=='main'?'show active':'' ?>">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="update_static" value="1">
                        
                        <div class="card mb-4 border-dark">
                            <div class="card-header bg-dark text-white">1. Introduction (Top)</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" name="sec1_title" class="form-control mb-2" value="<?= htmlspecialchars($data['sec1_title']) ?>">
                                        <textarea name="sec1_text" class="summernote"><?= htmlspecialchars($data['sec1_text']) ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Image</label>
                                        <input type="file" name="sec1_img" class="form-control mb-2">
                                        <input type="hidden" name="old_sec1_img" value="<?= $data['sec1_img'] ?>">
                                        <img src="../<?= $data['sec1_img'] ?>" style="width:100px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 border-dark">
                            <div class="card-header bg-dark text-white">2. Himalayan Roots</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" name="sec2_title" class="form-control mb-2" value="<?= htmlspecialchars($data['sec2_title']) ?>">
                                        <textarea name="sec2_text" class="summernote"><?= htmlspecialchars($data['sec2_text']) ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Image</label>
                                        <input type="file" name="sec2_img" class="form-control mb-2">
                                        <input type="hidden" name="old_sec2_img" value="<?= $data['sec2_img'] ?>">
                                        <img src="../<?= $data['sec2_img'] ?>" style="width:100px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 border-dark">
                            <div class="card-header bg-dark text-white">3. Yogic Lifestyle</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" name="sec3_title" class="form-control mb-2" value="<?= htmlspecialchars($data['sec3_title']) ?>">
                                        <textarea name="sec3_text" class="summernote"><?= htmlspecialchars($data['sec3_text']) ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Image</label>
                                        <input type="file" name="sec3_img" class="form-control mb-2">
                                        <input type="hidden" name="old_sec3_img" value="<?= $data['sec3_img'] ?>">
                                        <img src="../<?= $data['sec3_img'] ?>" style="width:100px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 border-dark">
                            <div class="card-header bg-dark text-white">4. Enlightenment (Bottom)</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" name="sec4_title" class="form-control mb-2" value="<?= htmlspecialchars($data['sec4_title']) ?>">
                                        <textarea name="sec4_text" class="summernote"><?= htmlspecialchars($data['sec4_text']) ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Image</label>
                                        <input type="file" name="sec4_img" class="form-control mb-2">
                                        <input type="hidden" name="old_sec4_img" value="<?= $data['sec4_img'] ?>">
                                        <img src="../<?= $data['sec4_img'] ?>" style="width:100px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-lg mb-5">Save Main Content</button>
                    </form>
                </div>

                <div class="tab-pane fade <?= $activeTab=='mission'?'show active':'' ?>">
                    
                    <form method="post" class="card mb-4">
                        <div class="card-header bg-secondary text-white">Section Heading</div>
                        <div class="card-body">
                            <input type="hidden" name="update_mission_header" value="1">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Small Subheading</label>
                                    <input type="text" name="mission_subheading" class="form-control" value="<?= htmlspecialchars($data['mission_subheading']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label>Main Heading</label>
                                    <input type="text" name="mission_heading" class="form-control" value="<?= htmlspecialchars($data['mission_heading']) ?>">
                                </div>
                            </div>
                            <button class="btn btn-secondary mt-3 btn-sm">Update Heading</button>
                        </div>
                    </form>

                    <form method="post" class="bg-light p-3 border rounded mb-3">
                        <input type="hidden" name="add_mission_item" value="1">
                        <h6>Add Mission/Vision Card</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="title" class="form-control" placeholder="Title (e.g. Our Vision)" required>
                            </div>
                            <div class="col-md-3">
                                <select name="icon" class="form-control">
                                    <option value="flaticon-yoga-pose">Yoga Pose</option>
                                    <option value="flaticon-workout-1">Lotus / Meditation</option>
                                    <option value="flaticon-workout-2">Mind / Head</option>
                                    <option value="flaticon-workout-4">Heart / Health</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="description" class="form-control" placeholder="Description text..." required>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-success w-100">Add</button>
                            </div>
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
                            <?php while($item = $missionList->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['icon']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td>
                                    <a href="?delete=<?= $item['id'] ?>&tab=mission" class="btn btn-danger btn-sm" onclick="return confirm('Delete this?')">Delete</a>
                                </td>
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
<script>
    $('.summernote').summernote({
        height: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
        ]
    });
</script>
</body>
</html>