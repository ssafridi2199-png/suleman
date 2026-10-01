<?php
$adminTitle = "Pharmacist Prescription Review";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

// Approve / Reject Prescription POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_rx'])) {
    $rxId = intval($_POST['rx_id']);
    $newStatus = sanitize($_POST['status']);
    $pharmacistNotes = sanitize($_POST['pharmacist_notes'] ?? '');

    $upd = $db->prepare("UPDATE prescriptions SET status = ?, pharmacist_notes = ? WHERE id = ?");
    $upd->execute([$newStatus, $pharmacistNotes, $rxId]);

    setFlash('success', "Prescription #{$rxId} status updated to {$newStatus}!");
    header("Location: " . SITE_URL . "admin/prescriptions.php");
    exit;
}

$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$where = ["1=1"];
$params = [];

if ($statusFilter !== '') {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

$whereSQL = implode(" AND ", $where);

$sql = "SELECT p.*, u.full_name AS patient_name, u.phone AS patient_phone, u.email AS patient_email, o.order_number 
        FROM prescriptions p 
        JOIN users u ON p.user_id = u.id 
        LEFT JOIN orders o ON p.order_id = o.id 
        WHERE {$whereSQL} 
        ORDER BY p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rxList = $stmt->fetchAll();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-medical me-2 text-danger"></i> Pharmacist Prescription Review Portal</h2>
        <p class="text-muted mb-0">Review doctor prescriptions, verify PMDC credentials, and approve orders</p>
      </div>
    </div>

    <?php displayFlash(); ?>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
      <form action="" method="GET" class="row g-3 align-items-center">
        <div class="col-md-6">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Review Statuses</option>
            <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending Pharmacist Review</option>
            <option value="Approved" <?= $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-pharmacy w-100 rounded-pill">Filter Prescriptions</button>
        </div>
      </form>
    </div>

    <!-- Prescriptions Grid -->
    <div class="row g-4">
      <?php if (empty($rxList)): ?>
        <div class="col-12 text-center text-muted py-5">No prescription files found matching filters.</div>
      <?php else: ?>
        <?php foreach ($rxList as $rx): 
          $badge = 'bg-warning text-dark';
          if ($rx['status'] === 'Approved') $badge = 'bg-success';
          elseif ($rx['status'] === 'Rejected') $badge = 'bg-danger';
        ?>
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <span class="badge <?= $badge; ?> px-3 py-2 rounded-pill fs-6"><?= $rx['status']; ?></span>
                  <span class="text-muted small ms-2">• <?= date('M d, Y h:i A', strtotime($rx['created_at'])); ?></span>
                </div>
                <?php if ($rx['order_number']): ?>
                  <span class="badge bg-light text-dark border">Order: <?= htmlspecialchars($rx['order_number']); ?></span>
                <?php endif; ?>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-5">
                  <div class="bg-light p-2 rounded-3 border text-center">
                    <a href="<?= SITE_URL; ?>uploads/prescriptions/<?= htmlspecialchars($rx['prescription_file']); ?>" target="_blank" title="Click to view full image">
                      <img src="<?= SITE_URL; ?>uploads/prescriptions/<?= htmlspecialchars($rx['prescription_file']); ?>" class="img-fluid rounded" style="max-height: 140px; object-fit: contain;">
                    </a>
                    <a href="<?= SITE_URL; ?>uploads/prescriptions/<?= htmlspecialchars($rx['prescription_file']); ?>" target="_blank" class="d-block small text-primary mt-1 text-decoration-none"><i class="bi bi-box-arrow-up-right me-1"></i> View Full File</a>
                  </div>
                </div>

                <div class="col-md-7">
                  <strong class="d-block text-dark fs-6"><?= htmlspecialchars($rx['patient_name']); ?></strong>
                  <small class="text-muted d-block"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($rx['patient_phone']); ?></small>
                  <small class="text-muted d-block"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($rx['patient_email']); ?></small>

                  <?php if (!empty($rx['notes'])): ?>
                    <div class="mt-2 small text-secondary">
                      <strong>Patient Note:</strong> <?= htmlspecialchars($rx['notes']); ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Pharmacist Action Form -->
              <form action="" method="POST" class="pt-3 border-top mt-auto">
                <input type="hidden" name="rx_id" value="<?= $rx['id']; ?>">
                <div class="mb-2">
                  <label class="form-label small fw-bold">Pharmacist Verification Note</label>
                  <input type="text" name="pharmacist_notes" class="form-control form-control-sm" placeholder="e.g. Verified valid prescription by Dr. PMDC #" value="<?= htmlspecialchars($rx['pharmacist_notes'] ?? ''); ?>">
                </div>

                <div class="d-flex gap-2">
                  <button type="submit" name="status" value="Approved" class="btn btn-sm btn-success rounded-pill flex-grow-1" onclick="return confirm('Approve this prescription?');">
                    <i class="bi bi-check-circle me-1"></i> Approve Rx
                  </button>
                  <button type="submit" name="status" value="Rejected" class="btn btn-sm btn-outline-danger rounded-pill flex-grow-1" onclick="return confirm('Reject this prescription?');">
                    <i class="bi bi-x-circle me-1"></i> Reject Rx
                  </button>
                  <input type="hidden" name="review_rx" value="1">
                </div>
              </form>

            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
