<!-- AFRIDI PHARMACY Footer -->
<footer class="footer-pharmacy">
  <div class="container">
    <div class="row g-4">
      <!-- Brand Summary -->
      <div class="col-lg-4 col-md-6">
        <h5 class="d-flex align-items-center gap-2">
          <i class="bi bi-crosshair2 text-emerald"></i> AFRIDI PHARMACY
        </h5>
        <p class="text-teal-200 small leading-relaxed mb-4">
          AFRIDI PHARMACY is your premier licensed online pharmacy and healthcare management solution. We provide 100% authentic medicines, surgical equipment, diagnostic devices, and health supplements delivered safely to your doorstep.
        </p>
        <div class="d-flex gap-3">
          <a href="#" class="btn btn-sm btn-outline-light rounded-circle" title="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" class="btn btn-sm btn-outline-light rounded-circle" title="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" class="btn btn-sm btn-outline-light rounded-circle" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          <a href="#" class="btn btn-sm btn-outline-light rounded-circle" title="Twitter"><i class="bi bi-twitter-x"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-2 col-md-6">
        <h5>Quick Links</h5>
        <ul>
          <li><a href="<?= SITE_URL; ?>index.php">Home</a></li>
          <li><a href="<?= SITE_URL; ?>products.php">Shop Medicines</a></li>
          <li><a href="<?= SITE_URL; ?>categories.php">Categories</a></li>
          <li><a href="<?= SITE_URL; ?>prescriptions.php">Upload Prescription</a></li>
          <li><a href="<?= SITE_URL; ?>about.php">About Us</a></li>
          <li><a href="<?= SITE_URL; ?>contact.php">Contact Us</a></li>
        </ul>
      </div>

      <!-- Top Categories -->
      <div class="col-lg-3 col-md-6">
        <h5>Top Categories</h5>
        <ul>
          <li><a href="<?= SITE_URL; ?>products.php?category=1">Prescription Medicines</a></li>
          <li><a href="<?= SITE_URL; ?>products.php?category=2">Vitamins & Supplements</a></li>
          <li><a href="<?= SITE_URL; ?>products.php?category=3">Baby Care & Formulas</a></li>
          <li><a href="<?= SITE_URL; ?>products.php?category=7">Medical Equipment</a></li>
          <li><a href="<?= SITE_URL; ?>products.php?category=8">Diabetes Care</a></li>
          <li><a href="<?= SITE_URL; ?>products.php?category=6">First Aid & Bandages</a></li>
        </ul>
      </div>

      <!-- Contact Info & Helpline -->
      <div class="col-lg-3 col-md-6">
        <h5>Store Location & Contact</h5>
        <ul class="small">
          <li class="d-flex gap-2">
            <i class="bi bi-geo-alt-fill text-emerald"></i>
            <span>Main Healthcare Commercial Plaza, Sector F-8/3, Islamabad, Pakistan</span>
          </li>
          <li class="d-flex gap-2 align-items-center">
            <i class="bi bi-telephone-fill text-emerald"></i>
            <span>+92 300 1234567 / +92 51 9876543</span>
          </li>
          <li class="d-flex gap-2 align-items-center">
            <i class="bi bi-envelope-fill text-emerald"></i>
            <span>support@afridipharmacy.com</span>
          </li>
          <li class="d-flex gap-2 align-items-center">
            <i class="bi bi-clock-fill text-emerald"></i>
            <span>Open 24 Hours / 7 Days a Week</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      <div>
        &copy; <?= date('Y'); ?> <strong>AFRIDI PHARMACY</strong>. All Rights Reserved. Licensed under Drug Regulatory Authority.
      </div>
      <div class="d-flex gap-3">
        <a href="<?= SITE_URL; ?>admin/index.php" class="text-teal-300 text-decoration-none"><i class="bi bi-shield-lock me-1"></i> Pharmacist Portal</a>
      </div>
    </div>
  </div>
</footer>

<!-- Toast Container for Dynamic Notifications -->
<div id="toast-container"></div>

<!-- Bootstrap 5 JavaScript Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Main JS -->
<script src="<?= SITE_URL; ?>assets/js/main.js"></script>

</body>
</html>
