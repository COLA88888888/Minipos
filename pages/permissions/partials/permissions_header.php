<?php
/**
 * --------------------------------------------------------------------------
 * ຟາຍ Partials Header: ສ່ວນຫົວຂໍ້ໜ້າກຳນົດສິດ (Permissions Page Header)
 * Path: pages/permissions/partials/permissions_header.php
 * --------------------------------------------------------------------------
 * ໜ້າທີ່: ສະແດງ Title Header ຂອງໜ້າກຳນົດສິດ ພ້ອມ ປຸ່ມທາງລັດໄປຫາ "ຈັດການຜູ້ນຳໃຊ້"
 */
?>

<!-- Page Header UI -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
  
  <!-- Title ຫົວຂໍ້ໜ້າ -->
  <div>
    <h4 class="fw-bold mb-1" style="font-family: 'Noto Sans Lao', 'Souliyo', 'Boon', sans-serif; color: #0f172a;">
      <i class="fas fa-user-shield text-warning mr-2"></i> <?php echo htmlspecialchars(t('permissions.header_title', 'ກຳນົດສິດການໃຊ້ງານ')); ?>
    </h4>
  </div>

  <!-- Quick Link ປຸ່ມທາງລັດໄປຫາໜ້າຈັດການຜູ້ນຳໃຊ້ (users_manage.php) -->
  <div class="mt-2 mt-md-0">
    <a href="<?php echo $base_path; ?>pages/users_manage/users_manage.php" class="btn btn-outline-primary shadow-sm font-weight-bold" style="border-radius: 10px; padding: 8px 16px;">
      <i class="fas fa-users-cog mr-1"></i> <?php echo htmlspecialchars(t('permissions.btn_manage_users', 'ຈັດການຜູ້ນຳໃຊ້')); ?>
    </a>
  </div>

</div>
