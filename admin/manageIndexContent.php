<?php
// admin/manageIndexContent.php
session_start();
include '../db.php';

// --- HELPER FUNCTIONS ---
function handleUpload($inputName, $folder = "../img/") {
    if (!empty($_FILES[$inputName]['name'])) {
        $fileName = time() . "_" . basename($_FILES[$inputName]['name']);
        $target = $folder . $fileName;
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $target)) {
            return str_replace("../", "", $target);
        }
    }
    return null;
}

// --- HANDLE SUBMISSIONS ---

// 1. UPDATE STATIC SECTIONS
if (isset($_POST['update_static'])) {
    $hero_video = handleUpload('hero_video', "../videos/") ?? $_POST['old_hero_video'];
    $about_img  = handleUpload('about_img') ?? $_POST['old_about_img'];

    $stmt = $conn->prepare("UPDATE page_home SET hero_title=?, hero_text=?, hero_video=?, about_sub_title=?, about_title=?, about_text=?, about_img=?, discount_percent=?, discount_text=? WHERE id=1");
    $stmt->bind_param("sssssssss", $_POST['hero_title'], $_POST['hero_text'], $hero_video, $_POST['about_sub_title'], $_POST['about_title'], $_POST['about_text'], $about_img, $_POST['discount_percent'], $_POST['discount_text']);
    $stmt->execute();
    header("Location: manageIndexContent.php?tab=main"); exit;
}

