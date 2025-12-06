<?php
// admin/manageSettings.php
session_start();
include '../db.php';

// --- HANDLE SUBMISSIONS ---

// 1. UPDATE GLOBAL SETTINGS
if (isset($_POST['update_globals'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $val = trim($value);
        $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param("ss", $val, $key);
        $stmt->execute();
    }
    header("Location: manageSettings.php?tab=globals"); exit;
}

// 2. ADD SOCIAL LINK
if (isset($_POST['add_social'])) {
    $stmt = $conn->prepare("INSERT INTO social_links (platform_name, url, icon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['platform_name'], $_POST['url'], $_POST['icon']);
    $stmt->execute();
    header("Location: manageSettings.php?tab=social"); exit;
}

// 3. ADD EXTRA SECTION
if (isset($_POST['add_extra'])) {
    $slug = strtolower(str_replace(' ', '_', $_POST['title'])); // Auto generate slug
    $stmt = $conn->prepare("INSERT INTO site_extra_sections (title, content, section_slug) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['title'], $_POST['content'], $slug);
    $stmt->execute();
    header("Location: manageSettings.php?tab=extra"); exit;
}

// 4. DELETE ACTIONS
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = intval($_GET['delete']);
    if ($_GET['type'] == 'social') {
        $conn->query("DELETE FROM social_links WHERE id=$id");
        header("Location: manageSettings.php?tab=social"); exit;
    }
    if ($_GET['type'] == 'extra') {
        $conn->query("DELETE FROM site_extra_sections WHERE id=$id");
        header("Location: manageSettings.php?tab=extra"); exit;
    }
}

// --- FETCH DATA ---
$settings = [];
$res = $conn->query("SELECT * FROM site_settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$socials = $conn->query("SELECT * FROM social_links");
$extras  = $conn->query("SELECT * FROM site_extra_sections");
$activeTab = $_GET['tab'] ?? 'globals';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<style>
    .nav-tabs .nav-link.active { background-color: #0d6efd; color: white; }
    .nav-tabs .nav-link { color: #495057; font-weight: bold; }
    .tab-content { border: 1px solid #dee2e6; border-top: none; padding: 20px; background: #fff; }
    
    /* Icon Picker Styles */
    .icon-grid-item { cursor: pointer; transition: all 0.2s; }
    .icon-grid-item:hover { background-color: #f0f0f0; border-color: #0d6efd !important; transform: scale(1.05); }
</style>

<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include 'includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main class="container-fluid px-4 mt-4">
            <h2>Global Settings</h2>
            
            <ul class="nav nav-tabs" id="settingTabs">
                <li class="nav-item"><a class="nav-link <?= $activeTab=='globals'?'active':'' ?>" href="?tab=globals">Contact Info</a></li>
                <li class="nav-item"><a class="nav-link <?= $activeTab=='social'?'active':'' ?>" href="?tab=social">Social Media</a></li>
                <li class="nav-item"><a class="nav-link <?= $activeTab=='extra'?'active':'' ?>" href="?tab=extra">Extra Sections</a></li>
            </ul>

            <div class="tab-content">
                
                <div class="tab-pane fade <?= $activeTab=='globals'?'show active':'' ?>">
                    <form method="post">
                        <input type="hidden" name="update_globals" value="1">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Site Name</label>
                                <input type="text" name="settings[site_name]" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Contact Email</label>
                                <input type="text" name="settings[contact_email]" class="form-control" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Contact Phone</label>
                                <input type="text" name="settings[contact_phone]" class="form-control" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">WhatsApp Link (wa.me/...)</label>
                                <input type="text" name="settings[whatsapp_url]" class="form-control" value="<?= htmlspecialchars($settings['whatsapp_url'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Address</label>
                            <textarea name="settings[address]" class="form-control"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Google Map Embed Link (src="..." only)</label>
                            <input type="text" name="settings[map_embed_url]" class="form-control" value="<?= htmlspecialchars($settings['map_embed_url'] ?? '') ?>">
                        </div>
                        <button class="btn btn-primary">Save Settings</button>
                    </form>
                </div>

                <div class="tab-pane fade <?= $activeTab=='social'?'show active':'' ?>">
                    <form method="post" class="bg-light p-3 border rounded mb-3">
                        <input type="hidden" name="add_social" value="1">
                        <h6>Add Social Link</h6>
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label>Platform Name</label>
                                <input type="text" name="platform_name" class="form-control" placeholder="e.g. Facebook" required>
                            </div>
                            <div class="col-md-3">
                                <label>Icon Class</label>
                                <div class="input-group">
                                    <input type="text" name="icon" id="socialIconInput" class="form-control" placeholder="Select Icon..." readonly required>
                                    <button type="button" class="btn btn-secondary" onclick="openIconModal()">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>URL</label>
                                <input type="text" name="url" class="form-control" placeholder="https://..." required>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-success w-100">Add</button>
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Icon Preview</th><th>Platform</th><th>URL</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php while($row = $socials->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><i class="<?= $row['icon'] ?> fa-2x"></i></td>
                                <td><?= htmlspecialchars($row['platform_name']) ?></td>
                                <td><?= htmlspecialchars($row['url']) ?></td>
                                <td><a href="?delete=<?= $row['id'] ?>&type=social" class="btn btn-danger btn-sm">Remove</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade <?= $activeTab=='extra'?'show active':'' ?>">
                    <form method="post" class="bg-light p-3 border rounded mb-3">
                        <input type="hidden" name="add_extra" value="1">
                        <h6>Add New Text Section</h6>
                        <div class="mb-2">
                            <input type="text" name="title" class="form-control" placeholder="Title (e.g. Opening Hours)" required>
                        </div>
                        <div class="mb-2">
                            <textarea name="content" class="summernote"></textarea>
                        </div>
                        <button class="btn btn-success">Add Section</button>
                    </form>

                    <div class="row">
                        <?php while($ex = $extras->fetch_assoc()): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header fw-bold d-flex justify-content-between">
                                    <span><?= htmlspecialchars($ex['title']) ?></span>
                                    <small class="text-muted">ID: <?= $ex['section_slug'] ?></small>
                                </div>
                                <div class="card-body">
                                    <?= $ex['content'] ?>
                                </div>
                                <div class="card-footer">
                                    <a href="?delete=<?= $ex['id'] ?>&type=extra" class="btn btn-danger btn-sm w-100" onclick="return confirm('Delete this section?')">Delete</a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>

<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select an Icon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="iconSearchInput" class="form-control mb-3" placeholder="Search icon (e.g. facebook, phone, map)...">
        <div id="iconGrid" class="d-flex flex-wrap justify-content-center" style="height: 300px; overflow-y: auto;">
            </div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    // Initialize Summernote
    $('.summernote').summernote({ height: 120 });

    // --- ICON PICKER LOGIC ---
    
    // 1. Icon Database
    const icons = [
        { class: 'fab fa-facebook-f', name: 'facebook' },
        { class: 'fab fa-facebook-square', name: 'facebook square' },
        { class: 'fab fa-instagram', name: 'instagram' },
        { class: 'fab fa-twitter', name: 'twitter' },
        { class: 'fab fa-youtube', name: 'youtube' },
        { class: 'fab fa-linkedin', name: 'linkedin' },
        { class: 'fab fa-whatsapp', name: 'whatsapp' },
        { class: 'fab fa-pinterest', name: 'pinterest' },
        { class: 'fas fa-envelope', name: 'email envelope' },
        { class: 'fas fa-phone', name: 'phone call' },
        { class: 'fas fa-map-marker-alt', name: 'map location' },
        { class: 'fas fa-globe', name: 'website globe' },
        { class: 'flaticon-yoga-pose', name: 'yoga pose' },
        { class: 'flaticon-workout', name: 'workout dumbbell' }
    ];

    // 2. Open Modal Function
    function openIconModal() {
        const modal = new bootstrap.Modal(document.getElementById('iconPickerModal'));
        renderIcons(icons); // Show all icons initially
        modal.show();
    }

    // 3. Render Icons to Grid
    function renderIcons(list) {
        const grid = document.getElementById('iconGrid');
        grid.innerHTML = ''; // Clear current
        
        if(list.length === 0) {
            grid.innerHTML = '<p class="text-muted">No icons found.</p>';
            return;
        }

        list.forEach(icon => {
            const div = document.createElement('div');
            div.className = 'icon-grid-item p-3 m-1 border rounded text-center';
            div.style.width = '100px';
            div.innerHTML = `<i class="${icon.class} fa-2x"></i><br><small style="font-size:10px;">${icon.name}</small>`;
            
            // On Click: Fill input and close modal
            div.onclick = function() {
                document.getElementById('socialIconInput').value = icon.class;
                // Close modal manually
                const modalEl = document.getElementById('iconPickerModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            };
            
            grid.appendChild(div);
        });
    }

    // 4. Search Filter
    document.getElementById('iconSearchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const filtered = icons.filter(i => i.name.includes(term) || i.class.includes(term));
        renderIcons(filtered);
    });
</script>

</body>
</html>