<!-- SECTION 1: ເມນູ (MAIN MENU) -->
<tr class="table-primary">
  <td colspan="5" class="font-weight-bold text-uppercase py-2" style="font-size: 0.82rem; letter-spacing: 0.5px;">
    <i class="fas fa-th-large mr-1"></i> 1. ເມນູ (MAIN MENU)
  </td>
</tr>

<!-- 1.1 ດາດສ໌ບອດ -->
<tr>
  <td class="pl-4">
    <div class="d-flex align-items-center">
      <i class="fas fa-chart-line text-success mr-3" style="font-size: 1.2rem; width: 24px;"></i>
      <div>
        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">ດາດສ໌ບອດ</div>
        <div class="text-muted" style="font-size: 0.8rem;">ເຂົ້າເຖິງ ແລະ ເບິ່ງສະຖິຕິໜ້າດາດສ໌ບອດຫຼັກ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_dashboard_view_<?php echo $u['Id']; ?>" 
             data-perm="dashboard" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['dashboard']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo $isAdmin ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'dashboard', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
</tr>
