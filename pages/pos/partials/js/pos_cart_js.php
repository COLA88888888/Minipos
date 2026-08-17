<script>
// --- DUAL-SCREEN POS CUSTOMER DISPLAY BROADCAST ---
var customerDisplayChannel = null;
try {
  customerDisplayChannel = new BroadcastChannel('pos_customer_display_channel');
} catch(e) {}

function broadcastCustomerDisplay(checkoutState, extraData) {
  var payload = {
    cart: (typeof cart !== 'undefined') ? cart : [],
    customer: (typeof selectedCustomer !== 'undefined') ? selectedCustomer : null,
    discount: $('#cartDiscount').val() || '0',
    checkoutState: checkoutState || 'shopping',
    ...(extraData || {})
  };

  localStorage.setItem('pos_customer_display_data', JSON.stringify(payload));
  if (customerDisplayChannel) {
    try {
      customerDisplayChannel.postMessage(payload);
    } catch(e) {}
  }
}

function openCustomerDisplayWindow() {
  window.open('customer_display.php', 'POSCustomerDisplay', 'width=1200,height=800,scrollbars=yes,resizable=yes');
  broadcastCustomerDisplay('shopping');
}

// --- CART & ITEM MANAGEMENT ---
function updateCartUI() {
  var container = $('#cartItemsContainer');
  container.empty();

  if (cart.length === 0) {
    container.html(`
      <div class="cart-empty-state d-flex flex-column align-items-center justify-content-center h-100 py-5">
        <i class="fas fa-shopping-basket text-muted mb-2" style="font-size: 2.2rem; opacity: 0.3;"></i>
        <div class="text-muted font-weight-bold" style="font-size: 0.92rem;">ບໍ່ມີລາຍການສິນຄ້າໃນກະຕ່າ</div>
        <small class="text-muted">ກະລຸນາເລືອກສິນຄ້າ ຫຼື ສະແກນບາໂຄ້ດ</small>
      </div>
    `);
    $('#cartItemCountBadge').text('0 ລາຍການ (0 ຈຳນວນ)');
    $('#cartSubtotal').text('0 ₭');
    $('#cartVat').text('0 ₭');
    $('#cartTotal').text('0 ₭');
    $('#mobileCartCountBadge').text('0').hide();
    $('#floatingCartItemCount').text('0 ລາຍການ (0 ຈຳນວນ)');
    $('#floatingCartTotal').html('0 ₭ <i class="fas fa-chevron-right ml-1"></i>');
    $('#mobileFloatingCartBar').hide();
    $('#btnCheckout').prop('disabled', true).addClass('disabled');
    $('#btnClearCart').prop('disabled', true).addClass('disabled');
    $('#btnHoldOrder, #btnHoldCart').prop('disabled', true).addClass('disabled');
    updateProductGridBadges();
    saveCurrentBillState();
    broadcastCustomerDisplay('shopping');
    return;
  }

  var subtotal = 0;
  var totalItemsCount = 0;

  cart.forEach(function(item, idx) {
    var itemTotal = item.quantity * item.unit_price;
    subtotal += itemTotal;
    totalItemsCount += item.quantity;

    var rawImg = item.image || item.img_url || '';
    var imgName = rawImg ? rawImg.split('/').pop().split('\\').pop() : 'image.jpg';
    if (!imgName || imgName === 'image.jpg') {
      var imgPath = '<?php echo $base_path; ?>assets/img/image.jpg';
    } else {
      var imgPath = '<?php echo $base_path; ?>assets/product_img/' + imgName;
    }

    var isGift = !!item.is_free_gift;
    var giftBadge = isGift ? `<span class="badge badge-success font-weight-bold ml-1" style="font-size:0.68rem; padding:2px 6px; border-radius:4px;"><i class="fas fa-gift mr-1"></i>ແຖມຟຣີ</span>` : '';
    var priceDisplay = isGift 
      ? `<span class="text-success font-weight-bold" style="font-size:0.80rem;">0 ₭</span>` 
      : `${Number(item.unit_price).toLocaleString()} ₭`;

    var rowHtml = `
      <div class="cart-item-row mb-2 d-flex align-items-center" style="background:${isGift ? '#f0fdf4' : '#ffffff'}; border:1.5px solid ${isGift ? '#86efac' : '#e2e8f0'}; border-radius:12px; padding:8px 10px; gap:8px; min-height:54px;">

        <!-- 1. ຮູບພາບ -->
        <img src="${imgPath}" class="cart-item-img" style="width:44px; height:44px; object-fit:contain; border-radius:8px; border:1px solid #e2e8f0; background:#f8fafc; padding:3px; flex-shrink:0;" onerror="this.src='<?php echo $base_path; ?>assets/img/image.jpg';">

        <!-- 2. ຊື່ສິນຄ້າ + ຫົວໜ່ວຍ -->
        <div class="cart-item-name-wrap" style="flex:1.4; min-width:0; display:flex; flex-direction:column; justify-content:center; gap:2px;">
          <div class="font-weight-bold text-truncate" style="font-size:0.86rem; color:#1e293b; line-height:1.2;" title="${item.product_name}">${item.product_name} ${giftBadge}</div>
          <span class="cart-item-unit-badge d-none d-lg-inline-block" style="font-size:0.70rem; font-weight:700; color:#2563eb; background:#eff6ff; border:1px solid #bfdbfe; border-radius:5px; padding:1px 7px; width:fit-content; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${item.unit_name}</span>
          <span class="cart-item-unit-badge-mobile d-inline-block d-lg-none" style="font-size:0.68rem; font-weight:700; color:#2563eb; background:#eff6ff; border:1px solid #bfdbfe; border-radius:5px; padding:1px 5px; width:fit-content; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${item.unit_name} (${priceDisplay})</span>
        </div>

        <!-- 3. ລາຄາຕໍ່ໜ່ວຍ (Desktop Only) -->
        <div class="cart-item-unit-price d-none d-lg-block" style="flex-shrink:0; text-align:center; min-width:60px;">
          <div style="font-size:0.84rem; font-weight:700; color:${isGift ? '#16a34a' : '#475569'};">${priceDisplay}</div>
        </div>

        <!-- 4. ຈຳນວນ -->
        <div class="cart-item-qty-wrap" style="flex-shrink:0; display:flex; align-items:center; border:1.5px solid #e2e8f0; border-radius:8px; overflow:hidden; background:#f8fafc;">
          <button onclick="incCartQty('${item.cartKey}', -1)" title="ຫຼຸດ" class="btn-qty-minus" style="width:28px; height:30px; border:none; background:transparent; color:#ef4444; font-size:0.78rem; cursor:pointer; line-height:1; display:flex; align-items:center; justify-content:center; padding:0;">
            <i class="fas fa-minus"></i>
          </button>
          <input type="number" min="1" value="${item.quantity}" onchange="changeCartQty('${item.cartKey}', this.value)" class="input-qty-val" style="width:34px; height:30px; border:none; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0; text-align:center; font-size:0.86rem; font-weight:700; color:#1e293b; background:#ffffff; padding:0; -moz-appearance:textfield;" ${isGift ? 'readonly' : ''}>
          <button onclick="incCartQty('${item.cartKey}', 1)" title="ເພີ່ມ" class="btn-qty-plus" style="width:28px; height:30px; border:none; background:transparent; color:#2563eb; font-size:0.78rem; cursor:pointer; line-height:1; display:flex; align-items:center; justify-content:center; padding:0;" ${isGift ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''}>
            <i class="fas fa-plus"></i>
          </button>
        </div>

        <!-- 5. ລວມ -->
        <div class="cart-item-total-wrap" style="flex-shrink:0; text-align:right; min-width:65px;">
          <div style="font-size:0.92rem; font-weight:800; color:${isGift ? '#16a34a' : '#16a34a'}; white-space:nowrap;">${isGift ? '0 ₭' : itemTotal.toLocaleString() + ' ₭'}</div>
        </div>

        <!-- 6. ປຸ່ມລົບ -->
        <button class="cart-item-del-btn" onclick="removeFromCart('${item.cartKey}')" title="ລຶບ" style="flex-shrink:0; width:28px; height:28px; border:none; background:transparent; color:#ef4444; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0;">
          <i class="fas fa-trash-alt" style="font-size:0.90rem;"></i>
        </button>

      </div>
    `;
    container.append(rowHtml);
  });

  <?php
    $storeTaxType = $company['tax_type'] ?? 'inclusive';
    $storeVatPercent = floatval($company['vat_percent'] ?? 7.00);
  ?>
  var discountStr  = $('#cartDiscount').val() || '0';
  var discountVal  = parseFloat(discountStr.replace(/\D/g, '')) || 0;
  var taxType      = '<?php echo $storeTaxType; ?>';
  var vatPercent   = <?php echo $storeVatPercent; ?>;
  var amtAfterDisc = Math.max(0, subtotal - discountVal);
  var vatVal       = 0;
  var netTotal     = amtAfterDisc;

  if (taxType === 'exclusive' && vatPercent > 0) {
    vatVal = Math.round(amtAfterDisc * (vatPercent / 100));
    netTotal = amtAfterDisc + vatVal;
    $('#cartVatRow').attr('style', 'display: flex !important;');
    $('#cartVatLabel').text('ພາສີ VAT (' + vatPercent + '%):');
    $('#cartVat').text('+' + vatVal.toLocaleString() + ' ₭');
  } else if (taxType === 'inclusive' && vatPercent > 0) {
    vatVal = Math.round(amtAfterDisc - (amtAfterDisc / (1 + (vatPercent / 100))));
    netTotal = amtAfterDisc;
    $('#cartVatRow').attr('style', 'display: flex !important;');
    $('#cartVatLabel').text('ລວມ ພາສີ VAT (' + vatPercent + '%):');
    $('#cartVat').text(vatVal.toLocaleString() + ' ₭');
  } else {
    vatVal = 0;
    netTotal = amtAfterDisc;
    $('#cartVatRow').attr('style', 'display: none !important;');
  }

  var lineCount = cart.length;
  var countText = lineCount + ' ລາຍການ (' + totalItemsCount + ' ຈຳນວນ)';

  $('#cartItemCountBadge').text(countText);
  $('#cartSubtotal').text(subtotal.toLocaleString() + ' ₭');
  $('#cartTotal').text(netTotal.toLocaleString() + ' ₭');

  if (totalItemsCount > 0) {
    $('#mobileCartCountBadge').text(lineCount).css('display', 'inline-block');
    $('#floatingCartItemCount').text(countText);
    $('#floatingCartTotal').html(netTotal.toLocaleString() + ' ₭ <i class="fas fa-chevron-right ml-1"></i>');
    if (window.innerWidth < 992 && $('#mobileTabProductsBtn').hasClass('active')) {
      $('#mobileFloatingCartBar').fadeIn(150);
    }
  } else {
    $('#mobileCartCountBadge').text('0').hide();
    $('#mobileFloatingCartBar').hide();
  }

  $('#btnCheckout').prop('disabled', false).removeClass('disabled');
  $('#btnClearCart').prop('disabled', false).removeClass('disabled');
  $('#btnHoldOrder, #btnHoldCart').prop('disabled', false).removeClass('disabled');

  updateProductGridBadges();
  saveCurrentBillState();
  broadcastCustomerDisplay('shopping');
}

