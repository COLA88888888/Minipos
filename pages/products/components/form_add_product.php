<!-- Modal: Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 860px;">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <form id="addProductForm" novalidate action="" method="POST" enctype="multipart/form-data" onsubmit="return submitAddProduct(event)">
        <input type="hidden" name="action" value="add_product">
        <input type="hidden" name="product_id" id="add_product_id">
        
        <!-- HEADER -->
        <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #2c5aa0, #244886) !important;">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao', 'Souliyo', 'Boon', sans-serif; font-size: 1.15rem;">
            <i class="fas fa-plus-circle mr-2"></i> ເພີ່ມສິນຄ້າໃໝ່
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- BODY -->
        <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
          <div class="row align-items-center">
            
            <!-- LEFT: Product Image -->
            <div class="col-md-4 text-center border-right pr-md-4 py-2 d-flex flex-column align-items-center justify-content-center">
              <div class="position-relative my-2 d-inline-block" style="width: 140px; height: 140px;">
                <img id="add_img_preview"
                     src="<?php echo $base_path; ?>assets/img/image.jpg"
                     class="shadow-sm"
                     style="width: 140px; height: 140px; object-fit: cover; border-radius: 14px; border: 2px dashed #93c5fd; background: #f8fafc; cursor: pointer;"
                     onclick="document.getElementById('add_product_img').click();"
                     title="ຄລິກເພື່ອເລືອກຮູບພາບ">
                
                <!-- Camera button -->
                <button type="button"
                        class="position-absolute d-flex align-items-center justify-content-center shadow-sm border-0"
                        style="right: -6px; bottom: -6px; width: 34px; height: 34px; background: #2563eb; color: #fff; border-radius: 50%; border: 2px solid #fff; cursor: pointer; z-index: 2;"
                        onclick="document.getElementById('add_product_img').click();"
                        title="ເລືອກຮູບ">
                  <i class="fas fa-camera" style="font-size: 0.85rem;"></i>
                </button>

                <!-- Remove button (hidden by default) -->
                <button type="button" id="btn_remove_add_img"
                        onclick="removeProductImg('add');"
                        class="position-absolute align-items-center justify-content-center border-0 shadow-sm"
                        style="right: -6px; top: -6px; width: 30px; height: 30px; background: #ef4444; color: #fff; border-radius: 50%; border: 2px solid #fff; cursor: pointer; display: none; z-index: 3;"
                        title="ລົບຮູບ">
                  <i class="fas fa-times" style="font-size: 0.82rem;"></i>
                </button>
              </div>

              <small class="text-muted mt-1 font-weight-bold" style="font-size: 0.78rem;">ຄລິກເພື່ອເລືອກຮູບສິນຄ້າ</small>
              <input type="file" name="product_img" id="add_product_img" accept="image/*" style="display: none;"
                     onchange="previewProductImg(this, 'add_img_preview', 'btn_remove_add_img')">
            </div>

            <!-- RIGHT: Base Form Fields -->
            <div class="col-md-8 pl-md-4 py-2">
              
              <!-- Row 1: ປະເພດສິນຄ້າ (ຂຶ້ນກ່ອນ) & ລະຫັດສິນຄ້າ (Auto) -->
              <div class="row">
                <div class="col-md-7 mb-3">
                  <label class="font-weight-bold text-dark mb-1">ປະເພດສິນຄ້າ: <span class="text-danger">*</span></label>
                  <select id="add_category_id" name="category_id" class="form-control" style="border-radius: 6px; height: 40px;" onchange="onAddCategoryChange()" required>
                    <option value="">-- ເລືອກປະເພດ --</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-5 mb-3">
                  <label class="font-weight-bold text-dark mb-1">ລະຫັດສິນຄ້າ:</label>
                  <input type="text" id="add_product_id_display" class="form-control bg-light font-weight-bold text-primary" placeholder="ເລືອກປະເພດສິນຄ້າ..." style="border-radius: 6px; height: 40px;" readonly>
                </div>
              </div>

              <!-- Row 2: ຊື່ສິນຄ້າ & ບາໂຄ້ດຫຼັກ -->
              <div class="row">
                <div class="col-md-7 mb-3">
                  <label class="font-weight-bold text-dark mb-1">ຊື່ສິນຄ້າ: <span class="text-danger">*</span></label>
                  <input type="text" id="add_product_name" name="product_name" class="form-control" placeholder="ປ້ອນຊື່ສິນຄ້າ..." style="border-radius: 6px; height: 40px;" required>
                </div>
                <div class="col-md-5 mb-3">
                  <label class="font-weight-bold text-dark mb-1">ບາໂຄ້ດ: <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" id="add_barcode" name="barcode" class="form-control" placeholder="ສະແກນ ຫຼື ປ້ອນຕົວເລກ..." style="border-radius: 6px 0 0 6px; height: 40px;" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-primary font-weight-bold" onclick="generateEAN13('add_barcode')" title="ສ້າງບາໂຄ້ດ 13 ຫຼັກ" style="border-radius: 0 6px 6px 0; height: 40px; border-color: #cbd5e1;">
                        <i class="fas fa-barcode mr-1"></i> ສ້າງ
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Row 3: ຫົວໜ່ວຍຍ່ອຍພື້ນຖານ & ຈຳນວນສະຕັອກ (ສະເພາະແອັດມິນ) -->
              <div class="row">
                <div class="<?php echo $isAdmin ? 'col-md-7' : 'col-md-12'; ?> mb-3">
                  <label class="font-weight-bold text-dark mb-1">ຫົວໜ່ວຍ: <span class="text-danger">*</span></label>
                  <select name="unit" id="add_unit" class="form-control" style="border-radius: 6px; height: 40px;" required>
                    <option value="">-- ເລືອກຫົວໜ່ວຍ --</option>
                    <?php foreach ($units as $u): ?>
                      <option value="<?php echo htmlspecialchars($u['unit_name']); ?>"><?php echo htmlspecialchars($u['unit_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if ($isAdmin): ?>
                  <div class="col-md-5 mb-3">
                    <label class="font-weight-bold text-dark mb-1">
                      ຈຳນວນສະຕັອກ: 
                      <small class="text-muted font-weight-normal">(ຖ້າມີ)</small>
                    </label>
                    <input type="number" name="qty" id="add_qty" class="form-control text-center font-weight-bold text-primary" placeholder="0" min="0" value="0" style="border-radius: 6px; height: 40px;">
                  </div>
                <?php endif; ?>
              </div>

              <!-- Row 4: ລາຄາຊື້ & ລາຄາຂາຍຍ່ອຍ -->
              <div class="row">
                <div class="col-6">
                  <label class="font-weight-bold text-dark mb-1">ລາຄາຊື້:<span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" id="add_bprice" name="bprice" class="form-control price-input text-right font-weight-bold" placeholder="0" style="border-radius: 6px 0 0 6px; height: 40px; color: #475569;" oninput="formatPriceInput(this)" required>
                    <div class="input-group-append"><span class="input-group-text" style="border-radius: 0 6px 6px 0; font-size: 0.85rem;">₭</span></div>
                  </div>
                </div>
                <div class="col-6">
                  <label class="font-weight-bold text-dark mb-1">ລາຄາຂາຍ: <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" id="add_price" name="price" class="form-control price-input text-right font-weight-bold" placeholder="0" style="border-radius: 6px 0 0 6px; height: 40px; color: #16a34a;" oninput="formatPriceInput(this)" required>
                    <div class="input-group-append"><span class="input-group-text" style="border-radius: 0 6px 6px 0; font-size: 0.85rem;">₭</span></div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Multi-Unit Section (ຫຼາຍລາຄາ / ຫຼາຍຫົວໜ່ວຍ ເຊັ່ນ: ແພັກ, ແກັດ) -->
          <div class="mt-3 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <span class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                   ລາຄາ ແລະ ຫົວໜ່ວຍເພີ່ມເຕີມ (ຫຼາຍລາຄາ)
                </span>
                <small class="text-muted d-block" style="font-size: 0.78rem;">ຖ້າສິນຄ້ານີ້ມີຂາຍເປັນ ແພັກ (x6 ປ໋ອງ), ແກັດ (x24 ປ໋ອງ)... ສາມາດເພີ່ມໄດ້ທີ່ນີ້ (ຕັດສະຕັອກອັດຕະໂນມັດ)</small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px; font-size: 0.82rem;" onclick="addUnitRow('add')">
                 ເພີ່ມລາຄາ/ຫົວໜ່ວຍ
              </button>
            </div>

            <div id="add_multi_units_container">
              <!-- Dynamic Unit Rows -->
            </div>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light font-weight-bold px-4" style="border-radius: 6px; cursor: pointer;" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 6px; cursor: pointer; background: linear-gradient(135deg, #2c5aa0, #244886); border: none;">
            <i class="fas fa-save mr-1"></i> ບັນທຶກ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// ດຶງລຳດັບການປ້ອນສິນຄ້າເພີ່ມເຂົ້າທົ່ວລະບົບ (Sequence: 0001, 0002, 0003...)
var GLOBAL_NEXT_SEQ = '<?php echo htmlspecialchars($nextSeqFormatted ?? "0001"); ?>';

function onAddCategoryChange() {
  var select = document.getElementById('add_category_id');
  var catId = select ? select.value : '';
  if (catId) {
    var fullCode = catId + GLOBAL_NEXT_SEQ;
    $('#add_product_id_display').val(fullCode);
    $('#add_product_id').val(fullCode);
  } else {
    $('#add_product_id_display').val('');
    $('#add_product_id').val('');
  }
}

$('#addProductModal').on('show.bs.modal', function() {
  $.getJSON('api_product.php?action=get_next_product_id', function(res) {
    if (res && res.success && res.seq_formatted) {
      GLOBAL_NEXT_SEQ = res.seq_formatted;
      onAddCategoryChange();
    }
  });
});

// ເພີ່ມແຖວຫົວໜ່ວຍເພີ່ມເຕີມ
function addUnitRow(mode, data) {
  var container = mode === 'add' ? $('#add_multi_units_container') : $('#edit_multi_units_container');
  var unitName   = data ? data.unit_name : '';
  var multiplier = data ? data.multiplier : '';
  var bprice     = data && data.bprice > 0 ? Number(data.bprice).toLocaleString('en-US') : '';
  var price      = data && data.price > 0 ? Number(data.price).toLocaleString('en-US') : '';
  var barcode    = data ? (data.barcode || '') : '';

  var rowHtml = `
    <div class="card bg-light border-0 mb-2 p-2 unit-row shadow-none" style="border-radius: 8px;">
      <div class="row align-items-center">
        <div class="col-md-3 col-6 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">ຊື່ຫົວໜ່ວຍ: <span class="text-danger">*</span></label>
          <input type="text" name="extra_unit_name[]" class="form-control form-control-sm" placeholder="ເຊັ່ນ: ແພັກ, ແກັດ..." value="${unitName}" required>
        </div>
        <div class="col-md-2 col-6 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">ຈຳນວນ: <span class="text-danger">*</span></label>
          <input type="number" name="extra_unit_multiplier[]" class="form-control form-control-sm text-center" placeholder="ເຊັ່ນ: 6" min="2" value="${multiplier}" required>
        </div>
        <div class="col-md-2 col-6 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">ລາຄາຊື້:</label>
          <input type="text" name="extra_unit_bprice[]" class="form-control form-control-sm text-right price-input" placeholder="0" value="${bprice}" oninput="formatPriceInput(this)">
        </div>
        <div class="col-md-2 col-6 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">ລາຄາຂາຍ: <span class="text-danger">*</span></label>
          <input type="text" name="extra_unit_price[]" class="form-control form-control-sm text-right font-weight-bold text-success price-input" placeholder="0" value="${price}" oninput="formatPriceInput(this)" required>
        </div>
        <div class="col-md-2 col-10 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">ບາໂຄ້ດ:</label>
          <div class="input-group input-group-sm">
            <input type="text" name="extra_unit_barcode[]" class="form-control form-control-sm" placeholder="ບາໂຄ້ດ..." value="${barcode}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-primary" onclick="generateEAN13(this)" title="ສ້າງບາໂຄ້ດ 13 ຫຼັກ">
                <i class="fas fa-barcode"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-1 col-2 text-right pt-md-3">
          <button type="button" class="btn btn-sm btn-outline-danger border-0" title="ລົບແຖວນີ້" onclick="$(this).closest('.unit-row').remove();">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </div>
    </div>
  `;
  container.append(rowHtml);
}

function submitAddProduct(e) {
  var catId   = $('#add_category_id').val();
  var name    = $('#add_product_name').val().trim();
  var barcode = $('#add_barcode').val().trim();
  var unit    = $('#add_unit').val().trim();
  var bprice  = $('#add_bprice').val().trim();
  var price   = $('#add_price').val().trim();

  if (!catId) {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະລຸນາເລືອກປະເພດສິນຄ້າ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $('#add_category_id').focus();
    });
    return false;
  }
  if (name === '') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະລຸນາປ້ອນຊື່ສິນຄ້າ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $('#add_product_name').focus();
    });
    return false;
  }
  if (barcode === '') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະລຸນາປ້ອນ ຫຼື ສະແກນລະຫັດບາໂຄ້ດສິນຄ້າ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $('#add_barcode').focus();
    });
    return false;
  }
  if (!/^[0-9]+$/.test(barcode)) {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ລະຫັດບາໂຄ້ດຮອງຮັບສະເພາະຕົວເລກ (0-9) ເທົ່ານັ້ນ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $('#add_barcode').focus();
    });
    return false;
  }
  if (unit === '') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະລຸນາປ້ອນຫົວໜ່ວຍ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $('#add_unit').focus();
    });
    return false;
  }
  if (bprice === '' || bprice === '0') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະລຸນາປ້ອນລາຄາຊື້!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $('#add_bprice').focus();
    });
    return false;
  }
  if (price === '' || price === '0') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະລຸນາປ້ອນລາຄາຂາຍ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $('#add_price').focus();
    });
    return false;
  }

  // Dynamic multi-unit validation
  var validExtra = true;
  $('#add_multi_units_container .unit-row').each(function() {
    var uName  = $(this).find('input[name="extra_unit_name[]"]').val().trim();
    var uMult  = $(this).find('input[name="extra_unit_multiplier[]"]').val().trim();
    var uPrice = $(this).find('input[name="extra_unit_price[]"]').val().trim();

    if (uName === '') {
      if (e) e.preventDefault();
      var inputEl = $(this).find('input[name="extra_unit_name[]"]');
      Swal.fire({
        icon: 'warning',
        title: 'ແຈ້ງເຕືອນ',
        text: 'ກະລຸນາປ້ອນຊື່ຫົວໜ່ວຍເພີ່ມເຕີມ!',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'ຕົກລົງ'
      }).then(function() {
        inputEl.focus();
      });
      validExtra = false;
      return false;
    }
    if (uMult === '' || parseInt(uMult) <= 0) {
      if (e) e.preventDefault();
      var inputEl = $(this).find('input[name="extra_unit_multiplier[]"]');
      Swal.fire({
        icon: 'warning',
        title: 'ແຈ້ງເຕືອນ',
        text: 'ກະລຸນາປ້ອນຈຳນວນຫົວໜ່ວຍເພີ່ມເຕີມ!',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'ຕົກລົງ'
      }).then(function() {
        inputEl.focus();
      });
      validExtra = false;
      return false;
    }
    if (uPrice === '' || uPrice === '0') {
      if (e) e.preventDefault();
      var inputEl = $(this).find('input[name="extra_unit_price[]"]');
      Swal.fire({
        icon: 'warning',
        title: 'ແຈ້ງເຕືອນ',
        text: 'ກະລຸນາປ້ອນລາຄາຂາຍຫົວໜ່ວຍເພີ່ມເຕີມ!',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'ຕົກລົງ'
      }).then(function() {
        inputEl.focus();
      });
      validExtra = false;
      return false;
    }
  });

  if (!validExtra) return false;

  // Check form internal duplicate barcodes
  var formBarcodes = [];
  var hasDuplicate = false;
  var dupValue = '';
  $('#addProductForm').find('#add_barcode, input[name="extra_unit_barcode[]"]').each(function() {
    var val = $(this).val().trim();
    if (val !== '') {
      if (formBarcodes.indexOf(val) !== -1) {
        hasDuplicate = true;
        dupValue = val;
        return false;
      }
      formBarcodes.push(val);
    }
  });

  if (hasDuplicate) {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ລະຫັດບາໂຄ້ດຊໍ້າກັນ!',
      text: 'ລະຫັດບາໂຄ້ດ "' + dupValue + '" ນີ້ຊໍ້າກັນກັບຫົວໜ່ວຍອື່ນໃນຟອມດຽວກັນ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    });
    return false;
  }

  return true;
}

