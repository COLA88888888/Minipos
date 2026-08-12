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
    if ($_POST['action'] === 'save_rate') {
        $thb_rate = floatval(str_replace(',', '', $_POST['ex_kip_bath'] ?? '0'));
        $usd_rate = floatval(str_replace(',', '', $_POST['ex_kip_us'] ?? '0'));
        $username = $_SESSION['username'] ?? 'admin';

        if ($thb_rate > 0 && $usd_rate > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO tbexchange (ex_date, ex_time, ex_kip_bath, ex_kip_us, ex_status, ex_userlogin, branch_id) VALUES (CURDATE(), CURTIME(), ?, ?, 'Active', ?, 1)");
                $stmt->execute([$thb_rate, $usd_rate, $username]);
                $message = 'ອັບເດດອັດຕາແລກປ່ຽນເງິນສຳເລັດແລ້ວ!';
                $message_type = 'success';
                logActivity($pdo, "ອັບເດດອັດຕາແລກປ່ຽນ", "THB: $thb_rate ₭, USD: $usd_rate ₭");
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        } else {
            $message = 'ກະລຸນາປ້ອນອັດຕາແລກປ່ຽນທີ່ຖືກຕ້ອງ!';
            $message_type = 'warning';
        }
    }
}

// Fetch latest rate
$latestRate = $pdo->query("SELECT * FROM tbexchange ORDER BY Id DESC LIMIT 1")->fetch();
$historyRates = $pdo->query("SELECT * FROM tbexchange ORDER BY Id DESC LIMIT 30")->fetchAll();

require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid p-4">
  <!-- Header -->
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-exchange-alt text-success mr-2"></i> ຕັ້ງຄ່າອັດຕາແລກປ່ຽນເງິນ (Exchange Rates)
      </h5>
    </div>
    <div class="col-sm-6 text-right">
      <button type="button" class="btn btn-success px-3 font-weight-bold" data-toggle="modal" data-target="#rateModal" style="border-radius: 6px;">
        <i class="fas fa-plus-circle mr-1"></i> ອັບເດດອັດຕາໃໝ່
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

  <!-- Summary Cards -->
  <div class="row mb-4">
    <div class="col-md-6 mb-3">
      <div class="card border-0 shadow-sm p-4 text-white" style="border-radius: 14px; background: linear-gradient(135deg, #10b981, #059669);">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="text-white-50 small font-weight-bold text-uppercase">1 ບາດໄທ (THB ➔ LAK)</span>
            <h3 class="font-weight-bold my-1"><?php echo $latestRate ? number_format(floatval($latestRate['ex_kip_bath']), 0) : '0'; ?> <small style="font-size: 1.1rem;">₭</small></h3>
            <small class="text-white-50">ອັບເດດລ່າສຸດ: <?php echo $latestRate ? ($latestRate['ex_date'] . ' ' . $latestRate['ex_time']) : '-'; ?></small>
          </div>
          <div style="font-size: 2.5rem; opacity: 0.3;"><i class="fas fa-coins"></i></div>
        </div>
      </div>
    </div>
    <div class="col-md-6 mb-3">
      <div class="card border-0 shadow-sm p-4 text-white" style="border-radius: 14px; background: linear-gradient(135deg, #2563eb, #1d4ed8);">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="text-white-50 small font-weight-bold text-uppercase">1 ໂດລາສະຫະລັດ (USD ➔ LAK)</span>
            <h3 class="font-weight-bold my-1"><?php echo $latestRate ? number_format(floatval($latestRate['ex_kip_us']), 0) : '0'; ?> <small style="font-size: 1.1rem;">₭</small></h3>
            <small class="text-white-50">ອັບເດດລ່າສຸດ: <?php echo $latestRate ? ($latestRate['ex_date'] . ' ' . $latestRate['ex_time']) : '-'; ?></small>
          </div>
          <div style="font-size: 2.5rem; opacity: 0.3;"><i class="fas fa-dollar-sign"></i></div>
        </div>
      </div>
    </div>
  </div>

  <!-- History Table -->
  <div class="card border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
    <div class="card-header bg-white py-3 px-4 border-bottom">
      <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-history mr-2 text-muted"></i> ປະຫວັດການປ່ຽນແປງອັດຕາແລກປ່ຽນ</h6>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="bg-light text-muted small font-weight-bold">
          <tr>
            <th class="text-center" style="width: 70px;">ລ/ດ</th>
            <th>ວັນທີ & ເວລາ</th>
            <th class="text-right">1 THB (ບາດ)</th>
            <th class="text-right">1 USD (ໂດລາ)</th>
            <th class="text-center">ສະຖານະ</th>
            <th>ຜູ້ບັນທຶກ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($historyRates)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">ບໍ່ມີປະຫວັດອັດຕາແລກປ່ຽນ</td></tr>
          <?php else: ?>
            <?php $i = 1; foreach ($historyRates as $r): ?>
              <tr>
                <td class="text-center text-muted"><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($r['ex_date'] . ' ' . $r['ex_time']); ?></td>
                <td class="text-right font-weight-bold text-success"><?php echo number_format(floatval($r['ex_kip_bath']), 0); ?> ₭</td>
                <td class="text-right font-weight-bold text-primary"><?php echo number_format(floatval($r['ex_kip_us']), 0); ?> ₭</td>
                <td class="text-center">
                  <span class="badge badge-success px-2 py-1"><?php echo htmlspecialchars($r['ex_status'] ?? 'Active'); ?></span>
                </td>
                <td class="text-muted small"><?php echo htmlspecialchars($r['ex_userlogin'] ?? 'admin'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Update Exchange Rate -->
<div class="modal fade" id="rateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <form action="" method="POST">
        <input type="hidden" name="action" value="save_rate">
        <div class="modal-header bg-success text-white py-3 px-4">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao Looped';">
            <i class="fas fa-plus-circle mr-2"></i> ອັບເດດອັດຕາແລກປ່ຽນໃໝ່
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">1 ບາດໄທ (THB ➔ LAK): <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" name="ex_kip_bath" class="form-control text-right font-weight-bold text-success" placeholder="0" value="<?php echo $latestRate ? number_format(floatval($latestRate['ex_kip_bath']), 0) : '720'; ?>" oninput="formatPriceInput(this)" required>
              <div class="input-group-append"><span class="input-group-text">₭</span></div>
            </div>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">1 ໂດລາສະຫະລັດ (USD ➔ LAK): <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" name="ex_kip_us" class="form-control text-right font-weight-bold text-primary" placeholder="0" value="<?php echo $latestRate ? number_format(floatval($latestRate['ex_kip_us']), 0) : '22000'; ?>" oninput="formatPriceInput(this)" required>
              <div class="input-group-append"><span class="input-group-text">₭</span></div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pb-4 px-4 pt-0">
          <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-success font-weight-bold px-4">ບັນທຶກອັດຕາໃໝ່</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function formatPriceInput(input) {
  var val = input.value.replace(/\D/g, '');
  input.value = val === '' ? '' : Number(val).toLocaleString('en-US');
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
