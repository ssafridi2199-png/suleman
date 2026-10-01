<?php
$currentUser = getCurrentUser();
$cartCount = getCartCount();
$currentSearch = isset($_GET['search']) ? sanitize($_GET['search']) : '';
?>
<!-- Top Announcement Strip -->
<div class="top-bar">
  <div class="container d-flex justify-content-between align-items-center">
    <div>
      <i class="bi bi-truck me-1"></i> Free Shipping on orders over PKR 3,000 | 
      <i class="bi bi-shield-check me-1 ms-2"></i> 100% Genuine Medicines & PMDC Certified Pharmacists
    </div>
    <div class="d-none d-md-block">
      <i class="bi bi-telephone-fill me-1"></i> 24/7 Helpline: <a href="tel:+923001234567">+92 300 1234567</a>
    </div>
  </div>
</div>

<!-- Main Sticky Navbar -->
<nav class="navbar navbar-expand-lg navbar-pharmacy sticky-top">
  <div class="container">
    <!-- Brand Logo -->
    <a class="navbar-brand navbar-brand-logo me-4" href="<?= SITE_URL; ?>">
      <i class="bi bi-crosshair2"></i>
      <span>AFRIDI <span style="color: var(--dark-text); font-weight: 400;">PHARMACY</span></span>
    </a>

    <!-- Mobile Toggler -->
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPharmacyContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Content -->
    <div class="collapse navbar-collapse" id="navbarPharmacyContent">
      
      <!-- Search Form -->
      <form class="search-form-wrap my-2 my-lg-0 mx-auto" action="<?= SITE_URL; ?>products.php" method="GET">
        <input class="form-control search-input-pill" type="search" name="search" placeholder="Search medicines, vitamins, medical devices..." value="<?= htmlspecialchars($currentSearch); ?>" required>
        <button class="search-btn-icon" type="submit" title="Search">
          <i class="bi bi-search"></i>
        </button>
      </form>

      <!-- Navigation & Auth Links -->
      <ul class="navbar-nav me-auto me-lg-0 mb-2 mb-lg-0 align-items-lg-center gap-1">
        <li class="nav-item">
          <a class="nav-link nav-link-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?= SITE_URL; ?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-item <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="<?= SITE_URL; ?>products.php">Shop All</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-item <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="<?= SITE_URL; ?>categories.php">Categories</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-item <?= basename($_SERVER['PHP_SELF']) == 'prescriptions.php' ? 'active' : ''; ?>" href="<?= SITE_URL; ?>prescriptions.php">
            <i class="bi bi-file-earmark-medical text-danger me-1"></i> Upload Rx
          </a>
        </li>

        <!-- User Dropdown or Login -->
        <?php if ($currentUser): ?>
          <li class="nav-item dropdown ms-lg-2">
            <a class="nav-link dropdown-toggle btn btn-light border-0 fw-semibold d-inline-flex align-items-center gap-2 px-3 rounded-pill" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle fs-5 text-teal"></i>
              <span><?= htmlspecialchars(explode(' ', $currentUser['full_name'])[0]); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
              <li class="dropdown-header text-muted small">Signed in as <strong><?= htmlspecialchars($currentUser['email']); ?></strong></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= SITE_URL; ?>orders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
              <li><a class="dropdown-item" href="<?= SITE_URL; ?>prescriptions.php"><i class="bi bi-file-medical me-2"></i>My Prescriptions</a></li>
              <li><a class="dropdown-item" href="<?= SITE_URL; ?>profile.php"><i class="bi bi-gear me-2"></i>My Profile</a></li>
              <?php if (isAdmin()): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-primary fw-bold" href="<?= SITE_URL; ?>admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= SITE_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-outline-success rounded-pill px-3 py-1 fw-semibold me-1" href="<?= SITE_URL; ?>login.php">Login</a>
            <a class="btn btn-pharmacy rounded-pill px-3 py-1" href="<?= SITE_URL; ?>register.php">Register</a>
          </li>
        <?php endif; ?>

        <!-- Cart Icon -->
        <li class="nav-item ms-2">
          <a href="<?= SITE_URL; ?>cart.php" class="cart-icon-badge" title="View Cart">
            <i class="bi bi-cart3"></i>
            <span class="cart-badge-counter <?= $cartCount > 0 ? '' : 'd-none'; ?>"><?= $cartCount; ?></span>
          </a>
        </li>
      </ul>

    </div>
  </div>
</nav>

<!-- Container for Flash Messages -->
<div class="container mt-3">
  <?php displayFlash(); ?>
</div>
