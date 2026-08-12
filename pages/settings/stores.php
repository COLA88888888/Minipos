<?php
session_start();
$base_path = '../../';
require_once __DIR__ . '/../../config/db.php';

// Check authorization
if (empty($_SESSION['user_id']) || (!hasPermission('setup') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// Handle POST Save Store Information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_store_info') {
    $com_name_la = trim($_POST['com_name_la'] ?? '');
    $com_address = trim($_POST['com_address'] ?? '');
    $com_tel     = trim($_POST['com_tel'] ?? '');
    $com_email   = trim($_POST['com_email'] ?? '');
    $receipt_footer = trim($_POST['receipt_footer'] ?? '');

    if ($com_name_la !== '') {
        try {
            $newLogoName = null;
            if (isset($_FILES['logo_img']) && !empty($_FILES['logo_img']['name']) && $_FILES['logo_img']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['logo_img']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $newLogoName = 'logo_' . time() . '.' . $ext;
                    $targetDir = __DIR__ . '/../../assets/img/logo/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    move_uploaded_file($_FILES['logo_img']['tmp_name'], $targetDir . $newLogoName);
                }
            }

            // Check if record exists in tbcompanyinfo
            $cnt = $pdo->query("SELECT COUNT(*) FROM tbcompanyinfo")->fetchColumn();
            if ($cnt > 0) {
                if ($newLogoName) {
                    $stmt = $pdo->prepare("UPDATE tbcompanyinfo SET com_name_la = ?, com_address = ?, com_tel = ?, com_email = ?, barcode = ?, img_url = ? WHERE Id = 1");
                    $stmt->execute([$com_name_la, $com_address, $com_tel, $com_email, $receipt_footer, $newLogoName]);
                } else {
                    $stmt = $pdo->prepare("UPDATE tbcompanyinfo SET com_name_la = ?, com_address = ?, com_tel = ?, com_email = ?, barcode = ? WHERE Id = 1");
                    $stmt->execute([$com_name_la, $com_address, $com_tel, $com_email, $receipt_footer]);
                }
            } else {
                $logo = $newLogoName ? $newLogoName : 'logo.png';
                $stmt = $pdo->prepare("INSERT INTO tbcompanyinfo (Id, com_name_la, com_address, com_tel, com_email, barcode, img_url, branch_id) VALUES (1, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$com_name_la, $com_address, $com_tel, $com_email, $receipt_footer, $logo]);
            }

            // Sync with tbstore if exists
            $storeCnt = $pdo->query("SELECT COUNT(*) FROM tbstore WHERE store_id = 1")->fetchColumn();
            if ($storeCnt > 0) {
                $stmtStore = $pdo->prepare("UPDATE tbstore SET store_name = ?, address = ?, tel = ? WHERE store_id = 1");
                $stmtStore->execute([$com_name_la, $com_address, $com_tel]);
            }

            $message = 'ບັນທຶກຂໍ້ມູນຮ້ານຄ້າສຳເລັດແລ້ວ!';
            $message_type = 'success';
            logActivity($pdo, "ແກ້ໄຂຂໍ້ມູນຮ້ານຄ້າ", $com_name_la);
        } catch (Exception $e) {
            $message = 'ຜິດພາດ: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = 'ກະລຸນາປ້ອນຊື່ຮ້ານຄ້າ!';
        $message_type = 'warning';
    }
}

// Fetch current company info
$company = $pdo->query("SELECT * FROM tbcompanyinfo LIMIT 1")->fetch();
if (!$company) {
    $company = [
        'com_name_la' => 'ຮ້ານ Corner Retail',
        'com_address' => 'ນະຄອນຫຼວງວຽງຈັນ',
        'com_tel'     => '020 55555555',
        'com_email'   => 'contact@cornerretail.la',
        'barcode'     => 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!',
        'img_url'     => 'logo.png'
    ];
}

$logoImg = !empty($company['img_url']) ? $company['img_url'] : 'logo.png';
$logoPath = $base_path . 'assets/img/logo/' . $logoImg;

require_once __DIR__ . '/../../layouts/header.php';
?>

<style>
.store-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(15,23,42,0.04);
}
.receipt-preview-box {
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 24px 20px;
    width: 100%;
    max-width: 320px;
    margin: 0 auto;
    font-family: 'Noto Sans Lao Looped', monospace, sans-serif;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    color: #1e293b;
}
.receipt-divider {
    border-top: 1px dashed #94a3b8;
    margin: 12px 0;
}
</style>

<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-store-alt text-primary mr-2"></i> ຂໍ້ມູນຮ້ານຄ້າ (Store Profile & Information)
      </h5>
      <p class="text-muted small mb-0">ຈັດການຊື່ຮ້ານ, ໂລໂກ້, ທີ່ຢູ່ ແລະ ເບີໂທ ເພື່ອສະແດງໃນລະບົບ ແລະ ຫົວໃບບິນ POS</p>
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
    <!-- Left: Store Information Edit Form -->
    <div class="col-lg-7 mb-4">
      <div class="store-card p-4">
        <h6 class="font-weight-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center">
          <i class="fas fa-edit text-primary mr-2"></i> ແກ້ໄຂຂໍ້ມູນຮ້ານຄ້າ
        </h6>

        <form action="" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="save_store_info">

          <!-- Logo Upload Section -->
          <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
            <div class="position-relative mr-4" style="width: 110px; height: 110px; flex-shrink: 0;">
              <img id="logo_preview" src="<?php echo htmlspecialchars($logoPath); ?>" 
                   style="width: 110px; height: 110px; object-fit: contain; border-radius: 50%; border: 2px solid #93c5fd; background: #f8fafc; padding: 4px; cursor: pointer;"
                   onclick="document.getElementById('logo_input').click();"
                   onerror="this.src='<?php echo $base_path; ?>assets/img/logo/logo.png';">
              <button type="button" class="btn btn-sm btn-primary position-absolute shadow-sm" 
                      style="right: -2px; bottom: -2px; border-radius: 50%; width: 34px; height: 34px; border: 2px solid #fff;"
                      onclick="document.getElementById('logo_input').click();"
                      title="ປ່ຽນຮູບໂລໂກ້">
                <i class="fas fa-camera"></i>
              </button>
            </div>
            <div>
              <h6 class="font-weight-bold text-dark mb-1">ໂລໂກ້ຮ້ານຄ້າ</h6>
              <small class="text-muted d-block mb-2">ຮອງຮັບຮູບພາບ JPG, PNG, WEBP (ຂະໜາດທີ່ແນະນຳ 500x500 ພິກເຊວ)</small>
              <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px;" onclick="document.getElementById('logo_input').click();">
                <i class="fas fa-upload mr-1"></i> ເລືອກຮູບໃໝ່
              </button>
              <input type="file" name="logo_img" id="logo_input" accept="image/*" style="display: none;" onchange="previewStoreLogo(this)">
            </div>
          </div>

          <!-- Store Name & Phone -->
          <div class="row">
            <div class="col-md-7 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ຊື່ຮ້ານຄ້າ / ບໍລິສັດ: <span class="text-danger">*</span></label>
              <input type="text" name="com_name_la" id="input_store_name" class="form-control font-weight-bold text-primary" 
                     value="<?php echo htmlspecialchars($company['com_name_la'] ?? ''); ?>" 
                     placeholder="ປ້ອນຊື່ຮ້ານຄ້າ..." oninput="updateReceiptPreview()" required>
            </div>
            <div class="col-md-5 mb-3">
              <label class="font-weight-bold text-dark small mb-1">ເບີໂທລະສັບຕິດຕໍ່: <span class="text-danger">*</span></label>
              <input type="text" name="com_tel" id="input_store_tel" class="form-control" 
                     value="<?php echo htmlspecialchars($company['com_tel'] ?? ''); ?>" 
                     placeholder="020 xxxxxxxx" oninput="updateReceiptPreview()" required>
            </div>
          </div>

          <!-- Store Address -->
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ທີ່ຢູ່ຮ້ານຄ້າ (ສະແດງໃນໃບບິນ):</label>
            <textarea name="com_address" id="input_store_address" rows="2" class="form-control" 
                      placeholder="ບ້ານ, ເມືອງ, ແຂວງ..." oninput="updateReceiptPreview()"><?php echo htmlspecialchars($company['com_address'] ?? ''); ?></textarea>
          </div>

          <!-- Email / Contact -->
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">ອີເມວ ຫຼື ຊ່ອງທາງຕິດຕໍ່ອື່ນໆ:</label>
            <input type="text" name="com_email" class="form-control" 
                   value="<?php echo htmlspecialchars($company['com_email'] ?? ''); ?>" 
                   placeholder="example@gmail.com ຫຼື Facebook Page...">
          </div>

          <!-- Receipt Footer Message -->
          <div class="form-group mb-4">
            <label class="font-weight-bold text-dark small mb-1">ຂໍ້ຄວາມທ້າຍໃບບິນ (Footer Message):</label>
            <input type="text" name="receipt_footer" id="input_store_footer" class="form-control" 
                   value="<?php echo htmlspecialchars($company['barcode'] ?? 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!'); ?>" 
                   placeholder="ເຊັ່ນ: ຂອບໃຈທີ່ມາອຸດໜູນ, ສິນຄ້າຊື້ແລ້ວບໍ່ຮັບປ່ຽນຄືນ..." oninput="updateReceiptPreview()">
          </div>

          <!-- Submit Button -->
          <div class="pt-2 border-top">
            <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 8px;">
              <i class="fas fa-save mr-2"></i> ບັນທຶກຂໍ້ມູນຮ້ານຄ້າ
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Right: Live Receipt Preview (ຕົວຢ່າງຫົວໃບບິນ) -->
    <div class="col-lg-5 mb-4">
      <div class="store-card p-4">
        <h6 class="font-weight-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center">
          <i class="fas fa-receipt text-success mr-2"></i> ຕົວຢ່າງໃບບິນຮັບເງິນສົດ (Receipt Live Preview)
        </h6>

        <div class="receipt-preview-box">
          <!-- Logo & Header -->
          <div class="text-center mb-2">
            <img id="receipt_logo" src="<?php echo htmlspecialchars($logoPath); ?>" 
                 style="width: 55px; height: 55px; object-fit: contain; border-radius: 50%; margin-bottom: 6px;"
                 onerror="this.style.display='none';">
            <h6 class="font-weight-bold mb-1" id="receipt_name" style="font-size: 1.05rem; color: #0f172a;">
              <?php echo htmlspecialchars($company['com_name_la'] ?? 'ຮ້ານ Corner Retail'); ?>
            </h6>
            <div class="small text-muted" id="receipt_address" style="font-size: 0.78rem;">
              <?php echo htmlspecialchars($company['com_address'] ?? 'ນະຄອນຫຼວງວຽງຈັນ'); ?>
            </div>
            <div class="small text-muted" id="receipt_tel" style="font-size: 0.78rem;">
              ໂທ: <?php echo htmlspecialchars($company['com_tel'] ?? '020 55555555'); ?>
            </div>
          </div>

          <div class="receipt-divider"></div>

          <!-- Receipt Details Sample -->
          <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.75rem;">
            <span>ເລກບິນ: #POS-00128</span>
            <span><?php echo date('d/m/Y H:i'); ?></span>
          </div>
          <div class="d-flex justify-content-between small text-muted mb-2" style="font-size: 0.75rem;">
            <span>ພະນັກງານ: <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            <span>ປະເພດ: ເງິນສົດ</span>
          </div>

          <div class="receipt-divider"></div>

          <!-- Sample Items -->
          <div class="small mb-1 d-flex justify-content-between" style="font-size: 0.8rem;">
            <span>1. ເບຍລາວ ກະປ໋ອງ x 2</span>
            <span class="font-weight-bold">24,000</span>
          </div>
          <div class="small mb-1 d-flex justify-content-between" style="font-size: 0.8rem;">
            <span>2. ນ້ຳດື່ມບໍລິສຸດ 600ml x 1</span>
            <span class="font-weight-bold">5,000</span>
          </div>

          <div class="receipt-divider"></div>

          <!-- Total -->
          <div class="d-flex justify-content-between font-weight-bold" style="font-size: 0.95rem;">
            <span>ຍອດລວມທັງໝົດ:</span>
            <span style="color: #16a34a;">29,000 ₭</span>
          </div>

          <div class="receipt-divider"></div>

          <!-- Footer Message -->
          <div class="text-center text-muted small mt-2" id="receipt_footer" style="font-size: 0.76rem;">
            <?php echo htmlspecialchars($company['barcode'] ?? 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function previewStoreLogo(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('logo_preview').src = e.target.result;
      var rLogo = document.getElementById('receipt_logo');
      if (rLogo) {
        rLogo.src = e.target.result;
        rLogo.style.display = 'inline-block';
      }
    }
    reader.readAsDataURL(input.files[0]);
  }
}

function updateReceiptPreview() {
  var name = document.getElementById('input_store_name').value || 'ຊື່ຮ້ານຄ້າ';
  var tel = document.getElementById('input_store_tel').value || '-';
  var address = document.getElementById('input_store_address').value || '-';
  var footer = document.getElementById('input_store_footer').value || 'ຂອບໃຈທີ່ມາອຸດໜູນ!';

  document.getElementById('receipt_name').textContent = name;
  document.getElementById('receipt_tel').textContent = 'ໂທ: ' + tel;
  document.getElementById('receipt_address').textContent = address;
  document.getElementById('receipt_footer').textContent = footer;
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
