<script>
// ==========================================
// REAL-TIME CROSS-WINDOW STOCK BROADCAST SYNC
// ==========================================
var posStockChannel = (typeof BroadcastChannel !== 'undefined') ? new BroadcastChannel('pos_stock_channel') : null;

function broadcastStockUpdate(updatedStocks) {
  if (!updatedStocks || !updatedStocks.length) return;
  try {
    if (posStockChannel) {
      posStockChannel.postMessage({ type: 'stock_updated', updated_stocks: updatedStocks });
    }
  } catch(e) {}
  try {
    localStorage.setItem('pos_live_stock_update', JSON.stringify({ timestamp: Date.now(), updated_stocks: updatedStocks }));
  } catch(e) {}
}

function applyStockUpdateFromBroadcast(updatedStocks) {
  if (!updatedStocks || !updatedStocks.length) return;

  var localStocks = {};
  try {
    localStocks = JSON.parse(localStorage.getItem('pos_local_stocks') || '{}');
  } catch(e) {}

  updatedStocks.forEach(function(st) {
    var pid = st.product_id;
    var newQty = parseFloat(st.new_qty);

    // Persist to localStocks
    localStocks[pid] = newQty;

    var stockValEl = $('.product-stock-val-' + pid);
    if (stockValEl.length) {
      stockValEl.attr('data-initial-stock', newQty);
      stockValEl.data('initial-stock', newQty);
      stockValEl.text(newQty.toLocaleString());
    }

    if (typeof allProducts !== 'undefined' && allProducts && allProducts.length > 0) {
      var pItem = allProducts.find(function(item) { return String(item.product_id) === String(pid); });
      if (pItem) {
        pItem.qty = newQty;
      }
    }
  });

  try {
    localStorage.setItem('pos_local_stocks', JSON.stringify(localStocks));
  } catch(e) {}

  if (typeof recalculateLiveStock === 'function') {
    recalculateLiveStock();
  }
}

if (posStockChannel) {
  posStockChannel.onmessage = function(ev) {
    if (ev && ev.data && ev.data.type === 'stock_updated') {
      applyStockUpdateFromBroadcast(ev.data.updated_stocks);
    }
  };
}

window.addEventListener('storage', function(e) {
  if (e.key === 'pos_live_stock_update' && e.newValue) {
    try {
      var data = JSON.parse(e.newValue);
      if (data && data.updated_stocks) {
        applyStockUpdateFromBroadcast(data.updated_stocks);
      }
    } catch(err) {}
  }
});

// ============================
// PAYMENT MODE & TYPE STATE
// ============================
var currentPayMode = 'single'; // 'single' | 'split'

// ============================
// NUMPAD
// ============================
function getActiveAmountInput() {
  if (currentPayMode === 'split') {
    // Focus on whichever split input was last focused
    if (document.activeElement && document.activeElement.id === 'splitQrAmount') return $('#splitQrAmount');
    return $('#splitCashAmount');
  }
  return $('#cashReceived');
}

function numpadPress(digit) {
  var inp = getActiveAmountInput();
  var cur = inp.val().replace(/[,\s]/g, '').replace(/[^0-9]/g, '');
  if (cur === '0' || cur === '') cur = '';
  cur += digit;
  var num = parseInt(cur, 10) || 0;
  inp.val(num.toLocaleString('en-US'));
  if (currentPayMode === 'split') calcSplitRemaining();
  else calculateChange();
}

function numpadBackspace() {
  var inp = getActiveAmountInput();
  var cur = inp.val().replace(/[,\s]/g, '').replace(/[^0-9]/g, '');
  cur = cur.slice(0, -1) || '0';
  var num = parseInt(cur, 10) || 0;
  inp.val(num === 0 ? '0' : num.toLocaleString('en-US'));
  if (currentPayMode === 'split') calcSplitRemaining();
  else calculateChange();
}

function setPayMode(mode) {
  currentPayMode = mode;
  if (mode === 'single') {
    $('#payModeSingleBtn').css({ 'border-color': '#2563eb', 'background': '#eff6ff', 'color': '#1d4ed8' });
    $('#payModeSplitBtn').css({ 'border-color': '#cbd5e1', 'background': '#ffffff', 'color': '#64748b' });
    $('#singlePaySection').show();
    $('#splitPaySection').hide();
    $('#payTypeRow').show();
    $('#singleSummary').show();
    $('#splitSummary').hide();
    if (selectedPayType === 'ເງິນສົດ') {
      $('#singleQrSection').hide();
    } else {
      $('#singleQrSection').show();
    }
    calculateChange();
  } else {
    $('#payModeSplitBtn').css({ 'border-color': '#7c3aed', 'background': '#faf5ff', 'color': '#7c3aed' });
    $('#payModeSingleBtn').css({ 'border-color': '#cbd5e1', 'background': '#ffffff', 'color': '#64748b' });
    $('#singlePaySection').hide();
    $('#splitPaySection').show();
    $('#payTypeRow').hide();
    $('#singleSummary').hide();
    $('#splitSummary').show();
    $('#singleQrSection').show();
    var totalVal = parseFloat($('#cartTotal').text().replace(/[^\d]/g, '')) || 0;
    $('#splitTotal').text(totalVal.toLocaleString() + ' ₭');
    calcSplitRemaining();
    $('#btnSubmitPayment').prop('disabled', true);
  }
}

// ============================
// SINGLE PAYMENT FUNCTIONS
// ============================
function selectPayTypeTab(type) {
  selectedPayType = type;
  if (type === 'ເງິນສົດ') {
    $('#payTypeCashBtn').css({ 'border-color': '#2563eb', 'background': '#eff6ff', 'color': '#1d4ed8' });
    $('#payTypeQrBtn').css({ 'border-color': '#cbd5e1', 'background': '#ffffff', 'color': '#64748b' });
    $('#singleQrSection').slideUp(150);
  } else {
    $('#payTypeQrBtn').css({ 'border-color': '#7c3aed', 'background': '#faf5ff', 'color': '#7c3aed' });
    $('#payTypeCashBtn').css({ 'border-color': '#cbd5e1', 'background': '#ffffff', 'color': '#64748b' });
    $('#singleQrSection').slideDown(150);

    // Auto trigger selected/first bank option to load bank QR
    var activeBankOpt = $('.pos-bank-option.selected-bank');
    if (!activeBankOpt.length) {
      activeBankOpt = $('.pos-bank-option').first();
    }
    if (activeBankOpt.length) {
      activeBankOpt.click();
    }
  }
}

