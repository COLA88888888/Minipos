<!-- Modal: Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <form id="editCategoryForm" action="" method="POST" novalidate>
        <input type="hidden" name="action" value="edit_category">
        <input type="hidden" name="category_id" id="edit_cat_id">
        <div class="modal-header bg-primary">
          <h5 class="modal-title font-weight-bold text-white" style="font-family: 'Noto Sans Lao Looped';">
            <i class="fas fa-edit mr-2"></i> ແກ້ໄຂປະເພດສິນຄ້າ
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark mb-1">ຊື່ປະເພດສິນຄ້າ <span class="text-danger">*</span></label>
            <input type="text" name="category_name" id="edit_cat_name" class="form-control" style="border-radius: 8px; height: 42px;">
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark mb-1">ລາຍລະອຽດ</label>
            <textarea name="description" id="edit_cat_desc" class="form-control" rows="3" style="border-radius: 8px;"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light font-weight-bold px-4" style="border-radius: 6px;" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="button" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 6px;" onclick="submitEditCategory()">
            <i class="fas fa-save mr-1"></i> ອັບເດດ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function submitEditCategory() {
  var catName = $('#edit_cat_name').val().trim();

  if (catName === '') {
    Swal.fire({
      icon: 'warning',
      title: 'ກະລຸນາປ້ອນຊື່',
      text: 'ຊື່ປະເພດສິນຄ້າບໍ່ສາມາດຫວ່າງໄດ້!',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'ຕົກລົງ'
    });
    $('#edit_cat_name').focus();
    return;
  }

  $('#editCategoryForm').submit();
}
</script>
