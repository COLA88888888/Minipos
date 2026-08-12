<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <form id="addCategoryForm" action="" method="POST" novalidate>
        <input type="hidden" name="action" value="add_category">
        <div class="modal-header bg-primary">
          <h5 class="modal-title font-weight-bold text-white" style="font-family: 'Noto Sans Lao Looped';">
            <i class="fas fa-folder-plus mr-2"></i> ເພີ່ມປະເພດສິນຄ້າ
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark mb-1">ລະຫັດປະເພດສິນຄ້າ</label>
            <input type="number" id="add_cat_id" name="category_id" class="form-control bg-light" style="border-radius: 8px; height: 42px;" min="1" readonly>
          </div>
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark mb-1">ຊື່ປະເພດສິນຄ້າ <span class="text-danger">*</span></label>
            <input type="text" id="add_cat_name" name="category_name" class="form-control" placeholder="ປ້ອນຊື່ປະເພດສິນຄ້າ" style="border-radius: 8px; height: 42px;">
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark mb-1">ລາຍລະອຽດ</label>
            <textarea name="description" class="form-control" rows="3" placeholder="ປ້ອນລາຍລະອຽດ(ບໍ່ບັງຄັບ)" style="border-radius: 8px;"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light font-weight-bold px-4" style="border-radius: 6px;" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="button" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 6px;" onclick="submitAddCategory()">
            <i class="fas fa-save mr-1"></i> ບັນທຶກ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function submitAddCategory() {
  var catId   = $('#add_cat_id').val().trim();
  var catName = $('#add_cat_name').val().trim();

  if (catId === '' || parseInt(catId) < 1) {
    Swal.fire({
      icon: 'warning',
      title: 'ກະລຸນາປ້ອນລະຫັດ',
      text: 'ລະຫັດປະເພດສິນຄ້າບໍ່ສາມາດຫວ່າງໄດ້!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    });
    $('#add_cat_id').focus();
    return;
  }

  if (catName === '') {
    Swal.fire({
      icon: 'warning',
      title: 'ກະລຸນາປ້ອນຊື່',
      text: 'ຊື່ປະເພດສິນຄ້າບໍ່ສາມາດຫວ່າງໄດ້!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    });
    $('#add_cat_name').focus();
    return;
  }

  $('#addCategoryForm').submit();
}

// Submit form when Enter is pressed inside add modal inputs
$(document).ready(function() {
  $(document).on('keydown', '#addCategoryModal input', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      submitAddCategory();
    }
  });
});
</script>
