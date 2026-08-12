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
    if ($_POST['action'] === 'add_printer') {
        $name = trim($_POST['name'] ?? '');
        $ip_address = trim($_POST['ip_address'] ?? '');
        $type = $_POST['type'] ?? 'browser';

        if ($name !== '') {
            try {
                $stmt = $pdo->prepare("INSERT INTO tbl_printer (name, ip_address, type, status, branch_id) VALUES (?, ?, ?, 1, 1)");
                $stmt->execute([$name, $ip_address, $type]);
                $message = 'ເພີ່ມເຄື່ອງພິມໃໝ່ສຳເລັດ!';
                $message_type = 'success';
                logActivity($pdo, "ເພີ່ມເຄື່ອງພິມ", "$name ($type)");
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif ($_POST['action'] === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        $status = intval($_POST['status'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE tbl_printer SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $message = 'ອັບເດດສະຖານະເຄື່ອງພິມສຳເລັດ!';
            $message_type = 'success';
        }
    } elseif ($_POST['action'] === 'delete_printer') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM tbl_printer WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'ລົບເຄື່ອງພິມສຳເລັດ!';
            $message_type = 'success';
        }
    }
}

$printers = $pdo->query("SELECT * FROM tbl_printer ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid p-4">
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-print text-info mr-2"></i> ຕັ້ງຄ່າເຄື່ອງພິມ (Printer Settings)
      </h5>
    </div>
    <div class="col-sm-6 text-right">
      <button type="button" class="btn btn-info px-3 font-weight-bold text-white" data-toggle="modal" data-target="#addPrinterModal" style="border-radius: 6px;">
        <i class="fas fa-plus-circle mr-1"></i> ເພີ່ມເຄື່ອງພິມໃໝ່
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
      <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-list mr-2 text-muted"></i> ລາຍການເຄື່ອງພິມໃນລະບົບ</h6>
      <span class="badge badge-light border text-muted"><?php echo count($printers); ?> ເຄື່ອງ</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="bg-light text-muted small font-weight-bold">
          <tr>
            <th class="text-center" style="width: 70px;">ລ/ດ</th>
            <th>ຊື່ເຄື່ອງພິມ</th>
            <th>ປະເພດການເຊື່ອມຕໍ່</th>
            <th>IP Address / Port</th>
            <th class="text-center">ສະຖານະ</th>
            <th class="text-center" style="width: 140px;">ຈັດການ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($printers)): ?>
            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-print fa-2x mb-2 d-block text-secondary opacity-50"></i>ບໍ່ມີລາຍການເຄື່ອງພິມ</td></tr>
          <?php else: ?>
            <?php $i = 1; foreach ($printers as $pr): ?>
              <tr>
                <td class="text-center text-muted"><?php echo $i++; ?></td>
                <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($pr['name']); ?></td>
                <td>
                  <span class="badge <?php echo $pr['type'] === 'ip' ? 'badge-primary' : 'badge-secondary'; ?> px-2 py-1">
                    <?php echo $pr['type'] === 'ip' ? 'LAN / Network IP' : 'Browser / USB System Print'; ?>
                  </span>
                </td>
                <td class="font-weight-bold" style="font-family: monospace; color: #334155;">
                  <?php echo !empty($pr['ip_address']) ? htmlspecialchars($pr['ip_address']) : '-'; ?>
                </td>
                <td class="text-center">
                  <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="<?php echo $pr['id']; ?>">
                    <input type="hidden" name="status" value="<?php echo $pr['status'] ? 0 : 1; ?>">
                    <button type="submit" class="btn btn-sm <?php echo $pr['status'] ? 'btn-success' : 'btn-secondary'; ?> font-weight-bold" style="border-radius: 20px; font-size: 0.75rem; padding: 2px 10px;">
                      <?php echo $pr['status'] ? '<i class="fas fa-check-circle mr-1"></i> ພ້ອມໃຊ້ງານ' : '<i class="fas fa-times-circle mr-1"></i> ປິດ'; ?>
                    </button>
                  </form>
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-outline-info mr-1" title="ທົດສອບພິມ" onclick="testPrint('<?php echo htmlspecialchars($pr['name']); ?>')">
                    <i class="fas fa-print"></i>
                  </button>
                  <form action="" method="POST" onsubmit="return confirm('ຕ້ອງການລົບເຄື່ອງພິມນີ້ແທ້ຫຼືບໍ່?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete_printer">
                    <input type="hidden" name="id" value="<?php echo $pr['id']; ?>">
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

<!-- Modal: Add Printer -->
<div class="modal fade" id="addPrinterModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <form action="" method="POST">
        <input type="hidden" name="action" value="add_printer">
        <div class="modal-header bg-info text-white py-3 px-4">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao Looped';">
            <i class="fas fa-plus-circle mr-2"></i> ເພີ່ມເຄື່ອງພິມໃໝ່
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ຊື່ເຄື່ອງພິມ: <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="ເຊັ່ນ: ເຄື່ອງພິມໃບບິນໜ້າຮ້ານ, ເຄື່ອງພິມເຮືອນຄົວ..." required>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ປະເພດການເຊື່ອມຕໍ່:</label>
            <select name="type" class="form-control" onchange="toggleIpField(this.value)">
              <option value="browser">Browser / Windows Driver / USB</option>
              <option value="ip">Network LAN / WiFi (IP Address)</option>
            </select>
          </div>
          <div class="form-group mb-3" id="ipFieldGroup" style="display: none;">
            <label class="font-weight-bold text-dark small mb-1">IP Address (ເຊັ່ນ: 192.168.1.200):</label>
            <input type="text" name="ip_address" class="form-control font-weight-bold" placeholder="192.168.1.xxx">
          </div>
        </div>
        <div class="modal-footer border-0 pb-4 px-4 pt-0">
          <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-info text-white font-weight-bold px-4">ບັນທຶກເຄື່ອງພິມ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleIpField(val) {
  if (val === 'ip') {
    $('#ipFieldGroup').slideDown();
  } else {
    $('#ipFieldGroup').slideUp();
  }
}

function testPrint(printerName) {
  Swal.fire({
    title: 'ທົດສອບເຄື່ອງພິມ',
    text: 'ກຳລັງທົດສອບພິມໄປທີ່: ' + printerName,
    icon: 'info',
    showConfirmButton: false,
    timer: 1500
  });
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
