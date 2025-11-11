<?php
// packageDetails.php - robust, schema-aware, complete page

require_once __DIR__ . '/yoga_session.php';
require_once __DIR__ . '/db.php'; // provides $conn (mysqli)

// small helper (safe output)
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }


// package id
$packageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($packageId <= 0) {
    http_response_code(400);
    echo "Invalid package ID.";
    exit;
}

/*
  1) Fetch package + retreat + organization information
     (ensuring both package and retreat are published).
*/
$pkg = null;
$sql = "
    SELECT p.*, r.title AS retreat_title, r.short_description AS retreat_short, r.full_description AS retreat_full,
           r.style AS retreat_style, r.organization_id, o.name AS org_name, o.address, o.city, o.state, o.country
    FROM yoga_packages p
    JOIN yoga_retreats r ON p.retreat_id = r.id
    JOIN organizations o ON r.organization_id = o.id
    WHERE p.id = ? AND p.is_published = 1 AND r.is_published = 1
    LIMIT 1
";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $pkg = $res->fetch_assoc();
    }
    $stmt->close();
}
if (!$pkg) {
    http_response_code(404);
    echo "Package not found or not published.";
    exit;
}

$retreatId = (int)$pkg['retreat_id'];
$orgId = (int)$pkg['organization_id'];

/*
  2) Gallery: images (yoga_retreat_images) — fallback to yoga_retreat_media images if needed
*/
$gallery = [];
if ($gstmt = $conn->prepare("SELECT id, image_path, alt_text, is_primary FROM yoga_retreat_images WHERE retreat_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC")) {
    $gstmt->bind_param('i', $retreatId);
    $gstmt->execute();
    $gres = $gstmt->get_result();
    while ($row = $gres->fetch_assoc()) {
        $gallery[] = $row;
    }
    $gstmt->close();
}
if (empty($gallery)) {
    // fallback to yoga_retreat_media images if that table exists
    $mediaExists = $conn->query("SHOW TABLES LIKE 'yoga_retreat_media'")->num_rows > 0;
    if ($mediaExists) {
        if ($mstmt = $conn->prepare("SELECT id, media_path FROM yoga_retreat_media WHERE retreat_id = ? AND type = 'image' ORDER BY id ASC")) {
            $mstmt->bind_param('i', $retreatId);
            $mres = $mstmt->get_result();
            while ($row = $mres->fetch_assoc()) {
                $gallery[] = ['image_path' => $row['media_path'], 'alt_text' => ''];
            }
            $mstmt->close();
        }
    }
}

/*
  3) Daily schedule - yoga_package_schedule (if exists)
*/
$schedule = [];
if ($conn->query("SHOW TABLES LIKE 'yoga_package_schedule'")->num_rows) {
    if ($sstmt = $conn->prepare("SELECT id, time, activity FROM yoga_package_schedule WHERE package_id = ? ORDER BY time ASC, id ASC")) {
        $sstmt->bind_param('i', $packageId);
        $sstmt->execute();
        $sres = $sstmt->get_result();
        while ($r = $sres->fetch_assoc()) $schedule[] = $r;
        $sstmt->close();
    }
}

/*
  4) Skill levels - yoga_retreat_levels (may contain enum 'Beginner','Intermediate','Advanced','All')
*/
$levels = [];
if ($conn->query("SHOW TABLES LIKE 'yoga_retreat_levels'")->num_rows) {
    if ($lstmt = $conn->prepare("SELECT level FROM yoga_retreat_levels WHERE retreat_id = ? ORDER BY level ASC")) {
        $lstmt->bind_param('i', $retreatId);
        $lstmt->execute();
        $lres = $lstmt->get_result();
        while ($r = $lres->fetch_assoc()) $levels[] = $r['level'];
        $lstmt->close();
    }
}

