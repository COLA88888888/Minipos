<!-- Modal: Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <form id="addCustomerForm" action="" method="POST" novalidate>
        <input type="hidden" name="action" value="add_customer">
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #2c5aa0, #244886);">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao', 'Souliyo', 'Boon', sans-serif;">
            <i class="fas fa-user-plus mr-2"></i> ເພີ່ມຂໍ້ມູນລູກຄ້າ
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark mb-1">ລະຫັດລູກຄ້າ <span class="text-danger">*</span></label>
              <input type="text" id="add_customer_code" name="customer_code" class="form-control bg-light font-weight-bold" readonly placeholder="CUST-001" style="border-radius: 8px; height: 42px; color: #2563eb;">
            </div>

            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark mb-1">ຊື່ ແລະ ນາມສະກຸນ <span class="text-danger">*</span></label>
              <input type="text" id="add_customer_name" name="customer_name" class="form-control" placeholder="ປ້ອນຊື່ ແລະ ນາມສະກຸນ" style="border-radius: 8px; height: 42px;">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark mb-1">ເບີໂທຕິດຕໍ່</label>
              <input type="text" id="add_phone" name="phone" class="form-control" placeholder="020 XXXXXXXX" style="border-radius: 8px; height: 42px;">
            </div>

            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark mb-1">ເລກບັດສະມາຊິກ</label>
              <input type="text" id="add_member_card" name="member_card" class="form-control" placeholder="ກະລຸນາປ້ອນເລກບັດສະມາຊິກ" style="border-radius: 8px; height: 42px;">
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark mb-1">ສາຂາ</label>
            <select name="store_id" id="add_store_id" class="form-control" style="border-radius: 8px; height: 42px;" <?php echo (!$isAdmin && !$isMain) ? 'disabled' : ''; ?>>
              <?php foreach ($stores as $st): ?>
                <option value="<?php echo $st['store_id']; ?>" <?php echo (($filter_store > 0 ? $filter_store : $userStoreId) == $st['store_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($st['store_name']); ?> <?php echo !empty($st['is_main']) ? '(ສາງຫຼັກ)' : ''; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (!$isAdmin && !$isMain): ?>
              <input type="hidden" name="store_id" value="<?php echo $userStoreId; ?>">
            <?php endif; ?>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark mb-1">ໝາຍເຫດ</label>
            <textarea name="notes" id="add_notes" class="form-control" rows="3" placeholder="ປ້ອນໝາຍເຫດເພີ່ມເຕີມ (ຖ້າມີ)" style="border-radius: 8px;"></textarea>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light font-weight-bold px-4" style="border-radius: 6px;" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="button" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 6px; background: linear-gradient(135deg, #2c5aa0, #244886); border: none;" onclick="submitAddCustomer()">
            <i class="fas fa-save mr-1"></i> ບັນທຶກ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function submitAddCustomer() {
  var code = $('#add_customer_code').val().trim();
  var name = $('#add_customer_name').val().trim();

  if (code === '') {
    Swal.fire({
      icon: 'warning',
      title: 'ກະລຸນາປ້ອນລະຫັດ',
      text: 'ລະຫັດລູກຄ້າບໍ່ສາມາດຫວ່າງໄດ້!',
      confirmButtonColor: '#244886',
      confirmButtonText: 'ຕົກລົງ'
    });
    $('#add_customer_code').focus();
    return;
  }

  if (name === '') {
    Swal.fire({
      icon: 'warning',
      title: 'ກະລຸນາປ້ອນຊື່',
      text: 'ຊື່ ແລະ ນາມສະກຸນລູກຄ້າບໍ່ສາມາດຫວ່າງໄດ້!',
      confirmButtonColor: '#244886',
      confirmButtonText: 'ຕົກລົງ'
    });
    $('#add_customer_name').focus();
    return;
  }

  var formData = $('#addCustomerForm').serialize() + '&is_ajax=1';
  $.post('customers.php', formData, function(res) {
    if (res.success) {
      $('#addCustomerModal').modal('hide');
      $('#addCustomerForm')[0].reset();
      refreshCustomerTable();
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: res.message || 'ເພີ່ມຂໍ້ມູນລູກຄ້າສຳເລັດ!',
        showConfirmButton: false,
        timer: 1500
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'ຜິດພາດ',
        text: res.message || 'ຜິດພາດ!',
        confirmButtonColor: '#244886'
      });
    }
  }, 'json');
}

$(document).ready(function() {
  $(document).on('keydown', '#addCustomerModal input', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      submitAddCustomer();
    }
  });
});
</script>
