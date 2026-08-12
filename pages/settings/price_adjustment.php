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

// Handle POST price adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'adjust_price') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $new_bprice = floatval(str_replace(',', '', $_POST['new_bprice'] ?? '0'));
    $new_price  = floatval(str_replace(',', '', $_POST['new_price'] ?? '0'));
    $remark     = trim($_POST['remark'] ?? '');
    $username   = $_SESSION['username'] ?? 'admin';
    $user_id    = $_SESSION['user_id'] ?? 1;

    if ($product_id > 0 && $new_price > 0) {
        try {
            $pdo->beginTransaction();

            // Fetch old price
            $cur = $pdo->prepare("SELECT product_name, bprice, price FROM products WHERE product_id = ?");
            $cur->execute([$product_id]);
            $prod = $cur->fetch();

            if ($prod) {
                // Update product table
                $upd = $pdo->prepare("UPDATE products SET bprice = ?, price = ? WHERE product_id = ?");
                $upd->execute([$new_bprice, $new_price, $product_id]);

                // Log into price_adjustments
                $stmt = $pdo->prepare("INSERT INTO price_adjustments (adjust_date, adjust_time, adjust_mode, adjust_type, price_type, username, user_id, remark, branch_id) VALUES (CURDATE(), CURTIME(), 'Single', 'Direct', 'Standard', ?, ?, ?, 1)");
                $stmt->execute([$username, $user_id, "ສິນຄ້າ: {$prod['product_name']} (ID: $product_id) | ລາຄາເກົ່າ: {$prod['price']} ➔ ລາຄາໃໝ່: $new_price | ເຫດຜົນ: $remark"]);

                $pdo->commit();
                $message = "ປັບລາຄາສິນຄ້າ '{$prod['product_name']}' ສຳເລັດແລ້ວ!";
                $message_type = 'success';
                logActivity($pdo, "ປັບລາຄາສິນຄ້າ", "ID: $product_id, ລາຄາໃໝ່: $new_price");
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'ຜິດພາດ: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Fetch products & recent adjustments
$products = $pdo->query("SELECT product_id, product_name, barcode, bprice, price, unit FROM products ORDER BY product_name ASC")->fetchAll();
$adjustments = $pdo->query("SELECT * FROM price_adjustments ORDER BY adjust_id DESC LIMIT 30")->fetchAll();

require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid p-4">
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-tags text-warning mr-2"></i> ປັບລາຄາສິນຄ້າ (Price Adjustment)
      </h5>
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

  <div class="row">
    <!-- Form Card (Left) -->
    <div class="col-lg-5 mb-4">
      <div class="card border-0 shadow-sm p-4" style="border-radius: 14px; border: 1px solid #e2e8f0;">
        <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
          <i class="fas fa-edit text-warning mr-1"></i> ຟອມປັບປຸງລາຄາສິນຄ້າ
        </h6>

        <form action="" method="POST">
          <input type="hidden" name="action" value="adjust_price">

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ເລືອກສິນຄ້າ: <span class="text-danger">*</span></label>
            <select id="adj_product_select" name="product_id" class="form-control" onchange="onSelectAdjProduct(this)" required>
              <option value="">-- ເລືອກສິນຄ້າ --</option>
              <?php foreach ($products as $p): ?>
                <option value="<?php echo $p['product_id']; ?>" 
                        data-bprice="<?php echo $p['bprice']; ?>" 
                        data-price="<?php echo $p['price']; ?>"
                        data-unit="<?php echo htmlspecialchars($p['unit'] ?? ''); ?>">
                  <?php echo htmlspecialchars($p['product_name']); ?> (<?php echo $p['product_id']; ?>) - <?php echo number_format($p['price'], 0); ?> ₭
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-6 mb-3">
              <label class="font-weight-bold text-muted small mb-1">ລາຄາຊື້ເກົ່າ:</label>
              <input type="text" id="cur_bprice" class="form-control bg-light text-right" readonly placeholder="0 ₭">
            </div>
            <div class="col-6 mb-3">
              <label class="font-weight-bold text-muted small mb-1">ລາຄາຂາຍເກົ່າ:</label>
              <input type="text" id="cur_price" class="form-control bg-light text-right font-weight-bold text-success" readonly placeholder="0 ₭">
            </div>
          </div>

          <div class="row">
            <div class="col-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ລາຄາຊື້ໃໝ່ (ທຶນ):</label>
              <div class="input-group">
                <input type="text" name="new_bprice" id="new_bprice" class="form-control text-right font-weight-bold" placeholder="0" oninput="formatPriceInput(this)" required>
                <div class="input-group-append"><span class="input-group-text">₭</span></div>
              </div>
            </div>
            <div class="col-6 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ລາຄາຂາຍໃໝ່: <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="text" name="new_price" id="new_price" class="form-control text-right font-weight-bold text-warning" placeholder="0" oninput="formatPriceInput(this)" required>
                <div class="input-group-append"><span class="input-group-text">₭</span></div>
              </div>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ເຫດຜົນ / ໝາຍເຫດ:</label>
            <input type="text" name="remark" class="form-control" placeholder="ເຊັ່ນ: ຕົ້ນທຶນຂຶ້ນ, ປັບຕາມຕະຫຼາດ...">
          </div>

          <button type="submit" class="btn btn-warning btn-block font-weight-bold text-white py-2 shadow-sm" style="border-radius: 8px;">
            <i class="fas fa-save mr-1"></i> ຢືນຢັນການປັບລາຄາ
          </button>
        </form>
      </div>
    </div>

    <!-- History Card (Right) -->
    <div class="col-lg-7 mb-4">
      <div class="card border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
        <div class="card-header bg-white py-3 px-4 border-bottom">
          <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-history mr-2 text-muted"></i> ປະຫວັດການປັບລາຄາ</h6>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="bg-light text-muted small font-weight-bold">
              <tr>
                <th class="text-center" style="width: 60px;">ລ/ດ</th>
                <th>ວັນທີ & ເວລາ</th>
                <th>ລາຍລະອຽດ</th>
                <th>ຜູ້ດຳເນີນການ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($adjustments)): ?>
                <tr><td colspan="4" class="text-center py-5 text-muted">ບໍ່ມີປະຫວັດການປັບລາຄາ</td></tr>
              <?php else: ?>
                <?php $i = 1; foreach ($adjustments as $adj): ?>
                  <tr>
                    <td class="text-center text-muted"><?php echo $i++; ?></td>
                    <td class="small text-muted" style="white-space: nowrap;"><?php echo htmlspecialchars($adj['adjust_date'] . ' ' . $adj['adjust_time']); ?></td>
                    <td><small class="font-weight-bold text-dark"><?php echo htmlspecialchars($adj['remark'] ?? '-'); ?></small></td>
                    <td class="small text-muted"><?php echo htmlspecialchars($adj['username'] ?? 'admin'); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function onSelectAdjProduct(select) {
  var opt = select.options[select.selectedIndex];
  if (opt && opt.value) {
    var bprice = opt.getAttribute('data-bprice') || '0';
    var price = opt.getAttribute('data-price') || '0';
    $('#cur_bprice').val(Number(bprice).toLocaleString() + ' ₭');
    $('#cur_price').val(Number(price).toLocaleString() + ' ₭');
    $('#new_bprice').val(Number(bprice).toLocaleString());
    $('#new_price').val(Number(price).toLocaleString());
  } else {
    $('#cur_bprice').val('');
    $('#cur_price').val('');
    $('#new_bprice').val('');
    $('#new_price').val('');
  }
}

function formatPriceInput(input) {
  var val = input.value.replace(/\D/g, '');
  input.value = val === '' ? '' : Number(val).toLocaleString('en-US');
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
