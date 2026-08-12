<?php
// ດຶງຂໍ້ມູນຊື່ຮ້ານ
$comp_stmt = $pdo->query("SELECT com_name_la FROM tbcompanyinfo LIMIT 1");
$company_name = $comp_stmt ? ($comp_stmt->fetchColumn() ?: 'MiniPos') : 'MiniPos';
?>
<!-- Modal: Print Barcode -->
<div class="modal fade" id="printBarcodeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      
      <!-- HEADER -->
      <div class="modal-header bg-info text-white py-3 px-4">
        <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao Looped'; font-size: 1.15rem;">
          <i class="fas fa-print mr-2"></i> ປິ່ນສະຕິກເກີບາໂຄ້ດ
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-4">
        <div class="row">
          
          <!-- LEFT: Settings -->
          <div class="col-md-6 border-right pr-md-4">
            
            <div class="form-group mb-3">
              <label class="font-weight-bold text-dark mb-1" style="font-size: 0.9rem;">
                <i class="fas fa-box text-primary mr-1"></i> ສິນຄ້າ:
              </label>
              <div id="print_prod_title" class="p-2 bg-light font-weight-bold text-dark" style="border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.95rem;">
                -
              </div>
            </div>

            <div class="form-group mb-3">
              <label class="font-weight-bold text-dark mb-1" style="font-size: 0.9rem;">
                <i class="fas fa-copy text-primary mr-1"></i> ຈຳນວນດວງທີ່ຕ້ອງການປິ່ນ:
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <button type="button" class="btn btn-outline-secondary" onclick="changePrintQty(-1)">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
                <input type="number" id="print_copies" class="form-control text-center font-weight-bold" value="1" min="1" max="500" style="height: 40px; font-size: 1.05rem;" oninput="updateBarcodePreview()">
                <div class="input-group-append">
                  <button type="button" class="btn btn-outline-secondary" onclick="changePrintQty(1)">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
              <div class="mt-2 d-flex" style="gap: 6px;">
                <button type="button" class="btn btn-sm btn-light border font-weight-bold px-2.5 py-1" onclick="setPrintCopies(1)">1 ດວງ</button>
                <button type="button" class="btn btn-sm btn-light border font-weight-bold px-2.5 py-1" onclick="setPrintCopies(5)">5 ດວງ</button>
                <button type="button" class="btn btn-sm btn-light border font-weight-bold px-2.5 py-1" onclick="setPrintCopies(10)">10 ດວງ</button>
                <button type="button" class="btn btn-sm btn-light border font-weight-bold px-2.5 py-1" onclick="setPrintCopies(20)">20 ດວງ</button>
              </div>
            </div>

            <div class="form-group mb-3">
              <label class="font-weight-bold text-dark mb-1" style="font-size: 0.9rem;">
                <i class="fas fa-expand text-primary mr-1"></i> ຂະໜາດສະຕິກເກີ / ເຈ້ຍ:
              </label>
              <select id="print_size" class="form-control" style="border-radius: 6px; height: 40px;" onchange="updateBarcodePreview()">
                <option value="40x30">40mm x 30mm (ສະຕິກເກີມ້ວນມາດຕະຖານ)</option>
                <option value="50x30">50mm x 30mm (ສະຕິກເກີມ້ວນຂະໜາດກາງ)</option>
                <option value="a4">ເຈ້ຍ A4 (ຕາຕະລາງຫຼາຍດວງ)</option>
              </select>
            </div>

            <div class="form-group mb-0">
              <label class="font-weight-bold text-dark mb-2" style="font-size: 0.9rem;">
                <i class="fas fa-sliders-h text-primary mr-1"></i> ຕົວເລືອກສະແດງຜົນໃນສະຕິກເກີ:
              </label>
              <div class="custom-control custom-checkbox mb-1.5">
                <input type="checkbox" class="custom-control-input" id="opt_show_shop" checked onchange="updateBarcodePreview()">
                <label class="custom-control-label font-weight-bold text-secondary" for="opt_show_shop" style="font-size: 0.88rem; cursor: pointer;">ສະແດງຊື່ຮ້ານ (<?php echo htmlspecialchars($company_name); ?>)</label>
              </div>
              <div class="custom-control custom-checkbox mb-1.5">
                <input type="checkbox" class="custom-control-input" id="opt_show_name" checked onchange="updateBarcodePreview()">
                <label class="custom-control-label font-weight-bold text-secondary" for="opt_show_name" style="font-size: 0.88rem; cursor: pointer;">ສະແດງຊື່ສິນຄ້າ</label>
              </div>
              <div class="custom-control custom-checkbox mb-1.5">
                <input type="checkbox" class="custom-control-input" id="opt_show_price" checked onchange="updateBarcodePreview()">
                <label class="custom-control-label font-weight-bold text-secondary" for="opt_show_price" style="font-size: 0.88rem; cursor: pointer;">ສະແດງລາຄາ</label>
              </div>
            </div>

          </div>

          <!-- RIGHT: Live Sticker Preview -->
          <div class="col-md-6 pl-md-4 d-flex flex-column align-items-center justify-content-center pt-3 pt-md-0">
            <!-- <label class="font-weight-bold text-dark mb-2 align-self-start" style="font-size: 0.9rem;">
              <i class="fas fa-eye text-primary mr-1"></i> ຕົວຢ່າງສະຕິກເກີ (Live Preview):
            </label> -->

            <!-- Preview Card -->
            <div id="barcodePreviewCard" class="shadow-sm bg-white p-3 d-flex flex-column align-items-center justify-content-center text-center"
                 style="width: 240px; min-height: 165px; border: 2px dashed #0284c7; border-radius: 12px; transition: all 0.2s;">
              
              <!-- Shop Name -->
              <div id="prev_shop_name" class="font-weight-bold text-truncate w-100" style="font-size: 0.78rem; color: #475569; line-height: 1.2; margin-bottom: 2px;">
                <?php echo htmlspecialchars($company_name); ?>
              </div>

              <!-- Product Name -->
              <div id="prev_prod_name" class="font-weight-bold text-truncate w-100" style="font-size: 0.86rem; color: #0f172a; line-height: 1.2; margin-bottom: 4px;">
                ຊື່ສິນຄ້າ
              </div>

              <!-- Barcode SVG -->
              <div class="my-1 d-flex align-items-center justify-content-center" style="max-width: 100%; overflow: hidden;">
                <svg id="barcodePreviewSvg" style="max-width: 210px;"></svg>
              </div>

              <!-- Price -->
              <div id="prev_prod_price" class="font-weight-bold text-dark" style="font-size: 0.95rem; margin-top: 2px;">
                0 ₭
              </div>

            </div>

            <small class="text-muted mt-2 text-center" style="font-size: 0.76rem;">
              <i class="fas fa-info-circle mr-1"></i> ຂະໜາດຈິງຈະປັບຕາມເຄື່ອງພິມສະຕິກເກີ
            </small>

          </div>

        </div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer border-0 pt-0 pb-4 px-4">
        <button type="button" class="btn btn-light font-weight-bold px-4" style="border-radius: 6px;" data-dismiss="modal">ຍົກເລີກ</button>
        <button type="button" class="btn btn-info font-weight-bold px-4 shadow-sm text-white" style="border-radius: 6px;" onclick="executeBarcodePrint()">
          <i class="fas fa-print mr-1"></i> ປິ່ນບາໂຄ້ດ
      </div>

    </div>
  </div>
