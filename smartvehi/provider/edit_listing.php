<?php
require_once '../includes/config.php';
requireRole('provider');
$user = getCurrentUser();
$db   = getDB();
$B    = baseUrl();

$id   = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM listings WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $id, $user['id']);
$stmt->execute();
$l = $stmt->get_result()->fetch_assoc();
if (!$l) redirect('provider/dashboard.php?tab=my');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($_POST['name']        ?? '');
    $location = clean($_POST['location']    ?? '');
    $desc     = clean($_POST['description'] ?? '');

    if (!$name || !$location) {
        $error = 'Name and location are required.';
    } else {
        // Image — keep old if no new file
        $imagePath = $l['image'];
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $ftype   = mime_content_type($_FILES['image']['tmp_name']);
            if (in_array($ftype, $allowed, true) && $_FILES['image']['size'] <= 5*1024*1024) {
                $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $fn   = 'uploads/' . uniqid('img_', true) . '.' . $ext;
                $dest = dirname(__DIR__) . DIRECTORY_SEPARATOR . $fn;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $imagePath = $fn;
                }
            }
        }

        if ($l['type'] === 'parking') {
            $ph = (float)($_POST['price_hour']  ?? 0);
            $pd = (float)($_POST['price_day']   ?? 0);
            $sl = (int)  ($_POST['total_slots'] ?? 0);
            $vt = clean($_POST['vehicle_type']  ?? 'Both');
            /*  s s s s d d i s i  → "ssssddiis" wait, let's count carefully:
                name=s location=s description=s image=s price_hour=d price_day=d total_slots=i vehicle_type=s id=i
                → "ssssddisi" — 9 types, 9 vars after the SET clause
             */
            $s = $db->prepare('
                UPDATE listings
                SET name=?, location=?, description=?, image=?,
                    price_hour=?, price_day=?, total_slots=?, vehicle_type=?
                WHERE id=?
            ');
            $s->bind_param('ssssddisi',
                $name, $location, $desc, $imagePath, $ph, $pd, $sl, $vt, $id);
            $ok = $s->execute();

        } elseif ($l['type'] === 'washing') {
            $pb  = (float)($_POST['price_basic']    ?? 0);
            $pf  = (float)($_POST['price_full']     ?? 0);
            $svo = clean($_POST['services_offered'] ?? '');
            /* name=s location=s description=s image=s price_basic=d price_full=d services_offered=s id=i
               → "ssssdds i" → "ssssddsi"
            */
            $s = $db->prepare('
                UPDATE listings
                SET name=?, location=?, description=?, image=?,
                    price_basic=?, price_full=?, services_offered=?
                WHERE id=?
            ');
            $s->bind_param('ssssddsi',
                $name, $location, $desc, $imagePath, $pb, $pf, $svo, $id);
            $ok = $s->execute();

        } else { // rental
            $rt = clean($_POST['rental_type']   ?? '');
            $vm = clean($_POST['vehicle_model'] ?? '');
            $rh = (float)($_POST['rent_hour']   ?? 0);
            $rd = (float)($_POST['rent_day']    ?? 0);
            $ft = clean($_POST['fuel_type']     ?? 'Petrol');
            /* name=s loc=s desc=s image=s rental_type=s vehicle_model=s rent_hour=d rent_day=d fuel_type=s id=i
               → "ssssssddsi"
            */
            $s = $db->prepare('
                UPDATE listings
                SET name=?, location=?, description=?, image=?,
                    rental_type=?, vehicle_model=?, rent_hour=?, rent_day=?, fuel_type=?
                WHERE id=?
            ');
            $s->bind_param('ssssssddsi',
                $name, $location, $desc, $imagePath, $rt, $vm, $rh, $rd, $ft, $id);
            $ok = $s->execute();
        }

        if (!empty($ok)) {
            // Reload fresh row
            $stmt2 = $db->prepare('SELECT * FROM listings WHERE id=?');
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            $l = $stmt2->get_result()->fetch_assoc();
            $success = 'Listing updated successfully!';
        } else {
            $error = 'Update failed — ' . $db->error;
        }
    }
}

$icons  = ['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$pageTitle = 'Edit Listing';
require_once '../includes/header.php';
?>

<div class="page top fu">
<div class="container" style="max-width:780px;">

  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.8rem;">
    <a href="<?= $B ?>/provider/dashboard.php?tab=my" class="nav-btn">← Back</a>
    <h2 style="font-size:1.5rem;font-weight:800;">
      <?= $icons[$l['type']] ?> Edit Listing
    </h2>
  </div>

  <?php if ($error):   ?><div class="alert alert-error">⚠️ <?= clean($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

  <div class="card">
    <form method="POST" enctype="multipart/form-data">

      <div class="form-row">
        <div class="fg">
          <label>NAME *</label>
          <input type="text" name="name" value="<?= clean($l['name']) ?>" required>
        </div>
        <div class="fg">
          <label>LOCATION *</label>
          <input type="text" name="location" value="<?= clean($l['location']) ?>" required>
        </div>
      </div>

      <?php if ($l['type'] === 'parking'): ?>
        <div class="form-row">
          <div class="fg">
            <label>PRICE/HOUR (₹)</label>
            <input type="number" name="price_hour" step="0.01" min="0"
                   value="<?= (float)$l['price_hour'] ?>">
          </div>
          <div class="fg">
            <label>PRICE/DAY (₹)</label>
            <input type="number" name="price_day" step="0.01" min="0"
                   value="<?= (float)$l['price_day'] ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="fg">
            <label>TOTAL SLOTS</label>
            <input type="number" name="total_slots" min="1"
                   value="<?= (int)$l['total_slots'] ?>">
          </div>
          <div class="fg">
            <label>VEHICLE TYPE</label>
            <select name="vehicle_type">
              <?php foreach (['2-Wheeler','4-Wheeler','Both'] as $o): ?>
                <option <?= $l['vehicle_type']===$o?'selected':'' ?>><?= $o ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

      <?php elseif ($l['type'] === 'washing'): ?>
        <div class="form-row">
          <div class="fg">
            <label>BASIC PRICE (₹)</label>
            <input type="number" name="price_basic" step="0.01" min="0"
                   value="<?= (float)$l['price_basic'] ?>">
          </div>
          <div class="fg">
            <label>FULL SERVICE (₹)</label>
            <input type="number" name="price_full" step="0.01" min="0"
                   value="<?= (float)$l['price_full'] ?>">
          </div>
        </div>
        <div class="fg">
          <label>SERVICES OFFERED</label>
          <input type="text" name="services_offered"
                 value="<?= clean($l['services_offered'] ?? '') ?>">
        </div>

      <?php else: // rental ?>
        <div class="form-row">
          <div class="fg">
            <label>VEHICLE CATEGORY</label>
            <select name="rental_type">
              <?php foreach (['2-Wheeler (Bike/Scooter)','3-Wheeler (Auto)','4-Wheeler (Car)'] as $o): ?>
                <option <?= $l['rental_type']===$o?'selected':'' ?>><?= $o ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="fg">
            <label>VEHICLE MODEL</label>
            <input type="text" name="vehicle_model"
                   value="<?= clean($l['vehicle_model'] ?? '') ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="fg">
            <label>RENT/HOUR (₹)</label>
            <input type="number" name="rent_hour" step="0.01" min="0"
                   value="<?= (float)$l['rent_hour'] ?>">
          </div>
          <div class="fg">
            <label>RENT/DAY (₹)</label>
            <input type="number" name="rent_day" step="0.01" min="0"
                   value="<?= (float)$l['rent_day'] ?>">
          </div>
        </div>
        <div class="fg">
          <label>FUEL TYPE</label>
          <select name="fuel_type">
            <?php foreach (['Petrol','Electric','Diesel','CNG'] as $o): ?>
              <option <?= $l['fuel_type']===$o?'selected':'' ?>><?= $o ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <div class="fg">
        <label>DESCRIPTION</label>
        <textarea name="description"><?= clean($l['description'] ?? '') ?></textarea>
      </div>

      <div class="fg">
        <label>UPDATE IMAGE (leave blank to keep current)</label>
        <?php if ($l['image']): ?>
          <div style="margin-bottom:.7rem;">
            <img src="<?= $B ?>/<?= clean($l['image']) ?>"
                 style="max-height:130px;border-radius:8px;">
          </div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*"
               style="background:var(--bg);border:1px solid var(--border);
                      border-radius:8px;padding:.5rem .8rem;width:100%;color:var(--muted);">
      </div>

      <div style="display:flex;gap:.8rem;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">✅ Save Changes</button>
        <a href="<?= $B ?>/provider/dashboard.php?tab=my"
           class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>

</div>
</div>

<?php require_once '../includes/footer.php'; ?>
