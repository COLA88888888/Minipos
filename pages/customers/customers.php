<?php
session_start();
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../../config/db.php';

// Check permissions
if (empty($_SESSION['user_id']) || (!hasPermission('customers') && !hasPermission('sale') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// Handle Customer Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_customer') {
            $code    = trim($_POST['customer_code'] ?? '');
            $name    = trim($_POST['customer_name'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $notes   = trim($_POST['notes'] ?? '');

            if ($code !== '' && $name !== '') {
                try {
                    $stmt = $pdo->prepare("INSERT INTO customers (customer_code, customer_name, phone, email, address, notes) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$code, $name, $phone, $email, $address, $notes]);
                    $message = 'ເພີ່ມຂໍ້ມູນລູກຄ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ເພີ່ມຂໍ້ມູນລູກຄ້າ", "ລະຫັດ: $code, ຊື່: $name");
                } catch (Exception $e) {
                    $message = 'ຜິດພາດ: ລະຫັດລູກຄ້ານີ້ອາດມີໃນລະບົບແລ້ວ ຫຼື ' . $e->getMessage();
                    $message_type = 'danger';
                }
            } else {
                $message = 'ກະລຸນາປ້ອນລະຫັດ ແລະ ຊື່ລູກຄ້າໃຫ້ຄົບຖ້ວນ!';
                $message_type = 'danger';
            }
        }
        elseif ($_POST['action'] === 'edit_customer') {
            $id      = intval($_POST['customer_id'] ?? 0);
            $name    = trim($_POST['customer_name'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $notes   = trim($_POST['notes'] ?? '');

            if ($id > 0 && $name !== '') {
                try {
                    $stmt = $pdo->prepare("UPDATE customers SET customer_name = ?, phone = ?, email = ?, address = ?, notes = ? WHERE customer_id = ?");
                    $stmt->execute([$name, $phone, $email, $address, $notes, $id]);
                    $message = 'ແກ້ໄຂຂໍ້ມູນລູກຄ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ແກ້ໄຂຂໍ້ມູນລູກຄ້າ", "ID: $id, ຊື່: $name");
                } catch (Exception $e) {
                    $message = 'ຜິດພາດ: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            } else {
                $message = 'ກະລຸນາປ້ອນຊື່ລູກຄ້າໃຫ້ຄົບຖ້ວນ!';
                $message_type = 'danger';
            }
        }
        elseif ($_POST['action'] === 'delete_customer') {
            $id = intval($_POST['customer_id'] ?? 0);
            if ($id > 0) {
                if ($id === 1) {
                    $message = 'ບໍ່ສາມາດລົບລູກຄ້າທົ່ວໄປ (ID: 1) ໄດ້!';
                    $message_type = 'danger';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id = ?");
                        $stmt->execute([$id]);
                        $message = 'ລົບຂໍ້ມູນລູກຄ້າສຳເລັດ!';
                        $message_type = 'success';
                        logActivity($pdo, "ລົບຂໍ້ມູນລູກຄ້າ", "ID: $id");
                    } catch (Exception $e) {
                        $message = 'ຜິດພາດ: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
            }
        }
    }
}

// Fetch All Customers
$stmtCust = $pdo->query("SELECT * FROM customers ORDER BY customer_id DESC");
$allCustomers = $stmtCust->fetchAll();
$total_records = count($allCustomers);

// Generate Next Customer Code
$maxId = (int)$pdo->query("SELECT IFNULL(MAX(customer_id), 0) + 1 FROM customers")->fetchColumn();
$next_cust_code = 'CUST-' . str_pad($maxId, 3, '0', STR_PAD_LEFT);

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/customers.css?v=<?php echo filemtime(__DIR__ . '/../../themes/customers.css'); ?>">

<div class="content-wrapper bg-light">
  
  <!-- Content Header -->
  <section class="content-header py-3">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h5 class="m-0 font-weight-bold text-dark" style="font-family: 'Noto Sans Lao Looped'; font-size: 18px;">
            <i class="fas fa-user-friends text-info mr-2"></i> ລາຍງານລູກຄ້າທັງໝົດ
          </h5>
        </div>
        <div class="col-sm-6 text-right">
          <button type="button" class="btn btn-primary px-3 py-1 font-weight-bold shadow-sm" data-toggle="modal" data-target="#addCustomerModal" style="border-radius: 6px;">
            <i class="fas fa-user-plus mr-1"></i> ເພີ່ມລູກຄ້າໃໝ່
          </button>
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

  <!-- Main Content -->
  <section class="content pb-5">
    <div class="container-fluid">

      <!-- MAIN CUSTOMER TABLE CARD -->
      <div class="card customer-card">
        
        <!-- Header Controls (ຊ້າຍ: ບັອກເລືອກຈຳນວນສະແດງ, ຂວາ: ບັອກຄົ້ນຫາ) -->
        <div class="customer-card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">

          <!-- Left Side: Page Size Dropdown (ຊ້າຍມື) -->
          <div class="page-size-wrap">
            <select id="pageSizeSelect" class="form-control form-control-sm" onchange="changePageSize()">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
              <option value="all">ທັງໝົດ</option>
            </select>
          </div>

          <!-- Right Side: Search Box (ຊິດຂວາມື) -->
          <div class="search-box-wrap ml-auto">
            <i class="fas fa-search"></i>
            <input type="text" id="customerSearchInput" class="form-control" placeholder="ຄົ້ນຫາ ລະຫັດ, ຊື່, ເບີໂທ..." onkeyup="filterCustomerTable()">
          </div>

        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap" id="customerTable">
              <thead class="bg-light text-secondary" style="font-size: 0.88rem; text-transform: uppercase;">
                <tr>
                  <th class="py-3 text-center" style="width: 60px;">ລຳດັບ</th>
                  <th class="py-3 text-center" style="width: 120px;">ລະຫັດລູກຄ້າ</th>
                  <th class="py-3">ຊື່ ແລະ ນາມສະກຸນ</th>
                  <th class="py-3">ເບີໂທຕິດຕໍ່</th>
                  <th class="py-3">ອີເມວ</th>
                  <th class="py-3">ທີ່ຢູ່</th>
                  <th class="py-3" style="width: 160px;">ວັນທີບັນທຶກ</th>
                  <th class="text-center py-3" style="width: 130px;">ຈັດການ</th>
                </tr>
              </thead>
              <tbody style="font-size: 0.95rem;">
                <tr id="noCustomerDataRow" style="<?php echo empty($allCustomers) ? '' : 'display: none;'; ?>">
                  <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-user-slash fa-2x mb-2 text-secondary d-block"></i>
                    <span class="font-weight-bold d-block" style="font-size: 1.05rem; color: #64748b;">ບໍ່ມີຂໍ້ມູນລູກຄ້າໃນລະບົບ</span>
                  </td>
                </tr>

                <?php if (!empty($allCustomers)): ?>
                  <?php $idx = 1; foreach ($allCustomers as $cust): 
                    $custJson = htmlspecialchars(json_encode($cust), ENT_QUOTES, 'UTF-8');
                    $createdAt = !empty($cust['created_at']) ? date('d/m/Y H:i', strtotime($cust['created_at'])) : '-';
                    $initial = mb_substr($cust['customer_name'], 0, 1, 'UTF-8');
                    $searchData = strtolower($cust['customer_code'] . ' ' . $cust['customer_name'] . ' ' . ($cust['phone'] ?? '') . ' ' . ($cust['email'] ?? '') . ' ' . ($cust['address'] ?? ''));
                  ?>
                    <tr class="cust-row" data-search="<?php echo htmlspecialchars($searchData); ?>">
                      <td class="align-middle text-center text-muted font-weight-bold row-index"><?php echo $idx++; ?></td>
                      
                      <td class="align-middle text-center font-weight-bold">
                        <span class="cust-code-badge"><?php echo htmlspecialchars($cust['customer_code']); ?></span>
                      </td>

                      <td class="align-middle font-weight-bold text-dark cust-name-cell">
                        <?php echo htmlspecialchars($cust['customer_name']); ?>
                      </td>

                      <td class="align-middle text-dark cust-phone-cell">
                        <?php if (!empty($cust['phone'])): ?>
                          <a href="tel:<?php echo htmlspecialchars($cust['phone']); ?>" class="text-dark">
                            <i class="fas fa-phone-alt text-success mr-1"></i> <?php echo htmlspecialchars($cust['phone']); ?>
                          </a>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>

                      <td class="align-middle text-muted">
                        <?php echo htmlspecialchars($cust['email'] ?: '-'); ?>
                      </td>

                      <td class="align-middle text-secondary text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($cust['address']); ?>">
                        <?php echo htmlspecialchars($cust['address'] ?: '-'); ?>
                      </td>

                      <td class="align-middle text-secondary" style="font-size: 0.88rem;">
                        <?php echo $createdAt; ?>
                      </td>

                      <td class="text-center align-middle">
                        <div class="btn-group btn-group-sm">
                          <!-- Edit Button -->
                          <button type="button" class="btn btn-outline-warning" title="ແກ້ໄຂ" onclick='openEditCustomerModal(<?php echo $custJson; ?>)'>
                            <i class="fas fa-edit"></i>
                          </button>
                          <!-- Delete Button -->
                          <?php if ($cust['customer_id'] == 1): ?>
                            <button type="button" class="btn btn-outline-secondary" title="ບໍ່ສາມາດລົບລູກຄ້າທົ່ວໄປໄດ້" disabled>
                              <i class="fas fa-lock"></i>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-outline-danger" title="ລົບ" onclick="confirmDeleteCustomer(<?php echo $cust['customer_id']; ?>, '<?php echo htmlspecialchars(addslashes($cust['customer_name'])); ?>')">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>

                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Card Footer: Blue Circular Pagination (ຊິດຂວາມື) -->
        <div class="card-footer bg-white border-top d-flex flex-column flex-md-row align-items-center justify-content-between py-3 px-3 px-md-4" style="border-radius: 0 0 14px 14px;">

          <div class="d-flex justify-content-end ml-md-auto">
            <nav aria-label="Customer Pagination">
              <ul class="pagination pagination-circle mb-0 justify-content-end" id="customerPagination">
                <!-- Dynamic circular pagination buttons -->
              </ul>
            </nav>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>

<!-- INCLUDE MODAL COMPONENTS -->
<?php
require_once __DIR__ . '/components/form_add_customer.php';
require_once __DIR__ . '/components/form_edit_customer.php';
?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<script>
  var NEXT_CUST_CODE = '<?php echo $next_cust_code; ?>';
  var currentPage = 1;
  var pageSize = 10;
  var filteredRows = [];

  $('#addCustomerModal').on('show.bs.modal', function() {
    $('#add_customer_code').val(NEXT_CUST_CODE);
  });

  function openEditCustomerModal(cust) {
    $('#edit_customer_id').val(cust.customer_id);
    $('#edit_customer_code').val(cust.customer_code);
    $('#edit_customer_name').val(cust.customer_name);
    $('#edit_phone').val(cust.phone);
    $('#edit_email').val(cust.email);
    $('#edit_address').val(cust.address);
    $('#edit_notes').val(cust.notes);
    $('#editCustomerModal').modal('show');
  }

  // ====== Pagination & Search Filter Logic ======
  function initPagination() {
    var rows = Array.from(document.querySelectorAll('.cust-row'));
    filteredRows = rows;
    currentPage = 1;
    applyPagination();
  }

  function changePageSize() {
    var val = document.getElementById('pageSizeSelect').value;
    if (val === 'all') {
      pageSize = filteredRows.length > 0 ? filteredRows.length : 99999;
    } else {
      pageSize = parseInt(val, 10);
    }
    currentPage = 1;
    applyPagination();
  }

  function goToPage(page) {
    var totalPages = Math.ceil(filteredRows.length / pageSize) || 1;
    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;
    currentPage = page;
    applyPagination();
  }

  function applyPagination() {
    var allRows = document.querySelectorAll('.cust-row');
    allRows.forEach(function(r) { r.style.display = 'none'; });

    var total = filteredRows.length;
    var noDataRow = document.getElementById('noCustomerDataRow');
    if (noDataRow) {
      noDataRow.style.display = (total === 0) ? '' : 'none';
    }

    var totalPages = Math.ceil(total / pageSize) || 1;
    if (currentPage > totalPages) currentPage = totalPages;

    var startIdx = (currentPage - 1) * pageSize;
    var endIdx = (pageSize >= 99999) ? total : startIdx + pageSize;

    for (var i = startIdx; i < endIdx && i < total; i++) {
      if (filteredRows[i]) {
        filteredRows[i].style.display = '';
      }
    }

    var startDisplay = total === 0 ? 0 : startIdx + 1;
    var endDisplay   = Math.min(endIdx, total);
    
    var startEl = document.getElementById('page_info_start');
    var endEl   = document.getElementById('page_info_end');
    var totalEl = document.getElementById('page_info_total');
    if (startEl) startEl.textContent = startDisplay;
    if (endEl)   endEl.textContent = endDisplay;
    if (totalEl) totalEl.textContent = total;

    renderPaginationControls(totalPages);
  }

  function renderPaginationControls(totalPages) {
    var paginationUl = document.getElementById('customerPagination');
    if (!paginationUl) return;
    paginationUl.innerHTML = '';

    if (totalPages < 1) totalPages = 1;

    // 1. Previous button
    var prevLi = document.createElement('li');
    prevLi.className = 'page-item ' + (currentPage <= 1 ? 'disabled' : '');
    prevLi.innerHTML = '<a class="page-link" href="javascript:void(0)" ' + (currentPage > 1 ? 'onclick="goToPage(' + (currentPage - 1) + ')"' : '') + ' title="ໜ້າກ່ອນໜ້າ"><i class="fas fa-chevron-left"></i></a>';
    paginationUl.appendChild(prevLi);

    // 2. Page numbers
    var maxButtons = 5;
    var startPage = Math.max(1, currentPage - 2);
    var endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) {
      startPage = Math.max(1, endPage - maxButtons + 1);
    }

    if (startPage > 1) {
      var firstLi = document.createElement('li');
      firstLi.className = 'page-item';
      firstLi.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="goToPage(1)">1</a>';
      paginationUl.appendChild(firstLi);

      if (startPage > 2) {
        var dotLi = document.createElement('li');
        dotLi.className = 'page-item disabled';
        dotLi.innerHTML = '<span class="page-link" style="border:none; background:transparent;">...</span>';
        paginationUl.appendChild(dotLi);
      }
    }

    for (var p = startPage; p <= endPage; p++) {
      var pageLi = document.createElement('li');
      pageLi.className = 'page-item ' + (p === currentPage ? 'active' : '');
      pageLi.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="goToPage(' + p + ')">' + p + '</a>';
      paginationUl.appendChild(pageLi);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        var dotLi2 = document.createElement('li');
        dotLi2.className = 'page-item disabled';
        dotLi2.innerHTML = '<span class="page-link" style="border:none; background:transparent;">...</span>';
        paginationUl.appendChild(dotLi2);
      }

      var lastLi = document.createElement('li');
      lastLi.className = 'page-item';
      lastLi.innerHTML = '<a class="page-link" href="javascript:void(0)" onclick="goToPage(' + totalPages + ')">' + totalPages + '</a>';
      paginationUl.appendChild(lastLi);
    }

    // 3. Next button
    var nextLi = document.createElement('li');
    nextLi.className = 'page-item ' + (currentPage >= totalPages ? 'disabled' : '');
    nextLi.innerHTML = '<a class="page-link" href="javascript:void(0)" ' + (currentPage < totalPages ? 'onclick="goToPage(' + (currentPage + 1) + ')"' : '') + ' title="ໜ້າຖັດໄປ"><i class="fas fa-chevron-right"></i></a>';
    paginationUl.appendChild(nextLi);
  }

  function filterCustomerTable() {
    var search = document.getElementById('customerSearchInput').value.toLowerCase().trim();
    var rows = document.querySelectorAll('.cust-row');
    filteredRows = [];

    rows.forEach(function(row) {
      var searchAttr = (row.getAttribute('data-search') || '').toLowerCase();
      var textContent = row.textContent.toLowerCase();

      if (search === '' || searchAttr.includes(search) || textContent.includes(search)) {
        filteredRows.push(row);
      }
    });

    var countBadge = document.getElementById('customerCountBadge');
    if (countBadge) {
      countBadge.textContent = 'ສະແດງ ' + filteredRows.length + ' ລາຍການ';
    }

    currentPage = 1;
    applyPagination();
  }

  $(document).ready(function() {
    initPagination();

    $('#customerSearchInput').on('keydown', function(e) {
      if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        filterCustomerTable();
      }
    });
  });

  function confirmDeleteCustomer(id, name) {
    Swal.fire({
      title: 'ຢືນຢັນການລົບ?',
      text: 'ທ່ານຕ້ອງການລົບຂໍ້ມູນລູກຄ້າ "' + name + '" ແທ້ຫຼືບໍ່?',
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
        actInput.value = 'delete_customer';
        form.appendChild(actInput);

        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'customer_id';
        idInput.value = id;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
      }
    });
  }
</script>
