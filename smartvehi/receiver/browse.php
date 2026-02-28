<?php
require_once '../includes/config.php';
requireRole('receiver');
$user = getCurrentUser();
$db   = getDB();
$B    = baseUrl();

$service = $_SESSION['chosen_service'] ?? 'parking';
$search  = trim($_GET['search'] ?? '');
$sort    = $_GET['sort'] ?? 'newest';

// ── Build query ────────────────────────────────────────────────────
$typeEsc = $db->real_escape_string($service);
$where   = "l.type = '$typeEsc' AND l.is_active = 1";

if ($search !== '') {
    $s     = $db->real_escape_string($search);
    $where .= " AND (l.name LIKE '%$s%' OR l.location LIKE '%$s%' OR l.description LIKE '%$s%')";
}

$order = match($sort) {
    'price-low'  => 'COALESCE(l.price_hour, l.price_basic, l.rent_hour, 0) ASC',
    'price-high' => 'COALESCE(l.price_hour, l.price_basic, l.rent_hour, 0) DESC',
    'rating'     => 'avg_rating DESC',
    default      => 'l.created_at DESC',
};

$rows = $db->query("
    SELECT l.*,
           u.full_name AS provider_name,
           ROUND(AVG(r.rating), 1)  AS avg_rating,
           COUNT(r.id)              AS review_count
    FROM listings l
    JOIN users u ON l.user_id = u.id
    LEFT JOIN reviews r ON r.listing_id = l.id
    WHERE $where
    GROUP BY l.id
    ORDER BY $order
");

$myBookingCount = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE user_id={$user['id']}")
                             ->fetch_row()[0];

$icons     = ['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$bCls      = ['parking'=>'badge-parking','washing'=>'badge-washing','rental'=>'badge-rental'];
$pageTitle = 'Find ' . ucfirst($service);
require_once '../includes/header.php';
?>

<div class="page top fu">
<div class="container">

  <!-- Header -->
  <div style="display:flex;justify-content:space-between;align-items:flex-start;
              margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <h2 style="font-size:1.75rem;font-weight:800;">
        <?= $icons[$service] ?> Find <?= ucfirst($service) ?>
      </h2>
      <div style="color:var(--muted);font-size:.88rem;margin-top:.2rem;">
        <?= clean($user['full_name']) ?> &bull;
        <?= $rows->num_rows ?> service(s) found
      </div>
    </div>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
      <a href="<?= $B ?>/receiver/my_bookings.php" class="nav-btn">
        📅 My Bookings (<?= $myBookingCount ?>)
      </a>
      <a href="<?= $B ?>/service.php?role=receiver" class="nav-btn">Change Service</a>
    </div>
  </div>

  <!-- Filter bar -->
  <form method="GET" action="">
    <div class="filter-bar">
      <input type="text" name="search"
             placeholder="🔍 Search by name or location…"
             value="<?= clean($search) ?>">
      <select name="sort">
        <option value="newest"     <?= $sort==='newest'     ?'selected':'' ?>>Newest First</option>
        <option value="price-low"  <?= $sort==='price-low'  ?'selected':'' ?>>Price: Low → High</option>
        <option value="price-high" <?= $sort==='price-high' ?'selected':'' ?>>Price: High → Low</option>
        <option value="rating"     <?= $sort==='rating'     ?'selected':'' ?>>Highest Rated</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm">Search</button>
      <?php if ($search || $sort !== 'newest'): ?>
        <a href="browse.php" class="btn btn-outline btn-sm">Clear</a>
      <?php endif; ?>
    </div>
  </form>

  <!-- Grid -->
  <?php if ($rows->num_rows === 0): ?>
    <div class="empty">
      <div class="ei">🔍</div>
      <p>No <?= $service ?> services available yet.<br>
        <?= $search ? 'Try a different search term.' : 'Check back soon!' ?>
      </p>
    </div>
  <?php else: ?>
  <div class="grid">
  <?php while ($l = $rows->fetch_assoc()):
    // Price display
    $price = match($l['type']) {
        'parking' => '₹'.(float)$l['price_hour'].'/hr &nbsp;·&nbsp; ₹'.(float)$l['price_day'].'/day',
        'washing' => 'Basic: ₹'.(float)$l['price_basic'].' &nbsp;·&nbsp; Full: ₹'.(float)$l['price_full'],
        'rental'  => '₹'.(float)$l['rent_hour'].'/hr &nbsp;·&nbsp; ₹'.(float)$l['rent_day'].'/day',
        default   => '—',
    };
    // Extra info line
    $extra = match($l['type']) {
        'parking' => ($l['total_slots']??'?').' slots &bull; '.clean($l['vehicle_type']??''),
        'washing' => clean($l['services_offered'] ?? 'Various services'),
        'rental'  => clean($l['rental_type']??'').' &bull; '.clean($l['fuel_type']??'').' &bull; '.clean($l['vehicle_model']??''),
        default   => '',
    };
    $avg = (float)$l['avg_rating'];
    $cnt = (int)$l['review_count'];
  ?>
  <div class="lcard">
    <div class="lcard-img">
      <?php if ($l['image']): ?>
        <img src="<?= $B ?>/<?= clean($l['image']) ?>"
             alt="<?= clean($l['name']) ?>">
      <?php else: echo $icons[$l['type']]; endif; ?>
    </div>
    <div class="lcard-body">
      <span class="badge <?= $bCls[$l['type']] ?>"><?= strtoupper($l['type']) ?></span>
      <div class="lcard-title" style="margin-top:.45rem;">
        <?= clean($l['name']) ?>
      </div>
      <div class="lcard-meta">📍 <?= clean($l['location']) ?> &bull; by <?= clean($l['provider_name']) ?></div>
      <div class="lcard-extra"><?= $extra ?></div>
      <div class="lcard-price"><?= $price ?></div>
      <div class="lcard-rating">
        <?= stars($avg, $cnt) ?>
      </div>
      <div class="lcard-actions">
        <a href="<?= $B ?>/receiver/book.php?id=<?= $l['id'] ?>"
           class="lcard-btn lcard-btn-primary">Book Now</a>
        <a href="<?= $B ?>/receiver/listing.php?id=<?= $l['id'] ?>"
           class="lcard-btn lcard-btn-secondary">Details</a>
        <a href="<?= $B ?>/receiver/reviews.php?id=<?= $l['id'] ?>"
           class="lcard-btn lcard-btn-secondary" style="flex:.6;color:#ffd700;">⭐</a>
      </div>
    </div>
  </div>
  <?php endwhile; ?>
  </div>
  <?php endif; ?>

</div>
</div>

<?php require_once '../includes/footer.php'; ?>
