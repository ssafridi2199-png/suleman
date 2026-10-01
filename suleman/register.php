<?php
$pageTitle = "Register Customer Account";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

if (isLoggedIn()) {
    header("Location: " . SITE_URL . "orders.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_btn'])) {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($fullName) || empty($email) || empty($password)) {
        setFlash('danger', 'Please fill in all required fields.');
    } elseif ($password !== $confirmPassword) {
        setFlash('danger', 'Passwords do not match. Please check again.');
    } elseif (strlen($password) < 6) {
        setFlash('danger', 'Password must be at least 6 characters long.');
    } else {
        $db = getDBConnection();
        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            setFlash('danger', 'An account with this email address already exists. Please login.');
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insStmt = $db->prepare("INSERT INTO users (full_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')");
            $insStmt->execute([$fullName, $email, $phone, $hashedPassword]);
            $newUserId = $db->lastInsertId();

            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'customer';

            // Merge guest cart items
            syncGuestCartToUser($newUserId);

            setFlash('success', 'Account registered successfully! Welcome to Afridi Pharmacy.');
            header("Location: " . SITE_URL . "orders.php");
            exit;
        }
    }
}
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
      <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        
        <div class="card-header bg-teal text-white p-4 text-center" style="background-color: var(--primary-color);">
          <i class="bi bi-person-plus-fill fs-1"></i>
          <h3 class="fw-bold mb-0 text-white">Create Afridi Pharmacy Account</h3>
          <span class="small text-teal-200">Get access to order tracking & prescription management</span>
        </div>

        <div class="card-body p-4 p-md-5">
          <form action="" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Full Name</label>
              <input type="text" name="full_name" class="form-control" placeholder="e.g. Dr. Ali Raza" value="<?= htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="+92 300 1234567" value="<?= htmlspecialchars($_POST['phone'] ?? ''); ?>">
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
              </div>
            </div>

            <button type="submit" name="register_btn" class="btn btn-pharmacy btn-lg w-100 rounded-pill mb-3">
              <i class="bi bi-check-circle me-2"></i> Register Account
            </button>
          </form>

          <div class="text-center pt-3 border-top">
            <p class="mb-0 text-muted">Already have an account? <a href="<?= SITE_URL; ?>login.php" class="fw-bold text-teal">Login Here</a></p>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
