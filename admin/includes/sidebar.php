<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config.php';
?>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="../dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>yoga/index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                    Yoga
                </a>

                <div class="sb-sidenav-menu-heading">Website Content</div>
                
                <a class="nav-link" href="<?= BASE_URL ?>manageIndexContent.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                    Manage Home Page
                </a>
                
                <a class="nav-link" href="<?= BASE_URL ?>manageAbout.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-info-circle"></i></div>
                    Manage About Page
                </a>

                <a class="nav-link" href="<?= BASE_URL ?>manageService.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-spa"></i></div>
                    Manage Service Page
                </a>

                <a class="nav-link" href="<?= BASE_URL ?>manageContact.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-address-book"></i></div>
                    Manage Contact Page
                </a>

                <div class="sb-sidenav-menu-heading">Settings</div>
                <a class="nav-link" href="<?= BASE_URL ?>manageSettings.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-cogs"></i></div>
                    Global Settings
                </a>

            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?= isset($_SESSION['emp_name']) ? htmlspecialchars($_SESSION['emp_name']) : 'Admin'; ?>
        </div>
    </nav>
</div>