function setPaymentType(type) { selectPayTypeTab(type); }

function setExactAmount() {
  var totalVal = parseFloat($('#cartTotal').text().replace(/[^\d]/g, '')) || 0;
  $('#cashReceived').val(totalVal.toLocaleString('en-US'));
  calculateChange();
}

function addCashShortcut(amount) {
  var cur = parseFloat($('#cashReceived').val().replace(/[^\d]/g, '')) || 0;
  $('#cashReceived').val((cur + amount).toLocaleString('en-US'));
  calculateChange();
}

function calculateChange() {
  if (currentPayMode !== 'single') return;
  var cash  = parseFloat($('#cashReceived').val().replace(/[^\d]/g, '')) || 0;
  var total = parseFloat($('#cartTotal').text().replace(/[^\d]/g, '')) || 0;
  var change = cash - total;

  // update summary labels
  $('#singleTotalLbl').text(total.toLocaleString() + ' ₭');
  $('#singleCashLbl').text(cash.toLocaleString() + ' ₭');

  if (change < 0) {
    $('#changeDisplay').text('ຂາດ ' + Math.abs(change).toLocaleString() + ' ₭').removeClass('text-success').addClass('text-danger').css('color','#ef4444');
    $('#btnSubmitPayment').prop('disabled', true);
  } else {
    $('#changeDisplay').text(change.toLocaleString() + ' ₭').removeClass('text-danger').addClass('text-success').css('color','#16a34a');
    $('#btnSubmitPayment').prop('disabled', false);
  }
}

// ============================
// SPLIT PAYMENT FUNCTIONS
// ============================
function calcSplitRemaining() {
  var total   = parseFloat($('#cartTotal').text().replace(/[^\d]/g, '')) || 0;
  var cash    = parseFloat($('#splitCashAmount').val().replace(/[^\d]/g, '')) || 0;
  var qr      = parseFloat($('#splitQrAmount').val().replace(/[^\d]/g, '')) || 0;
  var paid    = cash + qr;
  var remain  = total - paid;

  $('#splitTotal').text(total.toLocaleString() + ' ₭');
  $('#splitCashDisplay').text(cash.toLocaleString() + ' ₭');
  $('#splitQrDisplay').text(qr.toLocaleString() + ' ₭');

  if (remain > 0) {
    $('#splitStatusLabel').text('ຍັງຄ້າງ:').css('color', '#ef4444');
    $('#splitRemainingDisplay').text(remain.toLocaleString() + ' ₭').css('color', '#ef4444');
    $('#btnSubmitPayment').prop('disabled', true);
  } else if (remain < 0) {
    // over-paid → show change
    $('#splitStatusLabel').text('ເງິນທອນ:').css('color', '#16a34a');
    $('#splitRemainingDisplay').text(Math.abs(remain).toLocaleString() + ' ₭').css('color', '#16a34a');
    $('#btnSubmitPayment').prop('disabled', false);
  } else {
    $('#splitStatusLabel').text('ພໍດີ ✓').css('color', '#16a34a');
    $('#splitRemainingDisplay').text('0 ₭').css('color', '#16a34a');
    $('#btnSubmitPayment').prop('disabled', false);
  }
}

function setSplitCashExact() {
  var total = parseFloat($('#cartTotal').text().replace(/[^\d]/g, '')) || 0;
  $('#splitCashAmount').val(total.toLocaleString('en-US'));
  $('#splitQrAmount').val('0');
  calcSplitRemaining();
}

function addSplitCash(amount) {
  var cur = parseFloat($('#splitCashAmount').val().replace(/[^\d]/g, '')) || 0;
  $('#splitCashAmount').val((cur + amount).toLocaleString('en-US'));
  calcSplitRemaining();
}

function fillSplitQrRemaining() {
  var total = parseFloat($('#cartTotal').text().replace(/[^\d]/g, '')) || 0;
  var cash  = parseFloat($('#splitCashAmount').val().replace(/[^\d]/g, '')) || 0;
  var rem   = Math.max(0, total - cash);
  $('#splitQrAmount').val(rem.toLocaleString('en-US'));
  calcSplitRemaining();
}

// ============================
// OPEN CHECKOUT MODAL
// ============================
function openCheckoutModal() {
  if (cart.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'ແຈ້ງເຕືອນ',
      text: 'ກະລຸນາເພີ່ມສິນຄ້າລົງກະຕ່າກ່ອນ!',
      confirmButtonColor: '#2563eb'
    });
    return;
  }

  currentPayMode = 'single';
  $('#singlePaySection').show();
  $('#splitPaySection').hide();
  $('#payTypeRow').show();
  $('#singleSummary').show();
  $('#splitSummary').hide();
  $('#payModeSingleBtn').css({ 'border-color': '#2563eb', 'background': '#eff6ff', 'color': '#1d4ed8' });
  $('#payModeSplitBtn').css({ 'border-color': '#cbd5e1', 'background': '#ffffff', 'color': '#64748b' });
  window.currentSelectedBankId = null;
  window.currentSelectedBankName = null;
  window.currentSelectedBankQrPath = '';
  window.currentSelectedBankAccNo = '';
  window.currentSelectedBankAccName = '';
  $('input[name="pos_selected_bank_id"]').prop('checked', false);
  $('.pos-bank-option').removeClass('selected-bank').css({'background': '#ffffff', 'border-color': '#cbd5e1'});
  $('#posActiveBankQrContainer').hide();
  $('#posNoQrMessage').hide();
  $('#posActiveBankNameTitle').text('');

  selectPayTypeTab('ເງິນສົດ');
  var totalText = $('#cartTotal').text();
  $('#checkoutTotalDisplay').text(totalText);
  $('#cashReceived').val(totalText.replace(' ₭', ''));
  calculateChange();
  $('#checkoutModal').modal('show');
  if (typeof broadcastCustomerDisplay === 'function') {
    broadcastCustomerDisplay('checkout_modal_open', { paymentType: selectedPayType });
  }
  setTimeout(function(){ $('#cashReceived').focus(); }, 400);
}

