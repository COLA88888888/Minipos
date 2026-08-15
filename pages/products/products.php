<?php
session_start();
$base_path = '../../';
require_once __DIR__ . '/../../config/db.php';

// Check if logged in and has access to stock
if (empty($_SESSION['user_id']) || (!hasPermission('stock') && $_SESSION['status'] !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '../../index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

$isAdmin = (
    ($_SESSION['status'] ?? '') === 'ຜູ້ບໍລິຫານ' ||
    strtolower($_SESSION['status'] ?? '') === 'admin' ||
    strtolower($_SESSION['username'] ?? '') === 'admin' ||
    ($_SESSION['user_id'] ?? 0) == 1
);

// ====== Helper Functions ສຳລັບຈັດການຮູບພາບສິນຄ້າ (ເກັບໄວ້ໃນ assets/product_img) ======
function saveUploadedProductImage($fileInputKey = 'product_img', &$errorMsg = null) {
    if (!isset($_FILES[$fileInputKey]) || empty($_FILES[$fileInputKey]['name'])) {
        return null;
    }
    
    $fileError = $_FILES[$fileInputKey]['error'];
    if ($fileError !== UPLOAD_ERR_OK) {
        if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
            $errorMsg = 'ຂະໜາດຮູບພາບໃຫຍ່ເກີນກຳນົດຂອງເຊີເວີ';
        } elseif ($fileError !== UPLOAD_ERR_NO_FILE) {
            $errorMsg = 'ເກີດຂໍ້ຜິດພາດໃນການອັບໂຫຼດຮູບ (Error Code: ' . $fileError . ')';
        }
        return null;
    }

    $maxFileSize = 10 * 1024 * 1024; // 10MB
    if ($_FILES[$fileInputKey]['size'] > $maxFileSize) {
        $errorMsg = 'ຂະໜາດຮູບພາບໃຫຍ່ເກີນ 10MB';
        return null;
    }

    $fileTmp  = $_FILES[$fileInputKey]['tmp_name'];
    $fileName = $_FILES[$fileInputKey]['name'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'jfif', 'avif'];
    if (!in_array($fileExt, $allowed)) {
        $errorMsg = 'ຮູບແບບໄຟລ໌ບໍ່ຖືກຕ້ອງ (ຮອງຮັບ JPG, PNG, WEBP, GIF, JFIF)';
        return null;
    }

    $img_name = 'prod_' . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExt;
    
    // ໂຟນເດີເກັບຮູບພາບ (ຫຼັກແມ່ນ assets/product_img)
    $dirs = [
        __DIR__ . '/../../assets/product_img/',
        __DIR__ . '/../../assets/img/product_img/',
        __DIR__ . '/../../assets/img/products/',
        __DIR__ . '/../../assets/img/products_img/'
    ];

    $saved = false;
    $masterFile = '';
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (!$saved) {
            if (@move_uploaded_file($fileTmp, $dir . $img_name)) {
                $saved = true;
                $masterFile = $dir . $img_name;
            }
        } elseif ($masterFile !== '' && file_exists($masterFile)) {
            @copy($masterFile, $dir . $img_name);
        }
    }

    if (!$saved) {
        $errorMsg = 'ບໍ່ສາມາດບັນທຶກໄຟລ໌ລົງເຊີເວີໄດ້ ກະລຸນາກວດສອບ Permission';
        return null;
    }

    return $img_name;
}

function deleteProductImageFile($imgFile) {
    if (empty($imgFile)) return;
    $filename = basename(trim($imgFile));
    // ປ້ອງກັນບໍ່ໃຫ້ລົບໄຟລ໌ລະບົບ ຫຼື ໄຟລ໌ Default
    $protected = ['image.jpg', 'file.png', '404.png', 'repeat.png', '', '.', '..'];
    if (in_array(strtolower($filename), $protected)) {
        return;
    }
    $dirs = [
        __DIR__ . '/../../assets/product_img/',
        __DIR__ . '/../../assets/img/product_img/',
        __DIR__ . '/../../assets/img/products/',
        __DIR__ . '/../../assets/img/products_img/'
    ];
    foreach ($dirs as $dir) {
        $filePath = $dir . $filename;
        if (file_exists($filePath) && is_file($filePath)) {
            @unlink($filePath);
        }
    }
}

function getProductImagePath($imgFile) {
    if (empty($imgFile)) return null;
    static $imgPathCache = [];
    $key = trim($imgFile);
    if (isset($imgPathCache[$key])) {
        return $imgPathCache[$key];
    }
    $filename = basename($key);
    $res = '../../assets/product_img/' . $filename;
    $imgPathCache[$key] = $res;
    return $res;
}

