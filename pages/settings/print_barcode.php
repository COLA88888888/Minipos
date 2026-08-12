<?php
session_start();
$base_path = '../../';
require_once __DIR__ . '/../../config/db.php';

if (empty($_SESSION['user_id']) || (!hasPermission('setup') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

// Fetch categories & products
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
$products = $pdo->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.product_name ASC
")->fetchAll();

require_once __DIR__ . '/../../layouts/header.php';
?>

<script src="<?php echo $base_path; ?>assets/js/JsBarcode.all.min.js"></script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printArea, #printArea * {
        visibility: visible;
    }
    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .no-print {
        display: none !important;
    }
}
.barcode-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(15,23,42,0.04);
}
.barcode-preview-box {
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    padding: 14px;
    text-align: center;
    background: #ffffff;
    display: inline-block;
    margin: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}
</style>

<div class="container-fluid p-4 no-print">
  <!-- Page Header -->
  <div class="row mb-3 align-items-center">
    <div class="col-sm-6">
      <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
        <i class="fas fa-barcode text-primary mr-2"></i> ພິມບາໂຄ້ດສິນຄ້າ (Print Barcodes)
      </h5>
    </div>
  </div>

  <div class="row">
    <!-- Left: Product Selection & Controls -->
    <div class="col-lg-5 mb-4">
      <div class="barcode-card p-4">
        <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
          <i class="fas fa-sliders-h text-primary mr-1"></i> ກຳນົດການພິມບາໂຄ້ດ
        </h6>

        <div class="form-group mb-3">
          <label class="font-weight-bold text-dark small mb-1">ເລືອກສິນຄ້າ: <span class="text-danger">*</span></label>
          <select id="sel_product" class="form-control select2" onchange="onProductSelect(this.value)">
            <option value="">-- ເລືອກສິນຄ້າເພື່ອພິມ --</option>
            <?php foreach ($products as $p): ?>
              <option value="<?php echo htmlspecialchars(json_encode($p)); ?>">
                <?php echo htmlspecialchars($p['product_name']); ?> (<?php echo !empty($p['barcode']) ? $p['barcode'] : 'ID: ' . $p['product_id']; ?>) - <?php echo number_format($p['price'], 0); ?> ₭
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="row">
          <div class="col-6 mb-3">
            <label class="font-weight-bold text-dark small mb-1">ຈຳນວນດວງ (ໃບ):</label>
            <input type="number" id="print_qty" class="form-control text-center font-weight-bold text-primary" value="1" min="1" max="100" oninput="generatePreview()">
          </div>
          <div class="col-6 mb-3">
            <label class="font-weight-bold text-dark small mb-1">ຂະໜາດສະຕິກເກີ:</label>
            <select id="label_size" class="form-control" onchange="generatePreview()">
              <option value="standard">ມາດຕະຖານ (40x30mm)</option>
              <option value="small">ຂະໜາດນ້ອຍ (35x25mm)</option>
              <option value="large">ຂະໜາດໃຫຍ່ (50x35mm)</option>
            </select>
          </div>
        </div>

        <div class="form-group mb-3">
          <label class="font-weight-bold text-dark small mb-1">ຕົວເລືອກສະແດງ:</label>
          <div class="d-flex flex-wrap" style="gap: 12px;">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="show_name" checked onchange="generatePreview()">
              <label class="custom-control-label small" for="show_name">ຊື່ສິນຄ້າ</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="show_price" checked onchange="generatePreview()">
              <label class="custom-control-label small" for="show_price">ລາຄາ (₭)</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="show_code" checked onchange="generatePreview()">
              <label class="custom-control-label small" for="show_code">ຕົວເລກບາໂຄ້ດ</label>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button type="button" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm" onclick="window.print()">
            <i class="fas fa-print mr-2"></i> ສັ່ງພິມບາໂຄ້ດ (Print)
          </button>
        </div>
      </div>
    </div>

    <!-- Right: Live Preview Area -->
    <div class="col-lg-7 mb-4">
      <div class="barcode-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
          <h6 class="font-weight-bold text-dark mb-0">
            <i class="fas fa-eye text-success mr-1"></i> ຕົວຢ່າງກ່ອນພິມ (Live Preview)
          </h6>
          <span class="badge badge-light border text-muted" id="preview_count">0 ດວງ</span>
        </div>

        <div id="previewContainer" class="d-flex flex-wrap justify-content-center align-items-center p-3 bg-light" style="min-height: 280px; border-radius: 10px; overflow-y: auto; max-height: 500px;">
          <div class="text-center text-muted py-5" id="noProductMsg">
            <i class="fas fa-barcode fa-3x mb-2 text-secondary opacity-50"></i>
            <p class="mb-0">ກະລຸນາເລືອກສິນຄ້າດ້ານຊ້າຍເພື່ອສະແດງຕົວຢ່າງບາໂຄ້ດ</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Printable Area (Visible only when printing) -->
