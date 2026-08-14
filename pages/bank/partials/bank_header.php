<?php
// pages/bank/partials/bank_header.php
if (!defined('MINIPOS_APP')) {
    define('MINIPOS_APP', true);
}
?>
<!-- Bank Management Header -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
  <div>
    <h5 class="font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
      <i class="fas fa-university text-primary mr-2"></i> ຈັດການທະນາຄານ
    </h5>
  </div>
  <div class="d-flex align-items-center mt-2 mt-md-0" style="gap: 6px;">
    <button type="button" class="btn btn-sm btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#addBankAccountModal" style="border-radius: 6px; font-size: 0.82rem; padding: 6px 14px;">
      <i class="fas fa-plus-circle mr-1"></i> ເພີ່ມບັນຊີທະນາຄານ
    </button>
  </div>
</div>

<?php if (!empty($message)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: '<?php echo ($message_type === "success") ? "success" : "warning"; ?>',
        title: '<?php echo ($message_type === "success") ? "ດຳເນີນການສຳເລັດ!" : "ແຈ້ງເຕືອນ!"; ?>',
        text: <?php echo json_encode($message); ?>,
        confirmButtonColor: '#0284c7',
        confirmButtonText: 'ຕົກລົງ'
      });
    });
  </script>
<?php endif; ?>
