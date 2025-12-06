<?php
// admin/manageClassesList.php
session_start();
include '../db.php';

// ADD CLASS
if (isset($_POST['add_class'])) {
    $target = "../img/" . basename($_FILES['image']['name']);
    $db_path = "img/" . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $stmt = $conn->prepare("INSERT INTO list_classes (name, description, filter_category, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['name'], $_POST['description'], $_POST['filter_category'], $db_path);
    $stmt->execute();
    header("Location: manageClassesList.php");
}

// DELETE CLASS
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM list_classes WHERE id=" . intval($_GET['delete']));
    header("Location: manageClassesList.php");
}

$result = $conn->query("SELECT * FROM list_classes ORDER BY id DESC");
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
            <h2>Manage Yoga Classes</h2>

            <div class="card mb-4">
                <div class="card-header bg-info text-white">Add New Class</div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Class Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label>Filter Category</label>
                                <select name="filter_category" class="form-control">
                                    <option value="filter-1">Filter 1 (e.g. Hatha)</option>
                                    <option value="filter-2">Filter 2 (e.g. Vinyasa)</option>
                                    <option value="filter-3">Filter 3 (e.g. Ashtanga)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Image</label>
                                <input type="file" name="image" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" name="add_class" class="btn btn-success">Add Class</button>
                    </form>
                </div>
            </div>

            <div class="row">
                <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="../<?= $row['image'] ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                            <span class="badge bg-secondary"><?= $row['filter_category'] ?></span>
                        </div>
                        <div class="card-footer">
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm w-100" onclick="return confirm('Delete?')">Remove</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
</body>
</html>