// --- STOCK CHECK HELPERS & ALERTS ---
function getProductCutQty(productId) {
  var stockValEl = $('.product-stock-val-' + productId);
  if (stockValEl.length) {
    return parseInt(stockValEl.attr('data-cut-qty') ?? '1');
  }
  // fallback from allProducts
  var p = (typeof allProducts !== 'undefined') ? allProducts.find(function(item) { return String(item.product_id) === String(productId); }) : null;
  return p ? parseInt(p.cut_qty ?? 1) : 1;
}

function getProductRemainingStock(productId) {
  // ຖ້າ cut_qty = 0 = ບໍ່ຕັດ qty, ຊົ່ນ Infinity
  if (getProductCutQty(productId) === 0) return Infinity;

  var p = (typeof allProducts !== 'undefined') ? allProducts.find(function(item) { return String(item.product_id) === String(productId); }) : null;
  var initialStock = 0;

  var stockValEl = $('.product-stock-val-' + productId);
  if (stockValEl.length) {
    initialStock = parseFloat(stockValEl.attr('data-initial-stock')) || 0;
  } else if (p) {
    initialStock = parseFloat(p.qty) || 0;
  }

  var inCartDeduct = 0;
  if (cart && cart.length > 0) {
    cart.forEach(function(item) {
      if (String(item.product_id) === String(productId)) {
        var qty = parseFloat(item.quantity) || 0;
        var multiplier = parseInt(item.multiplier) || 1;
        inCartDeduct += (qty * multiplier);
      }
    });
  }

  return initialStock - inCartDeduct;
}