<div id="printArea" class="d-none d-print-block">
  <div id="printContainer" style="display: flex; flex-wrap: wrap; justify-content: flex-start;"></div>
</div>

<script>
var selectedProduct = null;

function onProductSelect(jsonStr) {
  if (!jsonStr) {
    selectedProduct = null;
    generatePreview();
    return;
  }
  try {
    selectedProduct = JSON.parse(jsonStr);
    generatePreview();
  } catch (e) {
    selectedProduct = null;
  }
}

function generatePreview() {
  var container = document.getElementById('previewContainer');
  var printContainer = document.getElementById('printContainer');
  var countBadge = document.getElementById('preview_count');

  if (!selectedProduct) {
    container.innerHTML = `
      <div class="text-center text-muted py-5" id="noProductMsg">
        <i class="fas fa-barcode fa-3x mb-2 text-secondary opacity-50"></i>
        <p class="mb-0">ກະລຸນາເລືອກສິນຄ້າດ້ານຊ້າຍເພື່ອສະແດງຕົວຢ່າງບາໂຄ້ດ</p>
      </div>`;
    printContainer.innerHTML = '';
    countBadge.textContent = '0 ດວງ';
    return;
  }

  var qty = parseInt(document.getElementById('print_qty').value) || 1;
  if (qty < 1) qty = 1;
  if (qty > 100) qty = 100;
  countBadge.textContent = qty + ' ດວງ';

  var showName = document.getElementById('show_name').checked;
  var showPrice = document.getElementById('show_price').checked;
  var showCode = document.getElementById('show_code').checked;
  var size = document.getElementById('label_size').value;

  var barcodeVal = selectedProduct.barcode && selectedProduct.barcode.trim() !== '' 
                   ? selectedProduct.barcode.trim() 
                   : String(selectedProduct.product_id);

  var barcodeType = (barcodeVal.length === 13 && /^\d+$/.test(barcodeVal)) ? 'EAN13' : 'CODE128';

  var width = size === 'small' ? 1.2 : (size === 'large' ? 1.8 : 1.5);
  var height = size === 'small' ? 30 : (size === 'large' ? 45 : 36);
  var fontSize = size === 'small' ? 10 : 12;

  var html = '';
  for (var i = 0; i < qty; i++) {
    html += `
      <div class="barcode-preview-box" style="margin: 6px;">
        ${showName ? `<div style="font-size: 0.82rem; font-weight: bold; color: #1e293b; max-width: 170px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${selectedProduct.product_name}</div>` : ''}
        ${showPrice ? `<div style="font-size: 0.88rem; font-weight: 800; color: #16a34a;">${Number(selectedProduct.price).toLocaleString()} ₭</div>` : ''}
        <svg class="barcode-svg" id="svg_preview_${i}"></svg>
      </div>
    `;
  }

  container.innerHTML = html;
  printContainer.innerHTML = html;

  // Render barcode SVGs
  setTimeout(function() {
    for (var i = 0; i < qty; i++) {
      try {
        JsBarcode("#svg_preview_" + i, barcodeVal, {
          format: barcodeType,
          width: width,
          height: height,
          displayValue: showCode,
          fontSize: fontSize,
          margin: 2
        });
      } catch (err) {
        try {
          JsBarcode("#svg_preview_" + i, barcodeVal, {
            format: "CODE128",
            width: width,
            height: height,
            displayValue: showCode,
            fontSize: fontSize,
            margin: 2
          });
        } catch (e2) {}
      }
    }
  }, 50);
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