// ====== Handle POST actions ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // --- ADD ---
    if ($_POST['action'] === 'add_product') {
        $name        = trim($_POST['product_name'] ?? '');
        $barcode     = trim($_POST['barcode'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $bprice      = floatval(str_replace(',', '', $_POST['bprice'] ?? '0'));
        $price       = floatval(str_replace(',', '', $_POST['price'] ?? '0'));
        $unit        = trim($_POST['unit'] ?? '');

        // Handle Image Upload
        $uploadError = null;
        $img_name = saveUploadedProductImage('product_img', $uploadError);
        if ($uploadError) {
            $message = $uploadError;
            $message_type = 'warning';
        }

        if ($name !== '' && $category_id > 0) {
            try {
                // ກວດສອບລະຫັດບາໂຄ້ດ
                if (empty($barcode)) {
                    $message = 'ກະລຸນາປ້ອນ ຫຼື ສະແກນລະຫັດບາໂຄ້ດສິນຄ້າ!';
                    $message_type = 'warning';
                } elseif (!preg_match('/^[0-9]+$/', $barcode)) {
                    $message = 'ລະຫັດບາໂຄ້ດ "' . htmlspecialchars($barcode) . '" ບໍ່ຖືກຕ້ອງ! ຮອງຮັບສະເພາະຕົວເລກ (0-9) ເທົ່ານັ້ນ!';
                    $message_type = 'warning';
                } else {
                    $chkStmt = $pdo->prepare("
                        SELECT p.product_name, p.product_id FROM products p WHERE p.barcode = ?
                        UNION ALL
                        SELECT p.product_name, u.product_id FROM product_units u JOIN products p ON u.product_id = p.product_id WHERE u.barcode = ?
                        LIMIT 1
                    ");
                    $chkStmt->execute([$barcode, $barcode]);
                    $dup = $chkStmt->fetch();
                    if ($dup) {
                        $message = 'ລະຫັດບາໂຄ້ດ "' . htmlspecialchars($barcode) . '" ນີ້ຖືກໃຊ້ແລ້ວນຳສິນຄ້າ: "' . htmlspecialchars($dup['product_name']) . '" (ID: ' . $dup['product_id'] . ')';
                        $message_type = 'warning';
                    }
                }

                if (empty($message_type) || $message_type !== 'warning') {
                    // ສ້າງ ລະຫັດສິນຄ້າອໍໂຕ້ (Category ID + Sequence ຕາມຈຳນວນທີ່ປ້ອນ: 10001, 80002, 120003, 20004...)
                    $product_id = intval($_POST['product_id'] ?? 0);
                    if ($product_id <= 0) {
                        $totalCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
                        $seqFormatted = str_pad($totalCount + 1, 4, '0', STR_PAD_LEFT);
                        $product_id = intval($category_id . $seqFormatted);
                    }

                    $qty = $isAdmin ? max(0, intval($_POST['qty'] ?? 0)) : 0;
                    $stmt = $pdo->prepare("INSERT INTO products (product_id, product_name, barcode, category_id, bprice, price, unit, img_url, qty) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$product_id, $name, $barcode, $category_id, $bprice, $price, $unit, $img_name, $qty]);

                    // ບັນທຶກຫຼາຍລາຄາ/ຫຼາຍຫົວໜ່ວຍ (product_units) ຖ້າມີ
                    if (isset($_POST['extra_unit_name']) && is_array($_POST['extra_unit_name'])) {
                        $insertUnitStmt = $pdo->prepare("INSERT INTO product_units (product_id, unit_name, multiplier, bprice, price, barcode) VALUES (?, ?, ?, ?, ?, ?)");
                        for ($i = 0; $i < count($_POST['extra_unit_name']); $i++) {
                            $uName = trim($_POST['extra_unit_name'][$i] ?? '');
                            $uMult = intval($_POST['extra_unit_multiplier'][$i] ?? 1);
                            $uBprice = floatval(str_replace(',', '', $_POST['extra_unit_bprice'][$i] ?? '0'));
                            $uPrice = floatval(str_replace(',', '', $_POST['extra_unit_price'][$i] ?? '0'));
                            $uBarcode = trim($_POST['extra_unit_barcode'][$i] ?? '');
                            if ($uName !== '' && $uMult > 0 && $uPrice > 0) {
                                $insertUnitStmt->execute([$product_id, $uName, $uMult, $uBprice, $uPrice, $uBarcode]);
                            }
                        }
                    }

                    $message = 'ເພີ່ມສິນຄ້າສຳເລັດ! (ລະຫັດ: ' . $product_id . ')';
                    $message_type = 'success';
                    logActivity($pdo, "ເພີ່ມສິນຄ້າ", "ID: $product_id, ຊື່: $name");
                }
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        } else {
            $message = 'ກະລຸນາປ້ອນຊື່ ແລະ ເລືອກປະເພດສິນຄ້າ!';
            $message_type = 'danger';
        }
    }

    // --- EDIT ---
    elseif ($_POST['action'] === 'edit_product') {
        $product_id  = intval($_POST['product_id'] ?? 0);
        $name        = trim($_POST['product_name'] ?? '');
        $barcode     = trim($_POST['barcode'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $bprice      = floatval(str_replace(',', '', $_POST['bprice'] ?? '0'));
        $price       = floatval(str_replace(',', '', $_POST['price'] ?? '0'));
        $unit        = trim($_POST['unit'] ?? '');
        $remove_img  = intval($_POST['remove_img'] ?? 0);

        if ($product_id > 0 && $name !== '' && $category_id > 0) {
            try {
                // ກວດສອບລະຫັດບາໂຄ້ດ
                if (empty($barcode)) {
                    $message = 'ກະລຸນາປ້ອນ ຫຼື ສະແກນລະຫັດບາໂຄ້ດສິນຄ້າ!';
                    $message_type = 'warning';
                } elseif (!preg_match('/^[0-9]+$/', $barcode)) {
                    $message = 'ລະຫັດບາໂຄ້ດ "' . htmlspecialchars($barcode) . '" ບໍ່ຖືກຕ້ອງ! ຮອງຮັບສະເພາະຕົວເລກ (0-9) ເທົ່ານັ້ນ!';
                    $message_type = 'warning';
                } else {
                    $chkStmt = $pdo->prepare("
                        SELECT p.product_name, p.product_id FROM products p WHERE p.barcode = ? AND p.product_id != ?
                        UNION ALL
                        SELECT p.product_name, u.product_id FROM product_units u JOIN products p ON u.product_id = p.product_id WHERE u.barcode = ? AND u.product_id != ?
                        LIMIT 1
                    ");
                    $chkStmt->execute([$barcode, $product_id, $barcode, $product_id]);
                    $dup = $chkStmt->fetch();
                    if ($dup) {
                        $message = 'ລະຫັດບາໂຄ້ດ "' . htmlspecialchars($barcode) . '" ນີ້ຖືກໃຊ້ແລ້ວນຳສິນຄ້າ: "' . htmlspecialchars($dup['product_name']) . '" (ID: ' . $dup['product_id'] . ')';
                        $message_type = 'warning';
                    }
                }

                if (empty($message_type) || $message_type !== 'warning') {
                    // Get current image
                    $curStmt = $pdo->prepare("SELECT img_url FROM products WHERE product_id = ?");
                    $curStmt->execute([$product_id]);
                    $currentImg = $curStmt->fetchColumn();
                    $img_name = $currentImg;

                    // If user chose to remove image
                    if ($remove_img === 1) {
                        deleteProductImageFile($currentImg);
                        $img_name = null;
                    }

                    // If new image uploaded
                    $uploadError = null;
                    $uploadedImg = saveUploadedProductImage('product_img', $uploadError);
                    if ($uploadError) {
                        $message = $uploadError;
                        $message_type = 'warning';
                    }
                    if ($uploadedImg !== null) {
                        if (!empty($currentImg)) {
                            deleteProductImageFile($currentImg);
                        }
                        $img_name = $uploadedImg;
                    }

                    if ($isAdmin && isset($_POST['qty'])) {
                        $qty = max(0, intval($_POST['qty']));
                        $stmt = $pdo->prepare("UPDATE products SET product_name=?, barcode=?, category_id=?, bprice=?, price=?, unit=?, img_url=?, qty=? WHERE product_id=?");
                        $stmt->execute([$name, $barcode, $category_id, $bprice, $price, $unit, $img_name, $qty, $product_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE products SET product_name=?, barcode=?, category_id=?, bprice=?, price=?, unit=?, img_url=? WHERE product_id=?");
                        $stmt->execute([$name, $barcode, $category_id, $bprice, $price, $unit, $img_name, $product_id]);
                    }

                    // ອັບເດດຫຼາຍລາຄາ/ຫຼາຍຫົວໜ່ວຍ (product_units)
                    $delUnitsStmt = $pdo->prepare("DELETE FROM product_units WHERE product_id = ?");
                    $delUnitsStmt->execute([$product_id]);

                    if (isset($_POST['extra_unit_name']) && is_array($_POST['extra_unit_name'])) {
                        $insertUnitStmt = $pdo->prepare("INSERT INTO product_units (product_id, unit_name, multiplier, bprice, price, barcode) VALUES (?, ?, ?, ?, ?, ?)");
                        for ($i = 0; $i < count($_POST['extra_unit_name']); $i++) {
                            $uName = trim($_POST['extra_unit_name'][$i] ?? '');
                            $uMult = intval($_POST['extra_unit_multiplier'][$i] ?? 1);
                            $uBprice = floatval(str_replace(',', '', $_POST['extra_unit_bprice'][$i] ?? '0'));
                            $uPrice = floatval(str_replace(',', '', $_POST['extra_unit_price'][$i] ?? '0'));
                            $uBarcode = trim($_POST['extra_unit_barcode'][$i] ?? '');
                            if ($uName !== '' && $uMult > 0 && $uPrice > 0) {
                                $insertUnitStmt->execute([$product_id, $uName, $uMult, $uBprice, $uPrice, $uBarcode]);
                            }
                        }
                    }

                    $message = 'ແກ້ໄຂສິນຄ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ແກ້ໄຂສິນຄ້າ", "ID: $product_id, ຊື່: $name");
                }
            } catch (Exception $e) {
                $message_type = 'success';
                logActivity($pdo, "ແກ້ໄຂສິນຄ້າ", "ID: $product_id, ຊື່ໃໝ່: $name");
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }

    // --- DELETE ---
    elseif ($_POST['action'] === 'delete_product') {
        $id = intval($_POST['product_id'] ?? 0);
        if ($id > 0) {
            try {
                // Delete associated image file
                $curStmt = $pdo->prepare("SELECT img_url FROM products WHERE product_id = ?");
                $curStmt->execute([$id]);
                $currentImg = $curStmt->fetchColumn();
                deleteProductImageFile($currentImg);

                // Delete associated units in product_units
                $delUnitsStmt = $pdo->prepare("DELETE FROM product_units WHERE product_id = ?");
                $delUnitsStmt->execute([$id]);

                $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
                $stmt->execute([$id]);
                $message = 'ລົບສິນຄ້າສຳເລັດ!';
                $message_type = 'success';
                logActivity($pdo, "ລົບສິນຄ້າ", "ID: $id");
            } catch (Exception $e) {
                $message = 'ຜິດພາດ (ອາດມີຂໍ້ມູນທີ່ກ່ຽວຂ້ອງ): ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// ====== Fetch data ======
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

$products_query = "
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_id DESC
";
$products = $pdo->query($products_query)->fetchAll();

// ດຶງຂໍ້ມູນ product_units ທັງໝົດມາ map
$allUnitsStmt = $pdo->query("SELECT * FROM product_units ORDER BY multiplier ASC, id ASC");
$productUnitsMap = [];
while ($u = $allUnitsStmt->fetch()) {
    $productUnitsMap[$u['product_id']][] = $u;
}

// ຄຳນວນລຳດັບສິນຄ້າຖັດໄປ (Global entry sequence number: 0001, 0002, 0003...)
$totalProductCount = count($products);
$nextSeqNum = $totalProductCount + 1;
$nextSeqFormatted = str_pad($nextSeqNum, 4, '0', STR_PAD_LEFT);

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/products.css?v=<?php echo filemtime(__DIR__ . '/../../themes/products.css'); ?>">

<div class="container-fluid p-4">

  <!-- Page Header -->
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-box text-primary mr-2"></i> ລາຍງານສິນຄ້າທັງໝົດ
      </h5>
    </div>
    <div class="col-sm-6 text-right">
      <button type="button" class="btn btn-info px-3 font-weight-bold mr-2 text-white" onclick="openPrintBarcodeModal()" style="border-radius: 6px; font-weight: 600;">
        <i class="fas fa-barcode mr-1"></i> ປິ່ນບາໂຄ້ດ
      </button>
      <button type="button" class="btn btn-primary px-3" data-toggle="modal" data-target="#addProductModal" style="border-radius: 6px; font-weight: 600;">
        <i class="fas fa-plus-circle mr-1"></i> ເພີ່ມສິນຄ້າໃໝ່
      </button>
    </div>
  </div>

  <!-- SweetAlert Notification -->
  <?php if ($message !== ''): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        <?php if ($message_type === 'success'): ?>
        Swal.fire({ icon: 'success', title: 'ສຳເລັດ', text: '<?php echo addslashes($message); ?>', showConfirmButton: false, timer: 1500 });
        <?php elseif ($message_type === 'warning'): ?>
        Swal.fire({ icon: 'warning', title: 'ລະຫັດບາໂຄ້ດຊໍ້າກັນ!', text: '<?php echo addslashes($message); ?>', confirmButtonColor: '#2563eb', confirmButtonText: 'ຕົກລົງ' });
        <?php else: ?>
        Swal.fire({ icon: 'error', title: 'ແຈ້ງເຕືອນ', text: '<?php echo addslashes($message); ?>', confirmButtonColor: '#2563eb', confirmButtonText: 'ຕົກລົງ' });
        <?php endif; ?>
      });
    </script>
  <?php endif; ?>

  <!-- Main Card -->
  <div class="category-card">

    <!-- Card Header: Title + Per Page Selector (left) + Category Filter & Search (right) -->
    <div class="category-card-header d-flex justify-content-between align-items-center" style="gap: 12px; flex-wrap: wrap;">

      <!-- Left: Title & Per Page selector (ບັອກໂຊລາຍການສິນຄ້າ) -->
      <div class="d-flex align-items-center" style="gap: 14px; flex-wrap: wrap;">
        <div class="d-flex align-items-center" style="gap: 6px;">
          <select id="perPageHeaderSelect" class="form-control form-control-sm bg-light font-weight-bold text-primary" style="width: 88px; height: 34px; border-radius: 8px; font-size: 0.88rem;" onchange="changePerPage(this.value)">
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="30">30</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="all">ທັງໝົດ</option>
          </select>
        </div>
      </div>

      <!-- Right Controls: Category Filter + Search (ຂວາ) -->
      <div class="d-flex align-items-center" style="gap: 10px; flex-wrap: wrap;">
        <!-- Dropdown: ເລືອກປະເພດ (ທາງໜ້າບັອກຄົ້ນຫາ) -->
        <div class="prod-filter-wrap" style="width: 200px;">
          <select id="categoryFilter" class="form-control" onchange="filterProducts()">
            <option value="">--ເລືອກປະເພດ--</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Search: ບັອກຄົ້ນຫາ -->
        <div class="search-box-wrap" style="width: 250px;">
          <i class="fas fa-search"></i>
          <input type="text" id="productSearch" class="form-control" placeholder="ຄົ້ນຫາ ຊື່, ບາໂຄ້ດ..." onkeyup="filterProducts()">
        </div>
      </div>

    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table-custom" id="productTable">
        <thead>
          <tr>
            <th class="text-center" style="width: 65px;">ລຳດັບ</th>
            <th class="text-center" style="width: 85px;">ຮູບພາບ</th>
            <th class="text-center" style="width: 105px;">ລະຫັດ</th>
            <th style="width: 175px;">ບາໂຄ້ດ</th>
            <th style="min-width: 200px;">ຊື່ສິນຄ້າ</th>
            <th class="text-right" style="width: 125px;">ລາຄາຊື້</th>
            <th class="text-right" style="width: 125px;">ລາຄາຂາຍ</th>
            <?php if ($isAdmin): ?>
              <th class="text-center" style="width: 95px;">ຈຳນວນ</th>
            <?php endif; ?>
            <th class="text-center" style="width: 85px;">ຈັດການ</th>
          </tr>
        </thead>
        <tbody>
          <!-- ບັອກສະແດງເມື່ອບໍ່ມີຂໍ້ມູນສິນຄ້າ ຫຼື ຄົ້ນຫາ/ເລືອກປະເພດແລ້ວບໍ່ພົບ -->
          <tr id="noProductDataRow" style="<?php echo empty($products) ? '' : 'display: none;'; ?>">
            <td colspan="<?php echo $isAdmin ? 9 : 8; ?>" class="text-center py-5 text-muted">
              <i class="fas fa-box-open fa-2x mb-2 d-block text-secondary"></i>
              <span class="font-weight-bold d-block" style="font-size: 1.05rem; color: #64748b;">ບໍ່ມີຂໍ້ມູນສິນຄ້າ</span>
            </td>
          </tr>
          <?php if (!empty($products)): ?>
            <?php $rowNo = 1; foreach ($products as $p): ?>
              <?php
                $extraBarcodes = [];
                if (!empty($productUnitsMap[$p['product_id']])) {
                    foreach ($productUnitsMap[$p['product_id']] as $u) {
                        if (!empty($u['barcode'])) {
                            $extraBarcodes[] = $u['barcode'];
                        }
                    }
                }
                $allBarcodes = array_filter(array_merge([$p['barcode'] ?? ''], $extraBarcodes));
              ?>
              <tr class="product-row" data-category="<?php echo $p['category_id']; ?>" data-barcodes="<?php echo htmlspecialchars(strtolower(implode(',', $allBarcodes))); ?>">

                <!-- ລຳດັບ -->
                <td class="text-center">
                  <span class="text-muted font-weight-bold" style="font-size: 0.85rem;"><?php echo $rowNo++; ?></span>
                </td>

                <!-- ຮູບພາບ (ຂະໜາດ 50x50) -->
                <td class="text-center">
                  <?php
                    $imgPath = getProductImagePath($p['img_url'] ?? '');
                  ?>
                  <?php if ($imgPath): ?>
                    <img src="<?php echo htmlspecialchars($imgPath); ?>"
                         style="width:50px; height:50px; object-fit:cover; border-radius:10px; border:1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.04);"
                         alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="width:50px; height:50px; border-radius:10px; background:#f1f5f9; border:1px solid #e2e8f0; display:none; align-items:center; justify-content:center; margin: 0 auto;">
                      <i class="fas fa-image" style="color:#cbd5e1; font-size:1.2rem;"></i>
                    </div>
                  <?php else: ?>
                    <div style="width:50px; height:50px; border-radius:10px; background:#f1f5f9; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; margin: 0 auto;">
                      <i class="fas fa-image" style="color:#cbd5e1; font-size:1.2rem;"></i>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- ລະຫັດສິນຄ້າ (ແຍກຕ່າງຫາກ) -->
                <td class="text-center product-code-cell">
                  <span class="cat-id-badge"><?php echo $p['product_id']; ?></span>
                </td>

                <!-- ບາໂຄ້ດ (ແຍກຕ່າງຫາກ - ພ້ອມໄອຄອນປິ່ນເຕີໃກ້ບາໂຄ້ດ) -->
                <td class="product-barcode-cell" style="white-space: nowrap;">
                  <div class="d-flex align-items-center" style="gap: 8px;">
                    <?php if (!empty($p['barcode'])): ?>
                      <span style="font-family: 'Courier New', monospace; font-size: 0.88rem; font-weight: 600; color: #334155; letter-spacing: 0.5px;">
                        <i class="fas fa-barcode mr-1 text-muted" style="font-size: 0.82rem;"></i><?php echo htmlspecialchars($p['barcode']); ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                    <button type="button" class="icon-btn icon-btn-print" title="ປິ່ນບາໂຄ້ດ"
                      onclick='openPrintBarcodeModal(<?php echo json_encode($p); ?>)'>
                      <i class="fas fa-print"></i>
                    </button>
                  </div>
                </td>

                <!-- ຊື່ສິນຄ້າ -->
                <td class="product-name-cell">
                  <span class="font-weight-bold" style="color: #1e293b;"><?php echo htmlspecialchars($p['product_name']); ?></span>
                  <?php if (!empty($p['unit'])): ?>
                    <span class="badge badge-light border ml-1 px-2 py-0.5" style="font-size:0.78rem; color:#64748b; font-weight: 500;">
                      <i class="fas fa-box-open mr-1" style="font-size:0.72rem; color:#94a3b8;"></i><?php echo htmlspecialchars($p['unit']); ?>
                    </span>
                  <?php endif; ?>
                </td>

                <!-- ລາຄາຊື້ -->
                <td class="text-right" style="color: #64748b; font-size: 0.9rem;">
                  <?php echo number_format($p['bprice'], 0); ?> <span style="font-size:0.78rem;">₭</span>
                </td>

                <!-- ລາຄາຂາຍ -->
                <td class="text-right font-weight-bold" style="color: #16a34a;">
                  <?php echo number_format($p['price'], 0); ?> <span style="font-size:0.78rem;">₭</span>
                </td>

                <?php if ($isAdmin): ?>
                  <!-- ຈຳນວນ (ສະແດງສະເພາະແອັດມິນ) -->
                  <td class="text-center">
                    <?php
                      $qty = floatval($p['qty'] ?? 0);
                      $qtyClass = $qty <= 0 ? 'badge-danger' : ($qty <= 10 ? 'badge-warning text-dark' : 'badge-success');
                      $qtyLabel = $qty <= 0 ? 'ໝົດແລ້ວ (0)' : (number_format($qty) . ' ' . (!empty($p['unit']) ? htmlspecialchars($p['unit']) : ''));
                    ?>
                    <span class="badge <?php echo $qtyClass; ?> px-2 py-1" style="border-radius: 6px; font-size: 0.82rem; min-width: 36px;">
                      <?php echo $qtyLabel; ?>
                    </span>
                  </td>
                <?php endif; ?>

                <!-- ຈັດການ (ໄອຄອນແກ້ໄຂ, ລົບ ສະເພາະ) -->
                <td class="text-center" style="white-space: nowrap;">
                  <button type="button" class="icon-btn icon-btn-edit mr-1" title="ແກ້ໄຂ"
                    onclick='editProduct(<?php echo json_encode($p); ?>)'>
                    <i class="fas fa-edit"></i>
                  </button>
                  <?php if (hasPermission('edit')): ?>
                    <button type="button" class="icon-btn icon-btn-delete" title="ລົບ"
                      onclick="confirmDeleteProduct(<?php echo $p['product_id']; ?>, '<?php echo htmlspecialchars(addslashes($p['product_name'])); ?>')">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  <?php endif; ?>
                </td>

              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Card Footer: Blue Circular Pagination (ສີຟ້າວົງມົນ ຊິດຂວາ) -->
    <div class="card-footer bg-white border-top d-flex flex-column flex-md-row align-items-center justify-content-between py-3 px-3 px-md-4" style="border-radius: 0 0 14px 14px;">
      <!-- Right: Blue Circular Pagination Buttons (ຊິດຂວາສຸດ) -->
      <div class="d-flex justify-content-end ml-md-auto">
        <nav aria-label="Product Pagination">
          <ul class="pagination pagination-circle mb-0 justify-content-end" id="productPagination">
            <!-- Dynamic circular page buttons -->
          </ul>
        </nav>
      </div>
    </div>

  </div>
</div>

<!-- ====== Modal Components ====== -->
<?php require_once __DIR__ . '/components/form_add_product.php'; ?>
<?php require_once __DIR__ . '/components/form_edit_product.php'; ?>
<?php require_once __DIR__ . '/components/modal_print_barcode.php'; ?>



<script>
  var DEFAULT_IMG = '<?php echo $base_path; ?>assets/img/image.jpg';

  // ====== ຟັງຊັນ Preview ຮູບພາບ ພ້ອມກວດສອບປະເພດ ແລະ ຂະໜາດໄຟລ໌ ======
  var MAX_IMG_SIZE = 10 * 1024 * 1024; // 10MB

  function previewProductImg(input, previewId, btnRemoveId) {
    if (input.files && input.files[0]) {
      var file = input.files[0];

      // 1. ກວດສອບປະເພດໄຟລ໌ (ກວດສອບທັງ MIME type ແລະ ນາມສະກຸນໄຟລ໌)
      var fileName = (file.name || '').toLowerCase();
      var ext = fileName.split('.').pop();
      var allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'jfif', 'avif'];
      var isImage = (file.type && file.type.startsWith('image/')) || allowedExts.includes(ext);

      if (!isImage) {
        Swal.fire({
          icon: 'error',
          title: 'ຮູບແບບໄຟລ໌ບໍ່ຖືກຕ້ອງ',
          text: 'ກະລຸນາເລືອກສະເພາະໄຟລ໌ຮູບພາບ (JPG, PNG, WEBP, GIF, JFIF)',
          confirmButtonColor: '#2563eb',
          confirmButtonText: 'ຕົກລົງ'
        });
        input.value = '';
        return;
      }

      // 2. ກວດສອບຂະໜາດໄຟລ໌ (ບໍ່ໃຫ້ເກີນ 10MB)
      if (file.size > MAX_IMG_SIZE) {
        var fileSizeMB = (file.size / (1024 * 1024)).toFixed(1);
        Swal.fire({
          icon: 'warning',
          title: 'ຂະໜາດຮູບພາບໃຫຍ່ເກີນໄປ',
          html: 'ຂະໜາດຮູບຂອງທ່ານ: <strong>' + fileSizeMB + ' MB</strong><br>ລະບົບຮອງຮັບຂະໜາດສູງສຸດບໍ່ເກີນ <strong>10 MB</strong>',
          confirmButtonColor: '#2563eb',
          confirmButtonText: 'ຕົກລົງ'
        });
        input.value = '';
        return;
      }

      // 3. ສະແດງ Preview
      var reader = new FileReader();
      reader.onload = function(e) {
        var prev = document.getElementById(previewId);
        if (prev) prev.src = e.target.result;
        var btn = document.getElementById(btnRemoveId);
        if (btn) btn.style.display = 'flex';
      };
      reader.readAsDataURL(file);

      // ຖ້າຢູ່ໃນ Edit Modal, ຣີເຊັດ remove_img flag ເປັນ 0
      if (previewId === 'edit_img_preview') {
        var flag = document.getElementById('edit_remove_img');
        if (flag) flag.value = '0';
      }
    }
  }

  // ====== ຟັງຊັນ ລົບຮູບພາບ ======
  function removeProductImg(mode) {
    if (mode === 'add') {
      var input = document.getElementById('add_product_img');
      if (input) input.value = '';
      var prev = document.getElementById('add_img_preview');
      if (prev) prev.src = DEFAULT_IMG;
      var btn = document.getElementById('btn_remove_add_img');
      if (btn) btn.style.display = 'none';
    } else if (mode === 'edit') {
      var input = document.getElementById('edit_product_img');
      if (input) input.value = '';
      var flag = document.getElementById('edit_remove_img');
      if (flag) flag.value = '1';
      var prev = document.getElementById('edit_img_preview');
      if (prev) prev.src = DEFAULT_IMG;
      var btn = document.getElementById('btn_remove_edit_img');
      if (btn) btn.style.display = 'none';
    }
  }

  // ====== ຟັງຊັນ ສ້າງບາໂຄ້ດອັດຕະໂນມັດ 13 ຫຼັກ (EAN-13 Generator) ======
  function generateEAN13(target) {
    // Generate 12 random digits (Prefix '20' for internal POS barcodes)
    var code = '20';
    for (var i = 0; i < 10; i++) {
      code += Math.floor(Math.random() * 10);
    }
    // Calculate EAN-13 Checksum digit
    var sum = 0;
    for (var i = 0; i < 12; i++) {
      var digit = parseInt(code.charAt(i), 10);
      sum += (i % 2 === 0) ? digit : digit * 3;
    }
    var checksum = (10 - (sum % 10)) % 10;
    var finalBarcode = code + checksum;

    if (typeof target === 'string') {
      $('#' + target).val(finalBarcode).trigger('change');
    } else if (target && (target instanceof HTMLElement || target.jquery)) {
      $(target).closest('.input-group').find('input').val(finalBarcode).trigger('change');
    }
  }

  // ====== ຟັງຊັນຈັດ format ລາຄາມີຈຸດ (Thousand Separator) ແລະ ຮັບແຕ່ຕົວເລກ ======
  function formatPriceInput(input) {
    // ອະນຸຍາດສະເພາະຕົວເລກ 0-9
    var val = input.value.replace(/\D/g, '');
    if (val === '') {
      input.value = '';
      return;
    }
    input.value = Number(val).toLocaleString('en-US');
  }

  // ====== Edit Modal: Populate fields ======
  function editProduct(p) {
    $('#edit_product_id').val(p.product_id);
    $('#edit_product_id_display').val(p.product_id);
    $('#edit_product_name').val(p.product_name);
    $('#edit_barcode').val(p.barcode || '');
    $('#edit_category_id').val(p.category_id);
    $('#edit_unit').val(p.unit || '');
    $('#edit_qty').val(p.qty !== undefined && p.qty !== null ? p.qty : 0);

    // Format ລາຄາມີຈຸດ
    var bpriceNum = p.bprice ? Number(p.bprice) : 0;
    var priceNum  = p.price ? Number(p.price) : 0;
    $('#edit_bprice').val(bpriceNum > 0 ? bpriceNum.toLocaleString('en-US') : '0');
    $('#edit_price').val(priceNum > 0 ? priceNum.toLocaleString('en-US') : '0');

    // ຈັດການຮູບພາບໃນ Edit Modal
    $('#edit_remove_img').val('0');
    $('#edit_product_img').val('');
    
    var imgName = p.img_url ? p.img_url.replace(/^.*[\\\/]/, '') : '';
    if (imgName) {
      $('#edit_img_preview').attr('src', '<?php echo $base_path; ?>assets/product_img/' + imgName);
      $('#edit_img_preview').off('error').on('error', function() {
        $(this).attr('src', '<?php echo $base_path; ?>assets/img/products/' + imgName);
      });
      $('#btn_remove_edit_img').css('display', 'flex');
    } else {
      $('#edit_img_preview').attr('src', DEFAULT_IMG);
      $('#btn_remove_edit_img').css('display', 'none');
    }

    $('#editProductModal').modal('show');

    // ໂຫຼດຫຼາຍລາຄາ / ຫຼາຍຫົວໜ່ວຍ (product_units) ຜ່ານ AJAX
    $('#edit_multi_units_container').empty();
    $.getJSON('api_product.php?action=get_product_units&product_id=' + p.product_id, function(res) {
      if (res && res.success && res.units && res.units.length > 0) {
        res.units.forEach(function(u) {
          addUnitRow('edit', u);
        });
      }
    });
  }

  // ====== Client-side Pagination Engine ======
  var currentPage = 1;
  var perPage = 10;
  var filteredRows = [];

  function initPagination() {
    filterProducts();
  }

  function changePerPage(val) {
    if (val === 'all') {
      perPage = 999999;
    } else {
      perPage = parseInt(val) || 10;
    }

    // Sync ທັງສອງ Dropdown (Header ແລະ Footer)
    var hSelect = document.getElementById('perPageHeaderSelect');
    var fSelect = document.getElementById('perPageSelect');
    if (hSelect) hSelect.value = val;
    if (fSelect) fSelect.value = val;

    currentPage = 1;
    applyPagination();
  }

  function goToPage(page) {
    var totalPages = Math.ceil(filteredRows.length / perPage) || 1;
    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;
    currentPage = page;
    applyPagination();
  }

  function applyPagination() {
    var total = filteredRows.length;
    var totalPages = Math.ceil(total / perPage) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    var startIdx = (currentPage - 1) * perPage;
    var endIdx   = startIdx + perPage;

    // Show/Hide rows
    var allRows = document.querySelectorAll('.product-row');
    allRows.forEach(function(row) {
      row.style.display = 'none';
    });

    var noDataEl = document.getElementById('noProductDataRow');

    if (total === 0) {
      if (noDataEl) noDataEl.style.display = '';
    } else {
      if (noDataEl) noDataEl.style.display = 'none';
      for (var i = startIdx; i < endIdx && i < total; i++) {
        if (filteredRows[i]) {
          filteredRows[i].style.display = '';
        }
      }
    }

    // Update left info text
    var startDisplay = total === 0 ? 0 : startIdx + 1;
    var endDisplay   = Math.min(endIdx, total);
    var startEl = document.getElementById('page_info_start');
    var endEl   = document.getElementById('page_info_end');
    var totalEl = document.getElementById('page_info_total');
    if (startEl) startEl.textContent = startDisplay;
    if (endEl)   endEl.textContent = endDisplay;
    if (totalEl) totalEl.textContent = total;

    // Render Circular Pagination Buttons
    renderPaginationControls(totalPages);
  }

  function renderPaginationControls(totalPages) {
    var paginationUl = document.getElementById('productPagination');
    if (!paginationUl) return;
    paginationUl.innerHTML = '';

    if (totalPages < 1) totalPages = 1;

    // 1. ປຸ່ມ ກ່ອນໜ້າ (Previous)
    var prevLi = document.createElement('li');
    prevLi.className = 'page-item ' + (currentPage <= 1 ? 'disabled' : '');
    prevLi.innerHTML = '<a class="page-link" href="javascript:void(0)" ' + (currentPage > 1 ? 'onclick="goToPage(' + (currentPage - 1) + ')"' : '') + ' title="ໜ້າກ່ອນໜ້າ"><i class="fas fa-chevron-left"></i></a>';
    paginationUl.appendChild(prevLi);

    // 2. ປຸ່ມເລກໜ້າ (Circular page numbers with smart window)
    var maxButtons = 5;
    var startPage = Math.max(1, currentPage - 2);
    var endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) {
      startPage = Math.max(1, endPage - maxButtons + 1);
    }

    if (startPage > 1) {
      var firstLi = document.createElement('li');
      firstLi.className = 'page-item';
      firstLi.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="goToPage(1)">1</a>';
      paginationUl.appendChild(firstLi);

      if (startPage > 2) {
        var dotLi = document.createElement('li');
        dotLi.className = 'page-item disabled';
        dotLi.innerHTML = '<span class="page-link" style="border:none; background:transparent;">...</span>';
        paginationUl.appendChild(dotLi);
      }
    }

    for (var p = startPage; p <= endPage; p++) {
      var pageLi = document.createElement('li');
      pageLi.className = 'page-item ' + (p === currentPage ? 'active' : '');
      pageLi.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="goToPage(' + p + ')">' + p + '</a>';
      paginationUl.appendChild(pageLi);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        var dotLi2 = document.createElement('li');
        dotLi2.className = 'page-item disabled';
        dotLi2.innerHTML = '<span class="page-link" style="border:none; background:transparent;">...</span>';
        paginationUl.appendChild(dotLi2);
      }

      var lastLi = document.createElement('li');
      lastLi.className = 'page-item';
      lastLi.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="goToPage(' + totalPages + ')">' + totalPages + '</a>';
      paginationUl.appendChild(lastLi);
    }

    // 3. ປຸ່ມ ຖັດໄປ (Next)
    var nextLi = document.createElement('li');
    nextLi.className = 'page-item ' + (currentPage >= totalPages ? 'disabled' : '');
    nextLi.innerHTML = '<a class="page-link" href="javascript:void(0)" ' + (currentPage < totalPages ? 'onclick="goToPage(' + (currentPage + 1) + ')"' : '') + ' title="ໜ້າຖັດໄປ"><i class="fas fa-chevron-right"></i></a>';
    paginationUl.appendChild(nextLi);
  }

  // ====== Combined filter: category + search ======
  function filterProducts() {
    var search   = document.getElementById('productSearch').value.toLowerCase().trim();
    var catId    = document.getElementById('categoryFilter').value;
    var rows     = document.querySelectorAll('.product-row');
    filteredRows = [];

    rows.forEach(function(row) {
      var rowCat  = row.getAttribute('data-category');
      var name    = row.querySelector('.product-name-cell').textContent.toLowerCase();
      var barCell = row.querySelector('.product-barcode-cell') ? row.querySelector('.product-barcode-cell').textContent.toLowerCase() : '';
      var barAttr = (row.getAttribute('data-barcodes') || '').toLowerCase();
      var code    = row.querySelector('.product-code-cell') ? row.querySelector('.product-code-cell').textContent.toLowerCase() : '';

      var matchCat    = (catId === '' || rowCat === catId);
      var matchSearch = (search === '' || name.includes(search) || barCell.includes(search) || barAttr.includes(search) || code.includes(search));

      if (matchCat && matchSearch) {
        filteredRows.push(row);
      }
    });

    // ອັບເດດ count badge
    var countBadge = document.getElementById('productCount');
    if (countBadge) {
      countBadge.textContent = 'ສະແດງ ' + filteredRows.length + ' ລາຍການ';
    }

    currentPage = 1;
    applyPagination();
  }

  $(document).ready(function() {
    initPagination();

    // Prevent Enter key in productSearch from reloading page or submitting forms
    $('#productSearch').on('keydown', function(e) {
      if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        filterProducts();
      }
    });

    // Global barcode scanner listener when no input/modal is active
    var barcodeBuffer = '';
    var barcodeTimer = null;

    $(document).on('keydown', function(e) {
      var activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
      var isInput = (activeTag === 'input' || activeTag === 'select' || activeTag === 'textarea');
      var isModalOpen = $('.modal.show').length > 0;

      if (isInput || isModalOpen) return;

      if (e.key === 'Enter' || e.keyCode === 13) {
        if (barcodeBuffer.length >= 2) {
          $('#productSearch').val(barcodeBuffer);
          filterProducts();
          $('#productSearch').focus();
        }
        barcodeBuffer = '';
        if (barcodeTimer) clearTimeout(barcodeTimer);
      } else if (e.key && e.key.length === 1) {
        barcodeBuffer += e.key;
        if (barcodeTimer) clearTimeout(barcodeTimer);
        barcodeTimer = setTimeout(function() {
          barcodeBuffer = '';
        }, 300);
      }
    });
  });

  // ====== Delete confirm ======
  function confirmDeleteProduct(productId, productName) {
    Swal.fire({
      title: 'ຢືນຢັນການລົບ?',
      html: 'ທ່ານຕ້ອງການລົບ <strong>"' + productName + '"</strong> ແທ້ຫຼືບໍ່?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> ລົບເລີຍ',
      cancelButtonText: 'ຍົກເລີກ',
      heightAuto: false
    }).then(function(result) {
      if (result.isConfirmed) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        form.innerHTML = '<input type="hidden" name="action" value="delete_product">'
                       + '<input type="hidden" name="product_id" value="' + productId + '">';
        document.body.appendChild(form);
        form.submit();
      }
    });
  }
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
