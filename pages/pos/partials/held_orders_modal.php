<!-- Modal: Held Orders List -->
<div class="modal fade" id="heldOrdersModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <div class="modal-header bg-info text-white py-3 px-4">
        <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao Looped';">
          <i class="fas fa-history mr-2"></i> ລາຍການບິນທີ່ພັກໄວ້
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body p-4" style="max-height: 420px; overflow-y: auto;">
        <div id="heldOrdersContainer">
          <!-- Dynamically populated held cards -->
        </div>
      </div>
      <div class="modal-footer border-0 bg-light p-3 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold" onclick="clearAllHeldOrders()">
          <i class="fas fa-trash mr-1"></i> ລ້າງບິນພັກທັງໝົດ
        </button>
        <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">ປິດ</button>
      </div>
    </div>
  </div>
</div>
