<?php
include 'db.php'; // DB Connection

// 1. Fetch Page Static Data (Hero, About, Discount)
$page = $conn->query("SELECT * FROM page_home WHERE id=1")->fetch_assoc();

// 2. Fetch "What We Do" (Home Features) - FIFO Order
$features = $conn->query("SELECT * FROM home_features ORDER BY id ASC");

// 3. Fetch Class Filters & Classes - FIFO Order
$filters = $conn->query("SELECT * FROM class_filters ORDER BY id ASC");
$classes = $conn->query("SELECT * FROM list_classes ORDER BY id ASC");

// 4. Fetch Pricing
$pricing = $conn->query("SELECT * FROM list_pricing ORDER BY id ASC");

// 5. Fetch Instagram
$insta = $conn->query("SELECT * FROM list_instagram ORDER BY id DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include 'head.php'; ?>
        
        <meta name="description" content="Yoga Bhawna Mission is a serene yoga retreat and teacher training center on the banks of the Bhagirathi River in Uttarkashi, Uttarakhand. Join 200-hour TTC, meditation, pranayama and Himalayan ashram life in a peaceful Himalayan setting.">
        <meta name="keywords" content="Yoga Bhawna Mission, Yoga retreat Uttarkashi, Yoga TTC Uttarakashi, Himalayan yoga school, ashram in Uttarkashi, yoga teacher training India, river side yoga Uttarakhand, yoga ashram Garhwal Himalayas, Uttarkashi yoga retreat, spiritual retreat Himalayas, yoga school Uttarkashi">

        <meta name="author" content="Yoga Bhawna Mission">
        <meta name="robots" content="index, follow">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="geo.region" content="IN-UT">
        <meta name="geo.placename" content="Uttarkashi, Uttarakhand, India">
        <meta name="geo.position" content="30.7280;78.4438">
        <meta name="ICBM" content="30.7280, 78.4438">

        <link rel="canonical" href="https://yogbhawnamission.com/">

        <meta property="og:title" content="Yoga Bhawna Mission | Himalayan Yoga Retreat in Uttarkashi">
        <meta property="og:description" content="Experience yoga teacher training, meditation, and Himalayan ashram life at Yoga Bhawna Mission in Uttarkashi, Uttarakhand — by the Bhagirathi River.">
        <meta property="og:image" content="https://yogbhawnamission.com/images/your-banner-or-photo.jpg">
        <meta property="og:url" content="https://yogbhawnamission.com/">
        <meta property="og:type" content="organization">
        <meta property="og:site_name" content="Yoga Bhawna Mission">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Yoga Bhawna Mission – Yoga Retreat & TTC in Uttarkashi">
        <meta name="twitter:description" content="Join Yoga Bhawna Mission in Uttarkashi for yoga teacher training, meditation, and spiritual retreat in the Himalayan foothills.">
        <meta name="twitter:image" content="https://yogbhawnamission.com/images/your-banner-or-photo.jpg">
    </head>

    <body>
        
        <?php include 'topBar.php'; ?>        
        <?php include 'ybm_navbar.php'; ?>

        <div class="hero" id="home">
            <div class="video-background">
                <video autoplay muted loop playsinline preload="auto" poster="img/hero-fallback.jpg" id="heroVideo">
                    <source src="<?= htmlspecialchars($page['hero_video']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <div class="container-fluid">
                <div class="row align-items-center">
                <div class="col-sm-12 col-md-6">
                    <div class="hero-text">
                    <h1><?= htmlspecialchars($page['hero_title']) ?></h1>
                    <p><?= nl2br(htmlspecialchars($page['hero_text'])) ?></p>
                    <div class="hero-btn">
                        <a class="btn" href="#query">Contact Us</a>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="quick-query wow fadeInUp" data-wow-delay="0.2s" id="quick-query">
            <div class="container">
                <form class="query-inline" action="sendQuery.php" method="post">
                    <div class="form-row">
                        <div class="form-group col-lg-2 col-md-3 col-12"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
                        <div class="form-group col-lg-2 col-md-3 col-12"><input type="tel" name="phone" class="form-control" placeholder="Phone Number" required></div>
                        <div class="form-group col-lg-2 col-md-3 col-12"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                        <div class="form-group col-lg-2 col-md-3 col-12">
                            <select name="service" class="form-control" required>
                                <option value="">Select Service</option>
                                <option value="Yoga Retreat">Yoga Retreat</option>
                                <option value="YTTC">Yoga Teacher Training Course</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-2 col-md-4 col-12"><input type="date" name="date" class="form-control" required></div>
                        <div class="form-group col-lg-1 col-md-4 col-12 text-center"><button type="submit" class="btn">Submit</button></div>
                    </div>
                </form>
            </div>
        </div>

        <div id="about" class="about wow fadeInUp" data-wow-delay="0.1s">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5 col-md-6">
                        <div class="about-img">
                            <img src="<?= htmlspecialchars($page['about_img']) ?>" alt="Image">
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <div class="section-header text-left">
                            <p><?= htmlspecialchars($page['about_sub_title']) ?></p>
                            <h2><?= htmlspecialchars($page['about_title']) ?></h2>
                        </div>
                        <div class="about-text">
                            <?= $page['about_text'] ?> 
                            <a class="btn" href="about.php">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="service" id="service">
            <div class="container">
                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                    <p>What we do</p>
                    <h2>Yoga For Health</h2>
                </div>
                <div class="row">
                    <?php 
                    $delay = 0;
                    $counter = 0;
                    while($feat = $features->fetch_assoc()): 
                        // FIX: Only the 2nd item (index 1) gets 'active' to match old code
                        $activeClass = ($counter == 1) ? 'active' : ''; 
                    ?>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= $delay ?>s">
                        <div class="service-item <?= $activeClass ?>">
                            <div class="service-icon">
                                <i class="<?= htmlspecialchars($feat['icon']) ?>"></i>
                            </div>
                            <h3><?= htmlspecialchars($feat['title']) ?></h3>
                            <p><?= htmlspecialchars($feat['description']) ?></p>
                        </div>
                    </div>
                    <?php 
                    $delay += 0.2; 
                    $counter++;
                    endwhile; 
                    ?>
                </div>
            </div>
        </div>
        <div class="class" id="class">
            <div class="container">
                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                    <p>Our Classes</p>
                    <h2>Yoga Class Shedule</h2>
                </div>
                <div class="row">
                    <div class="col-12">
                        <ul id="class-filter">
                            <li data-filter="*" class="filter-active">All Classes</li>
                            <?php while($fil = $filters->fetch_assoc()): ?>
                                <li data-filter=".<?= $fil['slug'] ?>"><?= htmlspecialchars($fil['name']) ?></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
                <div class="row class-container">
                    <?php 
                    $cDelay = 0;
                    while($cls = $classes->fetch_assoc()): 
                    ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 class-item <?= $cls['filter_category'] ?> wow fadeInUp" data-wow-delay="<?= $cDelay ?>s">
                        <div class="class-wrap">
                            <div class="class-img">
                                <img src="<?= htmlspecialchars($cls['image']) ?>" alt="Image">
                            </div>
                            <div class="class-text">
                                <h2><?= htmlspecialchars($cls['name']) ?></h2>
                                <div class="class-meta">
                                    <p class="text-muted"><?= htmlspecialchars($cls['description']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $cDelay = ($cDelay >= 0.8) ? 0 : $cDelay + 0.2;
                    endwhile; 
                    ?>
                </div>
            </div>
        </div>
        <div class="discount wow zoomIn" data-wow-delay="0.1s">
            <div class="container">
                <div class="section-header text-center">
                    <p>Special Offer</p>
                    <h2>Get <span><?= htmlspecialchars($page['discount_percent']) ?></span> Off on All Yoga Classes</h2>
                </div>
                <div class="container discount-text">
                    <p><?= nl2br(htmlspecialchars($page['discount_text'])) ?></p>
                    <a class="btn">Join Now</a>
                </div>
            </div>
        </div>
        <div class="price" id="price">
            <div class="container">
                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                    <p>Yoga Package</p>
                    <h2>Yoga Pricing Plan</h2>
                </div>
                <div class="row justify-content-center">
                    <?php 
                    $pDelay = 0;
                    while($plan = $pricing->fetch_assoc()): 
                        $featuredClass = ($plan['is_popular'] == 1) ? 'featured-item' : '';
                    ?>
                    <div class="col-md-4 wow fadeInUp" data-wow-delay="<?= $pDelay ?>s">
                        <div class="price-item <?= $featuredClass ?>">
                            <div class="price-header">
                                <?php if($plan['is_popular']): ?>
                                <div class="price-status"><span>Popular</span></div>
                                <?php endif; ?>
                                <div class="price-title">
                                    <h2><?= htmlspecialchars($plan['title']) ?></h2>
                                    <h4 style="<?= $plan['is_popular'] ? 'color: #6f8b46;' : '' ?>"><?= htmlspecialchars($plan['duration']) ?></h4>
                                </div>
                                <div class="price-prices">
                                    <h2><small>₹</small><?= htmlspecialchars($plan['price']) ?><span></span></h2>
                                </div>
                            </div>
                            <div class="price-body">
                                <div class="price-description">
                                    <ul>
                                        <li><?= htmlspecialchars($plan['certificate'] ?? 'Yoga Alliance') ?></li>
                                        <li>Level: <?= htmlspecialchars($plan['level']) ?></li>
                                        <li>Loc: <?= htmlspecialchars($plan['location'] ?? 'Uttarkashi') ?></li>
                                        <li>Lang: <?= htmlspecialchars($plan['language'] ?? 'English') ?></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="price-footer">
                                <div class="price-action">
                                    <a class="btn" href="list.php">Join Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $pDelay += 0.3;
                    endwhile; 
                    ?>
                </div>
            </div>
        </div>
        <div class="instagram" id="instagram">
            <div class="container">
                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                    <p>Follow Us</p>
                    <h2>Instagram Posts</h2>
                </div>
                <div class="row">
                    <?php 
                    $iDelay = 0;
                    while($post = $insta->fetch_assoc()): 
                    ?>
                    <div class="col-md-4 wow fadeInUp" data-wow-delay="<?= $iDelay ?>s">
                        <div class="insta-item">
                            <div class="insta-img">
                                <?php if($post['post_url']): ?><a href="<?= htmlspecialchars($post['post_url']) ?>" target="_blank"><?php endif; ?>
                                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Insta Post">
                                <?php if($post['post_url']): ?></a><?php endif; ?>
                            </div>
                            <div class="insta-content">
                                <p class="insta-caption"><?= htmlspecialchars($post['caption']) ?></p>
                                <div class="insta-stats">
                                    <span><i class="fa fa-heart"></i> <?= $post['likes_count'] ?></span>
                                    <span><i class="fa fa-comment"></i> <?= $post['comments_count'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $iDelay += 0.2;
                    endwhile; 
                    ?>
                </div>
            </div>
        </div>
        <div class="query-form" id="query">
            <div class="container">
                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                    <p>Get In Touch</p>
                    <h2>Submit Your Query</h2>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <form class="query-form-inner wow fadeInUp" data-wow-delay="0.2s" action="sendQuery.php" method="post">
                            <div class="form-group"><input type="text" class="form-control" name="fullname" placeholder="Full Name" required></div>
                            <div class="form-group"><input type="date" class="form-control" name="dob" placeholder="Date of Birth" required></div>
                            <div class="form-group"><textarea class="form-control" name="address" rows="2" placeholder="Address" required></textarea></div>
                            <div class="form-group"><input type="tel" class="form-control" name="phone" placeholder="Phone Number" required></div>
                            <div class="form-group"><input type="email" class="form-control" name="email" placeholder="Email Address" required></div>
                            <div class="form-group">
                                <select class="form-control" name="option" required>
                                    <option value="">Query About</option>
                                    <option value="YTTC">Yoga Teacher's Training Course</option>
                                    <option value="Yoga Retreat">Yoga Retreat</option>
                                </select>
                            </div>
                            <div class="text-center"><button type="submit" class="btn">Submit</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container"> 
            <div class="row">
                <div class="col-12">
                    <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                        <p>Find Us</p>
                        <h2>Our Location</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="map-full-width">
            <div id="map" class="map map_single add_bottom_30" style="width: 100%; height: 450px;">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3429.554389786153!2d78.4070514!3d30.7309254!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3908ed25c638acad%3A0xb58aa23be89327bd!2sYoga%20Bhawna%20Mission%20-%20Yoga%20Teacher%20Training%20Himalaya!5e0!3m2!1sen!2sin!4v1759989557131!5m2!1sen!2sin" 
                        width="100%" height="450px" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
        
        <?php include 'includes/footer.php' ; ?>

        <a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>
        <div class="whatsapp widget-sec">
          <a href="tel:+919917003456" class="cta-btn phone" title="Call Now"><i class="fa fa-phone"></i></a>
          <a aria-label="Chat on WhatsApp" href="https://wa.me/+919917003456" target="_blank" class="cta-btn whatsapp" title="Chat on WhatsApp"><i class="fab fa-whatsapp"></i></a>
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

        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const video = document.getElementById("heroVideo");
            if (video) {
                video.play().catch(() => {
                    video.style.display = "none";
                    document.querySelector(".hero").style.backgroundImage = "url('img/yogaBhawna12.jpg')";
                    document.querySelector(".hero").style.backgroundSize = "cover";
                    document.querySelector(".hero").style.backgroundPosition = "center";
                });
            }
        });
        </script>
    </body>
</html>