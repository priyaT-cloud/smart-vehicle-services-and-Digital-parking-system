<?php
// provider/add_listing.php — handles the Add Listing form POST
require_once '../includes/config.php';
requireRole('provider');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('provider/dashboard.php');
}

$user    = getCurrentUser();
$db      = getDB();
$service = trim($_POST['type'] ?? '');

if (!in_array($service, ['parking', 'washing', 'rental'], true)) {
    redirect('provider/dashboard.php');
}

// Sanitise common fields
$name     = clean($_POST['name']        ?? '');
$location = clean($_POST['location']    ?? '');
$desc     = clean($_POST['description'] ?? '');

if ($name === '' || $location === '') {
    redirect('provider/dashboard.php?tab=add&msg=missing');
}

// ── Image upload ──────────────────────────────────────────────────
$imagePath = null;
if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $ftype   = mime_content_type($_FILES['image']['tmp_name']);

    if (in_array($ftype, $allowed, true) && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = 'uploads/' . uniqid('img_', true) . '.' . $ext;
        $dest     = dirname(__DIR__) . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $imagePath = $filename;   // stored as  uploads/img_xxx.jpg
        }
    }
}

// ── Insert by service type ────────────────────────────────────────
$ok = false;

if ($service === 'parking') {
    /*
     * Columns : user_id  type  name  location  description  image  price_hour  price_day  total_slots  vehicle_type
     * Types   :    i      s     s       s           s         s        d           d           i             s
     * String  : i s s s s s d d i s  → "isssssddis"  (10 chars, 10 vars)
     */
    $ph = (float)($_POST['price_hour']  ?? 0);
    $pd = (float)($_POST['price_day']   ?? 0);
    $sl = (int)  ($_POST['total_slots'] ?? 0);
    $vt = clean($_POST['vehicle_type']  ?? 'Both');

    $stmt = $db->prepare('
        INSERT INTO listings
          (user_id, type, name, location, description, image, price_hour, price_day, total_slots, vehicle_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->bind_param('isssssddis',
        $user['id'], $service, $name, $location, $desc, $imagePath,
        $ph, $pd, $sl, $vt
    );
    $ok = $stmt->execute();

} elseif ($service === 'washing') {
    /*
     * Columns : user_id  type  name  location  description  image  price_basic  price_full  services_offered
     * Types   :    i      s     s       s           s         s         d            d              s
     * String  : i s s s s s d d s  → "isssssdds"  (9 chars, 9 vars)
     */
    $pb  = (float)($_POST['price_basic']    ?? 0);
    $pf  = (float)($_POST['price_full']     ?? 0);
    $svo = clean($_POST['services_offered'] ?? '');

    $stmt = $db->prepare('
        INSERT INTO listings
          (user_id, type, name, location, description, image, price_basic, price_full, services_offered)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->bind_param('isssssdds',
        $user['id'], $service, $name, $location, $desc, $imagePath,
        $pb, $pf, $svo
    );
    $ok = $stmt->execute();

} elseif ($service === 'rental') {
    /*
     * Columns : user_id  type  name  location  description  image  rental_type  vehicle_model  rent_hour  rent_day  fuel_type
     * Types   :    i      s     s       s           s         s          s             s            d          d         s
     * String  : i s s s s s s s d d s  → "isssssssdds"  (11 chars, 11 vars)
     */
    $rt = clean($_POST['rental_type']   ?? '');
    $vm = clean($_POST['vehicle_model'] ?? '');
    $rh = (float)($_POST['rent_hour']   ?? 0);
    $rd = (float)($_POST['rent_day']    ?? 0);
    $ft = clean($_POST['fuel_type']     ?? 'Petrol');

    $stmt = $db->prepare('
        INSERT INTO listings
          (user_id, type, name, location, description, image, rental_type, vehicle_model, rent_hour, rent_day, fuel_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->bind_param('isssssssdds',
        $user['id'], $service, $name, $location, $desc, $imagePath,
        $rt, $vm, $rh, $rd, $ft
    );
    $ok = $stmt->execute();
}

redirect($ok
    ? 'provider/dashboard.php?tab=my&msg=added'
    : 'provider/dashboard.php?tab=add&msg=error'
);
