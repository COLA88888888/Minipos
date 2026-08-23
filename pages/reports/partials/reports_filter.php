<?php if ($type === 'daily'): ?>
  <!-- Daily Report Date Filter Control Box (ON TOP ABOVE CARDS) -->
  <div class="report-filter-box no-print mb-3.5" style="padding: 14px 18px; border-radius: 12px; background: #ffffff; border: 1.5px solid #e2e8f0; box-shadow: 0 3px 12px rgba(0,0,0,0.03);">
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-flex align-items-end flex-wrap" style="gap: 14px;">
      <input type="hidden" name="type" value="daily">

      <!-- From Date -->
      <div style="flex: 0 0 auto; min-width: 170px;">
        <label class="font-weight-bold text-dark mb-1.5 d-block" style="font-size: 0.84rem; white-space: nowrap;">
          <i class="fas fa-calendar-alt text-primary mr-1"></i> ຕັ້ງແຕ່ວັນທີ:
        </label>
        <input type="date" name="from_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($from_date); ?>" style="border-radius: 8px; height: 38px; font-size: 0.85rem; border: 1.5px solid #cbd5e1;">
      </div>

      <!-- To Date -->
      <div style="flex: 0 0 auto; min-width: 170px;">
        <label class="font-weight-bold text-dark mb-1.5 d-block" style="font-size: 0.84rem; white-space: nowrap;">
          <i class="fas fa-calendar-check text-primary mr-1"></i> ຫາວັນທີ:
        </label>
        <input type="date" name="to_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($to_date); ?>" style="border-radius: 8px; height: 38px; font-size: 0.85rem; border: 1.5px solid #cbd5e1;">
      </div>

      <!-- Search & Refresh Buttons -->
      <div class="d-flex align-items-center" style="flex: 0 0 auto; gap: 6px;">
        <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3.5 d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, #2c5aa0, #244886); white-space: nowrap; font-size: 0.85rem;">
          <i class="fas fa-search mr-1.5"></i> ຄົ້ນຫາ
        </button>
        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?type=daily" class="btn btn-light btn-sm border font-weight-bold d-inline-flex align-items-center justify-content-center px-2.5" title="ລ້າງຄ່າ" style="border-radius: 8px; height: 38px;">
          <i class="fas fa-redo"></i>
        </a>
      </div>
    </form>
  </div>
