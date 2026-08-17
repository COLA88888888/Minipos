<!-- SECTION 6: ລາຍງານ (REPORTS) -->
<tr class="table-primary">
  <td colspan="5" class="font-weight-bold text-uppercase py-2" style="font-size: 0.82rem; letter-spacing: 0.5px;">
    <i class="fas fa-file-invoice-dollar mr-1"></i> 6. ລາຍງານ (REPORTS)
  </td>
</tr>

<!-- 6.1 ລາຍງານປະຈຳວັນ -->
<tr>
  <td class="pl-4">
    <div class="d-flex align-items-center">
      <i class="fas fa-calendar-day text-info mr-3" style="font-size: 1.2rem; width: 24px;"></i>
      <div>
        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">ລາຍງານປະຈຳວັນ</div>
        <div class="text-muted" style="font-size: 0.8rem;">ເບິ່ງສະຫຼຸບຍອດຂາຍ ແລະ ປະຫວັດປະຈຳວັນ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_daily_report_view_<?php echo $u['Id']; ?>" 
             data-perm="daily_report" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['daily_report']) || !empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'daily_report', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
</tr>

<!-- 6.2 ລາຍງານການຂາຍທັງໝົດ -->
<tr>
  <td class="pl-4">
    <div class="d-flex align-items-center">
      <i class="fas fa-file-invoice-dollar text-success mr-3" style="font-size: 1.2rem; width: 24px;"></i>
      <div>
        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">ລາຍງານການຂາຍທັງໝົດ</div>
        <div class="text-muted" style="font-size: 0.8rem;">ເບິ່ງລາຍງານການຂາຍລວມທັງໝົດຕາມໄລຍະເວລາ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_all_sales_view_<?php echo $u['Id']; ?>" 
             data-perm="all_sales" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['all_sales']) || !empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'all_sales', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
</tr>

<!-- 6.3 ລາຍງານສິນຄ້າຂາຍດີ -->
<tr>
  <td class="pl-4">
    <div class="d-flex align-items-center">
      <i class="fas fa-fire text-danger mr-3" style="font-size: 1.2rem; width: 24px;"></i>
      <div>
        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">ລາຍງານສິນຄ້າຂາຍດີ</div>
        <div class="text-muted" style="font-size: 0.8rem;">ເບິ່ງອັນດັບສິນຄ້າທີ່ຂາຍດີທີ່ສຸດ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_best_seller_view_<?php echo $u['Id']; ?>" 
             data-perm="best_seller" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['best_seller']) || !empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'best_seller', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
</tr>

<!-- 6.4 ລາຍງານກຳໄລ-ຕົ້ນທຶນ -->
<tr>
  <td class="pl-4">
    <div class="d-flex align-items-center">
      <i class="fas fa-chart-line text-warning mr-3" style="font-size: 1.2rem; width: 24px;"></i>
      <div>
        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">ລາຍງານກຳໄລ-ຕົ້ນທຶນ</div>
        <div class="text-muted" style="font-size: 0.8rem;">ວິເຄາະຕົ້ນທຶນ, ລາຍຮັບ ແລະ ກຳໄລສຸດທິ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_profit_cost_view_<?php echo $u['Id']; ?>" 
             data-perm="profit_cost" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['profit_cost']) || !empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'profit_cost', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
</tr>

<!-- 6.5 ລາຍງານການເງິນ -->
<tr>
  <td class="pl-4">
    <div class="d-flex align-items-center">
      <i class="fas fa-wallet text-info mr-3" style="font-size: 1.2rem; width: 24px;"></i>
      <div>
        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">ລາຍງານການເງິນ</div>
        <div class="text-muted" style="font-size: 0.8rem;">ເບິ່ງສະຫຼຸບການຮັບເງິນສົດ/ໂອນ ຕາມຊ່ອງທາງ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_financial_view_<?php echo $u['Id']; ?>" 
             data-perm="financial" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['financial']) || !empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'financial', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="badge badge-light text-muted">ບໍ່ມີ</span></td>
</tr>

<!-- 6.6 ລາຍງານຕາມປະເພດສິນຄ້າ -->
<tr>
  <td class="pl-4">
    <div class="d-flex align-items-center">
      <i class="fas fa-layer-group text-primary mr-3" style="font-size: 1.2rem; width: 24px;"></i>
      <div>
        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">ລາຍງານຕາມປະເພດສິນຄ້າ</div>
        <div class="text-muted" style="font-size: 0.8rem;">ເບິ່ງສະຖິຕິຍອດຂາຍແຍກຕາມໝວດໝູ່/ປະເພດ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_category_sales_<?php echo $u['Id']; ?>" 
             data-perm="category_sales" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['category_sales']) || !empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'category_sales', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
</tr>

<!-- 6.7 ປະຫວັດການລົບບິນຂາຍ -->
<tr class="bg-light">
  <td class="pl-4">
    <div class="perm-module-info">
      <i class="fas fa-trash-alt text-danger mr-2"></i>
      <div>
        <div class="perm-module-title" style="font-size: 0.92rem;">↳ ປະຫວັດການລົບບິນຂາຍ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_delete_bills_<?php echo $u['Id']; ?>" 
             data-perm="delete_bills" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['delete_bills']) || !empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'delete_bills', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
</tr>
