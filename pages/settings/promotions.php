<?php
session_start();
$base_path = '../../';
require_once __DIR__ . '/../../config/db.php';

if (empty($_SESSION['user_id']) || (!hasPermission('setup') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_promo') {
        $name = trim($_POST['promo_name'] ?? '');
        $discount_type = $_POST['discount_type'] ?? 'percentage';
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $start_date = $_POST['start_date'] ?? date('Y-m-d');
        $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $min_amount = floatval($_POST['min_amount'] ?? 0);

        if ($name !== '' && $discount_value > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO promotions (promo_name, promo_type, discount_type, discount_value, start_date, end_date, min_amount, status, branch_id) VALUES (?, 'discount', ?, ?, ?, ?, ?, 1, 1)");
                $stmt->execute([$name, $discount_type, $discount_value, $start_date, $end_date, $min_amount]);
                $message = 'ເພີ່ມໂປຣໂມຊັ່ນໃໝ່ສຳເລັດ!';
                $message_type = 'success';
                logActivity($pdo, "ເພີ່ມໂປຣໂມຊັ່ນ", $name);
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif ($_POST['action'] === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        $status = intval($_POST['status'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE promotions SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $message = 'ອັບເດດສະຖານະໂປຣໂມຊັ່ນສຳເລັດ!';
            $message_type = 'success';
        }
    } elseif ($_POST['action'] === 'delete_promo') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'ລົບໂປຣໂມຊັ່ນສຳເລັດ!';
            $message_type = 'success';
        }
    }
}

$promos = $pdo->query("SELECT * FROM promotions ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid p-4">
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-percent text-danger mr-2"></i> ຈັດການໂປຣໂມຊັ່ນ (Promotions & Discounts)
      </h5>
    </div>
    <div class="col-sm-6 text-right">
      <button type="button" class="btn btn-danger px-3 font-weight-bold" data-toggle="modal" data-target="#addPromoModal" style="border-radius: 6px;">
        <i class="fas fa-plus-circle mr-1"></i> ສ້າງໂປຣໂມຊັ່ນໃໝ່
      </button>
    </div>
  </div>

  <?php if ($message !== ''): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: '<?php echo $message_type === "success" ? "success" : "error"; ?>',
          title: '<?php echo $message_type === "success" ? "ສຳເລັດ" : "ແຈ້ງເຕືອນ"; ?>',
          text: '<?php echo $message; ?>',
          confirmButtonColor: '#2563eb'
        });
      });
    </script>
  <?php endif; ?>

  <div class="card border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-tags mr-2 text-danger"></i> ລາຍການໂປຣໂມຊັ່ນທັງໝົດ</h6>
      <span class="badge badge-light border text-muted"><?php echo count($promos); ?> ລາຍການ</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="bg-light text-muted small font-weight-bold">
          <tr>
            <th class="text-center" style="width: 70px;">ລ/ດ</th>
            <th>ຊື່ໂປຣໂມຊັ່ນ</th>
            <th class="text-center">ປະເພດສ່ວນຫຼຸດ</th>
            <th class="text-right">ມູນຄ່າສ່ວນຫຼຸດ</th>
            <th class="text-center">ໄລຍະເວລາ</th>
            <th class="text-center">ສະຖານະ</th>
            <th class="text-center" style="width: 100px;">ຈັດການ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($promos)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-percent fa-2x mb-2 d-block text-secondary opacity-50"></i>ບໍ່ມີລາຍການໂປຣໂມຊັ່ນ</td></tr>
          <?php else: ?>
            <?php $i = 1; foreach ($promos as $p): ?>
              <tr>
                <td class="text-center text-muted"><?php echo $i++; ?></td>
                <td>
                  <span class="font-weight-bold text-dark"><?php echo htmlspecialchars($p['promo_name']); ?></span>
                  <?php if ($p['min_amount'] > 0): ?>
                    <small class="d-block text-muted">ຍອດຊື້ຂັ້ນຕ່ຳ: <?php echo number_format($p['min_amount'], 0); ?> ₭</small>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="badge badge-light border font-weight-bold">
                    <?php echo $p['discount_type'] === 'percentage' ? 'ເປີເຊັນ (%)' : 'ຈຳນວນເງິນ (₭)'; ?>
                  </span>
                </td>
                <td class="text-right font-weight-bold text-danger">
                  <?php echo $p['discount_type'] === 'percentage' ? ($p['discount_value'] . '%') : (number_format($p['discount_value'], 0) . ' ₭'); ?>
                </td>
                <td class="text-center text-muted small">
                  <?php echo htmlspecialchars($p['start_date']); ?> ຫາ <?php echo htmlspecialchars($p['end_date']); ?>
                </td>
                <td class="text-center">
                  <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="status" value="<?php echo $p['status'] ? 0 : 1; ?>">
                    <button type="submit" class="btn btn-sm <?php echo $p['status'] ? 'btn-success' : 'btn-secondary'; ?> font-weight-bold" style="border-radius: 20px; font-size: 0.75rem; padding: 2px 10px;">
                      <?php echo $p['status'] ? '<i class="fas fa-check-circle mr-1"></i> ເປີດໃຊ້' : '<i class="fas fa-times-circle mr-1"></i> ປິດ'; ?>
                    </button>
                  </form>
                </td>
                <td class="text-center">
                  <form action="" method="POST" onsubmit="return confirm('ຕ້ອງການລົບໂປຣໂມຊັ່ນນີ້ແທ້ຫຼືບໍ່?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete_promo">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash-alt"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Add Promo -->
<div class="modal fade" id="addPromoModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <form action="" method="POST">
        <input type="hidden" name="action" value="add_promo">
        <div class="modal-header bg-danger text-white py-3 px-4">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao Looped';">
            <i class="fas fa-plus-circle mr-2"></i> ສ້າງໂປຣໂມຊັ່ນໃໝ່
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ຊື່ໂປຣໂມຊັ່ນ: <span class="text-danger">*</span></label>
            <input type="text" name="promo_name" class="form-control" placeholder="ເຊັ່ນ: ຫຼຸດພິເສດທ້າຍປີ, ສ່ວນຫຼຸດສະມາຊິກ..." required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ປະເພດສ່ວນຫຼຸດ:</label>
              <select name="discount_type" class="form-control">
                <option value="percentage">ເປີເຊັນ (%)</option>
                <option value="fixed">ຈຳນວນເງິນ (₭)</option>
              </select>
            </div>
            <div class="col-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ມູນຄ່າສ່ວນຫຼຸດ: <span class="text-danger">*</span></label>
              <input type="number" step="0.01" name="discount_value" class="form-control text-right font-weight-bold text-danger" placeholder="0" min="0.01" required>
            </div>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ວັນທີເລີ່ມ:</label>
              <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ວັນທີສິ້ນສຸດ:</label>
              <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
            </div>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ຍອດຊື້ຂັ້ນຕ່ຳ (₭):</label>
            <input type="number" name="min_amount" class="form-control text-right" placeholder="0" value="0">
          </div>
        </div>
        <div class="modal-footer border-0 pb-4 px-4 pt-0">
          <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">ບັນທຶກໂປຣໂມຊັ່ນ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
