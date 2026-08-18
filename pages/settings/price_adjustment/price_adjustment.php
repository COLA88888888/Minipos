<?php
// Price Adjustment Page - Modular Clean Architecture
session_start();
$base_path = '../../../';
require_once __DIR__ . '/../../../config/db.php';

if (empty($_SESSION['user_id']) || (!hasPermission('setup') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

// Load Backend Logic & Data Fetching from centralized API directory
require_once __DIR__ . '/../../../api/price_adj_backend.php';

require_once __DIR__ . '/../../../layouts/header.php';
?>

<div class="container-fluid p-4">
  <!-- Header Title & Add Button -->
  <div class="row mb-3 align-items-center justify-content-between">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold">
        <i class="fas fa-tags text-warning mr-2"></i> ຈັດການປັບລາຄາສິນຄ້າ
    </div>
    <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
      <?php if (hasPermission('price_adjustment', 'add')): ?>
        <button type="button" class="btn btn-primary font-weight-bold text-white shadow-sm px-3.5 py-2" data-toggle="modal" data-target="#priceAdjModal" style="border-radius: 6px; font-size: 0.92rem;">
          <i class="fas fa-plus-circle mr-1.5"></i> ປັບລາຄາສິນຄ້າໃໝ່
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Toast Notification -->
  <?php if ($message !== ''): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: '<?php echo $message_type === "success" ? "success" : "error"; ?>',
          title: <?php echo json_encode($message); ?>,
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true
        });
      });
    </script>
  <?php endif; ?>

  <!-- Modal Form Partial -->
  <?php require_once __DIR__ . '/partials/price_adj_modal.php'; ?>

  <!-- History Report Table Partial -->
  <div class="row">
    <div class="col-12 mb-4">
      <?php require_once __DIR__ . '/partials/price_adj_table.php'; ?>
    </div>
  </div>
</div>

<!-- JavaScript Functionality Partial -->
<?php require_once __DIR__ . '/partials/price_adj_js.php'; ?>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
