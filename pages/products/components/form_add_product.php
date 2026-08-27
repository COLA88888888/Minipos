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
            <i class="fas fa-plus-circle mr-2"></i> <?php echo htmlspecialchars(t('products.modal_add_title', 'ເພີ່ມສິນຄ້າໃໝ່')); ?>
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
                     title="<?php echo htmlspecialchars(t('products.img_click_select_title', 'ຄລິກເພື່ອເລືອກຮູບພາບ')); ?>">
                
                <!-- Camera button -->
                <button type="button"
                        class="position-absolute d-flex align-items-center justify-content-center shadow-sm border-0"
                        style="right: -6px; bottom: -6px; width: 34px; height: 34px; background: #2563eb; color: #fff; border-radius: 50%; border: 2px solid #fff; cursor: pointer; z-index: 2;"
                        onclick="document.getElementById('add_product_img').click();"
                        title="<?php echo htmlspecialchars(t('products.img_select_btn_title', 'ເລືອກຮູບ')); ?>">
                  <i class="fas fa-camera" style="font-size: 0.85rem;"></i>
                </button>

                <!-- Remove button (hidden by default) -->
                <button type="button" id="btn_remove_add_img"
                        onclick="removeProductImg('add');"
                        class="position-absolute align-items-center justify-content-center border-0 shadow-sm"
                        style="right: -6px; top: -6px; width: 30px; height: 30px; background: #ef4444; color: #fff; border-radius: 50%; border: 2px solid #fff; cursor: pointer; display: none; z-index: 3;"
                        title="<?php echo htmlspecialchars(t('products.img_remove_btn_title', 'ລົບຮູບ')); ?>">
                  <i class="fas fa-times" style="font-size: 0.82rem;"></i>
                </button>
              </div>

              <small class="text-muted mt-1 font-weight-bold" style="font-size: 0.78rem;"><?php echo htmlspecialchars(t('products.img_select_hint', 'ຄລິກເພື່ອເລືອກຮູບສິນຄ້າ')); ?></small>
              <input type="file" name="product_img" id="add_product_img" accept="image/*" style="display: none;"
                     onchange="previewProductImg(this, 'add_img_preview', 'btn_remove_add_img')">
            </div>

            <!-- RIGHT: Base Form Fields -->
            <div class="col-md-8 pl-md-4 py-2">
              
              <!-- Row 1: ປະເພດສິນຄ້າ (ຂຶ້ນກ່ອນ) & ລະຫັດສິນຄ້າ (Auto) -->
              <div class="row">
                <div class="col-md-7 mb-3">
                  <label class="font-weight-bold text-dark mb-1"><?php echo htmlspecialchars(t('products.field_category', 'ປະເພດສິນຄ້າ:')); ?> <span class="text-danger">*</span></label>
                  <select id="add_category_id" name="category_id" class="form-control" style="border-radius: 6px; height: 40px;" onchange="onAddCategoryChange()" required>
                    <option value=""><?php echo htmlspecialchars(t('products.select_category_placeholder', '-- ເລືອກປະເພດ --')); ?></option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-5 mb-3">
                  <label class="font-weight-bold text-dark mb-1"><?php echo htmlspecialchars(t('products.field_product_code', 'ລະຫັດສິນຄ້າ:')); ?></label>
                  <input type="text" id="add_product_id_display" class="form-control bg-light font-weight-bold text-primary" placeholder="<?php echo htmlspecialchars(t('products.product_code_placeholder', 'ເລືອກປະເພດສິນຄ້າ...')); ?>" style="border-radius: 6px; height: 40px;" readonly>
                </div>
              </div>

              <!-- Row 2: ຊື່ສິນຄ້າ & ບາໂຄ້ດຫຼັກ -->
              <div class="row">
                <div class="col-md-7 mb-3">
                  <label class="font-weight-bold text-dark mb-1"><?php echo htmlspecialchars(t('products.field_product_name', 'ຊື່ສິນຄ້າ:')); ?> <span class="text-danger">*</span></label>
                  <input type="text" id="add_product_name" name="product_name" class="form-control" placeholder="<?php echo htmlspecialchars(t('products.product_name_placeholder', 'ປ້ອນຊື່ສິນຄ້າ...')); ?>" style="border-radius: 6px; height: 40px;" required>
                </div>
                <div class="col-md-5 mb-3">
                  <label class="font-weight-bold text-dark mb-1"><?php echo htmlspecialchars(t('products.field_barcode', 'ບາໂຄ້ດ:')); ?> <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" id="add_barcode" name="barcode" class="form-control" placeholder="<?php echo htmlspecialchars(t('products.barcode_placeholder', 'ສະແກນ ຫຼື ປ້ອນຕົວເລກ...')); ?>" style="border-radius: 6px 0 0 6px; height: 40px;" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-primary font-weight-bold" onclick="generateEAN13('add_barcode')" title="<?php echo htmlspecialchars(t('products.gen_barcode_title', 'ສ້າງບາໂຄ້ດ 13 ຫຼັກ')); ?>" style="border-radius: 0 6px 6px 0; height: 40px; border-color: #cbd5e1;">
                        <i class="fas fa-barcode mr-1"></i> <?php echo htmlspecialchars(t('products.gen_barcode_btn', 'ສ້າງ')); ?>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Row 3: ຫົວໜ່ວຍຍ່ອຍພື້ນຖານ & ຈຳນວນສະຕັອກ (ສະເພາະແອັດມິນ) -->
              <div class="row">
                <div class="<?php echo $isAdmin ? 'col-md-7' : 'col-md-12'; ?> mb-3">
                  <label class="font-weight-bold text-dark mb-1"><?php echo htmlspecialchars(t('products.field_unit', 'ຫົວໜ່ວຍ:')); ?> <span class="text-danger">*</span></label>
                  <select name="unit_id" id="add_unit" class="form-control" style="border-radius: 6px; height: 40px;" required>
                    <option value=""><?php echo htmlspecialchars(t('products.select_unit_placeholder', '-- ເລືອກຫົວໜ່ວຍ --')); ?></option>
                    <?php foreach ($units as $u): ?>
                      <option value="<?php echo (int)$u['unit_id']; ?>"><?php echo htmlspecialchars($u['unit_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if ($isAdmin): ?>
                  <div class="col-md-5 mb-3">
                    <label class="font-weight-bold text-dark mb-1">
                      <?php echo htmlspecialchars(t('products.field_qty', 'ຈຳນວນສະຕັອກ:')); ?>
                      <small class="text-muted font-weight-normal"><?php echo htmlspecialchars(t('products.field_qty_optional', '(ຖ້າມີ)')); ?></small>
                    </label>
                    <input type="number" name="qty" id="add_qty" class="form-control text-center font-weight-bold text-primary" placeholder="0" min="0" value="0" style="border-radius: 6px; height: 40px;">
                  </div>
                <?php endif; ?>
              </div>

              <!-- Row 4: ລາຄາຊື້ & ລາຄາຂາຍຍ່ອຍ -->
              <div class="row">
                <div class="col-6">
                  <label class="font-weight-bold text-dark mb-1"><?php echo htmlspecialchars(t('products.field_bprice', 'ລາຄາຊື້:')); ?><span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" id="add_bprice" name="bprice" class="form-control price-input text-right font-weight-bold" placeholder="0" style="border-radius: 6px 0 0 6px; height: 40px; color: #475569;" oninput="formatPriceInput(this)" required>
                    <div class="input-group-append"><span class="input-group-text" style="border-radius: 0 6px 6px 0; font-size: 0.85rem;">₭</span></div>
                  </div>
                </div>
                <div class="col-6">
                  <label class="font-weight-bold text-dark mb-1"><?php echo htmlspecialchars(t('products.field_price', 'ລາຄາຂາຍ:')); ?> <span class="text-danger">*</span></label>
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
                   <?php echo htmlspecialchars(t('products.multi_unit_title', 'ລາຄາ ແລະ ຫົວໜ່ວຍເພີ່ມເຕີມ (ຫຼາຍລາຄາ)')); ?>
                </span>
                <small class="text-muted d-block" style="font-size: 0.78rem;"><?php echo htmlspecialchars(t('products.multi_unit_hint', 'ຖ້າສິນຄ້ານີ້ມີຂາຍເປັນ ແພັກ (x6 ປ໋ອງ), ແກັດ (x24 ປ໋ອງ)... ສາມາດເພີ່ມໄດ້ທີ່ນີ້ (ຕັດສະຕັອກອັດຕະໂນມັດ)')); ?></small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px; font-size: 0.82rem;" onclick="addUnitRow('add')">
                 <?php echo htmlspecialchars(t('products.btn_add_unit', 'ເພີ່ມລາຄາ/ຫົວໜ່ວຍ')); ?>
              </button>
            </div>

            <div id="add_multi_units_container">
              <!-- Dynamic Unit Rows -->
            </div>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light font-weight-bold px-4" style="border-radius: 6px; cursor: pointer;" data-dismiss="modal"><?php echo htmlspecialchars(t('products.cancel', 'ຍົກເລີກ')); ?></button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 6px; cursor: pointer; background: linear-gradient(135deg, #2c5aa0, #244886); border: none;">
            <i class="fas fa-save mr-1"></i> <?php echo htmlspecialchars(t('products.btn_save', 'ບັນທຶກ')); ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var I18N_ADD_PRODUCT = <?php echo tjson([
    'products.alert_notice_title' => 'ແຈ້ງເຕືອນ',
    'products.btn_ok' => 'ຕົກລົງ',
    'products.validate_category' => 'ກະລຸນາເລືອກປະເພດສິນຄ້າ!',
    'products.validate_name' => 'ກະລຸນາປ້ອນຊື່ສິນຄ້າ!',
    'products.validate_barcode_required' => 'ກະລຸນາປ້ອນ ຫຼື ສະແກນລະຫັດບາໂຄ້ດສິນຄ້າ!',
    'products.validate_barcode_numeric' => 'ລະຫັດບາໂຄ້ດຮອງຮັບສະເພາະຕົວເລກ (0-9) ເທົ່ານັ້ນ!',
    'products.validate_unit' => 'ກະລຸນາປ້ອນຫົວໜ່ວຍ!',
    'products.validate_bprice' => 'ກະລຸນາປ້ອນລາຄາຊື້!',
    'products.validate_price' => 'ກະລຸນາປ້ອນລາຄາຂາຍ!',
    'products.validate_unit_name' => 'ກະລຸນາປ້ອນຊື່ຫົວໜ່ວຍເພີ່ມເຕີມ!',
    'products.validate_unit_qty' => 'ກະລຸນາປ້ອນຈຳນວນຫົວໜ່ວຍເພີ່ມເຕີມ!',
    'products.validate_unit_price' => 'ກະລຸນາປ້ອນລາຄາຂາຍຫົວໜ່ວຍເພີ່ມເຕີມ!',
    'products.alert_duplicate_title' => 'ລະຫັດບາໂຄ້ດຊໍ້າກັນ!',
    'products.duplicate_in_form_text' => 'ລະຫັດບາໂຄ້ດ "{value}" ນີ້ຊໍ້າກັນກັບຫົວໜ່ວຍອື່ນໃນຟອມດຽວກັນ!',
    'products.duplicate_in_db_text' => 'ລະຫັດບາໂຄ້ດ "{value}" ນີ້ມີໃນລະບົບແລ້ວ!',
    'products.unit_row_name_label' => 'ຊື່ຫົວໜ່ວຍ:',
    'products.unit_row_name_placeholder' => 'ເຊັ່ນ: ແພັກ, ແກັດ...',
    'products.unit_row_qty_label' => 'ຈຳນວນ:',
    'products.unit_row_qty_placeholder' => 'ເຊັ່ນ: 6',
    'products.unit_row_bprice_label' => 'ລາຄາຊື້:',
    'products.unit_row_price_label' => 'ລາຄາຂາຍ:',
    'products.unit_row_barcode_label' => 'ບາໂຄ້ດ:',
    'products.unit_row_barcode_placeholder' => 'ບາໂຄ້ດ...',
    'products.gen_barcode_title' => 'ສ້າງບາໂຄ້ດ 13 ຫຼັກ',
    'products.unit_row_delete_title' => 'ລົບແຖວນີ້',
]); ?>;

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
          <label class="small text-muted font-weight-bold mb-1">${I18N_ADD_PRODUCT['products.unit_row_name_label']} <span class="text-danger">*</span></label>
          <input type="text" name="extra_unit_name[]" class="form-control form-control-sm" placeholder="${I18N_ADD_PRODUCT['products.unit_row_name_placeholder']}" value="${unitName}" required>
        </div>
        <div class="col-md-2 col-6 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">${I18N_ADD_PRODUCT['products.unit_row_qty_label']} <span class="text-danger">*</span></label>
          <input type="number" name="extra_unit_multiplier[]" class="form-control form-control-sm text-center" placeholder="${I18N_ADD_PRODUCT['products.unit_row_qty_placeholder']}" min="2" value="${multiplier}" required>
        </div>
        <div class="col-md-2 col-6 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">${I18N_ADD_PRODUCT['products.unit_row_bprice_label']}</label>
          <input type="text" name="extra_unit_bprice[]" class="form-control form-control-sm text-right price-input" placeholder="0" value="${bprice}" oninput="formatPriceInput(this)">
        </div>
        <div class="col-md-2 col-6 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">${I18N_ADD_PRODUCT['products.unit_row_price_label']} <span class="text-danger">*</span></label>
          <input type="text" name="extra_unit_price[]" class="form-control form-control-sm text-right font-weight-bold text-success price-input" placeholder="0" value="${price}" oninput="formatPriceInput(this)" required>
        </div>
        <div class="col-md-2 col-10 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">${I18N_ADD_PRODUCT['products.unit_row_barcode_label']}</label>
          <div class="input-group input-group-sm">
            <input type="text" name="extra_unit_barcode[]" class="form-control form-control-sm" placeholder="${I18N_ADD_PRODUCT['products.unit_row_barcode_placeholder']}" value="${barcode}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-primary" onclick="generateEAN13(this)" title="${I18N_ADD_PRODUCT['products.gen_barcode_title']}">
                <i class="fas fa-barcode"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-1 col-2 text-right pt-md-3">
          <button type="button" class="btn btn-sm btn-outline-danger border-0" title="${I18N_ADD_PRODUCT['products.unit_row_delete_title']}" onclick="$(this).closest('.unit-row').remove();">
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
      title: I18N_ADD_PRODUCT['products.alert_notice_title'],
      text: I18N_ADD_PRODUCT['products.validate_category'],
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
    }).then(function() {
      $('#add_category_id').focus();
    });
    return false;
  }
  if (name === '') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: I18N_ADD_PRODUCT['products.alert_notice_title'],
      text: I18N_ADD_PRODUCT['products.validate_name'],
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
    }).then(function() {
      $('#add_product_name').focus();
    });
    return false;
  }
  if (barcode === '') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: I18N_ADD_PRODUCT['products.alert_notice_title'],
      text: I18N_ADD_PRODUCT['products.validate_barcode_required'],
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
    }).then(function() {
      $('#add_barcode').focus();
    });
    return false;
  }
  if (!/^[0-9]+$/.test(barcode)) {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: I18N_ADD_PRODUCT['products.alert_notice_title'],
      text: I18N_ADD_PRODUCT['products.validate_barcode_numeric'],
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
    }).then(function() {
      $('#add_barcode').focus();
    });
    return false;
  }
  if (unit === '') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: I18N_ADD_PRODUCT['products.alert_notice_title'],
      text: I18N_ADD_PRODUCT['products.validate_unit'],
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
    }).then(function() {
      $('#add_unit').focus();
    });
    return false;
  }
  if (bprice === '' || bprice === '0') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: I18N_ADD_PRODUCT['products.alert_notice_title'],
      text: I18N_ADD_PRODUCT['products.validate_bprice'],
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
    }).then(function() {
      $('#add_bprice').focus();
    });
    return false;
  }
  if (price === '' || price === '0') {
    if (e) e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: I18N_ADD_PRODUCT['products.alert_notice_title'],
      text: I18N_ADD_PRODUCT['products.validate_price'],
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
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
        title: I18N_ADD_PRODUCT['products.alert_notice_title'],
        text: I18N_ADD_PRODUCT['products.validate_unit_name'],
        confirmButtonColor: '#2563eb',
        confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
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
        title: I18N_ADD_PRODUCT['products.alert_notice_title'],
        text: I18N_ADD_PRODUCT['products.validate_unit_qty'],
        confirmButtonColor: '#2563eb',
        confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
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
        title: I18N_ADD_PRODUCT['products.alert_notice_title'],
        text: I18N_ADD_PRODUCT['products.validate_unit_price'],
        confirmButtonColor: '#2563eb',
        confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
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
      title: I18N_ADD_PRODUCT['products.alert_duplicate_title'],
      text: I18N_ADD_PRODUCT['products.duplicate_in_form_text'].replace('{value}', dupValue),
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
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
      title: I18N_ADD_PRODUCT['products.alert_duplicate_title'],
      text: I18N_ADD_PRODUCT['products.duplicate_in_form_text'].replace('{value}', barcode),
      confirmButtonColor: '#2563eb',
      confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
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
          title: I18N_ADD_PRODUCT['products.alert_duplicate_title'],
          text: res.message || I18N_ADD_PRODUCT['products.duplicate_in_db_text'].replace('{value}', barcode),
          confirmButtonColor: '#2563eb',
          confirmButtonText: I18N_ADD_PRODUCT['products.btn_ok']
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
