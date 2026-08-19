<?php
// pages/branches/partials/js/branches_js.php
if (!defined('MINIPOS_APP')) {
    define('MINIPOS_APP', true);
}
?>
<script>
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
  var actionText = (status === 'active') ? 'ປິດສາຂາ' : 'ເປີດໃຊ້ງານສາຂາ';
  var questionText = (status === 'active') 
    ? 'ທ່ານຕ້ອງການປິດສາຂາ "' + name + '" ບໍ່?' 
    : 'ທ່ານຕ້ອງການເປີດໃຊ້ງານສາຂາ "' + name + '" ບໍ່?';
  var iconType = (status === 'active') ? 'warning' : 'question';
  var confirmBtnColor = (status === 'active') ? '#ef4444' : '#10b981';

  Swal.fire({
    title: actionText + '?',
    text: questionText,
    icon: iconType,
    showCancelButton: true,
    confirmButtonColor: confirmBtnColor,
    cancelButtonColor: '#64748b',
    confirmButtonText: 'ແມ່ນແລ້ວ, ' + actionText,
    cancelButtonText: 'ຍົກເລີກ'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '?toggle_status_id=' + id;
    }
  });
}

function confirmDeleteBranch(id, name) {
  Swal.fire({
    title: 'ລົບສາຂາ?',
    text: 'ທ່ານຕ້ອງການລົບສາຂາ "' + name + '" ຫຼືບໍ່? ການກະທຳນີ້ບໍ່ສາມາດຍົກເລີກໄດ້!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> ລົບເລີຍ',
    cancelButtonText: 'ຍົກເລີກ'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '?delete_branch_id=' + id;
    }
  });
}
</script>