function showLowStockToast(productName, remainingQty, unitName) {
  Swal.fire({
    icon: 'warning',
    title: 'ເຕືອນ: ສິນຄ້າໃກ້ຈະໝົດ!',
    text: 'ສິນຄ້າ "' + productName + '" ໃກ້ຈະໝົດແລ້ວ! (ເຫຼືອພຽງ ' + remainingQty.toLocaleString() + ' ' + (unitName || 'ອັນ') + ')',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
  });
}

function updateProductGridBadges() {
  // 1. Reset all cart badges
  $('.cart-qty-badge').hide().text('0');

  // 2. Map cart quantities and stock deductions
  var productCartQtyMap = {};
  var productStockDeductMap = {};

  if (cart && cart.length > 0) {
    cart.forEach(function(item) {
      var pid = item.product_id;
      var qty = parseFloat(item.quantity) || 0;
      var multiplier = parseInt(item.multiplier) || 1;
      var baseDeduct = qty * multiplier;

      productCartQtyMap[pid] = (productCartQtyMap[pid] || 0) + qty;
      productStockDeductMap[pid] = (productStockDeductMap[pid] || 0) + baseDeduct;
    });
  }

  // 3. Update every product card stock & badges dynamically
  $('.product-stock-val').each(function() {
    var stockValEl = $(this);
    var pidStr = stockValEl.attr('class').match(/product-stock-val-(\d+)/);
    if (!pidStr) return;
    var pid = pidStr[1];

    var initialStock = parseFloat(stockValEl.attr('data-initial-stock')) || 0;
    var totalStockDeducted = productStockDeductMap[pid] || 0;
    var remainingStock = Math.max(0, initialStock - totalStockDeducted);

    stockValEl.text(remainingStock.toLocaleString());

    var totalQtyInCart = productCartQtyMap[pid] || 0;
    var badge = $('.product-qty-badge-' + pid);
    if (badge.length) {
      if (totalQtyInCart > 0) {
        badge.text(totalQtyInCart).css('display', 'block').show();
      } else {
        badge.hide().text('0');
      }
    }

    var cardEl = $('.product-card-' + pid);
    var statusBadge = $('.stock-status-badge-' + pid);
    var infoWrap = $('.stock-info-wrap-' + pid);

    if (remainingStock <= 0) {
      if (statusBadge.length) {
        statusBadge.text('ໝົດແລ້ວ').removeClass('low-stock').addClass('out-of-stock').css('display', 'inline-block').show();
      }
      if (cardEl.length) {
        cardEl.css({'border': '2px solid #ef4444', 'opacity': '0.82', 'background': '#fff5f5'});
      }
      if (infoWrap.length) {
        infoWrap.removeClass('in-stock low-stock').addClass('out-of-stock');
      }
    } else if (remainingStock <= 10) {
      if (statusBadge.length) {
        statusBadge.text('ໃກ້ໝົດ').removeClass('out-of-stock').addClass('low-stock').css('display', 'inline-block').show();
      }
      if (cardEl.length) {
        cardEl.css({'border': '2px solid #f59e0b', 'opacity': '1.0', 'background': '#ffffff'});
      }
      if (infoWrap.length) {
        infoWrap.removeClass('in-stock out-of-stock').addClass('low-stock');
      }
    } else {
      if (statusBadge.length) {
        statusBadge.hide().removeClass('out-of-stock low-stock');
      }
      if (cardEl.length) {
        cardEl.css({'border': '1.5px solid #3b82f6', 'opacity': '1.0', 'background': '#ffffff'});
      }
      if (infoWrap.length) {
        infoWrap.removeClass('out-of-stock low-stock').addClass('in-stock');
      }
    }
  });
}

