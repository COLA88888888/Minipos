<?php
session_start();
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

// Load Centralized Backend API Logic
require_once __DIR__ . '/../../api/categories_backend.php';

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/categories.css?v=<?php echo filemtime(__DIR__ . '/../../themes/categories.css'); ?>">
<link rel="stylesheet" href="../../themes/users.css?v=<?php echo filemtime(__DIR__ . '/../../themes/users.css'); ?>">

<div class="content-wrapper bg-light">
  
  <!-- Content Header -->
  <section class="content-header py-3">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h5 class="m-0 font-weight-bold text-dark" style="font-family: 'Noto Sans Lao Looped'; font-size: 18px;">
            <i class="fas fa-th-list text-primary mr-2"></i> ຈັດການປະເພດສິນຄ້າ
          </h5>
        </div>
        <div class="col-sm-6 text-right">
          <?php if (hasPermission('categories')): ?>
          <button type="button" class="btn btn-primary px-3 py-1 font-weight-bold shadow-sm" data-toggle="modal" data-target="#addCategoryModal" style="border-radius: 6px;">
            <i class="fas fa-plus-circle mr-1"></i> ເພີ່ມປະເພດສິນຄ້າ
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- SweetAlert Notification -->
  <?php if ($message !== ''): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        <?php if ($message_type === 'success'): ?>
        Swal.fire({
          icon: 'success',
          title: 'ສຳເລັດ',
          text: '<?php echo $message; ?>',
          showConfirmButton: false,
          timer: 1500
        });
        <?php else: ?>
        Swal.fire({
          icon: 'error',
          title: 'ແຈ້ງເຕືອນ',
          text: '<?php echo $message; ?>',
          confirmButtonColor: '#2563eb',
          confirmButtonText: 'ຕົກລົງ'
        });
        <?php endif; ?>
      });
    </script>
  <?php endif; ?>

  <!-- Main content -->
  <section class="content pb-5">
    <div class="container-fluid">
      
      <!-- MAIN CATEGORY TABLE CARD -->
      <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
        
        <div class="card-header bg-white py-3 border-0">
          <h6 class="card-title m-0 font-weight-bold" style="font-family: 'Noto Sans Lao Looped'; color: #0f172a; font-size: 1rem;">
            ລາຍງານປະເພດສິນຄ້າທັງໝົດ
          </h6>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap" id="catTable">
              <thead class="bg-light text-secondary" style="font-size: 0.88rem; text-transform: uppercase;">
                <tr>
                  <th class="py-3 text-center" style="width: 60px;">ລຳດັບ</th>
                  <th class="py-3 text-center" style="width: 100px;">ລະຫັດ</th>
                  <th class="py-3">ຊື່ປະເພດສິນຄ້າ</th>
                  <th class="py-3">ລາຍລະອຽດ</th>
                  <th class="py-3" style="width: 180px;">ວັນທີບັນທຶກ</th>
                  <th class="text-center py-3" style="width: 140px;">ຈັດການ</th>
                </tr>
              </thead>
              <tbody style="font-size: 0.95rem;">
                <?php if (!empty($allCategories)): ?>
                  <?php $idx = 1; foreach ($allCategories as $cat): 
                    $catJson = htmlspecialchars(json_encode($cat), ENT_QUOTES, 'UTF-8');
                    $createdAt = !empty($cat['created_at']) ? date('d/m/Y H:i', strtotime($cat['created_at'])) : '-';
                    $productCount = intval($cat['product_count'] ?? 0);
                  ?>
                    <tr class="cat-row">
                      <td class="align-middle text-center text-muted font-weight-bold"><?php echo $idx++; ?></td>
                      
                      <td class="align-middle text-center font-weight-bold">
                        <span class="badge badge-light border px-2 py-1" style="font-size: 0.85rem; color: #2563eb; background-color: #eff6ff; border-color: #bfdbfe !important;"><?php echo $cat['category_id']; ?></span>
                      </td>

                      <td class="align-middle font-weight-bold text-dark cat-name-cell">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                      </td>

                      <td class="align-middle text-muted cat-desc-cell">
                        <?php echo htmlspecialchars($cat['description'] ?: '-'); ?>
                      </td>

                      <td class="align-middle text-secondary" style="font-size: 0.88rem;">
                        <?php echo $createdAt; ?>
                      </td>

                      <td class="text-center align-middle">
                        <?php 
                          $isUserAdmin = (($_SESSION['status'] ?? '') === 'ຜູ້ບໍລິຫານ' || ($_SESSION['user_id'] ?? 0) == 1);
                          $canEditCat = $isUserAdmin || !empty($_SESSION['permissions']['edit']);
                          $canDeleteCat = $isUserAdmin || !empty($_SESSION['permissions']['edit']);
                        ?>
                        <?php if ($canEditCat || $canDeleteCat): ?>
                          <div class="btn-group btn-group-sm">
                            <?php if ($canEditCat): ?>
                            <!-- Edit Button -->
                            <button type="button" class="btn btn-outline-warning" title="ແກ້ໄຂ" onclick='openEditModal(<?php echo $catJson; ?>)'>
                              <i class="fas fa-edit"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDeleteCat): ?>
                            <!-- Delete Button -->
                            <button type="button" class="btn btn-outline-danger" title="ລົບ" onclick="confirmDeleteCat(<?php echo $cat['category_id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['category_name'])); ?>', <?php echo $productCount; ?>)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                            <?php endif; ?>
                          </div>
                        <?php else: ?>
                          <span class="badge badge-light text-muted" style="font-size: 0.8rem;">ເບິ່ງຢ່າງດຽວ</span>
                        <?php endif; ?>
                      </td>

                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                      ບໍ່ພົບຂໍ້ມູນປະເພດສິນຄ້າໃນລະບົບ
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- FOOTER INFO -->
        <!-- <div class="card-footer bg-white py-3 px-4 border-top">
          <small class="text-muted font-weight-bold" style="font-size: 0.85rem;">
            <i class="fas fa-file-alt text-primary mr-1"></i> ຂໍ້ມູນປະເພດສິນຄ້າທັງໝົດ <?php echo $total_records; ?> ໝວດໝູ່
          </small>
        </div> -->

      </div>
    </div>
  </section>
