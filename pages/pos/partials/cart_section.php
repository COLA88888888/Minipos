<!-- Right Side: POS Cart Panel -->
<div class="pos-cart shadow-sm">
  <div class="bg-primary text-white p-3 d-flex justify-content-between align-items-center">
    <h5 class="mb-0 font-weight-bold" style="font-family: 'Noto Sans Lao Looped';">
      <i class="fas fa-shopping-basket mr-2"></i> ລາຍການຂາຍ
    </h5>
    <div class="d-flex align-items-center" style="gap: 6px;">
      <button type="button" class="btn btn-sm btn-light text-primary font-weight-bold shadow-sm" onclick="openCustomerDisplayWindow()" title="ເປີດໜ້າຈໍສະແດງຜົນລູກຄ້າ (Customer Display)" style="border-radius: 8px; font-size: 0.82rem; padding: 4px 10px;">
        <i class="fas fa-desktop mr-1"></i> ຈໍລູກຄ້າ
      </button>
      <button type="button" class="btn btn-sm btn-light text-primary font-weight-bold d-lg-none" onclick="switchMobilePosTab('products')" style="border-radius: 8px; font-size: 0.82rem; padding: 4px 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
        <i class="fas fa-boxes mr-1"></i> + ເລືອກສິນຄ້າ
      </button>
    </div>
  </div>

  <!-- Active Bills Navigation Bar -->
  <div class="pos-active-bills-bar bg-light border-bottom p-2 d-flex justify-content-end align-items-center">
    <div class="d-flex align-items-center" style="gap: 6px;">
      <button type="button" class="btn btn-sm btn-success font-weight-bold px-3 py-1.5 shadow-sm" onclick="createNewBillModal()" title="ເປີດບິນໃໝ່" style="border-radius: 8px; white-space: nowrap; font-size: 0.85rem;">
        <i class="fas fa-plus-circle mr-1"></i> ເປີດບິນໃໝ່
      </button>
      <button type="button" class="btn btn-sm btn-outline-warning text-dark font-weight-bold px-3 py-1.5 shadow-sm" id="btnActiveBills" onclick="openActiveBillsModal()" title="ບິນທີ່ເປີດຢູ່" disabled style="border-radius: 8px; white-space: nowrap; font-size: 0.85rem; background: #ffffff;">
        <i class="fas fa-list-alt mr-1 text-primary"></i> ບິນທີ່ເປີດຢູ່ <span class="badge badge-dark font-weight-bold ml-1" id="activeBillsCountBadge" style="display: none;">0</span>
      </button>
    </div>
  </div>

  <!-- Customer Bar -->
  <div class="pos-customer-bar bg-white border-bottom p-2 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center pr-2" style="overflow: hidden; flex: 1;">
      <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-2" style="width: 38px; height: 38px; flex-shrink: 0; border: 1.5px solid #bfdbfe;">
        <i class="fas fa-user text-primary" style="font-size: 1.1rem;"></i>
      </div>
      <div style="overflow: hidden; min-width: 0;">
        <small class="text-muted font-weight-bold d-block" style="font-size: 0.72rem; line-height: 1.1; margin-bottom: 2px;">ລູກຄ້າ / ສະມາຊິກ:</small>
        <span class="font-weight-bold text-dark text-truncate d-block" style="font-size: 0.92rem; line-height: 1.2;" id="selectedCustomerDisplay">ລູກຄ້າທົ່ວໄປ</span>
      </div>
    </div>
    <div class="d-flex align-items-center" style="gap: 6px; flex-shrink: 0;">
      <button class="btn btn-sm btn-outline-primary font-weight-bold px-2.5 py-1" type="button" onclick="openCustomerSelectModal()" style="border-radius: 8px; font-size: 0.82rem;">
        <i class="fas fa-search mr-1"></i> ເລືອກ
      </button>
      <button class="btn btn-sm btn-outline-success font-weight-bold px-2 py-1" type="button" onclick="openAddCustomerModal()" style="border-radius: 8px; font-size: 0.82rem;" title="ເພີ່ມລູກຄ້າໃໝ່">
        <i class="fas fa-user-plus"></i>
      </button>
    </div>
  </div>
  
  <div class="cart-items" id="cartItemsContainer">
    <div class="text-center py-5 text-muted" id="cartPlaceholder">
      <i class="fas fa-barcode fa-3x mb-2 text-secondary opacity-50"></i>
      <p class="mb-0">ສະແກນບາໂຄ້ດ ຫຼື ເລືອກສິນຄ້າເພື່ອເລີ່ມຕົ້ນຂາຍ</p>
    </div>
  </div>
  
  <!-- Calculations Summary & Actions -->
  <div class="cart-footer">
    <div class="d-flex justify-content-between mb-1 small text-muted">
      <span>ລວມມູນຄ່າ:</span>
      <span class="font-weight-bold text-dark" id="cartSubtotal">0 ₭</span>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="small text-muted">ສ່ວນຫຼຸດທ້າຍບິນ:</span>
      <div class="input-group input-group-sm" style="width: 130px;">
        <input type="text" id="cartDiscount" class="form-control text-right text-danger font-weight-bold" value="0" oninput="formatPriceInput(this); updateCartUI();">
        <div class="input-group-append"><span class="input-group-text font-weight-bold">₭</span></div>
      </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3 pt-2 border-top">
      <span class="font-weight-bold" style="font-size: 1.05rem; color: #1e293b;">ຍອດຊຳລະສຸດທິ:</span>
      <span class="font-weight-bold" style="font-size: 1.45rem; color: #16a34a;" id="cartTotal">0 ₭</span>
    </div>
    
    <!-- Hold & Clear & Checkout Action Buttons -->
    <div class="row no-gutters mb-2" style="gap: 6px;">
      <div class="col">
        <button class="btn btn-info btn-block font-weight-bold py-1.5 text-white" onclick="holdCurrentOrder()" id="btnHoldOrder" style="border-radius: 8px; font-size: 0.88rem;">
          <i class="fas fa-pause-circle mr-1"></i> ພັກບິນ
        </button>
      </div>
      <div class="col">
        <button class="btn btn-primary btn-block font-weight-bold py-1.5 text-white" onclick="openHeldOrdersModal()" id="btnHeldOrders" style="border-radius: 8px; font-size: 0.88rem;">
          <i class="fas fa-history mr-1"></i> ບິນທີ່ພັກ <span class="badge badge-light badge-hold-count text-dark font-weight-bold" id="heldCountBadge">0</span>
        </button>
      </div>
    </div>

    <div class="row no-gutters" style="gap: 6px;">
      <div class="col-4">
        <button class="btn btn-danger btn-block font-weight-bold py-2.5" onclick="clearCart()" id="btnClearCart" style="border-radius: 8px;">
          <i class="fas fa-trash-alt mr-1"></i> ລ້າງ
        </button>
      </div>
      <div class="col">
        <button class="btn btn-warning btn-block font-weight-bold py-2.5 text-dark shadow-sm" onclick="openCheckoutModal()" id="btnCheckout" style="border-radius: 8px;">
          <i class="fas fa-money-bill-wave mr-1"></i> ຊຳລະເງິນ
        </button>
      </div>
    </div>
  </div>
</div>
