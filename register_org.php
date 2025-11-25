<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    // $description = mysqli_real_escape_string($conn, $_POST['description']);
    $website = mysqli_real_escape_string($conn, $_POST['website']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $continent = mysqli_real_escape_string($conn, $_POST['continent']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    
    // REMOVED: $lat and $lng
    
    // NEW: Capture both map link fields
    $map_link = mysqli_real_escape_string($conn, $_POST['map_link']); 
    $map_embed_link = mysqli_real_escape_string($conn, $_POST['map_embed_link']); 

    $created_by = $_SESSION['yoga_host_id'];

    if (empty($_FILES['gst_doc']['name']) && empty($_FILES['msme_doc']['name'])) {
        $errors[] = "Please upload at least one document (GST or MSME).";
    }

    $gst_doc_path = NULL;
    $msme_doc_path = NULL;

    // Create upload directory if missing
$uploadDir = __DIR__ . '/uploads/docs/'; // inside admin
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// GST document
if (!empty($_FILES['gst_doc']['name'])) {
    $gst_file = $_FILES['gst_doc'];
    $gst_ext = pathinfo($gst_file['name'], PATHINFO_EXTENSION);
    $gst_name = 'gst_' . time() . '.' . $gst_ext;
    $gst_path = $uploadDir . $gst_name;
    if (!move_uploaded_file($gst_file['tmp_name'], $gst_path)) {
        $errors[] = "Failed to upload GST document.";
    } else {
        $gst_doc_path = 'uploads/docs/' . $gst_name; // store relative path in DB
    }
}

// MSME document (similar)
if (!empty($_FILES['msme_doc']['name'])) {
    $msme_file = $_FILES['msme_doc'];
    $msme_ext = pathinfo($msme_file['name'], PATHINFO_EXTENSION);
    $msme_name = 'msme_' . time() . '.' . $msme_ext;
    $msme_path = $uploadDir . $msme_name;
    if (!move_uploaded_file($msme_file['tmp_name'], $msme_path)) {
        $errors[] = "Failed to upload MSME document.";
    } else {
        $msme_doc_path = 'uploads/docs/' . $msme_name;
    }
}

    if (empty($errors)) {
        // UPDATED SQL: Removed location_lat, location_lng. Added map_link and map_embed_link.
        $sql = "INSERT INTO organizations 
        (name, slug, website, contact_email, contact_phone, address, continent, country, state, city, created_by, gst_doc, msme_doc, map_link, map_embed_link, status)
        VALUES 
        ('$name','$slug','$website','$contact_email','$contact_phone','$address','$continent','$country','$state','$city','$created_by','$gst_doc_path','$msme_doc_path','$map_link','$map_embed_link','pending')";

        if (mysqli_query($conn, $sql)) {
            $success = "Organization registered successfully! Await admin approval.";
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <meta charset="UTF-8">
    <title>Register Organization | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h1>Register Organization</h1>

            <?php if($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach($errors as $e) echo $e . "<br>"; ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Organization Name</label>
                        <input type="text" name="name" id="org_name" class="form-control" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>Website</label>
                        <input type="text" name="website" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Continent</label>
                        <select name="continent" id="continent" class="form-select">
                            <option value="">Select Continent</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Country</label>
                        <select name="country" id="country" class="form-select" disabled>
                            <option value="">Select Country</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>State</label>
                        <select name="state" id="state" class="form-select" disabled>
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>City</label>
                        <select name="city" id="city" class="form-select" disabled>
                            <option value="">Select City</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Slug</label>
                        <input type="text" name="slug" id="org_slug" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Enter your full address">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>Map Link (Normal URL)</label>
                        <input type="text" name="map_link" class="form-control" placeholder="Paste Google Maps URL">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>Map Embed Code</label>
                        <input type="text" name="map_embed_link" class="form-control" placeholder="Paste Map Embed iFrame code">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>GST Document</label>
                        <input type="file" name="gst_doc" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>MSME Document</label>
                        <input type="file" name="msme_doc" class="form-control">
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
// REMOVED: Google Places Autocomplete logic

// Load continents
const continents = ["Africa","Asia","Europe","North America","South America","Oceania","Antarctica"];
const continentSelect = document.getElementById('continent');
const countrySelect = document.getElementById('country');
const stateSelect = document.getElementById('state');
const citySelect = document.getElementById('city');

continents.forEach(c => {
  let opt = document.createElement('option');
  opt.value = c; opt.text = c;
  continentSelect.add(opt);
});

// Continent -> Country
continentSelect.addEventListener('change', async function() {
  const continent = this.value;
  countrySelect.innerHTML = '<option value="">Select Country</option>';
  countrySelect.disabled = false;
  stateSelect.innerHTML = '<option value="">Select State</option>'; stateSelect.disabled = true;
  citySelect.innerHTML = '<option value="">Select City</option>'; citySelect.disabled = true;

  const res = await fetch('https://restcountries.com/v3.1/region/' + continent);
  const data = await res.json();
  data.forEach(c => {
    let opt = document.createElement('option');
    opt.value = c.name.common; opt.text = c.name.common;
    countrySelect.add(opt);
  });
});

// Country -> State (free API)
countrySelect.addEventListener('change', async function() {
  const country = this.value;
  stateSelect.innerHTML = '<option value="">Select State</option>'; stateSelect.disabled = false;
  citySelect.innerHTML = '<option value="">Select City</option>'; citySelect.disabled = true;

  try {
    const res = await fetch(`https://countriesnow.space/api/v0.1/countries/states`, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({country})
    });
    const data = await res.json();
    if (data.data.states && data.data.states.length) {
      data.data.states.forEach(s => {
        let opt = document.createElement('option');
        opt.value = s.name; opt.text = s.name;
        stateSelect.add(opt);
      });
    }
  } catch(err) { console.log(err); }
});

// State -> City (free API)
stateSelect.addEventListener('change', async function() {
  const country = countrySelect.value;
  const state = this.value;
  citySelect.innerHTML = '<option value="">Select City</option>'; citySelect.disabled = false;

  try {
    const res = await fetch(`https://countriesnow.space/api/v0.1/countries/state/cities`, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({country, state})
    });
    const data = await res.json();
    if (data.data && data.data.length) {
      data.data.forEach(c => {
        let opt = document.createElement('option');
        opt.value = c; opt.text = c;
        citySelect.add(opt);
      });
    }
  } catch(err) { console.log(err); }
});

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const gst = form.querySelector('input[name="gst_doc"]');
  const msme = form.querySelector('input[name="msme_doc"]');

  form.addEventListener("submit", function (e) {
    if (!gst.value && !msme.value) {
      e.preventDefault();

      // Show warning if not already present
      let warn = form.querySelector(".doc-warning");
      if (!warn) {
        warn = document.createElement("div");
        warn.className = "alert alert-danger doc-warning mt-2";
        warn.innerText = "⚠ Please upload at least one document (GST or MSME).";
        form.insertBefore(warn, form.querySelector(".text-center"));
      }

      // Highlight file inputs
      gst.classList.add("is-invalid");
      msme.classList.add("is-invalid");
    }
  });

  // Remove warning + red border when any file selected
  [gst, msme].forEach(input => {
    input.addEventListener("change", () => {
      if (gst.value || msme.value) {
        const warn = form.querySelector(".doc-warning");
        if (warn) warn.remove();

        gst.classList.remove("is-invalid");
        msme.classList.remove("is-invalid");
      }
    });
  });
});
</script>

</body>
</html>