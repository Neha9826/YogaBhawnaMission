<?php
include 'db.php';

// 1. Fetch Page Data (Static Sections & Headings)
$page = $conn->query("SELECT * FROM page_about WHERE id=1")->fetch_assoc();

// 2. Fetch Mission & Vision Cards (Loop)
$mission = $conn->query("SELECT * FROM about_mission_list ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include 'head.php'; ?>
    </head>

    <body>
        <?php include 'topBar.php'; ?>        
        <?php include 'ybm_navbar.php'; ?>

        <div class="page-header about-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2>About Us</h2>
                    </div>
                    <div class="col-12">
                        <a href="">Home</a>
                        <a href="">About Us</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="about wow fadeInUp" data-wow-delay="0.1s">
            <div class="container">
                
                <div class="row align-items-center">
                    <div class="col-lg-5 col-md-6">
                        <div class="about-img">
                            <img src="<?= htmlspecialchars($page['sec1_img']) ?>" alt="Image">
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <div class="section-header text-left">
                            <p>About</p>
                            <h2><?= htmlspecialchars($page['sec1_title']) ?></h2>
                        </div>
                        <div class="about-text">
                            <?= $page['sec1_text'] ?>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-lg-7 col-md-6">
                        <div class="section-header text-left">
                            <p>Our Root</p>
                            <h2><?= htmlspecialchars($page['sec2_title']) ?></h2>
                        </div>
                        <div class="about-text">
                            <?= $page['sec2_text'] ?>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-6">
                        <div class="about-img">
                            <img src="<?= htmlspecialchars($page['sec2_img']) ?>" alt="Image">
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-lg-5 col-md-6">
                        <div class="about-img">
                            <img src="<?= htmlspecialchars($page['sec3_img']) ?>" alt="Image">
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <div class="section-header text-left">
                            <p>Yogi</p>
                            <h2><?= htmlspecialchars($page['sec3_title']) ?></h2>
                        </div>
                        <div class="about-text">
                            <?= $page['sec3_text'] ?>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-lg-7 col-md-6">
                        <div class="section-header text-left">
                            <p>Enlightenment</p>
                            <h2><?= htmlspecialchars($page['sec4_title']) ?></h2>
                        </div>
                        <div class="about-text">
                            <?= $page['sec4_text'] ?>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-6">
                        <div class="about-img">
                            <img src="<?= htmlspecialchars($page['sec4_img']) ?>" alt="Image">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="service">
            <div class="container">
                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                    <p><?= htmlspecialchars($page['mission_subheading']) ?></p>
                    <h2><?= htmlspecialchars($page['mission_heading']) ?></h2>
                </div>
                <div class="row justify-content-center">
                    <?php 
                    $mDelay = 0;
                    $mCount = 0;
                    while($m = $mission->fetch_assoc()): 
                        // Only the 2nd card (index 1) gets the 'active' class by default
                        $activeM = ($mCount == 1) ? 'active' : '';
                    ?>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= $mDelay ?>s">
                        <div class="service-item <?= $activeM ?>">
                            <div class="service-icon">
                                <i class="<?= htmlspecialchars($m['icon']) ?>"></i>
                            </div>
                            <h3><?= htmlspecialchars($m['title']) ?></h3>
                            <p>
                                <?= htmlspecialchars($m['description']) ?>
                            </p>
                        </div>
                    </div>
                    <?php 
                    $mDelay += 0.2; 
                    $mCount++;
                    endwhile; 
                    ?>
                </div>
            </div>
        </div>
        <?php include 'includes/footer.php' ; ?>
        <a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>

        <div class="whatsapp widget-sec">
          <a href="tel:+919917003456" class="cta-btn phone" title="Call Now">
            <i class="fa fa-phone"></i>
          </a>
          <a aria-label="Chat on WhatsApp" href="https://wa.me/+919917003456" target="_blank" class="cta-btn whatsapp" title="Chat on WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>
        </div>

        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
        <script src="lib/easing/easing.min.js"></script>
        <script src="lib/wow/wow.min.js"></script>
        <script src="lib/owlcarousel/owl.carousel.min.js"></script>
        <script src="lib/isotope/isotope.pkgd.min.js"></script>
        <script src="lib/lightbox/js/lightbox.min.js"></script>
        
        <script src="mail/jqBootstrapValidation.min.js"></script>
        <script src="mail/contact.js"></script>

        <script src="js/main.js"></script>
    </body>
</html>