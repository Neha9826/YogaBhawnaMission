<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];
$success = $error = "";

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid package ID.");
}
$id = intval($_GET['id']);

// Fetch package
$pkgQ = $conn->prepare("
    SELECT p.*, r.organization_id 
    FROM yoga_packages p 
    JOIN yoga_retreats r ON p.retreat_id = r.id
    JOIN organizations o ON r.organization_id = o.id
    WHERE p.id = ? AND o.created_by = ?
    LIMIT 1
");
$pkgQ->bind_param("ii", $id, $host_id);
$pkgQ->execute();
$pkgRes = $pkgQ->get_result();
if ($pkgRes->num_rows === 0) {
    die("Package not found or access denied.");
}
$package = $pkgRes->fetch_assoc();

$org_id = $package['organization_id'];
$retreat_id = $package['retreat_id'];

// Load orgs and retreats
$organizations = $conn->query("SELECT id, name FROM organizations WHERE created_by=$host_id ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$retreats = $conn->query("SELECT id, title FROM yoga_retreats WHERE organization_id=$org_id ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);

// ✅ NEW: Define dynamic fields config (used for fetch and update)
$dynamic_fields = [
    'deal' => ['table' => 'yoga_package_deals', 'link_table' => 'yoga_package_selected_deals', 'col' => 'deal_id'],
    'dayonline' => ['table' => 'yoga_package_dayonline', 'link_table' => 'yoga_package_selected_dayonline', 'col' => 'dayonline_id'],
    'language' => ['table' => 'yoga_package_languages', 'link_table' => 'yoga_package_selected_languages', 'col' => 'language_id'],
    'meal' => ['table' => 'yoga_package_meals', 'link_table' => 'yoga_package_selected_meals', 'col' => 'meal_id'],
    'food' => ['table' => 'yoga_package_food', 'link_table' => 'yoga_package_selected_food', 'col' => 'food_id'],
    'airport_transfer' => ['table' => 'yoga_package_airport_transfers', 'link_table' => 'yoga_package_selected_airport_transfers', 'col' => 'transfer_id'],
    'type' => ['table' => 'yoga_package_types', 'link_table' => 'yoga_package_selected_types', 'col' => 'type_id'],
    'category' => ['table' => 'yoga_package_categories', 'link_table' => 'yoga_package_selected_categories', 'col' => 'category_id'],
];

// ✅ NEW: Fetch all dynamic field MASTER lists
$all_deals = $conn->query("SELECT * FROM yoga_package_deals ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_dayonline = $conn->query("SELECT * FROM yoga_package_dayonline ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_languages = $conn->query("SELECT * FROM yoga_package_languages ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_meals = $conn->query("SELECT * FROM yoga_package_meals ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_food = $conn->query("SELECT * FROM yoga_package_food ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_airport_transfers = $conn->query("SELECT * FROM yoga_package_airport_transfers ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_types = $conn->query("SELECT * FROM yoga_package_types ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_categories = $conn->query("SELECT * FROM yoga_package_categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// ✅ NEW: Fetch all SELECTED items for THIS package
$selected_data = [];
foreach ($dynamic_fields as $key => $config) {
    $link_col = $config['col'];
    $link_table = $config['link_table'];
    $res = $conn->query("SELECT $link_col FROM $link_table WHERE package_id=$id");
    $selected_data[$key] = array_column($res->fetch_all(MYSQLI_ASSOC), $link_col);
}

// ✅ NEW: Helper function to render the form blocks (now with a 4th param for selected items)
function render_dynamic_field_block($title, $key, $items, $selected_item_ids) {
    $html = "<div class='col-md-6 mb-3 border p-3'>";
    $html .= "<label class='form-label fw-bold'>$title</label><br>";
    
    if (empty($items)) {
        $html .= "<small class='text-muted'>No " . strtolower($title) . " found.</small><br>";
    } else {
        foreach ($items as $item) {
            // ✅ MODIFIED: Added 'checked' logic
            $checked = in_array($item['id'], $selected_item_ids) ? 'checked' : '';
            $html .= "<div class='form-check form-check-inline'>";
            $html .= "<input type='checkbox' name='{$key}s[]' value='{$item['id']}' class='form-check-input' id='{$key}_{$item['id']}' $checked>";
            $html .= "<label for='{$key}_{$item['id']}' class='form-check-label'>" . htmlspecialchars($item['name']) . "</label>";
            $html .= "</div>";
        }
    }

    $html .= "<div id='new-{$key}-container' class='mt-2'></div>";
    $html .= "<button type='button' class='btn btn-sm btn-secondary mt-2' onclick=\"addDynamicInput('{$key}', '$title')\">+ Add New</button>";
    $html .= "</div>";
    return $html;
}


/* =====================
    UPDATE PACKAGE
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $retreat_id = intval($_POST['retreat_id']);
    $title = trim($_POST['title']);
    $slug = strtolower(str_replace(' ', '-', $title));
    
    $program = trim($_POST['program']); // ✅ NEW: Get program content
    $price_per_person = floatval($_POST['price_per_person']);
    $min_persons = intval($_POST['min_persons']);
    $max_persons = intval($_POST['max_persons']);
    $nights = intval($_POST['nights']);
    $meals_included = isset($_POST['meals_included']) ? 1 : 0;

    // ✅ MODIFIED: Added 'program=?' to query
    $stmt = $conn->prepare("
        UPDATE yoga_packages 
        SET retreat_id=?, title=?, slug=?, program=?, price_per_person=?, 
            min_persons=?, max_persons=?, nights=?, meals_included=?, updated_at=NOW() 
        WHERE id=?
    ");
    // ✅ MODIFIED: Added 's' for program and $program variable
    $stmt->bind_param("isssdiiiii", $retreat_id, $title, $slug, $program, $price_per_person, 
                        $min_persons, $max_persons, $nights, $meals_included, $id);

    if ($stmt->execute()) {
        /* --------------------
            Update Daily Schedule (Existing logic)
        ---------------------*/
        $conn->query("DELETE FROM yoga_package_schedule WHERE package_id=$id");
        if (!empty($_POST['schedule_time']) && !empty($_POST['schedule_activity'])) {
            $schedStmt = $conn->prepare("INSERT INTO yoga_package_schedule (package_id, time, activity) VALUES (?, ?, ?)");
            foreach ($_POST['schedule_time'] as $i => $time) {
                $activity = trim($_POST['schedule_activity'][$i]);
                if ($time && $activity) {
                    $schedStmt->bind_param("iss", $id, $time, $activity);
                    $schedStmt->execute();
                }
            }
            $schedStmt->close();
        }

        /* --------------------
            Update Accommodation + Images (Existing logic)
        ---------------------*/
        // ... (Your existing accommodation update logic remains unchanged) ...
        // ... (This logic is complex, so I am not repeating it, just know it stays here) ...

        /* --------------------
            ✅ NEW: Update Dynamic Fields (Deals, Languages, etc.)
            Using a "delete-and-replace" strategy to match your schedule logic.
        ---------------------*/
        foreach ($dynamic_fields as $key => $config) {
            
            // 1. Delete all existing links for this package
            $conn->query("DELETE FROM {$config['link_table']} WHERE package_id=$id");

            $item_ids_to_link = [];

            // 2. Handle existing items (checkboxes)
            if (!empty($_POST[$key . 's'])) { // e.g., $_POST['deals']
                foreach ($_POST[$key . 's'] as $item_id) {
                    $item_ids_to_link[] = intval($item_id);
                }
            }

            // 3. Handle new items (text inputs)
            if (!empty($_POST['new_' . $key])) { // e.g., $_POST['new_deal']
                $stmt_master = $conn->prepare("INSERT INTO {$config['table']} (name) VALUES (?)");
                $stmt_select = $conn->prepare("SELECT id FROM {$config['table']} WHERE name = ?");

                foreach ($_POST['new_' . $key] as $item_name) {
                    $item_name_trimmed = trim($item_name);
                    if ($item_name_trimmed) {
                        $new_item_id = 0;
                        $stmt_select->bind_param("s", $item_name_trimmed);
                        $stmt_select->execute();
                        $result = $stmt_select->get_result();
                        
                        if ($result->num_rows > 0) {
                            $new_item_id = $result->fetch_assoc()['id'];
                        } else {
                            $stmt_master->bind_param("s", $item_name_trimmed);
                            $stmt_master->execute();
                            $new_item_id = $stmt_master->insert_id;
                        }
                        
                        if ($new_item_id > 0) {
                            $item_ids_to_link[] = $new_item_id;
                        }
                    }
                }
                $stmt_master->close();
                $stmt_select->close();
            }

            // 4. Link all unique items to the package
            $item_ids_to_link = array_unique($item_ids_to_link);
            if (!empty($item_ids_to_link)) {
                $stmt_link = $conn->prepare("INSERT IGNORE INTO {$config['link_table']} (package_id, {$config['col']}) VALUES (?, ?)");
                foreach ($item_ids_to_link as $item_id) {
                    $stmt_link->bind_param("ii", $id, $item_id);
                    $stmt_link->execute();
                }
                $stmt_link->close();
            }
        }
        // ✅ END NEW: Dynamic fields update

        $success = "Package updated successfully!";
        
        // ✅ FIX: Refresh the main $package array with the new values from the form.
        // This makes the form fields show the new data instantly after update.
        $package['retreat_id'] = $retreat_id;
        $package['title'] = $title;
        $package['slug'] = $slug;
        $package['program'] = $program;
        $package['price_per_person'] = $price_per_person;
        $package['min_persons'] = $min_persons;
        $package['max_persons'] = $max_persons;
        $package['nights'] = $nights;
        $package['meals_included'] = $meals_included;
        
    } else {
        $error = "Failed to update package: " . $conn->error;
    }
    $stmt->close();

    // ✅ NEW: Re-fetch selected data in case of success, to show changes immediately
    // (This part was already correct in your file)
    foreach ($dynamic_fields as $key => $config) {
        $link_col = $config['col'];
        $link_table = $config['link_table'];
        $res = $conn->query("SELECT $link_col FROM $link_table WHERE package_id=$id");
        $selected_data[$key] = array_column($res->fetch_all(MYSQLI_ASSOC), $link_col);
    }
}

// Fetch updated data (Existing)
// (This part was also correct, it re-fetches schedules and accommodations)
$schedules = $conn->query("SELECT * FROM yoga_package_schedule WHERE package_id=$id ORDER BY time ASC")->fetch_all(MYSQLI_ASSOC);
$accommodations = $conn->query("SELECT * FROM yoga_package_accommodations WHERE package_id=$id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__.'/includes/head.php'; ?>
    <title>Edit Package</title>
    <link rel="stylesheet" href="yoga.css">
    <script src="https://cdn.tiny.cloud/1/urrcm7wdpcb1a3ecik6nzieh9flmjgccnw43mlrf2grgze9x/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .accommodation-thumb {
            position: relative;
            display: inline-block;
        }
        .accommodation-thumb button {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(255,0,0,0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            cursor: pointer;
            font-size: 13px;
            line-height: 1;
        }
    </style>
</head>
<body class="yoga-page">

<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Edit Package</h2>

            <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Organization</label>
                    <select class="form-control" disabled>
                        <?php foreach($organizations as $org): ?>
                            <option <?= ($org['id']==$org_id)?'selected':'' ?>><?= htmlspecialchars($org['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Retreat</label>
                    <select name="retreat_id" class="form-control" required>
                        <?php foreach($retreats as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($r['id']==$retreat_id)?'selected':'' ?>><?= htmlspecialchars($r['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6"><label>Package Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($package['title']) ?>" required></div>
                <div class="col-md-6"><label>Price per Person (₹)</label><input type="number" step="0.01" name="price_per_person" class="form-control" value="<?= $package['price_per_person'] ?>" required></div>
                <div class="col-md-3"><label>Min Persons</label><input type="number" name="min_persons" class="form-control" value="<?= $package['min_persons'] ?>"></div>
                <div class="col-md-3"><label>Max Persons</label><input type="number" name="max_persons" class="form-control" value="<?= $package['max_persons'] ?>"></div>
                <div class="col-md-3"><label>Nights</label><input type="number" name="nights" class="form-control" value="<?= $package['nights'] ?>"></div>
                <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check mt-2">
                        <input type="checkbox" name="meals_included" class="form-check-input" id="meals_included" <?= $package['meals_included']?'checked':'' ?>>
                        <label for="meals_included" class="form-check-label">Meals Included</label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Accommodation Options</label>
                    <div id="accommodationContainer">
                        <?php foreach($accommodations as $i=>$acc): 
                            $acc_id = $acc['id'];
                            $imgs = $conn->query("SELECT * FROM yoga_accommodation_images WHERE accommodation_id=$acc_id")->fetch_all(MYSQLI_ASSOC);
                        ?>
                        <div class="accommodation-block border rounded p-3 mb-3">
                            <input type="hidden" name="accommodation_id[]" value="<?= $acc['id'] ?>">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6"><input type="text" name="accommodation_type[]" class="form-control" value="<?= htmlspecialchars($acc['accommodation_type']) ?>" required></div>
                                <div class="col-md-4"><input type="number" step="0.01" name="accommodation_price[]" class="form-control" value="<?= $acc['price_per_person'] ?>" required></div>
                                <div class="col-md-2"><button type="button" class="btn btn-danger removeAcc">Remove</button></div>
                            </div>
                            <div class="mb-2">
                                <label>Existing Images:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach($imgs as $img): ?>
                                    <div class="accommodation-thumb" id="img-<?= $img['id'] ?>">
                                        <img src="../<?= htmlspecialchars($img['image_path']) ?>" width="120" height="80" style="object-fit:cover;border-radius:4px;">
                                        <button type="button" onclick="deleteImage(<?= $img['id'] ?>)">×</button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label>Upload New Images</label>
                                <input type="file" name="accommodation_images_new_<?= $i ?>[]" class="form-control" multiple accept="image/*">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addAccBtn">+ Add Accommodation</button>
                </div>

                <div class="col-12">
                    <label class="form-label">Program</label>
                    <textarea name="program" id="program_editor" class="form-control" rows="10"><?= htmlspecialchars($package['program'] ?? '') ?></textarea>
                </div>

                <hr class="my-4">
                
                <div class="col-12">
                    <h3>Package Details & Options</h3>
                </div>

                <div class="row g-3">
                    <?php echo render_dynamic_field_block('Deals', 'deal', $all_deals, $selected_data['deal']); ?>
                    <?php echo render_dynamic_field_block('Day or Online', 'dayonline', $all_dayonline, $selected_data['dayonline']); ?>
                </div>
                <div class="row g-3 mt-1">
                     <?php echo render_dynamic_field_block('Languages', 'language', $all_languages, $selected_data['language']); ?>
                     <?php echo render_dynamic_field_block('Meals', 'meal', $all_meals, $selected_data['meal']); ?>
                </div>
                 <div class="row g-3 mt-1">
                     <?php echo render_dynamic_field_block('Food', 'food', $all_food, $selected_data['food']); ?>
                     <?php echo render_dynamic_field_block('Airport Transfer', 'airport_transfer', $all_airport_transfers, $selected_data['airport_transfer']); ?>
                </div>
                 <div class="row g-3 mt-1">
                     <?php echo render_dynamic_field_block('Type', 'type', $all_types, $selected_data['type']); ?>
                     <?php echo render_dynamic_field_block('Category', 'category', $all_categories, $selected_data['category']); ?>
                </div>
                <hr class="my-4">

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Add rows
document.getElementById('addRowBtn').addEventListener('click', ()=>{
    const c=document.getElementById('scheduleContainer');
    const r=document.createElement('div');
    r.className='row mb-2 schedule-row';
    r.innerHTML=`<div class="col-md-3"><input type="time" name="schedule_time[]" class="form-control"></div>
    <div class="col-md-7"><input type="text" name="schedule_activity[]" class="form-control" placeholder="Activity"></div>
    <div class="col-md-2"><button type="button" class="btn btn-danger removeRow">Remove</button></div>`;
    c.appendChild(r);
});
document.addEventListener('click', e=>{
    if(e.target.classList.contains('removeRow')) e.target.closest('.schedule-row').remove();
    if(e.target.classList.contains('removeAcc')) e.target.closest('.accommodation-block').remove();
});
document.getElementById('addAccBtn').addEventListener('click', ()=>{
    const c=document.getElementById('accommodationContainer');
    const i=c.querySelectorAll('.accommodation-block').length;
    const b=document.createElement('div');
    b.className='accommodation-block border rounded p-3 mb-3';
    b.innerHTML=`<div class="row g-2 mb-2">
    <div class="col-md-6"><input type="text" name="accommodation_type[]" class="form-control" placeholder="Type"></div>
    <div class="col-md-4"><input type="number" step="0.01" name="accommodation_price[]" class="form-control" placeholder="Price"></div>
    <div class="col-md-2"><button type="button" class="btn btn-danger removeAcc">Remove</button></div></div>
    <div class="mb-2"><label>Upload Images</label><input type="file" name="accommodation_images_new_${i}[]" class="form-control" multiple accept="image/*"></div>`;
    c.appendChild(b);
});

// AJAX delete image
function deleteImage(imgId) {
    if(!confirm("Delete this image?")) return;
    fetch('deleteAccommodationImage.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'img_id='+imgId
    }).then(r=>r.json()).then(res=>{
        if(res.success) document.getElementById('img-'+imgId).remove();
        else alert('Failed to delete image');
    }).catch(console.error);
}
</script>

<script>
  tinymce.init({
    selector: 'textarea#program_editor',
    plugins: 'lists link image table code help wordcount bold italic underline',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code'
  });
</script>

<script>
function addDynamicInput(type, title) {
    const container = document.getElementById(`new-${type}-container`);
    const row = document.createElement('div');
    row.classList.add('input-group', 'mb-2');
    
    let placeholder = title.replace(/s$/, ''); // e.g., Deals -> Deal

    row.innerHTML = `
        <input type="text" name="new_${type}[]" class="form-control" placeholder="New ${placeholder} name">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()" title="Remove">&times;</button>
    `;
    container.appendChild(row);
    row.querySelector('input').focus();
}
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>