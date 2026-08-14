<?php
// ==========================================
// ໂມດູນຈັດການໂປຣໂມຊັ່ນ (Promotions Management Module)
// ==========================================
session_start();
$base_path = '../../../';
require_once __DIR__ . '/../../../config/db.php';

if (empty($_SESSION['user_id']) || (!hasPermission('setup') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_promo') {
        $promo_name     = trim($_POST['promo_name'] ?? '');
        $promo_type     = $_POST['promo_type'] ?? 'discount';
        $discount_type  = $_POST['discount_type'] ?? 'percentage';
        $discount_value    = floatval(str_replace(',', '', $_POST['discount_value'] ?? 0));
        $gift_product_name = trim($_POST['gift_product_name'] ?? '');
        $gift_qty          = intval($_POST['gift_qty'] ?? 1);
        $start_date        = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
        $end_date          = !empty($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d', strtotime('+30 days'));
        $min_qty           = intval(str_replace(',', '', $_POST['min_qty'] ?? 0));
        $min_amount        = floatval(str_replace(',', '', $_POST['min_amount'] ?? 0));
        $target_type       = $_POST['target_type'] ?? 'all';
        $target_name       = trim($_POST['target_name'] ?? 'ທຸກສິນຄ້າ');
        $target_unit_name  = trim($_POST['target_unit_name'] ?? 'all');
        if (empty($target_unit_name)) $target_unit_name = 'all';
        $status            = intval($_POST['status'] ?? 1);

        if ($target_type === 'all') {
            $target_name = 'ທຸກສິນຄ້າ';
        }

        if ($promo_name !== '') {
            try {
                $stmt = $pdo->prepare("INSERT INTO promotions (promo_name, promo_type, discount_type, discount_value, gift_product_name, gift_qty, start_date, end_date, min_qty, min_amount, target_type, target_name, target_unit_name, status, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$promo_name, $promo_type, $discount_type, $discount_value, $gift_product_name, $gift_qty, $start_date, $end_date, $min_qty, $min_amount, $target_type, $target_name, $target_unit_name, $status]);
                $message = 'ເພີ່ມໂປຣໂມຊັ່ນໃໝ່ສຳເລັດ!';
                $message_type = 'success';
                logActivity($pdo, "ເພີ່ມໂປຣໂມຊັ່ນ", $promo_name);
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif ($action === 'edit_promo') {
        $id                = intval($_POST['id'] ?? 0);
        $promo_name        = trim($_POST['promo_name'] ?? '');
        $promo_type        = $_POST['promo_type'] ?? 'discount';
        $discount_type     = $_POST['discount_type'] ?? 'percentage';
        $discount_value    = floatval(str_replace(',', '', $_POST['discount_value'] ?? 0));
        $gift_product_name = trim($_POST['gift_product_name'] ?? '');
        $gift_qty          = intval($_POST['gift_qty'] ?? 1);
        $start_date        = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
        $end_date          = !empty($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d', strtotime('+30 days'));
        $min_qty           = intval(str_replace(',', '', $_POST['min_qty'] ?? 0));
        $min_amount        = floatval(str_replace(',', '', $_POST['min_amount'] ?? 0));
        $target_type       = $_POST['target_type'] ?? 'all';
        $target_name       = trim($_POST['target_name'] ?? 'ທຸກສິນຄ້າ');
        $target_unit_name  = trim($_POST['target_unit_name'] ?? 'all');
        if (empty($target_unit_name)) $target_unit_name = 'all';

        if ($target_type === 'all') {
            $target_name = 'ທຸກສິນຄ້າ';
        }

        if ($id > 0 && $promo_name !== '') {
            try {
                $stmt = $pdo->prepare("UPDATE promotions SET promo_name = ?, promo_type = ?, discount_type = ?, discount_value = ?, gift_product_name = ?, gift_qty = ?, start_date = ?, end_date = ?, min_qty = ?, min_amount = ?, target_type = ?, target_name = ?, target_unit_name = ? WHERE id = ?");
                $stmt->execute([$promo_name, $promo_type, $discount_type, $discount_value, $gift_product_name, $gift_qty, $start_date, $end_date, $min_qty, $min_amount, $target_type, $target_name, $target_unit_name, $id]);
                $message = 'ອັບເດດໂປຣໂມຊັ່ນສຳເລັດ!';
                $message_type = 'success';
                logActivity($pdo, "ແກ້ໄຂໂປຣໂມຊັ່ນ", "$promo_name (ID: $id)");
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        $status = intval($_POST['status'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE promotions SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $message = 'ອັບເດດສະຖານະໂປຣໂມຊັ່ນສຳເລັດ!';
            $message_type = 'success';
        }
    } elseif ($action === 'delete_promo') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'ລົບໂປຣໂມຊັ່ນສຳເລັດ!';
            $message_type = 'success';
        }
    }
}

try {
    $pdo->exec("UPDATE promotions SET status = 0 WHERE status = 1 AND end_date < CURDATE()");
} catch (Exception $e) {}
$promos = $pdo->query("SELECT * FROM promotions ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories & products for target selection
$categories = [];
try {
    $categories = $pdo->query("SELECT category_name FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

$productsList = [];
try {
    $productsList = $pdo->query("SELECT product_name FROM products ORDER BY product_name ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

require_once __DIR__ . '/../../../layouts/header.php';
?>

<div class="container-fluid p-4">
  <!-- Page Header Title -->
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-tags text-primary mr-2"></i> ຈັດການໂປຣໂມຊັ່ນ (Promotions & Discounts)
      </h5>
    </div>
    <div class="col-sm-6 text-right">
      <button type="button" class="btn btn-primary px-3.5 font-weight-bold text-white shadow-sm" data-toggle="modal" data-target="#addPromoModal" style="border-radius: 8px;">
        <i class="fas fa-plus-circle mr-1.5"></i> ສ້າງໂປຣໂມຊັ່ນໃໝ່
      </button>
    </div>
  </div>

  <!-- Auto-closing Toast notification (No OK button required) -->
  <?php if ($message !== ''): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: '<?php echo $message_type === "success" ? "success" : "error"; ?>',
          title: <?php echo json_encode($message); ?>,
          showConfirmButton: false,
          timer: 1300,
          timerProgressBar: true
        });
      });
    </script>
  <?php endif; ?>

  <!-- Table Card: ລຳດັບ, ຊື່ໂປຣ, ປະເພດ, ສ່ວນຫຼຸດ, ວັນທີ, ເງື່ອນໄຂ, ສິນຄ້າ/ປະເພດ, ສະຖານະ, ຈັດການ -->
  <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1.5px solid #e2e8f0; background: #ffffff;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-list mr-2 text-primary"></i> ລາຍການໂປຣໂມຊັ່ນທັງໝົດ</h6>
      <span class="badge badge-light border font-weight-bold text-muted px-2.5 py-1"><?php echo count($promos); ?> ລາຍການ</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
        <thead style="background: #f8fafc; color: #475569; font-size: 0.82rem;" class="font-weight-bold">
          <tr>
            <th class="text-center" style="width: 55px;">ລຳດັບ</th>
            <th>ຊື່ໂປຣ</th>
            <th>ປະເພດ</th>
            <th class="text-right">ສ່ວນຫຼຸດ</th>
            <th class="text-center">ວັນທີ</th>
            <th class="text-center">ເງື່ອນໄຂ</th>
            <th>ສິນຄ້າ/ປະເພດ</th>
            <th class="text-center" style="width: 80px;">ສະຖານະ</th>
            <th class="text-center" style="width: 100px;">ຈັດການ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($promos)): ?>
            <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-tags fa-2x mb-2 d-block text-secondary opacity-50"></i>ບໍ່ມີລາຍການໂປຣໂມຊັ່ນ</td></tr>
          <?php else: ?>
            <?php $i = 1; foreach ($promos as $p): ?>
              <tr>
                <!-- 1. ລຳດັບ -->
                <td class="text-center font-weight-bold text-muted"><?php echo $i++; ?></td>
                
                <!-- 2. ຊື່ໂປຣ -->
                <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($p['promo_name']); ?></td>
                
                <!-- 3. ປະເພດ -->
                <td>
                  <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 0.76rem;">
                    <?php 
                      switch ($p['promo_type'] ?? 'discount') {
                        case 'qty_discount': echo 'ສ່ວນຫຼຸດຕາມຈຳນວນ'; break;
                        case 'amount_discount': echo 'ສ່ວນຫຼຸດຕາມຍອດຊື້'; break;
                        case 'buy_x_get_y': echo 'ຊື້ X ແຖມ Y'; break;
                        default: echo 'ສ່ວນຫຼຸດທົ່ວໄປ'; break;
                      }
                    ?>
                  </span>
                </td>
                
                <!-- 4. ສ່ວນຫຼຸດ -->
                <td class="text-right font-weight-bold text-danger" style="font-size: 0.92rem;">
                  <?php 
                    if (($p['discount_type'] ?? '') === 'percentage') {
                        $val = floatval($p['discount_value']);
                        echo number_format($val, floor($val) == $val ? 0 : 2) . '%';
                    } else {
                        echo number_format(floatval($p['discount_value']), 0) . ' ₭';
                    }
                  ?>
                </td>
                
                <!-- 5. ວັນທີ (Start Date - End Date) -->
                <td class="text-center font-weight-bold text-muted" style="font-size: 0.8rem; font-family: monospace;">
                  <?php echo date('d/m/Y', strtotime($p['start_date'])); ?> - <?php echo date('d/m/Y', strtotime($p['end_date'])); ?>
                </td>
                
                <!-- 6. ເງື່ອນໄຂ -->
                <td class="text-center small" style="white-space: nowrap;">
                  <?php
                    $conds = [];
                    if (!empty($p['min_qty']) && $p['min_qty'] > 0) {
                        $conds[] = '<span class="font-weight-bold text-dark">' . number_format($p['min_qty'], 0) . ' ຊິ້ນ</span>';
                    }
                    if (!empty($p['min_amount']) && $p['min_amount'] > 0) {
                        $conds[] = '<span class="text-primary font-weight-bold">' . number_format($p['min_amount'], 0) . ' ₭</span>';
                    }
                    if (!empty($conds)) {
                        echo implode(' , ', $conds);
                    } else {
                        echo '<span class="text-muted">ບໍ່ມີເງື່ອນໄຂ</span>';
                    }
                  ?>
                </td>
                
                <!-- 7. ສິນຄ້າ/ປະເພດ -->
                <td>
                  <span class="badge badge-primary font-weight-bold px-2 py-1" style="font-size: 0.76rem; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                    <i class="fas fa-box-open mr-1"></i> <?php echo htmlspecialchars($p['target_name'] ?? 'ທຸກສິນຄ້າ'); ?>
                  </span>
                </td>
                
                <!-- 8. ສະຖານະ (Toggle Switch - 48x20px, no hover scale) -->
                <td class="text-center" style="white-space: nowrap;">
                  <form action="" method="POST" style="display:inline-block; vertical-align: middle;">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="status" value="<?php echo $p['status'] ? 0 : 1; ?>">
                    <button type="submit" class="btn btn-link p-0 border-0 shadow-none align-middle" style="outline: none; text-decoration: none; cursor: pointer; transform: none !important;" title="<?php echo $p['status'] ? 'ປິດໃຊ້ງານ' : 'ເປີດໃຊ້ງານ'; ?>">
                      <div style="width: 48px; height: 20px; background: <?php echo $p['status'] ? '#10b981' : '#cbd5e1'; ?>; border-radius: 20px; position: relative; transition: background-color 0.2s ease-in-out; display: inline-block; vertical-align: middle;">
                        <div style="width: 14px; height: 14px; background: #ffffff; border-radius: 50%; position: absolute; top: 3px; <?php echo $p['status'] ? 'right: 3px;' : 'left: 3px;'; ?> transition: all 0.2s ease-in-out; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>
                      </div>
                    </button>
                  </form>
                </td>
                
                <!-- 9. ຈັດການ -->
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm px-2" title="ແກ້ໄຂ"
                            onclick="editPromo(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form action="" method="POST" onsubmit="return confirm('ຕ້ອງການລົບໂປຣໂມຊັ່ນນີ້ແທ້ຫຼືບໍ່?');" style="display:inline;">
                      <input type="hidden" name="action" value="delete_promo">
                      <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                      <button type="submit" class="btn btn-outline-danger btn-sm px-2" title="ລຶບ"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  </div>
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
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <form action="" method="POST">
        <input type="hidden" name="action" value="add_promo">
        <div class="modal-header bg-primary text-white py-3 px-4">
          <h5 class="modal-title font-weight-bold" style="font-size: 1rem;">
            <i class="fas fa-plus-circle mr-2"></i> ສ້າງໂປຣໂມຊັ່ນໃໝ່
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-4">
          <div class="row">
            <div class="col-md-7 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຊື່ໂປຣ: <span class="text-danger">*</span></label>
              <input type="text" name="promo_name" class="form-control" placeholder="ເຊັ່ນ: ຫຼຸດພິເສດທ້າຍປີ, ສ່ວນຫຼຸດ 10%..." required style="height: 42px;">
            </div>
            <div class="col-md-5 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ປະເພດ:</label>
              <select name="promo_type" id="add_promo_type" class="form-control" style="height: 42px;" onchange="togglePromoType(this.value, 'add')">
                <option value="discount">ສ່ວນຫຼຸດທົ່ວໄປ (Discount)</option>
                <option value="qty_discount">ສ່ວນຫຼຸດຕາມຈຳນວນຊື້ (Quantity)</option>
                <option value="amount_discount">ສ່ວນຫຼຸດຕາມຍອດຊື້ (Amount)</option>
                <option value="buy_x_get_y">ຊື້ X ແຖມ Y / ແຖມສິນຄ້າ (Free Gift)</option>
              </select>
            </div>
          </div>

          <div class="row" id="add_gift_section" style="display: none;">
            <div class="col-md-8 mb-3">
              <label class="font-weight-bold text-success small mb-1"><i class="fas fa-gift mr-1"></i> ຊື່ສິນຄ້າທີ່ແຖມ (Free Gift Item):</label>
              <input type="text" name="gift_product_name" id="add_gift_product_name" class="form-control border-success" list="product_datalist" placeholder="ເຊັ່ນ: ນ້ຳກ້ອນ, ເຄື່ອງດື່ມ..." style="height: 42px;">
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold text-success small mb-1">ຈຳນວນແຖມ (ຊິ້ນ):</label>
              <input type="number" name="gift_qty" id="add_gift_qty" class="form-control text-right border-success" value="1" min="1" style="height: 42px;">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ປະເພດສ່ວນຫຼຸດ%:</label>
              <select name="discount_type" class="form-control" style="height: 42px;">
                <option value="percentage">ເປີເຊັນ (%)</option>
                <option value="fixed">ຈຳນວນເງິນ (₭)</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ມູນຄ່າສ່ວນຫຼຸດ: <span class="text-danger">*</span></label>
              <input type="text" inputmode="decimal" name="discount_value" class="form-control font-weight-bold text-danger text-right" placeholder="0" required style="height: 42px;" oninput="formatNumberInput(this)">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ເລີ່ມວັນທີ:</label>
              <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required style="height: 42px;">
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຫາ ວັນທີ:</label>
              <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required style="height: 42px;">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຈຳນວນ ຂັ້ນຕ່ຳ (ຊິ້ນ):</label>
              <input type="text" inputmode="numeric" name="min_qty" class="form-control text-right" placeholder="0" value="0" style="height: 42px;" oninput="formatNumberInput(this)">
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຍອດເງິນຂັ້ນຕ່ຳ (₭):</label>
              <input type="text" inputmode="decimal" name="min_amount" class="form-control text-right" placeholder="0" value="0" style="height: 42px;" oninput="formatNumberInput(this)">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ເປົ້າໝາຍໂປຣ:</label>
              <select name="target_type" id="add_target_type" class="form-control" style="height: 42px;" onchange="toggleTargetInput(this.value, 'add')">
                <option value="all">ທຸກສິນຄ້າ (All Products)</option>
                <option value="category">ສະເພາະ ໝວດໝູ່ສິນຄ້າ</option>
                <option value="product">ສະເພາະ ສິນຄ້າ</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຊື່ ສິນຄ້າ/ປະເພດ:</label>
              <input type="text" name="target_name" id="add_target_name" class="form-control" value="ທຸກສິນຄ້າ" placeholder="ເຊັ່ນ: ເບຍລາວ..." style="height: 42px;">
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold text-primary small mb-1"><i class="fas fa-boxes mr-1"></i> ສະເພາະ ຫົວໜ່ວຍ:</label>
              <input type="text" name="target_unit_name" id="add_target_unit_name" class="form-control border-primary" value="all" list="units_datalist" placeholder="ທຸກຫົວໜ່ວຍ (ຫຼື: ລັງ, ແກ້ວ, ເເກັດ...)" style="height: 42px;">
            </div>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark small mb-1">ສະຖານະ:</label>
            <select name="status" class="form-control" style="height: 42px;">
              <option value="1">ເປີດໃຊ້ງານ (Active)</option>
              <option value="0">ປິດໃຊ້ງານ (Inactive)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pb-4 px-4 pt-0">
          <button type="button" class="btn btn-light font-weight-bold px-3" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4">ບັນທຶກໂປຣໂມຊັ່ນ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Promo -->
<div class="modal fade" id="editPromoModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <form action="" method="POST">
        <input type="hidden" name="action" value="edit_promo">
        <input type="hidden" name="id" id="edit_promo_id" value="">
        <div class="modal-header bg-primary text-white py-3 px-4">
          <h5 class="modal-title font-weight-bold" style="font-size: 1rem;">
            <i class="fas fa-edit mr-2"></i> ແກ້ໄຂຂໍ້ມູນໂປຣໂມຊັ່ນ
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-4">
          <div class="row">
            <div class="col-md-7 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຊື່ໂປຣ: <span class="text-danger">*</span></label>
              <input type="text" name="promo_name" id="edit_promo_name" class="form-control" required style="height: 42px;">
            </div>
            <div class="col-md-5 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ປະເພດ:</label>
              <select name="promo_type" id="edit_promo_type" class="form-control" style="height: 42px;" onchange="togglePromoType(this.value, 'edit')">
                <option value="discount">ສ່ວນຫຼຸດທົ່ວໄປ (Discount)</option>
                <option value="qty_discount">ສ່ວນຫຼຸດຕາມຈຳນວນຊື້ (Quantity)</option>
                <option value="amount_discount">ສ່ວນຫຼຸດຕາມຍອດຊື້ (Amount)</option>
                <option value="buy_x_get_y">ຊື້ X ແຖມ Y / ແຖມສິນຄ້າ (Free Gift)</option>
              </select>
            </div>
          </div>

          <div class="row" id="edit_gift_section" style="display: none;">
            <div class="col-md-8 mb-3">
              <label class="font-weight-bold text-success small mb-1"><i class="fas fa-gift mr-1"></i> ຊື່ສິນຄ້າທີ່ແຖມ (Free Gift Item):</label>
              <input type="text" name="gift_product_name" id="edit_gift_product_name" class="form-control border-success" list="product_datalist" placeholder="ເຊັ່ນ: ນ້ຳກ້ອນ, ເຄື່ອງດື່ມ..." style="height: 42px;">
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold text-success small mb-1">ຈຳນວນແຖມ (ຊິ້ນ):</label>
              <input type="number" name="gift_qty" id="edit_gift_qty" class="form-control text-right border-success" value="1" min="1" style="height: 42px;">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ປະເພດສ່ວນຫຼຸດ%:</label>
              <select name="discount_type" id="edit_discount_type" class="form-control" style="height: 42px;">
                <option value="percentage">ເປີເຊັນ (%)</option>
                <option value="fixed">ຈຳນວນເງິນ (₭)</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ມູນຄ່າສ່ວນຫຼຸດ: <span class="text-danger">*</span></label>
              <input type="text" inputmode="decimal" name="discount_value" id="edit_discount_value" class="form-control font-weight-bold text-danger text-right" required style="height: 42px;" oninput="formatNumberInput(this)">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ເລີ່ມວັນທີ:</label>
              <input type="date" name="start_date" id="edit_start_date" class="form-control" required style="height: 42px;">
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຫາ ວັນທີ:</label>
              <input type="date" name="end_date" id="edit_end_date" class="form-control" required style="height: 42px;">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຈຳນວນ ຂັ້ນຕ່ຳ (ຊິ້ນ):</label>
              <input type="text" inputmode="numeric" name="min_qty" id="edit_min_qty" class="form-control text-right" style="height: 42px;" oninput="formatNumberInput(this)">
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຍອດເງິນຂັ້ນຕ່ຳ (₭):</label>
              <input type="text" inputmode="decimal" name="min_amount" id="edit_min_amount" class="form-control text-right" style="height: 42px;" oninput="formatNumberInput(this)">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ເປົ້າໝາຍໂປຣ:</label>
              <select name="target_type" id="edit_target_type" class="form-control" style="height: 42px;" onchange="toggleTargetInput(this.value, 'edit')">
                <option value="all">ທຸກສິນຄ້າ (All Products)</option>
                <option value="category">ສະເພາະ ໝວດໝູ່ສິນຄ້າ</option>
                <option value="product">ສະເພາະ ສິນຄ້າ</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຊື່ ສິນຄ້າ/ປະເພດ:</label>
              <input type="text" name="target_name" id="edit_target_name" class="form-control" style="height: 42px;">
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold text-primary small mb-1"><i class="fas fa-boxes mr-1"></i> ສະເພາະ ຫົວໜ່ວຍ:</label>
              <input type="text" name="target_unit_name" id="edit_target_unit_name" class="form-control border-primary" value="all" list="units_datalist" placeholder="ທຸກຫົວໜ່ວຍ (ຫຼື: ລັງ, ແກ້ວ, ເເກັດ...)" style="height: 42px;">
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pb-4 px-4 pt-0">
          <button type="button" class="btn btn-light font-weight-bold px-3" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4">ບັນທຶກການແກ້ໄຂ</button>
        </div>
      </form>
    </div>
  </div>
<datalist id="category_datalist">
  <?php foreach ($categories as $cat): ?>
    <option value="<?php echo htmlspecialchars($cat); ?>"></option>
  <?php endforeach; ?>
</datalist>

<datalist id="product_datalist">
  <?php foreach ($productsList as $prod): ?>
    <option value="<?php echo htmlspecialchars($prod); ?>"></option>
  <?php endforeach; ?>
</datalist>

<script>
function formatNumberInput(input) {
  let val = input.value.replace(/[^0-9.]/g, '');
  if (val === '') {
    input.value = '';
    return;
  }
  let parts = val.split('.');
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  if (parts.length > 2) {
    parts = [parts[0], parts.slice(1).join('')];
  }
  input.value = parts.join('.');
}

function formatNumVal(val) {
  if (val === null || val === undefined || val === '') return '0';
  let num = Number(val);
  if (isNaN(num) || num === 0) return '0';
  return num.toLocaleString('en-US');
}

function togglePromoType(val, mode) {
  var giftSec = document.getElementById(mode + '_gift_section');
  if (giftSec) {
    giftSec.style.display = (val === 'buy_x_get_y') ? 'flex' : 'none';
  }
}

function toggleTargetInput(val, mode) {
  var inputEl = document.getElementById(mode + '_target_name');
  if (val === 'all') {
    inputEl.value = 'ທຸກສິນຄ້າ';
    inputEl.removeAttribute('list');
  } else if (val === 'category') {
    if (inputEl.value === 'ທຸກສິນຄ້າ') inputEl.value = '';
    inputEl.setAttribute('list', 'category_datalist');
  } else if (val === 'product') {
    if (inputEl.value === 'ທຸກສິນຄ້າ') inputEl.value = '';
    inputEl.setAttribute('list', 'product_datalist');
  }
}

function editPromo(p) {
  document.getElementById('edit_promo_id').value = p.id;
  document.getElementById('edit_promo_name').value = p.promo_name || '';
  document.getElementById('edit_promo_type').value = p.promo_type || 'discount';
  document.getElementById('edit_discount_type').value = p.discount_type || 'percentage';
  document.getElementById('edit_discount_value').value = formatNumVal(p.discount_value);
  if (document.getElementById('edit_gift_product_name')) {
    document.getElementById('edit_gift_product_name').value = p.gift_product_name || '';
  }
  if (document.getElementById('edit_gift_qty')) {
    document.getElementById('edit_gift_qty').value = p.gift_qty || 1;
  }
  document.getElementById('edit_start_date').value = p.start_date || '';
  document.getElementById('edit_end_date').value = p.end_date || '';
  document.getElementById('edit_min_qty').value = formatNumVal(p.min_qty);
  document.getElementById('edit_min_amount').value = formatNumVal(p.min_amount);
  document.getElementById('edit_target_type').value = p.target_type || 'all';
  document.getElementById('edit_target_name').value = p.target_name || 'ທຸກສິນຄ້າ';
  if (document.getElementById('edit_target_unit_name')) {
    document.getElementById('edit_target_unit_name').value = p.target_unit_name || 'all';
  }
  toggleTargetInput(p.target_type || 'all', 'edit');
  togglePromoType(p.promo_type || 'discount', 'edit');
  $('#editPromoModal').modal('show');
}
</script>

<datalist id="units_datalist">
  <option value="all">ທຸກຫົວໜ່ວຍ (All Units)</option>
  <option value="ລັງ">ລັງ (Carton)</option>
  <option value="ແກ້ວ">ແກ້ວ (Bottle)</option>
  <option value="ເເກັດ">ເເກັດ (Crate)</option>
  <option value="ປ໋ອງ">ປ໋ອງ (Can)</option>
  <option value="ແພັກ">ແພັກ (Pack)</option>
</datalist>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