</div>

<!-- INCLUDE MODAL COMPONENTS -->
<?php
require_once __DIR__ . '/components/form_add_category.php';
require_once __DIR__ . '/components/form_edit_category.php';
?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<script>
  var NEXT_CAT_ID = <?php echo $next_cat_id; ?>;

  // Auto-fill next ID when add modal opens
  $('#addCategoryModal').on('show.bs.modal', function() {
    $('#add_cat_id').val(NEXT_CAT_ID);
  });

  function openEditModal(cat) {
    $('#edit_cat_id').val(cat.category_id);
    $('#edit_cat_name').val(cat.category_name);
    $('#edit_cat_desc').val(cat.description);
    $('#editCategoryModal').modal('show');
  }

  function confirmDeleteCat(catId, catName, productCount) {
    if (productCount && productCount > 0) {
      Swal.fire({
        icon: 'warning',
        title: 'ບໍ່ສາມາດລົບໄດ້!',
        html: '<div style="font-family: \'Noto Sans Lao Looped\', sans-serif;">ໝວດໝູ່ <b>"' + catName + '"</b> ມີລາຍການສິນຄ້າຢູ່ <b>' + productCount + '</b> ລາຍການ.<br><span style="font-size: 0.9rem; color: #64748b; display: inline-block; margin-top: 8px;">ກະລຸນາຍ້າຍ ຫຼື ລົບລາຍການສິນຄ້າໃນໝວດໝູ່ນີ້ອອກກ່ອນ ຈຶ່ງຈະສາມາດລົບໄດ້!</span></div>',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'ຕົກລົງ',
        heightAuto: false
      });
      return;
    }

    Swal.fire({
      title: 'ຢືນຢັນການລົບ?',
      text: 'ທ່ານຕ້ອງການລົບປະເພດສິນຄ້າ "' + catName + '" ແທ້ຫຼືບໍ່?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> ລົບເລີຍ',
      cancelButtonText: 'ຍົກເລີກ',
      heightAuto: false
    }).then(function(result) {
      if (result.isConfirmed) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        var actInput = document.createElement('input');
        actInput.type = 'hidden';
        actInput.name = 'action';
        actInput.value = 'delete_category';
        form.appendChild(actInput);

        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'category_id';
        idInput.value = catId;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
      }
    });
  }
</script>
