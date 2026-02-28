<?php
require_once '../includes/config.php';
requireRole('receiver');
$user = getCurrentUser();
$db   = getDB();
$B    = baseUrl();

$id   = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('
    SELECT l.*, u.full_name AS provider_name
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.id = ? AND l.is_active = 1
');
$stmt->bind_param('i', $id);
$stmt->execute();
$l = $stmt->get_result()->fetch_assoc();
if (!$l) redirect('receiver/browse.php');

$error = '';
$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $custName = clean($_POST['customer_name'] ?? '');
    $date     = $_POST['booking_date'] ?? '';
    $duration = clean($_POST['duration'] ?? '');
    $notes    = clean($_POST['notes'] ?? '');

    if (!$custName || !$date || !$duration) {
        $error = 'Please fill in all required fields.';
    } elseif ($date < $today) {
        $error = 'Booking date cannot be in the past.';
    } else {
        $cost = calcCost($l, $duration);
        $uid  = $user['id'];
        $stmt2 = $db->prepare('
            INSERT INTO bookings
              (listing_id, user_id, customer_name, booking_date, duration, notes, cost)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt2->bind_param('iissssd', $id, $uid, $custName, $date, $duration, $notes, $cost);
        if ($stmt2->execute()) {
            redirect('receiver/my_bookings.php?msg=booked');
        } else {
            $error = 'Booking failed — please try again.';
        }
    }
}

$icons  = ['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
// JS cost lookup table
$hourRate = (float)($l['price_hour'] ?? $l['price_basic'] ?? $l['rent_hour'] ?? 0);
$dayRate  = (float)($l['price_day']  ?? $l['price_full']  ?? $l['rent_day']  ?? 0);

$pageTitle = 'Book Service';
require_once '../includes/header.php';
?>

<div class="page fu" style="align-items:center;">
  <div class="card" style="width:100%;max-width:470px;">

    <!-- Listing summary -->
    <div style="background:var(--surface2);border-radius:12px;padding:1rem;
                margin-bottom:1.5rem;display:flex;gap:1rem;align-items:center;">
      <div style="font-size:2.4rem;flex-shrink:0;"><?= $icons[$l['type']] ?></div>
      <div>
        <div style="font-weight:700;font-size:.98rem;"><?= clean($l['name']) ?></div>
        <div style="color:var(--muted);font-size:.8rem;">
          📍 <?= clean($l['location']) ?> &bull; by <?= clean($l['provider_name']) ?>
        </div>
      </div>
    </div>

    <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:.3rem;">Book Service</h2>
    <p style="color:var(--muted);font-size:.86rem;margin-bottom:1.4rem;">
      Fill in your details to confirm the booking.
    </p>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= clean($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="fg">
        <label>YOUR NAME *</label>
        <input type="text" name="customer_name"
               value="<?= clean($user['full_name']) ?>" required>
      </div>
      <div class="fg">
        <label>BOOKING DATE *</label>
        <input type="date" name="booking_date" min="<?= $today ?>"
               value="<?= $today ?>" required>
      </div>
      <div class="fg">
        <label>DURATION *</label>
        <select name="duration" id="dur" onchange="updateCost()">
          <option value="1 Hour">1 Hour</option>
          <option value="3 Hours">3 Hours</option>
          <option value="6 Hours">6 Hours</option>
          <option value="1 Day">1 Day</option>
          <option value="3 Days">3 Days</option>
          <option value="1 Week">1 Week</option>
        </select>
      </div>
      <div class="fg">
        <label>SPECIAL REQUESTS (optional)</label>
        <textarea name="notes"
                  placeholder="Any special instructions…"></textarea>
      </div>

      <!-- Cost display -->
      <div class="cost-box" style="margin-bottom:1.2rem;">
        <div class="lbl">ESTIMATED COST</div>
        <div class="val" id="cost-display">₹<?= number_format($hourRate, 2) ?></div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">✓ Confirm Booking</button>
      <a href="<?= $B ?>/receiver/browse.php"
         class="btn btn-outline btn-block" style="margin-top:.8rem;">← Cancel</a>
    </form>
  </div>
</div>

<script>
const H = <?= $hourRate ?>;
const D = <?= $dayRate ?>;
const COSTS = {
    '1 Hour' : H,
    '3 Hours': H * 3,
    '6 Hours': H * 6,
    '1 Day'  : D,
    '3 Days' : D * 3,
    '1 Week' : D * 7,
};
function updateCost() {
    const v = document.getElementById('dur').value;
    const c = COSTS[v] ?? H;
    document.getElementById('cost-display').textContent = '₹' + c.toFixed(2);
}
updateCost();
</script>

<?php require_once '../includes/footer.php'; ?>
