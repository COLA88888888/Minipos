<?php
session_start();
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../../config/db.php';

// Check authorization
if (empty($_SESSION['user_id']) || (!hasPermission('import_stock') && !hasPermission('stock') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// ====== HANDLE POST ACTIONS ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // --- 1. ACTION: IMPORT STOCK BILL (MULTI-ITEM OR SINGLE ITEM) ---
    if ($_POST['action'] === 'import_stock_bill') {
        $invoice_number = trim($_POST['invoice_number'] ?? '');
        $supplier_name  = trim($_POST['supplier_name'] ?? '');
        $raw_date       = trim($_POST['import_date'] ?? '');
        $import_date    = !empty($raw_date) ? $raw_date . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
        $notes          = trim($_POST['notes'] ?? '');
        $cart_json      = $_POST['cart_json'] ?? '[]';
        $items          = json_decode($cart_json, true);

        if ($invoice_number === '') {
            $maxImpId = (int)$pdo->query("SELECT IFNULL(MAX(import_id), 0) + 1 FROM imports")->fetchColumn();
            $invoice_number = str_pad($maxImpId, 6, '0', STR_PAD_LEFT);
        }

        if (!empty($items) && is_array($items)) {
            try {
                $pdo->beginTransaction();

                // Calculate bill total cost
                $total_bill_cost = 0;
                foreach ($items as $it) {
                    $q = intval($it['quantity'] ?? 0);
                    $c = floatval($it['cost_price'] ?? 0);
                    $total_bill_cost += ($q * $c);
                }

                // Insert master `imports` record with selected import_date
                $insImp = $pdo->prepare("INSERT INTO imports (invoice_number, supplier_name, import_date, total_cost, created_by, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $insImp->execute([$invoice_number, $supplier_name, $import_date, $total_bill_cost, $_SESSION['user_id'], $notes]);
                $import_id = $pdo->lastInsertId();

                $insertedCount = 0;
                foreach ($items as $item) {
                    $product_id   = intval($item['product_id'] ?? 0);
                    $unit_key     = trim($item['unit_key'] ?? 'base');
                    $quantity     = intval($item['quantity'] ?? 0);
                    $cost_price   = floatval($item['cost_price'] ?? 0);
                    $expiry_date  = !empty($item['expiry_date']) ? $item['expiry_date'] : null;

                    if ($product_id <= 0 || $quantity <= 0) continue;

                    // Fetch Product
                    $activeStoreId = getActiveStoreId($pdo);
                    $pStmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ? AND store_id = ? FOR UPDATE");
                    $pStmt->execute([$product_id, $activeStoreId]);
                    $product = $pStmt->fetch();

                    if (!$product) continue;

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

                    $total_base_qty = $quantity * $multiplier;
                    $total_cost     = $quantity * $cost_price;

                    // Insert into import_details
                    $insDet = $pdo->prepare("INSERT INTO import_details (import_id, product_id, unit_name, multiplier, quantity, total_base_qty, cost_price, total_cost, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $insDet->execute([
                        $import_id, $product_id, $unit_name, $multiplier,
                        $quantity, $total_base_qty, $cost_price, $total_cost, $expiry_date
                    ]);
                    $import_detail_id = $pdo->lastInsertId();

                    // Insert into product_batches
                    $insBatch = $pdo->prepare("INSERT INTO product_batches (product_id, import_detail_id, expiry_date, initial_qty, quantity) VALUES (?, ?, ?, ?, ?)");
                    $insBatch->execute([$product_id, $import_detail_id, $expiry_date, $total_base_qty, $total_base_qty]);

                    // Update product stock and buy price
                    $updProd = $pdo->prepare("UPDATE products SET qty = qty + ?, bprice = ? WHERE product_id = ? AND store_id = ?");
                    $updProd->execute([$total_base_qty, $cost_price, $product_id, $activeStoreId]);

                    $insertedCount++;
                }

                $pdo->commit();
                $message = "ບັນທຶກໃບບິນຮັບສິນຄ້າເຂົ້າເລກທີ $invoice_number ສຳເລັດ (ລວມ $insertedCount ລາຍການ)!";
                $message_type = 'success';
                logActivity($pdo, "ຮັບສິນຄ້າເຂົ້າ (ໃບບິນ)", "ໃບບິນ: $invoice_number, $insertedCount ລາຍການ, ລວມ: " . number_format($total_bill_cost) . " ₭");
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        } else {
            $message = 'ກະລຸນາເພີ່ມສິນຄ້າໃສ່ລາຍການຮັບເຂົ້າຢ່າງນ້ອຍ 1 ລາຍການ!';
            $message_type = 'danger';
        }
    }

    // --- 2. ACTION: DELETE/CANCEL IMPORT DETAIL ---
    elseif ($_POST['action'] === 'delete_import_detail') {
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
}

// Fetch categories for modal filter
$categories = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC")->fetchAll();

// Fetch products for current active store with categories & multi-units
$activeStoreId = getActiveStoreId($pdo);
$prodStmt = $pdo->prepare("
    SELECT p.product_id, p.product_name, p.barcode, p.unit, p.bprice, p.price, p.qty, p.category_id, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.store_id = ?
    ORDER BY p.product_name ASC
");
$prodStmt->execute([$activeStoreId]);
$products = $prodStmt->fetchAll();

$product_units_map = [];
$uRows = $pdo->query("SELECT * FROM product_units ORDER BY multiplier ASC")->fetchAll();
foreach ($uRows as $u) {
    $product_units_map[$u['product_id']][] = $u;
}

// Fetch Import History
$userStoreId = intval($_SESSION['store_id'] ?? 1);
$isAdmin = ($_SESSION['status'] ?? '') === 'ຜູ້ບໍລິຫານ' || strtolower($_SESSION['status'] ?? '') === 'admin' || ($_SESSION['user_id'] ?? 0) == 1;
$isMain = isMainBranch($pdo, $userStoreId);

$historyWhere = (!$isAdmin && !$isMain) ? " WHERE p.store_id = :store_id " : "";
$historyQuery = "
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
    {$historyWhere}
    ORDER BY id.import_detail_id DESC
";
$histStmt = $pdo->prepare($historyQuery);
if (!$isAdmin && !$isMain) {
    $histStmt->execute([':store_id' => $activeStoreId]);
} else {
    $histStmt->execute();
}
$importHistory = $histStmt->fetchAll();
$total_imports = count($importHistory);

// Next available auto invoice number (000001, 000002, 000003...)
$maxImpId = (int)$pdo->query("SELECT IFNULL(MAX(import_id), 0) + 1 FROM imports")->fetchColumn();
$next_invoice = str_pad($maxImpId, 6, '0', STR_PAD_LEFT);

// Current User Receiver Name
$receiverName = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
if (empty($receiverName)) $receiverName = $_SESSION['username'] ?? 'Admin';

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
            <i class="fas fa-truck-loading text-primary mr-2"></i> ຮັບສິນຄ້າເຂົ້າສະຕັອກ
          </h4>
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
                title: 'ບັນທຶກສຳເລັດ!',
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

      <!-- 1. DIRECT ENTRY FORM CARD (ຟອມບັນທຶກຮັບສິນຄ້າເຂົ້າ ໃນໜ້າຈໍເລີຍ) -->
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">

        <div class="card-body p-4">
          <form id="directImportForm" action="" method="POST">
            <input type="hidden" name="action" value="import_stock_bill">
            <input type="hidden" id="cart_json_input" name="cart_json" value="[]">

            <!-- Top Header Details Row: Invoice No, Date, Receiver, Supplier -->
            <div class="row bg-light p-3 rounded mb-3 border" style="border-color: #cbd5e1 !important;">
              <!-- 1. Auto Invoice Number -->
              <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">
                  ເລກທີໃບບິນຮັບເຂົ້າ <small class="text-muted"></small>
                </label>
                <input type="text" name="invoice_number" id="invoice_number_input" class="form-control font-weight-bold bg-white text-primary border-primary" value="<?php echo $next_invoice; ?>" readonly style="height: 42px; border-radius: 8px; cursor: not-allowed; font-size: 1.05rem;">
              </div>

              <!-- 2. Import Date (Editable Date Picker - No Time) -->
              <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">
                  ວັນທີຮັບເຂົ້າ <span class="text-danger">*</span>
                </label>
                <input type="date" name="import_date" id="import_date_input" class="form-control font-weight-bold" value="<?php echo date('Y-m-d'); ?>" required style="height: 42px; border-radius: 8px;">
              </div>

              <!-- 3. Receiver Name -->
              <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">
                  ຜູ້ບັນທຶກ / ຜູ້ຮັບເຂົ້າ
                </label>
                <input type="text" class="form-control bg-white font-weight-bold" value="<?php echo htmlspecialchars($receiverName); ?>" readonly style="height: 42px; border-radius: 8px;">
              </div>

              <!-- 4. Supplier Name -->
              <div class="col-md-3">
                <label class="font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">
                  ຊື່ຜູ້ສະໜອງ / ຮ້ານສົ່ງ
                </label>
                <input type="text" name="supplier_name" id="supplier_name_input" class="form-control" placeholder="ປ້ອນຊື່ຜູ້ສະໜອງ (ຖ້າມີ)" style="height: 42px; border-radius: 8px;">
              </div>
            </div>

            <!-- Barcode Scanning & Item Entry Row -->
            <div class="row align-items-end mb-3">
              <!-- Barcode / Search Box -->
              <div class="col-md-4 mb-3 mb-md-0">
                <label class="font-weight-bold text-dark mb-1">
                  ສະແກນບາໂຄ້ດ / ລະຫັດສິນຄ້າ <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input type="text" id="direct_barcode_input" class="form-control" placeholder="ສະແກນບາໂຄ້ດ ຫຼື ປ້ອນລະຫັດ..." autocomplete="off" style="height: 42px; border-radius: 8px 0 0 8px;" oninput="onDirectBarcodeChange()" onkeydown="onDirectBarcodeKeyDown(event)">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-primary font-weight-bold" data-toggle="modal" data-target="#productSelectModal" title="ເປີດປັອບອັບເລືອກສິນຄ້າ">
                      <i class="fas fa-search-plus mr-1"></i> ເລືອກສິນຄ້າ
                    </button>
                  </div>
                </div>
              </div>

              <!-- Quantity Input (Positioned directly next to Barcode Box) -->
              <div class="col-md-2 mb-3 mb-md-0">
                <label class="font-weight-bold text-dark mb-1">
                  ຈຳນວນ <span class="text-danger">*</span>
                </label>
                <input type="number" id="direct_qty" class="form-control font-weight-bold border-primary text-center" min="1" value="1" style="height: 42px; border-radius: 8px; font-size: 1.1rem;" onkeydown="onQtyKeyDown(event)">
              </div>

              <!-- Unit Select -->
              <div class="col-md-2 mb-3 mb-md-0">
                <label class="font-weight-bold text-dark mb-1">ຫົວໜ່ວຍ</label>
                <select id="direct_unit_key" class="form-control" style="height: 42px; border-radius: 8px;">
                  <option value="base">ຫົວໜ່ວຍ</option>
                </select>
              </div>

              <!-- Expiry Date -->
              <div class="col-md-2 mb-3 mb-md-0">
                <label class="font-weight-bold text-dark mb-1">ວັນໝົດອາຍຸ</label>
                <input type="date" id="direct_expiry_date" class="form-control" style="height: 42px; border-radius: 8px;">
              </div>

              <!-- Add Item Button -->
              <div class="col-md-2">
                <button type="button" class="btn btn-primary font-weight-bold btn-block shadow-sm" style="height: 42px; border-radius: 6px;" onclick="addCurrentItemToCart()">
                  <i class="fas fa-plus-circle mr-1"></i> ເພີ່ມລາຍການ
                </button>
              </div>
            </div>

            <!-- Active Product Selected Banner -->
            <div id="direct_matched_banner" class="p-2.5 px-3 rounded border d-none align-items-center justify-content-between mb-3" style="border-color: #93c5fd !important; background-color: #eff6ff !important;">
              <div>
                <span class="font-weight-bold text-dark d-block" id="direct_matched_name" style="font-size: 1rem;">-</span>
                <small class="text-muted">
                   ບາໂຄ້ດ: <span id="direct_matched_barcode" class="font-weight-bold text-primary mr-2">-</span>
                   ລາຄາຊື້: <span id="direct_matched_bprice" class="font-weight-bold text-danger mr-2">0 ₭</span>
                   ສະຕັອກປັດຈຸບັນ: <span id="direct_matched_stock" class="font-weight-bold text-success">0</span>
                </small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetDirectSelection()"><i class="fas fa-times"></i></button>
            </div>

            <!-- LIVE IMPORT ITEMS TABLE (ຕາຕະລາງລາຍການຮັບເຂົ້າ ໃນໜ້າຈໍ) -->
            <div class="table-responsive border rounded mb-3 shadow-sm" style="border-radius: 12px; overflow: hidden; background-color: #ffffff;">
              <table class="table table-hover mb-0 align-middle">
                <thead class="font-weight-bold">
                  <tr>
                    <th class="text-center" style="width: 50px;">ລຳດັບ</th>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th class="text-center" style="width: 160px;">ຈຳນວນຮັບເຂົ້າ</th>
                    <th class="text-right" style="width: 160px;">ລາຄາຊື້</th>
                    <th class="text-right" style="width: 180px;">ລວມ</th>
                    <th class="text-center" style="width: 80px;">ຈັດການ</th>
                  </tr>
                </thead>
                <tbody id="cart_table_body">
                  <tr id="empty_cart_row">
                    <td colspan="6" class="text-center text-muted py-5">
                      <i class="fas fa-box-open fa-3x d-block mb-2 text-muted" style="opacity: 0.4;"></i>
                      <span class="font-weight-bold">ຍັງບໍ່ມີລາຍການສິນຄ້າໃນໃບບິນ</span><br>
                      <small>ກະລຸນາກະແກນບາໂຄ້ດ ຫຼື ກົດປຸ່ມ "ເລືອກສິນຄ້າ" ເພື່ອເພີ່ມສິນຄ້າຮັບເຂົ້າ</small>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Table Footer Action Bar: Save Stock Button (Right Aligned) -->
              <div class="card-footer bg-light border-top p-3 d-flex align-items-center justify-content-end" style="border-color: #e2e8f0 !important;">
                <button type="button" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="height: 38px; border-radius: 6px;" onclick="submitDirectImportBill()">
                  <i class="fas fa-save mr-1.5" style="font-size: 1.1rem;"></i> ບັນທຶກສະຕັອກ
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>


      </div>
    </div>
  </section>
</div>

<!-- Modal: Select Product Popup -->
<?php require_once __DIR__ . '/components/modal_select_product.php'; ?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<script>
  var PRODUCTS_LIST = <?php echo json_encode($products); ?>;
  var PRODUCTS_UNITS = <?php echo json_encode($product_units_map); ?>;
  var selectedProduct = null;
  var cartItems = [];

  // ====== DIRECT ENTRY FORM JS LOGIC ======
  $(document).ready(function() {
    $('#direct_barcode_input').focus();
  });

  function onDirectBarcodeKeyDown(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      searchDirectProduct(true);
    }
  }

  function onDirectBarcodeChange() {
    searchDirectProduct(false);
  }

  function searchDirectProduct(isEnter) {
    var query = $('#direct_barcode_input').val().trim().toLowerCase();
    if (!query) {
      resetDirectSelection();
      return;
    }

    var exactMatch = null;
    var partialMatches = [];

    PRODUCTS_LIST.forEach(function(p) {
      var pBarcode = (p.barcode || "").toLowerCase();
      var pId = p.product_id.toString();
      var pName = (p.product_name || "").toLowerCase();

      var extraBarcodes = [];
      if (PRODUCTS_UNITS[p.product_id]) {
        PRODUCTS_UNITS[p.product_id].forEach(function(u) {
          if (u.barcode) extraBarcodes.push(u.barcode.toLowerCase());
        });
      }

      if (pBarcode === query || extraBarcodes.includes(query) || pId === query) {
        exactMatch = p;
      } else if (pBarcode.includes(query) || pName.includes(query)) {
        partialMatches.push(p);
      }
    });

    var targetProduct = exactMatch;
    if (!targetProduct && isEnter && partialMatches.length > 0) {
      targetProduct = partialMatches[0];
    }

    if (targetProduct) {
      setDirectSelectedProduct(targetProduct);
      if (isEnter) {
        $('#direct_qty').focus().select();
      }
    }
  }

  function selectProductFromModal(productId) {
    var p = PRODUCTS_LIST.find(function(item) { return item.product_id == productId; });
    if (p) {
      setDirectSelectedProduct(p);
      $('#productSelectModal').modal('hide');
      $('#direct_qty').focus().select();
    }
  }

  function setDirectSelectedProduct(p) {
    selectedProduct = p;
    $('#direct_matched_name').text(p.product_name);
    $('#direct_matched_barcode').text(p.barcode || '-');
    $('#direct_matched_bprice').text(parseFloat(p.bprice || 0).toLocaleString() + ' ₭');
    $('#direct_matched_stock').text(parseInt(p.qty || 0).toLocaleString());
    $('#direct_matched_banner').removeClass('d-none').addClass('d-flex');

    // Build units dropdown
    var unitSelect = document.getElementById("direct_unit_key");
    unitSelect.options.length = 0;
    var baseUnit = p.unit || "ອັນ";

    unitSelect.options[unitSelect.options.length] = new Option("ຍ່ອຍ (" + baseUnit + ")", "base");

    if (PRODUCTS_UNITS[p.product_id]) {
      PRODUCTS_UNITS[p.product_id].forEach(function(u) {
        var optText = u.unit_name + " (1 " + u.unit_name + " = " + u.multiplier + " " + baseUnit + ")";
        unitSelect.options[unitSelect.options.length] = new Option(optText, u.id);
      });
    }
  }

  function resetDirectSelection() {
    selectedProduct = null;
    $('#direct_barcode_input').val('');
    $('#direct_qty').val(1);
    $('#direct_expiry_date').val('');
    $('#direct_matched_banner').addClass('d-none').removeClass('d-flex');
    var unitSelect = document.getElementById("direct_unit_key");
    unitSelect.options.length = 0;
    unitSelect.options[unitSelect.options.length] = new Option("ຫົວໜ່ວຍຍ່ອຍ", "base");
  }

  function onQtyKeyDown(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      addCurrentItemToCart();
    }
  }

  function addCurrentItemToCart() {
    if (!selectedProduct) {
      Swal.fire({ icon: 'warning', title: 'ກະລຸນາເລືອກສິນຄ້າ', text: 'ກະລຸນາສະແກນບາໂຄ້ດ ຫຼື ກົດປຸ່ມ ເລືອກສິນຄ້າ ກ່ອນ!', confirmButtonColor: '#2563eb' });
      return;
    }

    var qty = intval($('#direct_qty').val() || 1);
    if (qty <= 0) {
      Swal.fire({ icon: 'warning', title: 'ຈຳນວນບໍ່ຖືກຕ້ອງ', text: 'ຈຳນວນຮັບເຂົ້າຕ້ອງຫຼາຍກວ່າ 0!', confirmButtonColor: '#2563eb' });
      return;
    }

    var unitKey = $('#direct_unit_key').val();
    var expiryDate = $('#direct_expiry_date').val();
    var baseUnit = selectedProduct.unit || 'ອັນ';
    var unitName = baseUnit;
    var multiplier = 1;
    var costPrice = parseFloat(selectedProduct.bprice || 0);

    if (unitKey !== 'base' && PRODUCTS_UNITS[selectedProduct.product_id]) {
      var foundU = PRODUCTS_UNITS[selectedProduct.product_id].find(function(u) { return u.id == unitKey; });
      if (foundU) {
        unitName = foundU.unit_name;
        multiplier = parseInt(foundU.multiplier || 1);
        if (foundU.bprice && parseFloat(foundU.bprice) > 0) {
          costPrice = parseFloat(foundU.bprice);
        }
      }
    }

    // Check if item already exists in cart
    var existingIdx = cartItems.findIndex(function(item) {
      return item.product_id == selectedProduct.product_id && item.unit_key == unitKey && item.expiry_date == expiryDate;
    });

    if (existingIdx >= 0) {
      cartItems[existingIdx].quantity += qty;
    } else {
      cartItems.push({
        product_id: selectedProduct.product_id,
        product_name: selectedProduct.product_name,
        barcode: selectedProduct.barcode || '-',
        base_unit: baseUnit,
        unit_key: unitKey,
        unit_name: unitName,
        multiplier: multiplier,
        quantity: qty,
        cost_price: costPrice,
        expiry_date: expiryDate
      });
    }

    resetDirectSelection();
    renderCartTable();
    $('#direct_barcode_input').focus();
  }

  // ====== LIVE CART TABLE PAGINATION ======
  var cartCurrentPage = 1;
  var cartPageSize = 10;

  function renderCartTable() {
    var tbody = $('#cart_table_body');
    tbody.empty();

    if (cartItems.length === 0) {
      tbody.html(`
        <tr id="empty_cart_row">
          <td colspan="6" class="text-center text-muted py-5">
            <i class="fas fa-box-open fa-3x d-block mb-2 text-muted" style="opacity: 0.4;"></i>
            <span class="font-weight-bold">ຍັງບໍ່ມີລາຍການສິນຄ້າໃນໃບບິນ</span><br>
            <small>ກະລຸນາສີດບາໂຄ້ດ ຫຼື ກົດປຸ່ມ "ເລືອກສິນຄ້າ" ເພື່ອເພີ່ມສິນຄ້າຮັບເຂົ້າ</small>
          </td>
        </tr>
      `);
      $('#cart_total_items').text(0);
      $('#cart_total_base').text(0);
      $('#cart_grand_total').text('0 ₭');
      $('#cart_page_start').text(0);
      $('#cart_page_end').text(0);
      $('#cart_page_total').text(0);
      $('#cart_json_input').val('[]');
      renderCartPagination(1);
      return;
    }

    var totalItems = cartItems.length;
    var grandTotalBase = 0;
    var grandTotalCost = 0;

    cartItems.forEach(function(item) {
      var itemBaseQty = item.quantity * item.multiplier;
      var itemTotalCost = item.quantity * item.cost_price;
      grandTotalBase += itemBaseQty;
      grandTotalCost += itemTotalCost;
    });

    $('#cart_total_items').text(totalItems);
    $('#cart_total_base').text(grandTotalBase.toLocaleString());
    $('#cart_grand_total').text(grandTotalCost.toLocaleString() + ' ₭');
    $('#cart_json_input').val(JSON.stringify(cartItems));

    // Pagination for cart items table
    var totalPages = Math.ceil(totalItems / cartPageSize) || 1;
    if (cartCurrentPage > totalPages) cartCurrentPage = totalPages;
    if (cartCurrentPage < 1) cartCurrentPage = 1;

    var startIdx = (cartCurrentPage - 1) * cartPageSize;
    var endIdx = Math.min(startIdx + cartPageSize, totalItems);

    for (var idx = startIdx; idx < endIdx; idx++) {
      var item = cartItems[idx];
      var itemTotalCost = item.quantity * item.cost_price;

      var rowHtml = `
        <tr>
          <td class="text-center align-middle font-weight-bold text-muted">${idx + 1}</td>
          <td class="align-middle">
            <span class="font-weight-bold text-dark d-block" style="font-size: 0.98rem;">${escapeHtml(item.product_name)}</span>
            <small class="text-muted"><i class="fas fa-barcode mr-1"></i> ${escapeHtml(item.barcode)} ${item.expiry_date ? '| ໝົດອາຍຸ: ' + item.expiry_date : ''}</small>
          </td>
          <td class="text-center align-middle">
            <div class="input-group input-group-sm mx-auto" style="max-width: 130px;">
              <input type="number" class="form-control text-center font-weight-bold" min="1" value="${item.quantity}" onchange="updateCartQty(${idx}, this.value)">
              <div class="input-group-append">
                <span class="input-group-text bg-light font-weight-bold text-dark">${escapeHtml(item.unit_name)}</span>
              </div>
            </div>
          </td>
          <td class="text-right align-middle font-weight-bold text-dark" style="font-size: 0.95rem;">
            ${item.cost_price.toLocaleString()} ₭
          </td>
          <td class="text-right align-middle font-weight-bold text-primary" style="font-size: 1.05rem;">
            ${itemTotalCost.toLocaleString()} ₭
          </td>
          <td class="text-center align-middle">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCartItem(${idx})" title="ລົບອອກ"><i class="fas fa-trash-alt"></i></button>
          </td>
        </tr>
      `;
      tbody.append(rowHtml);
    }

    $('#cart_page_start').text(startIdx + 1);
    $('#cart_page_end').text(endIdx);
    $('#cart_page_total').text(totalItems);

    renderCartPagination(totalPages);
  }

  function renderCartPagination(totalPages) {
    var container = $('#cartTablePagination');
    container.empty();

    if (totalPages < 1) totalPages = 1;

    var prevDisabled = (cartCurrentPage === 1) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToCartPage(${cartCurrentPage - 1})">
          <i class="fas fa-chevron-left"></i>
        </a>
      </li>
    `);

    var maxButtons = 5;
    var startPage = Math.max(1, cartCurrentPage - 2);
    var endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) {
      startPage = Math.max(1, endPage - maxButtons + 1);
    }

    if (startPage > 1) {
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToCartPage(1)">1</a></li>`);
      if (startPage > 2) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
    }

    for (var p = startPage; p <= endPage; p++) {
      var activeClass = (p === cartCurrentPage) ? 'active' : '';
      container.append(`
        <li class="page-item ${activeClass}">
          <a class="page-link" href="javascript:void(0)" onclick="goToCartPage(${p})">${p}</a>
        </li>
      `);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToCartPage(${totalPages})">${totalPages}</a></li>`);
    }

    var nextDisabled = (cartCurrentPage === totalPages) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToCartPage(${cartCurrentPage + 1})">
          <i class="fas fa-chevron-right"></i>
        </a>
      </li>
    `);
  }

  function goToCartPage(page) {
    cartCurrentPage = page;
    renderCartTable();
  }

  function updateCartQty(idx, val) {
    var q = intval(val || 1);
    if (q <= 0) q = 1;
    cartItems[idx].quantity = q;
    renderCartTable();
  }

  function removeCartItem(idx) {
    cartItems.splice(idx, 1);
    renderCartTable();
  }

  function submitDirectImportBill() {
    if (cartItems.length === 0) {
      Swal.fire({ icon: 'warning', title: 'ໃບບິນຫວ່າງເປົ່າ', text: 'ກະລຸນາເພີ່ມສິນຄ້າຮັບເຂົ້າຢ່າງນ້ອຍ 1 ລາຍການ!', confirmButtonColor: '#2563eb' });
      return;
    }

    Swal.fire({
      title: 'ຢືນຢັນການບັນທຶກຮັບເຂົ້າ?',
      text: "ລາຍການຮັບເຂົ້າທັງໝົດ " + cartItems.length + " ລາຍການ ຈະຖືກເພີ່ມເຂົ້າສະຕັອກຄັງສິນຄ້າ!",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#10b981',
      cancelButtonColor: '#64748b',
      confirmButtonText: '<i class="fas fa-save mr-1"></i> ບັນທຶກຮັບເຂົ້າ',
      cancelButtonText: 'ຍົກເລີກ'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#directImportForm').submit();
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

  function intval(val) {
    var parsed = parseInt(val, 10);
    return isNaN(parsed) ? 0 : parsed;
  }

  function escapeHtml(text) {
    return String(text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
</script>
