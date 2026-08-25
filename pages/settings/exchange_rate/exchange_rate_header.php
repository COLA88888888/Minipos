<!-- ============================================================
     exchange_rate_header.php - ສ່ວນหัวຂໍ້ໜ້າ ແລະ ການແຈ້ງເຕືອນອັດຕາແລກປ່ຽນ
     ============================================================ -->
<?php
$bp = isset($base_path) ? rtrim($base_path, '/') . '/' : '../../../';
?>
<!-- ດຶງ CSS ສຳລັບ Flag Icons -->
<link rel="stylesheet" href="<?php echo $bp; ?>plugins/flag-icon-css/css/flag-icon.min.css">

<!-- ແຖບหัวຂໍ້ໜ້າ ແລະ ປຸ່ມເພີ່ມອັດຕາແລກປ່ຽນໃໝ່ -->
<div class="row mb-3 align-items-center">
  <div class="col-sm-6">
    <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
      <i class="fas fa-exchange-alt text-primary mr-2"></i> <?php echo htmlspecialchars(t('exchange_rate.header_title', 'ອັດຕາແລກປ່ຽນເງິນ (Exchange Rates)')); ?>
    </h5>
  </div>
  <div class="col-sm-6 text-right">
    <?php if (hasPermission('exchange_rate', 'add')): ?>
      <button type="button" class="btn btn-primary px-3.5 font-weight-bold" data-toggle="modal" data-target="#rateModal" style="border-radius: 6px; height: 38px; border: none; background: linear-gradient(135deg, #2c5aa0, #244886);">
        <i class="fas fa-plus-circle mr-1.5"></i> <?php echo htmlspecialchars(t('exchange_rate.btn_add_new', 'ອັບເດດອັດຕາໃໝ່')); ?>
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- ສະແດງຂໍ້ຄວາມແຈ້ງເຕືອນ (SweetAlert2) ເມື່ອດຳເນີນການສຳເລັດ ຫຼື ຜິດພາດ -->
<?php if ($message !== ''): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: '<?php echo $message_type === "success" ? "success" : "error"; ?>',
        title: '<?php echo $message_type === "success" ? t('exchange_rate.swal_success_title', 'ສຳເລັດ') : t('exchange_rate.swal_error_title', 'ແຈ້ງເຕືອນ'); ?>',
        text: '<?php echo addslashes($message); ?>',
        confirmButtonColor: '#2563eb'
      });
    });
  </script>
<?php endif; ?>