function incCartQty(cartKey, delta) {
  var item = cart.find(function(i) { return i.cartKey === cartKey; });
  if (item) {
    if (delta > 0) {
      var remaining = getProductRemainingStock(item.product_id);
      var needed = delta * (item.multiplier || 1);
      if (remaining < needed) {
        Swal.fire({
          icon: 'error',
          title: 'ຈຳນວນສິນຄ້າບໍ່ພໍ!',
          html: '<div style="font-size:1.0rem; font-weight:600; color:#ef4444;">ສິນຄ້າ "' + item.product_name + '" ບໍ່ສາມາດເພີ່ມຈຳນວນໄດ້ອີກ</div><div class="mt-2 text-muted" style="font-size:0.88rem;">ຈຳນວນເຫຼືອໃນສາງ: <b>' + Math.max(0, remaining).toLocaleString() + '</b> ' + item.unit_name + '</div>',
          confirmButtonText: 'ຕົກລົງ',
          confirmButtonColor: '#ef4444'
        });
        return;
      }
      var newRemaining = remaining - needed;
      if (newRemaining <= 10 && newRemaining >= 0) {
        showLowStockToast(item.product_name, newRemaining, item.unit_name);
      }
    }
    item.quantity += delta;
    if (item.quantity <= 0) {
      removeFromCart(cartKey);
    } else {
      updateCartUI();
    }
  }
}

