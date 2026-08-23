<?php
session_start();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

// Load Centralized Backend API Logic
require_once __DIR__ . '/../../api/expiry_check_backend.php';

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/expiry_check.css?v=<?php echo filemtime(__DIR__ . '/../../themes/expiry_check.css'); ?>">

<div class="container-fluid p-4">
  <div class="row mb-3">
    <div class="col-12">
      <h3 style="font-family: 'Noto Sans Lao', 'Souliyo', 'Boon', sans-serif; color: #1a252f;"><i class="fas fa-hourglass-end mr-2 text-danger"></i> ກວດສອບວັນໝົດອາຍຸ (Expiry & Disposal Manager)</h3>
      <p class="text-muted">ກວດສອບ ແລະ ຕັດຈຳໜ່າຍສິນຄ້າທີ່ໃກ້ໝົດອາຍຸ ແລະ ໝົດອາຍຸແລ້ວ</p>
    </div>
  </div>

  <?php if ($message !== ''): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
      <?php echo $message; ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- Filters Card -->
  <div class="card shadow-sm mb-4" style="border-radius: 12px; border: none; background: white;">
    <div class="card-body">
      <form action="" method="GET" class="row">
        <div class="col-md-3 mb-2">
          <label>ຊື່ສິນຄ້າ</label>
          <input type="text" name="search_query" class="form-control" placeholder="ຊອກຫາສິນຄ້າ..." value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        <div class="col-md-3 mb-2">
          <label>ປະເພດສິນຄ້າ</label>
          <select name="category_id" class="form-control">
            <option value="">-- ທັງໝົດ --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['category_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 mb-2">
          <label>ສະຖານະວັນໝົດອາຍຸ</label>
          <select name="status_filter" class="form-control">
            <option value="">-- ທັງໝົດ --</option>
            <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>ໝົດອາຍຸແລ້ວ</option>
            <option value="near" <?php echo $status_filter === 'near' ? 'selected' : ''; ?>>ໃກ້ໝົດອາຍຸ</option>
            <option value="normal" <?php echo $status_filter === 'normal' ? 'selected' : ''; ?>>ປົກກະຕິ</option>
          </select>
        </div>
        <div class="col-md-3 mb-2 d-flex align-items-end">
          <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search mr-1"></i> ຄົ້ນຫາ</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Batches Table -->
  <div class="card shadow-sm" style="border-radius: 12px; border: none; background: white;">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-striped mb-0" style="font-size: 0.9rem;">
          <thead class="thead-light">
            <tr>
              <th>ຊື່ສິນຄ້າ</th>
              <th>ປະເພດສິນຄ້າ</th>
              <th>ບ່ອນວາງ</th>
              <th class="text-right">ຈຳນວນໃນລັອດ</th>
              <th>ວັນໝົດອາຍຸ</th>
              <th>ຈຳນວນວັນທີ່ເຫຼືອ</th>
              <th class="text-center">ສະຖານະ</th>
              <th class="text-center" width="150">ຈັດການ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($batches)): ?>
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">ບໍ່ມີລາຍການສິນຄ້າໝົດອາຍຸໃນຄັງ</td>
              </tr>
            <?php else: ?>
              <?php foreach ($batches as $b): ?>
                <?php
                $days = $b['days_left'];
                $row_class = '';
                $status_badge = '';
                
                if ($days <= 0) {
                    $row_class = 'table-danger';
                    $status_badge = '<span class="badge badge-dark p-2"><i class="fas fa-skull-crossbones mr-1"></i> ໝົດອາຍຸແລ້ວ</span>';
                } elseif ($days <= $expiry_warning_days) {
                    $row_class = 'table-warning';
                    $status_badge = '<span class="badge badge-warning text-dark p-2"><i class="fas fa-exclamation-circle mr-1"></i> ໃກ້ໝົດອາຍຸ</span>';
                } else {
                    $status_badge = '<span class="badge badge-success p-2"><i class="fas fa-check-circle mr-1"></i> ປົກກະຕິ</span>';
                }
                ?>
                <tr class="<?php echo $row_class; ?>">
                  <td class="font-weight-bold"><?php echo htmlspecialchars($b['product_name']); ?></td>
                  <td><?php echo htmlspecialchars($b['category_name']); ?></td>
                  <td><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($b['shelf_name'] ?? 'ບໍ່ມີບ່ອນວາງ'); ?></td>
                  <td class="text-right font-weight-bold"><?php echo $b['quantity']; ?> <?php echo htmlspecialchars($b['base_unit']); ?></td>
                  <td class="font-weight-bold"><?php echo date('d/m/Y', strtotime($b['expiry_date'])); ?></td>
                  <td>
                    <?php 
                    if ($days <= 0) {
                        echo '<span class="text-danger font-weight-bold">ກາຍກຳນົດ ' . abs($days) . ' ວັນ</span>';
                    } else {
                        echo 'ເຫຼືອ ' . $days . ' ວັນ';
                    }
                    ?>
                  </td>
                  <td class="text-center"><?php echo $status_badge; ?></td>
                  <td class="text-center">
                    <?php if ($days <= 0 || $_SESSION['status'] === 'ຜູ້ບໍລິຫານ'): ?>
                      <button class="btn btn-danger btn-sm font-weight-bold" onclick="openDisposalModal(<?php echo $b['batch_id']; ?>, '<?php echo htmlspecialchars($b['product_name']); ?>', <?php echo $b['quantity']; ?>, '<?php echo htmlspecialchars($b['base_unit']); ?>')">
                        <i class="fas fa-trash-alt mr-1"></i> ຕັດຈຳໜ່າຍ
                      </button>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Dispose/Write-off -->
<div class="modal fade" id="disposalModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius:12px;">
      <form action="" method="POST">
        <input type="hidden" name="action" value="dispose_batch">
        <input type="hidden" name="batch_id" id="dispose_batch_id">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao', 'Souliyo', 'Boon', sans-serif;">ຢືນຢັນການຕັດຈຳໜ່າຍສິນຄ້າ</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="p-3 bg-light rounded mb-3">
            <p class="mb-1"><strong>ຊື່ສິນຄ້າ:</strong> <span id="disp_product_name"></span></p>
            <p class="mb-0"><strong>ຈຳນວນທີ່ຈະຕັດຈຳໜ່າຍ:</strong> <span class="text-danger font-weight-bold" id="disp_qty_display"></span></p>
          </div>
          <div class="form-group">
            <label>ເຫດຜົນໃນການຕັດຈຳໜ່າຍ <span class="text-danger">*</span></label>
            <select name="reason" class="form-control" required>
              <option value="ສິນຄ້າໝົດອາຍຸ">ສິນຄ້າໝົດອາຍຸ (Expired)</option>
              <option value="ສິນຄ້າເສຍຫາຍ/ແຕກຫັກ">ສິນຄ້າເສຍຫາຍ/ແຕກຫັກ (Damaged)</option>
              <option value="ສິນຄ້າເສື່ອມສະພາບ">ສິນຄ້າເສື່ອມສະພາບ (Deteriorated)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-danger font-weight-bold"><i class="fas fa-check-circle mr-1"></i> ຢືນຢັນການຕັດຈຳໜ່າຍ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<script>
  function openDisposalModal(batchId, productName, quantity, baseUnit) {
    $('#dispose_batch_id').val(batchId);
    $('#disp_product_name').text(productName);
    $('#disp_qty_display').text(quantity + ' ' + baseUnit);
    $('#disposalModal').modal('show');
  }
</script>
