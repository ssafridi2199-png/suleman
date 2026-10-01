<?php
$pageTitle = "Login Account";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? SITE_URL . "admin/index.php" : SITE_URL . "orders.php"));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('danger', 'Please provide both email address and password.');
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                setFlash('danger', 'Your account is deactivated. Please contact support.');
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Merge Guest Cart to User Cart
                syncGuestCartToUser($user['id']);

                setFlash('success', "Welcome back, {$user['full_name']}!");

                if ($user['role'] === 'admin') {
                    header("Location: " . SITE_URL . "admin/index.php");
                } else {
                    $redirect = $_SESSION['redirect_url'] ?? SITE_URL . "orders.php";
                    unset($_SESSION['redirect_url']);
                    header("Location: " . $redirect);
                }
                exit;
            }
        } else {
            setFlash('danger', 'Invalid email address or password.');
        }
    }
}
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
      <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        
        <div class="card-header bg-teal text-white p-4 text-center" style="background-color: var(--primary-color);">
          <i class="bi bi-crosshair2 fs-1"></i>
          <h3 class="fw-bold mb-0 text-white">AFRIDI PHARMACY</h3>
          <span class="small text-teal-200">Sign in to manage orders & prescriptions</span>
        </div>

        <div class="card-body p-4 p-md-5">
          <form action="" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Email Address</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
              </div>
            </div>

            <button type="submit" name="login_btn" class="btn btn-pharmacy btn-lg w-100 rounded-pill mb-3">
              <i class="bi bi-box-arrow-in-right me-2"></i> Log In
            </button>
          </form>

          <div class="alert alert-info border-info small mb-3">
            <strong>Demo Logins:</strong><br>
            • Admin: <code>admin@afridipharmacy.com</code> / <code>password123</code><br>
            • Customer: <code>john@example.com</code> / <code>password123</code>
          </div>

          <div class="text-center pt-3 border-top">
            <p class="mb-0 text-muted">Don't have an account yet? <a href="<?= SITE_URL; ?>register.php" class="fw-bold text-teal">Register Here</a></p>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
