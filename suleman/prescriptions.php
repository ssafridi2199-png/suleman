<?php
$pageTitle = "Prescription Upload & Review";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$db = getDBConnection();
$userId = $_SESSION['user_id'] ?? null;

// Handle Standalone Prescription Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_rx'])) {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please log in or create an account to submit a prescription for pharmacist review.');
        header("Location: " . SITE_URL . "login.php");
        exit;
    }

    $notes = sanitize($_POST['notes'] ?? '');

    if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['prescription_file']['tmp_name'];
        $fileName = $_FILES['prescription_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($fileExt, $allowed)) {
            setFlash('danger', 'Invalid file format. Please upload JPG, PNG, or PDF format.');
        } else {
            $rxFileName = 'rx_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $targetPath = __DIR__ . '/uploads/prescriptions/' . $rxFileName;
            
            if (move_uploaded_file($fileTmp, $targetPath)) {
                $ins = $db->prepare("INSERT INTO prescriptions (user_id, prescription_file, notes, status) VALUES (?, ?, ?, 'Pending')");
                $ins->execute([$userId, $rxFileName, $notes]);

                // Create notification for admin
                $notif = $db->prepare("INSERT INTO notifications (title, message, type) VALUES ('New Prescription Uploaded', 'Customer uploaded prescription {$rxFileName} for pharmacist review.', 'info')");
                $notif->execute();

                setFlash('success', 'Prescription uploaded successfully! Our licensed pharmacist will review it shortly.');
                header("Location: " . SITE_URL . "prescriptions.php");
                exit;
            } else {
                setFlash('danger', 'Failed to save uploaded file. Please try again.');
            }
        }
    } else {
        setFlash('danger', 'Please select a valid prescription file to upload.');
    }
}

// Fetch User Prescriptions
$prescriptions = [];
if ($userId) {
    $stmt = $db->prepare("SELECT p.*, o.order_number FROM prescriptions p LEFT JOIN orders o ON p.order_id = o.id WHERE p.user_id = ? ORDER BY p.created_at DESC");
    $stmt->execute([$userId]);
    $prescriptions = $stmt->fetchAll();
}
?>

<div class="container my-4">
  <div class="row g-4">
    
    <!-- UPLOAD FORM -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
        <h4 class="fw-bold mb-2 text-dark"><i class="bi bi-file-earmark-medical me-2 text-danger"></i> Upload Doctor's Prescription</h4>
        <p class="text-muted small mb-4">Upload your prescription photo or PDF. Our certified pharmacists will verify your medication and assist with dosage and delivery.</p>

        <form action="" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label small fw-bold">Select Prescription File</label>
            <input type="file" name="prescription_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
            <div class="form-text">Supported formats: JPG, PNG, PDF (Max 5MB)</div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold">Additional Notes / Patient Details (Optional)</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Specify required dosage, quantity, or patient medical notes..."></textarea>
          </div>

          <button type="submit" name="upload_rx" class="btn btn-pharmacy btn-lg w-100 rounded-pill shadow-sm">
            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Submit Prescription
          </button>
        </form>
      </div>
    </div>

    <!-- PRESCRIPTION HISTORY & TRACKER -->
    <div class="col-lg-7">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">My Uploaded Prescriptions</h4>
      </div>

      <?php if (!isLoggedIn()): ?>
        <div class="card border-0 shadow-sm rounded-4 text-center p-5">
          <i class="bi bi-lock fs-1 text-muted mb-2"></i>
          <h5 class="fw-bold">Login to View Prescriptions</h5>
          <p class="text-muted small">Please sign in to track your pharmacist reviews and prescription order history.</p>
          <div>
            <a href="<?= SITE_URL; ?>login.php" class="btn btn-pharmacy rounded-pill btn-sm">Login Now</a>
          </div>
        </div>
      <?php elseif (empty($prescriptions)): ?>
        <div class="card border-0 shadow-sm rounded-4 text-center p-5">
          <i class="bi bi-file-earmark-x fs-1 text-muted mb-2"></i>
          <h5 class="fw-bold">No Prescriptions Uploaded</h5>
          <p class="text-muted small">You haven't uploaded any standalone prescriptions yet. Use the upload box on the left to get started!</p>
        </div>
      <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($prescriptions as $rx): 
            $statusBadge = 'bg-warning text-dark';
            if ($rx['status'] === 'Approved') $statusBadge = 'bg-success';
            elseif ($rx['status'] === 'Rejected') $statusBadge = 'bg-danger';
          ?>
            <div class="card border-0 shadow-sm rounded-4 p-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="bg-teal-subtle text-teal rounded-3 p-3 text-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-file-earmark-pdf fs-3"></i>
                  </div>
                  <div>
                    <strong class="d-block text-dark"><?= htmlspecialchars($rx['prescription_file']); ?></strong>
                    <span class="text-muted small">Uploaded: <?= date('M d, Y, h:i A', strtotime($rx['created_at'])); ?></span>
                  </div>
                </div>
                <span class="badge <?= $statusBadge; ?> px-3 py-2 rounded-pill fs-6"><?= $rx['status']; ?></span>
              </div>

              <?php if (!empty($rx['notes'])): ?>
                <div class="small text-muted mb-2"><strong>Patient Note:</strong> <?= htmlspecialchars($rx['notes']); ?></div>
              <?php endif; ?>

              <?php if (!empty($rx['pharmacist_notes'])): ?>
                <div class="alert alert-info border-info py-2 px-3 small rounded-3 mb-2">
                  <strong>Pharmacist Feedback:</strong> <?= htmlspecialchars($rx['pharmacist_notes']); ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($rx['order_number'])): ?>
                <div class="small fw-semibold text-teal mt-2">Linked Order: <a href="<?= SITE_URL; ?>order-details.php?id=<?= $rx['order_id']; ?>"><?= htmlspecialchars($rx['order_number']); ?></a></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
