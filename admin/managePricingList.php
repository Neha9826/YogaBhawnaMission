<?php
// admin/managePricingList.php
session_start();
include '../db.php';

// ADD PRICE PLAN
if (isset($_POST['add_plan'])) {
    $is_pop = isset($_POST['is_popular']) ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO list_pricing (title, duration, price, level, is_popular) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $_POST['title'], $_POST['duration'], $_POST['price'], $_POST['level'], $is_pop);
    $stmt->execute();
    header("Location: managePricingList.php");
}

// DELETE
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM list_pricing WHERE id=" . intval($_GET['delete']));
    header("Location: managePricingList.php");
}

$result = $conn->query("SELECT * FROM list_pricing");
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
            <h2>Manage Pricing Plans</h2>

            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">Add New Plan</div>
                <div class="card-body">
                    <form method="post">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Plan Title</label>
                                <input type="text" name="title" class="form-control" placeholder="200-Hour TTC" required>
                            </div>
                            <div class="col-md-3">
                                <label>Duration</label>
                                <input type="text" name="duration" class="form-control" placeholder="24 Days">
                            </div>
                            <div class="col-md-2">
                                <label>Price</label>
                                <input type="text" name="price" class="form-control" placeholder="99">
                            </div>
                            <div class="col-md-2">
                                <label>Level</label>
                                <input type="text" name="level" class="form-control" placeholder="Beginner">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_popular" id="popCheck">
                                    <label class="form-check-label" for="popCheck">Mark as Popular?</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_plan" class="btn btn-success">Add Plan</button>
                    </form>
                </div>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Level</th>
                        <th>Popular</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['duration']) ?></td>
                        <td>₹<?= htmlspecialchars($row['price']) ?></td>
                        <td><?= htmlspecialchars($row['level']) ?></td>
                        <td><?= $row['is_popular'] ? '<span class="badge bg-success">Yes</span>' : 'No' ?></td>
                        <td>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
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