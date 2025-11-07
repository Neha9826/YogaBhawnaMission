<?php
// Get the current page's file name (e.g., 'about.php')
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container-fluid">
        <a href="index.php" class="navbar-brand">
            <img src="img/ybm.png" alt="Yoga Bhavna Mission" class="logo-img">
        </a>

        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <div class="navbar-nav ml-auto">
                <a href="index.php" class="nav-item nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Home</a>
                <a href="about.php" class="nav-item nav-link <?php echo ($currentPage == 'about.php') ? 'active' : ''; ?>">About</a>
                <a href="service.php" class="nav-item nav-link <?php echo ($currentPage == 'service.php') ? 'active' : ''; ?>">Service</a>
                <a href="list.php" class="nav-item nav-link <?php echo ($currentPage == 'list.php') ? 'active' : ''; ?>">Our Courses</a>
                <a href="contact.php" class="nav-item nav-link <?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>">Contact</a>
            </div>
        </div>
    </div>
</div>