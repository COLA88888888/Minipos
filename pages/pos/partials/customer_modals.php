<!-- Modal: Select Customer -->
<div class="modal fade" id="selectCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <div class="modal-header bg-primary text-white py-3 px-4">
        <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao Looped';">
          <i class="fas fa-user-friends mr-2"></i> ເລືອກລູກຄ້າ / ສະມາຊິກ
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body p-4">
        <div class="row mb-3 align-items-center">
          <div class="col-md-8 mb-2">
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span></div>
              <input type="text" id="customerSearchInput" class="form-control" placeholder="ຄົ້ນຫາຊື່, ເບີໂທ ຫຼື ລະຫັດລູກຄ້າ..." oninput="filterCustomersList()">
            </div>
          </div>
          <div class="col-md-4 mb-2 text-right">
            <button class="btn btn-success font-weight-bold w-100" onclick="openAddCustomerModal()">
              <i class="fas fa-plus-circle mr-1"></i> ເພີ່ມລູກຄ້າໃໝ່
            </button>
          </div>
        </div>

        <div class="table-responsive" style="max-height: 350px;">
          <table class="table table-hover table-striped border-top mb-0">
            <thead class="bg-light">
              <tr>
                <th>ລະຫັດ</th>
                <th>ຊື່ລູກຄ້າ</th>
                <th>ເບີໂທ</th>
                <th>ທີ່ຢູ່</th>
                <th class="text-center">ເລືອກ</th>
              </tr>
            </thead>
            <tbody id="customerTableBody">
              <!-- Dynamically populated -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light p-3">
        <button type="button" class="btn btn-primary font-weight-bold px-3 py-2" onclick="selectCustomer(null)" data-dismiss="modal" style="border-radius: 8px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; box-shadow: 0 3px 10px rgba(37, 99, 235, 0.30);">
          <i class="fas fa-user-check mr-1.5"></i> ເລືອກລູກຄ້າທົ່ວໄປ
        </button>
        <button type="button" class="btn btn-secondary font-weight-bold px-3 py-2" data-dismiss="modal" style="border-radius: 8px;">ປິດ</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Quick Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <div class="modal-header bg-success text-white py-3 px-4">
        <h5 class="modal-title font-weight-bold" style="font-family: 'Noto Sans Lao Looped';">
          <i class="fas fa-user-plus mr-2"></i> ເພີ່ມຂໍ້ມູນລູກຄ້າໃໝ່
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body p-4">
        <form id="quickAddCustomerForm">
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">ລະຫັດລູກຄ້າ:</label>
            <input type="text" id="newCusCode" class="form-control" placeholder="CUS-XXXX">
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">ຊື່ ແລະ ນາມສະກຸນ: <span class="text-danger">*</span></label>
            <input type="text" id="newCusName" class="form-control" placeholder="ປ້ອນຊື່ລູກຄ້າ" required>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">ເບີໂທລະສັບ:</label>
            <input type="text" id="newCusPhone" class="form-control" placeholder="020 XXXXXXXX">
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold small text-dark">ທີ່ຢູ່:</label>
            <input type="text" id="newCusAddress" class="form-control" placeholder="ເມືອງ, ແຂວງ...">
          </div>
        </form>
      </div>
      <div class="modal-footer border-0 bg-light p-3">
        <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">ຍົກເລີກ</button>
        <button type="button" class="btn btn-success font-weight-bold px-4" onclick="submitQuickCustomer()">
          <i class="fas fa-save mr-1"></i> ບັນທຶກ
        </button>
      </div>
    </div>
  </div>
</div>
