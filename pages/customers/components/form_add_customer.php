<!-- Modal: Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <form id="addCustomerForm" action="" method="POST" novalidate>
        <input type="hidden" name="action" value="add_customer">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao Looped';">
            <i class="fas fa-user-plus mr-2"></i> ເພີ່ມຂໍ້ມູນລູກຄ້າໃໝ່
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark mb-1">ລະຫັດລູກຄ້າ <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light"><i class="fas fa-id-badge text-primary"></i></span>
                </div>
                <input type="text" id="add_customer_code" name="customer_code" class="form-control" placeholder="ຕົວຢ່າງ: CUST-001" style="border-radius: 0 8px 8px 0; height: 42px;">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark mb-1">ຊື່ ແລະ ນາມສະກຸນລູກຄ້າ <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light"><i class="fas fa-user text-info"></i></span>
                </div>
                <input type="text" id="add_customer_name" name="customer_name" class="form-control" placeholder="ປ້ອນຊື່ ແລະ ນາມສະກຸນ" style="border-radius: 0 8px 8px 0; height: 42px;">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark mb-1">ເບີໂທຕິດຕໍ່</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light"><i class="fas fa-phone text-success"></i></span>
                </div>
                <input type="text" id="add_phone" name="phone" class="form-control" placeholder="020 XXXXXXXX" style="border-radius: 0 8px 8px 0; height: 42px;">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="font-weight-bold text-dark mb-1">ອີເມວ (Email)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light"><i class="fas fa-envelope text-warning"></i></span>
                </div>
                <input type="email" id="add_email" name="email" class="form-control" placeholder="example@domain.com" style="border-radius: 0 8px 8px 0; height: 42px;">
              </div>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark mb-1">ທີ່ຢູ່</label>
            <textarea name="address" id="add_address" class="form-control" rows="2" placeholder="ປ້ອນທີ່ຢູ່ລູກຄ້າ (ບ້ານ, ເມືອງ, ແຂວງ)" style="border-radius: 8px;"></textarea>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark mb-1">ໝາຍເຫດ</label>
            <textarea name="notes" id="add_notes" class="form-control" rows="2" placeholder="ປ້ອນໝາຍເຫດເພີ່ມເຕີມ (ຖ້າມີ)" style="border-radius: 8px;"></textarea>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light font-weight-bold px-4" style="border-radius: 6px;" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="button" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 6px;" onclick="submitAddCustomer()">
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
      confirmButtonColor: '#2563eb',
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
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    });
    $('#add_customer_name').focus();
    return;
  }

  $('#addCustomerForm').submit();
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
