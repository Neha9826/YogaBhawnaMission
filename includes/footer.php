<?php
// footer.php
include 'db.php'; // Ensure this points to your DB connection

// 1. FETCH GLOBAL SETTINGS
$settings = [];
$res = $conn->query("SELECT * FROM site_settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// 2. FETCH SOCIAL LINKS
$socials = $conn->query("SELECT * FROM social_links");

// 3. FETCH EXTRA SECTIONS (like "Opening Hours")
$extras = [];
$extRes = $conn->query("SELECT * FROM site_extra_sections");
while ($row = $extRes->fetch_assoc()) {
    $extras[$row['section_slug']] = $row; // Store by slug for easy access
}

// Prepare phone links
$phone = $settings['contact_phone'] ?? '';
$plainPhone = preg_replace('/\D+/', '', $phone);
$telHref = $plainPhone ? "tel:{$plainPhone}" : "#";
$waHref = $plainPhone ? "https://wa.me/{$plainPhone}" : "#";
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
    .new-footer {
        background-color: #d8dbdcff; /* Dark background from screenshot */
        color: #000000ff; /* Light gray text */
        padding-top: 60px;
        font-size: 15px;
        line-height: 1.7;
    }
    .new-footer .footer-top {
        padding-bottom: 30px;
    }
    .new-footer-col h5 {
        color: #000000ff; /* White headings */
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 25px;
        text-transform: capitalize; /* Matches screenshot "Our Other Themes" */
    }
    .new-footer-col p {
        color: #000000ff;
        margin-bottom: 15px;
    }
    .new-footer-col .line-button {
        color: #018a8aff; /* Kept your brand color for the link */
        text-decoration: none;
        font-weight: 600;
    }
    .new-footer-col .line-button:hover {
        color: #000000ff;
    }
    .new-footer-col ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .new-footer-col ul li {
        margin-bottom: 12px;
    }
    .new-footer-col ul li a {
        color: #000000ff;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .new-footer-col ul li a:hover {
        color: #000000ff;
        text-decoration: none;
    }
    /* Styles for the "Connect with us" social icons */
    .new-footer-socials {
        margin-top: 25px;
    }
    .new-footer-socials a {
        display: inline-block;
        color: #000000ff;
        font-size: 20px;
        margin-right: 18px;
        transition: color 0.3s ease;
    }
    .new-footer-socials a:hover {
        color: #000000ff;
    }
    /* Bottom Copyright Bar */
    .new-footer-bottom {
        border-top: 1px solid #333; /* Faint line like screenshot */
        padding: 30px 0;
        margin-top: 30px;
    }
    .new-footer-bottom p {
        color: #000000ff;
        font-size: 14px;
        margin: 0;
    }
    .new-footer-bottom p a {
        color: #018a8aff; /* Kept your brand color */
        text-decoration: none;
    }
    .new-footer-bottom p a:hover {
        color: #000000ff;
    }
    /* Ensure links from Reservation block look right */
    .new-footer-col .reservation-links a {
        color: #000000ff;
        text-decoration: none;
    }
    .new-footer-col .reservation-links a:hover {
        color: #000000ff;
    }

    /* MOBILE ONLY: Center align footer content */
    @media (max-width: 767px) {
        .new-footer-col {
            text-align: center !important;
            margin-bottom: 40px; /* Adds space between the stacked columns */
        }
        .new-footer-col h5 {
            text-align: center;
        }
        .new-footer-col ul {
            padding: 0;
        }
    }
</style>
<footer class="new-footer">
    <div class="footer-top">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="new-footer-col">
                        <h5>Address</h5>
                        <p>
                            <?= nl2br(htmlspecialchars($settings['address'] ?? '')) ?>
                        </p>
                        <a href="<?= htmlspecialchars($settings['map_embed_url'] ?? '#') ?>" target="_blank" class="line-button">Get Direction</a>

                        <h5 style="margin-top: 30px;">Connect with us</h5>
                        <div class="new-footer-socials">
                            <?php while($soc = $socials->fetch_assoc()): ?>
                                <a href="<?= htmlspecialchars($soc['url']) ?>" target="_blank" title="<?= htmlspecialchars($soc['platform_name']) ?>">
                                    <i class="<?= htmlspecialchars($soc['icon']) ?>"></i>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="new-footer-col">
                        <h5>Navigation</h5>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="services.php">Services</a></li>
                            <li><a href="classes.php">Our Classes</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="new-footer-col">
                        <h5>Reservation</h5>
                        <p class="reservation-links">
                            <?php if ($plainPhone): ?>
                                <a href="<?= htmlspecialchars($telHref) ?>"><?= htmlspecialchars($settings['contact_phone']) ?></a><br>
                            <?php endif; ?>
                            <a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? '') ?>"><?= htmlspecialchars($settings['contact_email'] ?? '') ?></a>
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="new-footer-col">
                        <?php if(isset($extras['opening_hours'])): ?>
                            <h5><?= htmlspecialchars($extras['opening_hours']['title']) ?></h5>
                            <?= $extras['opening_hours']['content'] ?> 
                        <?php else: ?>
                            <h5>Opening Hours</h5>
                            <ul>
                                <li>Mon - Fri: 9:00 AM - 6:00 PM</li>
                                <li>Sat: 10:00 AM - 4:00 PM</li>
                                <li>Sun: Closed</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="new-footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="copy_right text-center"> Copyright ©<script>document.write(new Date().getFullYear());</script>
                        All rights reserved
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
window.addEventListener('scroll', function() {
  // Check if nav exists before adding class
  const nav = document.querySelector('.navbar');
  if (nav) {
    if (window.scrollY > 50) {
      nav.classList.add('sticky');
    } else {
      nav.classList.remove('sticky');
    }
  }
});
</script>