// ============================
// PROCESS CHECKOUT
// ============================
function processCheckout() {
  var total    = parseFloat($('#cartTotal').text().replace(/[^\d]/g, '')) || 0;
  var discount = parseFloat(($('#cartDiscount').val() || '0').replace(/[^\d]/g, '')) || 0;

  var cashReceived, qrReceived, paymentType, change;

  if (currentPayMode === 'split') {
    cashReceived = parseFloat($('#splitCashAmount').val().replace(/[^\d]/g, '')) || 0;
    qrReceived   = parseFloat($('#splitQrAmount').val().replace(/[^\d]/g, '')) || 0;
    var totalPaid = cashReceived + qrReceived;
    if (totalPaid < total) {
      Swal.fire({ icon: 'error', title: 'ຍອດບໍ່ຄົບ!', text: 'ຍັງຂາດ ' + (total - totalPaid).toLocaleString() + ' ₭ ກະລຸນາຕື່ມ', confirmButtonColor: '#ef4444' });
      return;
    }
    paymentType  = 'ເງິນສົດ + ໂອນ';
    change       = Math.max(0, totalPaid - total);
  } else {
    var rawInput = parseFloat($('#cashReceived').val().replace(/[^\d]/g, '')) || 0;
    if (rawInput < total) {
      Swal.fire({ icon: 'error', title: 'ເງິນທີ່ຮັບມາບໍ່ພໍ!', text: 'ກະລຸນາກວດສອບຈຳນວນເງິນທີ່ຮັບມາ', confirmButtonColor: '#ef4444' });
      return;
    }
    if (selectedPayType === 'ໂອນ' || selectedPayType === 'QR') {
      cashReceived = 0;
      qrReceived   = rawInput;
    } else {
      cashReceived = rawInput;
      qrReceived   = 0;
    }
    paymentType = selectedPayType;
    change      = Math.max(0, (cashReceived + qrReceived) - total);
  }

  var activeBankId = (selectedPayType === 'ໂອນ' || selectedPayType === 'QR' || selectedPayType === 'ໂອນເງິນ / QR' || currentPayMode === 'multiple') ? (window.currentSelectedBankId || $('input[name="pos_selected_bank_id"]:checked').val() || null) : null;
  var activeBankName = (selectedPayType === 'ໂອນ' || selectedPayType === 'QR' || selectedPayType === 'ໂອນເງິນ / QR' || currentPayMode === 'multiple') ? (window.currentSelectedBankName || null) : null;

  // Validate & clean cart items payload before sending to server
  var cleanCart = cart.filter(function(item) {
    if (!item) return false;
    var pid = parseInt(item.product_id, 10);
    return !isNaN(pid) || item.is_free_gift || (typeof item.product_id === 'string' && item.product_id.indexOf('GIFT_') === 0);
  }).map(function(item) {
    var pid = parseInt(item.product_id, 10);
    if (isNaN(pid)) {
      if (item.parent_product_id) {
        pid = parseInt(item.parent_product_id, 10);
      } else {
        pid = 999999;
      }
    }
    return {
      product_id:     pid,
      product_name:   item.product_name || '',
      unit_name:      item.unit_name || 'ອັນ',
      unit_price:     parseFloat(item.unit_price) || 0,
      original_price: parseFloat(item.original_price || item.base_price || item.unit_price) || 0,
      cost_price:     parseFloat(item.cost_price) || 0,
      multiplier:     parseInt(item.multiplier, 10) || 1,
      quantity:       parseFloat(item.quantity) || 1,
      is_free_gift:   !!item.is_free_gift,
      parent_product_id: item.parent_product_id ? parseInt(item.parent_product_id, 10) : null
    };
  });

  if (cleanCart.length === 0) {
    Swal.fire({
      icon: 'error',
      title: 'ຂໍ້ມູນສິນຄ້າຜິດພາດ!',
      text: 'ບໍ່ພົບ ID ສິນຄ້າທີ່ຖືກຕ້ອງໃນກະຕ່າ ກະລຸນາລ້າງກະຕ່າແລ້ວເລືອກສິນຄ້າໃໝ່!',
      confirmButtonColor: '#ef4444'
    });
    return;
  }

  var offlinePayload = {
    cart:            cleanCart,
    cash_received:   cashReceived,
    qr_received:     qrReceived,
    payment_type:    paymentType,
    pay_mode:        currentPayMode,
    discount_amount: discount,
    bank_account_id: activeBankId,
    bank_name:       activeBankName,
    customer_id:     selectedCustomer ? selectedCustomer.customer_id : null,
    customer_name:   selectedCustomer ? selectedCustomer.customer_name : 'ລູກຄ້າທົ່ວໄປ'
  };

  $.ajax({
    url: (window.POS_BACKEND_URL || '../../api/pos_backend.php'),
    type: 'POST',
    data: Object.assign({ action: 'checkout', cart: JSON.stringify(cleanCart) }, offlinePayload),
    dataType: 'json',
    success: function(res) {
      if (res && res.success) {
        handleOnlineCheckoutReceiptUI(res);
      } else {
        Swal.fire({ icon: 'error', title: 'ຜິດພາດ', text: (res && res.message) ? res.message : 'ບໍ່ສາມາດຊຳລະເງິນໄດ້!' });
      }
    },
    error: function(xhr, status, err) {
      var parsedRes = null;
      try {
        if (xhr.responseJSON) {
          parsedRes = xhr.responseJSON;
        } else if (xhr.responseText) {
          var match = xhr.responseText.match(/\{[\s\S]*\}/);
          if (match) {
            parsedRes = JSON.parse(match[0]);
          }
        }
      } catch(e) {}

      if (parsedRes && parsedRes.success) {
        handleOnlineCheckoutReceiptUI(parsedRes);
        return;
      }

      if (parsedRes && !parsedRes.success && parsedRes.message) {
        Swal.fire({ icon: 'error', title: 'ຜິດພາດ', text: parsedRes.message, confirmButtonColor: '#ef4444' });
        return;
      }

      console.warn('POS Checkout connection blip/error, executing seamless fallback:', status, err);
      handleOfflineCheckoutFallback(offlinePayload, total, change);
    }
  });
}

