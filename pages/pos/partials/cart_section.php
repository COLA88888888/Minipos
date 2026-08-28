<!-- Right Side: POS Cart Panel -->
<div class="pos-cart shadow-sm">
  <!-- Active Bills Navigation Bar -->
  <div class="pos-active-bills-bar bg-light border-bottom p-2 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center" style="gap: 6px;">
      <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold px-3 py-1.5 shadow-sm" onclick="openCustomerDisplayWindow()" title="<?php echo htmlspecialchars(t('pos.customer_display_title', 'ເປີດໜ້າຈໍສະແດງຜົນລູກຄ້າ (Customer Display)')); ?>" style="border-radius: 8px; white-space: nowrap; font-size: 0.85rem; background: #ffffff;">
        <i class="fas fa-desktop mr-1"></i> <?php echo htmlspecialchars(t('pos.customer_display_btn', 'ຈໍລູກຄ້າ')); ?>
      </button>
      <!-- Network Status Badge (Shown ONLY when Offline or when Pending Sync exists) -->
      <span class="badge px-2.5 py-1.5 shadow-2xs font-weight-bold" id="netStatusBadge" onclick="syncOfflineSalesToServer(true)" style="display: none; font-size: 0.86rem; border-radius: 6px; background-color: #e2e8f0; color: #1e293b; cursor: pointer;" title="<?php echo htmlspecialchars(t('pos.net_status_title', 'ສະຖານະການເຊື່ອມຕໍ່ (ກົດເພື່ອ Sync)')); ?>">
        <i class="fas fa-circle text-success mr-1" id="netStatusIcon" style="font-size: 0.6rem;"></i>
        <span id="netStatusText"><?php echo htmlspecialchars(t('pos.net_status_online', 'ອອນໄລນ໌')); ?></span>
        <span id="offlineQueueBadge" class="badge badge-warning ml-1 text-dark" style="display: none; font-size: 0.8rem; border-radius: 4px;">0</span>
      </span>
    </div>
    <div class="d-flex align-items-center" style="gap: 6px;">
      <button type="button" class="btn btn-sm btn-success font-weight-bold px-3 py-1.5 shadow-sm" onclick="createNewBillModal()" title="<?php echo htmlspecialchars(t('pos.new_bill', 'ເປີດບິນໃໝ່')); ?>" style="border-radius: 8px; white-space: nowrap; font-size: 0.85rem;">
        <i class="fas fa-plus-circle mr-1"></i> <?php echo htmlspecialchars(t('pos.new_bill', 'ເປີດບິນໃໝ່')); ?>
      </button>
      <button type="button" class="btn btn-sm btn-outline-warning text-dark font-weight-bold px-3 py-1.5 shadow-sm" id="btnActiveBills" onclick="openActiveBillsModal()" title="<?php echo htmlspecialchars(t('pos.active_bills_title', 'ບິນທີ່ເປີດຢູ່')); ?>" disabled style="border-radius: 8px; white-space: nowrap; font-size: 0.85rem; background: #ffffff;">
        <i class="fas fa-list-alt mr-1 text-primary"></i> <?php echo htmlspecialchars(t('pos.active_bills_title', 'ບິນທີ່ເປີດຢູ່')); ?> <span class="badge badge-dark font-weight-bold ml-1" id="activeBillsCountBadge" style="display: none;">0</span>
      </button>
    </div>
  </div>

  <!-- Customer Bar -->
  <div class="pos-customer-bar bg-white border-bottom p-2 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center pr-2" style="overflow: hidden; flex: 1;">
      <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-2" style="width: 38px; height: 38px; flex-shrink: 0; border: 1.5px solid #c7d2e8;">
        <i class="fas fa-user text-primary" style="font-size: 1.1rem;"></i>
      </div>
      <div style="overflow: hidden; min-width: 0;">
        <small class="text-muted font-weight-bold d-block" style="font-size: 0.82rem; line-height: 1.1; margin-bottom: 2px;"><?php echo htmlspecialchars(t('pos.customer_label', 'ລູກຄ້າ / ສະມາຊິກ:')); ?></small>
        <span class="font-weight-bold text-dark text-truncate d-block" style="font-size: 0.92rem; line-height: 1.2;" id="selectedCustomerDisplay"><?php echo htmlspecialchars(t('pos.customer_default', 'ລູກຄ້າທົ່ວໄປ')); ?></span>
      </div>
    </div>
    <div class="d-flex align-items-center" style="gap: 6px; flex-shrink: 0;">
      <button class="btn btn-sm btn-outline-primary font-weight-bold px-2.5 py-1" type="button" onclick="openCustomerSelectModal()" style="border-radius: 8px; font-size: 0.82rem;">
        <i class="fas fa-search mr-1"></i> <?php echo htmlspecialchars(t('pos.select', 'ເລືອກ')); ?>
      </button>
      <button class="btn btn-sm btn-outline-success font-weight-bold px-2 py-1" type="button" onclick="openAddCustomerModal()" style="border-radius: 8px; font-size: 0.82rem;" title="<?php echo htmlspecialchars(t('pos.add_new_customer', 'ເພີ່ມລູກຄ້າໃໝ່')); ?>">
        <i class="fas fa-user-plus"></i>
      </button>
    </div>
  </div>

  <div class="cart-items" id="cartItemsContainer">
    <div class="text-center py-5 text-muted" id="cartPlaceholder">
      <i class="fas fa-barcode fa-3x mb-2 text-secondary opacity-50"></i>
      <p class="mb-0"><?php echo htmlspecialchars(t('pos.cart_empty_hint', 'ສະແກນບາໂຄ້ດ ຫຼື ເລືອກສິນຄ້າເພື່ອເລີ່ມຕົ້ນຂາຍ')); ?></p>
    </div>
  </div>

  <!-- Calculations Summary & Actions -->
  <div class="cart-footer">
    <div class="d-flex justify-content-between mb-1 small text-muted">
      <span><?php echo htmlspecialchars(t('pos.subtotal_label', 'ລວມມູນຄ່າ:')); ?></span>
      <span class="font-weight-bold text-dark" id="cartSubtotal">0 ₭</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="small text-muted"><?php echo htmlspecialchars(t('pos.bill_discount_label', 'ສ່ວນຫຼຸດທ້າຍບິນ:')); ?></span>
      <div class="input-group input-group-sm" style="width: 130px;">
        <input type="text" id="cartDiscount" class="form-control text-right text-danger font-weight-bold" value="0" oninput="formatPriceInput(this); updateCartUI();">
        <div class="input-group-append"><span class="input-group-text font-weight-bold">₭</span></div>
      </div>
    </div>
    
    <?php
      $cTaxType = !empty($company['tax_type']) ? $company['tax_type'] : 'none';
      $cVatPercent = ($cTaxType === 'none') ? 0 : (isset($company['vat_percent']) ? floatval($company['vat_percent']) : 0);
      $showCartVat = ($cVatPercent > 0 && $cTaxType !== 'none');
      $cLabelText = ($cTaxType === 'exclusive') ? (t('pos.vat_exclusive_label', 'ອມພ') . " ({$cVatPercent}%):") : (t('pos.vat_inclusive_label', 'ລວມ ອມພ') . " ({$cVatPercent}%):");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-1 small text-muted" id="cartVatRow" style="<?php echo $showCartVat ? 'display: flex !important;' : 'display: none !important;'; ?>">
      <span id="cartVatLabel"><?php echo $cLabelText; ?></span>
      <span class="font-weight-bold text-dark" id="cartVat">0 ₭</span>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3 pt-2 border-top">
      <span class="font-weight-bold" style="font-size: 1.05rem; color: #1e293b;"><?php echo htmlspecialchars(t('pos.net_total_label', 'ຍອດຊຳລະສຸດທິ:')); ?></span>
      <span class="font-weight-bold" style="font-size: 1.45rem; color: #16a34a;" id="cartTotal">0 ₭</span>
    </div>

    <!-- Desktop Action Buttons (2 Rows Layout - Visible ≥ 992px) -->
    <div class="d-none d-lg-block">
      <div class="row no-gutters mb-2" style="gap: 6px;">
        <div class="col">
          <button class="btn btn-info btn-block font-weight-bold py-2 text-white" onclick="holdCurrentOrder()" id="btnHoldOrder" style="border-radius: 8px; font-size: 0.88rem;">
            <i class="fas fa-pause-circle mr-1"></i> <?php echo htmlspecialchars(t('pos.hold_bill', 'ພັກບິນ')); ?>
          </button>
        </div>
        <div class="col">
          <button class="btn btn-primary btn-block font-weight-bold py-2 text-white" onclick="openHeldOrdersModal()" id="btnHeldOrders" style="border-radius: 8px; font-size: 0.88rem;">
            <i class="fas fa-history mr-1"></i> <?php echo htmlspecialchars(t('pos.held_bills', 'ບິນທີ່ພັກ')); ?> <span class="badge badge-light badge-hold-count text-dark font-weight-bold ml-1" id="heldCountBadge">0</span>
          </button>
        </div>
      </div>

      <div class="row no-gutters" style="gap: 6px;">
        <div class="col-4">
          <button class="btn btn-danger btn-block font-weight-bold py-2.5" onclick="clearCart()" id="btnClearCart" style="border-radius: 8px; font-size: 0.92rem;">
            <i class="fas fa-trash-alt mr-1"></i> <?php echo htmlspecialchars(t('pos.clear', 'ລ້າງ')); ?>
          </button>
        </div>
        <div class="col">
          <button class="btn btn-warning btn-block font-weight-bold py-2.5 text-dark shadow-sm" onclick="openCheckoutModal()" id="btnCheckout" style="border-radius: 8px; font-size: 1.0rem;">
            <i class="fas fa-money-bill-wave mr-1"></i> <?php echo htmlspecialchars(t('pos.pay', 'ຊຳລະເງິນ')); ?>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Action Buttons (Single Row 4 Buttons Grid - Visible ≤ 991px) -->
    <div class="d-lg-none pt-1">
      <div class="row no-gutters" style="gap: 3px; flex-wrap: nowrap;">
        <div class="col-3">
          <button class="btn btn-info btn-block font-weight-bold text-white d-flex align-items-center justify-content-center shadow-sm" onclick="holdCurrentOrder()" id="btnHoldOrderMobile" style="border-radius: 6px; font-size: 0.86rem; min-height: 36px; padding: 3px 1px; white-space: nowrap;">
            <i class="fas fa-pause-circle mr-1" style="font-size: 0.9rem !important;"></i> <span><?php echo htmlspecialchars(t('pos.hold_bill', 'ພັກບິນ')); ?></span>
          </button>
        </div>
        <div class="col-3">
          <button class="btn btn-primary btn-block font-weight-bold text-white d-flex align-items-center justify-content-center shadow-sm" onclick="openHeldOrdersModal()" id="btnHeldOrdersMobile" style="border-radius: 6px; font-size: 0.86rem; min-height: 36px; padding: 3px 1px; white-space: nowrap;">
            <i class="fas fa-history mr-1" style="font-size: 0.9rem !important;"></i> <span><?php echo htmlspecialchars(t('pos.held_bills_mobile', 'ບິນພັກ')); ?></span> <span class="badge badge-light badge-hold-count text-dark font-weight-bold ml-1" style="font-size: 0.78rem; padding: 1px 3px;">0</span>
          </button>
        </div>
        <div class="col-2">
          <button class="btn btn-danger btn-block font-weight-bold d-flex align-items-center justify-content-center shadow-sm" onclick="clearCart()" id="btnClearCartMobile" style="border-radius: 6px; font-size: 0.86rem; min-height: 36px; padding: 3px 1px; white-space: nowrap;">
            <i class="fas fa-trash-alt mr-1" style="font-size: 0.9rem !important;"></i> <span><?php echo htmlspecialchars(t('pos.clear', 'ລ້າງ')); ?></span>
          </button>
        </div>
        <div class="col-4">
          <button class="btn btn-warning btn-block font-weight-bold text-dark shadow-sm d-flex align-items-center justify-content-center" onclick="openCheckoutModal()" id="btnCheckoutMobile" style="border-radius: 6px; font-size: 0.9rem; min-height: 36px; padding: 3px 1px; white-space: nowrap; background: #eab308; border-color: #ca8a04;">
            <i class="fas fa-money-bill-wave mr-1" style="font-size: 0.96rem !important;"></i> <span><?php echo htmlspecialchars(t('pos.pay', 'ຊຳລະເງິນ')); ?></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
