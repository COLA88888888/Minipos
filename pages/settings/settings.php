<?php
session_start();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../../config/db.php';

// Check if logged in and has access
if (empty($_SESSION['user_id']) || (!hasPermission('setup') && $_SESSION['status'] !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $vat_rate = floatval($_POST['vat_rate'] ?? 0.00);
    $expiry_warning_days = intval($_POST['expiry_warning_days'] ?? 30);
    
    if ($vat_rate >= 0 && $expiry_warning_days > 0) {
        try {
            $pdo->beginTransaction();
            
            // Update VAT rate
            $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'vat_rate'");
            $stmt->execute([$vat_rate]);
            
            // Update Expiry Warning Days
            $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'expiry_warning_days'");
            $stmt->execute([$expiry_warning_days]);
            
            $pdo->commit();
            
            $message = 'ບັນທຶກການຕັ້ງຄ່າລະບົບສຳເລັດ!';
            $message_type = 'success';
            logActivity($pdo, "ແກ້ໄຂການຕັ້ງຄ່າລະບົບ", "VAT: $vat_rate%, ເຕືອນໝົດອາຍຸ: $expiry_warning_days ວັນ");
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'ຜິດພາດ: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Fetch current values
$vat_rate = getSetting($pdo, 'vat_rate', '10');
$expiry_warning_days = getSetting($pdo, 'expiry_warning_days', '30');

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/settings.css?v=<?php echo filemtime(__DIR__ . '/../../themes/settings.css'); ?>">

<div class="container p-4" style="max-width: 600px;">
  <div class="row mb-3">
    <div class="col-12">
      <h3 style="font-family: 'Noto Sans Lao Looped'; color: #1a252f;"><i class="fas fa-cogs mr-2 text-primary"></i> ຕັ້ງຄ່າລະບົບ (System Settings)</h3>
      <p class="text-muted">ກຳນົດຄ່າພາສີມູນຄ່າເພີ່ມ ແລະ ການເຕືອນວັນໝົດອາຍຸຂອງສິນຄ້າ</p>
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

  <div class="card shadow-sm" style="border-radius: 12px; border: none; background: white;">
    <div class="card-header bg-white border-0 py-3">
      <h5 class="card-title fw-bold" style="font-family: 'Noto Sans Lao Looped';"><i class="fas fa-sliders-h text-success"></i> ຕົວເລືອກການຕັ້ງຄ່າ</h5>
    </div>
    <div class="card-body">
      <form action="" method="POST">
        <input type="hidden" name="action" value="save_settings">
        
        <!-- VAT Configuration -->
        <div class="form-group mb-4">
          <label class="font-weight-bold"><i class="fas fa-percent text-info mr-1"></i> ອັດຕາພາສີມູນຄ່າເພີ່ມ (VAT %) <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" step="0.01" name="vat_rate" class="form-control form-control-lg text-primary font-weight-bold" value="<?php echo htmlspecialchars($vat_rate); ?>" min="0" max="100" required>
            <div class="input-group-append">
              <span class="input-group-text font-weight-bold">%</span>
            </div>
          </div>
          <small class="text-muted">ກຳນົດອັດຕາພາສີເພື່ອຄຳນວນໃນໜ້າຈໍຂາຍ POS ແລະ ອອກໃບບິນ</small>
        </div>

        <!-- Expiry Warning Configuration -->
        <div class="form-group mb-4">
          <label class="font-weight-bold"><i class="fas fa-hourglass-half text-warning mr-1"></i> ເຕືອນກ່ອນໝົດອາຍຸ (ວັນ) <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" name="expiry_warning_days" class="form-control form-control-lg text-warning font-weight-bold" value="<?php echo htmlspecialchars($expiry_warning_days); ?>" min="1" required>
            <div class="input-group-append">
              <span class="input-group-text font-weight-bold">ວັນ</span>
            </div>
          </div>
          <small class="text-muted">ລະບົບຈະໝາຍສີສົ້ມເຕືອນໃນໜ້າກວດສອບ ຫາກສິນຄ້າໃກ້ໝົດອາຍຸພາຍໃນຈຳນວນວັນນີ້</small>
        </div>

        <button type="submit" class="btn btn-success btn-lg btn-block font-weight-bold py-3"><i class="fas fa-save mr-2"></i> ບັນທຶກການຕັ້ງຄ່າ</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
