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
          <button class="btn btn-select-customer font-weight-bold" onclick='selectCustomer(${jsonStr})'>
            <i class="fas fa-check-circle"></i> ເລືອກ
          </button>
        </td>
      </tr>
    `);
  });
}

function openAddCustomerModal() {
  $('#newCusCode').val('');
  $('#newCusName').val('');
  $('#newCusPhone').val('');
  $('#newCusAddress').val('');
  $('#addCustomerModal').modal('show');
}

function submitQuickAddCustomer() {
  var name = $('#newCusName').val().trim();
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
      phone: $('#newCusPhone').val().trim(),
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
