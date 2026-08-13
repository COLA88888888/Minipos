<?php
session_start();
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../../config/db.php';

// Check authorization
if (empty($_SESSION['user_id']) || (!hasPermission('stock') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// ====== HANDLE POST ACTIONS ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // --- 1. ACTION: DELETE SINGLE IMPORT DETAIL ---
    if ($_POST['action'] === 'delete_import_detail') {
        $import_detail_id = intval($_POST['import_detail_id'] ?? 0);
        if ($import_detail_id > 0) {
            try {
                $pdo->beginTransaction();

                $detStmt = $pdo->prepare("
                    SELECT id.*, p.qty AS current_product_qty, pb.initial_qty AS batch_initial_qty, pb.quantity AS batch_rem_qty
                    FROM import_details id
                    JOIN products p ON id.product_id = p.product_id
                    LEFT JOIN product_batches pb ON id.import_detail_id = pb.import_detail_id
                    WHERE id.import_detail_id = ? FOR UPDATE
                ");
                $detStmt->execute([$import_detail_id]);
                $det = $detStmt->fetch();

                if ($det) {
                    $bRem = isset($det['batch_rem_qty']) ? intval($det['batch_rem_qty']) : null;
                    $bInit = isset($det['batch_initial_qty']) ? intval($det['batch_initial_qty']) : null;
                    $pQty = intval($det['current_product_qty']);
                    $baseQty = intval($det['total_base_qty']);

                    if (($bRem !== null && $bInit !== null && $bRem < $bInit) || ($pQty < $baseQty)) {
                        throw new Exception("ບໍ່ສາມາດລົບ/ຍົກເລີກໃບບິນນີ້ໄດ້ ເນື່ອງຈາກສິນຄ້ານີ້ມີການເຄື່ອນໄຫວ ຫຼື ຖືກຂາຍອອກໄປແລ້ວ!");
                    }

                    // Revert stock quantity in products
                    $revStock = $pdo->prepare("UPDATE products SET qty = GREATEST(0, qty - ?) WHERE product_id = ?");
                    $revStock->execute([$det['total_base_qty'], $det['product_id']]);

                    // Update total cost in imports master
                    $updImp = $pdo->prepare("UPDATE imports SET total_cost = GREATEST(0, total_cost - ?) WHERE import_id = ?");
                    $updImp->execute([$det['total_cost'], $det['import_id']]);

                    // Delete batches
                    $delBatch = $pdo->prepare("DELETE FROM product_batches WHERE import_detail_id = ?");
                    $delBatch->execute([$import_detail_id]);

                    // Delete detail
                    $delDet = $pdo->prepare("DELETE FROM import_details WHERE import_detail_id = ?");
                    $delDet->execute([$import_detail_id]);

                    $message = 'ຍົກເລີກການຮັບສິນຄ້າເຂົ້າສຳເລັດ (ປັບສະຕັອກຄືນແລ້ວ)!';
                    $message_type = 'success';
                    logActivity($pdo, "ຍົກເລີກການຮັບສິນຄ້າເຂົ້າ", "Detail ID: $import_detail_id");
                }

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = $e->getMessage();
                $message_type = 'danger';
            }
        }
    }

    // --- 2. ACTION: DELETE ENTIRE MASTER IMPORT BILL ---
    elseif ($_POST['action'] === 'delete_master_import') {
        $import_id = intval($_POST['import_id'] ?? 0);
        if ($import_id > 0) {
            try {
                $pdo->beginTransaction();

                $detStmt = $pdo->prepare("
                    SELECT id.*, p.qty AS current_product_qty, pb.initial_qty AS batch_initial_qty, pb.quantity AS batch_rem_qty
                    FROM import_details id
                    JOIN products p ON id.product_id = p.product_id
                    LEFT JOIN product_batches pb ON id.import_detail_id = pb.import_detail_id
                    WHERE id.import_id = ? FOR UPDATE
                ");
                $detStmt->execute([$import_id]);
                $details = $detStmt->fetchAll();

                foreach ($details as $det) {
                    $bRem = isset($det['batch_rem_qty']) ? intval($det['batch_rem_qty']) : null;
                    $bInit = isset($det['batch_initial_qty']) ? intval($det['batch_initial_qty']) : null;
                    $pQty = intval($det['current_product_qty']);
                    $baseQty = intval($det['total_base_qty']);

                    if (($bRem !== null && $bInit !== null && $bRem < $bInit) || ($pQty < $baseQty)) {
                        throw new Exception("ບໍ່ສາມາດລົບໃບບິນນີ້ໄດ້ ເນື່ອງຈາກມີສິນຄ້າໃນໃບບິນນີ້ຖືກຂາຍ ຫຼື ເຄື່ອນໄຫວແລ້ວ!");
                    }
                }

                foreach ($details as $det) {
                    $pdo->prepare("UPDATE products SET qty = GREATEST(0, qty - ?) WHERE product_id = ?")
                        ->execute([$det['total_base_qty'], $det['product_id']]);
                    $pdo->prepare("DELETE FROM product_batches WHERE import_detail_id = ?")
                        ->execute([$det['import_detail_id']]);
                }

                $pdo->prepare("DELETE FROM import_details WHERE import_id = ?")->execute([$import_id]);
                $pdo->prepare("DELETE FROM imports WHERE import_id = ?")->execute([$import_id]);

                $message = 'ລົບໃບບິນຮັບເຂົ້າສິນຄ້າສຳເລັດ (ປັບສະຕັອກຄືນແລ້ວ)!';
                $message_type = 'success';
                logActivity($pdo, "ລົບໃບບິນຮັບເຂົ້າສິນຄ້າ", "Import ID: $import_id");

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = $e->getMessage();
                $message_type = 'danger';
            }
        }
    }

    // --- 3. ACTION: EDIT IMPORT DETAIL ---
    elseif ($_POST['action'] === 'edit_import_detail') {
        $import_detail_id = intval($_POST['import_detail_id'] ?? 0);
        $supplier_name    = trim($_POST['supplier_name'] ?? '');
        $quantity         = intval($_POST['quantity'] ?? 0);
        $cost_price       = floatval($_POST['cost_price'] ?? 0);
        $unit_key         = trim($_POST['unit_key'] ?? 'base');
        $expiry_date      = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $notes            = trim($_POST['notes'] ?? '');

        if ($import_detail_id > 0 && $quantity > 0) {
            try {
                $pdo->beginTransaction();

                $detStmt = $pdo->prepare("SELECT * FROM import_details WHERE import_detail_id = ? FOR UPDATE");
                $detStmt->execute([$import_detail_id]);
                $oldDet = $detStmt->fetch();

                if ($oldDet) {
                    $product_id = $oldDet['product_id'];

                    // Fetch Product
                    $pStmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ? FOR UPDATE");
                    $pStmt->execute([$product_id]);
                    $product = $pStmt->fetch();

                    $unit_name = !empty($product['unit']) ? $product['unit'] : 'ອັນ';
                    $multiplier = 1;

                    if ($unit_key !== 'base') {
                        $uStmt = $pdo->prepare("SELECT unit_name, multiplier FROM product_units WHERE id = ? AND product_id = ?");
                        $uStmt->execute([intval($unit_key), $product_id]);
                        $puRow = $uStmt->fetch();
                        if ($puRow) {
                            $unit_name  = $puRow['unit_name'];
                            $multiplier = max(1, intval($puRow['multiplier']));
                        }
                    }

                    $new_total_base_qty = $quantity * $multiplier;
                    $new_total_cost     = $quantity * $cost_price;

                    $diff_base_qty = $new_total_base_qty - $oldDet['total_base_qty'];
                    $diff_cost     = $new_total_cost - $oldDet['total_cost'];

                    // Update products stock
                    $updStock = $pdo->prepare("UPDATE products SET qty = GREATEST(0, qty + ?), bprice = ? WHERE product_id = ?");
                    $updStock->execute([$diff_base_qty, $cost_price, $product_id]);

                    // Update master import
                    $updImp = $pdo->prepare("UPDATE imports SET supplier_name = ?, total_cost = GREATEST(0, total_cost + ?), notes = IF(? != '', ?, notes) WHERE import_id = ?");
                    $updImp->execute([$supplier_name, $diff_cost, $notes, $notes, $oldDet['import_id']]);

                    // Update import_details
                    $updDet = $pdo->prepare("UPDATE import_details SET unit_name = ?, multiplier = ?, quantity = ?, total_base_qty = ?, cost_price = ?, total_cost = ?, expiry_date = ? WHERE import_detail_id = ?");
                    $updDet->execute([$unit_name, $multiplier, $quantity, $new_total_base_qty, $cost_price, $new_total_cost, $expiry_date, $import_detail_id]);

                    // Update product_batches
                    $updBatch = $pdo->prepare("UPDATE product_batches SET expiry_date = ?, initial_qty = ?, quantity = GREATEST(0, quantity + ?) WHERE import_detail_id = ?");
                    $updBatch->execute([$expiry_date, $new_total_base_qty, $diff_base_qty, $import_detail_id]);

                    $message = 'ດັດແກ້ຂໍ້ມູນການຮັບສິນຄ້າເຂົ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ແກ້ໄຂການຮັບສິນຄ້າເຂົ້າ", "Detail ID: $import_detail_id");
                }

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }

    // --- 4. ACTION: EDIT MASTER IMPORT BILL (SUPPLIER, DATE, NOTES) ---
    elseif ($_POST['action'] === 'edit_master_import') {
        $import_id     = intval($_POST['import_id'] ?? 0);
        $supplier_name = trim($_POST['supplier_name'] ?? '');
        $raw_date      = trim($_POST['import_date'] ?? '');
        $import_date   = !empty($raw_date) ? $raw_date . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
        $notes         = trim($_POST['notes'] ?? '');

        if ($import_id > 0) {
            try {
                $updMaster = $pdo->prepare("UPDATE imports SET supplier_name = ?, import_date = ?, notes = ? WHERE import_id = ?");
                $updMaster->execute([$supplier_name, $import_date, $notes, $import_id]);

                $message = 'ດັດແກ້ຂໍ້ມູນໃບບິນຮັບເຂົ້າສຳເລັດ!';
                $message_type = 'success';
                logActivity($pdo, "ແກ້ໄຂຂໍ້ມູນໃບບິນຮັບເຂົ້າ", "Import ID: $import_id");
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// ====== GET FILTER PARAMETERS ======
$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date   = $_GET['to_date']   ?? date('Y-m-d');
$view_type = $_GET['view_type'] ?? 'bill';

// Fetch categories for modal filter
$categories = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC")->fetchAll();

// Fetch all products with categories & multi-units
$products = $pdo->query("
    SELECT p.product_id, p.product_name, p.barcode, p.unit, p.bprice, p.price, p.qty, p.category_id, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.product_name ASC
")->fetchAll();

$product_units_map = [];
$uRows = $pdo->query("SELECT * FROM product_units ORDER BY multiplier ASC")->fetchAll();
foreach ($uRows as $u) {
    $product_units_map[$u['product_id']][] = $u;
}

// Pre-fetch items per bill for the View Bill Items Modal
$allBillItems = $pdo->query("
    SELECT id.*, p.product_name, p.barcode, p.unit AS base_unit,
    (
        CASE 
            WHEN (pb.quantity IS NOT NULL AND pb.initial_qty IS NOT NULL AND pb.quantity < pb.initial_qty)
                 OR (p.qty < id.total_base_qty) THEN 1 
            ELSE 0 
        END
    ) AS has_movement
    FROM import_details id
    JOIN products p ON id.product_id = p.product_id
    LEFT JOIN product_batches pb ON id.import_detail_id = pb.import_detail_id
    ORDER BY id.import_detail_id ASC
")->fetchAll();

$billItemsMap = [];
foreach ($allBillItems as $bi) {
    // Make sure we carry forward necessary fields
    $bi['invoice_number'] = ''; // Will be populated in JS or we can join imports
    $billItemsMap[$bi['import_id']][] = $bi;
}

// ====== FETCH IMPORT RECORDS BASED ON VIEW TYPE & DATE RANGE ======
$importList = [];
if ($view_type === 'bill') {
    $billQuery = "
        SELECT 
            i.import_id,
            i.invoice_number,
            i.supplier_name,
            i.import_date,
            i.total_cost,
            i.notes,
            u.username,
            u.fname,
            u.lname,
            COUNT(id.import_detail_id) AS item_count,
            SUM(id.total_base_qty) AS sum_base_qty,
            MAX(
                CASE 
                    WHEN (pb.quantity IS NOT NULL AND pb.initial_qty IS NOT NULL AND pb.quantity < pb.initial_qty)
                         OR (p.qty < id.total_base_qty) THEN 1 
                    ELSE 0 
                END
            ) AS has_movement
        FROM imports i
        LEFT JOIN import_details id ON i.import_id = id.import_id
        LEFT JOIN products p ON id.product_id = p.product_id
        LEFT JOIN product_batches pb ON id.import_detail_id = pb.import_detail_id
        LEFT JOIN tbuser u ON i.created_by = u.Id
        WHERE DATE(i.import_date) BETWEEN ? AND ?
        GROUP BY i.import_id
        ORDER BY i.import_date DESC, i.import_id DESC
    ";
    $stmt = $pdo->prepare($billQuery);
    $stmt->execute([$from_date, $to_date]);
    $importList = $stmt->fetchAll();
} else {
    $detailQuery = "
        SELECT 
            id.import_detail_id,
            id.import_id,
            id.product_id,
            id.quantity,
            id.unit_name,
            id.multiplier,
            id.total_base_qty,
            id.cost_price,
            id.total_cost,
            id.expiry_date,
            i.invoice_number,
            i.supplier_name,
            i.import_date,
            p.product_name,
            p.qty AS current_product_qty,
            p.unit AS base_unit,
            u.username,
            u.fname,
            u.lname,
            pb.initial_qty AS batch_initial_qty,
            pb.quantity AS batch_rem_qty
        FROM import_details id
        JOIN imports i ON id.import_id = i.import_id
        JOIN products p ON id.product_id = p.product_id
        LEFT JOIN tbuser u ON i.created_by = u.Id
        LEFT JOIN product_batches pb ON id.import_detail_id = pb.import_detail_id
        WHERE DATE(i.import_date) BETWEEN ? AND ?
        ORDER BY i.import_date DESC, id.import_detail_id DESC
    ";
    $stmt = $pdo->prepare($detailQuery);
    $stmt->execute([$from_date, $to_date]);
    $importList = $stmt->fetchAll();
}

$total_records = count($importList);

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/import_stock.css?v=<?php echo filemtime(__DIR__ . '/../../themes/import_stock.css'); ?>">

<div class="content-wrapper bg-light">
  
  <!-- Page Header -->
  <section class="content-header py-3">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h4 class="m-0 font-weight-bold text-dark" style="font-family: 'Noto Sans Lao Looped';">
            <i class="fas fa-list-alt text-success mr-2"></i> ລາຍການສິນຄ້າຮັບເຂົ້າ
          </h4>
        </div>
        <div class="col-sm-6 text-right">
          <a href="import_stock.php" class="btn btn-primary font-weight-bold shadow-sm" style="border-radius: 8px;">
            <i class="fas fa-plus-circle mr-1"></i> ນຳເຂົ້າສິນຄ້າ
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <section class="content pb-5">
    <div class="container-fluid">

      <!-- Alert Notification & Success Popup Modal -->
      <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-radius: 10px;">
          <i class="fas <?php echo ($message_type === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
          <?php echo htmlspecialchars($message); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <?php if ($message_type === 'success'): ?>
          <script>
            $(document).ready(function() {
              Swal.fire({
                title: 'ດຳເນີນການສຳເລັດ!',
                text: '<?php echo addslashes($message); ?>',
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'ຕົກລົງ',
                timer: 3500,
                timerProgressBar: true
              });
            });
          </script>
        <?php endif; ?>
      <?php endif; ?>

      <!-- DATE RANGE & VIEW TYPE FILTER CARD -->
      <?php require_once __DIR__ . '/components/filter_bar.php'; ?>

      <!-- MAIN TABLE CARD -->
      <div class="card shadow-sm border-0" style="border-radius: 16px;">
        <div class="card-header bg-white border-bottom py-3 px-4">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h5 class="card-title font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped';">
                <i class="fas fa-table text-primary mr-2"></i> ຕາຕະລາງລາຍການສິນຄ້າຮັບເຂົ້າ 
                <!-- <small class="text-primary font-weight-bold">(<?php echo ($view_type === 'bill') ? 'ສະແດງຕາມໃບບິນ' : 'ສະແດງລາຍລະອຽດ'; ?>)</small> -->
              </h5>
            </div>
            <div class="col-md-6 text-right d-flex align-items-center justify-content-end">
              <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 0.9rem; height: 34px; line-height: 18px;">
                ລວມ <?php echo number_format($total_records); ?> <?php echo ($view_type === 'bill') ? 'ໃບບິນ' : 'ລາຍການ'; ?>
              </span>
            </div>
          </div>
        </div>

        <div class="card-body p-4">
          <!-- Search & Page Size Bar -->
          <div class="row align-items-center mb-3">
            <div class="col-md-4 mb-2 mb-md-0">
              <div class="d-flex align-items-center">
                <!-- <span class="mr-2 font-weight-bold text-muted" style="font-size: 0.88rem;">ສະແດງ:</span> -->
                <select id="pageSizeSelect" class="form-control form-control-sm" style="width: 100px; height: 38px; border-radius: 6px;" onchange="changePageSize(this.value)">
                  <option value="10" selected>10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
              </div>
            </div>

            <div class="col-md-8 text-md-right">
              <div class="input-group input-group-sm ml-md-auto" style="max-width: 340px;">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-white border-right-0" style="border-radius: 6px 0 0 6px;"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" id="import_search" class="form-control border-left-0" placeholder="ຄົ້ນຫາ ໃບບິນ, ຊື່ສິນຄ້າ, ຜູ້ສະໜອງ..." onkeyup="filterImportHistory()" style="border-radius: 0 6px 6px 0; height: 36px;">
              </div>
            </div>
          </div>

          <!-- TABLE RENDER: VIEW TYPE == 'bill' OR 'detail' -->
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              
              <?php if ($view_type === 'bill'): ?>
                <!-- HEADER FOR MODE 1: ຕາມບິນ (Blue Header) -->
                <thead class=" font-weight-bold">
                  <tr>
                    <th class="text-center" style="width: 50px;">ລຳດັບ</th>
                    <th class="text-center">ວັນທີຮັບເຂົ້າ</th>
                    <th class="text-center">ເລກທີໃບບິນ</th>
                    <th>ຜູ້ສະໜອງ</th>
                    <th class="text-center">ຈຳນວນລາຍການ</th>
                    <th class="text-right">ມູນຄ່າທຶນລວມ</th>
                    <th>ຜູ້ບັນທຶກ</th>
                    <th>ໝາຍເຫດ</th>
                    <th class="text-center" style="width: 120px;">ຈັດການ</th>
                  </tr>
                </thead>
                <tbody id="historyTableBody">
                  <?php if (empty($importList)): ?>
                    <tr>
                      <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-box-open fa-3x d-block mb-2 text-muted" style="opacity: 0.4;"></i>
                        ບໍ່ມີລາຍການໃບບິນຮັບເຂົ້າໃນຊ່ວງວັນທີນີ້
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php $idx = 1; foreach ($importList as $row): ?>
                      <?php 
                        $importDate = !empty($row['import_date']) ? date('d/m/Y', strtotime($row['import_date'])) : '-';
                        $creatorName = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
                        if (empty($creatorName)) $creatorName = $row['username'] ?? 'Admin';

                        $searchStr = strtolower(($row['invoice_number'] ?? '') . ' ' . ($row['supplier_name'] ?? '') . ' ' . ($row['notes'] ?? '') . ' ' . $creatorName);
                        $hasMov = !empty($row['has_movement']);
                      ?>
                      <tr class="import-row" data-search="<?php echo htmlspecialchars($searchStr); ?>">
                        <td class="align-middle text-center text-muted font-weight-bold row-index"><?php echo $idx++; ?></td>
                        
                        <td class="align-middle text-center text-secondary font-weight-bold" style="font-size: 0.9rem;">
                          <i class="fas fa-calendar-alt text-primary mr-1"></i> <?php echo $importDate; ?>
                        </td>

                        <td class="align-middle text-center font-weight-bold">
                          <span class="invoice-badge"><?php echo htmlspecialchars($row['invoice_number']); ?></span>
                        </td>

                        <td class="align-middle font-weight-bold text-dark">
                          <?php if (!empty($row['supplier_name'])): ?>
                            <i class="fas fa-building text-secondary mr-1"></i> <?php echo htmlspecialchars($row['supplier_name']); ?>
                          <?php else: ?>
                            <span class="text-muted font-weight-normal">-</span>
                          <?php endif; ?>
                        </td>

                        <td class="align-middle text-center font-weight-bold text-info">
                          <span class="badge badge-info px-2 py-1" style="font-size: 0.88rem;">
                            <?php echo number_format($row['item_count']); ?> ລາຍການ
                          </span>
                        </td>

                        <td class="align-middle text-right font-weight-bold text-success" style="font-size: 1rem;">
                          <?php echo number_format($row['total_cost']); ?> ₭
                        </td>

                        <td class="align-middle text-secondary font-weight-bold" style="font-size: 0.88rem;">
                          <i class="fas fa-user-circle mr-1 text-muted"></i> <?php echo htmlspecialchars($creatorName); ?>
                        </td>

                        <td class="align-middle text-muted" style="font-size: 0.88rem;">
                          <?php echo !empty($row['notes']) ? htmlspecialchars($row['notes']) : '-'; ?>
                        </td>

                        <td class="text-center align-middle">
                          <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info btn-view-bill" title="ເບິ່ງລາຍລະອຽດສິນຄ້າໃນບິນ" data-id="<?php echo $row['import_id']; ?>" data-invoice="<?php echo htmlspecialchars($row['invoice_number']); ?>" data-supplier="<?php echo htmlspecialchars($row['supplier_name'] ?? ''); ?>" data-date="<?php echo $importDate; ?>" data-cost="<?php echo floatval($row['total_cost']); ?>">
                              <i class="fas fa-eye"></i>
                            </button>

                            <a href="print_import.php?import_id=<?php echo $row['import_id']; ?>" target="_blank" class="btn btn-outline-primary" title="ພິມໃບບິນ">
                              <i class="fas fa-print"></i>
                            </a>

                            <?php if (!$hasMov): ?>
                              <button type="button" class="btn btn-outline-danger btn-delete-master-bill" title="ລົບບິນນີ້" data-id="<?php echo $row['import_id']; ?>" data-invoice="<?php echo htmlspecialchars($row['invoice_number']); ?>">
                                <i class="fas fa-trash-alt"></i>
                              </button>
                            <?php else: ?>
                              <button type="button" class="btn btn-outline-secondary disabled" title="ບໍ່ສາມາດລົບໄດ້ ເນື່ອງຈາກສິນຄ້າມີການເຄື່ອນໄຫວແລ້ວ">
                                <i class="fas fa-lock"></i>
                              </button>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>

              <?php else: ?>
                <!-- HEADER FOR MODE 2: ລາຍລະອຽດ (Blue Header) -->
                <thead class="bg-primary text-white font-weight-bold">
                  <tr>
                    <th class="text-center" style="width: 50px;">ລຳດັບ</th>
                    <th class="text-center">ວັນທີຮັບເຂົ້າ</th>
                    <th class="text-center">ເລກທີໃບບິນ</th>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th class="text-center">ຈຳນວນຮັບເຂົ້າ</th>
                    <th class="text-center">ຈຳນວນຍ່ອຍລວມ</th>
                    <th class="text-right">ມູນຄ່າທຶນລວມ</th>
                    <th class="text-center">ວັນໝົດອາຍຸ</th>
                    <th>ຜູ້ບັນທຶກ</th>
                    <th class="text-center" style="width: 110px;">ຈັດການ</th>
                  </tr>
                </thead>
                <tbody id="historyTableBody">
                  <?php if (empty($importList)): ?>
                    <tr>
                      <td colspan="10" class="text-center text-muted py-5">
                        <i class="fas fa-box-open fa-3x d-block mb-2 text-muted" style="opacity: 0.4;"></i>
                        ບໍ່ມີລາຍການສິນຄ້າຮັບເຂົ້າໃນຊ່ວງວັນທີນີ້
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php $idx = 1; foreach ($importList as $row): ?>
                      <?php 
                        $importDate = !empty($row['import_date']) ? date('d/m/Y', strtotime($row['import_date'])) : '-';
                        
                        // Expiry badge evaluation
                        $expBadge = '<span class="text-muted">-</span>';
                        if (!empty($row['expiry_date'])) {
                            $expTime = strtotime($row['expiry_date']);
                            $nowTime = time();
                            $daysDiff = floor(($expTime - $nowTime) / (60 * 60 * 24));
                            $formattedExp = date('d/m/Y', $expTime);

                            if ($daysDiff < 0) {
                                $expBadge = '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> ໝົດອາຍຸ (' . $formattedExp . ')</span>';
                            } elseif ($daysDiff <= 30) {
                                $expBadge = '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> ໃກ້ໝົດອາຍຸ (' . $formattedExp . ')</span>';
                            } else {
                                $expBadge = '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> ' . $formattedExp . '</span>';
                            }
                        }

                        $creatorName = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
                        if (empty($creatorName)) $creatorName = $row['username'] ?? 'Admin';

                        $searchStr = strtolower(($row['invoice_number'] ?? '') . ' ' . ($row['product_name'] ?? '') . ' ' . ($row['supplier_name'] ?? '') . ' ' . $creatorName);

                        // Stock movement check
                        $batchRem     = isset($row['batch_rem_qty']) ? intval($row['batch_rem_qty']) : null;
                        $batchInit    = isset($row['batch_initial_qty']) ? intval($row['batch_initial_qty']) : null;
                        $productQty   = intval($row['current_product_qty']);
                        $totalBaseQty = intval($row['total_base_qty']);

                        $hasMovement = false;
                        if ($batchRem !== null && $batchInit !== null && $batchRem < $batchInit) {
                            $hasMovement = true;
                        } elseif ($productQty < $totalBaseQty) {
                            $hasMovement = true;
                        }
                      ?>
                      <tr class="import-row" data-search="<?php echo htmlspecialchars($searchStr); ?>">
                        <td class="align-middle text-center text-muted font-weight-bold row-index"><?php echo $idx++; ?></td>
                        
                        <td class="align-middle text-center text-secondary font-weight-bold" style="font-size: 0.88rem;">
                          <?php echo $importDate; ?>
                        </td>

                        <td class="align-middle text-center font-weight-bold">
                          <span class="invoice-badge"><?php echo htmlspecialchars($row['invoice_number']); ?></span>
                        </td>

                        <td class="align-middle font-weight-bold text-dark">
                          <?php echo htmlspecialchars($row['product_name']); ?>
                          <?php if (!empty($row['supplier_name'])): ?>
                            <small class="text-muted d-block font-weight-normal"><i class="fas fa-store mr-1"></i> <?php echo htmlspecialchars($row['supplier_name']); ?></small>
                          <?php endif; ?>
                        </td>

                        <td class="align-middle text-center font-weight-bold text-dark">
                          <?php echo number_format($row['quantity']); ?> <?php echo htmlspecialchars($row['unit_name']); ?>
                        </td>

                        <td class="align-middle text-center font-weight-bold text-info">
                          <?php echo number_format($row['total_base_qty']); ?> <?php echo htmlspecialchars($row['base_unit'] ?: 'ອັນ'); ?>
                        </td>

                        <td class="align-middle text-right font-weight-bold text-primary">
                          <?php echo number_format($row['total_cost']); ?> ₭
                        </td>

                        <td class="align-middle text-center">
                          <?php echo $expBadge; ?>
                        </td>

                        <td class="align-middle text-secondary font-weight-bold" style="font-size: 0.88rem;">
                          <i class="fas fa-user-circle mr-1 text-muted"></i> <?php echo htmlspecialchars($creatorName); ?>
                        </td>

                        <td class="text-center align-middle">
                          <?php $rowJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>
                          <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-warning btn-edit-import" title="ແກ້ໄຂ" data-id="<?php echo $row['import_detail_id']; ?>" data-json="<?php echo $rowJson; ?>">
                              <i class="fas fa-edit"></i>
                            </button>
                            <?php if (!$hasMovement): ?>
                              <button type="button" class="btn btn-outline-danger btn-delete-import" title="ຍົກເລີກ/ລົບບິນນີ້" data-id="<?php echo $row['import_detail_id']; ?>" data-invoice="<?php echo htmlspecialchars($row['invoice_number']); ?>" data-name="<?php echo htmlspecialchars($row['product_name']); ?>">
                                <i class="fas fa-trash-alt"></i>
                              </button>
                            <?php else: ?>
                              <button type="button" class="btn btn-outline-secondary disabled" title="ບໍ່ສາມາດຍົກເລີກໄດ້ ເນື່ອງຈາກສິນຄ້າມີການເຄື່ອນໄຫວແລ້ວ">
                                <i class="fas fa-lock"></i>
                              </button>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              <?php endif; ?>

            </table>
          </div>

          <!-- Pagination Bar -->
          <div class="d-flex flex-column flex-md-row align-items-center justify-content-between pt-4 px-2">
            <!-- <div class="text-muted font-weight-bold mb-3 mb-md-0" style="font-size: 0.9rem;">
              ສະແດງ <span id="page_info_start" class="text-primary font-weight-bold">0</span> ຫາ <span id="page_info_end" class="text-primary font-weight-bold">0</span> ຈາກທັງໝົດ <span id="page_info_total" class="text-dark font-weight-bold">0</span> ລາຍການ
            </div> -->

            <div class="d-flex justify-content-end ml-md-auto">
              <ul class="pagination pagination-circle mb-0" id="importPagination">
              </ul>
            </div>
          </div>

        </div>
      </div>

    </div>
  </section>
</div>

<!-- MODAL: EDIT IMPORT DETAIL -->
<div class="modal fade" id="editImportModal" tabindex="-1" role="dialog" aria-labelledby="editImportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title font-weight-bold" id="editImportModalLabel" style="font-family: 'Noto Sans Lao Looped';">
          <i class="fas fa-edit mr-2"></i> ແກ້ໄຂຂໍ້ມູນການຮັບສິນຄ້າເຂົ້າ
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="action" value="edit_import_detail">
        <input type="hidden" name="import_detail_id" id="edit_import_detail_id">

        <div class="modal-body p-4">
          <div class="alert alert-info py-2 px-3 mb-3" style="border-radius: 8px;">
            <small class="font-weight-bold d-block text-primary">
              <i class="fas fa-receipt mr-1"></i> ໃບບິນ: <span id="edit_invoice_label" class="text-dark"></span>
            </small>
            <small class="font-weight-bold d-block text-dark mt-1" style="font-size: 0.95rem;" id="edit_product_name_label"></small>
          </div>

          <!-- Supplier Name -->
          <div class="form-group">
            <label class="form-label font-weight-bold text-dark">ຜູ້ສະໜອງສິນຄ້າ:</label>
            <input type="text" name="supplier_name" id="edit_supplier_name" class="form-control" placeholder="ຊື່ຜູ້ສະໜອງ/ຮ້ານຄ້າ" style="border-radius: 8px;">
          </div>

          <div class="row">
            <!-- Unit Selector -->
            <div class="col-md-6 form-group">
              <label class="form-label font-weight-bold text-dark">ຫົວໜ່ວຍຮັບເຂົ້າ:</label>
              <select name="unit_key" id="edit_unit_key" class="form-control font-weight-bold" style="border-radius: 8px;">
              </select>
            </div>

            <!-- Quantity -->
            <div class="col-md-6 form-group">
              <label class="form-label font-weight-bold text-dark">ຈຳນວນຮັບເຂົ້າ:</label>
              <input type="number" name="quantity" id="edit_quantity" class="form-control font-weight-bold" min="1" required style="border-radius: 8px;">
            </div>
          </div>

          <div class="row">
            <!-- Cost Price -->
            <div class="col-md-6 form-group">
              <label class="form-label font-weight-bold text-dark">ລາຄາຊື້:</label>
              <input type="number" step="any" name="cost_price" id="edit_cost_price" class="form-control font-weight-bold" min="0" required style="border-radius: 8px;">
            </div>

            <!-- Expiry Date -->
            <div class="col-md-6 form-group">
              <label class="form-label font-weight-bold text-dark">ວັນໝົດອາຍຸ:</label>
              <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control font-weight-bold" style="border-radius: 8px;">
            </div>
          </div>

          <!-- Notes -->
          <div class="form-group mb-0">
            <label class="form-label font-weight-bold text-dark">ໝາຍເຫດ:</label>
            <textarea name="notes" id="edit_notes" class="form-control" rows="2" placeholder="ໝາຍເຫດເພີ່ມເຕີມ" style="border-radius: 8px;"></textarea>
          </div>
        </div>

        <div class="modal-footer bg-light py-3 px-4 border-top">
          <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 8px;">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 8px;">
            <i class="fas fa-save mr-1"></i> ອັບເດດ
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- MODAL: VIEW BILL ITEMS (ສຳລັບໂໝດ ຕາມບິນ) -->
<div class="modal fade" id="billDetailsModal" tabindex="-1" role="dialog" aria-labelledby="billDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title font-weight-bold" id="billDetailsModalLabel" style="font-family: 'Noto Sans Lao Looped';">
          <i class="fas fa-file-invoice mr-2"></i> ລາຍລະອຽດສິນຄ້າໃນໃບບິນ: <span id="modal_invoice_no" class="badge badge-light text-primary px-2 py-1 ml-1" style="font-size: 1.05rem;"></span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between bg-light p-3 rounded mb-3 border">
          <div>
            <span class="text-muted font-weight-bold d-block" style="font-size: 0.85rem;">ຜູ້ສະໜອງ:</span>
            <span id="modal_supplier" class="font-weight-bold text-dark" style="font-size: 0.98rem;">-</span>
          </div>
          <div>
            <span class="text-muted font-weight-bold d-block" style="font-size: 0.85rem;">ວັນທີຮັບເຂົ້າ:</span>
            <span id="modal_import_date" class="font-weight-bold text-primary" style="font-size: 0.98rem;">-</span>
          </div>
          <div>
            <span class="text-muted font-weight-bold d-block" style="font-size: 0.85rem;">ມູນຄ່າທຶນລວມ:</span>
            <span id="modal_total_cost" class="font-weight-bold text-success" style="font-size: 1.1rem;">0 ₭</span>
          </div>
        </div>

        <div class="table-responsive border rounded" style="max-height: 350px; overflow-y: auto;">
          <table class="table table-hover mb-0 align-middle">
            <thead class="bg-light text-dark font-weight-bold" style="position: sticky; top: 0; z-index: 5;">
              <tr>
                <th class="text-center" style="width: 50px;">ລຳດັບ</th>
                <th>ຊື່ສິນຄ້າ</th>
                <th class="text-center">ຈຳນວນຮັບເຂົ້າ</th>
                <th class="text-right">ລາຄາຊື້</th>
                <th class="text-right">ລວມ</th>
              </tr>
            </thead>
            <tbody id="bill_items_modal_tbody">
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-end">
        <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 6px;">ປິດ</button>
      </div>
    </div>
  <!-- MODAL: EDIT MASTER IMPORT BILL -->
<div class="modal fade" id="editMasterBillModal" tabindex="-1" role="dialog" aria-labelledby="editMasterBillModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header bg-warning text-dark py-3">
        <h5 class="modal-title font-weight-bold" id="editMasterBillModalLabel" style="font-family: 'Noto Sans Lao Looped';">
          <i class="fas fa-edit mr-2"></i> ແກ້ໄຂຂໍ້ມູນໃບບິນຮັບເຂົ້າ: <span id="edit_master_invoice_label" class="badge badge-light text-dark px-2 py-1"></span>
        </h5>
        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="action" value="edit_master_import">
        <input type="hidden" name="import_id" id="edit_master_import_id">

        <div class="modal-body p-4">
          <!-- Supplier Name -->
          <div class="form-group">
            <label class="form-label font-weight-bold text-dark">ຜູ້ສະໜອງສິນຄ້າ:</label>
            <input type="text" name="supplier_name" id="edit_master_supplier" class="form-control font-weight-bold" placeholder="ຊື່ຜູ້ສະໜອງ/ຮ້ານຄ້າ" style="border-radius: 8px; height: 42px;">
          </div>

          <!-- Import Date -->
          <div class="form-group">
            <label class="form-label font-weight-bold text-dark">ວັນທີຮັບເຂົ້າ:</label>
            <input type="date" name="import_date" id="edit_master_date" class="form-control font-weight-bold" required style="border-radius: 8px; height: 42px;">
          </div>

          <!-- Notes -->
          <div class="form-group mb-0">
            <label class="form-label font-weight-bold text-dark">ໝາຍເຫດ:</label>
            <textarea name="notes" id="edit_master_notes" class="form-control" rows="2" placeholder="ໝາຍເຫດເພີ່ມເຕີມສຳລັບໃບບິນນີ້" style="border-radius: 8px;"></textarea>
          </div>
        </div>

        <div class="modal-footer bg-light py-3 px-4 border-top">
          <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 8px;">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-warning font-weight-bold px-4 shadow-sm" style="border-radius: 6px;">
            <i class="fas fa-save mr-1"></i> ອັບເດດ
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/components/modal_select_product.php'; ?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<script>
  var PRODUCTS_LIST = <?php echo json_encode($products); ?>;
  var PRODUCTS_UNITS = <?php echo json_encode($product_units_map); ?>;
  var BILL_ITEMS_MAP = <?php echo json_encode($billItemsMap); ?>;
  
  var currentPage = 1;
  var pageSize = 10;
  var filteredRows = [];

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  $(document).ready(function() {
    filterImportHistory();

    $(document).on('click', '.btn-view-bill', function(e) {
      e.preventDefault();
      var id = $(this).data('id');
      var invoice = $(this).data('invoice');
      var supplier = $(this).data('supplier');
      var date = $(this).data('date');
      var cost = parseFloat($(this).data('cost') || 0);
      openBillDetailsModal(id, invoice, supplier, date, cost);
    });

    $(document).on('click', '.btn-edit-master-bill', function(e) {
      e.preventDefault();
      var id = $(this).data('id');
      var invoice = $(this).data('invoice');
      var supplier = $(this).data('supplier');
      var date = $(this).data('date');
      var notes = $(this).data('notes');

      $('#edit_master_import_id').val(id);
      $('#edit_master_invoice_label').text(invoice);
      $('#edit_master_supplier').val(supplier || '');
      $('#edit_master_date').val(date || '');
      $('#edit_master_notes').val(notes || '');

      $('#editMasterBillModal').modal('show');
    });

    $(document).on('click', '.btn-delete-master-bill', function(e) {
      e.preventDefault();
      var id = $(this).data('id');
      var invoice = $(this).data('invoice');
      confirmDeleteMasterBill(id, invoice);
    });

    $(document).on('click', '.btn-edit-import, .btn-edit-import-detail', function(e) {
      e.preventDefault();
      var rawJson = $(this).attr('data-json') || $(this).data('json');
      var rowObj = null;
      if (typeof rawJson === 'object') {
        rowObj = rawJson;
      } else {
        try { rowObj = JSON.parse(rawJson); } catch(err) { console.error(err); }
      }
      if (rowObj) {
        if (!rowObj.invoice_number) {
            rowObj.invoice_number = $(this).data('invoice'); // Fallback if missing
        }
        openEditImportModal(rowObj);
      }
    });

    $(document).on('click', '.btn-delete-import, .btn-delete-import-detail', function(e) {
      e.preventDefault();
      var id = $(this).data('id');
      var invoice = $(this).data('invoice');
      var name = $(this).data('name');
      confirmDeleteImport(id, invoice, name);
    });
  });

  function filterImportHistory() {
    var query = $('#import_search').val().trim().toLowerCase();
    var rows = document.querySelectorAll('#historyTableBody .import-row');
    filteredRows = [];

    rows.forEach(function(row) {
      var searchStr = row.getAttribute('data-search') || '';
      if (!query || searchStr.indexOf(query) !== -1) {
        filteredRows.push(row);
      } else {
        row.style.display = 'none';
      }
    });

    currentPage = 1;
    renderHistoryTable();
  }

  function changePageSize(val) {
    pageSize = parseInt(val);
    currentPage = 1;
    renderHistoryTable();
  }

  function renderHistoryTable() {
    var totalRows = filteredRows.length;
    var totalPages = Math.ceil(totalRows / pageSize) || 1;

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    var startIdx = (currentPage - 1) * pageSize;
    var endIdx = startIdx + pageSize;

    document.querySelectorAll('#historyTableBody .import-row').forEach(function(r) {
      r.style.display = 'none';
    });

    for (var i = startIdx; i < endIdx && i < totalRows; i++) {
      var r = filteredRows[i];
      r.style.display = '';
      var cellIndex = r.querySelector('.row-index');
      if (cellIndex) cellIndex.textContent = i + 1;
    }

    $('#page_info_start').text(totalRows > 0 ? startIdx + 1 : 0);
    $('#page_info_end').text(Math.min(endIdx, totalRows));
    $('#page_info_total').text(totalRows);

    renderHistoryPagination(totalPages);
  }

  function renderHistoryPagination(totalPages) {
    var container = $('#importPagination');
    container.empty();

    if (totalPages < 1) totalPages = 1;

    var prevDisabled = (currentPage === 1) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToHistoryPage(${currentPage - 1})">
          <i class="fas fa-chevron-left"></i>
        </a>
      </li>
    `);

    var maxButtons = 5;
    var startPage = Math.max(1, currentPage - 2);
    var endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) {
      startPage = Math.max(1, endPage - maxButtons + 1);
    }

    if (startPage > 1) {
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToHistoryPage(1)">1</a></li>`);
      if (startPage > 2) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
    }

    for (var p = startPage; p <= endPage; p++) {
      var activeClass = (p === currentPage) ? 'active' : '';
      container.append(`
        <li class="page-item ${activeClass}">
          <a class="page-link" href="javascript:void(0)" onclick="goToHistoryPage(${p})">${p}</a>
        </li>
      `);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToHistoryPage(${totalPages})">${totalPages}</a></li>`);
    }

    var nextDisabled = (currentPage === totalPages) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToHistoryPage(${currentPage + 1})">
          <i class="fas fa-chevron-right"></i>
        </a>
      </li>
    `);
  }

  function goToHistoryPage(page) {
    currentPage = page;
    renderHistoryTable();
  }

  var currentModalImportId = 0;

  function exportToExcel() {
    var table = document.querySelector('.table-responsive table');
    if (!table) return;

    var cloneTable = table.cloneNode(true);

    // Remove action column (last column) from header and body
    cloneTable.querySelectorAll('tr').forEach(function(row) {
      if (row.children.length > 0) {
        row.removeChild(row.lastElementChild);
      }
    });

    var tableHtml = cloneTable.outerHTML;

    var excelDoc = `
      <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
      <head>
        <meta charset="utf-8">
        <!--[if gte mso 9]>
        <xml>
          <x:ExcelWorkbook>
            <x:ExcelWorksheets>
              <x:ExcelWorksheet>
                <x:Name>ລາຍການສິນຄ້າຮັບເຂົ້າ</x:Name>
                <x:WorksheetOptions>
                  <x:DisplayGridlines/>
                </x:WorksheetOptions>
              </x:ExcelWorksheet>
            </x:ExcelWorksheets>
          </x:ExcelWorkbook>
        </xml>
        <![endif]-->
        <style>
          body, table, td, th {
            font-family: 'Phetsarath OT', 'Noto Sans Lao', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            font-size: 11pt !important;
          }
          table {
            border-collapse: collapse !important;
            width: 100% !important;
          }
          th {
            background-color: #007bff !important;
            color: #ffffff !important;
            font-weight: bold !important;
            text-align: center !important;
            padding: 10px 14px !important;
            border: 1px solid #0056b3 !important;
            height: 36px !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
          }
          td {
            padding: 8px 12px !important;
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
          }
          .badge {
            background: none !important;
            border: none !important;
            color: inherit !important;
          }
        </style>
      </head>
      <body>
        ${tableHtml}
      </body>
      </html>
    `;

    var blob = new Blob(['\ufeff' + excelDoc], {
      type: 'application/vnd.ms-excel;charset=utf-8'
    });

    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'ລາຍການສິນຄ້າຮັບເຂົ້າ_' + new Date().toISOString().slice(0, 10) + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  function openBillDetailsModal(importId, invoiceNo, supplier, importDate, totalCost) {
    currentModalImportId = importId;
    $('#modal_invoice_no').text(invoiceNo);
    $('#modal_supplier').text(supplier || '-');
    $('#modal_import_date').text(importDate);
    $('#modal_total_cost').text(totalCost.toLocaleString() + ' ₭');

    var items = BILL_ITEMS_MAP[importId] || [];
    var tbody = $('#bill_items_modal_tbody');
    tbody.empty();

    if (items.length === 0) {
      tbody.html('<tr><td colspan="6" class="text-center text-muted py-4">ບໍ່ມີລາຍການສິນຄ້າໃນໃບບິນນີ້</td></tr>');
    } else {
      items.forEach(function(item, idx) {
        var actionCol = '';
        if (parseInt(item.has_movement) === 1) {
          actionCol = '<i class="fas fa-lock text-muted" title="ບໍ່ສາມາດແກ້ໄຂໄດ້ (ສິນຄ້າເຄື່ອນໄຫວແລ້ວ)"></i>';
        } else {
          var safeJson = JSON.stringify(item).replace(/'/g, "&apos;");
          actionCol = `
            <button class="btn btn-sm btn-outline-warning rounded-circle px-2 py-1 mr-1 btn-edit-import-detail" data-invoice="${invoiceNo}" data-json='${safeJson}' title="ແກ້ໄຂ"><i class="fas fa-edit"></i></button>
            <button class="btn btn-sm btn-outline-danger rounded-circle px-2 py-1 btn-delete-import-detail" data-id="${item.import_detail_id}" data-invoice="${invoiceNo}" data-name="${escapeHtml(item.product_name)}" title="ລົບ"><i class="fas fa-trash"></i></button>
          `;
        }

        tbody.append(`
          <tr>
            <td class="text-center font-weight-bold text-muted">${idx + 1}</td>
            <td class="align-middle">
              <span class="font-weight-bold text-dark d-block">${escapeHtml(item.product_name)}</span>
              ${item.barcode ? '<small class="text-muted"><i class="fas fa-barcode mr-1"></i> ' + escapeHtml(item.barcode) + '</small>' : ''}
            </td>
            <td class="text-center align-middle font-weight-bold text-dark">${item.quantity.toLocaleString()} ${escapeHtml(item.unit_name)}</td>
            <td class="text-right align-middle font-weight-bold text-dark">${parseFloat(item.cost_price).toLocaleString()} ₭</td>
            <td class="text-right align-middle font-weight-bold text-primary">${parseFloat(item.total_cost).toLocaleString()} ₭</td>
            <td class="text-center align-middle">${actionCol}</td>
          </tr>
        `);
      });
    }

    $('#billDetailsModal').modal('show');
  }

  function printCurrentModalBill() {
    if (currentModalImportId > 0) {
      window.open('print_import.php?import_id=' + currentModalImportId, '_blank');
    }
  }

  function openEditImportModal(row) {
    $('#billDetailsModal').modal('hide'); // Fix z-index stacking by hiding the background modal

    $('#edit_import_detail_id').val(row.import_detail_id);
    $('#edit_invoice_label').text(row.invoice_number);
    $('#edit_product_name_label').text(row.product_name);
    $('#edit_supplier_name').val(row.supplier_name || '');
    $('#edit_quantity').val(row.quantity);
    $('#edit_cost_price').val(row.cost_price);
    $('#edit_expiry_date').val(row.expiry_date || '');
    $('#edit_notes').val(row.notes || '');

    var unitSelect = $('#edit_unit_key');
    unitSelect.empty();

    var baseUnit = row.base_unit || 'ອັນ';
    unitSelect.append(`<option value="base">${escapeHtml(baseUnit)} (1)</option>`);

    var productId = row.product_id;
    if (PRODUCTS_UNITS[productId]) {
      PRODUCTS_UNITS[productId].forEach(function(u) {
        var isSel = (row.unit_name === u.unit_name && parseInt(row.multiplier) === parseInt(u.multiplier)) ? 'selected' : '';
        unitSelect.append(`<option value="${u.id}" ${isSel}>${escapeHtml(u.unit_name)} (${u.multiplier})</option>`);
      });
    }

    $('#editImportModal').modal('show');
  }

  function confirmDeleteImport(importDetailId, invoiceNumber, productName) {
    Swal.fire({
      title: 'ຍົກເລີກການຮັບສິນຄ້າເຂົ້າ?',
      text: "ທ່ານຕ້ອງການຍົກເລີກໃບບິນທີ [" + invoiceNumber + "] ສິນຄ້າ (" + productName + ") ແທ້ບໍ? ສະຕັອກຈະຖືກປັບຫຼຸດລົງຄືນ!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'ລົບບິນນີ້',
      cancelButtonText: 'ຍົກເລີກ'
    }).then((result) => {
      if (result.isConfirmed) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_import_detail';
        form.appendChild(actionInput);

        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'import_detail_id';
        idInput.value = importDetailId;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
      }
    });
  }

  function confirmDeleteMasterBill(importId, invoiceNumber) {
    Swal.fire({
      title: 'ລົບໃບບິນຮັບເຂົ້າສິນຄ້າ?',
      text: "ທ່ານຕ້ອງການລົບໃບບິນທີ [" + invoiceNumber + "] ທັງໝົດແທ້ບໍ? ສິນຄ້າທັງໝົດໃນໃບບິນນີ້ຈະຖືກປັບຫຼຸດສະຕັອກຄືນ!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'ລົບບິນນີ້',
      cancelButtonText: 'ຍົກເລີກ'
    }).then((result) => {
      if (result.isConfirmed) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_master_import';
        form.appendChild(actionInput);

        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'import_id';
        idInput.value = importId;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
      }
    });
  }

  // ====== MODAL PAGINATION & SEARCH ======
  var modalCurrentPage = 1;
  var modalPageSize = 10;
  var modalFilteredRows = [];

  function filterModalProducts() {
    var q = $('#modal_product_search').val().trim().toLowerCase();
    var cat = $('#modal_category_filter').val();
    var rows = document.querySelectorAll('.modal-product-row');
    modalFilteredRows = [];

    rows.forEach(function(r) {
      var sData = r.getAttribute('data-search') || '';
      var rCat = r.getAttribute('data-category') || '';

      var matchSearch = !q || sData.includes(q);
      var matchCat = !cat || rCat === cat;

      if (matchSearch && matchCat) {
        modalFilteredRows.push(r);
      } else {
        r.style.display = 'none';
      }
    });

    modalCurrentPage = 1;
    renderModalProductTable();
  }

  function changeModalPageSize(val) {
    modalPageSize = parseInt(val, 10) || 10;
    modalCurrentPage = 1;
    renderModalProductTable();
  }

  function renderModalProductTable() {
    var totalRows = modalFilteredRows.length;
    var totalPages = Math.ceil(totalRows / modalPageSize) || 1;

    if (modalCurrentPage > totalPages) modalCurrentPage = totalPages;
    if (modalCurrentPage < 1) modalCurrentPage = 1;

    var startIdx = (modalCurrentPage - 1) * modalPageSize;
    var endIdx = startIdx + modalPageSize;

    document.querySelectorAll('.modal-product-row').forEach(function(r) {
      r.style.display = 'none';
    });

    for (var i = startIdx; i < endIdx && i < totalRows; i++) {
      var r = modalFilteredRows[i];
      r.style.display = '';
    }

    $('#modal_page_start').text(totalRows > 0 ? startIdx + 1 : 0);
    $('#modal_page_end').text(Math.min(endIdx, totalRows));
    $('#modal_page_total').text(totalRows);

    renderModalPagination(totalPages);
  }

  function renderModalPagination(totalPages) {
    var container = $('#modalProductPagination');
    container.empty();

    if (totalPages < 1) totalPages = 1;

    var prevDisabled = (modalCurrentPage === 1) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToModalPage(${modalCurrentPage - 1})">
          <i class="fas fa-chevron-left"></i>
        </a>
      </li>
    `);

    var maxButtons = 5;
    var startPage = Math.max(1, modalCurrentPage - 2);
    var endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) {
      startPage = Math.max(1, endPage - maxButtons + 1);
    }

    if (startPage > 1) {
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToModalPage(1)">1</a></li>`);
      if (startPage > 2) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
    }

    for (var p = startPage; p <= endPage; p++) {
      var activeClass = (p === modalCurrentPage) ? 'active' : '';
      container.append(`
        <li class="page-item ${activeClass}">
          <a class="page-link" href="javascript:void(0)" onclick="goToModalPage(${p})">${p}</a>
        </li>
      `);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToModalPage(${totalPages})">${totalPages}</a></li>`);
    }

    var nextDisabled = (modalCurrentPage === totalPages) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToModalPage(${modalCurrentPage + 1})">
          <i class="fas fa-chevron-right"></i>
        </a>
      </li>
    `);
  }

  function goToModalPage(page) {
    modalCurrentPage = page;
    renderModalProductTable();
  }

  $('#productSelectModal').on('shown.bs.modal', function () {
    filterModalProducts();
    $('#modal_product_search').focus();
  });
</script>