function handleOnlineCheckoutReceiptUI(res) {
  $('#checkoutModal').modal('hide');

  // ອັບເດດຍອດສະຕັອກຄົງເຫຼືອທີ່ຕັດແລ້ວເຂົ້າໃນ DOM ແລະ memory ທັນທີ
  if (res.updated_stocks && res.updated_stocks.length > 0) {
    applyStockUpdateFromBroadcast(res.updated_stocks);
    broadcastStockUpdate(res.updated_stocks);
  }

  // Reset cart memory and recalculate live stock
  cart = [];
  if (typeof renderCart === 'function') renderCart();
  if (typeof recalculateLiveStock === 'function') recalculateLiveStock();

  var cashierName = res.cashier || window.CURRENT_USER_NAME || 'Admin';
  if (!cashierName || cashierName === 'Cashier (Offline)') cashierName = 'Admin';

  $('#rc_bill').text(res.invoice_number);
  $('#rc_date').text(res.date);
  $('#rc_cashier').text(cashierName);
  $('#rc_customer').text(res.customer_name || 'ລູກຄ້າທົ່ວໄປ');
  $('#rc_subtotal').text(res.subtotal.toLocaleString() + ' ₭');
  $('#rc_discount').text(res.discount_amount.toLocaleString() + ' ₭');

  var rTaxType = res.tax_type || window.STORE_TAX_TYPE || 'none';
  var rTaxId   = res.tax_id || window.STORE_TAX_ID || '';
  if (rTaxType === 'none' || !rTaxId || rTaxId.trim() === '') {
    $('#rc_tax_id_row').attr('style', 'display: none !important;').hide();
  } else {
    $('#rc_tax_id').text(rTaxId);
    $('#rc_tax_id_row').attr('style', 'color:#000;font-weight:600;display:flex !important;').show();
  }

  var rVatRate = parseFloat(res.vat_rate) || parseFloat(window.STORE_VAT_PERCENT) || 0;
  var rVatAmt  = parseFloat(res.vat_amount);

  if (isNaN(rVatAmt) || (rVatAmt === 0 && rVatRate > 0 && rTaxType !== 'none')) {
    var subAmt = parseFloat(res.subtotal) || 0;
    var discAmt = parseFloat(res.discount_amount) || 0;
    var amtAfterDisc = Math.max(0, subAmt - discAmt);
    if (rTaxType === 'exclusive' && rVatRate > 0) {
      rVatAmt = Math.round(amtAfterDisc * (rVatRate / 100));
    } else if (rTaxType === 'inclusive' && rVatRate > 0) {
      rVatAmt = Math.round(amtAfterDisc - (amtAfterDisc / (1 + (rVatRate / 100))));
    } else {
      rVatAmt = 0;
    }
  }

  if (rTaxType === 'none' || rVatRate <= 0 || rVatAmt <= 0) {
    $('#rc_vat_row').attr('style', 'display: none !important;').hide();
  } else if (rTaxType === 'exclusive' && (rVatAmt > 0 || rVatRate > 0)) {
    $('#rc_vat_label').text('ອມພ (' + rVatRate + '%):');
    $('#rc_vat').text(rVatAmt.toLocaleString() + ' ₭');
    $('#rc_vat_row').attr('style', 'color:#000;font-weight:600;display:flex !important;').show();
  } else if (rTaxType === 'inclusive' && (rVatAmt > 0 || rVatRate > 0)) {
    $('#rc_vat_label').text('ລວມ ອມພ (' + rVatRate + '%):');
    $('#rc_vat').text(rVatAmt.toLocaleString() + ' ₭');
    $('#rc_vat_row').attr('style', 'color:#000;font-weight:600;display:flex !important;').show();
  } else if (rVatAmt > 0) {
    $('#rc_vat_label').text('ອມພ (' + (rVatRate > 0 ? rVatRate + '%' : '') + '):');
    $('#rc_vat').text(rVatAmt.toLocaleString() + ' ₭');
    $('#rc_vat_row').attr('style', 'color:#000;font-weight:600;display:flex !important;').show();
  } else {
    $('#rc_vat_row').attr('style', 'display: none !important;').hide();
  }

  $('#rc_total').text(res.total_amount.toLocaleString() + ' ₭');
  $('#rc_change').text(res.change.toLocaleString() + ' ₭');

  var cashAmt = parseFloat(res.cash_received) || 0;
  var qrAmt   = parseFloat(res.qr_received) || 0;

  if (cashAmt === 0 && qrAmt === 0) {
    var pType = res.payment_type || '';
    if (pType === 'ເງິນສົດ') {
      cashAmt = (parseFloat(res.total_amount) || 0) + (parseFloat(res.change) || 0);
    } else {
      qrAmt = parseFloat(res.total_amount) || 0;
    }
  }

  $('#rc_cash_amt').text(cashAmt.toLocaleString() + ' ₭');
  $('#rc_qr_amt').text(qrAmt.toLocaleString() + ' ₭');

  var bName = res.bank_name || window.currentSelectedBankName || '';
  var bAccNo = res.bank_account_no || window.currentSelectedBankAccNo || '';
  var bAccName = res.bank_account_name || window.currentSelectedBankAccName || '';
  var bQrImg = res.bank_qr_img || window.currentSelectedBankQrPath || '';
  var pType = res.payment_type || selectedPayType || '';

  var isTransferPayment = (qrAmt > 0) || (pType && (pType.includes('ໂອນ') || pType.includes('QR')));

  if (isTransferPayment) {
    if (bQrImg) {
      $('#rc_bank_qr_img').attr('src', bQrImg);
    }
    var labelBank = bName ? bName : 'BCEL One';
    if (bAccName) labelBank += ' (' + bAccName + ')';
    $('#rc_bank_name_lbl').text('ສະແກນ QR ໂອນຊຳລະ (' + labelBank + ')');

    if (bAccNo) {
      $('#rc_bank_acc_lbl').text('ເລກບັນຊີ: ' + bAccNo).show();
    } else {
      $('#rc_bank_acc_lbl').hide();
    }
    $('.receipt-qr-box').show();
  } else {
    $('.receipt-qr-box').hide();
  }

  var tbody = $('#rc_items');
  tbody.empty();
  if (res.details && res.details.length > 0) {
    res.details.forEach(function(d) {
      var uName = d.unit_name || '';
      var displayName = d.proname || '';
      if (uName && !displayName.includes('(')) {
        displayName += ' (' + uName + ')';
      }

      var isGift = !!d.is_free_gift || d.price === 0 || displayName.includes('(ແຖມ)');
      var origUnitPrice = parseFloat(d.original_price || d.price) || 0;
      var curUnitPrice = parseFloat(d.price) || 0;
      var totalAmt = parseFloat(d.total) || (curUnitPrice * d.qty);

      var nameHtml = displayName;
      var priceHtml = '';

      if (isGift) {
        if (!nameHtml.includes('ແຖມ')) {
          nameHtml = `<span style="font-weight:bold; color:#059669;">[ແຖມຟຣີ]</span> ${nameHtml}`;
        }
        priceHtml = '<span style="font-weight:bold; color:#059669;">0 ₭ (ແຖມຟຣີ)</span>';
      } else if (origUnitPrice > curUnitPrice && curUnitPrice >= 0) {
        var origTotal = origUnitPrice * d.qty;
        priceHtml = `<del style="color:#64748b; font-size:0.85em;">${origTotal.toLocaleString()} ₭</del><br><span style="font-weight:bold;">${totalAmt.toLocaleString()} ₭</span>`;
      } else {
        priceHtml = `${totalAmt.toLocaleString()} ₭`;
      }

      tbody.append(`
        <tr>
          <td>${nameHtml}</td>
          <td class="text-center">x${d.qty}</td>
          <td class="text-right">${priceHtml}</td>
        </tr>
      `);
    });
  }

  if (typeof broadcastCustomerDisplay === 'function') {
    broadcastCustomerDisplay('payment_success', { cashReceived: res.cash_received, qrReceived: res.qr_received, changeAmount: res.change });
  }
  printReceipt();
  resetPOS();
}

