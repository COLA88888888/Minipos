<?php
// Standalone Page: ລາຍການຂາຍສິນຄ້າ (Itemized Sales Report)
// Uses shared partials via reports_backend.php
// ຄ່າເລີ່ມຕົ້ນ: ສະແດງຕາມບິນ (invoice)
$_GET['type']      = 'item_sales';
$_GET['view_mode'] = $_GET['view_mode'] ?? 'invoice';

require_once __DIR__ . '/partials/reports_backend.php';
require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/reports.css?v=<?php echo filemtime(__DIR__ . '/../../themes/reports.css'); ?>">

<div class="container-fluid p-3 p-md-4">
  <!-- Header & Page Meta -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
    <div>
      <h5 class="font-weight-bold text-dark mb-1" style="font-family: 'Noto Sans Lao', 'Souliyo', 'Boon', sans-serif;">
        <i class="fas fa-boxes mr-2" style="color: #a855f7;"></i> ລາຍການຂາຍສິນຄ້າ
      </h5>
    </div>
  </div>

  <!-- Search & Filter Controls (shared partial) -->
  <?php require_once __DIR__ . '/partials/reports_filter.php'; ?>

  <!-- KPI Summary Cards (shared partial) -->
  <?php require_once __DIR__ . '/partials/reports_kpi.php'; ?>

  <!-- Main Sales Report Table (shared partial - item mode) -->
  <?php require_once __DIR__ . '/partials/reports_table.php'; ?>

  <!-- Circular Blue Pagination (shared partial) -->
  <?php require_once __DIR__ . '/partials/reports_pagination.php'; ?>
</div>

<!-- Modal & JavaScript (shared partials) -->
<?php require_once __DIR__ . '/partials/reports_modal.php'; ?>
<?php require_once __DIR__ . '/partials/js/reports_js.php'; ?>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
