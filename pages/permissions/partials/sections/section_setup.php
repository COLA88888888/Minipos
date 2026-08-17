<!-- SECTION 7: ຕັ້ງຄ່າລະບົບ (SYSTEM SETTINGS) -->
<tr class="table-primary">
  <td colspan="5" class="font-weight-bold text-uppercase py-2" style="font-size: 0.82rem; letter-spacing: 0.5px;">
    <i class="fas fa-cogs mr-1"></i> 7. ຕັ້ງຄ່າລະບົບ & ຈັດການຜູ້ນຳໃຊ້
  </td>
</tr>

<!-- 7.1 ຈັດການຜູ້ນຳໃຊ້ (Users Management) -->
<tr>
  <td class="pl-4">
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

<!-- 7.2 ກຳນົດສິດ (Permissions Management) -->
<tr>
  <td class="pl-4">
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

<!-- 7.3 ຈັດການສາຂາ (Multi-Branch Management) -->
<tr>
  <td class="pl-4">
    <div class="perm-module-info">
      <div class="perm-module-icon" style="background: #dcfce7; color: #16a34a;">
        <i class="fas fa-network-wired"></i>
      </div>
      <div>
        <div class="perm-module-title">ຈັດການສາຂາ</div>
        <div class="perm-module-desc">ເພີ່ມ, ແກ້ໄຂ ແລະ ຈັດການສາຂາທັງໝົດ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_branches_view_<?php echo $u['Id']; ?>" 
             data-perm="branches" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['branches']) || !empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'branches', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_branches_add_<?php echo $u['Id']; ?>" 
             data-perm="branches" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['branches']) || !empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'branches', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_branches_edit_<?php echo $u['Id']; ?>" 
             data-perm="branches" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['branches']) || !empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'branches', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_branches_del_<?php echo $u['Id']; ?>" 
             data-perm="branches" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['branches']) || !empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'branches', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
</tr>

<!-- 7.4 ຕັ້ງຄ່າລະບົບ & ຈັດການສາຂາ -->
<tr>
  <td class="pl-4">
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

<!-- 7.5 ຖານຂໍ້ມູນ (Database Management) -->
<tr>
  <td class="pl-4">
    <div class="perm-module-info">
      <div class="perm-module-icon" style="background: #e0f2fe; color: #0284c7;">
        <i class="fas fa-database"></i>
      </div>
      <div>
        <div class="perm-module-title">ຈັດການຖານຂໍ້ມູນ</div>
        <div class="perm-module-desc">ເຂົ້າເຖິງ ແລະ ເບິ່ງສະຖິຕິ/ຈັດການຖານຂໍ້ມູນລະບົບ</div>
      </div>
    </div>
  </td>
  <td class="text-center">
    <label class="matrix-switch">
      <input type="checkbox" 
             id="perm_database_view_<?php echo $u['Id']; ?>" 
             data-perm="database" 
             data-user-id="<?php echo $u['Id']; ?>"
             <?php echo (!empty($u['database']) || $isAdmin) ? 'checked' : ''; ?>
             <?php echo ($isAdmin) ? 'disabled' : ''; ?>
             onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'database', this)">
      <span class="matrix-slider"></span>
    </label>
  </td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
  <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
</tr>