// ============================
// PRINT RECEIPT
// ============================
function printReceipt() {
  var printContent = document.getElementById("receiptPrintArea").innerHTML;
  var iframe = document.getElementById("posPrintIframe");
  if (!iframe) {
    iframe = document.createElement('iframe');
    iframe.id = 'posPrintIframe';
    iframe.style.cssText = 'position:fixed;right:-9999px;bottom:-9999px;width:300px;height:300px;border:none;opacity:0;pointer-events:none;';
    document.body.appendChild(iframe);
  }
  var iframeDoc = iframe.contentWindow || iframe.contentDocument;
  if (iframeDoc.document) iframeDoc = iframeDoc.document;

  iframeDoc.open();
  iframeDoc.write(`<!DOCTYPE html><html><head>
    <meta charset="utf-8">
    <title>ໃບບິນຮັບເງິນ</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/local-font.css">
    <style>
      @page { size: 80mm auto; margin: 0mm; }
      * { box-sizing: border-box; font-family: 'Noto Sans Lao Looped', 'Noto Sans Lao', 'Phetsarath OT', 'Saysettha OT', Arial, sans-serif !important; color: #000 !important; }
      html, body { width: 80mm; margin: 0 auto; padding: 8px 6px; background: #fff; color: #000 !important; font-size: 12px; line-height: 1.4; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .text-center { text-align: center !important; } .text-right { text-align: right !important; }
      .font-weight-bold { font-weight: 700 !important; color: #000 !important; } 
      .small { font-size: 11.5px !important; color: #000 !important; font-weight: 600 !important; }
      .d-flex { display: flex; } .justify-content-between { justify-content: space-between; }
      [style*="display: none"], [style*="display:none"], .d-none { display: none !important; }
      .mb-0{margin-bottom:0!important}.mb-1{margin-bottom:4px!important}.mb-2{margin-bottom:8px!important}
      .mt-2{margin-top:8px!important} 
      .text-muted { color: #000 !important; font-weight: 600 !important; }
      .receipt-header-address, .receipt-header-tel { font-size: 12px !important; font-weight: 600 !important; color: #000 !important; line-height: 1.4 !important; }
      .receipt-footer-msg { font-size: 12.5px !important; font-weight: 700 !important; color: #000 !important; border-top: 1px dashed #000 !important; margin-top: 10px !important; padding-top: 8px !important; text-align: center !important; }
      img.receipt-logo { max-width:80px!important; max-height:80px!important; height:auto!important; display:block!important; margin:10px auto 2px auto!important; object-fit:contain!important; }
      img.receipt-qr-img { max-width:100px!important; max-height:100px!important; height:auto!important; display:block!important; margin:6px auto 2px auto!important; object-fit:contain!important; }
      table { width:100%; border-collapse:collapse; margin:4px 0; font-size:11.5px; color: #000 !important; }
      td,th { padding:3px 0; vertical-align:top; color: #000 !important; font-weight: 600 !important; }
      th { font-weight: 700 !important; }
      @media print { 
        html,body { width:100%; margin:0; padding:2mm; color: #000 !important; } 
        * { color: #000 !important; }
      }
    </style>
  </head><body>${printContent}</body></html>`);
  iframeDoc.close();

  var images = iframeDoc.getElementsByTagName('img');
  var totalImages = images.length;
  var loadedCount = 0;
  var printTriggered = false;

  function doTriggerPOSPrint() {
    if (printTriggered) return;
    printTriggered = true;
    try {
      iframe.contentWindow.focus();
      iframe.contentWindow.print();
    } catch(e) {
      console.warn('Iframe print error, falling back to window.print():', e);
      window.print();
    }
  }

  if (totalImages === 0) {
    setTimeout(doTriggerPOSPrint, 200);
  } else {
    for (var i = 0; i < totalImages; i++) {
      if (images[i].complete && images[i].naturalWidth !== 0) {
        loadedCount++;
      } else {
        images[i].onload = images[i].onerror = function() {
          loadedCount++;
          if (loadedCount >= totalImages) {
            setTimeout(doTriggerPOSPrint, 150);
          }
        };
      }
    }
    if (loadedCount >= totalImages) {
      setTimeout(doTriggerPOSPrint, 200);
    } else {
      setTimeout(doTriggerPOSPrint, 600);
    }
  }
}

