<?php
include 'db.php';

// 1. Fetch Page Data (Headings & Why Us Section)
$page = $conn->query("SELECT * FROM page_service WHERE id=1")->fetch_assoc();

// 2. Fetch Offerings Cards (Loop)
$offerings = $conn->query("SELECT * FROM service_offerings ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include 'head.php'; ?>
    </head>

    <body>
        <?php include 'topBar.php'; ?>        
        <?php include 'ybm_navbar.php'; ?>

        <div class="page-header service-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2>Service</h2>
                    </div>
                    <div class="col-12">
                        <a href="">Home</a>
                        <a href="">Service</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="service">
            <div class="container">
                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                    <p><?= htmlspecialchars($page['header_subtitle']) ?></p>
                    <h2><?= htmlspecialchars($page['header_title']) ?></h2>
                </div>
                <div class="row">
                    <?php 
                    $oDelay = 0;
                    $oCount = 0;
                    while($off = $offerings->fetch_assoc()): 
                        // Only the 2nd card (index 1) gets the 'active' class
                        $activeO = ($oCount == 1) ? 'active' : '';
                    ?>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= $oDelay ?>s">
                        <div class="service-item <?= $activeO ?>">
                            <div class="service-icon">
                                <i class="<?= htmlspecialchars($off['icon']) ?>"></i>
                            </div>
                            <h3><?= htmlspecialchars($off['title']) ?></h3>
                            <p>
                                <?= htmlspecialchars($off['description']) ?>
                            </p>
                        </div>
                    </div>
                    <?php 
                    $oDelay += 0.2; 
                    $oCount++;
                    endwhile; 
                    ?>
                </div>
            </div>
        </div>
        <div class="about wow fadeInUp" data-wow-delay="0.1s">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5 col-md-6">
                        <div class="about-img">
                            <img src="<?= htmlspecialchars($page['why_us_image']) ?>" alt="Image">
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <div class="section-header text-left">
                            <p><?= htmlspecialchars($page['why_us_subtitle']) ?></p>
                            <h2><?= htmlspecialchars($page['why_us_title']) ?></h2>
                        </div>
                        <div class="about-text">
                            <?= $page['why_us_text'] ?>
                        </div>
                    </div>
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