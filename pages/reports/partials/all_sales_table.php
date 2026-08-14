<div class="report-table-card card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; max-width: 100%;">
  <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table class="table table-hover table-striped align-middle report-table mb-0" style="font-size: 0.85rem; min-width: 720px;">
      <thead style="position: sticky; top: 0; z-index: 10; background: #ffffff !important; color: #1e293b;">
        <?php if ($view_mode === 'item'): ?>
          <!-- PRODUCT SUMMARY HEADERS FOR ALL SALES REPORT (7 COLUMNS) -->
          <tr style="background: #ffffff; color: #1e293b;">
            <th class="text-center" style="width: 60px; background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ລຳດັບ</th>
            <th class="text-left" style="width: 130px; background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ລະຫັດ</th>
            <th class="text-left" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ຊື່ສິນຄ້າ</th>
            <th class="text-center" style="width: 100px; background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ຈຳນວນ</th>
            <th class="text-right" style="width: 140px; background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ລາຄາລວມ</th>
            <th class="text-right" style="width: 130px; background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ສ່ວນຫຼຸດ</th>
            <th class="text-right" style="width: 150px; background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ສຸດທິ</th>
          </tr>
        <?php else: ?>
          <!-- ALL SALES INVOICE HEADERS (12 COLUMNS) -->
          <tr style="background: #ffffff; color: #1e293b;">
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ລຳດັບ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ເລກບິນ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ວັນທີ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ຈຳນວນ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ລາຄາລວມ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ສ່ວນຫຼຸດ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ສຸດທິ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ເງິນສົດ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ໂອນ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ສະຖານະ</th>
            <th class="text-center" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0; vertical-align: middle;">ຜູ້ຂາຍ</th>
          </tr>
        <?php endif; ?>
      </thead>
      <tbody>
        <?php if (empty($display_data)): ?>
          <tr>
            <td colspan="<?php echo $view_mode === 'item' ? '7' : '12'; ?>" class="text-center py-5">
              <div class="text-muted font-weight-bold" style="font-size: 0.95rem;">ບໍ່ພົບຂໍ້ມູນລາຍງານການຂາຍ</div>
              <small class="text-muted">ກະລຸນາປ່ຽນເງື່ອນໄຂການຄົ້ນຫາ ຫຼື ເລືອກຊ່ວງວັນທີໃໝ່</small>
            </td>
          </tr>
        <?php else: ?>

          <?php if ($view_mode === 'item'): ?>
            <!-- PRODUCT SUMMARY TABLE FOR ALL SALES REPORT (7 COLUMNS) -->
            <?php $i = $start_record; foreach ($display_data as $row): ?>
              <tr>
                <td class="text-center font-weight-bold text-muted"><?php echo $i++; ?></td>
                <td class="text-left font-weight-bold text-secondary"><?php echo htmlspecialchars($row['pro_code']); ?></td>
                <td class="text-left font-weight-bold text-dark"><?php echo htmlspecialchars($row['pro_name']); ?></td>
                <td class="text-center font-weight-bold text-dark"><?php echo number_format($row['qty']); ?></td>
                <td class="text-right font-weight-bold text-secondary"><?php echo number_format($row['gross'], 0); ?> ₭</td>
                <td class="text-right text-danger font-weight-bold"><?php echo number_format($row['discount'], 0); ?> ₭</td>
                <td class="text-right font-weight-bold text-success" style="font-size: 0.94rem;"><?php echo number_format($row['net'], 0); ?> ₭</td>
              </tr>
            <?php endforeach; ?>

          <?php else: ?>
            <!-- INVOICE SUMMARY TABLE FOR ALL SALES REPORT (12 COLUMNS) -->
            <?php $i = $start_record; foreach ($display_data as $row): ?>
              <tr>
                <td class="text-center font-weight-bold text-muted"><?php echo $i++; ?></td>
                <td class="text-left font-weight-bold text-primary">
                  <a href="javascript:void(0)" onclick="viewBillDetails('<?php echo htmlspecialchars($row['bill_no']); ?>')" class="text-primary" title="ເບິ່ງລາຍລະອຽດບິນ">
                    <?php echo htmlspecialchars($row['bill_no']); ?>
                  </a>
                </td>
                <td class="text-left" style="font-size: 0.84rem; color: #475569;"><?php echo htmlspecialchars($row['date_time']); ?></td>
                <td class="text-center font-weight-bold text-dark"><?php echo number_format($row['qty']); ?></td>
                <td class="text-right font-weight-bold text-secondary"><?php echo number_format($row['gross'], 0); ?> ₭</td>
                <td class="text-right text-danger font-weight-bold"><?php echo number_format($row['discount'], 0); ?> ₭</td>
                <td class="text-right font-weight-bold text-success" style="font-size: 0.94rem;"><?php echo number_format($row['net'], 0); ?> ₭</td>
                <td class="text-right font-weight-bold text-dark"><?php echo number_format($row['cash'], 0); ?> ₭</td>
                <td class="text-right font-weight-bold text-primary">
                  <?php if (($row['qr'] ?? 0) > 0): ?>
                    <div><?php echo number_format($row['qr'], 0); ?> ₭</div>
                    <?php 
                      $bName = !empty($row['bank_name']) ? $row['bank_name'] : 'BCEL One';
                      $bLogoUrl = getBankLogoByInfo($bName, $row['bank_account_id'] ?? 0);
                    ?>
                    <div class="d-inline-flex align-items-center mt-1 justify-content-end">
                      <img src="<?php echo htmlspecialchars($bLogoUrl); ?>" style="width: 24px; height: 24px; object-fit: cover; border-radius: 50% !important; background: #ffffff; padding: 1px; border: 1.5px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.08);" title="<?php echo htmlspecialchars($bName); ?>" onerror="this.src='../../assets/img/banks/bcel.svg';">
                    </div>
                  <?php else: ?>
                    <span class="text-muted">0 ₭</span>
                  <?php endif; ?>
                </td>
                <td class="text-center font-weight-bold">
                  <?php if (($row['status'] ?? 'SUCCESS') === 'CANCEL'): ?>
                    <span class="badge badge-danger px-2 py-1" style="font-size: 0.76rem;">ຍົກເລີກ</span>
                  <?php else: ?>
                    <span class="badge badge-success px-2 py-1" style="font-size: 0.76rem;">ຈ່າຍແລ້ວ</span>
                  <?php endif; ?>
                </td>
                <td class="text-left font-weight-bold text-dark" style="font-size: 0.84rem;"><?php echo htmlspecialchars($row['cashier']); ?></td>
                <td class="text-center no-print" style="white-space: nowrap !important;">
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

        <?php endif; ?>
      </tbody>
      
      <!-- Table Footer Totals -->
      <?php if (!empty($sales_data)): ?>
        <tfoot style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
          <tr>
            <?php if ($view_mode === 'item'): ?>
              <td colspan="3" class="text-right text-dark">ລວມທັງໝົດ:</td>
              <td class="text-center text-dark"><?php echo number_format($total_qty); ?></td>
              <td class="text-right text-secondary"><?php echo number_format($total_gross, 0); ?> ₭</td>
              <td class="text-right text-danger"><?php echo number_format($total_disc, 0); ?> ₭</td>
              <td class="text-right text-success" style="font-size: 1.0rem;"><?php echo number_format($total_net, 0); ?> ₭</td>
            <?php else: ?>
              <td colspan="3" class="text-right text-dark">ລວມທັງໝົດ:</td>
              <td class="text-center text-dark"><?php echo number_format($total_qty); ?></td>
              <td class="text-right text-secondary"><?php echo number_format($total_gross, 0); ?> ₭</td>
              <td class="text-right text-danger"><?php echo number_format($total_disc, 0); ?> ₭</td>
              <td class="text-right text-success" style="font-size: 1.0rem;"><?php echo number_format($total_net, 0); ?> ₭</td>
              <td class="text-right text-dark"><?php echo number_format($total_cash, 0); ?> ₭</td>
              <td class="text-right text-primary"><?php echo number_format($total_qr, 0); ?> ₭</td>
              <td></td>
              <td colspan="2"></td>
            <?php endif; ?>
          </tr>
        </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>
