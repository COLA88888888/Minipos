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

// Handle POST save company info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_company') {
    $com_name_la = trim($_POST['com_name_la'] ?? '');
    $com_address = trim($_POST['com_address'] ?? '');
    $com_tel     = trim($_POST['com_tel'] ?? '');
    $com_email   = trim($_POST['com_email'] ?? '');
    $barcode     = trim($_POST['barcode'] ?? 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!');

    if ($com_name_la !== '') {
        try {
            // Ensure barcode column exists in tbcompanyinfo
            try {
                $pdo->exec("ALTER TABLE tbcompanyinfo ADD COLUMN barcode VARCHAR(255) DEFAULT 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!'");
            } catch (Throwable $e) {}

            // Check if logo is uploaded
            $img_sql = "";
            $params = [$com_name_la, $com_address, $com_tel, $com_email, $barcode];

            if (isset($_FILES['logo_img']) && !empty($_FILES['logo_img']['name']) && $_FILES['logo_img']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['logo_img']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $newLogoName = 'logo_' . time() . '.' . $ext;
                    $targetDir = __DIR__ . '/../../assets/img/logo/';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    if (move_uploaded_file($_FILES['logo_img']['tmp_name'], $targetDir . $newLogoName)) {
                        $img_sql = ", img_url = ?";
                        $params[] = $newLogoName;
                    }
                }
            }

            // Check if record exists
            $cnt = $pdo->query("SELECT COUNT(*) FROM tbcompanyinfo")->fetchColumn();
            if ($cnt > 0) {
                $stmt = $pdo->prepare("UPDATE tbcompanyinfo SET com_name_la = ?, com_address = ?, com_tel = ?, com_email = ?, barcode = ? $img_sql WHERE Id = 1");
                $stmt->execute($params);
            } else {
                $stmt = $pdo->prepare("INSERT INTO tbcompanyinfo (Id, com_name_la, com_address, com_tel, com_email, barcode, img_url, branch_id) VALUES (1, ?, ?, ?, ?, ?, 'logo.png', 1)");
                $stmt->execute([$com_name_la, $com_address, $com_tel, $com_email, $barcode]);
            }

            $message = 'ບັນທຶກຂໍ້ມູນຮ້ານຄ້າສຳເລັດ!';
            $message_type = 'success';
            logActivity($pdo, "ແກ້ໄຂຂໍ້ມູນຮ້ານຄ້າ", $com_name_la);
        } catch (Exception $e) {
            $message = 'ຜິດພາດ: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Fetch current company info
$company = $pdo->query("SELECT * FROM tbcompanyinfo LIMIT 1")->fetch();
if (!$company) {
    $company = [
        'com_name_la' => 'ຮ້ານ Corner Retail',
        'com_address' => 'ນະຄອນຫຼວງວຽງຈັນ',
        'com_tel' => '020 xxxxxxxx',
        'com_email' => 'contact@cornerretail.la',
        'img_url' => 'logo.png'
    ];
}

$logoUrl = $base_path . 'assets/img/logo/' . (!empty($company['img_url']) ? $company['img_url'] : 'logo.png');

require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container p-4" style="max-width: 780px;">
  <div class="row mb-3 align-items-center">
    <div class="col-12">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-store-alt text-primary mr-2"></i> ຂໍ້ມູນຮ້ານຄ້າ (Store Information & Profile)
      </h5>
      <p class="text-muted small mb-0">ກຳນົດຊື່ຮ້ານ, ໂລໂກ້, ທີ່ຢູ່, ແລະ ເບີໂທ ເພື່ອສະແດງໃນຫົວໃບບິນຮັບເງິນ POS</p>
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

  <div class="card border-0 shadow-sm p-4" style="border-radius: 14px; border: 1px solid #e2e8f0; background: #fff;">
    <form action="" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="save_company">

      <div class="row align-items-center mb-4 pb-3 border-bottom">
        <div class="col-md-4 text-center">
          <div class="position-relative d-inline-block">
            <img id="logo_preview" src="<?php echo htmlspecialchars($logoUrl); ?>" 
                 style="width: 120px; height: 120px; object-fit: contain; border-radius: 50%; border: 2px solid #e2e8f0; background: #f8fafc; padding: 4px;"
                 onerror="this.src='<?php echo $base_path; ?>assets/img/logo/logo.png';">
            <button type="button" class="btn btn-sm btn-primary position-absolute shadow-sm" 
                    style="right: 0; bottom: 0; border-radius: 50%; width: 34px; height: 34px;" 
                    onclick="document.getElementById('logo_input').click();">
              <i class="fas fa-camera"></i>
            </button>
          </div>
          <small class="d-block text-muted mt-2 font-weight-bold">ໂລໂກ້ຮ້ານຄ້າ</small>
          <input type="file" name="logo_img" id="logo_input" accept="image/*" style="display: none;" onchange="previewLogo(this)">
        </div>

        <div class="col-md-8">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ຊື່ຮ້ານຄ້າ / ບໍລິສັດ: <span class="text-danger">*</span></label>
            <input type="text" name="com_name_la" class="form-control font-weight-bold text-primary" value="<?php echo htmlspecialchars($company['com_name_la'] ?? ''); ?>" required>
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark small mb-1">ເບີໂທລະສັບຕິດຕໍ່: <span class="text-danger">*</span></label>
            <input type="text" name="com_tel" class="form-control" value="<?php echo htmlspecialchars($company['com_tel'] ?? ''); ?>" required>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-12 mb-3">
          <label class="font-weight-bold text-dark small mb-1">ທີ່ຢູ່ຮ້ານຄ້າ (ສະແດງໃນໃບບິນ):</label>
          <textarea name="com_address" rows="3" class="form-control" placeholder="ບ້ານ, ເມືອງ, ແຂວງ..."><?php echo htmlspecialchars($company['com_address'] ?? ''); ?></textarea>
        </div>
        <div class="col-md-12 mb-3">
          <label class="font-weight-bold text-dark small mb-1">ອີເມວ / ຊ່ອງທາງຕິດຕໍ່ອື່ນໆ:</label>
          <input type="email" name="com_email" class="form-control" value="<?php echo htmlspecialchars($company['com_email'] ?? ''); ?>" placeholder="example@gmail.com">
        </div>
        <div class="col-md-12 mb-3">
          <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-heart text-danger mr-1"></i> ຂໍ້ຄວາມທ້າຍໃບບິນ (ຄຳຂອບໃຈ):</label>
          <input type="text" name="barcode" class="form-control font-weight-bold text-dark" value="<?php echo htmlspecialchars($company['barcode'] ?? 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!'); ?>" placeholder="ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!">
          <small class="text-muted">ຂໍ້ຄວາມນີ້ຈະສະແດງຢູ່ສ່ວນລຸ່ມສຸດຂອງໃບບິນຮັບເງິນ POS</small>
        </div>
      </div>

      <div class="text-right pt-2 border-top">
        <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 8px;">
          <i class="fas fa-save mr-1"></i> ບັນທຶກຂໍ້ມູນຮ້ານຄ້າ
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function previewLogo(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('logo_preview').src = e.target.result;
    }
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