</div>

<!-- Hidden Iframe for printing -->
<iframe id="barcodePrintIframe" style="display: none; width: 0; height: 0; border: none;"></iframe>

<script src="<?php echo $base_path; ?>assets/js/JsBarcode.all.min.js"></script>
<script>
var currentBarcodeProduct = null;
var SHOP_NAME = <?php echo json_encode($company_name); ?>;

function openPrintBarcodeModal(p) {
  currentBarcodeProduct = p;
  
  var title = p.product_name;
  if (p.unit) title += ' (' + p.unit + ')';
  $('#print_prod_title').text(title);
  $('#print_copies').val(1);

  updateBarcodePreview();
  $('#printBarcodeModal').modal('show');
}

function changePrintQty(delta) {
  var val = parseInt($('#print_copies').val()) || 1;
  val += delta;
  if (val < 1) val = 1;
  if (val > 500) val = 500;
  $('#print_copies').val(val);
  updateBarcodePreview();
}

function setPrintCopies(num) {
  $('#print_copies').val(num);
  updateBarcodePreview();
}

function updateBarcodePreview() {
  if (!currentBarcodeProduct) return;

  var showShop  = $('#opt_show_shop').is(':checked');
  var showName  = $('#opt_show_name').is(':checked');
  var showPrice = $('#opt_show_price').is(':checked');

  // Shop Name
  if (showShop) {
    $('#prev_shop_name').text(SHOP_NAME).show();
  } else {
    $('#prev_shop_name').hide();
  }

  // Product Name
  if (showName) {
    var pName = currentBarcodeProduct.product_name || '';
    if (currentBarcodeProduct.unit) pName += ' (' + currentBarcodeProduct.unit + ')';
    $('#prev_prod_name').text(pName).show();
  } else {
    $('#prev_prod_name').hide();
  }

  // Price
  if (showPrice) {
    var priceNum = currentBarcodeProduct.price ? Number(currentBarcodeProduct.price) : 0;
    $('#prev_prod_price').text(priceNum.toLocaleString('en-US') + ' ₭').show();
  } else {
    $('#prev_prod_price').hide();
  }

  // Barcode string: use barcode if available, else product_id (zero padded or raw)
  var barcodeValue = currentBarcodeProduct.barcode && currentBarcodeProduct.barcode.trim() !== '' 
                     ? currentBarcodeProduct.barcode.trim() 
                     : String(currentBarcodeProduct.product_id);

  try {
    JsBarcode("#barcodePreviewSvg", barcodeValue, {
      format: "CODE128",
      width: 1.6,
      height: 40,
      displayValue: true,
      fontSize: 12,
      font: "monospace",
      margin: 2,
      textMargin: 2
    });
  } catch (err) {
    console.error("Barcode generation error:", err);
  }
}

