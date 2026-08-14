<!-- ============================================================
     print_barcode_controls.php - ແຜງຄວບຄຸມການຄົ້ນຫາ, Filter ແລະ ຕັ້ງຄ່າສະຕິກເກີ
     ============================================================ -->
<div class="barcode-page-card p-3 mb-4">
  <div class="row align-items-center gy-2">
    <!-- 1. ຊ່ອງຄົ້ນຫາຊື່ສິນຄ້າ ຫຼື ບາໂຄ້ດ -->
    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;">
            <i class="fas fa-search text-muted"></i>
          </span>
        </div>
        <input type="text" id="pageBarcodeSearch" class="form-control border-left-0" 
               placeholder="ຄົ້ນຫາຊື່ສິນຄ້າ ຫຼື ບາໂຄ້ດ..." 
               style="border-radius: 0 8px 8px 0; font-size: 0.9rem;" 
               onkeyup="renderPageBarcodeBlocks()">
      </div>
    </div>

    <!-- 2. ຕົວເລືອກ Filter ປະເພດສິນຄ້າ -->
    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
      <select id="pageCategoryFilter" class="form-control" style="border-radius: 8px; font-size: 0.9rem;" onchange="renderPageBarcodeBlocks()">
        <option value="">-- ເລືອກປະເພດສິນຄ້າທັງໝົດ --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- 3. ປຸ່ມເລືອກທັງໝົດ/ຍົກເລີກ ແລະ ປຸ່ມຕັ້ງຈຳນວນດວງດ່ວນ (Quick Setters) -->
    <div class="col-md-5 col-12 d-flex flex-wrap align-items-center justify-content-md-end" style="gap: 6px;">
      <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px;" onclick="selectAllPageProducts(true)">
        <i class="fas fa-check-square mr-1"></i> ເລືອກທັງໝົດ
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 6px;" onclick="selectAllPageProducts(false)">
        <i class="far fa-square mr-1"></i> ຍົກເລີກທັງໝົດ
      </button>

      <div class="btn-group btn-group-sm">
        <span class="btn btn-sm btn-light border disabled font-weight-bold text-dark px-2" style="font-size: 0.78rem;">ຕັ້ງດວງ:</span>
        <button type="button" class="btn btn-sm btn-light border font-weight-bold" onclick="setAllPageCopies(1)">1</button>
        <button type="button" class="btn btn-sm btn-light border font-weight-bold" onclick="setAllPageCopies(2)">2</button>
        <button type="button" class="btn btn-sm btn-light border font-weight-bold" onclick="setAllPageCopies(5)">5</button>
        <button type="button" class="btn btn-sm btn-light border font-weight-bold" onclick="setAllPageCopies(10)">10</button>
      </div>
    </div>
  </div>

  <!-- 4. ຕົວເລືອກສະແດງຜົນ (ຊື່ຮ້ານ, ຊື່ສິນຄ້າ, ລາຄາ) ແລະ ເລືອກຂະໜາດສະຕິກເກີ (40x30, 50x30, A4) -->
  <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 pt-2 border-top" style="gap: 12px;">
    
    <div class="d-flex flex-wrap align-items-center" style="gap: 16px;">
      <small class="font-weight-bold text-secondary"><i class="fas fa-sliders-h mr-1"></i> ຕົວເລືອກສະແດງ:</small>
      <div class="custom-control custom-checkbox custom-control-inline mb-0">
        <input type="checkbox" class="custom-control-input" id="opt_show_shop" checked onchange="renderPageBarcodeBlocks()">
        <label class="custom-control-label font-weight-bold text-dark" for="opt_show_shop" style="font-size: 0.85rem; cursor: pointer;">
          ຊື່ຮ້ານ (<?php echo htmlspecialchars($company_name); ?>)
        </label>
      </div>
      <div class="custom-control custom-checkbox custom-control-inline mb-0">
        <input type="checkbox" class="custom-control-input" id="opt_show_name" checked onchange="renderPageBarcodeBlocks()">
        <label class="custom-control-label font-weight-bold text-dark" for="opt_show_name" style="font-size: 0.85rem; cursor: pointer;">
          ຊື່ສິນຄ້າ
        </label>
      </div>
      <div class="custom-control custom-checkbox custom-control-inline mb-0">
        <input type="checkbox" class="custom-control-input" id="opt_show_price" checked onchange="renderPageBarcodeBlocks()">
        <label class="custom-control-label font-weight-bold text-dark" for="opt_show_price" style="font-size: 0.85rem; cursor: pointer;">
          ລາຄາຂາຍ
        </label>
      </div>
    </div>

    <div class="d-flex align-items-center" style="gap: 8px;">
      <small class="font-weight-bold text-secondary"><i class="fas fa-expand mr-1"></i> ຂະໜາດສະຕິກເກີ:</small>
      <select id="print_size" class="form-control form-control-sm font-weight-bold text-dark" style="border-radius: 6px; width: 210px;" onchange="renderPageBarcodeBlocks()">
        <option value="40x30" selected>40mm x 30mm (ມ້ວນມາດຕະຖານ)</option>
        <option value="50x30">50mm x 30mm (ມ້ວນກາງ)</option>
        <option value="a4">ເຈ້ຍ A4 (ຕາຕະລາງ)</option>
      </select>
    </div>

  </div>
</div>