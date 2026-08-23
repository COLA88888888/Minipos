<!-- Modal: Active / Opened Bills List -->
<div class="modal fade" id="activeBillsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <div class="modal-header bg-primary text-white py-3 px-4">
        <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao', 'Souliyo', 'Boon', sans-serif;">
          <i class="fas fa-list-alt mr-2"></i> ລາຍການບິນທີ່ເປີດຢູ່
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body p-4" style="max-height: 420px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="font-weight-bold mb-0 text-dark">ລາຍການບິນທີ່ກຳລັງເປີດຢູ່:</h6>
          <button type="button" class="btn btn-sm btn-success font-weight-bold" onclick="createNewBillModal()" data-dismiss="modal">
            <i class="fas fa-plus-circle mr-1"></i> ເປີດບິນໃໝ່
          </button>
        </div>
        <div id="activeBillsContainer">
          <!-- Dynamically populated active bills -->
        </div>
      </div>
      <div class="modal-footer border-0 bg-light p-3">
        <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">ປິດ</button>
      </div>
    </div>
  </div>
</div>
