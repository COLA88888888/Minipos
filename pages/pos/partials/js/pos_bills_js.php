<script>
// --- ACTIVE BILLS & HELD ORDERS MANAGEMENT ---
function initActiveBills() {
  try {
    activeBills = JSON.parse(localStorage.getItem('pos_active_bills') || '[]');
  } catch(e) { activeBills = []; }

  isBillOpened = true;
  localStorage.setItem('pos_bill_opened', '1');

  if (activeBills.length === 0) {
    var newId = 'BILL-' + Date.now();
    activeBills = [{
      id: newId,
      name: 'ບິນທີ 1',
      time: new Date().toLocaleTimeString('lo-LA', { hour: '2-digit', minute: '2-digit' }),
      customer: { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' },
      cart: [],
      discount: '0'
    }];
    localStorage.setItem('pos_active_bills', JSON.stringify(activeBills));
  }

  currentBillId = activeBills[0].id;
  loadBillState(currentBillId);

  var cur = activeBills.find(function(b) { return b.id === currentBillId; });
  $('#currentBillBadge')
    .text(cur ? cur.name : 'ບິນທີ 1')
    .removeClass('badge-secondary')
    .addClass('badge-primary');
  updateActiveBillsUI();
}

function saveCurrentBillState() {
  var idx = activeBills.findIndex(function(b) { return b.id === currentBillId; });
  if (idx !== -1) {
    activeBills[idx].cart = JSON.parse(JSON.stringify(cart));
    activeBills[idx].customer = JSON.parse(JSON.stringify(selectedCustomer));
    activeBills[idx].discount = $('#cartDiscount').val();
    localStorage.setItem('pos_active_bills', JSON.stringify(activeBills));
  }
}

function loadBillState(billId) {
  var bill = activeBills.find(function(b) { return b.id === billId; });
  if (!bill) return;

  currentBillId = bill.id;
  cart = JSON.parse(JSON.stringify(bill.cart || []));
  selectedCustomer = JSON.parse(JSON.stringify(bill.customer || { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' }));
  $('#cartDiscount').val(bill.discount || '0');

  updateSelectedCustomerUI();
  updateCartUI();
  updateActiveBillsUI();
}

function resequenceActiveBills() {
  activeBills.forEach(function(b, idx) {
    b.name = 'ບິນທີ ' + (idx + 1);
  });
  localStorage.setItem('pos_active_bills', JSON.stringify(activeBills));
}

window.createNewBillModal = function() {
  saveCurrentBillState();

  // ຖ້າ ບິນປັດຈຸບັນ ຍັງຫວ່າງເປົ່າ (ບໍ່ມີລາຍການ ແລະ ລູກຄ້າທົ່ວໄປ) — ໃຊ້ ບິນນັ້ນເລີຍ ບໍ່ຕ້ອງສ້າງໃໝ່
  var currentBill = activeBills.find(function(b) { return b.id === currentBillId; });
  var isCurrentEmpty = currentBill
    && (!currentBill.cart || currentBill.cart.length === 0)
    && (!currentBill.customer || !currentBill.customer.customer_id);

  if (isCurrentEmpty) {
    isBillOpened = true;
    localStorage.setItem('pos_bill_opened', '1');
    Swal.fire({
      icon: 'success',
      title: 'ເປີດບິນໃໝ່ສຳເລັດ!',
      text: 'ທ່ານສາມາດເພີ່ມລາຍການຂາຍໃນ ' + (currentBill.name) + ' ໄດ້ເລີຍ',
      timer: 1000,
      showConfirmButton: false
    });
    updateActiveBillsUI();
    return;
  }

  resequenceActiveBills();
  var billNumber = activeBills.length + 1;
  var newId = 'BILL-' + Date.now();

  var newBill = {
    id: newId,
    name: 'ບິນທີ ' + billNumber,
    time: new Date().toLocaleTimeString('lo-LA', { hour: '2-digit', minute: '2-digit' }),
    customer: { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' },
    cart: [],
    discount: '0'
  };

  activeBills.push(newBill);
  resequenceActiveBills();

  isBillOpened = true;
  localStorage.setItem('pos_bill_opened', '1');

  loadBillState(newId);

  Swal.fire({
    icon: 'success',
    title: 'ເປີດບິນໃໝ່ສຳເລັດ!',
    text: 'ທ່ານສາມາດເພີ່ມລາຍການຂາຍໃນ ' + newBill.name + ' ໄດ້ເລີຍ',
    timer: 1200,
    showConfirmButton: false
  });
}

function updateActiveBillsUI() {
  // ຖ້າຍັງບໍ່ໄດ້ເປີດບິນ — ສະແດງ 0 ແລະ ບໍ່ໃຫ້ກົດໄດ້
  if (!isBillOpened) {
    $('#activeBillsCountBadge').text('0').show();
    $('#btnActiveBills').prop('disabled', true).addClass('disabled').css('opacity', '0.6');
    $('#currentBillBadge').text('ຍັງບໍ່ທັນເປີດ');
    return;
  }

  var openedCount = 0;
  activeBills.forEach(function(b) {
    var hasItems = b.cart && b.cart.length > 0;
    var hasCustomer = b.customer && b.customer.customer_id;
    if (hasItems || hasCustomer || isBillOpened) {
      openedCount++;
    }
  });

  // ນັບຕາມຈຳນວນ activeBills ຈິງ ຖ້າ isBillOpened
  openedCount = activeBills.length;

  if (openedCount > 0) {
    $('#activeBillsCountBadge').text(openedCount).show();
    $('#btnActiveBills').prop('disabled', false).removeClass('disabled').css('opacity', '1');
  } else {
    $('#activeBillsCountBadge').text('0').show();
    $('#btnActiveBills').prop('disabled', true).addClass('disabled').css('opacity', '0.6');
  }

  var currentBill = activeBills.find(function(b) { return b.id === currentBillId; });
  if (currentBill && isBillOpened) {
    $('#currentBillBadge')
      .text(currentBill.name)
      .removeClass('badge-secondary')
      .addClass('badge-primary');
  }
}

function openActiveBillsModal() {
  if ($('#btnActiveBills').is(':disabled')) return;
  saveCurrentBillState();
  renderActiveBills();
  $('#activeBillsModal').modal('show');
}

function renderActiveBills() {
  var container = $('#activeBillsContainer');
  container.empty();

  if (activeBills.length === 0) {
    container.html(`<div class="text-center py-4 text-muted">ບໍ່ມີບິນທີ່ເປີດຢູ່</div>`);
    return;
  }

  activeBills.forEach(function(bill) {
    var isCurrent = (bill.id === currentBillId);
    var lineCount = (bill.cart || []).length;
    var totalQty  = 0;
    var totalAmount = 0;
    var itemsListHtml = '<div class="my-2 p-2 bg-white border rounded" style="font-size: 0.84rem;">';

    (bill.cart || []).forEach(function(i) {
      totalQty += i.quantity;
      var itemTotal = i.quantity * i.unit_price;
      totalAmount += itemTotal;
      var unitStr = i.unit_name ? (' ' + i.unit_name) : '';
      itemsListHtml += `
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="border-color: #f1f5f9 !important;">
          <span class="text-dark font-weight-bold text-truncate" style="max-width: 180px;">• ${i.product_name}</span>
          <span class="text-nowrap">
            <span class="badge badge-light border text-primary font-weight-bold mr-1" style="font-size: 0.78rem;">x${i.quantity}${unitStr}</span>
            <strong class="text-dark">${itemTotal.toLocaleString()} ₭</strong>
          </span>
        </div>
      `;
    });
    itemsListHtml += '</div>';

    var discountVal = parseFloat((bill.discount || '0').replace(/\D/g, '')) || 0;
    var netTotal = Math.max(0, totalAmount - discountVal);
    var cusName = (bill.customer && bill.customer.customer_name) ? bill.customer.customer_name : 'ລູກຄ້າທົ່ວໄປ';

    var cardHtml = `
      <div class="held-item-card ${isCurrent ? 'border-primary bg-light' : ''}">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div>
            <span class="badge ${isCurrent ? 'badge-primary' : 'badge-secondary'} font-weight-bold mr-1">${bill.name}</span>
            <span class="small text-muted"><i class="fas fa-clock mr-1"></i>${bill.time}</span>
          </div>
          <h5 class="font-weight-bold text-success mb-0">${netTotal.toLocaleString()} ₭</h5>
        </div>
        <div class="small text-muted mb-1">
          <i class="fas fa-user mr-1"></i> ລູກຄ້າ: <strong>${cusName}</strong> | 
          <i class="fas fa-shopping-basket mr-1 ml-1"></i> <strong>${lineCount}</strong> ລາຍການ (${totalQty} ຈຳນວນ)
        </div>
        ${itemsListHtml}
        <div class="d-flex justify-content-end mt-2" style="gap: 6px;">
          ${!isCurrent ? `
            <button class="btn btn-sm btn-primary font-weight-bold" onclick="selectActiveBill('${bill.id}')">
              <i class="fas fa-sign-in-alt mr-1"></i> ເປີດບິນນີ້
            </button>
          ` : '<span class="badge badge-success align-self-center px-2 py-1"><i class="fas fa-check-circle mr-1"></i>ກຳລັງເປີດຢູ່</span>'}
          ${activeBills.length > 1 ? `
            <button class="btn btn-sm btn-outline-danger" onclick="closeActiveBill('${bill.id}')" title="ປິດບິນນີ້">
              <i class="fas fa-times"></i>
            </button>
          ` : ''}
        </div>
      </div>
    `;
    container.append(cardHtml);
  });
}

function selectActiveBill(billId) {
  saveCurrentBillState();
  loadBillState(billId);
  $('#activeBillsModal').modal('hide');
}

function closeActiveBill(billId) {
  if (activeBills.length <= 1) return;
  activeBills = activeBills.filter(function(b) { return b.id !== billId; });
  localStorage.setItem('pos_active_bills', JSON.stringify(activeBills));

  if (currentBillId === billId) {
    currentBillId = activeBills[0].id;
    loadBillState(currentBillId);
  }
  renderActiveBills();
  updateActiveBillsUI();
}

// --- HELD ORDERS (ພັກບິນ) MANAGEMENT ---
function holdCurrentCart() {
  holdCurrentOrder();
}

function holdCurrentOrder() {
  if (cart.length === 0) {
    Swal.fire({ icon: 'warning', title: 'ແຈ້ງເຕືອນ', text: 'ກະຕ່າສິນຄ້າຫວ່າງເປົ່າ ບໍ່ສາມາດພັກບິນໄດ້! ກະລຸນາເພີ່ມສິນຄ້າລົງກະຕ່າກ່ອນ', confirmButtonColor: '#2563eb' });
    return;
  }

  var heldItem = {
    id: 'HOLD-' + Date.now(),
    time: new Date().toLocaleTimeString('lo-LA', { hour: '2-digit', minute: '2-digit' }),
    cart: JSON.parse(JSON.stringify(cart)),
    customer: JSON.parse(JSON.stringify(selectedCustomer)),
    discount: $('#cartDiscount').val()
  };

  heldOrders.push(heldItem);
  localStorage.setItem('pos_held_orders', JSON.stringify(heldOrders));

  cart = [];
  selectedCustomer = { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' };
  $('#cartDiscount').val('0');

  updateSelectedCustomerUI();
  updateCartUI();
  saveCurrentBillState();
  updateHeldOrdersBadge();

  Swal.fire({
    icon: 'success',
    title: 'ພັກບິນສຳເລັດ!',
    timer: 1200,
    showConfirmButton: false
  });
}

function updateHeldOrdersBadge() {
  var count = heldOrders.length;
  if (count > 0) {
    $('#heldCountBadge').text(count).show();
    $('#btnHeldOrders').prop('disabled', false).removeClass('disabled').css('opacity', '1');
  } else {
    $('#heldCountBadge').hide().text('0');
    $('#btnHeldOrders').prop('disabled', true).addClass('disabled').css('opacity', '0.6');
  }
}

function openHeldOrdersModal() {
  if ($('#btnHeldOrders').is(':disabled')) return;
  renderHeldOrders();
  $('#heldOrdersModal').modal('show');
}

function renderHeldOrders() {
  var container = $('#heldOrdersContainer');
  container.empty();

  if (heldOrders.length === 0) {
    container.html(`<div class="text-center py-4 text-muted">ບໍ່ມີບິນທີ່ພັກໄວ້</div>`);
    return;
  }

  heldOrders.forEach(function(order, idx) {
    var lineCount = (order.cart || []).length;
    var totalQty  = 0;
    var totalAmount = 0;
    var itemsListHtml = '<div class="my-2 p-2 bg-white border rounded" style="font-size: 0.84rem;">';

    (order.cart || []).forEach(function(i) {
      totalQty += i.quantity;
      var itemTotal = i.quantity * i.unit_price;
      totalAmount += itemTotal;
      var unitStr = i.unit_name ? (' ' + i.unit_name) : '';
      itemsListHtml += `
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="border-color: #f1f5f9 !important;">
          <span class="text-dark font-weight-bold text-truncate" style="max-width: 220px;">• ${i.product_name}</span>
          <span class="text-nowrap">
            <span class="badge badge-light border text-primary font-weight-bold mr-1" style="font-size: 0.78rem;">x${i.quantity}${unitStr}</span>
            <strong class="text-dark">${itemTotal.toLocaleString()} ₭</strong>
          </span>
        </div>
      `;
    });
    itemsListHtml += '</div>';

    var discountVal = parseFloat((order.discount || '0').replace(/\D/g, '')) || 0;
    var netTotal = Math.max(0, totalAmount - discountVal);

    var cardHtml = `
      <div class="held-item-card">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div>
            <span class="badge badge-warning font-weight-bold mr-1">ບິນພັກ #${idx + 1}</span>
            <span class="small text-muted"><i class="fas fa-clock mr-1"></i>${order.time}</span>
          </div>
          <h5 class="font-weight-bold text-success mb-0">${netTotal.toLocaleString()} ₭</h5>
        </div>
        <div class="small text-muted mb-1">
          <i class="fas fa-user mr-1"></i> ລູກຄ້າ: <strong>${order.customer.customer_name}</strong> | 
          <i class="fas fa-shopping-basket mr-1 ml-1"></i> <strong>${lineCount}</strong> ລາຍການ (${totalQty} ຈຳນວນ)
        </div>
        ${itemsListHtml}
        <div class="d-flex justify-content-end mt-2" style="gap: 6px;">
          <button class="btn btn-sm btn-success font-weight-bold" onclick="restoreHeldOrder('${order.id}')">
            <i class="fas fa-undo mr-1"></i> ເອີ້ນຄືນບິນ
          </button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteHeldOrder('${order.id}')" title="ລຶບ">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </div>
    `;
    container.append(cardHtml);
  });
}

function restoreHeldOrder(orderId) {
  var idx = heldOrders.findIndex(function(o) { return o.id === orderId; });
  if (idx === -1) return;

  var order = heldOrders[idx];
  cart = JSON.parse(JSON.stringify(order.cart));
  selectedCustomer = JSON.parse(JSON.stringify(order.customer));
  $('#cartDiscount').val(order.discount || '0');

  heldOrders.splice(idx, 1);
  localStorage.setItem('pos_held_orders', JSON.stringify(heldOrders));

  updateSelectedCustomerUI();
  updateCartUI();
  saveCurrentBillState();
  updateHeldOrdersBadge();

  $('#heldOrdersModal').modal('hide');
}

function deleteHeldOrder(orderId) {
  heldOrders = heldOrders.filter(function(o) { return o.id !== orderId; });
  localStorage.setItem('pos_held_orders', JSON.stringify(heldOrders));
  renderHeldOrders();
  updateHeldOrdersBadge();
}
</script>