<?php else: ?>
  <!-- Regular Search & Filter Controls Box (Full Width - Single Inline Row) -->
  <div class="report-filter-box no-print mb-3" style="padding: 12px 16px; width: 100%; box-sizing: border-box;">
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="reportFilterForm" style="display: flex; align-items: flex-end; flex-wrap: nowrap; gap: 8px; width: 100%;">
      <input type="hidden" name="type" value="<?php echo htmlspecialchars($type ?: 'all_sales'); ?>">

      <!-- From Date -->
      <div style="flex: 1 1 0; min-width: 120px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-alt text-primary mr-1"></i> ຕັ້ງແຕ່ວັນທີ:
        </label>
        <input type="date" name="from_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($from_date); ?>" style="border-radius: 8px; height: 38px; font-size: 0.85rem; width: 100%;">
      </div>

      <!-- To Date -->
      <div style="flex: 1 1 0; min-width: 120px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-check text-primary mr-1"></i> ຫາວັນທີ:
        </label>
        <input type="date" name="to_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($to_date); ?>" style="border-radius: 8px; height: 38px; font-size: 0.85rem; width: 100%;">
      </div>

      <!-- Search Input -->
      <div style="flex: 2 1 0; min-width: 160px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-search text-primary mr-1"></i> ຄົ້ນຫາ (ເລກບິນ / ຜູ້ຂາຍ):
        </label>
        <input type="text" name="search" id="reportSearchInput" class="form-control form-control-sm" placeholder="ປ້ອນເລກບິນ ຫຼື ຊື່ພະນັກງານ..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" style="border-radius: 8px; height: 38px; font-size: 0.85rem; width: 100%;">
      </div>

      <!-- Branch Store Filter Dropdown (Visible ONLY for Main Branch / Admin) -->
      <?php if ($isMain || $isAdmin): ?>
        <div style="flex: 1 1 0; min-width: 140px;">
          <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
            <i class="fas fa-store text-info mr-1"></i> ສາຂາ:
          </label>
          <select name="store_id" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="border-radius: 8px; height: 38px; font-size: 0.85rem; border: 1.5px solid #059669; color: #047857; background: #ecfdf5; width: 100%;">
            <option value="0">-- ທຸກສາຂາ --</option>
            <?php if (!empty($branchesList)): ?>
              <?php foreach ($branchesList as $b): ?>
                <option value="<?php echo $b['store_id']; ?>" <?php echo ($filter_store_id == $b['store_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($b['store_name']); ?> <?php echo !empty($b['is_main']) ? '(ສາຂາໃຫຍ່)' : ''; ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      <?php else: ?>
        <input type="hidden" name="store_id" value="<?php echo $userStoreId; ?>">
      <?php endif; ?>

      <!-- Bank Account Filter Dropdown -->
      <div style="flex: 1 1 0; min-width: 150px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-university text-primary mr-1"></i> ທະນາຄານ / ຊຳລະ:
        </label>
        <select name="bank_filter" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="border-radius: 8px; height: 38px; font-size: 0.85rem; border: 1.5px solid #0284c7; color: #0369a1; background: #f0f9ff; width: 100%;">
          <option value="">-- ຊຳລະທັງໝົດ --</option>
          <option value="cash" <?php echo ($bank_filter ?? '') === 'cash' ? 'selected' : ''; ?>>ເງິນສົດ</option>
          <option value="transfer" <?php echo ($bank_filter ?? '') === 'transfer' ? 'selected' : ''; ?>>ເງິນໂອນ</option>
          <?php if (!empty($bank_accounts)): ?>
            <optgroup label="ແຍກຕາມບັນຊີທະນາຄານ">
              <?php foreach ($bank_accounts as $bAcc): ?>
                <option value="<?php echo $bAcc['id']; ?>" <?php echo (string)($bank_filter ?? '') === (string)$bAcc['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($bAcc['bank_name']); ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>
        </select>
      </div>

      <!-- View Mode Dropdown -->
      <div style="flex: 1 1 0; min-width: 140px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-list text-primary mr-1"></i> ຮູບແບບການສະແດງ:
        </label>
        <select name="view_mode" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="border-radius: 8px; height: 38px; font-size: 0.85rem; border: 1.5px solid #2563eb; color: #1e40af; background: #eff6ff; width: 100%;">
          <option value="invoice" <?php echo $view_mode === 'invoice' ? 'selected' : ''; ?>>ສະແດງຕາມບິນ</option>
          <option value="item" <?php echo $view_mode === 'item' ? 'selected' : ''; ?>><?php echo $type === 'all_sales' ? 'ລາຍການສິນຄ້າ' : 'ລາຍລະອຽດ'; ?></option>
        </select>
      </div>

      <!-- Per Page Dropdown -->
      <div style="flex: 1 1 0; min-width: 100px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-layer-group text-primary mr-1"></i> ໂຊລາຍການ:
        </label>
        <select name="per_page" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="border-radius: 8px; height: 38px; font-size: 0.85rem; background: #ffffff; border: 1.5px solid #cbd5e1; width: 100%;">
          <option value="5" <?php echo $per_page_raw === '5' ? 'selected' : ''; ?>>5</option>
          <option value="10" <?php echo $per_page_raw === '10' ? 'selected' : ''; ?>>10</option>
          <option value="25" <?php echo $per_page_raw === '25' ? 'selected' : ''; ?>>25</option>
          <option value="50" <?php echo $per_page_raw === '50' ? 'selected' : ''; ?>>50</option>
          <option value="100" <?php echo $per_page_raw === '100' ? 'selected' : ''; ?>>100</option>
          <option value="all" <?php echo $per_page_raw === 'all' ? 'selected' : ''; ?>>ທັງໝົດ</option>
        </select>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex align-items-center" style="flex: 0 0 auto; gap: 6px;">
        <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3 d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, #2c5aa0, #244886); white-space: nowrap; font-size: 0.85rem;">
          <i class="fas fa-search mr-1"></i> ຄົ້ນຫາ
        </button>
        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?type=<?php echo htmlspecialchars($type ?: 'all_sales'); ?>&view_mode=<?php echo htmlspecialchars($view_mode); ?>" class="btn btn-light btn-sm border font-weight-bold d-inline-flex align-items-center justify-content-center px-2" title="ລ້າງຄ່າ" style="border-radius: 8px; height: 38px;">
          <i class="fas fa-redo"></i>
        </a>
      </div>
    </form>
  </div>
<?php endif; ?>
