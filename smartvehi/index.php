<?php
require_once 'includes/config.php';

// Already logged in → go straight to dashboard
if (isLoggedIn()) {
    $u = getCurrentUser();
    redirect($u['role'] === 'provider' ? 'provider/dashboard.php' : 'receiver/browse.php');
}

$pageTitle = 'Welcome';
require_once 'includes/header.php';
?>

<div class="page fu" style="text-align:center;
  background:radial-gradient(ellipse at 65% 20%,rgba(0,212,170,.07),transparent 55%),
             radial-gradient(ellipse at 20% 80%,rgba(124,92,252,.06),transparent 55%);">

  <div style="display:inline-block;background:rgba(0,212,170,.1);border:1px solid rgba(0,212,170,.3);
              color:var(--accent);padding:.32rem 1rem;border-radius:999px;
              font-size:.78rem;font-weight:600;letter-spacing:.06em;margin-bottom:1.4rem;">
    🚗 SMART VEHICLE PLATFORM &bull; 2026
  </div>

  <h1 style="font-size:clamp(2.1rem,6vw,4rem);font-weight:800;line-height:1.1;margin-bottom:1.1rem;">
    Your Vehicle Services,<br><span style="color:var(--accent)">All In One Place</span>
  </h1>
  <p style="color:var(--muted);font-size:1.05rem;max-width:500px;margin:0 auto 3rem;line-height:1.7;">
    Connect vehicle service providers and receivers — parking, washing &amp; rentals,
    unified into one smart digital ecosystem.
  </p>

  <div style="display:flex;gap:1.4rem;flex-wrap:wrap;justify-content:center;">

    <!-- PROVIDER -->
    <a href="service.php?role=provider" class="role-link" style="text-decoration:none;">
      <div class="role-card" data-hover="orange"
           style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
                  padding:2.4rem 2rem;width:255px;text-align:center;transition:all .3s;cursor:pointer;">
        <div style="font-size:2.8rem;margin-bottom:1.1rem;">🏢</div>
        <h3 style="font-size:1.3rem;font-weight:700;margin-bottom:.55rem;">Service Provider</h3>
        <p style="color:var(--muted);font-size:.88rem;line-height:1.6;">
          List parking zones, washing centers, or rental vehicles.
          Manage bookings &amp; earn more.
        </p>
        <span class="badge badge-provider" style="margin-top:1rem;">LIST &amp; EARN</span>
      </div>
    </a>

    <!-- RECEIVER -->
    <a href="service.php?role=receiver" class="role-link" style="text-decoration:none;">
      <div class="role-card" data-hover="green"
           style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
                  padding:2.4rem 2rem;width:255px;text-align:center;transition:all .3s;cursor:pointer;">
        <div style="font-size:2.8rem;margin-bottom:1.1rem;">🙋</div>
        <h3 style="font-size:1.3rem;font-weight:700;margin-bottom:.55rem;">Service Receiver</h3>
        <p style="color:var(--muted);font-size:.88rem;line-height:1.6;">
          Find nearby parking, book a wash or rent a vehicle.
          Fast, cashless &amp; convenient.
        </p>
        <span class="badge badge-receiver" style="margin-top:1rem;">FIND &amp; BOOK</span>
      </div>
    </a>

  </div>
</div>

<script>
document.querySelectorAll('.role-card').forEach(card => {
    const color = card.dataset.hover === 'orange'
        ? 'rgba(255,107,53,.4)' : 'rgba(0,212,170,.4)';
    card.addEventListener('mouseenter', () => {
        card.style.transform   = 'translateY(-7px)';
        card.style.borderColor = color;
        card.style.boxShadow   = '0 22px 60px rgba(0,0,0,.45)';
    });
    card.addEventListener('mouseleave', () => {
        card.style.transform   = '';
        card.style.borderColor = '';
        card.style.boxShadow   = '';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
