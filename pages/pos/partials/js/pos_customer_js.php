<script>
// --- CUSTOMER SELECTION & QUICK ADD CUSTOMER ---
function updateSelectedCustomerUI() {
  if (selectedCustomer && selectedCustomer.customer_id) {
    $('#selectedCustomerDisplay').text(selectedCustomer.customer_name);
  } else {
    $('#selectedCustomerDisplay').text('ລູກຄ້າທົ່ວໄປ');
  }
}

function selectCustomer(cus) {
  if (cus) {
    selectedCustomer = {
      customer_id: cus.customer_id,
      customer_name: cus.customer_name,
      phone: cus.phone || ''
    };
  } else {
    selectedCustomer = { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' };
  }
  updateSelectedCustomerUI();
  saveCurrentBillState();
  $('#selectCustomerModal').modal('hide');
}

function openCustomerSelectModal() {
  $('#customerSearchInput').val('');
  filterCustomersList();
  $('#selectCustomerModal').modal('show');
}

function filterCustomersList() {
  var q = ($('#customerSearchInput').val() || '').toLowerCase().trim();
  var tbody = $('#customerTableBody');
  tbody.empty();

  var filtered = allCustomers.filter(function(c) {
    var name = (c.customer_name || '').toLowerCase();
    var phone = (c.phone || '').toLowerCase();
    var code = (c.customer_code || '').toLowerCase();
    return name.indexOf(q) !== -1 || phone.indexOf(q) !== -1 || code.indexOf(q) !== -1;
  });

  if (filtered.length === 0) {
    tbody.append(`<tr><td colspan="5" class="text-center text-muted py-3">ບໍ່ພົບຂໍ້ມູນລູກຄ້າ</td></tr>`);
    return;
  }

  filtered.forEach(function(c) {
    var jsonStr = JSON.stringify(c).replace(/'/g, "&apos;");
    tbody.append(`
      <tr>
        <td><span class="badge badge-light border">${c.customer_code || '-'}</span></td>
        <td class="font-weight-bold text-dark">${c.customer_name}</td>
        <td>${c.phone || '-'}</td>
        <td class="small text-muted">${c.address || '-'}</td>
        <td class="text-center">
          <button type="button" class="btn" onclick='selectCustomer(${jsonStr})' style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important; background-color: #2563eb !important; color: #ffffff !important; border: none !important; border-radius: 20px !important; padding: 5px 14px !important; font-size: 0.82rem !important; font-weight: 700 !important; box-shadow: 0 3px 8px rgba(37, 99, 235, 0.30) !important; cursor: pointer !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 4px !important; line-height: 1.2 !important; white-space: nowrap !important;">
            <i class="fas fa-check-circle mr-1" style="color: #ffffff !important; font-size: 0.85rem !important;"></i> <span style="color: #ffffff !important;">ເລືອກ</span>
          </button>
        </td>
      </tr>
    `);
  });
}

var matchedExistingCustomer = null;

function checkExistingCustomerByPhone() {
  var rawPhone = ($('#newCusPhone').val() || '').trim();
  var cleanedPhone = rawPhone.replace(/\D/g, '');
  
  matchedExistingCustomer = null;
  $('#existingCustomerAlert').addClass('d-none');
  
  if (cleanedPhone.length >= 6) {
    var found = allCustomers.find(function(c) {
      if (!c.phone) return false;
      var p = c.phone.replace(/\D/g, '');
      return p.length >= 6 && (p === cleanedPhone || p.endsWith(cleanedPhone) || cleanedPhone.endsWith(p));
    });

    if (found) {
      matchedExistingCustomer = found;
      $('#existingCusInfoText').text(found.customer_name + ' (' + (found.phone || '-') + ')');
      $('#existingCustomerAlert').removeClass('d-none');
      $('#newCusName').val(found.customer_name);
      $('#newCusCode').val(found.customer_code || '');
      $('#newCusAddress').val(found.address || '');
    }
  }
}

$(document).on('click', '#btnSelectExistingCus', function() {
  if (matchedExistingCustomer) {
    selectCustomer(matchedExistingCustomer);
    $('#addCustomerModal').modal('hide');
    Swal.fire({
      icon: 'success',
      title: 'ເລືອກລູກຄ້າເກົ່າສຳເລັດ!',
      text: 'ເລືອກ: ' + matchedExistingCustomer.customer_name + ' ແລ້ວ',
      timer: 1500,
      showConfirmButton: false
    });
  }
});

function openAddCustomerModal() {
  matchedExistingCustomer = null;
  $('#existingCustomerAlert').addClass('d-none');
  $('#newCusCode').val('');
  $('#newCusName').val('');
  $('#newCusPhone').val('');
  $('#newCusAddress').val('');
  $('#addCustomerModal').modal('show');
  setTimeout(function() { $('#newCusPhone').focus(); }, 400);
}

function submitQuickAddCustomer() {
  var phone = $('#newCusPhone').val().trim();
  var name  = $('#newCusName').val().trim();

  // Check if phone matches an existing customer
  if (phone) {
    var cleanedPhone = phone.replace(/\D/g, '');
    var existing = allCustomers.find(function(c) {
      if (!c.phone) return false;
      var p = c.phone.replace(/\D/g, '');
      return p.length >= 6 && p === cleanedPhone;
    });

    if (existing) {
      selectCustomer(existing);
      $('#addCustomerModal').modal('hide');
      Swal.fire({
        icon: 'info',
        title: 'ພົບຂໍ້ມູນລູກຄ້າເກົ່າ!',
        text: 'ລະບົບໄດ້ເລືອກລູກຄ້າເກົ່າ: "' + existing.customer_name + '" ໃຫ້ອັດຕະໂນມັດແລ້ວ',
        confirmButtonColor: '#2563eb'
      });
      return;
    }
  }

  if (!name) {
    Swal.fire({ icon: 'warning', title: 'ແຈ້ງເຕືອນ', text: 'ກະລຸນາປ້ອນຊື່ລູກຄ້າ!' });
    return;
  }

  $.ajax({
    url: '',
    type: 'POST',
    data: {
      action: 'add_customer_ajax',
      customer_code: $('#newCusCode').val().trim(),
      customer_name: name,
      phone: phone,
      address: $('#newCusAddress').val().trim()
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        allCustomers.push(res.customer);
        selectCustomer(res.customer);
        $('#addCustomerModal').modal('hide');
        Swal.fire({ icon: 'success', title: 'ເພີ່ມລູກຄ້າໃໝ່ສຳເລັດ!', timer: 1200, showConfirmButton: false });
      } else {
        Swal.fire({ icon: 'error', title: 'ຜິດພາດ', text: res.message });
      }
    },
    error: function() {
      Swal.fire({ icon: 'error', title: 'ຜິດພາດ', text: 'ບໍ່ສາມາດບັນທຶກຂໍ້ມູນລູກຄ້າໄດ້!' });
    }
  });
}
</script>
