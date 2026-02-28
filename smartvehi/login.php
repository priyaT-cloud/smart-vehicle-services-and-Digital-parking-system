<?php
require_once 'includes/config.php';

// Store chosen service from URL
$valid = ['parking', 'washing', 'rental'];
if (isset($_GET['service']) && in_array($_GET['service'], $valid, true)) {
    $_SESSION['chosen_service'] = $_GET['service'];
}

$service = $_SESSION['chosen_service'] ?? 'parking';
$role    = $_SESSION['chosen_role']    ?? 'receiver';

// Already logged in → skip auth
if (isLoggedIn()) {
    $u = getCurrentUser();
    redirect($u['role'] === 'provider' ? 'provider/dashboard.php' : 'receiver/browse.php');
}

$error = '';
$mode  = $_POST['mode'] ?? ($_GET['mode'] ?? 'login');

// ── Handle POST ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db   = getDB();
    $mode = $_POST['mode'];

    // LOGIN
    if ($mode === 'login') {
        $email = trim($_POST['email']    ?? '');
        $pass  = trim($_POST['password'] ?? '');

        if (!$email || !$pass) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } else {
            $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
            $stmt->bind_param('ss', $email, $role);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                redirect($role === 'provider' ? 'provider/dashboard.php' : 'receiver/browse.php');
            } else {
                $error = 'Invalid email or password. Make sure you selected the correct role.';
            }
        }
    }

    // REGISTER
    if ($mode === 'register') {
        $name  = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email']     ?? '');
        $phone = trim($_POST['phone']     ?? '');
        $pass  = trim($_POST['password']  ?? '');
        $pass2 = trim($_POST['password2'] ?? '');

        if (!$name || !$email || !$phone || !$pass || !$pass2) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($pass) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($pass !== $pass2) {
            $error = 'Passwords do not match.';
        } else {
            // Duplicate check
            $chk = $db->prepare('SELECT id FROM users WHERE email = ?');
            $chk->bind_param('s', $email);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $error = 'An account with this email already exists.';
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $ins  = $db->prepare(
                    'INSERT INTO users (full_name, email, phone, password, role) VALUES (?,?,?,?,?)'
                );
                $ins->bind_param('sssss', $name, $email, $phone, $hash, $role);
                $ins->execute();
                $_SESSION['user_id'] = $db->insert_id;
                redirect($role === 'provider' ? 'provider/dashboard.php' : 'receiver/browse.php');
            }
        }
    }
}

// Icons/labels for context bar
$svcIcons  = ['parking' => '🅿️', 'washing' => '🚿', 'rental' => '🚗'];
$svcLabels = ['parking' => 'Parking', 'washing' => 'Car Washing', 'rental' => 'Vehicle Rental'];

$pageTitle = 'Sign In';
require_once 'includes/header.php';
?>

<div class="page fu">
  <div class="card" style="width:100%;max-width:420px;">

    <!-- Context bar -->
    <div class="ctx-bar">
      <?= $svcIcons[$service] ?>
      <?= ucfirst($role) ?> &rarr; <?= $svcLabels[$service] ?>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= clean($error) ?></div>
    <?php endif; ?>

    <!-- Tab switcher -->
    <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;">
      <a href="?service=<?= $service ?>&mode=login"
         class="tab <?= $mode !== 'register' ? 'active' : '' ?>" style="flex:1;text-align:center;">
        Sign In
      </a>
      <a href="?service=<?= $service ?>&mode=register"
         class="tab <?= $mode === 'register' ? 'active' : '' ?>" style="flex:1;text-align:center;">
        Register
      </a>
    </div>

    <?php if ($mode === 'register'): ?>
    <!-- ── REGISTER ── -->
    <h2 style="font-size:1.55rem;font-weight:800;margin-bottom:.3rem;">Create Account</h2>
    <p style="color:var(--muted);font-size:.87rem;margin-bottom:1.4rem;">
      Join SmartVehi as a <strong><?= $role ?></strong>
    </p>
    <form method="POST">
      <input type="hidden" name="mode" value="register">
      <div class="fg">
        <label>FULL NAME *</label>
        <input type="text" name="full_name"
               value="<?= clean($_POST['full_name'] ?? '') ?>"
               placeholder="Your full name" required>
      </div>
      <div class="fg">
        <label>EMAIL ADDRESS *</label>
        <input type="email" name="email"
               value="<?= clean($_POST['email'] ?? '') ?>"
               placeholder="you@example.com" required>
      </div>
      <div class="fg">
        <label>PHONE NUMBER *</label>
        <input type="tel" name="phone"
               value="<?= clean($_POST['phone'] ?? '') ?>"
               placeholder="+91 XXXXX XXXXX" required>
      </div>
      <div class="fg">
        <label>PASSWORD * (min 6 chars)</label>
        <input type="password" name="password" placeholder="Create a strong password" required>
      </div>
      <div class="fg">
        <label>CONFIRM PASSWORD *</label>
        <input type="password" name="password2" placeholder="Re-enter password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Create Account →</button>
    </form>

    <?php else: ?>
    <!-- ── LOGIN ── -->
    <h2 style="font-size:1.55rem;font-weight:800;margin-bottom:.3rem;">Welcome Back</h2>
    <p style="color:var(--muted);font-size:.87rem;margin-bottom:1.4rem;">
      Sign in as a <strong><?= $role ?></strong>
    </p>
    <form method="POST">
      <input type="hidden" name="mode" value="login">
      <div class="fg">
        <label>EMAIL ADDRESS *</label>
        <input type="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="fg">
        <label>PASSWORD *</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign In →</button>
    </form>
    <div style="margin-top:1rem;padding:.8rem;background:var(--surface2);
                border-radius:8px;font-size:.79rem;color:var(--muted);text-align:center;">
      Demo — Provider: <code style="color:var(--accent)">provider@demo.com</code> |
      Receiver: <code style="color:var(--accent)">receiver@demo.com</code><br>
      Password: <code style="color:var(--accent)">password</code>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