function changeCartQty(cartKey, val) {
  var q = parseInt(val) || 1;
  if (q <= 0) q = 1;
  var item = cart.find(function(i) { return i.cartKey === cartKey; });
  if (item) {
    var oldQty = item.quantity;
    var diffQty = q - oldQty;
    if (diffQty > 0) {
      var remaining = getProductRemainingStock(item.product_id);
      var needed = diffQty * (item.multiplier || 1);
      if (remaining < needed) {
        var maxAdd = Math.floor(remaining / (item.multiplier || 1));
        var allowedQty = oldQty + maxAdd;
        item.quantity = allowedQty > 0 ? allowedQty : 1;
        Swal.fire({
          icon: 'error',
          title: 'ຈຳນວນສິນຄ້າບໍ່ພໍ!',
          html: '<div style="font-size:1.0rem; font-weight:600; color:#ef4444;">ສິນຄ້າ "' + item.product_name + '" ເຫຼືອໃນສາງບໍ່ພໍ</div><div class="mt-2 text-muted" style="font-size:0.88rem;">ສາມາດຂາຍໄດ້ສູງສຸດ: <b>' + (oldQty + maxAdd).toLocaleString() + '</b> ' + item.unit_name + '</div>',
          confirmButtonText: 'ຕົກລົງ',
          confirmButtonColor: '#ef4444'
        });
        updateCartUI();
        return;
      }
      var newRemaining = remaining - needed;
      if (newRemaining <= 10 && newRemaining >= 0) {
        showLowStockToast(item.product_name, newRemaining, item.unit_name);
      }
    }
    item.quantity = q;
    updateCartUI();
  }
}

function removeFromCart(cartKey) {
  var itemToRemove = cart.find(function(i) { return i.cartKey === cartKey; });
  if (itemToRemove && itemToRemove.product_id) {
    var pid = itemToRemove.product_id;
    cart = cart.filter(function(i) { 
      return i.cartKey !== cartKey && i.parent_product_id !== pid; 
    });
  } else {
    cart = cart.filter(function(i) { return i.cartKey !== cartKey; });
  }
  updateCartUI();
}

function clearCart() {
  if (cart.length === 0) {
    Swal.fire({
      icon: 'info',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະຕ່າສິນຄ້າຫວ່າງເປົ່າຢູ່ແລ້ວ!',
      confirmButtonColor: '#2563eb'
    });
    return;
  }
  Swal.fire({
    title: 'ຢືນຢັນລ້າງກະຕ່າ?',
    text: 'ລາຍການສິນຄ້າທັງໝົດໃນກະຕ່າຈະຖືກລຶບອອກ!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'ລ້າງກະຕ່າ',
    cancelButtonText: 'ຍົກເລີກ'
  }).then(function(result) {
    if (result.isConfirmed) {
      cart = [];
      updateCartUI();
    }
  });
}

function buildAvailableUnits(product) {
  var units = [];
  var baseUnitName = product.unit ? product.unit : 'ອັນ';
  var basePrice = parseFloat(product.price) || 0;
  var baseBprice = parseFloat(product.bprice) || 0;

  units.push({
    unit_name: baseUnitName,
    multiplier: 1,
    price: basePrice,
    bprice: baseBprice
  });

  if (product.extra_units && product.extra_units.length > 0) {
    product.extra_units.forEach(function(eu) {
      units.push({
        unit_name: eu.unit_name,
        multiplier: parseInt(eu.multiplier) || 1,
        price: parseFloat(eu.price) || 0,
        bprice: parseFloat(eu.bprice) || 0
      });
    });
  }

  return units;
}

