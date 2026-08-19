<div class="card border-0 shadow-sm" style="border-radius: 14px; border: 1.5px solid #e2e8f0; background: #ffffff;">
  <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-dark">
      <i class="fas fa-list-alt text-primary mr-2"></i> ລາຍງານການປັບລາຄາທັງໝົດ
    </h6>
    <span class="badge badge-light border font-weight-bold text-muted px-2.5 py-1"><?php echo count($adjustments); ?> ລາຍການ</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
      <thead style="background: #f8fafc; color: #475569; font-size: 0.80rem;" class="font-weight-bold">
        <tr style="white-space: nowrap;">
          <th class="text-center" style="width: 50px; white-space: nowrap;">ລ/ດ</th>
          <th class="text-center" style="white-space: nowrap;">ລະຫັດ</th>
          <th style="white-space: nowrap;">ບາໂຄ້ດ</th>
          <th>ຊື່ສິນຄ້າ</th>
          <th>ສາຂາ</th>
          <th class="text-right">ລາຄາເກົ່າ</th>
          <th class="text-right">ລາຄາໃໝ່</th>
          <th class="text-right">ຜົນຕ່າງ</th>
          <th class="text-center">ວັນທີ & ເວລາ</th>
          <th>ຜູ້ປັບ</th>
          <th>ລາຍລະອຽດ</th>
          <th class="text-center" style="width: 55px;">ຈັດການ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($adjustments)): ?>
          <tr>
            <td colspan="12" class="text-center py-5 text-muted">
              <i class="fas fa-tags fa-2x mb-2 d-block text-secondary opacity-50"></i>
              ບໍ່ມີປະຫວັດການປັບລາຄາ
            </td>
          </tr>
        <?php else: ?>
          <?php $i = 1; foreach ($adjustments as $adj): ?>
            <?php 
              $diff = floatval($adj['diff_amount'] ?? 0);
              $oldP = floatval($adj['old_price'] ?? 0);
              $newP = floatval($adj['new_price'] ?? 0);
              if ($adj['target_price_type'] === 'bprice') {
                  $oldP = floatval($adj['old_bprice'] ?? 0);
                  $newP = floatval($adj['new_bprice'] ?? 0);
              }
              $prodIdCode = $adj['product_id'] ? ('P-' . str_pad($adj['product_id'], 4, '0', STR_PAD_LEFT)) : '-';
              $barcodeVal = !empty($adj['prod_barcode']) ? $adj['prod_barcode'] : '-';
            ?>
            <tr>
              <!-- 1. ລຳດັບ -->
              <td class="text-center font-weight-bold text-muted"><?php echo $i++; ?></td>

              <!-- 2. ລະຫັດສິນຄ້າ -->
              <td class="text-center font-weight-bold text-dark" style="font-family: monospace;">
                <?php if ($adj['target_mode'] === 'category'): ?>
                  <span class="badge badge-light border text-muted">ໝວດໝູ່</span>
                <?php else: ?>
                  <span class="badge badge-secondary px-2 py-1"><?php echo htmlspecialchars($prodIdCode); ?></span>
                <?php endif; ?>
              </td>

              <!-- 3. ບາໂຄ້ດ -->
              <td class="font-weight-bold text-secondary" style="font-family: monospace;">
                <?php if ($adj['target_mode'] === 'category'): ?>
                  -
                <?php else: ?>
                  <i class="fas fa-barcode text-muted mr-1" style="font-size: 0.8rem;"></i><?php echo htmlspecialchars($barcodeVal); ?>
                <?php endif; ?>
              </td>

              <!-- 4. ຊື່ສິນຄ້າ -->
              <td class="font-weight-bold text-dark">
                <?php if ($adj['target_mode'] === 'category'): ?>
                  <span class="badge badge-info font-weight-bold mr-1"><i class="fas fa-folder mr-1"></i> ໝວດໝູ່: <?php echo htmlspecialchars($adj['category_name'] ?? 'ທຸກສິນຄ້າ'); ?></span>
                <?php else: ?>
                  <?php echo htmlspecialchars($adj['product_name'] ?? ('ສິນຄ້າ ID: ' . $adj['product_id'])); ?>
                <?php endif; ?>
              </td>

              <!-- 5. ສາຂາ -->
              <td class="font-weight-bold">
                <span class="badge badge-light border text-primary px-2 py-1" style="font-size: 0.78rem;">
                  <i class="fas fa-store mr-1 text-primary"></i><?php echo htmlspecialchars($adj['store_name'] ?: 'ສາຂາຫຼັກ'); ?>
                </span>
              </td>

              <!-- 5. ລາຄາເກົ່າ -->
              <td class="text-right text-muted font-weight-bold">
                <?php echo number_format($oldP, 0); ?> ₭
              </td>

              <!-- 6. ລາຄາໃໝ່ -->
              <td class="text-right font-weight-bold text-primary">
                <?php echo number_format($newP, 0); ?> ₭
              </td>

              <!-- 7. ຜົນຕ່າງ -->
              <td class="text-right font-weight-bold">
                <?php if ($diff > 0): ?>
                  <span class="text-danger"><i class="fas fa-arrow-up mr-1" style="font-size:0.7rem;"></i>+<?php echo number_format($diff, 0); ?> ₭</span>
                <?php elseif ($diff < 0): ?>
                  <span class="text-success"><i class="fas fa-arrow-down mr-1" style="font-size:0.7rem;"></i><?php echo number_format($diff, 0); ?> ₭</span>
                <?php else: ?>
                  <span class="text-muted">0 ₭</span>
                <?php endif; ?>
              </td>

              <!-- 8. ວັນທີ & ເວລາ -->
              <td class="text-center text-muted small" style="white-space: nowrap; font-family: monospace;">
                <?php echo date('d/m/Y', strtotime($adj['adjust_date'])); ?> <small><?php echo date('H:i', strtotime($adj['adjust_time'])); ?></small>
              </td>

              <!-- 9. ຜູ້ປັບ -->
              <td class="small font-weight-bold text-dark">
                <i class="fas fa-user-circle text-secondary mr-1"></i><?php echo htmlspecialchars($adj['username'] ?? 'admin'); ?>
              </td>

              <!-- 10. ລາຍລະອຽດ / ໝາຍເຫດ -->
              <td>
                <small class="text-muted">
                  <?php
                    $typeText = 'ລາຄາຂາຍ';
                    if ($adj['target_price_type'] === 'bprice') $typeText = 'ລາຄາຊື້/ທຶນ';
                    if ($adj['target_price_type'] === 'both') $typeText = 'ທັງຂາຍ&ຊື້';

                    $calcText = 'ຕັ້ງໃໝ່';
                    if ($adj['calc_type'] === 'increase') $calcText = 'ເພີ່ມ';
                    if ($adj['calc_type'] === 'decrease') $calcText = 'ຫຼຸດ';

                    echo '<strong>' . $typeText . ' (' . $calcText . ')</strong>';
                    if (!empty($adj['remark'])) {
                        echo '<span class="d-block text-truncate" style="max-width: 140px;" title="' . htmlspecialchars($adj['remark']) . '">' . htmlspecialchars($adj['remark']) . '</span>';
                    }
                  ?>
                </small>
              </td>

              <!-- 11. ຈັດການ (ໄອຄອນລຶບ - Real-time AJAX) -->
              <td class="text-center">
                <?php if (hasPermission('price_adjustment', 'del')): ?>
                  <button type="button" class="btn btn-outline-danger btn-sm px-2 py-1" title="ລຶບປະຫວັດ" style="border-radius: 6px;" onclick="deletePriceAdjLog(<?php echo $adj['adjust_id']; ?>, this)">
                    <i class="fas fa-trash-alt" style="font-size: 0.84rem;"></i>
                  </button>
                <?php else: ?>
                  <span class="badge badge-light text-muted" style="font-size: 0.75rem;">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