// Global Barcode Duplicate Checker Function
function checkBarcodeDuplicate(inputEl, productId) {
  var $input = $(inputEl);
  var barcode = $input.val().trim();
  if (!barcode) return;

  var $form = $input.closest('form');

  // 1. Check for duplicate within the same form
  var allBarcodes = [];
  $form.find('#add_barcode, #edit_barcode, input[name="extra_unit_barcode[]"]').each(function() {
    var val = $(this).val().trim();
    if (val !== '') {
      allBarcodes.push({ el: this, val: val });
    }
  });

  var duplicateCount = 0;
  for (var i = 0; i < allBarcodes.length; i++) {
    if (allBarcodes[i].val === barcode) {
      duplicateCount++;
    }
  }

  if (duplicateCount > 1) {
    Swal.fire({
      icon: 'warning',
      title: 'ລະຫັດບາໂຄ້ດຊໍ້າກັນ!',
      text: 'ລະຫັດບາໂຄ້ດ "' + barcode + '" ນີ້ຊໍ້າກັນກັບຫົວໜ່ວຍອື່ນໃນຟອມດຽວກັນ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $input.val('').focus();
    });
    return false;
  }

  // 2. Check duplicate against Database via AJAX
  $.ajax({
    url: 'api_product.php',
    type: 'GET',
    dataType: 'json',
    data: {
      action: 'check_barcode',
      barcode: barcode,
      product_id: productId || 0
    },
    success: function(res) {
      if (res && res.exists) {
        Swal.fire({
          icon: 'warning',
          title: 'ລະຫັດບາໂຄ້ດຊໍ້າກັນ!',
          text: res.message || ('ລະຫັດບາໂຄ້ດ "' + barcode + '" ນີ້ມີໃນລະບົບແລ້ວ!'),
          confirmButtonColor: '#2563eb',
          confirmButtonText: 'ຕົກລົງ'
        }).then(function() {
          $input.val('').focus();
        });
      }
    }
  });
}

// Event Listeners for Add Product Barcode checking
$(document).on('change blur', '#add_barcode', function() {
  checkBarcodeDuplicate(this, 0);
});

$(document).on('change blur', '#addProductForm input[name="extra_unit_barcode[]"]', function() {
  checkBarcodeDuplicate(this, 0);
});

// Reset add modal on close
$('#addProductModal').on('hidden.bs.modal', function() {
  $('#addProductForm')[0].reset();
  $('#add_product_id_display').val('');
  $('#add_product_id').val('');
  $('#add_multi_units_container').empty();
  removeProductImg('add');
});

// Prevent Enter key in barcode inputs from prematurely submitting form
$(document).on('keydown', '#add_barcode, input[name="extra_unit_barcode[]"]', function(e) {
  if (e.key === 'Enter' || e.keyCode === 13) {
    e.preventDefault();
    return false;
  }
});
</script>