// ============================
// RESET POS
// ============================
function resetPOS() {
  activeBills = activeBills.filter(function(b) { return b.id !== currentBillId; });
  if (typeof resequenceActiveBills === 'function') resequenceActiveBills();

  if (activeBills.length === 0) {
    isBillOpened = false;
    localStorage.removeItem('pos_bill_opened');
    localStorage.removeItem('pos_active_bills');
    cart = [];
    selectedCustomer = { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' };
    currentBillId = null;
    updateCartUI();
    updateActiveBillsUI();
  } else {
    isBillOpened = true;
    localStorage.setItem('pos_bill_opened', '1');
    currentBillId = activeBills[0].id;
    localStorage.setItem('pos_active_bills', JSON.stringify(activeBills));
    loadBillState(currentBillId);
  }
  $('#receiptModal').modal('hide');
  $('#barcodeInput').val('').focus();
}

function selectPosBank(bankId, bankName, qrPath, accNo, accName, el) {
  $('.pos-bank-option').css({'background': '#ffffff', 'border-color': '#cbd5e1'}).removeClass('selected-bank');
  if (el) {
    $(el).addClass('selected-bank').css({'background': '#f0f9ff', 'border-color': '#0284c7'});
    $(el).find('input[type="radio"]').prop('checked', true);
  }

  if (qrPath && qrPath.trim() !== '') {
    $('#posActiveBankQrImg').attr('src', qrPath);
    $('#rc_bank_qr_img').attr('src', qrPath);
    $('#posActiveBankQrContainer').show();
    $('#posNoQrMessage').hide();
  } else {
    $('#posActiveBankQrImg').attr('src', '');
    $('#posActiveBankQrContainer').hide();
    $('#posNoQrMessage').show();
  }

  if (bankName) {
    $('#posActiveBankNameTitle').text(' - ' + bankName);
  }
  window.currentSelectedBankId = bankId;
  window.currentSelectedBankName = bankName;
  window.currentSelectedBankQrPath = qrPath || '';
  window.currentSelectedBankAccNo = accNo || '';
  window.currentSelectedBankAccName = accName || '';
}

function formatPriceInput(input) {
  var val = input.value.replace(/\D/g, '');
  input.value = val === '' ? '0' : Number(val).toLocaleString('en-US');
}

// ============================================================
// INDEXEDDB OFFLINE SALES QUEUE & AUTO-SYNC ENGINE
// ============================================================
var dbName = 'minipos_offline_db';
var dbVersion = 1;
var dbInstance = null;

function initMiniPosIndexedDB() {
  if (!('indexedDB' in window)) {
    console.warn('IndexedDB not supported on this browser.');
    return;
  }
  var request = window.indexedDB.open(dbName, dbVersion);
  request.onupgradeneeded = function(e) {
    var db = e.target.result;
    if (!db.objectStoreNames.contains('offline_sales')) {
      var store = db.createObjectStore('offline_sales', { keyPath: 'id', autoIncrement: true });
      store.createIndex('synced', 'synced', { unique: false });
    }
  };
  request.onsuccess = function(e) {
    dbInstance = e.target.result;
    updateOfflineQueueCountBadge();
    setTimeout(function() { syncOfflineSalesToServer(); }, 1500);
  };
  request.onerror = function(e) {
    console.error('IndexedDB open error:', e);
  };
}

function updateNetworkStatusUI() {
  var isOnline = navigator.onLine;
  var icon = $('#netStatusIcon');
  var text = $('#netStatusText');
  var badge = $('#netStatusBadge');

  if (isOnline) {
    badge.hide();
    $('#offlineQueueBadge').hide();
    syncOfflineSalesToServer();
  } else {
    icon.attr('class', 'fas fa-exclamation-triangle text-warning mr-1');
    text.text('ອັອບໄລນ໌ (Offline)');
    badge.css({'background-color': '#d97706', 'display': 'inline-flex'}).show();
    updateOfflineQueueCountBadge();
  }
}

window.addEventListener('online', updateNetworkStatusUI);
window.addEventListener('offline', updateNetworkStatusUI);
$(document).ready(function() {
  initMiniPosIndexedDB();
  updateNetworkStatusUI();
});

function saveOfflineSaleToIndexedDB(saleData, callback) {
  if (!dbInstance) {
    if (callback) callback(null);
    return;
  }
  try {
    var tx = dbInstance.transaction(['offline_sales'], 'readwrite');
    var store = tx.objectStore('offline_sales');
    saleData.synced = 0;
    saleData.created_at = new Date().toISOString();
    var req = store.add(saleData);
    req.onsuccess = function(e) {
      updateOfflineQueueCountBadge();
      if (callback) callback(e.target.result);
    };
    req.onerror = function(e) {
      console.error('Save offline sale DB error:', e);
      if (callback) callback(null);
    };
  } catch (err) {
    console.error('IndexedDB Transaction exception:', err);
    if (callback) callback(null);
  }
}

function updateOfflineQueueCountBadge() {
  var isOnline = navigator.onLine;
  if (isOnline) {
    $('#netStatusBadge').hide();
    $('#offlineQueueBadge').hide();
    return;
  }
  if (!dbInstance) return;
  try {
    var tx = dbInstance.transaction(['offline_sales'], 'readonly');
    var store = tx.objectStore('offline_sales');
    var req = store.getAll();
    req.onsuccess = function(e) {
      var all = e.target.result || [];
      var unsynced = all.filter(function(item) { return item.synced === 0; });
      var count = unsynced.length;

      if (!navigator.onLine) {
        $('#netStatusBadge').css('display', 'inline-flex').show();
        if (count > 0) {
          $('#offlineQueueBadge').text(count + ' ຄ້າງ Sync').show();
        } else {
          $('#offlineQueueBadge').hide();
        }
      } else {
        $('#netStatusBadge').hide();
        $('#offlineQueueBadge').hide();
      }
    };
  } catch (err) {}
}

function handleOfflineCheckoutFallback(saleObj, total, change) {
  var now = new Date();
  var dateStr = now.getFullYear().toString() + (now.getMonth() + 1).toString().padStart(2, '0') + now.getDate().toString().padStart(2, '0');
  window.POS_TODAY_SALE_COUNT = (parseInt(window.POS_TODAY_SALE_COUNT) || 0) + 1;
  var seqStr = String(window.POS_TODAY_SALE_COUNT).padStart(4, '0');
  var offlineBillNum = dateStr + '-' + seqStr;
  var cashierName = window.CURRENT_USER_NAME || 'Admin';

  saveOfflineSaleToIndexedDB(saleObj, function(savedId) {
    $('#checkoutModal').modal('hide');

    // 1. ຕັດສະຕັອກເຣວທາມໃນ DOM ແລະ memory ສຳລັບບິນອັອບໄລນ໌ທັນທີ (ບໍ່ຕ້ອງຣີເຟສ)
    var offlineUpdatedStocks = [];
    if (saleObj.cart && saleObj.cart.length > 0) {
      saleObj.cart.forEach(function(item) {
        var pid = item.product_id;
        var deductQty = (parseFloat(item.quantity) || 1) * (parseInt(item.multiplier) || 1);

        var newStock = 0;
        var stockValEl = $('.product-stock-val-' + pid);
        if (stockValEl.length) {
          var currentStock = parseFloat(stockValEl.attr('data-initial-stock')) || 0;
          newStock = Math.max(0, currentStock - deductQty);
          stockValEl.attr('data-initial-stock', newStock);
          stockValEl.text(newStock.toLocaleString());
        }

        if (typeof allProducts !== 'undefined' && allProducts && allProducts.length > 0) {
          var pItem = allProducts.find(function(p) { return String(p.product_id) === String(pid); });
          if (pItem) {
            pItem.qty = Math.max(0, (parseFloat(pItem.qty) || 0) - deductQty);
            newStock = pItem.qty;
          }
        }

        offlineUpdatedStocks.push({ product_id: pid, new_qty: newStock });
      });

      applyStockUpdateFromBroadcast(offlineUpdatedStocks);
      broadcastStockUpdate(offlineUpdatedStocks);
    }

    // 2. ເຄຼຍກະຕ່າ ແລະ ຄຳນວນສະຕັອກຄົງເຫຼືອທັນທີ
    cart = [];
    if (typeof renderCart === 'function') renderCart();
    if (typeof recalculateLiveStock === 'function') recalculateLiveStock();

    var offTaxType = window.STORE_TAX_TYPE || 'none';
    var offVatRate = parseFloat(window.STORE_VAT_PERCENT) || 0;
    var offSubtotal = total + (saleObj.discount_amount || 0);
    var offAmtAfterDisc = Math.max(0, offSubtotal - (saleObj.discount_amount || 0));
    var offVatAmt = 0;

    if (offTaxType === 'exclusive' && offVatRate > 0) {
      offVatAmt = Math.round(offAmtAfterDisc * (offVatRate / 100));
    } else if (offTaxType === 'inclusive' && offVatRate > 0) {
      offVatAmt = Math.round(offAmtAfterDisc - (offAmtAfterDisc / (1 + (offVatRate / 100))));
    }

    saleObj.tax_type = offTaxType;
    saleObj.vat_rate = offVatRate;
    saleObj.vat_amount = offVatAmt;

    // 3. ສະແດງໃບບິນ
    $('#rc_bill').text(offlineBillNum);
    $('#rc_date').text(now.toLocaleDateString('en-GB') + ' ' + now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
    $('#rc_cashier').text(cashierName);
    $('#rc_customer').text(saleObj.customer_name || 'ລູກຄ້າທົ່ວໄປ');
    $('#rc_subtotal').text(offSubtotal.toLocaleString() + ' ₭');
    $('#rc_discount').text(saleObj.discount_amount.toLocaleString() + ' ₭');

    if (offTaxType === 'none' || offVatRate <= 0) {
      $('#rc_vat_row').hide();
    } else if (offTaxType === 'exclusive' && (offVatAmt > 0 || offVatRate > 0)) {
      $('#rc_vat_label').text('ອມພ (' + offVatRate + '%):');
      $('#rc_vat').text(offVatAmt.toLocaleString() + ' ₭');
      $('#rc_vat_row').show();
    } else if (offTaxType === 'inclusive' && (offVatAmt > 0 || offVatRate > 0)) {
      $('#rc_vat_label').text('ລວມ ອມພ (' + offVatRate + '%):');
      $('#rc_vat').text(offVatAmt.toLocaleString() + ' ₭');
      $('#rc_vat_row').show();
    } else if (offVatAmt > 0) {
      $('#rc_vat_label').text('ອມພ (' + (offVatRate > 0 ? offVatRate + '%' : '') + '):');
      $('#rc_vat').text(offVatAmt.toLocaleString() + ' ₭');
      $('#rc_vat_row').show();
    } else {
      $('#rc_vat_row').hide();
    }
    $('#rc_total').text(total.toLocaleString() + ' ₭');
    $('#rc_change').text(change.toLocaleString() + ' ₭');

    var offCashAmt = parseFloat(saleObj.cash_received) || 0;
    var offQrAmt   = parseFloat(saleObj.qr_received) || 0;

    if (offCashAmt === 0 && offQrAmt === 0) {
      var pType = saleObj.payment_type || saleObj.pay_mode || '';
      if (pType === 'ເງິນສົດ' || pType === 'cash') {
        offCashAmt = (parseFloat(saleObj.total_amount) || 0) + (parseFloat(saleObj.change) || 0);
      } else {
        offQrAmt = parseFloat(saleObj.total_amount) || 0;
      }
    }

    $('#rc_cash_amt').text(offCashAmt.toLocaleString() + ' ₭');
    $('#rc_qr_amt').text(offQrAmt.toLocaleString() + ' ₭');

    var tbody = $('#rc_items');
    tbody.empty();
    saleObj.cart.forEach(function(item) {
      var itemTotal = (item.unit_price * item.quantity);
      var uName = item.unit_name || '';
      var displayName = item.product_name || '';
      if (uName && !displayName.includes('(')) {
        displayName += ' (' + uName + ')';
      }

      var isGift = !!item.is_free_gift || item.unit_price === 0 || displayName.includes('(ແຖມ)');
      var origUnitPrice = parseFloat(item.original_price || item.unit_price) || 0;
      var curUnitPrice = parseFloat(item.unit_price) || 0;

      var nameHtml = displayName;
      var priceHtml = '';

      if (isGift) {
        if (!nameHtml.includes('ແຖມ')) {
          nameHtml = `<span style="font-weight:bold; color:#059669;">[ແຖມຟຣີ]</span> ${nameHtml}`;
        }
        priceHtml = '<span style="font-weight:bold; color:#059669;">0 ₭ (ແຖມຟຣີ)</span>';
      } else if (origUnitPrice > curUnitPrice && curUnitPrice >= 0) {
        var origTotal = origUnitPrice * item.quantity;
        priceHtml = `<del style="color:#64748b; font-size:0.85em;">${origTotal.toLocaleString()} ₭</del><br><span style="font-weight:bold;">${itemTotal.toLocaleString()} ₭</span>`;
      } else {
        priceHtml = `${itemTotal.toLocaleString()} ₭`;
      }

      tbody.append(`
        <tr>
          <td>${nameHtml}</td>
          <td class="text-center">x${item.quantity}</td>
          <td class="text-right">${priceHtml}</td>
        </tr>
      `);
    });

    if (typeof broadcastCustomerDisplay === 'function') {
      broadcastCustomerDisplay('payment_success', { cashReceived: saleObj.cash_received, qrReceived: saleObj.qr_received, changeAmount: change });
    }
    printReceipt();
    resetPOS();
  });
}

var isSyncingOfflineSales = false;
function syncOfflineSalesToServer(userClicked) {
  if (!navigator.onLine || !dbInstance || isSyncingOfflineSales) {
    if (userClicked && !navigator.onLine) {
      Swal.fire({ icon: 'warning', title: 'ອັອບໄລນ໌ຢູ່', text: 'ກະລຸນາເຊື່ອມຕໍ່ອິນເຕີເນັດ/ເຄືອຂ່າຍ ກ່ອນກົດ Sync ບິນ!', confirmButtonColor: '#0284c7' });
    }
    return;
  }

  isSyncingOfflineSales = true;
  try {
    var tx = dbInstance.transaction(['offline_sales'], 'readonly');
    var store = tx.objectStore('offline_sales');
    var req = store.getAll();
    req.onsuccess = function(e) {
      var all = e.target.result || [];
      var unsynced = all.filter(function(item) { return item.synced === 0; });
      if (unsynced.length === 0) {
        isSyncingOfflineSales = false;
        if (userClicked) {
          Swal.fire({ icon: 'success', title: 'Sync ຄົບຖ້ວນ', text: 'ບໍ່ມີບິນອັອບໄລນ໌ຄ້າງ Sync ແລ້ວ!', confirmButtonColor: '#0284c7' });
        }
        return;
      }

      var syncedSuccessCount = 0;
      var processChain = Promise.resolve();

      unsynced.forEach(function(item) {
        processChain = processChain.then(function() {
          return new Promise(function(resolve) {
            var cartParam = (typeof item.cart === 'string') ? item.cart : JSON.stringify(item.cart || []);
            $.ajax({
              url: '../../api/pos_backend.php',
              type: 'POST',
              data: {
                action:          'checkout',
                cart:            cartParam,
                cash_received:   item.cash_received,
                qr_received:     item.qr_received,
                payment_type:    item.payment_type,
                pay_mode:        item.pay_mode,
                discount_amount: item.discount_amount,
                bank_account_id: item.bank_account_id,
                bank_name:       item.bank_name,
                customer_id:     item.customer_id,
                customer_name:   item.customer_name
              },
              dataType: 'json',
              success: function(res) {
                if (res && res.success) {
                  syncedSuccessCount++;
                  try {
                    var delTx = dbInstance.transaction(['offline_sales'], 'readwrite');
                    delTx.objectStore('offline_sales').delete(item.id);
                  } catch (ex) {}
                } else if (res && !res.success) {
                  // Server responded but rejected item (e.g. empty cart or duplicate), delete to unblock queue
                  try {
                    var delTx = dbInstance.transaction(['offline_sales'], 'readwrite');
                    delTx.objectStore('offline_sales').delete(item.id);
                  } catch (ex) {}
                }
                resolve();
              },
              error: function() {
                // On error, retain bill in IndexedDB for retry on next sync
                resolve();
              }
            });
          });
        });
      });

      processChain.then(function() {
        isSyncingOfflineSales = false;
        updateOfflineQueueCountBadge();
        if (userClicked && syncedSuccessCount > 0) {
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
          });
          Toast.fire({
            icon: 'success',
            title: `Sync ບິນອັອບໄລນ໌ສຳເລັດ ${syncedSuccessCount} ລາຍການ!`
          });
        }
      });
    };
    req.onerror = function() {
      isSyncingOfflineSales = false;
    };
  } catch (err) {
    isSyncingOfflineSales = false;
  }
}
</script>
