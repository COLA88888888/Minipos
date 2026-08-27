<?php
// Component: Date Range Filter, Branch Selector & View Type Bar
?>
<div class="card border-0 shadow-sm bg-white mb-4" style="border-radius: 16px;">
  <div class="card-body p-3.5">
    <form method="GET" action="import_list.php" class="row align-items-end">

      <!-- 1. From Date -->
      <div class="col-md-6 col-lg-2 mb-2 mb-lg-0">
        <label class="form-label font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">
          <i class="fas fa-calendar-alt text-primary mr-1"></i> <?php echo htmlspecialchars(t('import_list.filter_from_date', 'ຕັ້ງແຕ່ວັນທີ:')); ?>
        </label>
        <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="form-control font-weight-bold" style="height: 42px; border-radius: 8px;">
      </div>

      <!-- 2. To Date -->
      <div class="col-md-6 col-lg-2 mb-2 mb-lg-0">
        <label class="form-label font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">
          <i class="fas fa-calendar-alt text-primary mr-1"></i> <?php echo htmlspecialchars(t('import_list.filter_to_date', 'ຫາວັນທີ:')); ?>
        </label>
        <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" class="form-control font-weight-bold" style="height: 42px; border-radius: 8px;">
      </div>

      <!-- 3. Branch / Store Selector -->
      <div class="col-md-6 col-lg-3 mb-2 mb-lg-0">
        <label class="form-label font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">
          <i class="fas fa-store text-info mr-1"></i> <?php echo htmlspecialchars(t('import_list.filter_select_branch', 'ເລືອກສາຂາ:')); ?>
        </label>
        <select name="store_id" class="form-control font-weight-bold border-info" style="height: 42px; border-radius: 8px; background-color: #f0f9ff;" onchange="this.form.submit()" <?php echo (!$isAdmin && !$isMain) ? 'disabled' : ''; ?>>
          <option value="0"><?php echo htmlspecialchars(t('import_list.filter_all_branches', '-- ທຸກສາຂາ --')); ?></option>
          <?php foreach ($stores as $st): ?>
            <option value="<?php echo $st['store_id']; ?>" <?php echo ($filter_store == $st['store_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($st['store_name']); ?> <?php echo !empty($st['is_main']) ? htmlspecialchars(t('import_list.main_branch_suffix', '(ສາງຫຼັກ)')) : ''; ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!$isAdmin && !$isMain): ?>
          <input type="hidden" name="store_id" value="<?php echo $filter_store; ?>">
        <?php endif; ?>
      </div>

      <!-- 4. Report View Type Selector -->
      <div class="col-md-6 col-lg-3 mb-2 mb-lg-0">
        <label class="form-label font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">
          <i class="fas fa-list text-success mr-1"></i> <?php echo htmlspecialchars(t('import_list.filter_report_type', 'ປະເພດລາຍງານ:')); ?>
        </label>
        <select name="view_type" class="form-control font-weight-bold" style="height: 42px; border-radius: 8px;" onchange="this.form.submit()">
          <option value="bill" <?php echo ($view_type === 'bill') ? 'selected' : ''; ?>><?php echo htmlspecialchars(t('import_list.opt_view_bill', 'ຈັດການໃບບິນ')); ?></option>
          <option value="detail" <?php echo ($view_type === 'detail') ? 'selected' : ''; ?>><?php echo htmlspecialchars(t('import_list.opt_view_detail', 'ລາຍລະອຽດສິນຄ້າ')); ?></option>
        </select>
      </div>

      <!-- 5. Action Buttons -->
      <div class="col-md-12 col-lg-2 mt-2 mt-lg-0 d-flex align-items-center">
        <button type="submit" class="btn btn-primary font-weight-bold px-3 shadow-sm mr-2" style="height: 42px; border-radius: 6px; flex: 1; background: linear-gradient(135deg, #2c5aa0, #244886); border: none;">
          <i class="fas fa-search mr-1"></i> <?php echo htmlspecialchars(t('import_list.search_btn', 'ຄົ້ນຫາ')); ?>
        </button>
        <a href="import_list.php" class="btn btn-outline-secondary font-weight-bold px-3" style="height: 42px; border-radius: 6px; display: inline-flex; align-items: center;" title="<?php echo htmlspecialchars(t('import_list.clear_filter_title', 'ລ້າງຄ່າກັ່ນກອງ')); ?>">
          <i class="fas fa-sync-alt"></i>
        </a>
      </div>

    </form>
  </div>
</div>
