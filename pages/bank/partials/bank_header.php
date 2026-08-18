<?php
// pages/bank/partials/bank_header.php
if (!defined('MINIPOS_APP')) {
    define('MINIPOS_APP', true);
}
?>
<?php
  $selected_branch_name = 'ທຸກສາຂາ (All Branches)';
  if (!empty($filter_store_id) && $filter_store_id > 0) {
    foreach ($branches_list as $b) {
      if ($b['store_id'] == $filter_store_id) {
        $selected_branch_name = $b['store_name'];
        break;
      }
    }
  }
?>
<!-- Bank Management Header with Date Range & Store Filter -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h5 class="font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
      <i class="fas fa-university text-primary mr-2"></i> ຈັດການທະນາຄານ
    </h5>
  </div>

  <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0" style="gap: 8px;">
    <!-- Date & Store Filter Form -->
    <form method="GET" action="" class="form-inline d-flex align-items-center" style="gap: 6px;">
      <!-- Store / Branch Selector Box -->
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text bg-white font-weight-bold text-dark" style="font-size: 0.78rem;">
            <i class="fas fa-store text-primary mr-1"></i> ສາຂາ:
          </span>
        </div>
        <select name="store_id" class="form-control form-control-sm font-weight-bold text-primary" style="border-radius: 0 6px 6px 0; min-width: 150px; width: auto; font-size: 0.82rem;" onchange="this.form.submit()">
          <option value="0">-- ທຸກສາຂາ --</option>
          <?php foreach ($branches_list as $b): ?>
            <option value="<?php echo $b['store_id']; ?>" <?php echo ($filter_store_id == $b['store_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($b['store_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text bg-white font-weight-bold" style="font-size: 0.78rem;">ແຕ່ວັນທີ:</span>
        </div>
        <input type="date" name="start_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($start_date === '2000-01-01' ? date('Y-m-01') : $start_date); ?>" style="border-radius: 0 6px 6px 0;">
      </div>

      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text bg-white font-weight-bold" style="font-size: 0.78rem;">ຫາວັນທີ:</span>
        </div>
        <input type="date" name="end_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($end_date); ?>" style="border-radius: 0 6px 6px 0;">
      </div>

      <button type="submit" class="btn btn-sm btn-info font-weight-bold shadow-xs px-2.5" style="border-radius: 6px; font-size: 0.82rem;" title="ຄົ້ນຫາຕາມຊ່ວງວັນທີ ແລະ ສາຂາ">
        <i class="fas fa-search mr-1"></i> ຄົ້ນຫາ
      </button>

      <a href="bank.php" class="btn btn-sm btn-outline-secondary font-weight-bold shadow-xs px-2" style="border-radius: 6px; font-size: 0.82rem;" title="ລ້າງຄ່າຄົ້ນຫາ (ທັງໝົດ)">
        <i class="fas fa-sync-alt"></i> ທັງໝົດ
      </a>
    </form>

    <?php if (hasPermission('accounting', 'add') || hasPermission('bank', 'add')): ?>
      <button type="button" class="btn btn-sm btn-primary font-weight-bold shadow-sm ml-md-2" data-toggle="modal" data-target="#addBankAccountModal" style="border-radius: 6px; font-size: 0.82rem; padding: 6px 14px;">
        <i class="fas fa-plus-circle mr-1"></i> ເພີ່ມບັນຊີທະນາຄານ
      </button>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($message)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: '<?php echo ($message_type === "success") ? "success" : "warning"; ?>',
        title: '<?php echo ($message_type === "success") ? "ດຳເນີນການສຳເລັດ!" : "ແຈ້ງເຕືອນ!"; ?>',
        text: <?php echo json_encode($message); ?>,
        confirmButtonColor: '#0284c7',
        confirmButtonText: 'ຕົກລົງ'
      });
    });
  </script>
<?php endif; ?>
