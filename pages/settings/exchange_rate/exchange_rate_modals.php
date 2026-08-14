<!-- ============================================================
     exchange_rate_modals.php - ຟອມ Modal ເພີ່ມ, ແກ້ໄຂ ແລະ ລຶບອັດຕາແລກປ່ຽນ
     ============================================================ -->

<!-- 1. Modal: ເພີ່ມອັດຕາແລກປ່ຽນເງິນໃໝ່ -->
<div class="modal fade" id="rateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <form action="" method="POST">
        <input type="hidden" name="action" value="save_rate">
        <div class="modal-header bg-primary text-white py-3 px-4">
          <h5 class="modal-title font-weight-bold" style="font-size: 1rem;">
            <i class="fas fa-plus-circle mr-2"></i> ອັບເດດອັດຕາແລກປ່ຽນເງິນໃໝ່
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">1 ບາດ (THB ➔ LAK): <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" name="ex_kip_bath" class="form-control text-right font-weight-bold text-success" placeholder="0" value="720" oninput="formatPriceInput(this)" required style="height: 42px; font-size: 1.05rem;">
              <div class="input-group-append"><span class="input-group-text font-weight-bold">₭</span></div>
            </div>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">1 ໂດລາ (USD ➔ LAK): <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" name="ex_kip_us" class="form-control text-right font-weight-bold text-primary" placeholder="0" value="22000" oninput="formatPriceInput(this)" required style="height: 42px; font-size: 1.05rem;">
              <div class="input-group-append"><span class="input-group-text font-weight-bold">₭</span></div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pb-4 px-4 pt-0">
          <button type="button" class="btn btn-light font-weight-bold px-3" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4" style="border: none;">ບັນທຶກ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 2. Modal: ແກ້ໄຂອັດຕາແລກປ່ຽນເງິນ -->
<div class="modal fade" id="editRateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <form action="" method="POST">
        <input type="hidden" name="action" value="update_rate">
        <input type="hidden" name="rate_id" id="edit_rate_id" value="">
        <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
          <h5 class="modal-title font-weight-bold" style="font-size: 1rem;">
            <i class="fas fa-edit mr-2"></i> ແກ້ໄຂອັດຕາແລກປ່ຽນເງິນ
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">1 ບາດ (THB ➔ LAK): <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" name="ex_kip_bath" id="edit_ex_kip_bath" class="form-control text-right font-weight-bold text-success" placeholder="0" oninput="formatPriceInput(this)" required style="height: 42px; font-size: 1.05rem;">
              <div class="input-group-append"><span class="input-group-text font-weight-bold">₭</span></div>
            </div>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">1 ໂດລາ (USD ➔ LAK): <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" name="ex_kip_us" id="edit_ex_kip_us" class="form-control text-right font-weight-bold text-primary" placeholder="0" oninput="formatPriceInput(this)" required style="height: 42px; font-size: 1.05rem;">
              <div class="input-group-append"><span class="input-group-text font-weight-bold">₭</span></div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pb-4 px-4 pt-0">
          <button type="button" class="btn btn-light font-weight-bold px-3" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4">ອັບເດດ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 3. ຟອມ Hidden ສຳລັບສົ່ງຄ່າລຶບອັດຕາແລກປ່ຽນ -->
<form id="deleteRateForm" action="" method="POST" style="display: none;">
  <input type="hidden" name="action" value="delete_rate">
  <input type="hidden" name="rate_id" id="delete_rate_id" value="">
</form>