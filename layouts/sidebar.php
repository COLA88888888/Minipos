<?php $bp = $base_path ?? ''; ?>
<!-- UNIFIED SIDEBAR (ແຖບເມນູໄດນາມິກກວດສອບຕາມສິດ) -->
<aside class="main-sidebar elevation-4 sidebar-dark-primary" style="background-color: rgb(2, 99, 255); position: fixed; top: 0; bottom: 0; left: 0; z-index: 1038;">
  <!-- Logo & Brand -->
  <a href="<?php echo $bp; ?>home/home.php" target="frame" class="brand-link" style="padding: 0 16px; display: flex; align-items: center; justify-content: flex-start; gap: 12px; text-decoration: none; border-bottom: none; background-color: rgb(2, 99, 255);">
    <div style="background: #ffffff; border-radius: 50%; padding: 3px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.18); width: 44px; height: 44px; min-width: 44px;">
      <img src="<?php echo $bp; ?>assets/img/logo/logo.png" alt="POS Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
    </div>
    <span class="brand-text font-weight-bold" style="font-size: 1.05rem; color: #ffffff; letter-spacing: 0.5px; white-space: nowrap;">POS System</span>
  </a>

  <!-- Menu Sidebar -->
  <div class="sidebar" style="height: calc(100vh - 64px); overflow-y: auto;">
  
    <!-- ===== User Panel (ຂໍ້ມູນຜູ້ໃຊ້ຢູ່ sidebar) ===== -->
    <?php
      // ດຶງຂໍ້ມູນ user ຈາກ session
      $sb_display_name = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
      if ($sb_display_name === '') $sb_display_name = $_SESSION['username'] ?? 'admin';
      $sb_profile_img = $_SESSION['profile_img'] ?? 'default.png';
      if (empty($sb_profile_img) || !file_exists(__DIR__ . '/../assets/img/users/' . $sb_profile_img)) {
        $sb_profile_img = 'default.png';
      }
      $sb_img_path = $bp . 'assets/img/users/' . $sb_profile_img;
    ?>
    <div class="user-panel mt-3 pb-3 mb-2 d-flex align-items-center" style="border-bottom: 1px solid rgba(255,255,255,0.15);">
      <div class="image" style="flex-shrink:0;">
        <img src="<?php echo htmlspecialchars($sb_img_path); ?>" class="img-circle elevation-2"
             alt="User Avatar"
             style="width: 42px; height: 42px; object-fit: cover; border: 2px solid rgba(255,255,255,0.55);">
      </div>
      <div class="info" style="padding-left: 10px; overflow: hidden;">
        <span style="display: block; color: #ffffff; font-weight: 700; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
          <?php echo htmlspecialchars($sb_display_name); ?>
        </span>
        <span style="font-size: 0.72rem; color: #4ade80; font-weight: 600; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
          <i class="fas fa-circle" style="font-size: 6px;"></i> ກຳລັງໃຊ້ງານ
        </span>
      </div>
    </div>
    <!-- /User Panel -->

    <!-- Dynamic Menu Items Based on Permissions -->
    <nav class="mt-2 pb-4">
      <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu" data-accordion="true">
        
        <?php if (hasPermission('dashboard')): ?>
          <!-- Header: Main menu -->
          <li class="nav-header text-uppercase" style="color: rgba(255,255,255,0.5); font-size: 0.75rem; letter-spacing: 1px;">ເມນູ</li>
          
          <!-- Menu: Dashboard -->
          <li class="nav-item">
            <a href="home.php" target="frame" class="nav-link">
              <i class="nav-icon fas fa-chart-line text-success"></i>
              <p>ດາດສ໌ບອດ</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if (hasPermission('sale')): ?>
          <?php if (hasPermission('report')): ?>
            <!-- Dropdown Treeview: ຂາຍສິນຄ້າ POS (When both POS and Report permissions exist) -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-cash-register text-warning"></i>
                <p>
                  ຂາຍສິນຄ້າ POS
                  <i class="right fas fa-angle-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <!-- Sub-menu 1: ຂາຍສິນຄ້າ -->
                <li class="nav-item">
                  <a href="<?php echo $bp; ?>pages/pos/pos.php" target="frame" class="nav-link">
                    <i class="nav-icon fas fa-shopping-cart text-warning"></i>
                    <p>ຂາຍສິນຄ້າ</p>
                  </a>
                </li>

                <!-- Sub-menu 2: ລາຍການຂາຍສິນຄ້າ -->
                <li class="nav-item">
                  <a href="<?php echo $bp; ?>pages/reports/item_sales.php" target="frame" class="nav-link">
                    <i class="nav-icon fas fa-boxes" style="color: #a855f7;"></i>
                    <p>ລາຍການຂາຍສິນຄ້າ</p>
                  </a>
                </li>
              </ul>
            </li>
          <?php else: ?>
            <!-- Single Direct Link: ຂາຍສິນຄ້າ POS (When only POS Sale permission exists) -->
            <li class="nav-item">
              <a href="<?php echo $bp; ?>pages/pos/pos.php" target="frame" class="nav-link">
                <i class="nav-icon fas fa-cash-register text-warning"></i>
                <p>ຂາຍສິນຄ້າ POS</p>
              </a>
            </li>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (hasPermission('customers')): ?>
          <!-- Menu: Customer Management -->
          <li class="nav-item">
            <a href="<?php echo $bp; ?>pages/customers/customers.php" target="frame" class="nav-link">
              <i class="nav-icon fas fa-user-friends text-info"></i>
              <p>ຈັດການລູກຄ້າ</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if (hasPermission('stock')): ?>
          <!-- Header: Inventory & Products -->
          <!-- <li class="nav-header text-uppercase" style="color: rgba(255,255,255,0.5); font-size: 0.75rem; letter-spacing: 1px; padding-top: 15px;">ການຈັດການສິນຄ້າ</li> -->

          <!-- Dropdown Treeview: ຂໍ້ມູນສິນຄ້າ -->
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-boxes text-primary"></i>
              <p>
                ຂໍ້ມູນສິນຄ້າ
                <i class="right fas fa-angle-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <!-- Sub-menu: ປະເພດສິນຄ້າ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/categories/categories.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-th-list text-info"></i>
                  <p>ປະເພດສິນຄ້າ</p>
                </a>
              </li>

              <!-- Sub-menu: ລາຍການສິນຄ້າ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/products/products.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-box text-primary"></i>
                  <p>ລາຍການສິນຄ້າ</p>
                </a>
              </li>

              <!-- Sub-menu: ນຳເຂົ້າສິນຄ້າ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/import_stock/import_stock.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-truck-loading text-warning"></i>
                  <p>ນຳເຂົ້າສິນຄ້າ</p>
                </a>
              </li>

              <!-- Sub-menu: ລາຍການສິນຄ້າຮັບເຂົ້າ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/import_stock/import_list.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-list-alt text-success"></i>
                  <p>ລາຍການສິນຄ້າຮັບເຂົ້າ</p>
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>

        <?php if (hasPermission('accounting')): ?>
          <!-- Header: Reports & Accounting -->
          <li class="nav-header text-uppercase" style="color: rgba(255,255,255,0.5); font-size: 0.75rem; letter-spacing: 1px; padding-top: 15px;">ລາຍງານ & ການເງິນ</li>

          <!-- Menu: Bank Management (ຈັດການທະນາຄານ) -->
          <li class="nav-item">
            <a href="<?php echo $bp; ?>pages/bank/bank.php" target="frame" class="nav-link">
              <i class="nav-icon fas fa-university text-info"></i>
              <p>ຈັດການທະນາຄານ</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if (hasPermission('report')): ?>
          <!-- Dropdown Treeview: ລາຍງານ -->
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-file-invoice-dollar text-orange"></i>
              <p>
                ລາຍງານ
                <i class="right fas fa-angle-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <!-- 1. ລາຍງານປະຈຳວັນ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/reports/daily_report.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-calendar-day text-info"></i>
                  <p>ລາຍງານປະຈຳວັນ</p>
                </a>
              </li>

              <!-- 2. ລາຍງານການຂາຍທັງໝົດ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/reports/all_sales.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-file-invoice-dollar text-success"></i>
                  <p>ລາຍງານການຂາຍທັງໝົດ</p>
                </a>
              </li>

              <!-- 3. ລາຍງານສິນຄ້າຂາຍດີ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/reports/best_seller.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-fire text-danger"></i>
                  <p>ລາຍງານສິນຄ້າຂາຍດີ</p>
                </a>
              </li>

              <!-- 4. ລາຍງານກຳໄລ-ຕົ້ນທຶນ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/reports/profit_cost.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-chart-line text-warning"></i>
                  <p>ລາຍງານກຳໄລ-ຕົ້ນທຶນ</p>
                </a>
              </li>

              <!-- 5. ລາຍງານການເງິນ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/reports/financial.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-wallet text-info"></i>
                  <p>ລາຍງານການເງິນ</p>
                </a>
              </li>

              <!-- 6. ລາຍງານຕາມ/ປະເພດສິນຄ້າ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/reports/category_sales.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-layer-group text-primary"></i>
                  <p>ລາຍງານຕາມປະເພດສິນຄ້າ</p>
                </a>
              </li>

              <!-- 7. ປະຫວັດການລົບບິນຂາຍ -->
              <li class="nav-item">
                <a href="<?php echo $bp; ?>pages/reports/delete_bills.php" target="frame" class="nav-link">
                  <i class="nav-icon fas fa-trash-alt text-danger"></i>
                  <p>ປະຫວັດການລົບບິນຂາຍ</p>
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>

        <?php if (hasPermission('users') || hasPermission('permissions') || hasPermission('setup')): ?>
          <!-- Header: Administration & Settings -->
          <li class="nav-header text-uppercase" style="color: rgba(255,255,255,0.5); font-size: 0.75rem; letter-spacing: 1px; padding-top: 15px;">ການຈັດການລະບົບ</li>

          <?php if (hasPermission('users')): ?>
            <!-- Menu: Users Management -->
            <li class="nav-item">
              <a href="<?php echo $bp; ?>pages/users_manage/users_manage.php" target="frame" class="nav-link">
                <i class="nav-icon fas fa-users text-info"></i>
                <p>ຈັດການຜູ້ນຳໃຊ້</p>
              </a>
            </li>
          <?php endif; ?>

          <?php if (hasPermission('permissions')): ?>
            <!-- Menu: Permission Management -->
            <li class="nav-item">
              <a href="<?php echo $bp; ?>pages/permissions/permissions.php" target="frame" class="nav-link">
                <i class="nav-icon fas fa-user-shield text-warning"></i>
                <p>ກຳນົດສິດ</p>
              </a>
            </li>
          <?php endif; ?>

          <?php if (hasPermission('setup')): ?>
            <!-- Menu: ຈັດການສາຂາ (ໂຟເດີແຍກຕ່າງຫາກ pages/branches/) -->
            <li class="nav-item">
              <a href="<?php echo $bp; ?>pages/branches/branches.php" target="frame" class="nav-link">
                <i class="nav-icon fas fa-network-wired text-success"></i>
                <p>ຈັດການສາຂາ</p>
              </a>
            </li>

            <!-- Dropdown Treeview: ຕັ້ງຄ່າລະບົບ -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-cogs text-secondary"></i>
                <p>
                  ຕັ້ງຄ່າລະບົບ
                  <i class="right fas fa-angle-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <!-- 1. ຂໍ້ມູນຮ້ານ (pages/settings/stores/) -->
                <li class="nav-item">
                  <a href="<?php echo $bp; ?>pages/settings/stores/" target="frame" class="nav-link">
                    <i class="nav-icon fas fa-store text-primary"></i>
                    <p>ຂໍ້ມູນຮ້ານ</p>
                  </a>
                </li>

                <!-- 2. ພິມບາໂຄ້ດ -->
                <li class="nav-item">
                  <a href="<?php echo $bp; ?>pages/settings/print_barcode/" target="frame" class="nav-link">
                    <i class="nav-icon fas fa-barcode text-info"></i>
                    <p>ພິມບາໂຄ້ດ</p>
                  </a>
                </li>

                <!-- 3. ອັດຕາແລກປ່ຽນເງິນ -->
                <li class="nav-item">
                  <a href="<?php echo $bp; ?>pages/settings/exchange_rate/" target="frame" class="nav-link">
                    <i class="nav-icon fas fa-exchange-alt text-success"></i>
                    <p>ອັດຕາເເລກປ່ຽນເງິນ</p>
                  </a>
                </li>

                <!-- 4. ໂປຣໂມຊັ່ນ -->
                <li class="nav-item">
                  <a href="<?php echo $bp; ?>pages/settings/promotions/" target="frame" class="nav-link">
                    <i class="nav-icon fas fa-percent text-danger"></i>
                    <p>ໂປຣໂມຊັ່ນ</p>
                  </a>
                </li>

                <!-- 5. ປັບລາຄາສິນຄ້າ -->
                <li class="nav-item">
                  <a href="<?php echo $bp; ?>pages/settings/price_adjustment/" target="frame" class="nav-link">
                    <i class="nav-icon fas fa-tags text-warning"></i>
                    <p>ປັບລາຄາສິນຄ້າ</p>
                  </a>
                </li>

                <!-- 6. ຕັ້ງຄ່າປິ່ນເຕີ -->
                <li class="nav-item">
                  <a href="<?php echo $bp; ?>pages/settings/printers/" target="frame" class="nav-link">
                    <i class="nav-icon fas fa-print text-teal"></i>
                    <p>ຕັ້ງຄ່າປິ່ນເຕີ</p>
                  </a>
                </li>
              </ul>
            </li>
          <?php endif; ?>
        <?php endif; ?>

      </ul>
    </nav>
  </div>

</aside>
