<?php
require_once 'includes/config.php';

$role = $_GET['role'] ?? '';
if (!in_array($role, ['provider', 'receiver'], true)) {
    redirect('index.php');
}
$_SESSION['chosen_role'] = $role;

$pageTitle   = 'Choose Service';
$isProvider  = ($role === 'provider');
require_once 'includes/header.php';
?>

<div class="page fu">
  <div style="text-align:center;margin-bottom:2.8rem;">
    <span class="badge <?= $isProvider ? 'badge-provider' : 'badge-receiver' ?>"
          style="margin-bottom:.9rem;display:inline-block;font-size:.8rem;padding:.3rem .9rem;">
      <?= $isProvider ? '🏢 SERVICE PROVIDER' : '🙋 SERVICE RECEIVER' ?>
    </span>
    <h2 style="font-size:1.9rem;font-weight:800;margin-bottom:.5rem;">
      <?= $isProvider ? 'What service do you offer?' : 'What are you looking for?' ?>
    </h2>
    <p style="color:var(--muted);">
      <?= $isProvider
          ? 'Choose a category to list your service and start earning.'
          : 'Choose a service to browse and book near you.' ?>
    </p>
  </div>

  <div style="display:flex;gap:1.4rem;flex-wrap:wrap;justify-content:center;">
    <?php
    $B = baseUrl();
    $services = [
      'parking' => ['🅿️', 'Parking',
        $isProvider ? 'List your parking zones with images, pricing &amp; availability.'
                    : 'Find &amp; book parking slots near your destination.'],
      'washing' => ['🚿', 'Car Washing',
        $isProvider ? 'Register your washing center, services &amp; pricing.'
                    : 'Discover nearby washing centers and book a slot.'],
      'rental'  => ['🚗', 'Vehicle Rental',
        $isProvider ? 'Add your 2/3/4-wheelers for rent.'
                    : 'Browse available vehicles to rent for any duration.'],
    ];
    foreach ($services as $key => [$icon, $label, $desc]):
    ?>
    <a href="<?= $B ?>/login.php?service=<?= $key ?>" style="text-decoration:none;">
      <div class="svc-card"
           style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
                  padding:2rem 1.7rem;width:215px;text-align:center;transition:all .3s;cursor:pointer;">
        <div style="font-size:2.7rem;margin-bottom:.95rem;"><?= $icon ?></div>
        <h3 style="font-size:1.05rem;font-weight:700;margin-bottom:.38rem;"><?= $label ?></h3>
        <p style="color:var(--muted);font-size:.81rem;line-height:1.55;"><?= $desc ?></p>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<script>
document.querySelectorAll('.svc-card').forEach(c => {
    c.addEventListener('mouseenter', () => {
        c.style.transform   = 'translateY(-5px)';
        c.style.borderColor = 'var(--accent)';
        c.style.boxShadow   = 'var(--glow)';
    });
    c.addEventListener('mouseleave', () => {
        c.style.transform = c.style.borderColor = c.style.boxShadow = '';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
