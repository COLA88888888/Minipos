<script>
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
  $('#splitCashAmount').val('0');
  $('#splitQrAmount').val('0');

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
    return item && parseInt(item.product_id, 10) > 0;
  }).map(function(item) {
    return {
      product_id:   parseInt(item.product_id, 10),
      product_name: item.product_name || '',
      unit_name:    item.unit_name || 'ອັນ',
      unit_price:   parseFloat(item.unit_price) || 0,
      cost_price:   parseFloat(item.cost_price) || 0,
      multiplier:   parseInt(item.multiplier, 10) || 1,
      quantity:     parseFloat(item.quantity) || 1,
      is_free_gift: !!item.is_free_gift,
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

  $.ajax({
    url: '',
    type: 'POST',
    data: {
      action:          'checkout',
      cart:            JSON.stringify(cleanCart),
      cash_received:   cashReceived,
      qr_received:     qrReceived,
      payment_type:    paymentType,
      pay_mode:        currentPayMode,
      discount_amount: discount,
      bank_account_id: activeBankId,
      bank_name:       activeBankName,
      customer_id:     selectedCustomer ? selectedCustomer.customer_id : null,
      customer_name:   selectedCustomer ? selectedCustomer.customer_name : 'ລູກຄ້າທົ່ວໄປ'
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        $('#checkoutModal').modal('hide');

        // ອັບເດດຍອດສະຕັອກຄົງເຫຼືອທີ່ຕັດແລ້ວເຂົ້າໃນ DOM ແລະ memory ທັນທີ (ບໍ່ໃຫ້ເດັ້ງກັບເປັນຍອດເກົ່າ)
        if (res.updated_stocks && res.updated_stocks.length > 0) {
          res.updated_stocks.forEach(function(st) {
            var pid = st.product_id;
            var newQty = parseFloat(st.new_qty);

            // 1. ອັບເດດ attribute data-initial-stock
            var stockValEl = $('.product-stock-val-' + pid);
            if (stockValEl.length) {
              stockValEl.attr('data-initial-stock', newQty);
            }

            // 2. ອັບເດດ allProducts array
            if (typeof allProducts !== 'undefined' && allProducts && allProducts.length > 0) {
              var pItem = allProducts.find(function(item) { return String(item.product_id) === String(pid); });
              if (pItem) {
                pItem.qty = newQty;
              }
            }
          });
        }

        $('#rc_bill').text(res.invoice_number);
        $('#rc_date').text(res.date);
        $('#rc_cashier').text(res.cashier);
        $('#rc_customer').text(res.customer_name || 'ລູກຄ້າທົ່ວໄປ');
        $('#rc_subtotal').text(res.subtotal.toLocaleString() + ' ₭');
        $('#rc_discount').text(res.discount_amount.toLocaleString() + ' ₭');

        var rTaxType = res.tax_type || 'none';
        var rVatRate = parseFloat(res.vat_rate) || 0;
        var rVatAmt  = parseFloat(res.vat_amount) || 0;

        if (rTaxType === 'exclusive' && rVatAmt > 0) {
          $('#rc_vat_label').text('ພາສີ (VAT ' + rVatRate + '%):');
          $('#rc_vat').text('+' + rVatAmt.toLocaleString() + ' ₭');
          $('#rc_vat_row').show();
        } else if (rTaxType === 'inclusive' && rVatAmt > 0) {
          $('#rc_vat_label').text('ລວມ ພາສີ (VAT ' + rVatRate + '%):');
          $('#rc_vat').text(rVatAmt.toLocaleString() + ' ₭');
          $('#rc_vat_row').show();
        } else {
          $('#rc_vat_row').hide();
        }

        $('#rc_total').text(res.total_amount.toLocaleString() + ' ₭');
        $('#rc_change').text(res.change.toLocaleString() + ' ₭');

        // Payment rows (Always display Cash & Transfer amounts, showing 0 ₭ if unreceived)
        var cashAmt = parseFloat(res.cash_received) || 0;
        var qrAmt   = parseFloat(res.qr_received) || 0;

        if (cashAmt === 0 && qrAmt === 0) {
          var pType = res.payment_type || '';
          if (pType === 'ເງິນສົດ') {
            cashAmt = (parseFloat(res.total_amount) || 0) + (parseFloat(res.change) || 0);
          } else if (pType === 'ໂອນ' || pType === 'QR' || pType === 'ເງິນໂອນ') {
            qrAmt = parseFloat(res.total_amount) || 0;
          }
        }

        var bName = res.bank_name || window.currentSelectedBankName || '';
        var payRows = '';
        payRows += '<div class="d-flex justify-content-between"><span>ຮັບເງິນສົດ:</span><span>' + cashAmt.toLocaleString() + ' ₭</span></div>';
        payRows += '<div class="d-flex justify-content-between"><span>ຮັບເງິນໂອນ:</span><span>' + qrAmt.toLocaleString() + ' ₭</span></div>';
        if (qrAmt > 0 || (paymentType && (paymentType.indexOf('ໂອນ') !== -1 || paymentType.indexOf('QR') !== -1))) {
          var displayBank = bName ? bName : 'BCEL One';
          payRows += '<div class="d-flex justify-content-between font-weight-bold" style="font-weight:700;"><span>ໂອນຜ່ານທະນາຄານ:</span><span>' + displayBank + '</span></div>';
        }
        $('#rc_payment_rows').html(payRows);

        // Update Receipt Bank QR & Title
        var activeBankQr = $('#posActiveBankQrImg').attr('src');
        if (activeBankQr && (qrAmt > 0 || bName)) {
          $('#rc_bank_qr_img').attr('src', activeBankQr);
        }
        if ((bName || qrAmt > 0)) {
          var labelBank = bName ? bName : 'BCEL One';
          $('#rc_bank_name_lbl').text('ສະແກນ QR ໂອນຊຳລະ (' + labelBank + ')');
        } else {
          $('#rc_bank_name_lbl').text('ສະແກນ QR Code ເພື່ອຊຳລະເງິນ');
        }

        // Items
        var tbody = $('#rc_items');
        tbody.empty();
        res.details.forEach(function(d) {
          tbody.append(`
            <tr>
              <td>${d.proname}</td>
              <td class="text-center">x${d.qty}</td>
              <td class="text-right">${d.total.toLocaleString()}</td>
            </tr>
          `);
        });

        if (typeof broadcastCustomerDisplay === 'function') {
          broadcastCustomerDisplay('payment_success', { cashReceived: res.cash_received, qrReceived: res.qr_received, changeAmount: res.change });
        }
        printReceipt();
        setTimeout(function() { resetPOS(); }, 1200);
      } else {
        Swal.fire({ icon: 'error', title: 'ຜິດພາດ', text: res.message });
      }
    },
    error: function() {
      Swal.fire({ icon: 'error', title: 'ຜິດພາດ', text: 'ບໍ່ສາມາດເຊື່ອມຕໍ່ກັບເຊີເວີໄດ້!' });
    }
  });
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
    iframe.style.cssText = 'position:absolute;width:0px;height:0px;border:none;';
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
      * { box-sizing: border-box; font-family: 'Noto Sans Lao Looped', 'Phetsarath OT', Arial, sans-serif !important; color: #000 !important; }
      html, body { width: 80mm; margin: 0 auto; padding: 8px 6px; background: #fff; color: #000 !important; font-size: 12px; line-height: 1.4; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .text-center { text-align: center !important; } .text-right { text-align: right !important; }
      .font-weight-bold { font-weight: 700 !important; color: #000 !important; } 
      .small { font-size: 11.5px !important; color: #000 !important; font-weight: 600 !important; }
      .d-flex { display: flex !important; } .justify-content-between { justify-content: space-between !important; }
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
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
  }

  if (totalImages === 0) {
    setTimeout(doTriggerPOSPrint, 150);
  } else {
    for (var i = 0; i < totalImages; i++) {
      if (images[i].complete && images[i].naturalWidth !== 0) {
        loadedCount++;
      } else {
        images[i].onload = images[i].onerror = function() {
          loadedCount++;
          if (loadedCount >= totalImages) {
            setTimeout(doTriggerPOSPrint, 100);
          }
        };
      }
    }
    if (loadedCount >= totalImages) {
      setTimeout(doTriggerPOSPrint, 150);
    } else {
      setTimeout(doTriggerPOSPrint, 500);
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

function selectPosBank(bankId, bankName, qrPath, el) {
  $('.pos-bank-option').css({'background': '#ffffff', 'border-color': '#cbd5e1'}).removeClass('selected-bank');
  if (el) {
    $(el).addClass('selected-bank').css({'background': '#f0f9ff', 'border-color': '#0284c7'});
    $(el).find('input[type="radio"]').prop('checked', true);
  }
  if (qrPath) {
    $('#posActiveBankQrImg').attr('src', qrPath);
  }
  if (bankName) {
    $('#posActiveBankNameTitle').text(' - ' + bankName);
  }
  window.currentSelectedBankId = bankId;
  window.currentSelectedBankName = bankName;
}

function formatPriceInput(input) {
  var val = input.value.replace(/\D/g, '');
  input.value = val === '' ? '0' : Number(val).toLocaleString('en-US');
}
</script>