// ====== ຟັງຊັນສັ່ງປິ່ນບາໂຄ້ດ (Execute Print) ======
function executeBarcodePrint() {
  if (!currentBarcodeProduct) return;

  var copies    = parseInt($('#print_copies').val()) || 1;
  var size      = $('#print_size').val();
  var showShop  = $('#opt_show_shop').is(':checked');
  var showName  = $('#opt_show_name').is(':checked');
  var showPrice = $('#opt_show_price').is(':checked');

  var barcodeVal = currentBarcodeProduct.barcode && currentBarcodeProduct.barcode.trim() !== ''
                   ? currentBarcodeProduct.barcode.trim()
                   : String(currentBarcodeProduct.product_id);

  var prodName = currentBarcodeProduct.product_name || '';
  if (currentBarcodeProduct.unit) prodName += ' (' + currentBarcodeProduct.unit + ')';

  var priceFormatted = Number(currentBarcodeProduct.price || 0).toLocaleString('en-US') + ' ₭';

  // Build print HTML document
  var printDoc = `
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="UTF-8">
    <title>Print Barcode - ${prodName}</title>
    <script src="${'<?php echo $base_path; ?>assets/js/JsBarcode.all.min.js'}"><\/script>
    <style>
      @page {
        margin: 0;
        size: auto;
      }
      body {
        margin: 0;
        padding: 6px;
        font-family: 'Noto Sans Lao Looped', 'Noto Sans Lao', Arial, sans-serif;
        background: #fff;
        color: #000;
        -webkit-print-color-adjust: exact;
      }
      .label-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: flex-start;
      }
      .barcode-label {
        box-sizing: border-box;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        page-break-inside: avoid;
        break-inside: avoid;
        margin-bottom: 6px;
      }
      ${size === '40x30' ? `
        .barcode-label {
          width: 40mm;
          height: 30mm;
          padding: 1.5mm 1mm;
          overflow: hidden;
        }
        .shop-title { font-size: 8px; font-weight: bold; line-height: 1; max-width: 38mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 1px; }
        .prod-title { font-size: 9px; font-weight: bold; line-height: 1.1; max-width: 38mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 1px; }
        .price-title { font-size: 10px; font-weight: bold; line-height: 1; margin-top: 1px; }
      ` : size === '50x30' ? `
        .barcode-label {
          width: 50mm;
          height: 30mm;
          padding: 2mm 1.5mm;
          overflow: hidden;
        }
        .shop-title { font-size: 9px; font-weight: bold; line-height: 1; max-width: 47mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 1px; }
        .prod-title { font-size: 10px; font-weight: bold; line-height: 1.1; max-width: 47mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 1px; }
        .price-title { font-size: 11px; font-weight: bold; line-height: 1; margin-top: 1px; }
      ` : `
        /* A4 Layout: 3 columns */
        .label-container {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 8px;
          padding: 10mm;
        }
        .barcode-label {
          border: 1px dashed #cbd5e1;
          border-radius: 6px;
          padding: 4mm 2mm;
          min-height: 32mm;
        }
        .shop-title { font-size: 10px; font-weight: bold; line-height: 1.1; margin-bottom: 2px; }
        .prod-title { font-size: 11px; font-weight: bold; line-height: 1.2; margin-bottom: 2px; }
        .price-title { font-size: 12px; font-weight: bold; line-height: 1; margin-top: 2px; }
      `}
      svg {
        max-width: 100%;
        height: auto;
      }
    </style>
  </head>
  <body>
    <div class="label-container">
  `;

  for (var i = 0; i < copies; i++) {
    printDoc += `
      <div class="barcode-label">
        ${showShop ? `<div class="shop-title">${SHOP_NAME}</div>` : ''}
        ${showName ? `<div class="prod-title">${prodName}</div>` : ''}
        <svg class="barcode-svg" data-barcode="${barcodeVal}"></svg>
        ${showPrice ? `<div class="price-title">${priceFormatted}</div>` : ''}
      </div>
    `;
  }

  printDoc += `
    </div>
    <script>
      window.onload = function() {
        var svgs = document.querySelectorAll('.barcode-svg');
        svgs.forEach(function(el) {
          var code = el.getAttribute('data-barcode');
          JsBarcode(el, code, {
            format: "CODE128",
            width: ${size === '40x30' ? 1.3 : (size === '50x30' ? 1.5 : 1.7)},
            height: ${size === '40x30' ? 32 : (size === '50x30' ? 36 : 42)},
            displayValue: true,
            fontSize: ${size === '40x30' ? 9 : 11},
            font: "monospace",
            margin: 1,
            textMargin: 1
          });
        });
        setTimeout(function() {
          window.print();
        }, 300);
      };
    <\/script>
  </body>
  </html>
  `;

  var iframe = document.getElementById('barcodePrintIframe');
  var ifrDoc = iframe.contentWindow || iframe.contentDocument;
  if (ifrDoc.document) ifrDoc = ifrDoc.document;
  ifrDoc.open();
  ifrDoc.write(printDoc);
  ifrDoc.close();
}
</script>
