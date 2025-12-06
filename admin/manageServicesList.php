<?php
// admin/manageServicesList.php
session_start();
include '../db.php';

// ADD NEW SERVICE
if (isset($_POST['add_service'])) {
    $stmt = $conn->prepare("INSERT INTO list_services (title, description, icon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['title'], $_POST['description'], $_POST['icon']);
    $stmt->execute();
    header("Location: manageServicesList.php");
}

// DELETE SERVICE
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM list_services WHERE id=" . intval($_GET['delete']));
    header("Location: manageServicesList.php");
}

$result = $conn->query("SELECT * FROM list_services ORDER BY id DESC");
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
            <h2>Manage Services (What We Do)</h2>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Add New Service</div>
                <div class="card-body">
                    <form method="post">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="title" class="form-control" placeholder="Title (e.g. Heal Emotions)" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="icon" class="form-control" placeholder="Icon Class (e.g. flaticon-workout)">
                                <small><a href="https://fontawesome.com/v4/icons/" target="_blank">Icon Reference</a></small>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="description" class="form-control" placeholder="Short Description" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="add_service" class="btn btn-success w-100">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><i class="<?= $row['icon'] ?>"></i> (<?= $row['icon'] ?>)</td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
</body>
</html>