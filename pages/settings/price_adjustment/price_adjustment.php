<?php
// Price Adjustment Page - Modular Clean Architecture
session_start();
$base_path = '../../../';
require_once __DIR__ . '/../../../config/db.php';

if (empty($_SESSION['user_id']) || (!hasPermission('price_adjustment') && !hasPermission('setup') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

// Load Backend Logic & Data Fetching from centralized API directory
require_once __DIR__ . '/../../../api/price_adj_backend.php';

require_once __DIR__ . '/../../../layouts/header.php';
?>

<div class="container-fluid p-4">
  <!-- Header Title & Add Button -->
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 10px;">
    <div>
      <h5 class="m-0 font-weight-bold text-dark" style="font-size: 1.1rem; white-space: nowrap;">
        <i class="fas fa-tags text-warning mr-2"></i> ຈັດການປັບລາຄາສິນຄ້າ
      </h5>
    </div>
    <div class="d-flex align-items-center justify-content-end ml-auto" style="gap: 8px; flex-wrap: wrap;">
      <?php if ($isAdmin || $isMain): ?>
        <form method="GET" action="" class="m-0 d-inline-block">
          <select name="store_id" class="form-control form-control-sm font-weight-bold border-warning text-warning" style="height: 38px; border-radius: 8px; background-color: #fffbeb; min-width: 130px;" onchange="this.form.submit()">
            <option value="0">-- ທຸກສາຂາ --</option>
            <?php foreach ($stores as $st): ?>
              <option value="<?php echo $st['store_id']; ?>" <?php echo ($filter_store == $st['store_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($st['store_name']); ?> <?php echo !empty($st['is_main']) ? '(ສາງຫຼັກ)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      <?php endif; ?>
      <?php if (hasPermission('price_adjustment', 'add')): ?>
        <button type="button" class="btn btn-primary font-weight-bold text-white shadow-sm px-3 py-2" data-toggle="modal" data-target="#priceAdjModal" style="border-radius: 8px; font-size: 0.88rem; height: 38px; white-space: nowrap;">
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
