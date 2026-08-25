<!-- ============================================================
     exchange_rate_js.php - JavaScript ຄິດໄລ່ ແລະ ຈັດການ Event ໜ້າອັດຕາແລກປ່ຽນ
     ============================================================ -->
<script>
// ຟັງຊັ້ນຈັດຮູບແບບຕົວເລກຈຳນວນເງິນໃຫ້ມີ ຈຸດ (Comma) ເວລາພິມ
function formatPriceInput(input) {
  var val = input.value.replace(/\D/g, '');
  input.value = val === '' ? '' : Number(val).toLocaleString('en-US');
}

// ຟັງຊັ້ນເປີດ Modal ແກ້ໄຂອັດຕາແລກປ່ຽນ ພ້ອມໂຫຼດຄ່າເກົ່າໃສ່ Input
function editRate(id, thb, usd, cny) {
  document.getElementById('edit_rate_id').value = id;
  document.getElementById('edit_ex_kip_bath').value = thb;
  document.getElementById('edit_ex_kip_us').value = usd;
  if (document.getElementById('edit_ex_kip_cn')) {
    document.getElementById('edit_ex_kip_cn').value = cny || '0';
  }
  $('#editRateModal').modal('show');
}

// ຟັງຊັ້ນ SweetAlert ຢືນຢັນການລຶບອັດຕາແລກປ່ຽນ
function deleteRate(id) {
  Swal.fire({
    title: 'ຢືນຢັນການລຶບ?',
    text: 'ທ່ານຕ້ອງການລຶບປະຫວັດອັດຕາແລກປ່ຽນນີ້ແທ້ຫຼືບໍ່?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'ລຶບເລີຍ',
    cancelButtonText: 'ຍົກເລີກ'
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('delete_rate_id').value = id;
      document.getElementById('deleteRateForm').submit();
    }
  });
}
</script>