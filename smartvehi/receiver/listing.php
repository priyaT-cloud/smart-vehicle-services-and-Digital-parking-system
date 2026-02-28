<?php
require_once '../includes/config.php';
requireRole('receiver');
$user = getCurrentUser();
$db   = getDB();
$B    = baseUrl();

$id   = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('
    SELECT l.*, u.full_name AS provider_name,
           ROUND(AVG(r.rating), 1) AS avg_rating,
           COUNT(r.id)             AS review_count
    FROM listings l
    JOIN users u ON l.user_id = u.id
    LEFT JOIN reviews r ON r.listing_id = l.id
    WHERE l.id = ?
    GROUP BY l.id
');
$stmt->bind_param('i', $id);
$stmt->execute();
$l = $stmt->get_result()->fetch_assoc();
if (!$l) redirect('receiver/browse.php');

$reviews = $db->query("
    SELECT r.*, u.full_name
    FROM reviews r JOIN users u ON r.user_id = u.id
    WHERE r.listing_id = $id
    ORDER BY r.created_at DESC
    LIMIT 5
");

$icons   = ['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$avg     = (float)$l['avg_rating'];
$cnt     = (int)$l['review_count'];
$pageTitle = clean($l['name']);
require_once '../includes/header.php';
?>

<div class="page top fu">
<div class="container" style="max-width:800px;">

  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="<?= $B ?>/receiver/browse.php" class="nav-btn">← Back</a>
    <span class="badge badge-<?= $l['type'] ?>"><?= strtoupper($l['type']) ?></span>
  </div>

  <!-- Image -->
  <?php if ($l['image']): ?>
    <img src="<?= $B ?>/<?= clean($l['image']) ?>" alt="<?= clean($l['name']) ?>"
         style="width:100%;max-height:340px;object-fit:cover;border-radius:var(--radius-lg);
                margin-bottom:1.5rem;">
  <?php else: ?>
    <div style="width:100%;height:200px;background:var(--surface);border-radius:var(--radius-lg);
                display:flex;align-items:center;justify-content:center;
                font-size:5rem;margin-bottom:1.5rem;">
      <?= $icons[$l['type']] ?>
    </div>
  <?php endif; ?>

  <h2 style="font-size:1.9rem;font-weight:800;margin-bottom:.35rem;">
    <?= clean($l['name']) ?>
  </h2>
  <p style="color:var(--muted);margin-bottom:.9rem;font-size:.87rem;">
    📍 <?= clean($l['location']) ?> &bull;
    Listed by <?= clean($l['provider_name']) ?> &bull;
    <?= date('d M Y', strtotime($l['created_at'])) ?>
  </p>
  <div style="margin-bottom:1.5rem;font-size:.92rem;">
    <?= stars($avg, $cnt) ?>
  </div>

  <!-- Details -->
  <div class="card" style="margin-bottom:1.5rem;">
    <h3 style="margin-bottom:1.1rem;font-size:1rem;">Service Details</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;font-size:.9rem;">
      <?php if ($l['type'] === 'parking'): ?>
        <div><span style="color:var(--muted);">Price/Hour</span><br>
          <strong style="color:var(--accent);">₹<?= number_format((float)$l['price_hour'],2) ?></strong></div>
        <div><span style="color:var(--muted);">Price/Day</span><br>
          <strong style="color:var(--accent);">₹<?= number_format((float)$l['price_day'],2) ?></strong></div>
        <div><span style="color:var(--muted);">Total Slots</span><br>
          <strong><?= (int)$l['total_slots'] ?></strong></div>
        <div><span style="color:var(--muted);">Vehicle Type</span><br>
          <strong><?= clean($l['vehicle_type']??'') ?></strong></div>
      <?php elseif ($l['type'] === 'washing'): ?>
        <div><span style="color:var(--muted);">Basic Wash</span><br>
          <strong style="color:var(--accent);">₹<?= number_format((float)$l['price_basic'],2) ?></strong></div>
        <div><span style="color:var(--muted);">Full Service</span><br>
          <strong style="color:var(--accent);">₹<?= number_format((float)$l['price_full'],2) ?></strong></div>
        <div style="grid-column:1/-1;"><span style="color:var(--muted);">Services</span><br>
          <strong><?= clean($l['services_offered']??'') ?></strong></div>
      <?php else: ?>
        <div><span style="color:var(--muted);">Category</span><br>
          <strong><?= clean($l['rental_type']??'') ?></strong></div>
        <div><span style="color:var(--muted);">Model</span><br>
          <strong><?= clean($l['vehicle_model']??'') ?></strong></div>
        <div><span style="color:var(--muted);">Rent/Hour</span><br>
          <strong style="color:var(--accent);">₹<?= number_format((float)$l['rent_hour'],2) ?></strong></div>
        <div><span style="color:var(--muted);">Rent/Day</span><br>
          <strong style="color:var(--accent);">₹<?= number_format((float)$l['rent_day'],2) ?></strong></div>
        <div><span style="color:var(--muted);">Fuel</span><br>
          <strong><?= clean($l['fuel_type']??'') ?></strong></div>
      <?php endif; ?>
    </div>
    <?php if (!empty($l['description'])): ?>
      <div class="divider"></div>
      <p style="color:var(--muted);font-size:.88rem;line-height:1.7;">
        <?= nl2br(clean($l['description'])) ?>
      </p>
    <?php endif; ?>
  </div>

  <!-- Action buttons -->
  <div style="display:flex;gap:.9rem;margin-bottom:2rem;flex-wrap:wrap;">
    <a href="<?= $B ?>/receiver/book.php?id=<?= $l['id'] ?>"
       class="btn btn-primary" style="flex:1;min-width:160px;">Book Now</a>
    <a href="<?= $B ?>/receiver/reviews.php?id=<?= $l['id'] ?>"
       class="btn btn-outline">⭐ All Reviews</a>
  </div>

  <!-- Recent reviews -->
  <h3 style="font-size:1rem;margin-bottom:1rem;">Recent Reviews</h3>
  <?php if ($reviews->num_rows === 0): ?>
    <p style="color:var(--muted);font-size:.88rem;">
      No reviews yet —
      <a href="<?= $B ?>/receiver/reviews.php?id=<?= $l['id'] ?>"
         style="color:var(--accent);">be the first!</a>
    </p>
  <?php else: while ($r = $reviews->fetch_assoc()): ?>
    <div class="rv-item">
      <div class="rv-user">
        <?= clean($r['full_name']) ?>
        <span style="color:#ffd700;"><?= str_repeat('⭐', (int)$r['rating']) ?></span>
      </div>
      <div class="rv-text"><?= clean($r['comment']) ?></div>
      <div class="rv-date"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
    </div>
  <?php endwhile; endif; ?>

</div>
</div>

<?php require_once '../includes/footer.php'; ?>
