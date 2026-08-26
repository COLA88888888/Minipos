<!-- Modal: Edit Product -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 860px;">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <form id="editProductForm" novalidate action="" method="POST" enctype="multipart/form-data" onsubmit="return submitEditProduct(event)">
        <input type="hidden" name="action" value="edit_product">
        <input type="hidden" name="product_id" id="edit_product_id">
        <input type="hidden" name="remove_img" id="edit_remove_img" value="0">

        <!-- HEADER -->
        <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #2c5aa0, #244886) !important;">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao', 'Souliyo', 'Boon', sans-serif; font-size: 1.15rem;">
            <i class="fas fa-edit mr-2"></i> ແກ້ໄຂຂໍ້ມູນສິນຄ້າ
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
                <img id="edit_img_preview"
                     src="<?php echo $base_path; ?>assets/img/image.jpg"
                     class="shadow-sm"
                     style="width: 140px; height: 140px; object-fit: cover; border-radius: 14px; border: 2px dashed #006affff; background: #f8fafc; cursor: pointer;"
                     onclick="document.getElementById('edit_product_img').click();"
                     title="ຄລິກເພື່ອປ່ຽນຮູບພາບ">
                
                <!-- Camera button -->
                <button type="button"
                        class="position-absolute d-flex align-items-center justify-content-center shadow-sm border-0"
                        style="right: -6px; bottom: -6px; width: 34px; height: 34px; background: #006affff; color: #fff; border-radius: 50%; border: 2px solid #fff; cursor: pointer; z-index: 2;"
                        onclick="document.getElementById('edit_product_img').click();"
                        title="ເລືອກຮູບໃໝ່">
                  <i class="fas fa-camera" style="font-size: 0.85rem;"></i>
                </button>

                <!-- Remove button (hidden by default unless product has image or new image selected) -->
                <button type="button" id="btn_remove_edit_img"
                        onclick="removeProductImg('edit');"
                        class="position-absolute align-items-center justify-content-center border-0 shadow-sm"
                        style="right: -6px; top: -6px; width: 30px; height: 30px; background: #ef4444; color: #fff; border-radius: 50%; border: 2px solid #fff; cursor: pointer; display: none; z-index: 3;"
                        title="ລົບຮູບ">
                  <i class="fas fa-times" style="font-size: 0.82rem;"></i>
                </button>
              </div>

              <small class="text-muted mt-1 font-weight-bold" style="font-size: 0.78rem;">ຄລິກເພື່ອປ່ຽນຮູບສິນຄ້າ</small>
              <input type="file" name="product_img" id="edit_product_img" accept="image/*" style="display: none;"
                     onchange="previewProductImg(this, 'edit_img_preview', 'btn_remove_edit_img')">
            </div>

            <!-- RIGHT: Base Form Fields -->
            <div class="col-md-8 pl-md-4 py-2">
              
              <!-- Row 1: ປະເພດສິນຄ້າ & ລະຫັດສິນຄ້າ (Readonly) -->
              <div class="row">
                <div class="col-md-7 mb-3">
                  <label class="font-weight-bold text-dark mb-1">ປະເພດສິນຄ້າ: <span class="text-danger">*</span></label>
                  <select name="category_id" id="edit_category_id" class="form-control" style="border-radius: 6px; height: 40px;" required>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-5 mb-3">
                  <label class="font-weight-bold text-dark mb-1">ລະຫັດສິນຄ້າ:</label>
                  <input type="text" id="edit_product_id_display" class="form-control bg-light font-weight-bold text-primary" style="border-radius: 6px; height: 40px;" readonly>
                </div>
              </div>

              <!-- Row 2: ຊື່ສິນຄ້າ & ບາໂຄ້ດຫຼັກ -->
              <div class="row">
                <div class="col-md-7 mb-3">
                  <label class="font-weight-bold text-dark mb-1">ຊື່ສິນຄ້າ: <span class="text-danger">*</span></label>
                  <input type="text" name="product_name" id="edit_product_name" class="form-control" placeholder="ປ້ອນຊື່ສິນຄ້າ..." style="border-radius: 6px; height: 40px;" required>
                </div>
                <div class="col-md-5 mb-3">
                  <label class="font-weight-bold text-dark mb-1">ບາໂຄ້ດ: <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" name="barcode" id="edit_barcode" class="form-control" placeholder="ບາໂຄ້ດ..." style="border-radius: 6px 0 0 6px; height: 40px;" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-primary font-weight-bold" onclick="generateEAN13('edit_barcode')" title="ສ້າງບາໂຄ້ດ 13 ຫຼັກ" style="border-radius: 0 6px 6px 0; height: 40px; border-color: #cbd5e1;">
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
                  <select name="unit" id="edit_unit" class="form-control" style="border-radius: 6px; height: 40px;" required>
                    <option value="">-- ເລືອກຫົວໜ່ວຍ --</option>
                    <?php foreach ($units as $u): ?>
                      <option value="<?php echo htmlspecialchars($u['unit_name']); ?>"><?php echo htmlspecialchars($u['unit_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if ($isAdmin): ?>
                  <div class="col-md-5 mb-3">
                    <label class="font-weight-bold text-dark mb-1">ຈຳນວນສະຕັອກ:</label>
                    <input type="number" name="qty" id="edit_qty" class="form-control text-center font-weight-bold text-primary" placeholder="0" min="0" style="border-radius: 6px; height: 40px;">
                  </div>
                <?php endif; ?>
              </div>

              <!-- Row 4: ລາຄາຊື້ & ລາຄາຂາຍຍ່ອຍ -->
              <div class="row">
                <div class="col-6">
                  <label class="font-weight-bold text-dark mb-1">ລາຄາຊື້:<span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" name="bprice" id="edit_bprice" class="form-control price-input text-right font-weight-bold" placeholder="0" style="border-radius: 6px 0 0 6px; height: 40px; color: #475569;" oninput="formatPriceInput(this)" required>
                    <div class="input-group-append"><span class="input-group-text" style="border-radius: 0 6px 6px 0; font-size: 0.85rem;">₭</span></div>
                  </div>
                </div>
                <div class="col-6">
                  <label class="font-weight-bold text-dark mb-1">ລາຄາຂາຍຍ່ອຍ <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" name="price" id="edit_price" class="form-control price-input text-right font-weight-bold" placeholder="0" style="border-radius: 6px 0 0 6px; height: 40px; color: #16a34a;" oninput="formatPriceInput(this)" required>
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
                  <i class="fas fa-tags text-warning mr-1"></i> ລາຄາ ແລະ ຫົວໜ່ວຍເພີ່ມເຕີມ (ຫຼາຍລາຄາ)
                </span>
                <small class="text-muted d-block" style="font-size: 0.76rem;">ຖ້າສິນຄ້ານີ້ມີຂາຍເປັນ ແພັກ (x6 ປ໋ອງ), ແກັດ (x24 ປ໋ອງ)... ສາມາດເພີ່ມໄດ້ທີ່ນີ້ (ຕັດສະຕັອກອັດຕະໂນມັດ)</small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold" style="border-radius: 6px; font-size: 0.82rem;" onclick="addUnitRow('edit')">
                <i class="fas fa-plus mr-1"></i> ເພີ່ມລາຄາ/ຫົວໜ່ວຍ
              </button>
            </div>

            <div id="edit_multi_units_container">
              <!-- Dynamic Unit Rows loaded via AJAX -->
            </div>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light font-weight-bold px-4" style="border-radius: 8px; cursor: pointer;" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm text-white" style="border-radius: 8px; cursor: pointer; background: linear-gradient(135deg, #2c5aa0, #244886); border: none;">
            <i class="fas fa-save mr-1"></i> ອັບເດດ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function submitEditProduct(e) {
  var catId   = $('#edit_category_id').val();
  var name    = $('#edit_product_name').val().trim();
  var barcode = $('#edit_barcode').val().trim();
  var unit    = $('#edit_unit').val().trim();
  var bprice  = $('#edit_bprice').val().trim();
  var price   = $('#edit_price').val().trim();

  if (!catId) {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະລຸນາເລືອກປະເພດສິນຄ້າ!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    }).then(function() {
      $('#edit_category_id').focus();
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
      $('#edit_product_name').focus();
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
      $('#edit_barcode').focus();
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
      $('#edit_barcode').focus();
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
      $('#edit_unit').focus();
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
      $('#edit_bprice').focus();
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
      $('#edit_price').focus();
    });
    return false;
  }

  // Dynamic multi-unit validation
  var validExtra = true;
  $('#edit_multi_units_container .unit-row').each(function() {
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
  $('#editProductForm').find('#edit_barcode, input[name="extra_unit_barcode[]"]').each(function() {
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

// Event Listeners for Edit Product Barcode checking
$(document).on('change blur', '#edit_barcode', function() {
  var prodId = $('#edit_product_id').val();
  checkBarcodeDuplicate(this, prodId);
});

$(document).on('change blur', '#editProductForm input[name="extra_unit_barcode[]"]', function() {
  var prodId = $('#edit_product_id').val();
  checkBarcodeDuplicate(this, prodId);
});

// Prevent Enter key in barcode inputs from prematurely submitting form
$(document).on('keydown', '#edit_barcode, input[name="extra_unit_barcode[]"]', function(e) {
  if (e.key === 'Enter' || e.keyCode === 13) {
    e.preventDefault();
    return false;
  }
});
</script>