function _doAddToCart(product, unitObj) {
  if (!isBillOpened || activeBills.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'ກະລຸນາເປີດບິນກ່ອນ!',
      html: '<div style="font-size:1.0rem; font-weight:600; color:#d97706;">ທ່ານຍັງບໍ່ທັນໄດ້ເປີດບິນການຂາຍ!</div><div class="mt-2 text-muted" style="font-size:0.88rem;">ກະລຸນາກົດ <b>"+ ເປີດບິນໃໝ່"</b> ເພື່ອເລີ່ມຕົ້ນການຂາຍ.</div>',
      confirmButtonText: '<i class="fas fa-plus-circle mr-1"></i> ເປີດບິນໃໝ່',
      confirmButtonColor: '#16a34a',
      showCancelButton: true,
      cancelButtonText: 'ຍົກເລີກ',
      cancelButtonColor: '#64748b'
    }).then(function(result) {
      if (result.isConfirmed) {
        if (typeof createNewBillModal === 'function') {
          createNewBillModal();
        }
      }
    });
    return false;
  }

  var unitName = unitObj ? unitObj.unit_name : (product.unit || 'ອັນ');
  var unitPrice = unitObj ? parseFloat(unitObj.price) : parseFloat(product.price);
  var costPrice = unitObj ? parseFloat(unitObj.bprice) : parseFloat(product.bprice);
  var multiplier = unitObj ? (parseInt(unitObj.multiplier) || 1) : 1;

  var currentRemaining = getProductRemainingStock(product.product_id);
  var cutQty = getProductCutQty(product.product_id);

  // 1. ຖ້າສິນຄ້າເປັນ 0 (ຫຼື ໝົດແລ້ວ) -> ບໍ່ສາມາດຂາຍໄດ້, ສະແດງ ປັອບອັບ ເເຈ້ງເຕືອນ
  if (cutQty !== 0 && (currentRemaining <= 0 || parseFloat(product.qty || 0) <= 0)) {
    Swal.fire({
      icon: 'error',
      title: 'ສິນຄ້າໝົດແລ້ວ!',
      html: '<div style="font-size:1.05rem; font-weight:700; color:#ef4444; margin-bottom:6px;">ສິນຄ້າ "' + product.product_name + '"</div>' +
            '<div class="text-dark font-weight-bold" style="font-size:0.92rem;">ບໍ່ສາມາດກົດຂາຍໄດ້ ເນື່ອງຈາກຈຳນວນສິນຄ້າໃນຄັງເປັນ 0</div>' +
            '<small class="text-muted d-block mt-2">ກະລຸນາເພີ່ມສະຕັອກ ຫຼື ເລືອກສິນຄ້າອື່ນ</small>',
      confirmButtonText: '<i class="fas fa-check mr-1"></i> ຕົກລົງ',
      confirmButtonColor: '#ef4444',
      customClass: {
        confirmButton: 'btn btn-danger font-weight-bold px-4'
      }
    });
    return false;
  }

  // 2. ຖ້າຈຳນວນເຫຼືອໜ້ອຍກວ່າຫົວໜ່ວຍທີ່ເລືອກ
  if (currentRemaining < multiplier) {
    Swal.fire({
      icon: 'warning',
      title: 'ຈຳນວນສິນຄ້າບໍ່ພໍ!',
      html: '<div style="font-size:1.0rem; font-weight:700; color:#d97706;">ສິນຄ້າ "' + product.product_name + '" ເຫຼືອພຽງ ' + currentRemaining.toLocaleString() + ' ' + (product.unit || 'ອັນ') + '</div><div class="mt-2 text-dark" style="font-size:0.88rem;">ບໍ່ສາມາດຂາຍເກີນຈຳນວນທີ່ມີໃນສາງໄດ້.</div>',
      confirmButtonText: 'ຕົກລົງ',
      confirmButtonColor: '#f59e0b'
    });
    return false;
  }

  var cartKey = product.product_id + '_' + unitName;
  var existing = cart.find(function(i) { return i.cartKey === cartKey; });

  if (existing) {
    existing.quantity += 1;
  } else {
    cart.push({
      cartKey: cartKey,
      product_id: product.product_id,
      product_name: product.product_name,
      unit_name: unitName,
      unit_price: unitPrice,
      cost_price: costPrice,
      multiplier: multiplier,
      quantity: 1,
      image: product.img_url || product.image || ''
    });
  }

  // Handle Free Gift Auto-Adding into Cart
  var giftName = '';
  var giftQty = 1;
  var targetUnit = (product && product.target_unit_name) ? trimStr(product.target_unit_name) : 'all';
  
  function trimStr(str) {
    return String(str || '').trim().toLowerCase();
  }

  function checkUnitMatch(u1, u2) {
    var s1 = trimStr(u1);
    var s2 = trimStr(u2);
    if (s2 === 'all' || s2 === '' || s1 === 'all' || s1 === '') return true;
    if (s1 === s2) return true;
    // Normalize Lao spelling variations for crates/cartons (ເຊັ່ນ: ເກັດ vs ເເກັດ, ເເກັດ vs ເເກັດ)
    var norm1 = s1.replace(/ເເກັດ|ເກັດ|ແກັດ|ແກັດ/g, 'ເກັດ');
    var norm2 = s2.replace(/ເເກັດ|ເກັດ|ແກັດ|ແກັດ/g, 'ເກັດ');
    if (norm1 === norm2) return true;
    return s1.indexOf(s2) !== -1 || s2.indexOf(s1) !== -1;
  }

  var unitMatches = checkUnitMatch(unitName, targetUnit);

  if (unitMatches) {
    if (product && product.gift_product_name && product.gift_product_name !== '') {
      giftName = product.gift_product_name;
      giftQty = parseInt(product.gift_qty) || 1;
    } else if (product && product.promo_type === 'buy_x_get_y') {
      giftName = 'ສິນຄ້າແຖມ';
      giftQty = parseInt(product.gift_qty) || 1;
    }
  }

  if (giftName !== '' && unitMatches) {
    var giftCartKey = 'GIFT_' + product.product_id + '_' + giftName;
    var existingGift = cart.find(function(i) { return i.cartKey === giftCartKey; });
    
    if (existingGift) {
      existingGift.quantity += giftQty;
    } else {
      cart.push({
        cartKey: giftCartKey,
        product_id: 'GIFT_' + product.product_id,
        product_name: '🎁 ' + giftName + ' (ແຖມ' + (targetUnit !== 'all' ? ' ສະເພາະ ' + targetUnit : '') + ')',
        unit_name: 'ຊິ້ນ',
        unit_price: 0,
        cost_price: 0,
        multiplier: 1,
        quantity: giftQty,
        image: 'image.jpg',
        is_free_gift: true,
        parent_product_id: product.product_id
      });
    }
  }

  var newRemaining = currentRemaining - multiplier;

  // 3. ຖ້າເຫຼືອ 10 ລາຍການ (ຫຼື ຫຼຸດລົງ <= 10) -> ແຈ້ງເຕືອນວ່າສິນຄ້າໃກ້ຈະໝົດແລ້ວ (ເທົ່ານັ້ນ cut_qty = 1)
  if (getProductCutQty(product.product_id) === 1 && newRemaining <= 10 && newRemaining >= 0) {
    showLowStockToast(product.product_name, newRemaining, product.unit || 'ອັນ');
  } else {
    // Toast alert feedback on success
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 1200,
      timerProgressBar: false
    });
    Toast.fire({
      icon: 'success',
      title: 'ເພີ່ມ "' + product.product_name + '" ລົງກະຕ່າແລ້ວ'
    });
  }

  updateCartUI();
  return true;
}

// --- MOBILE TAB SWITCHER ---
function switchMobilePosTab(tab) {
  if (window.innerWidth >= 992) return;

  if (tab === 'products') {
    $('.pos-products').removeClass('mobile-hidden').addClass('mobile-active').show();
    $('.pos-cart').removeClass('mobile-active').addClass('mobile-hidden').hide();
    $('#mobileTabProductsBtn').addClass('btn-primary active').removeClass('btn-outline-primary');
    $('#mobileTabCartBtn').removeClass('btn-primary active').addClass('btn-outline-primary');
    
    if (typeof cart !== 'undefined' && cart.length > 0) {
      $('#mobileFloatingCartBar').fadeIn(150);
    } else {
      $('#mobileFloatingCartBar').hide();
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } else if (tab === 'cart') {
    $('.pos-products').removeClass('mobile-active').addClass('mobile-hidden').hide();
    $('.pos-cart').removeClass('mobile-hidden').addClass('mobile-active').show();
    $('#mobileTabProductsBtn').removeClass('btn-primary active').addClass('btn-outline-primary');
    $('#mobileTabCartBtn').addClass('btn-primary active').removeClass('btn-outline-primary');
    $('#mobileFloatingCartBar').hide();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}
</script>
