<?php
// pages/branches/partials/js/branches_js.php
if (!defined('MINIPOS_APP')) {
    define('MINIPOS_APP', true);
}
?>
<script>
var I18N_BRANCHES = <?php echo tjson([
    'branches.action_close' => 'ປິດສາຂາ',
    'branches.action_open' => 'ເປີດໃຊ້ງານສາຂາ',
    'branches.confirm_close_text' => 'ທ່ານຕ້ອງການປິດສາຂາ "{name}" ບໍ່?',
    'branches.confirm_open_text' => 'ທ່ານຕ້ອງການເປີດໃຊ້ງານສາຂາ "{name}" ບໍ່?',
    'branches.yes_prefix' => 'ແມ່ນແລ້ວ, ',
    'branches.btn_cancel' => 'ຍົກເລີກ',
    'branches.delete_title' => 'ລົບສາຂາ?',
    'branches.confirm_delete_text' => 'ທ່ານຕ້ອງການລົບສາຂາ "{name}" ຫຼືບໍ່? ການກະທຳນີ້ບໍ່ສາມາດຍົກເລີກໄດ້!',
    'branches.btn_delete_now' => 'ລົບເລີຍ',
]); ?>;

function openEditBranchModal(data) {
  if (!data) return;
  $('#edit_store_id').val(data.store_id);
  $('#edit_store_code').val(data.store_code || '');
  $('#edit_store_name').val(data.store_name || '');
  $('#edit_store_tel').val(data.tel || '');
  $('#edit_store_address').val(data.address || '');
  $('#edit_is_main').prop('checked', parseInt(data.is_main) === 1);
  $('#editBranchModal').modal('show');
}

function confirmToggleBranchStatus(id, name, status) {
  var actionText = (status === 'active') ? I18N_BRANCHES['branches.action_close'] : I18N_BRANCHES['branches.action_open'];
  var questionText = (status === 'active')
    ? I18N_BRANCHES['branches.confirm_close_text'].replace('{name}', name)
    : I18N_BRANCHES['branches.confirm_open_text'].replace('{name}', name);
  var iconType = (status === 'active') ? 'warning' : 'question';
  var confirmBtnColor = (status === 'active') ? '#ef4444' : '#10b981';

  Swal.fire({
    title: actionText + '?',
    text: questionText,
    icon: iconType,
    showCancelButton: true,
    confirmButtonColor: confirmBtnColor,
    cancelButtonColor: '#64748b',
    confirmButtonText: I18N_BRANCHES['branches.yes_prefix'] + actionText,
    cancelButtonText: I18N_BRANCHES['branches.btn_cancel']
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '?toggle_status_id=' + id;
    }
  });
}

function confirmDeleteBranch(id, name) {
  Swal.fire({
    title: I18N_BRANCHES['branches.delete_title'],
    text: I18N_BRANCHES['branches.confirm_delete_text'].replace('{name}', name),
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> ' + I18N_BRANCHES['branches.btn_delete_now'],
    cancelButtonText: I18N_BRANCHES['branches.btn_cancel']
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '?delete_branch_id=' + id;
    }
  });
}
</script>
