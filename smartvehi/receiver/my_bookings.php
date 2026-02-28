<?php
require_once '../includes/config.php';
requireRole('receiver');
$user = getCurrentUser();
$db   = getDB();
$B    = baseUrl();

$uid  = $user['id'];
$rows = $db->query("
    SELECT b.*, l.name AS lname, l.location AS lloc, l.type AS ltype,
           u.full_name AS provider_name
    FROM bookings b
    JOIN listings l ON b.listing_id = l.id
    JOIN users u ON l.user_id = u.id
    WHERE b.user_id = $uid
    ORDER BY b.created_at DESC
");

$msg  = $_GET['msg'] ?? '';
$icons = ['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$pageTitle = 'My Bookings';
require_once '../includes/header.php';
?>

<div class="page top fu">
<div class="container" style="max-width:900px;">

  <div style="display:flex;justify-content:space-between;align-items:flex-start;
              margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <h2 style="font-size:1.75rem;font-weight:800;">📅 My Bookings</h2>
      <div style="color:var(--muted);font-size:.88rem;margin-top:.2rem;">
        <?= clean($user['full_name']) ?> &bull; <?= $rows->num_rows ?> booking(s)
      </div>
    </div>
    <a href="<?= $B ?>/receiver/browse.php" class="nav-btn">← Browse Services</a>
  </div>

  <?php if ($msg === 'booked'): ?>
    <div class="alert alert-success">
      ✅ Booking confirmed! Your details are shown below.
    </div>
  <?php endif; ?>

  <?php if ($rows->num_rows === 0): ?>
    <div class="empty">
      <div class="ei">📅</div>
      <p>No bookings yet.<br>
        <a href="<?= $B ?>/receiver/browse.php" style="color:var(--accent);">Browse services →</a>
      </p>
    </div>
  <?php else: while ($b = $rows->fetch_assoc()): ?>
    <div class="bitem">
      <div style="display:flex;gap:1rem;align-items:center;flex:1;">
        <div style="font-size:2rem;flex-shrink:0;"><?= $icons[$b['ltype']] ?></div>
        <div class="bitem-info">
          <h4><?= clean($b['lname']) ?></h4>
          <p>📍 <?= clean($b['lloc']) ?> &bull; by <?= clean($b['provider_name']) ?></p>
          <p>📅 <?= $b['booking_date'] ?> &bull; ⏱ <?= clean($b['duration']) ?></p>
          <?php if ($b['notes']): ?>
            <p style="margin-top:.25rem;">📝 <?= clean($b['notes']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div style="text-align:right;flex-shrink:0;">
        <span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
        <div style="color:var(--accent);font-weight:800;font-size:1.05rem;margin-top:.4rem;">
          ₹<?= number_format((float)$b['cost'], 2) ?>
        </div>
        <div style="color:var(--muted);font-size:.73rem;margin-top:.2rem;">
          Booked <?= date('d M Y', strtotime($b['created_at'])) ?>
        </div>
      </div>
    </div>
  <?php endwhile; endif; ?>

</div>
</div>

<?php require_once '../includes/footer.php'; ?>
