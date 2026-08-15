<?php
/**
 * --------------------------------------------------------------------------
 * ຟາຍ Partials: ຕາຕະລາງປຸ່ມສະວິດກຳນົດສິດ (Permissions Matrix Table)
 * Path: pages/permissions/partials/permissions_matrix_table.php
 * --------------------------------------------------------------------------
 * ໜ້າທີ່:
 * - ສະແດງຂໍ້ມູນລາຍລະອຽດຜູ້ນຳໃຊ້ທີ່ເລືອກ (Detail Header) ພ້ອມ ຊື່ສາຂາ ແລະ ປຸ່ມ "ກຳນົດສິດດ່ວນ"
 * - ສະແດງຕາຕະລາງ 10 ໂມດູນລະບົບ ພ້ອມ Sticky Header ແລະ ສະວິດ ເປີດ/ປິດ (View, Add, Edit, Delete)
 */
?>

<!-- RIGHT COLUMN (DETAIL): Menu Permissions Matrix Table -->
<div class="col-lg-8 col-xl-8 mb-4">
  <div class="perm-detail-card">
    
    <?php if (empty($users)): ?>
      <div class="p-5 text-center text-muted">
        <i class="fas fa-info-circle fa-2x mb-2"></i>
        <p>ກະລຸນາເລືອກຜູ້ນຳໃຊ້ຈາກລາຍຊື່ທາງຊ້າຍເພື່ອຕັ້ງຄ່າສິດ</p>
      </div>
    <?php else: ?>
      <?php foreach ($users as $index => $u): ?>
        <?php 
          $uStatus = $u['status'] ?? $u['userstatus'] ?? '';
          $isAdmin = (strtolower($uStatus) === 'admin' || $uStatus === 'ຜູ້ບໍລິຫານ' || $u['Id'] == 1);
          $displayName = htmlspecialchars($u['username'] ?? 'User');
          $isActiveFirst = ($index === 0);
        ?>
        <!-- Panel ຕາຕະລາງສິດຂອງແຕ່ລະຜູ້ນຳໃຊ້ (ສະແດງສະເພາະຄົນທີ່ເລືອກ) -->
        <div class="user-detail-panel w-100 flex-column" 
             id="user-detail-<?php echo $u['Id']; ?>" 
             style="display: <?php echo $isActiveFirst ? 'flex' : 'none'; ?>;">
          
          <!-- 1. Detail Header (ຂໍ້ມູນຜູ້ນຳໃຊ້ + ສາຂາ + ປຸ່ມກຳນົດສິດດ່ວນ) -->
          <div class="perm-detail-header">
            <div class="d-flex align-items-center flex-wrap" style="width: 100%;">
              
              <!-- Avatar ຜູ້ໃຊ້ -->
              <div class="user-avatar-circle mr-3 <?php echo $isAdmin ? 'user-avatar-admin' : ''; ?>" style="width: 46px; height: 46px; font-size: 1.2rem;">
                <?php if ($u['Id'] == 1): ?>
                  <i class="fas fa-crown text-white"></i>
                <?php else: ?>
                  <?php echo mb_substr($displayName, 0, 1, 'UTF-8'); ?>
                <?php endif; ?>
              </div>

              <!-- ຊື່, ID, ບົດບາດ ແລະ ຊື່ສາຂາ (ສາຂາ [ຊື່ສາຂາ]) -->
              <div>
                <h5 class="font-weight-bold mb-1 text-dark" style="font-family: 'Noto Sans Lao Looped'; font-size: 1.1rem;">
                  <?php echo $displayName; ?>
                  <?php if ($u['Id'] == 1): ?>
                    <span class="badge badge-danger ml-2 px-2 py-1" style="font-size: 0.75rem; border-radius: 6px;"><i class="fas fa-shield-alt"></i>Admin</span>
                  <?php elseif ($isAdmin): ?>
                    <span class="badge badge-primary ml-2 px-2 py-1" style="font-size: 0.75rem; border-radius: 6px;"> ຜູ້ບໍລິຫານ</span>
                  <?php else: ?>
                    <span class="badge badge-secondary ml-2 px-2 py-1" style="font-size: 0.75rem; border-radius: 6px;"><?php echo htmlspecialchars($uStatus ?: 'ພະນັກງານ'); ?></span>
                  <?php endif; ?>
                </h5>
                <div class="text-muted" style="font-size: 0.85rem;">
                  <!-- <i class="fas fa-id-card mr-1"></i> ລະຫັດຜູ້ໃຊ້ ID: #<?php echo $u['Id']; ?> &nbsp;|&nbsp;  -->
                  <i class="fas fa-store text-primary mr-1"></i>  
                  <?php 
                    $bName = !empty($u['store_name']) ? $u['store_name'] : ('ສາຂາ #' . ($u['store_id'] ?? '1'));
                    echo htmlspecialchars(mb_strpos($bName, 'ສາຂາ') === 0 ? $bName : ('ສາຂາ ' . $bName)); 
                  ?>
                </div>
              </div>

              <!-- ປຸ່ມກຳນົດສິດດ່ວນ (Quick Presets Dropdown) -->
              <div class="ml-auto mt-2 mt-md-0">
                <?php if (!$isAdmin): ?>
                  <div class="dropdown">
                    <button class="btn btn-warning text-dark font-weight-bold shadow-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false" style="border-radius: 10px; font-size: 0.88rem; padding: 8px 16px;">
                      <i class="fas fa-magic mr-1"></i> ກຳນົດສິດດ່ວນ
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-lg border-0" style="border-radius: 12px; min-width: 220px;">
                      <h6 class="dropdown-header text-uppercase font-weight-bold text-muted" style="font-size: 0.72rem;">ເລືອກຮູບແບບສິດ (Presets)</h6>
                      <a class="dropdown-item py-2" href="javascript:void(0)" onclick="applyRolePreset(<?php echo $u['Id']; ?>, 'cashier')">
                        <i class="fas fa-cash-register text-success mr-2"></i> ພະນັກງານຂາຍ POS
                      </a>
                      <a class="dropdown-item py-2" href="javascript:void(0)" onclick="applyRolePreset(<?php echo $u['Id']; ?>, 'accountant')">
                        <i class="fas fa-calculator text-primary mr-2"></i> ຄົນຈັດການບັນຊີ
                      </a>
                      <a class="dropdown-item py-2" href="javascript:void(0)" onclick="applyRolePreset(<?php echo $u['Id']; ?>, 'stock_keeper')">
                        <i class="fas fa-boxes text-warning mr-2"></i> ພະນັກງານຄັງສິນຄ້າ
                      </a>
                      <a class="dropdown-item py-2" href="javascript:void(0)" onclick="applyRolePreset(<?php echo $u['Id']; ?>, 'auditor')">
                        <i class="fas fa-search-dollar text-info mr-2"></i> ຜູ້ກວດສອບບັນຊີ
                      </a>
                      <a class="dropdown-item py-2" href="javascript:void(0)" onclick="applyRolePreset(<?php echo $u['Id']; ?>, 'manager')">
                        <i class="fas fa-user-tie text-purple mr-2"></i> ຜູ້ບໍລິຫານ / ຈັດການທັງໝົດ
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item py-2 text-success font-weight-bold" href="javascript:void(0)" onclick="applyRolePreset(<?php echo $u['Id']; ?>, 'all_on')">
                        <i class="fas fa-check-circle mr-2"></i> ເປີດທຸກສິດ
                      </a>
                      <a class="dropdown-item py-2 text-danger font-weight-bold" href="javascript:void(0)" onclick="applyRolePreset(<?php echo $u['Id']; ?>, 'all_off')">
                        <i class="fas fa-times-circle mr-2"></i> ປິດທຸກສິດ
                      </a>
                    </div>
                  </div>
                <?php else: ?>
                  <span class="badge badge-success px-3 py-2" style="border-radius: 8px; font-size: 0.82rem;">
                    <i class="fas fa-check-double mr-1"></i> ເປີດສິດ 100% (ຜູ້ບໍລິຫານ)
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- 2. Detail Body: ຕາຕະລາງສິດ Matrix Table -->
          <div class="perm-detail-body">
            
            <div class="perm-matrix-table-wrapper">
              <table class="perm-matrix-table">
                <!-- Sticky Header ຂອງຕາຕະລາງ -->
                <thead>
                  <tr>
                    <th>ໂມດູນລະບົບ</th>
                    <th class="text-center" style="width: 90px;">ເບິ່ງ</th>
                    <th class="text-center" style="width: 90px;">ເພີ່ມ</th>
                    <th class="text-center" style="width: 90px;">ແກ້ໄຂ</th>
                    <th class="text-center" style="width: 90px;">ລົບ</th>
                  </tr>
                </thead>
                <tbody>

                                    <!-- ໂມດູນ 1: ດາດສ໌ບອດ (Dashboard) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon icon-bg-dashboard">
                          <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ໜ້າ ດາດສ໌ບອດ</div>
                          <div class="perm-module-desc">ເຂົ້າເຖິງ ແລະ ເບິ່ງສະຖິຕິໜ້າດາດສ໌ບອດຫຼັກ</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_dashboard_view_<?php echo $u['Id']; ?>" 
                               data-perm="dashboard" 
                               data-perm-type="view"
                               <?php echo (!empty($u['dashboard']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo $isAdmin ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'dashboard', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                    <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                    <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                  </tr>

                  <!-- ໂມດູນ 2: ຂາຍສິນຄ້າ POS (POS Sales) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon icon-bg-stockin">
                          <i class="fas fa-cash-register"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ຂາຍສິນຄ້າ POS</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_sale_view_<?php echo $u['Id']; ?>" 
                               data-perm="sale" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['sale']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'sale', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_sale_add_<?php echo $u['Id']; ?>" 
                               data-perm="sale" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['sale']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'sale', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_sale_edit_<?php echo $u['Id']; ?>" 
                               data-perm="edit" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_sale_del_<?php echo $u['Id']; ?>" 
                               data-perm="edit" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                  </tr>

                  <!-- ໂມດູນ 3: ຈັດການລູກຄ້າ (Customer Management) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon" style="background: #e0f2fe; color: #0284c7;">
                          <i class="fas fa-user-friends"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ຈັດການລູກຄ້າ</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_customers_view_<?php echo $u['Id']; ?>" 
                               data-perm="customers" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['customers']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'customers', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_customers_add_<?php echo $u['Id']; ?>" 
                               data-perm="customers" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['customers']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'customers', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_customers_edit_<?php echo $u['Id']; ?>" 
                               data-perm="customers" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['customers']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'customers', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_customers_del_<?php echo $u['Id']; ?>" 
                               data-perm="customers" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['customers']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'customers', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                  </tr>

                  <!-- ໂມດູນ 4: ຂໍ້ມູນສິນຄ້າ & ຄັງສິນຄ້າ (Stock Inventory) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon icon-bg-building">
                          <i class="fas fa-boxes"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ຂໍ້ມູນສິນຄ້າ & ຄັງສິນຄ້າ</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_stock_view_<?php echo $u['Id']; ?>" 
                               data-perm="stock" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['stock']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'stock', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_stock_add_<?php echo $u['Id']; ?>" 
                               data-perm="stock" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['stock']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'stock', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_stock_edit_<?php echo $u['Id']; ?>" 
                               data-perm="stock" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['stock']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'stock', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_stock_del_<?php echo $u['Id']; ?>" 
                               data-perm="stock" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['stock']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'stock', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                  </tr>

                  <!-- ໂມດູນ 5: ຈັດການບັນຊີ (Accounting) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon icon-bg-category" style="background: #f3e8ff; color: #8b5cf6;">
                          <i class="fas fa-calculator"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ຈັດການບັນຊີ</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_acc_view_<?php echo $u['Id']; ?>" 
                               data-perm="accounting" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['accounting']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'accounting', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_acc_add_<?php echo $u['Id']; ?>" 
                               data-perm="accounting" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['accounting']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'accounting', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_acc_edit_<?php echo $u['Id']; ?>" 
                               data-perm="accounting" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['accounting']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'accounting', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_acc_del_<?php echo $u['Id']; ?>" 
                               data-perm="accounting" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['accounting']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'accounting', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                  </tr>

                  <!-- ໂມດູນ 6: ລາຍງານ & ການເງິນ (Reports & Finance) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon icon-bg-asset">
                          <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ລາຍງານ & ການເງິນ</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_report_view_<?php echo $u['Id']; ?>" 
                               data-perm="report" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'report', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_report_edit_<?php echo $u['Id']; ?>" 
                               data-perm="report" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'report', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_report_del_<?php echo $u['Id']; ?>" 
                               data-perm="report" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'report', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                  </tr>

                  <!-- ໂມດູນ 7: ຈັດການຜູ້ນຳໃຊ້ (Users Management) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon icon-bg-users">
                          <i class="fas fa-users-cog"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ຈັດການຜູ້ນຳໃຊ້</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_users_view_<?php echo $u['Id']; ?>" 
                               data-perm="users" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['users']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'users', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_users_add_<?php echo $u['Id']; ?>" 
                               data-perm="users" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['users']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'users', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_users_edit_<?php echo $u['Id']; ?>" 
                               data-perm="users" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['users']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'users', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_users_del_<?php echo $u['Id']; ?>" 
                               data-perm="users" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['users']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'users', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                  </tr>

                  <!-- ໂມດູນ 8: ກຳນົດສິດ (Permissions Management) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon" style="background: #fef3c7; color: #d97706;">
                          <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ກຳນົດສິດ</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_permissions_view_<?php echo $u['Id']; ?>" 
                               data-perm="permissions" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['permissions']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'permissions', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_permissions_edit_<?php echo $u['Id']; ?>" 
                               data-perm="permissions" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['permissions']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'permissions', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                  </tr>

                  <!-- ໂມດູນ 9: ຈັດການສາຂາ (Multi-Branch Management) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon" style="background: #dcfce7; color: #16a34a;">
                          <i class="fas fa-network-wired"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ຈັດການສາຂາ</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_branches_view_<?php echo $u['Id']; ?>" 
                               data-perm="setup" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'setup', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_branches_add_<?php echo $u['Id']; ?>" 
                               data-perm="setup" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'setup', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_branches_edit_<?php echo $u['Id']; ?>" 
                               data-perm="setup" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'setup', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_branches_del_<?php echo $u['Id']; ?>" 
                               data-perm="setup" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'setup', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                  </tr>

                                    <!-- ໂມດູນ 10: ຕັ້ງຄ່າລະບົບ (System Settings) -->
                  <tr>
                    <td>
                      <div class="perm-module-info">
                        <div class="perm-module-icon icon-bg-settings">
                          <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                          <div class="perm-module-title">ຕັ້ງຄ່າລະບົບ & ຈັດການສາຂາ</div>
                          <div class="perm-module-desc">ເຂົ້າເຖິງ ແລະ ຕັ້ງຄ່າລະບົບ, ຮ້ານ, ບາໂຄ້ດ, ອັດຕາແລກປ່ຽນ ຯລຯ</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_setup_view_<?php echo $u['Id']; ?>" 
                               data-perm="setup" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'setup', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                    <td class="text-center">
                      <label class="matrix-switch">
                        <input type="checkbox" 
                               id="perm_setup_edit_<?php echo $u['Id']; ?>" 
                               data-perm="edit" 
                               data-user-id="<?php echo $u['Id']; ?>"
                               <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                               <?php echo ($isAdmin) ? 'disabled' : ''; ?>
                               onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                        <span class="matrix-slider"></span>
                      </label>
                    </td>
                    <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                  </tr>

</tbody>
              </table>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</div>
