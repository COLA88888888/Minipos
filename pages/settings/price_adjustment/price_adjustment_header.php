<!-- Top Header Row -->
<div class="row mb-3 align-items-center">
  <div class="col-sm-6">
    <h5 class="m-0 font-weight-bold" style="color: #1e293b; font-size: 1.15rem;">
      <i class="fas fa-tags text-primary mr-2"></i> ປັບລາຄາຂາຍສິນຄ້າ
    </h5>
  </div>
</div>

<?php if ($message !== ''): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: '<?php echo $message_type === "success" ? "success" : "error"; ?>',
        title: '<?php echo $message_type === "success" ? "ສຳເລັດ" : "ແຈ້ງເຕືອນ"; ?>',
        text: '<?php echo $message; ?>',
        confirmButtonColor: '#2563eb'
      });
    });
  </script>
<?php endif; ?>