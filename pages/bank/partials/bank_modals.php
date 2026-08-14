<?php
// pages/bank/partials/bank_modals.php
if (!defined('MINIPOS_APP')) {
    define('MINIPOS_APP', true);
}
?>

<!-- ADD / EDIT BANK ACCOUNT MODAL -->
<div class="modal fade" id="addBankAccountModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 14px; overflow: hidden;">
      <form id="bankAccountForm" method="POST" enctype="multipart/form-data" onsubmit="return validateBankForm(event)">
        <input type="hidden" name="action" id="form_bank_action" value="add_bank_account">
        <input type="hidden" name="bank_id" id="form_bank_id" value="0">
        
        <div class="modal-header bg-primary text-white py-3 px-4">
          <h5 class="modal-title font-weight-bold" id="bankModalTitle">
            <i class="fas fa-university mr-2"></i> ເພີ່ມບັນຊີທະນາຄານໃໝ່
          </h5>
          <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close" onclick="resetBankForm()">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4" style="font-size: 0.9rem;">
          
          <div class="form-group">
            <label class="font-weight-bold">ຊື່ທະນາຄານ: <span class="text-danger">*</span></label>
            <input type="text" name="bank_name" id="form_bank_name" class="form-control font-weight-bold text-dark" placeholder="BCEL, LDB, JDB..." required autocomplete="off" style="border-radius: 8px;">
          </div>

          <div class="form-group">
            <label class="font-weight-bold">ເລກບັນຊີ: <span class="text-danger">*</span></label>
            <input type="text" name="account_number" id="form_account_number" class="form-control font-weight-bold text-primary" placeholder="ກະລຸນາປ້ອນເລກບັນຊີທະນາຄານ..." required autocomplete="off" style="border-radius: 8px;">
          </div>

          <div class="form-group">
            <label class="font-weight-bold">ຊື່ບັນຊີ: <span class="text-danger">*</span></label>
            <input type="text" name="account_name" id="form_account_name" class="form-control" placeholder="ກະລຸນາປ້ອນຊື່ບັນຊີທະນາຄານ..." required autocomplete="off" style="border-radius: 8px;">
          </div>

          <div class="row">
            <div class="col-6">
              <div class="form-group mb-0">
                <label class="font-weight-bold" style="font-size: 0.82rem;">ຮູບໂລໂກ້ທະນາຄານ:</label>
                <input type="file" name="bank_logo" class="form-control-file" accept="image/*">
              </div>
            </div>
            <div class="col-6">
              <div class="form-group mb-0">
                <label class="font-weight-bold" style="font-size: 0.82rem;">ຮູບ QR Code:</label>
                <input type="file" name="qr_code_img" class="form-control-file" accept="image/*">
              </div>
            </div>
          </div>

          <div class="form-group mt-3 mb-0">
            <label class="font-weight-bold">ສະຖານະ:</label>
            <select name="is_active" id="form_is_active" class="form-control" style="border-radius: 8px;">
              <option value="1">ເປີດໃຊ້ງານ</option>
              <option value="0">ປິດຕິດ</option>
            </select>
          </div>

        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" onclick="resetBankForm()">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4"><i class="fas fa-save mr-1"></i> ບັນທຶກ</button>
        </div>
      </form>
    </div>
  </div>
</div>
