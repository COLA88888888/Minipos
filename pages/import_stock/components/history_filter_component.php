<?php
// Component: Stock Transfer History Filters Form
?>
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
  <div class="card-body p-3">
    <form method="GET" action="" class="form-row align-items-end">
      <!-- Date range -->
      <div class="col-md-2 mb-2 mb-md-0">
        <label class="font-weight-bold text-dark mb-1" style="font-size: 0.82rem;"><i class="fas fa-calendar-alt text-primary mr-1"></i> ຕັ້ງແຕ່ວັນທີ:</label>
        <input type="date" name="from_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($from_date); ?>" style="border-radius: 6px; height: 36px;">
      </div>
      <div class="col-md-2 mb-2 mb-md-0">
        <label class="font-weight-bold text-dark mb-1" style="font-size: 0.82rem;"><i class="fas fa-calendar-check text-primary mr-1"></i> ຫາວັນທີ:</label>
        <input type="date" name="to_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($to_date); ?>" style="border-radius: 6px; height: 36px;">
      </div>

      <?php if ($isMain || $isAdmin): ?>
        <!-- Origin Branch -->
        <div class="col-md-2 mb-2 mb-md-0">
          <label class="font-weight-bold text-dark mb-1" style="font-size: 0.82rem;"><i class="fas fa-store text-info mr-1"></i> ສາຂາຕົ້ນທາງ:</label>
          <select name="from_store" class="form-control form-control-sm" style="border-radius: 6px; height: 36px;">
            <option value="">-- ທັງໝົດ --</option>
            <?php foreach ($stores as $st): ?>
              <option value="<?php echo $st['store_id']; ?>" <?php echo ($filter_from_store == $st['store_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($st['store_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Destination Branch -->
        <div class="col-md-2 mb-2 mb-md-0">
          <label class="font-weight-bold text-dark mb-1" style="font-size: 0.82rem;"><i class="fas fa-store-alt text-success mr-1"></i> ສາຂາປາຍທາງ:</label>
          <select name="to_store" class="form-control form-control-sm" style="border-radius: 6px; height: 36px;">
            <option value="">-- ທັງໝົດ --</option>
            <?php foreach ($stores as $st): ?>
              <option value="<?php echo $st['store_id']; ?>" <?php echo ($filter_to_store == $st['store_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($st['store_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <!-- Search input -->
      <div class="col-md-2 mb-2 mb-md-0">
        <label class="font-weight-bold text-dark mb-1" style="font-size: 0.82rem;"><i class="fas fa-search text-primary mr-1"></i> ຄົ້ນຫາລະຫັດ/ໝາຍເຫດ:</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="ລະຫັດໂອນ, ໝາຍເຫດ..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 6px; height: 36px;">
      </div>

      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm font-weight-bold flex-fill" style="height: 36px; border-radius: 6px; background: linear-gradient(135deg, #2c5aa0, #244886);">
          <i class="fas fa-search mr-1"></i> ຄົ້ນຫາ
        </button>
        <a href="transfer_history.php" class="btn btn-light btn-sm border font-weight-bold px-2.5" title="ລ້າງຄ່າ" style="height: 36px; border-radius: 6px;">
          <i class="fas fa-redo"></i>
        </a>
      </div>
    </form>
  </div>
</div>
