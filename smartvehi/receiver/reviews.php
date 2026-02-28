<?php
require_once '../includes/config.php';
requireRole('receiver');
$user = getCurrentUser();
$db   = getDB();
$B    = baseUrl();

$id   = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT l.*, u.full_name AS provider_name FROM listings l JOIN users u ON l.user_id=u.id WHERE l.id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$l = $stmt->get_result()->fetch_assoc();
if (!$l) redirect('receiver/browse.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rname   = clean($_POST['reviewer_name'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = clean($_POST['comment'] ?? '');

    if (!$rname || !$comment || $rating < 1 || $rating > 5) {
        $error = 'Please fill in all fields with a valid rating (1–5).';
    } else {
        $uid  = $user['id'];
        $stmt2 = $db->prepare('INSERT INTO reviews (listing_id, user_id, reviewer_name, rating, comment) VALUES (?,?,?,?,?)');
        $stmt2->bind_param('iiisi', $id, $uid, $rname, $rating, $comment);
        if ($stmt2->execute()) {
            $success = 'Thank you! Your review has been submitted.';
        } else {
            $error = 'Failed to submit review — please try again.';
        }
    }
}

// Summary
$sumRow  = $db->query("SELECT ROUND(AVG(rating),1) AS avg, COUNT(*) AS cnt FROM reviews WHERE listing_id=$id")->fetch_assoc();
$avgRat  = (float)$sumRow['avg'];
$cntRat  = (int)$sumRow['cnt'];

$allRevs = $db->query("SELECT * FROM reviews WHERE listing_id=$id ORDER BY created_at DESC");

$icons     = ['parking'=>'🅿️','washing'=>'🚿','rental'=>'🚗'];
$pageTitle = 'Reviews — ' . clean($l['name']);
require_once '../includes/header.php';
?>

<div class="page top fu">
<div class="container" style="max-width:700px;">

  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="<?= $B ?>/receiver/listing.php?id=<?= $id ?>" class="nav-btn">← Back</a>
    <h2 style="font-size:1.4rem;font-weight:800;">
      <?= $icons[$l['type']] ?> Reviews
    </h2>
  </div>

  <!-- Rating summary -->
  <div class="card" style="text-align:center;margin-bottom:1.5rem;">
    <div style="font-size:2.8rem;font-weight:800;color:var(--accent);margin-bottom:.3rem;">
      <?= $avgRat ?: '—' ?>
    </div>
    <div style="font-size:1.3rem;color:#ffd700;margin-bottom:.3rem;">
      <?= $avgRat ? str_repeat('⭐', (int)round($avgRat)) . str_repeat('☆', 5-(int)round($avgRat)) : '☆☆☆☆☆' ?>
    </div>
    <div style="color:var(--muted);font-size:.86rem;">
      <?= $cntRat ?> review(s) for <?= clean($l['name']) ?>
    </div>
  </div>

  <?php if ($error):   ?><div class="alert alert-error">⚠️ <?= clean($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

  <!-- Write review -->
  <div class="card" style="margin-bottom:1.5rem;">
    <h3 style="font-size:1rem;margin-bottom:1.1rem;">✍️ Write a Review</h3>
    <form method="POST">
      <div class="form-row">
        <div class="fg">
          <label>YOUR NAME *</label>
          <input type="text" name="reviewer_name"
                 value="<?= clean($user['full_name']) ?>" required>
        </div>
        <div class="fg">
          <label>RATING *</label>
          <select name="rating">
            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
            <option value="4">⭐⭐⭐⭐ Good</option>
            <option value="3">⭐⭐⭐ Average</option>
            <option value="2">⭐⭐ Poor</option>
            <option value="1">⭐ Terrible</option>
          </select>
        </div>
      </div>
      <div class="fg">
        <label>COMMENT *</label>
        <textarea name="comment"
                  placeholder="Share your experience…" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Submit Review</button>
    </form>
  </div>

  <!-- All reviews -->
  <div class="card">
    <h3 style="font-size:1rem;margin-bottom:1rem;">All Reviews</h3>
    <?php if ($allRevs->num_rows === 0): ?>
      <p style="color:var(--muted);font-size:.88rem;">No reviews yet — be the first!</p>
    <?php else: while ($r = $allRevs->fetch_assoc()): ?>
      <div class="rv-item">
        <div class="rv-user">
          <?= clean($r['reviewer_name']) ?>
          <span style="color:#ffd700;"><?= str_repeat('⭐', (int)$r['rating']) ?></span>
        </div>
        <div class="rv-text"><?= clean($r['comment']) ?></div>
        <div class="rv-date"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
      </div>
    <?php endwhile; endif; ?>
  </div>

</div>
</div>

<?php require_once '../includes/footer.php'; ?>