// 2. ADD "WHAT WE DO" (Fixed: Now allows Icon Selection)
if (isset($_POST['add_feature'])) {
    $stmt = $conn->prepare("INSERT INTO home_features (title, description, icon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['title'], $_POST['description'], $_POST['icon']);
    $stmt->execute();
    header("Location: manageIndexContent.php?tab=whatwedo"); exit;
}

// 3.A. CREATE CLASS FILTER
if (isset($_POST['add_filter'])) {
    $name = trim($_POST['filter_name']);
    $slug = "filter-" . strtolower(str_replace(' ', '-', $name)); 
    $stmt = $conn->prepare("INSERT INTO class_filters (name, slug) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $slug);
    $stmt->execute();
    header("Location: manageIndexContent.php?tab=classes"); exit;
}

// 3.B. ADD CLASS (Fixed: Added Description)
if (isset($_POST['add_class'])) {
    $img = handleUpload('image') ?? 'img/default.jpg';
    $stmt = $conn->prepare("INSERT INTO list_classes (name, description, filter_category, image) VALUES (?, ?, ?, ?)");
    // Now binding 4 strings: Name, Description, Filter, Image
    $stmt->bind_param("ssss", $_POST['name'], $_POST['description'], $_POST['filter_category'], $img);
    $stmt->execute();
    header("Location: manageIndexContent.php?tab=classes"); exit;
}

// 4. ADD PRICING
if (isset($_POST['add_plan'])) {
    $pop = isset($_POST['is_popular']) ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO list_pricing (title, duration, price, level, certificate, location, language, is_popular) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $_POST['title'], $_POST['duration'], $_POST['price'], $_POST['level'], $_POST['certificate'], $_POST['location'], $_POST['language'], $pop);
    $stmt->execute();
    header("Location: manageIndexContent.php?tab=pricing"); exit;
}

// 5. ADD INSTAGRAM
if (isset($_POST['add_insta'])) {
    $img = handleUpload('image') ?? 'img/default.jpg';
    $stmt = $conn->prepare("INSERT INTO list_instagram (image_path, caption, likes_count, comments_count, post_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $img, $_POST['caption'], $_POST['likes'], $_POST['comments'], $_POST['post_url']);
    $stmt->execute();
    header("Location: manageIndexContent.php?tab=instagram"); exit;
}

// DELETE LOGIC
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = intval($_GET['delete']);
    $type = $_GET['type'];
    if ($type == 'feature') { $conn->query("DELETE FROM home_features WHERE id=$id"); $tab='whatwedo'; }
    if ($type == 'filter')  { $conn->query("DELETE FROM class_filters WHERE id=$id"); $tab='classes'; } 
    if ($type == 'class')   { $conn->query("DELETE FROM list_classes WHERE id=$id");  $tab='classes'; }
    if ($type == 'price')   { $conn->query("DELETE FROM list_pricing WHERE id=$id");  $tab='pricing'; }
    if ($type == 'insta')   { $conn->query("DELETE FROM list_instagram WHERE id=$id"); $tab='instagram'; }
    header("Location: manageIndexContent.php?tab=$tab"); exit;
}

// --- FETCH DATA ---
$static   = $conn->query("SELECT * FROM page_home WHERE id=1")->fetch_assoc();
$features = $conn->query("SELECT * FROM home_features ORDER BY id ASC");
$filters  = $conn->query("SELECT * FROM class_filters ORDER BY id ASC");
$classes  = $conn->query("SELECT * FROM list_classes ORDER BY id DESC");
$pricing  = $conn->query("SELECT * FROM list_pricing ORDER BY id ASC");
$insta    = $conn->query("SELECT * FROM list_instagram ORDER BY id DESC");
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
            <h2>Manage Home Page</h2>
            
            <ul class="nav nav-tabs" id="myTab">
                <li class="nav-item"><a class="nav-link <?= $activeTab=='main'?'active':'' ?>" href="?tab=main">Main Sections</a></li>
                <li class="nav-item"><a class="nav-link <?= $activeTab=='whatwedo'?'active':'' ?>" href="?tab=whatwedo">What We Do</a></li>
                <li class="nav-item"><a class="nav-link <?= $activeTab=='classes'?'active':'' ?>" href="?tab=classes">Classes & Filters</a></li>
                <li class="nav-item"><a class="nav-link <?= $activeTab=='pricing'?'active':'' ?>" href="?tab=pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link <?= $activeTab=='instagram'?'active':'' ?>" href="?tab=instagram">Instagram</a></li>
            </ul>

            <div class="tab-content">
                
                <div class="tab-pane fade <?= $activeTab=='main'?'show active':'' ?>">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="update_static" value="1">
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white">Hero Section</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="fw-bold">Headline</label>
                                        <input type="text" name="hero_title" class="form-control" value="<?= htmlspecialchars($static['hero_title']) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-bold">Video</label>
                                        <input type="file" name="hero_video" class="form-control">
                                        <input type="hidden" name="old_hero_video" value="<?= $static['hero_video'] ?>">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <textarea name="hero_text" class="form-control"><?= htmlspecialchars($static['hero_text']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-4 border-info">
                            <div class="card-header bg-info text-white">About Preview</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" name="about_sub_title" class="form-control mb-2" value="<?= htmlspecialchars($static['about_sub_title']) ?>">
                                        <input type="text" name="about_title" class="form-control mb-2" value="<?= htmlspecialchars($static['about_title']) ?>">
                                        <textarea name="about_text" class="summernote"><?= htmlspecialchars($static['about_text']) ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="file" name="about_img" class="form-control mb-2">
                                        <input type="hidden" name="old_about_img" value="<?= $static['about_img'] ?>">
                                        <img src="../<?= $static['about_img'] ?>" class="img-thumbnail" style="height: 100px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-4 border-warning">
                            <div class="card-header bg-warning text-dark">Discount Banner</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2"><input type="text" name="discount_percent" class="form-control" value="<?= htmlspecialchars($static['discount_percent']) ?>"></div>
                                    <div class="col-md-10"><input type="text" name="discount_text" class="form-control" value="<?= htmlspecialchars($static['discount_text']) ?>"></div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary">Save Main Sections</button>
                    </form>
                </div>

                <div class="tab-pane fade <?= $activeTab=='whatwedo'?'show active':'' ?>">
                    <form method="post" class="mb-4 bg-light p-3 border rounded">
                        <input type="hidden" name="add_feature" value="1">
                        <h6>Add New Item</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Title (e.g. Body & Mind)" required>
                            </div>
                            <div class="col-md-3">
                                <label>Icon</label>
                                <select name="icon" class="form-control">
                                    <option value="flaticon-workout">Dumbbell (Workout)</option>
                                    <option value="flaticon-workout-1">Lotus (Meditation)</option>
                                    <option value="flaticon-workout-2">Head (Mind/Serenity)</option>
                                    <option value="flaticon-workout-3">Leaf (Nature/Life)</option>
                                    <option value="flaticon-workout-4">Heart (Spirituality)</option>
                                    <option value="flaticon-yoga-pose">Yoga Pose (Body & Mind)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Description</label>
                                <input type="text" name="description" class="form-control" placeholder="Description..." required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-success w-100">Add</button>
                            </div>
                        </div>
                    </form>
                    <table class="table table-sm table-bordered">
                        <thead class="table-dark"><tr><th>Icon</th><th>Title</th><th>Description</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php while($f = $features->fetch_assoc()): ?>
                            <tr><td><?= htmlspecialchars($f['icon']) ?></td><td><?= htmlspecialchars($f['title']) ?></td><td><?= htmlspecialchars($f['description']) ?></td><td><a href="?delete=<?= $f['id'] ?>&type=feature" class="btn btn-danger btn-sm">X</a></td></tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade <?= $activeTab=='classes'?'show active':'' ?>">
                    <div class="row">
                        <div class="col-md-4 border-end">
                            <h5 class="text-primary">1. Create Filters</h5>
                            <form method="post" class="mb-3">
                                <input type="hidden" name="add_filter" value="1">
                                <div class="input-group">
                                    <input type="text" name="filter_name" class="form-control" placeholder="Name (e.g. Hatha)" required>
                                    <button class="btn btn-primary">Add</button>
                                </div>
                            </form>
                            <ul class="list-group mb-4">
                                <?php 
                                $filterArray = []; 
                                while($fil = $filters->fetch_assoc()): 
                                    $filterArray[] = $fil;
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($fil['name']) ?> 
                                    <a href="?delete=<?= $fil['id'] ?>&type=filter" class="text-danger" onclick="return confirm('Delete filter?')">X</a>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <div class="col-md-8">
                            <h5 class="text-success">2. Add Class</h5>
                            <form method="post" enctype="multipart/form-data" class="bg-light p-3 border rounded mb-3">
                                <input type="hidden" name="add_class" value="1">
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label>Class Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Filter Category</label>
                                        <select name="filter_category" class="form-control" required>
                                            <option value="">-- Select Filter --</option>
                                            <?php foreach($filterArray as $f): ?>
                                                <option value="<?= $f['slug'] ?>"><?= $f['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label>Description (Text Muted)</label>
                                    <textarea name="description" class="form-control" rows="2" placeholder="Short description..."></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-8"><input type="file" name="image" class="form-control" required></div>
                                    <div class="col-md-4"><button class="btn btn-success w-100">Add Class</button></div>
                                </div>
                            </form>
                            
                            <div class="row">
                                <?php while($c = $classes->fetch_assoc()): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <img src="../<?= $c['image'] ?>" class="card-img-top" style="height:100px; object-fit:cover;">
                                        <div class="card-body p-2">
                                            <h6><?= htmlspecialchars($c['name']) ?></h6>
                                            <small class="text-muted d-block"><?= htmlspecialchars($c['description']) ?></small>
                                            <span class="badge bg-secondary"><?= $c['filter_category'] ?></span>
                                            <a href="?delete=<?= $c['id'] ?>&type=class" class="text-danger float-end" onclick="return confirm('Delete?')">Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?= $activeTab=='pricing'?'show active':'' ?>">
                    <form method="post" class="mb-4 bg-light p-3 border rounded">
                        <input type="hidden" name="add_plan" value="1">
                        <h6>Add New Plan</h6>
                        <div class="row mb-2">
                            <div class="col-md-4"><input type="text" name="title" class="form-control" placeholder="Title (200 Hr)" required></div>
                            <div class="col-md-4"><input type="text" name="price" class="form-control" placeholder="Price (99)" required></div>
                            <div class="col-md-4"><input type="text" name="duration" class="form-control" placeholder="Duration (24 days)" required></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><input type="text" name="level" class="form-control" placeholder="Level (Beginner)"></div>
                            <div class="col-md-4"><input type="text" name="certificate" class="form-control" placeholder="Certificate (Yoga Alliance)"></div>
                            <div class="col-md-4"><input type="text" name="location" class="form-control" placeholder="Location"></div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-4"><input type="text" name="language" class="form-control" placeholder="Language (English)"></div>
                            <div class="col-md-4"><label><input type="checkbox" name="is_popular"> Mark as Popular?</label></div>
                            <div class="col-md-4"><button class="btn btn-success w-100">Add Plan</button></div>
                        </div>
                    </form>
                    <table class="table table-striped table-sm">
                        <thead><tr><th>Title</th><th>Price</th><th>Dur</th><th>Cert</th><th>Pop</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php while($p = $pricing->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['title']) ?></td>
                                <td><?= htmlspecialchars($p['price']) ?></td>
                                <td><?= htmlspecialchars($p['duration']) ?></td>
                                <td><?= htmlspecialchars($p['certificate']) ?></td>
                                <td><?= $p['is_popular']?'Yes':'No' ?></td>
                                <td><a href="?delete=<?= $p['id'] ?>&type=price" class="btn btn-danger btn-sm">X</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade <?= $activeTab=='instagram'?'show active':'' ?>">
                    <form method="post" enctype="multipart/form-data" class="mb-4 bg-light p-3 border rounded">
                        <input type="hidden" name="add_insta" value="1">
                        <h6>Add Post</h6>
                        <div class="row mb-2">
                            <div class="col-md-4"><label>Upload Image</label><input type="file" name="image" class="form-control" required></div>
                            <div class="col-md-8"><label>Post URL</label><input type="url" name="post_url" class="form-control" placeholder="https://instagram.com/p/..."></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><input type="text" name="caption" class="form-control" placeholder="Caption"></div>
                            <div class="col-md-3"><input type="number" name="likes" class="form-control" placeholder="Likes Count"></div>
                            <div class="col-md-3"><input type="number" name="comments" class="form-control" placeholder="Comments Count"></div>
                            <div class="col-md-2"><button class="btn btn-success w-100">Add</button></div>
                        </div>
                    </form>
                    <div class="row">
                        <?php while($i = $insta->fetch_assoc()): ?>
                        <div class="col-md-2 mb-3">
                            <div class="card">
                                <img src="../<?= $i['image_path'] ?>" class="card-img-top" style="height:120px; object-fit:cover;">
                                <div class="card-footer p-1 text-center">
                                    <small class="d-block text-truncate"><a href="<?= $i['post_url'] ?>" target="_blank">View Post</a></small>
                                    <a href="?delete=<?= $i['id'] ?>&type=insta" class="text-danger"><i class="fa fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

            </div> </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script> $('.summernote').summernote({ height: 150 }); </script>
</body>
</html>