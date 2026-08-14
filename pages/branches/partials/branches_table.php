<?php
// pages/branches/partials/branches_table.php
if (!defined('MINIPOS_APP')) {
    define('MINIPOS_APP', true);
}
?>
<!-- Branch Stores Table Card -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: white;">
  <div class="card-header bg-white font-weight-bold py-3 border-0 d-flex justify-content-between align-items-center">
    <span><i class="fas fa-store-alt text-info mr-2"></i> ລາຍຊື່ສາຂາທັງໝົດໃນລະບົບ (<?php echo count($all_branches); ?> ສາຂາ)</span>
  </div>
  <div class="card-body p-0 table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="bg-light">
        <tr>
          <th class="text-center" style="width: 60px;">ID</th>
          <th>ລະຫັດສາຂາ</th>
          <th>ຊື່ສາຂາ</th>
          <th>ປະເພດ</th>
          <th>ເບີໂທ</th>
          <th>ທີ່ຢູ່</th>
          <th class="text-center">ສະຖານະ</th>
          <th class="text-center" style="width: 140px;">ຈັດການ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($all_branches)): ?>
          <?php foreach ($all_branches as $b): ?>
            <tr>
              <td class="text-center font-weight-bold"><?php echo $b['store_id']; ?></td>
              <td><span class="badge badge-secondary px-2 py-1"><?php echo htmlspecialchars($b['store_code']); ?></span></td>
              <td class="font-weight-bold text-dark">
                <?php echo htmlspecialchars($b['store_name']); ?>
              </td>
              <td>
                <?php if (!empty($b['is_main'])): ?>
                  <span class="badge badge-primary px-2 py-1">ສາຂາໃຫຍ່</span>
                <?php else: ?>
                  <span class="badge badge-info px-2 py-1">ສາຂາຍ່ອຍ</span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($b['tel'] ?? '-'); ?></td>
              <td><small class="text-muted"><?php echo htmlspecialchars($b['address'] ?? '-'); ?></small></td>
              <td class="text-center">
                <?php if ($b['status'] === 'active'): ?>
                  <span class="badge badge-success px-2 py-1">ເປີດໃຊ້ງານ</span>
                <?php else: ?>
                  <span class="badge badge-danger px-2 py-1">ປິດໃຊ້ງານ</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold rounded-circle mr-1" 
                        onclick='openEditBranchModal(<?php echo json_encode($b); ?>)' title="ແກ້ໄຂ">
                  <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm <?php echo $b['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success'; ?> rounded-circle" 
                        onclick='confirmToggleBranchStatus(<?php echo $b["store_id"]; ?>, <?php echo json_encode($b["store_name"]); ?>, <?php echo json_encode($b["status"]); ?>)' 
                        title="<?php echo $b['status'] === 'active' ? 'ປິດສາຂາ' : 'ເປີດໃຊ້ງານສາຂາ'; ?>">
                  <i class="fas fa-power-off"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center py-4 text-muted">ບໍ່ພົບຂໍ້ມູນສາຂາ</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
