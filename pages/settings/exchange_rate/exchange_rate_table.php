<!-- ============================================================
     exchange_rate_table.php - ຕາຕະລາງສະແດງປະຫວັດອັດຕາແລກປ່ຽນເງິນ
     ============================================================ -->
<div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1.5px solid #e2e8f0; background: #ffffff;">
  <!-- ຫົວຂໍ້ຕາຕະລາງ ແລະ ຈຳນວນລາຍການລວມ -->
  <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.95rem;">
      <i class="fas fa-history mr-2 text-primary"></i> ຕາຕະລາງອັດຕາແລກປ່ຽນເງິນ
    </h6>
    <span class="badge badge-light font-weight-bold text-muted border px-2.5 py-1">
      ລວມທັງໝົດ: <?php echo number_format(count($historyRates)); ?> ລາຍການ
    </span>
  </div>

  <!-- ຕາຕະລາງລາຍລະອຽດອັດຕາແລກປ່ຽນເງິນ (ກີບ, ບາດ, ໂດລາ) -->
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead style="background: #f8fafc; color: #475569; font-size: 0.82rem;" class="font-weight-bold">
        <tr>
          <th class="text-center" style="width: 70px;">ລຳດັບ</th>
          <th class="text-center" style="width: 120px;">ວັນທີ</th>
          <th class="text-center" style="width: 110px;">ເວລາ</th>
          <th class="text-right" style="width: 140px;">ກີບ</th>
          <th class="text-right" style="width: 160px;">ບາດ</th>
          <th class="text-right" style="width: 160px;">ໂດລາ</th>
          <th class="text-center" style="width: 150px;">ຜູ້ບັນທຶກ</th>
          <th class="text-center" style="width: 130px;">ຈັດການ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($historyRates)): ?>
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <i class="fas fa-exchange-alt fa-2x mb-2 d-block text-muted opacity-50"></i>
              ບໍ່ມີຂໍ້ມູນອັດຕາແລກປ່ຽນເງິນ
            </td>
          </tr>
        <?php else: ?>
          <?php $i = 1; foreach ($historyRates as $r): ?>
            <?php 
              $thbVal = floatval($r['ex_kip_bath']);
              $usdVal = floatval($r['ex_kip_us']);
            ?>
            <tr>
              <td class="text-center font-weight-bold text-muted"><?php echo $i++; ?></td>
              <td class="text-center font-weight-bold text-dark"><?php echo date('d/m/Y', strtotime($r['ex_date'])); ?></td>
              <td class="text-center text-muted"><?php echo htmlspecialchars($r['ex_time']); ?></td>
              <td class="text-right font-weight-bold text-dark">1,000 ₭</td>
              <td class="text-right font-weight-bold text-success" style="font-size: 0.92rem;">
                <?php echo number_format($thbVal, 0); ?> ₭
              </td>
              <td class="text-right font-weight-bold text-primary" style="font-size: 0.92rem;">
                <?php echo number_format($usdVal, 0); ?> ₭
              </td>
              <td class="text-center">
                <span class="badge badge-light border text-dark font-weight-bold px-2.5 py-1" style="font-size: 0.80rem;">
                  <i class="fas fa-user-circle mr-1 text-muted"></i><?php echo htmlspecialchars($r['ex_userlogin'] ?? 'admin'); ?>
                </span>
              </td>
              <td class="text-center">
                <!-- ປຸ່ມຈັດການ (ແກ້ໄຂ ແລະ ລຶບ) -->
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-outline-primary btn-sm px-2" title="ແກ້ໄຂ" 
                          onclick="editRate(<?php echo $r['Id']; ?>, '<?php echo number_format($thbVal, 0); ?>', '<?php echo number_format($usdVal, 0); ?>')">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm px-2" title="ລຶບ" 
                          onclick="deleteRate(<?php echo $r['Id']; ?>)">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>