<?php
require_once '../includes/config.php';
requireRole('provider');
$user = getCurrentUser();
$db   = getDB();

$B       = baseUrl();
$service = $_SESSION['chosen_service'] ?? 'parking';
$tab     = $_GET['tab'] ?? 'add';

// ── Handle listing delete ──────────────────────────────────────────
if (isset($_GET['delete'])) {
    $lid  = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM listings WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $lid, $user['id']);
    $stmt->execute();
    redirect('provider/dashboard.php?tab=my&msg=deleted');
}

// ── Stats ──────────────────────────────────────────────────────────
$uid = $user['id'];
$totalListings = (int) $db->query("SELECT COUNT(*) FROM listings WHERE user_id=$uid")
                           ->fetch_row()[0];
$totalBookings = (int) $db->query("SELECT COUNT(*) FROM bookings b
                                    JOIN listings l ON b.listing_id=l.id
                                    WHERE l.user_id=$uid")->fetch_row()[0];
$totalEarnings = (float) $db->query("SELECT COALESCE(SUM(b.cost),0) FROM bookings b
                                      JOIN listings l ON b.listing_id=l.id
                                      WHERE l.user_id=$uid")->fetch_row()[0];
$totalReviews  = (int) $db->query("SELECT COUNT(*) FROM reviews r
                                    JOIN listings l ON r.listing_id=l.id
                                    WHERE l.user_id=$uid")->fetch_row()[0];

$icons  = ['parking' => '🅿️', 'washing' => '🚿', 'rental' => '🚗'];
$labels = ['parking' => 'Parking Zone', 'washing' => 'Washing Center', 'rental' => 'Rental Vehicle'];

$msg = '';
$msgs = [
    'deleted' => '✅ Listing deleted successfully.',
    'added'   => '✅ Listing published successfully!',
    'updated' => '✅ Listing updated successfully!',
    'missing' => '⚠️ Name and location are required.',
    'error'   => '⚠️ Save failed — please try again.',
];
if (isset($_GET['msg'], $msgs[$_GET['msg']])) $msg = $msgs[$_GET['msg']];

$pageTitle = 'Provider Dashboard';
require_once '../includes/header.php';
?>

<div class="page top fu">
<div class="container">

  <!-- Header row -->
  <div style="display:flex;justify-content:space-between;align-items:flex-start;
              margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <h2 style="font-size:1.75rem;font-weight:800;">
        <?= $icons[$service] ?> Provider Dashboard
      </h2>
      <div style="color:var(--muted);font-size:.88rem;margin-top:.2rem;">
        <?= clean($user['full_name']) ?> &bull; <?= ucfirst($service) ?> Provider
      </div>
    </div>
    <a href="<?= $B ?>/service.php?role=provider" class="nav-btn">Change Service</a>
  </div>

  <!-- Stats -->
  <div class="stats">
    <div class="stat"><div class="stat-num" style="color:var(--accent)"><?= $totalListings ?></div><div class="stat-lbl">Listings</div></div>
    <div class="stat"><div class="stat-num" style="color:#a78bfa"><?= $totalBookings ?></div><div class="stat-lbl">Bookings</div></div>
    <div class="stat"><div class="stat-num" style="color:var(--accent2)">₹<?= number_format($totalEarnings) ?></div><div class="stat-lbl">Earnings</div></div>
    <div class="stat"><div class="stat-num" style="color:#ffd700"><?= $totalReviews ?></div><div class="stat-lbl">Reviews</div></div>
  </div>

  <?php if ($msg): ?>
    <div class="alert <?= str_starts_with($msg,'⚠') ? 'alert-error' : 'alert-success' ?>">
      <?= $msg ?>
    </div>
  <?php endif; ?>

  <!-- Tabs -->
  <div class="tabs">
    <a href="?tab=add"      class="tab <?= $tab==='add'      ?'active':'' ?>">➕ Add Listing</a>
    <a href="?tab=my"       class="tab <?= $tab==='my'       ?'active':'' ?>">📋 My Listings</a>
    <a href="?tab=bookings" class="tab <?= $tab==='bookings' ?'active':'' ?>">📅 Bookings</a>
  </div>

  <!-- ═══════════════════════════════ ADD LISTING ════ -->
  <?php if ($tab === 'add'): ?>
  <div class="card">
    <h3 style="margin-bottom:1.5rem;font-size:1.15rem;">
      <?= $icons[$service] ?> Add New <?= $labels[$service] ?>
    </h3>
    <form method="POST" action="<?= $B ?>/provider/add_listing.php"
          enctype="multipart/form-data">
      <input type="hidden" name="type" value="<?= $service ?>">

      <div class="form-row">
        <div class="fg">
          <label>NAME / TITLE *</label>
          <input type="text" name="name"
                 placeholder="e.g. City Center Parking Zone A" required>
        </div>
        <div class="fg">
          <label>LOCATION / ADDRESS *</label>
          <input type="text" name="location"
                 placeholder="e.g. Anna Nagar, Chennai" required>
        </div>
      </div>

      <?php if ($service === 'parking'): ?>
        <div class="form-row">
          <div class="fg">
            <label>PRICE PER HOUR (₹)</label>
            <input type="number" name="price_hour" min="0" step="0.01" placeholder="e.g. 30">
          </div>
          <div class="fg">
            <label>PRICE PER DAY (₹)</label>
            <input type="number" name="price_day" min="0" step="0.01" placeholder="e.g. 200">
          </div>
        </div>
        <div class="form-row">
          <div class="fg">
            <label>TOTAL SLOTS</label>
            <input type="number" name="total_slots" min="1" placeholder="e.g. 20">
          </div>
          <div class="fg">
            <label>VEHICLE TYPE</label>
            <select name="vehicle_type">
              <option>2-Wheeler</option>
              <option>4-Wheeler</option>
              <option selected>Both</option>
            </select>
          </div>
        </div>

      <?php elseif ($service === 'washing'): ?>
        <div class="form-row">
          <div class="fg">
            <label>BASIC WASH PRICE (₹)</label>
            <input type="number" name="price_basic" min="0" step="0.01" placeholder="e.g. 150">
          </div>
          <div class="fg">
            <label>FULL SERVICE PRICE (₹)</label>
            <input type="number" name="price_full" min="0" step="0.01" placeholder="e.g. 400">
          </div>
        </div>
        <div class="fg">
          <label>SERVICES OFFERED</label>
          <input type="text" name="services_offered"
                 placeholder="e.g. Exterior wash, Interior cleaning, Waxing">
        </div>

      <?php elseif ($service === 'rental'): ?>
        <div class="form-row">
          <div class="fg">
            <label>VEHICLE CATEGORY</label>
            <select name="rental_type">
              <option>2-Wheeler (Bike/Scooter)</option>
              <option>3-Wheeler (Auto)</option>
              <option>4-Wheeler (Car)</option>
            </select>
          </div>
          <div class="fg">
            <label>VEHICLE MODEL</label>
            <input type="text" name="vehicle_model" placeholder="e.g. Honda Activa">
          </div>
        </div>
        <div class="form-row">
          <div class="fg">
            <label>RENT PER HOUR (₹)</label>
            <input type="number" name="rent_hour" min="0" step="0.01" placeholder="e.g. 80">
          </div>
          <div class="fg">
            <label>RENT PER DAY (₹)</label>
            <input type="number" name="rent_day" min="0" step="0.01" placeholder="e.g. 500">
          </div>
        </div>
        <div class="fg">
          <label>FUEL TYPE</label>
          <select name="fuel_type">
            <option>Petrol</option>
            <option>Electric</option>
            <option>Diesel</option>
            <option>CNG</option>
          </select>
        </div>
      <?php endif; ?>

      <div class="fg">
        <label>DESCRIPTION</label>
        <textarea name="description"
                  placeholder="Describe your service in detail..."></textarea>
      </div>

      <div class="fg">
        <label>UPLOAD IMAGE</label>
        <div class="upload-box" onclick="document.getElementById('img-file').click()">
          <div style="font-size:1.9rem;margin-bottom:.45rem;">📷</div>
          <p style="font-size:.88rem;color:var(--muted);">
            Click to upload a photo of your <?= strtolower($labels[$service]) ?>
          </p>
          <p style="font-size:.74rem;color:var(--muted);margin-top:.25rem;">
            JPG, PNG, WEBP — max 5 MB
          </p>
          <input type="file" id="img-file" name="image" accept="image/*"
                 style="display:none" onchange="previewImg(this)">
          <img id="img-preview" src="" alt=""
               style="display:none;max-width:100%;max-height:200px;
                      border-radius:8px;margin-top:1rem;object-fit:cover;">
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="min-width:200px;">
        🚀 Publish Listing
      </button>
    </form>
  </div>

  <!-- ═══════════════════════════════ MY LISTINGS ════ -->
  <?php elseif ($tab === 'my'):
    $res = $db->query("
      SELECT l.*,
        (SELECT ROUND(AVG(rating),1) FROM reviews WHERE listing_id=l.id) AS avg_rating,
        (SELECT COUNT(*) FROM bookings WHERE listing_id=l.id) AS book_count
      FROM listings l
      WHERE l.user_id = $uid
      ORDER BY l.created_at DESC
    ");
    if ($res->num_rows === 0):
  ?>
    <div class="empty"><div class="ei">📭</div>
      <p>No listings yet.<br>Go to <a href="?tab=add" style="color:var(--accent)">Add Listing</a> to get started.</p>
    </div>
  <?php else: while ($l = $res->fetch_assoc()): ?>
    <div class="mcard">
      <div class="mcard-thumb">
        <?php if ($l['image']): ?>
          <img src="<?= $B ?>/<?= clean($l['image']) ?>" alt="">
        <?php else: echo $icons[$l['type']]; endif; ?>
      </div>
      <div class="mcard-info">
        <div class="mcard-title"><?= clean($l['name']) ?></div>
        <div class="mcard-sub">
          📍 <?= clean($l['location']) ?> &bull;
          <?= $l['avg_rating'] ? '⭐ '.$l['avg_rating'] : 'No reviews' ?> &bull;
          <?= (int)$l['book_count'] ?> booking(s)
        </div>
      </div>
      <div class="mcard-actions">
        <a href="<?= $B ?>/provider/edit_listing.php?id=<?= $l['id'] ?>"
           class="btn-edit">✏️ Edit</a>
        <a href="?tab=my&delete=<?= $l['id'] ?>"
           class="btn-danger"
           onclick="return confirm('Delete this listing? This cannot be undone.')">
           🗑️ Delete
        </a>
      </div>
    </div>
  <?php endwhile; endif; ?>

  <!-- ═══════════════════════════════ BOOKINGS ════ -->
  <?php elseif ($tab === 'bookings'):
    $res = $db->query("
      SELECT b.*, l.name AS lname, l.location AS lloc, l.type AS ltype
      FROM bookings b
      JOIN listings l ON b.listing_id = l.id
      WHERE l.user_id = $uid
      ORDER BY b.created_at DESC
    ");
    if ($res->num_rows === 0):
  ?>
    <div class="empty"><div class="ei">📅</div><p>No bookings received yet.</p></div>
  <?php else: while ($b = $res->fetch_assoc()): ?>
    <div class="bitem">
      <div class="bitem-info">
        <h4><?= $icons[$b['ltype']] ?> <?= clean($b['lname']) ?></h4>
        <p>
          👤 <?= clean($b['customer_name']) ?> &bull;
          📅 <?= $b['booking_date'] ?> &bull;
          ⏱ <?= clean($b['duration']) ?>
        </p>
        <?php if ($b['notes']): ?>
          <p style="margin-top:.3rem;">📝 <?= clean($b['notes']) ?></p>
        <?php endif; ?>
      </div>
      <div style="text-align:right;">
        <span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
        <div style="color:var(--accent);font-weight:800;font-size:.98rem;margin-top:.4rem;">
          ₹<?= number_format((float)$b['cost'], 2) ?>
        </div>
      </div>
    </div>
  <?php endwhile; endif; ?>

  <?php endif; ?>

</div><!-- /.container -->
</div><!-- /.page -->

<script>
function previewImg(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('img-preview');
        img.src = e.target.result;
        img.style.display = 'block';
    };
    reader.readAsDataURL(file);
}
</script>

<?php require_once '../includes/footer.php'; ?>