/*
  5) Amenities: yoga_retreat_amenities -> yoga_amenities
*/
$amenities = [];
$amenitiesTableExists = $conn->query("SHOW TABLES LIKE 'yoga_amenities'")->num_rows > 0;
if ($conn->query("SHOW TABLES LIKE 'yoga_retreat_amenities'")->num_rows && $amenitiesTableExists) {
    if ($astmt = $conn->prepare("
        SELECT a.id, a.name, COALESCE(a.icon_class, 'bi-check-circle') AS icon_class
        FROM yoga_retreat_amenities ra
        JOIN yoga_amenities a ON ra.amenity_id = a.id
        WHERE ra.retreat_id = ?
        ORDER BY a.name ASC
    ")) {
        $astmt->bind_param('i', $retreatId);
        $astmt->execute();
        $ares = $astmt->get_result();
        while ($r = $ares->fetch_assoc()) $amenities[] = $r;
        $astmt->close();
    }
} else {
    if ($conn->query("SHOW TABLES LIKE 'yoga_retreat_amenities'")->num_rows) {
        $tmpRes = $conn->query("SELECT amenity_id FROM yoga_retreat_amenities WHERE retreat_id = " . intval($retreatId));
        if ($tmpRes && $tmpRes->num_rows) {
            while ($r = $tmpRes->fetch_assoc()) $amenities[] = ['id' => $r['amenity_id'], 'name' => 'Amenity #' . $r['amenity_id'], 'icon_class' => 'bi-check-circle'];
        }
    }
}

/*
  6) Instructors for this retreat (via yoga_retreat_instructors -> yoga_instructors)
*/
$instructors = [];
if ($conn->query("SHOW TABLES LIKE 'yoga_retreat_instructors'")->num_rows && $conn->query("SHOW TABLES LIKE 'yoga_instructors'")->num_rows) {
    if ($istmt = $conn->prepare("
        SELECT i.id, i.name, i.bio, i.photo, i.specialization, i.experience_years
        FROM yoga_retreat_instructors ri
        JOIN yoga_instructors i ON ri.instructor_id = i.id
        WHERE ri.retreat_id = ?
        ORDER BY i.name ASC
    ")) {
        $istmt->bind_param('i', $retreatId);
        $istmt->execute();
        $ires = $istmt->get_result();
        while ($r = $ires->fetch_assoc()) $instructors[] = $r;
        $istmt->close();
    }
}

/*
  7) Retreat media (videos) - optional
*/
$videos = [];
if ($conn->query("SHOW TABLES LIKE 'yoga_retreat_media'")->num_rows) {
    if ($mv = $conn->prepare("SELECT id, media_path, type FROM yoga_retreat_media WHERE retreat_id = ? AND type = 'video' ORDER BY id ASC")) {
        $mv->bind_param('i', $retreatId);
        $mv->execute();
        $mres = $mv->get_result();
        while ($r = $mres->fetch_assoc()) $videos[] = $r;
        $mv->close();
    }
}

// --- fetch accommodations for this package (and their images)
$accommodations = [];
$acQ = $conn->prepare("SELECT id, accommodation_type, price_per_person FROM yoga_package_accommodations WHERE package_id = ? ORDER BY id ASC");
if ($acQ) {
    $acQ->bind_param('i', $packageId);
    $acQ->execute();
    $acRes = $acQ->get_result();
    while ($a = $acRes->fetch_assoc()) {
        $a['images'] = [];
        $imgQ = $conn->prepare("SELECT id, image_path FROM yoga_accommodation_images WHERE accommodation_id = ? ORDER BY id ASC");
        if ($imgQ) {
            $imgQ->bind_param('i', $a['id']);
            $imgQ->execute();
            $imgR = $imgQ->get_result();
            while ($im = $imgR->fetch_assoc()) $a['images'][] = $im;
            $imgQ->close();
        }
        $accommodations[] = $a;
    }
    $acQ->close();
}

// --- fetch batches for this package (open ones only)
$batches = [];
$bstmt = $conn->prepare("
    SELECT id, start_date, end_date, status, capacity, available_slots 
    FROM yoga_batches 
    WHERE package_id = ? AND status = 'open' 
    ORDER BY start_date ASC
");
if ($bstmt) {
    $bstmt->bind_param('i', $packageId);
    $bstmt->execute();
    $bres = $bstmt->get_result();
    while ($b = $bres->fetch_assoc()) {
        $batches[] = $b;
    }
    $bstmt->close();
}

// reviews count
$reviews_count = 0;
if ($conn->query("SHOW TABLES LIKE 'y_reviews'")->num_rows) {
    $rvq = $conn->prepare("SELECT COUNT(*) AS c FROM y_reviews WHERE retreat_id = ?");
    if ($rvq) {
        $rvq->bind_param('i', $retreatId);
        $rvq->execute();
        $rr = $rvq->get_result()->fetch_assoc();
        $reviews_count = (int)($rr['c'] ?? 0);
        $rvq->close();
    }
}

// Helper to get gallery image path, checking for array/string
function getImagePath($img) {
    if (is_array($img) && isset($img['image_path'])) {
        return $img['image_path'];
    }
    if (is_string($img)) {
        return $img;
    }
    return 'images/default-package.jpg'; // fallback
}

// Prepare gallery images for the grid
$galleryGrid = array_slice($gallery, 0, 5);
$galleryAll = $gallery; // for modal

// Fill grid with placeholders if less than 5 images
$placeholderImg = 'https://via.placeholder.com/600x400.png?text=Yoga+Retreat';
while (count($galleryGrid) < 5) {
    $galleryGrid[] = ['image_path' => $placeholderImg, 'alt_text' => 'Placeholder'];
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= esc($pkg['title']) ?></title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="/yoga.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    :root {
      --brand-color: #008080; /* Teal color from reference */
      --brand-color-dark: #006666;
      --brand-color-light: #e6f2f2;
      --star-color: #ffb400;
      --text-body: #333;
      --text-muted: #666;
      --border-color: #e0e0e0;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      color: var(--text-body);
      background-color: #f8f9fa;
    }
    
    /* Remove the old hero */
    .hero { display: none; }

    /* --- New Gallery Grid --- */
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      grid-template-rows: repeat(2, 1fr);
      gap: 8px;
      height: 450px; /* Adjust height as needed */
      max-width: 1200px;
      margin: 1rem auto;
      padding: 0 1rem;
    }
    .gallery-item {
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      border-radius: 8px;
      overflow: hidden;
      cursor: pointer;
      position: relative;
    }
    .gallery-item:first-child {
      grid-column: 1 / 3;
      grid-row: 1 / 3;
    }
    .gallery-item:nth-child(2) { grid-column: 3 / 4; grid-row: 1 / 2; }
    .gallery-item:nth-child(3) { grid-column: 4 / 5; grid-row: 1 / 2; }
    .gallery-item:nth-child(4) { grid-column: 3 / 4; grid-row: 2 / 3; }
    .gallery-item:nth-child(5) { grid-column: 4 / 5; grid-row: 2 / 3; }
    
    .gallery-item .view-all-btn {
      position: absolute;
      bottom: 1rem;
      right: 1rem;
      background-color: rgba(255, 255, 255, 0.9);
      color: #000;
      border: 1px solid #ccc;
    }
    
    @media (max-width: 767px) {
      .gallery-grid {
        height: 300px;
        grid-template-columns: 1fr;
        grid-template-rows: 1fr;
      }
      .gallery-item:not(:first-child) {
        display: none; /* Hide smaller images on mobile */
      }
      .gallery-item:first-child {
        grid-column: 1 / 2;
        grid-row: 1 / 2;
      }
      .gallery-item .view-all-btn {
        bottom: 0.5rem;
        right: 0.5rem;
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
      }
    }
    
    /* --- Page Header --- */
    .page-header h1 {
      font-size: 2.25rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 0.5rem;
    }
    .page-header .location,
    .page-header .reviews-link {
      font-size: 1rem;
      color: var(--text-muted);
    }
    .page-header .reviews-link .bi-star-fill {
      color: var(--star-color);
    }
    
    /* --- Main Content Layout --- */
    .content-section {
      background-color: #fff;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    .section-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #111;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid var(--border-color);
    }
    
    /* --- Share Box --- */
    .share-box-inline {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .share-box-inline h6 {
      margin: 0;
      font-weight: 600;
    }
    .share-box-inline .share-icon {
      font-size: 1.25rem;
      color: var(--text-muted);
      text-decoration: none;
    }
    .share-box-inline .share-icon:hover { color: var(--brand-color); }

    /* --- Highlights / Amenities --- */
    .highlight-list {
      padding-left: 0;
      list-style-type: none;
      columns: 2;
    }
    .highlight-list li {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
      font-size: 0.95rem;
    }
    .highlight-list .icon {
      color: var(--brand-color);
      font-size: 1.25rem;
    }
    @media (max-width: 576px) {
      .highlight-list { columns: 1; }
    }

    /* --- Instructors --- */
    .instructor-card {
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 1rem;
      background-color: #fff;
    }
    .instructor-photo {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 50%;
    }
    .instructor-card h6 {
      font-weight: 600;
      color: var(--brand-color);
    }
    
    /* --- Sticky Booking Box --- */
    .booking-box-sticky {
      position: sticky;
      top: 20px;
      background-color: #fff;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .booking-box-header {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid var(--border-color);
    }
    .booking-box-header .price-from {
      font-size: 0.9rem;
      color: var(--text-muted);
    }
    .booking-box-header .price-main {
      font-size: 1.75rem;
      font-weight: 700;
      color: #111;
    }
    .booking-box-header .price-person {
      font-size: 0.9rem;
      color: var(--text-muted);
      font-weight: 500;
    }
    .booking-box-body {
      padding: 1.25rem;
    }
    .booking-box-body .form-label {
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    
    /* Batch/Date selection */
    .batch-option {
      border: 1px solid var(--border-color);
      border-radius: 6px;
      padding: 0.75rem;
      margin-bottom: 0.5rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .batch-option:hover {
      background-color: #f8f8f8;
    }
    .batch-option.active {
      border-color: var(--brand-color);
      background-color: var(--brand-color-light);
      box-shadow: 0 0 0 2px var(--brand-color-light);
    }
    .batch-option strong { font-size: 0.9rem; }
    .batch-option .small { font-size: 0.8rem; }
    .selectBatchBtn {
      font-size: 0.85rem;
      padding: 0.25rem 0.75rem;
    }
    
    /* Accommodation selection */
    #accomList .list-group-item {
      padding: 0.75rem 1rem;
      cursor: pointer;
    }
    #accomList .list-group-item input[type="radio"] {
      margin-right: 0.5rem;
    }
    #accomList .list-group-item:hover {
      background-color: #f8f8f8;
    }
    #accomList .list-group-item.active {
       background-color: var(--brand-color-light);
       border-color: var(--brand-color);
       color: var(--text-body); /* <-- ADD THIS LINE */
    }
    .accom-price {
      font-size: 0.9rem;
      font-weight: 800;
      color: #ab1111ff;
    }
    .show-photos-btn {
      font-size: 0.85rem;
      text-decoration: none;
      font-weight: 500;
      color: var(--brand-color);
    }
    .show-photos-btn:hover {
      text-decoration: underline;
    }
    
    /* Booking Buttons */
    .booking-btn-group {
      display: grid;
      grid-template-columns: 1fr;
      gap: 0.75rem;
      /* padding-top: 1rem; */
    }

    /* --- ADD THIS ENTIRE NEW RULE --- */
    .booking-box-footer {
      padding: 1.25rem;
      border-top: 1px solid var(--border-color);
      background-color: #fff; /* Ensures it's opaque */
      margin-top: auto; /* Pushes it to the bottom */
      border-bottom-left-radius: 8px; /* Match parent card */
      border-bottom-right-radius: 8px; /* Match parent card */
    }

    .booking-btn-group .btn {
      padding: 0.75rem;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 6px;
    }
    .btn-brand-primary {
      background-color: var(--brand-color);
      border-color: var(--brand-color);
      color: #fff;
    }
    .btn-brand-primary:hover {
      background-color: var(--brand-color-dark);
      border-color: var(--brand-color-dark);
      color: #fff;
    }
    .btn-brand-outline {
      background-color: #fff;
      border-color: var(--brand-color);
      color: var(--brand-color);
    }
    .btn-brand-outline:hover {
      background-color: var(--brand-color-light);
      border-color: var(--brand-color);
      color: var(--brand-color);
    }

    /* Remove old fixed buttons */
    .fixed-booking-buttons { display: none; }
    
    /* Responsive Booking Box (becomes static on mobile) */
    @media (max-width: 991.98px) {
      .booking-box-sticky {
        position: static;
        top: auto;
        margin-top: 1.5rem;
        max-height: none; /* <-- ADD THIS */
        display: block; /* <-- ADD THIS */
      }

      .booking-box-footer { /* <-- MODIFY THIS (was .booking-box-sticky .booking-btn-group) */
        display: none; 
      }
      
      .booking-box-body { /* <-- ADD THIS to reset scrolling */
          overflow-y: visible;
      }
      /* Show the mobile-only buttons as a fallback */
      .fixed-booking-buttons {
        display: block;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        padding: 0.75rem;
        border-top: 1px solid var(--border-color);
        z-index: 1000;
      }
      .booking-box-sticky .booking-btn-group {
        display: none; /* Hide sidebar buttons on mobile */
      }
      body {
        padding-bottom: 70px; /* Space for fixed buttons */
      }
    }
  </style>
</head>
<body class="yoga-page">

<?php include __DIR__ . '/yoga_navbar.php'; ?>


<div class="gallery-grid">
  <div class="gallery-item" style="background-image: url('<?= esc(getImagePath($galleryGrid[0])) ?>');" data-bs-toggle="modal" data-bs-target="#galleryModal" data-bs-slide-to="0"></div>
  <div class="gallery-item" style="background-image: url('<?= esc(getImagePath($galleryGrid[1])) ?>');" data-bs-toggle="modal" data-bs-target="#galleryModal" data-bs-slide-to="1"></div>
  <div class="gallery-item" style="background-image: url('<?= esc(getImagePath($galleryGrid[2])) ?>');" data-bs-toggle="modal" data-bs-target="#galleryModal" data-bs-slide-to="2"></div>
  <div class="gallery-item" style="background-image: url('<?= esc(getImagePath($galleryGrid[3])) ?>');" data-bs-toggle="modal" data-bs-target="#galleryModal" data-bs-slide-to="3"></div>
  <div class="gallery-item" style="background-image: url('<?= esc(getImagePath($galleryGrid[4])) ?>');" data-bs-toggle="modal" data-bs-target="#galleryModal" data-bs-slide-to="4">
    <button class="btn btn-sm view-all-btn" data-bs-toggle="modal" data-bs-target="#galleryModal">
      <i class="bi bi-images me-1"></i> View all
    </button>
  </div>
</div>

<main class="py-4">
  <div class="container">
  
    <div class="page-header mb-4">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1><?= esc($pkg['title']) ?></h1>
          <span class="location">
            <i class="bi bi-geo-alt-fill me-1"></i>
            <?= esc($pkg['city'] ?? '') ?>, <?= esc($pkg['country'] ?? '') ?>
          </span>
        </div>
        <div class="col-md-4 text-md-end mt-2 mt-md-0">
          <?php if ($reviews_count > 0): ?>
            <a href="#reviews" class="reviews-link text-decoration-none">
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-half"></i>
              <span class="ms-1 fw-bold"><?= $reviews_count ?> reviews</span>
            </a>
          <?php else: ?>
             <span class="reviews-link">
                <i class="bi bi-star"></i>
                <span class="ms-1">No reviews yet</span>
             </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  
    <div class="row g-4">
      <div class="col-lg-8">

        <div class="share-box-inline mb-4">
          <h6 class="text-uppercase">Share this listing:</h6>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('packageDetails.php?id=' . $pkg['id']) ?>" target="_blank" class="share-icon"><i class="fab fa-facebook-f"></i></a>
          <a href="https://api.whatsapp.com/send?text=<?= urlencode('Check out this Yoga Retreat: ' . 'packageDetails.php?id=' . $pkg['id']) ?>" target="_blank" class="share-icon"><i class="fab fa-whatsapp"></i></a>
          <a href="#" target="_blank" class="share-icon"><i class="fab fa-twitter"></i></a>
          <a href="#" target="_blank" class="share-icon"><i class="bi bi-envelope-fill"></i></a>
        </div>

        <section class="content-section">
          <h2 class="section-title"><?= esc($pkg['retreat_title']) ?></h2>
          <div class="lead mb-3"><?= nl2br(esc($pkg['retreat_short'] ?: $pkg['description'] ?: '')) ?></div>
          <p><?= nl2br(esc($pkg['retreat_full'] ?: 'Program details will be updated.')) ?></p>
        </section>

        <section class="content-section">
          <h2 class="section-title">Highlights</h2>
          <?php if (!empty($amenities)): ?>
            <ul class="highlight-list">
              <?php foreach ($amenities as $am): ?>
                <li>
                  <i class="icon <?= esc($am['icon_class'] ?: 'bi-check-circle-fill') ?>"></i>
                  <span><?= esc($am['name']) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">No highlights listed yet.</p>
          <?php endif; ?>
        </section>
        
        <section class="content-section">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-2">Skill level</h5>
                    <?php if (!empty($levels)): ?>
                      <p><?= esc(implode(', ', $levels)) ?></p>
                    <?php else: ?>
                      <p class="text-muted">All levels</p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold mb-2">Yoga styles</h5>
                    <p><?= esc($pkg['retreat_style'] ?: 'General Yoga') ?></p>
                </div>
            </div>
        </section>

        <section class="content-section">
          <h2 class="section-title">Typical daily schedule</h2>
          <?php if (!empty($schedule)): ?>
            <ul class="list-unstyled">
              <?php foreach ($schedule as $s): ?>
                <li class="mb-2 pb-2 border-bottom">
                  <strong><?= esc(date('h:i A', strtotime($s['time']))) ?></strong>
                  <p class="mb-0 ms-2"><?= esc($s['activity']) ?></p>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">No daily schedule available for this package.</p>
          <?php endif; ?>
        </section>

        <section class="content-section">
          <h2 class="section-title">Meet the Instructors</h2>
          <?php if (!empty($instructors)): ?>
            <div id="instructorList" class="d-grid gap-3">
              <?php foreach ($instructors as $index => $ins): ?>
                <div class="d-flex align-items-start gap-3 instructor-card <?= $index >= 2 ? 'extra-instructor d-none' : '' ?>">
                  <img src="<?= esc($ins['photo'] ?? 'uploads/default-user.png') ?>"
                       alt="<?= esc($ins['name']) ?>"
                       class="instructor-photo">
                  <div class="flex-grow-1">
                    <h6 class="mb-0"><?= esc($ins['name']) ?></h6>
                    <?php if (!empty($ins['specialization'])): ?>
                      <p class="text-muted small mb-1"><?= esc($ins['specialization']) ?></p>
                    <?php endif; ?>
                    <p class="text-secondary small lh-base mb-0">
                      <?= nl2br(esc(substr($ins['bio'], 0, 350))) ?>
                      <?= strlen($ins['bio']) > 350 ? '…' : '' ?>
                    </p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <?php if (count($instructors) > 2): ?>
              <div class="text-center mt-3">
                <button type="button" id="toggleInstructors" class="btn btn-outline-secondary btn-sm">
                  Show More <i class="bi bi-chevron-down"></i>
                </button>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <p class="text-muted">No instructors listed yet.</p>
          <?php endif; ?>
        </section>

        <section class="content-section" id="reviews">
            <h2 class="section-title">Reviews</h2>
            <div class="d-flex align-items-center mb-3">
                <span class="fw-bold fs-4 me-2">4.8</span>
                <div class_="me-2">
                    <i class="bi bi-star-fill" style="color:var(--star-color);"></i>
                    <i class="bi bi-star-fill" style="color:var(--star-color);"></i>
                    <i class="bi bi-star-fill" style="color:var(--star-color);"></i>
                    <i class="bi bi-star-fill" style="color:var(--star-color);"></i>
                    <i class="bi bi-star-half" style="color:var(--star-color);"></i>
                </div>
                <span class="text-muted ms-2">(Based on <?= $reviews_count ?> reviews)</span>
            </div>
            <div class="review-item border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between">
                    <h6 class="fw-bold">Amazing Experience!</h6>
                    <span class="small text-muted">March 27, 2025</span>
                </div>
                <p class="small">"I liked everything about this school, all the staff is very spiritual, the founder was educated in a Gurukul, so they have the seeking of the..."</p>
                <span class="fw-bold small">Vlad A. - Romania</span>
            </div>
             <div class="review-item">
                <h6 class="fw-bold">Life Changing</h6>
                <p class="small">"The instructors were knowledgeable and the location was breathtaking. Highly recommend."</p>
                <span class="fw-bold small">Jane D. - USA</span>
            </div>
        </section>

        <section class="content-section">
          <h2 class="section-title">Location</h2>
            <p>
              <?= esc($pkg['address'] ?? '') ?><br>
              <?= esc($pkg['city'] ?? '') ?><?= (!empty($pkg['state']) ? ', ' . esc($pkg['state']) : '') ?><?= (!empty($pkg['country']) ? ', ' . esc($pkg['country']) : '') ?>
            </p>
            <div style="height:300px; background:#eee; border-radius:8px;" class="d-flex align-items-center justify-content-center text-muted">
                (Map Placeholder)
            </div>
        </section>    

        <?php if (!empty($videos)): ?>
          <section class="content-section">
            <h2 class="section-title">Videos</h2>
            <div class="ratio ratio-16x9">
              <?php
              $v = $videos[0];
              $mp = esc($v['media_path']);
              if (strpos($mp, 'youtube.com') !== false || strpos($mp, 'youtu.be') !== false) {
                  $ytid = null;
                  if (preg_match('#(?:v=|/)([A-Za-z0-9_-]{6,})#', $mp, $m)) $ytid = $m[1];
                  if ($ytid) {
                      echo '<iframe src="https://www.youtube.com/embed/' . esc($ytid) . '" style="width:100%;height:100%; border-radius: 8px;" frameborder="0" allowfullscreen></iframe>';
                  } else {
                      echo '<a href="' . $mp . '" target="_blank">' . $mp . '</a>';
                  }
              } else {
                  echo '<a href="' . $mp . '" target="_blank">' . $mp . '</a>';
              }
              ?>
            </div>
          </section>
        <?php endif; ?>

      </div> <div class="col-lg-4">
        <div class="booking-box-sticky">
          <div class="booking-box-header">
            <div class="price-from">Starting from</div>
            <span id="basePrice" class="price-main">₹ <?= number_format((float)$pkg['price_per_person'], 0) ?></span>
            <span class="price-person">/ person</span>
          </div>
          
          <div class="booking-box-body">
            <div class="mb-3">
              <label class="form-label">Select Batch or Choose Dates</label>
              <?php if (!empty($batches)): ?>
                <div id="batchCalendar" class="mb-2">
                  <?php foreach (array_slice($batches, 0, 3) as $b): // Show first 3
                    $start = date('M d, Y', strtotime($b['start_date']));
                    $end = date('M d, Y', strtotime($b['end_date']));
                    $slots = (int)$b['available_slots'];
                  ?>
                    <div class="batch-option" data-id="<?= $b['id'] ?>" data-start="<?= $b['start_date'] ?>" data-end="<?= $b['end_date'] ?>">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <strong><?= $start ?> → <?= $end ?></strong>
                          <div class="small text-muted">Slots: <?= $slots ?></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary selectBatchBtn">Select</button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                 <?php else: ?>
                <div class="text-muted small mb-2">No predefined batches.</div>
              <?php endif; ?>
              <input type="hidden" name="batch_id" id="batch_id" value="">
            </div>

            <div class="mb-3">
              <label for="check_in" class="form-label">Arrival date</label>
              <input type="date" id="check_in" class="form-control">
            </div>
            <div class="mb-3">
              <label for="check_out" class="form-label">Checkout date</label>
              <input type="date" id="check_out" class="form-control" readonly>
              <input type="hidden" id="package_nights" value="<?= (int)$pkg['nights'] ?>">
            </div>

            <div class_="mb-3">
              <label class="form-label">Select your package</label>
              <div class="list-group" id="accomList">
                <label class="list-group-item list-group-item-action" data-price="<?= (float)$pkg['price_per_person'] ?>">
                  <div class="d-flex w-100 justify-content-between">
                    <div>
                      <input type="radio" name="accommodation_id" value="0" checked onchange="updateSelectedPrice(this)">
                      <span class="fw-bold ms-2">Standard (No accommodation)</span>
                    </div>
                    <span class="accom-price">₹<?= number_format((float)$pkg['price_per_person'], 0) ?></span>
                  </div>
                </label>

                <?php if (!empty($accommodations)): ?>
                  <?php foreach ($accommodations as $idx => $acc): 
                    $priceLabel = number_format((float)$acc['price_per_person'], 0);
                    $imgsJson = htmlspecialchars(json_encode(array_column($acc['images'],'image_path')), ENT_QUOTES, 'UTF-8');
                  ?>
                    <label class="list-group-item list-group-item-action" data-price="<?= (float)$acc['price_per_person'] ?>">
                      <div class="d-flex w-100 justify-content-between">
                         <div>
                            <input type="radio" name="accommodation_id" value="<?= (int)$acc['id'] ?>" onchange="updateSelectedPrice(this)">
                            <span class="fw-bold ms-2"><?= htmlspecialchars($acc['accommodation_type']) ?></span>
                         </div>
                         <span class="accom-price">₹<?= $priceLabel ?></span>
                      </div>
                      <?php if (!empty($acc['images'])): ?>
                        <div class="mt-1 ms-4">
                            <a href="#" class="show-photos-btn" onclick='event.preventDefault(); openAccomModal(<?= json_encode(htmlspecialchars($acc["accommodation_type"])) ?>, <?= $imgsJson ?>, <?= json_encode($priceLabel) ?>)'>
                              Show photos
                            </a>
                        </div>
                      <?php endif; ?>
                    </label>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- <div class="booking-btn-group">
                <button type="button" class="btn btn-brand-primary" id="requestBookBtn">Request to book</button>
                <button type="button" class="btn btn-brand-outline" id="sendQueryBtn">Send Inquiry</button>
            </div> -->
            
          </div>

          <div class="booking-box-footer">
            <div class="booking-btn-group">
                <button type="button" class="btn btn-brand-primary" id="requestBookBtn">Request to book</button>
                <button type="button" class="btn btn-brand-outline" id="sendQueryBtn">Send Inquiry</button>
            </div>
          </div>
          
        </div>
      </div> </div>
  </div>
</main>

<div class="fixed-booking-buttons d-lg-none">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-brand-outline flex-fill" id="sendQueryBtnMobile">Send Inquiry</button>
        <button type="button" class="btn btn-brand-primary flex-fill" id="requestBookBtnMobile">Request to book</button>
    </div>
</div>

<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="galleryModalLabel"><?= esc($pkg['title']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php if (empty($galleryAll)): ?>
              <div class="carousel-item active">
                <img src="<?= $placeholderImg ?>" class="d-block w-100" alt="Placeholder">
              </div>
            <?php else: ?>
              <?php foreach ($galleryAll as $i => $img): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                  <img src="<?= esc(getImagePath($img)) ?>" class="d-block w-100" style="max-height: 80vh; object-fit: contain;" alt="<?= esc(is_array($img) ? $img['alt_text'] : '') ?>">
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="accomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="accomModalTitle">Accommodation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="accomCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner" id="accomCarouselInner"></div>
          <button class="carousel-control-prev" type="button" data-bs-target="#accomCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span></button>
          <button class="carousel-control-next" type="button" data-bs-target="#accomCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span></button>
        </div>
        <div class="mt-3">
          <div id="accomModalPrice" class="fw-bold fs-5"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="queryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="queryForm">
        <div class="modal-header">
          <h5 class="modal-title">Send Inquiry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted">Send an inquiry for <strong><?= esc($pkg['title']) ?></strong></p>
          <input type="hidden" name="package_id" value="<?= (int)$pkg['id'] ?>">
          <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Email</label><input name="email" type="email" class="form-control"></div>
          <div class="mb-2"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
          <div class="mb-2 d-flex gap-2 align-items-center">
            <div style="flex:1"><label class="form-label">Arrival date</label><input name="arrival_date" id="query_arrival_date" type="date" class="form-control"></div>
            <div class="form-check ms-2 pt-3">
              <input type="checkbox" id="query_no_date" name="no_dates_yet" value="1" class="form-check-input">
              <label class="form-check-label small" for="query_no_date">No dates yet</label>
            </div>
          </div>
          <div class="mb-2"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="3"></textarea></div>
          <div id="queryStatus" class="small mt-2"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-brand-primary">Send Inquiry</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="bookingForm">
        <div class="modal-header">
          <h5 class="modal-title">Request to Book</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted">Your request for <strong><?= esc($pkg['title']) ?></strong></p>
          <input type="hidden" name="package_id" value="<?= (int)$pkg['id'] ?>">
          <input type="hidden" name="retreat_id" value="<?= (int)$pkg['retreat_id'] ?>">
          <input type="hidden" name="batch_id" id="booking_batch_id">

          <div class="mb-2">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="<?= isset($_SESSION['yoga_user_name']) ? htmlspecialchars($_SESSION['yoga_user_name']) : '' ?>" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label> 
            <input name="email" type="email" class="form-control" value="<?= isset($_SESSION['yoga_user_email']) ? htmlspecialchars($_SESSION['yoga_user_email']) : '' ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control" value="<?= isset($_SESSION['yoga_user_phone']) ? htmlspecialchars($_SESSION['yoga_user_phone']) : '' ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Arrival date</label>
            <input name="arrival_date" id="booking_arrival_date" type="date" class="form-control">
          </div>

          <div class="mb-2">
            <label class="form-label">Number of Guests</label>
            <input type="number" name="guests" class="form-control" min="1" value="1" required>
          </div>

          <div class="mb-2">
            <label class="form-label">Accommodation</label>
            <select name="accommodation_id" class="form-select">
              <option value="0" data-price="<?= (float)$pkg['price_per_person'] ?>">Standard (no accommodation) — ₹<?= number_format((float)$pkg['price_per_person'], 0) ?></option>
              <?php foreach ($accommodations as $acc): ?>
                <option value="<?= (int)$acc['id'] ?>" data-price="<?= (float)$acc['price_per_person'] ?>">
                  <?= esc($acc['accommodation_type']) ?> — ₹<?= number_format((float)$acc['price_per_person'], 0) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-2">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-control" rows="3"></textarea>
          </div>

          <div id="bookingStatus" class="small mt-2 text-muted"></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand-primary">Request to Book</button>
        </div>
      </form>
    </div>
  </div>
</div>


<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  
  /* Helper: add days to date (returns yyyy-mm-dd) */
  function addDaysToDate(inputDate, days) {
    const d = new Date(inputDate);
    d.setDate(d.getDate() + Number(days));
    return d.toISOString().split('T')[0];
  }

  // Auto set checkout when checkin selected
  document.getElementById('check_in').addEventListener('change', function() {
    const nights = parseInt(document.getElementById('package_nights').value) || 0;
    if (!this.value || nights <= 0) {
        document.getElementById('check_out').value = '';
        return;
    };
    const co = addDaysToDate(this.value, nights);
    document.getElementById('check_out').value = co;
    document.getElementById('query_arrival_date').value = this.value;
    document.getElementById('booking_arrival_date').value = this.value;
  });

  // --- Batch selection logic ---
  document.querySelectorAll('.batch-option').forEach(box => {
    box.addEventListener('click', function() {
      const btn = this.querySelector('.selectBatchBtn');
      
      // Clear custom dates
      const ci = document.getElementById('check_in');
      const co = document.getElementById('check_out');
      ci.value = this.dataset.start;
      co.value = this.dataset.end;
      ci.setAttribute('readonly', true);
      co.setAttribute('readonly', true);
      
      // Set hidden input
      document.getElementById('batch_id').value = this.dataset.id;

      // Reset all buttons and boxes
      document.querySelectorAll('.batch-option').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.selectBatchBtn').forEach(b => {
        b.textContent = 'Select';
        b.classList.replace('btn-success', 'btn-outline-primary');
      });

      // Activate this one
      this.classList.add('active');
      btn.textContent = 'Selected';
      btn.classList.replace('btn-outline-primary', 'btn-success');
    });
  });

  // --- Custom date selection logic ---
  document.getElementById('check_in').addEventListener('focus', function() {
    // Clear batch selection
    document.getElementById('batch_id').value = '';
    document.querySelectorAll('.batch-option').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.selectBatchBtn').forEach(b => {
      b.textContent = 'Select';
      b.classList.replace('btn-success', 'btn-outline-primary');
    });
    // Make dates editable
    this.removeAttribute('readonly');
    // Note: check_out remains readonly, calculated from check_in
  });
  
  // --- Accommodation selection logic ---
  // Make labels clickable to select radio
  document.querySelectorAll('#accomList .list-group-item').forEach(label => {
    label.addEventListener('click', function(e) {
        // Don't trigger if clicking the 'show photos' link
        if (e.target.classList.contains('show-photos-btn')) return;
        
        const radio = this.querySelector('input[type="radio"]');
        if (radio && !radio.checked) {
            radio.checked = true;
            // Manually trigger change event
            radio.dispatchEvent(new Event('change'));
        }
    });
  });

  // Update accommodation price in main box
  window.updateSelectedPrice = function(radio) {
      if (!radio) {
        radio = document.querySelector('input[name="accommodation_id"]:checked');
      }
      
      // Highlight active label
      document.querySelectorAll('#accomList .list-group-item').forEach(l => l.classList.remove('active'));
      const activeLabel = radio.closest('.list-group-item');
      if (activeLabel) {
          activeLabel.classList.add('active');
      }

      // Update price
      const price = radio.closest('.list-group-item').dataset.price;
      if (price) {
          const formattedPrice = '₹ ' + parseFloat(price).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
          document.getElementById('basePrice').textContent = formattedPrice;
      }
  }
  // Set initial price and active state
  updateSelectedPrice(null);


  // --- Instructor Toggle ---
  const toggleBtn = document.getElementById('toggleInstructors');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const hiddenCards = document.querySelectorAll('.extra-instructor');
      const isHidden = hiddenCards[0].classList.contains('d-none');
      hiddenCards.forEach(c => c.classList.toggle('d-none'));
      toggleBtn.innerHTML = isHidden
        ? 'Show Less <i class="bi bi-chevron-up"></i>'
        : 'Show More <i class="bi bi-chevron-down"></i>';
    });
  }

  // --- Modal Button Handlers ---
  const queryModal = new bootstrap.Modal(document.getElementById('queryModal'));
  const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));

  // Sync function for booking modal
  function syncBookingModal() {
    // 1. Sync arrival date
    document.getElementById('booking_arrival_date').value = document.getElementById('check_in').value || '';
    
    // 2. Sync selected accommodation
    const selectedAccomRadio = document.querySelector('input[name="accommodation_id"]:checked');
    const bookingAccomSelect = document.querySelector('#bookingForm select[name="accommodation_id"]');
    if (selectedAccomRadio && bookingAccomSelect) {
      bookingAccomSelect.value = selectedAccomRadio.value;
    }
    
    // 3. Sync batch ID
    document.getElementById('booking_batch_id').value = document.getElementById('batch_id').value || '';
  }

  // Desktop Buttons
  document.getElementById('sendQueryBtn').addEventListener('click', () => queryModal.show());
  document.getElementById('requestBookBtn').addEventListener('click', () => {
    syncBookingModal();
    bookingModal.show();
  });
  
  // Mobile Buttons
  document.getElementById('sendQueryBtnMobile').addEventListener('click', () => queryModal.show());
  document.getElementById('requestBookBtnMobile').addEventListener('click', () => {
    syncBookingModal();
    bookingModal.show();
  });


  // --- AJAX Form Submissions ---

  // AJAX submit (Query)
  const queryForm = document.getElementById("queryForm");
  if (queryForm) {
    queryForm.addEventListener("submit", async function(e) {
      e.preventDefault();
      const status = document.getElementById("queryStatus");
      const submitBtn = queryForm.querySelector('button[type="submit"]');
      status.textContent = "Sending...";
      submitBtn.disabled = true;

      try {
        const res = await fetch("submitQuery.php", { method: "POST", body: new FormData(e.target) });
        const j = await res.json();

        if (j.success) {
          status.innerHTML = '<span class="text-success">Sent successfully!</span>';
          e.target.reset();
          setTimeout(() => {
            queryModal.hide();
            status.textContent = "";
            submitBtn.disabled = false;
          }, 1500);
        } else {
          status.innerHTML = '<span class="text-danger">' + (j.msg || "Error sending.") + '</span>';
          submitBtn.disabled = false;
        }
      } catch (err) {
        status.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
        submitBtn.disabled = false;
      }
    });
  }

  // AJAX submit (Booking)
  const bookingForm = document.getElementById('bookingForm');
  if(bookingForm) {
    bookingForm.addEventListener('submit', async e => {
      e.preventDefault();
      const status = document.getElementById('bookingStatus');
      const submitBtn = bookingForm.querySelector('button[type="submit"]');
      status.innerHTML = '<span class="text-muted">Sending request...</span>';
      submitBtn.disabled = true;

      try {
        const res = await fetch('submitBooking.php', { method: 'POST', body: new FormData(e.target) });
        const j = await res.json();

        if (j.require_login) {
          status.innerHTML = `
            <div class="alert alert-warning p-2 small">
              ${j.msg}<br>
              <a href="login.php" class="btn btn-sm btn-primary mt-2">Login to Continue</a>
            </div>`;
          submitBtn.disabled = false;
          return;
        }

        if (j.success) {
          status.innerHTML = '<span class="text-success">Booking request sent successfully!</span>';
          setTimeout(() => {
            bookingModal.hide();
            status.innerHTML = '';
            submitBtn.disabled = false;
          }, 1500);
        } else {
          status.innerHTML = '<span class="text-danger">' + (j.msg || 'Error submitting booking.') + '</span>';
          submitBtn.disabled = false;
        }
      } catch (err) {
        status.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
        submitBtn.disabled = false;
      }
    });
  }
  
}); // End DOMContentLoaded

// Show Accommodation Photos Modal
function openAccomModal(title, imgs, priceLabel) {
  const inner = document.getElementById('accomCarouselInner');
  document.getElementById('accomModalTitle').textContent = title;
  inner.innerHTML = '';

  if (!imgs || imgs.length === 0) {
    inner.innerHTML = '<div class="carousel-item active"><div class="p-5 text-center text-muted">No images available for this accommodation.</div></div>';
  } else {
    imgs.forEach((src, i) => {
      inner.innerHTML += `
        <div class="carousel-item ${i === 0 ? 'active' : ''}">
          <img src="${src}" class="d-block w-100" style="height:50vh; object-fit:cover;">
        </div>`;
    });
  }
  document.getElementById('accomModalPrice').textContent = 'Price: ₹' + priceLabel + ' / person';
  new bootstrap.Modal(document.getElementById('accomModal')).show();
}

</script>

</body>
</html>