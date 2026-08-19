<?php
// Component: Stock Transfer History Results Table
?>
<div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0 text-nowrap" style="font-size: 0.85rem; min-width: 950px;">
      <thead class="bg-light text-dark font-weight-bold">
        <tr>
          <th class="text-center" style="width: 45px;">ລຳດັບ</th>
          <th style="width: 130px;">ລະຫັດໃບໂອນ</th>
          <th style="width: 120px;">ວັນທີໂອນ</th>
          <th style="width: 110px;">ຜູ້ໂອນ</th>
          <th>ສາຂາຕົ້ນທາງ</th>
          <th style="width: 20px;" class="text-center"></th>
          <th>ສາຂາປາຍທາງ</th>
          <th style="min-width: 160px;">ລາຍການສິນຄ້າ</th>
          <th class="text-center" style="width: 80px;">ຈຳນວນລວມ</th>
          <th class="text-center" style="width: 90px;">ສະຖານະ</th>
          <th style="width: 100px;" class="text-center no-print">ເບິ່ງ</th>
        </tr>
      </thead>
      <tbody id="historyTableBody">
        <?php if (empty($transfers)): ?>
          <tr class="empty-row">
            <td colspan="11" class="text-center py-5 text-muted">
              <i class="fas fa-box-open fa-3x d-block mb-2 text-muted" style="opacity: 0.4;"></i>
              <span class="font-weight-bold">ບໍ່ພົບປະຫວັດການໂອນສິນຄ້າ</span>
            </td>
          </tr>
        <?php else: ?>
          <?php $i = 1; foreach ($transfers as $trf): ?>
            <tr class="transfer-row" data-search="<?php echo htmlspecialchars(strtolower($trf['transfer_code'] . ' ' . ($trf['creator_name'] ?: 'admin') . ' ' . $trf['from_store_name'] . ' ' . $trf['to_store_name'] . ' ' . ($trf['product_list'] ?: '') . ' ' . ($trf['notes'] ?: ''))); ?>">
              <td class="text-center font-weight-bold text-secondary row-index align-middle"><?php echo $i++; ?></td>
              <td class="align-middle">
                <strong class="text-primary" style="cursor: pointer;" onclick="viewTransferDetails(<?php echo $trf['transfer_id']; ?>)" title="ກົດເພື່ອເບິ່ງລາຍລະອຽດ">
                  <i class="fas fa-receipt mr-1"></i><?php echo htmlspecialchars($trf['transfer_code']); ?>
                </strong>
              </td>
              <td class="align-middle" style="font-size: 0.82rem; color: #475569;"><?php echo date('d/m/Y H:i', strtotime($trf['transfer_date'])); ?></td>
              <td class="align-middle">
                <span class="badge badge-light border text-dark px-2 py-1.5" style="border-radius: 6px; font-weight: 500;"><i class="fas fa-user text-muted mr-1"></i><?php echo htmlspecialchars($trf['creator_name'] ?: 'Admin'); ?></span>
              </td>
              <td class="align-middle">
                <span class="badge badge-light border text-dark px-2 py-1.5" style="border-radius: 6px;"><i class="fas fa-store text-primary mr-1"></i><?php echo htmlspecialchars($trf['from_store_name']); ?></span>
              </td>
              <td class="text-center align-middle"><i class="fas fa-long-arrow-alt-right text-muted"></i></td>
              <td class="align-middle">
                <span class="badge badge-success text-white px-2 py-1.5" style="background: #059669; border-radius: 6px;"><i class="fas fa-store mr-1"></i><?php echo htmlspecialchars($trf['to_store_name']); ?></span>
              </td>
              <td class="align-middle">
                <strong class="text-dark"><?php echo $trf['total_items']; ?> ລາຍການ</strong>
              </td>
              <td class="text-center align-middle font-weight-bold text-dark"><?php echo number_format($trf['total_qty']); ?></td>
              <td class="text-center align-middle">
                <?php if ($trf['status'] === 'completed'): ?>
                  <span class="badge badge-success px-2 py-1.5 font-weight-bold" style="border-radius: 12px; font-size: 0.72rem;"><i class="fas fa-check-circle mr-1"></i>ສຳເລັດ</span>
                <?php else: ?>
                  <span class="badge badge-danger px-2 py-1.5 font-weight-bold" style="border-radius: 12px; font-size: 0.72rem;"><i class="fas fa-times-circle mr-1"></i>ຍົກເລີກ</span>
                <?php endif; ?>
              </td>
              <td class="text-center align-middle no-print">
                <button type="button" class="btn btn-outline-primary btn-sm px-2.5 py-1" onclick="viewTransferDetails(<?php echo $trf['transfer_id']; ?>)" title="ເບິ່ງລາຍລະອຽດ" style="border-radius: 6px;">
                  <i class="fas fa-eye fa-lg"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination Bar -->
  <div class="d-flex flex-column flex-md-row align-items-center justify-content-between pt-4 pb-3 px-3 border-top bg-light" style="border-radius: 0 0 14px 14px;">

    <div class="d-flex justify-content-end ml-md-auto">
      <ul class="pagination pagination-circle mb-0" id="transferPagination">
      </ul>
    </div>
  </div>
</div>
