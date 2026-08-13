<?php
// Standalone Page: ລາຍງານຕາມປະເພດສິນຄ້າ (Category Sales Report)
session_start();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once dirname(__DIR__, 2) . '/config/db.php';

if (!hasPermission('report')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$from_date     = trim($_GET['from_date'] ?? date('Y-m-01'));
$to_date       = trim($_GET['to_date'] ?? date('Y-m-d'));
$category_id   = trim($_GET['category_id'] ?? '');
$search        = trim($_GET['search'] ?? '');
$employee_name = trim($_GET['employee_name'] ?? '');
$page          = max(1, intval($_GET['page'] ?? 1));
$per_page_raw  = trim($_GET['per_page'] ?? '10');

if ($per_page_raw === 'all') {
    $per_page = 999999;
} else {
    $per_page = max(1, intval($per_page_raw));
}

// Fetch Categories for Dropdown
$catStmt = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$all_categories = $catStmt ? $catStmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Query ALL categories from database, joining sales data to get total quantity sold and revenue
$sql = "
    SELECT 
        cat.category_id,
        cat.category_name,
        COALESCE(SUM(d.save_qty), 0) AS total_qty_sold,
        COALESCE(SUM(d.save_money), 0) AS total_category_revenue
    FROM categories cat
    LEFT JOIN products p ON cat.category_id = p.category_id
    LEFT JOIN tbsale_save_detail d ON p.product_id = d.save_proid
    LEFT JOIN tbsale_save s ON d.save_bill = s.sale_save_bill 
        AND (s.sale_status IS NULL OR s.sale_status != 'CANCEL')
        " . (!empty($from_date) ? " AND s.sale_date >= :from_date" : "") . "
        " . (!empty($to_date) ? " AND s.sale_date <= :to_date" : "") . "
        " . (!empty($employee_name) ? " AND s.user_receive LIKE :employee_name" : "") . "
    " . (!empty($category_id) ? "WHERE cat.category_id = :category_filter_id" : "") . "
    " . (!empty($search) ? (!empty($category_id) ? "AND" : "WHERE") . " (cat.category_name LIKE :search OR d.save_proname LIKE :search OR p.product_name LIKE :search)" : "") . "
    GROUP BY cat.category_id, cat.category_name
    ORDER BY cat.category_name ASC
";

$params = [];
if (!empty($from_date)) {
    $params[':from_date'] = $from_date;
}
if (!empty($to_date)) {
    $params[':to_date'] = $to_date;
}
if (!empty($employee_name)) {
    $params[':employee_name'] = '%' . $employee_name . '%';
}
if (!empty($category_id)) {
    $params[':category_filter_id'] = $category_id;
}
if (!empty($search)) {
    $params[':search'] = '%' . $search . '%';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sold products per category
$items_sql = "
    SELECT 
        COALESCE(cat.category_id, 0) AS category_id,
        COALESCE(d.save_proname, p.product_name, 'ບໍ່ລະບຸຊື່ສິນຄ້າ') AS product_name,
        SUM(d.save_qty) AS qty_sold,
        SUM(d.save_money) AS item_revenue
    FROM tbsale_save_detail d
    INNER JOIN tbsale_save s ON d.save_bill = s.sale_save_bill
    LEFT JOIN products p ON d.save_proid = p.product_id
    LEFT JOIN categories cat ON p.category_id = cat.category_id
    WHERE (s.sale_status IS NULL OR s.sale_status != 'CANCEL')
        " . (!empty($from_date) ? " AND s.sale_date >= :from_date" : "") . "
        " . (!empty($to_date) ? " AND s.sale_date <= :to_date" : "") . "
        " . (!empty($employee_name) ? " AND s.user_receive LIKE :employee_name" : "") . "
        " . (!empty($search) ? " AND (d.save_proname LIKE :search OR d.save_proid LIKE :search OR p.product_name LIKE :search)" : "") . "
    GROUP BY category_id, product_name
    ORDER BY qty_sold DESC
";
$items_stmt = $pdo->prepare($items_sql);
$item_params = [];
if (!empty($from_date)) $item_params[':from_date'] = $from_date;
if (!empty($to_date)) $item_params[':to_date'] = $to_date;
if (!empty($employee_name)) $item_params[':employee_name'] = '%' . $employee_name . '%';
if (!empty($search)) $item_params[':search'] = '%' . $search . '%';
$items_stmt->execute($item_params);
$raw_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

$category_products = [];
foreach ($raw_items as $item) {
    $cid = $item['category_id'];
    if (!isset($category_products[$cid])) {
        $category_products[$cid] = [];
    }
    $category_products[$cid][] = $item;
}

$total_records = count($categories);
$total_pages   = max(1, ceil($total_records / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$display_data = array_slice($categories, $offset, $per_page);

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/reports.css?v=<?php echo filemtime(__DIR__ . '/../../themes/reports.css'); ?>">

<div class="container-fluid p-3 p-md-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h5 class="font-weight-bold text-dark mb-1" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
        <i class="fas fa-layer-group text-primary mr-2"></i> ລາຍງານຕາມປະເພດສິນຄ້າ ແລະ ລາຍການສິນຄ້າທີ່ຂາຍ
      </h5>
    </div>
  </div>

  <!-- Search & Filter Bar -->
  <div class="report-filter-box no-print mb-3.5" style="padding: 12px 16px;">
    <form method="GET" action="category_sales.php" class="d-flex flex-column flex-md-row align-items-md-end flex-wrap" style="gap: 12px;">
      <!-- Per Page Dropdown (ໂຊລາຍການ - ໜ້າສຸດ) -->
      <div style="flex: 1 1 120px; width: 100%;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-layer-group text-primary mr-1"></i> ໂຊລາຍການ:
        </label>
        <select name="per_page" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="border-radius: 8px; height: 38px; font-size: 0.85rem; background: #ffffff; border: 1.5px solid #cbd5e1; width: 100%;">
          <option value="5" <?php echo $per_page_raw === '5' ? 'selected' : ''; ?>>5</option>
          <option value="10" <?php echo $per_page_raw === '10' ? 'selected' : ''; ?>>10</option>
          <option value="25" <?php echo $per_page_raw === '25' ? 'selected' : ''; ?>>25</option>
          <option value="50" <?php echo $per_page_raw === '50' ? 'selected' : ''; ?>>50</option>
          <option value="100" <?php echo $per_page_raw === '100' ? 'selected' : ''; ?>>100</option>
          <option value="all" <?php echo $per_page_raw === 'all' ? 'selected' : ''; ?>>ທັງໝົດ</option>
        </select>
      </div>

      <!-- From Date (ຕັ້ງແຕ່ວັນທີ) -->
      <div style="flex: 1 1 130px; width: 100%;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-alt text-primary mr-1"></i> ຕັ້ງແຕ່ວັນທີ:
        </label>
        <input type="date" name="from_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($from_date); ?>" style="border-radius: 8px; height: 38px; width: 100%;">
      </div>

      <!-- To Date (ຫາວັນທີ) -->
      <div style="flex: 1 1 130px; width: 100%;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-check text-primary mr-1"></i> ຫາວັນທີ:
        </label>
        <input type="date" name="to_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($to_date); ?>" style="border-radius: 8px; height: 38px; width: 100%;">
      </div>

      <!-- Category Filter Dropdown (ເລືອກປະເພດສິນຄ້າ) -->
      <div style="flex: 1 1 150px; width: 100%;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-tags text-primary mr-1"></i> ປະເພດສິນຄ້າ:
        </label>
        <select name="category_id" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="border-radius: 8px; height: 38px; font-size: 0.85rem; background: #ffffff; border: 1.5px solid #cbd5e1; width: 100%;">
          <option value="">-- ເລືອກປະເພດສິນຄ້າ --</option>
          <?php foreach ($all_categories as $c): ?>
            <option value="<?php echo $c['category_id']; ?>" <?php echo $category_id == $c['category_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($c['category_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Employee Name Input (ຊື່ພະນັກງານ) -->
      <div style="flex: 1 1 150px; width: 100%;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-user text-primary mr-1"></i> ຊື່ພະນັກງານ:
        </label>
        <input type="text" name="employee_name" id="catEmployeeInput" class="form-control form-control-sm" placeholder="ປ້ອນຊື່ພະນັກງານ..." value="<?php echo htmlspecialchars($employee_name); ?>" style="border-radius: 8px; height: 38px; width: 100%;">
      </div>

      <!-- Action Buttons (ຄົ້ນຫາ & ຣີໂຫລດ) -->
      <div style="flex: 1 1 130px; width: 100%;">
        <div class="d-flex align-items-center" style="gap: 8px; width: 100%;">
          <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3 d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, #2563eb, #1d4ed8); flex: 1;">
            <i class="fas fa-search mr-1.5"></i> ຄົ້ນຫາ
          </button>
          <a href="category_sales.php" class="btn btn-light btn-sm border font-weight-bold px-3 d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px;" title="ລ້າງຄ່າ">
            <i class="fas fa-redo"></i>
          </a>
        </div>
      </div>
    </form>
  </div>

  <!-- Category Table Card -->
  <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-0">
      <div class="table-responsive" style="overflow: visible;">
        <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 0.88rem;">
          <thead style="background-color: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">
            <tr style="background: #ffffff; color: #1e293b;">
              <th class="text-center py-3" style="width: 60px; border-bottom: 2px solid #cbd5e1;">ລຳດັບ</th>
              <th class="py-3" style="border-bottom: 2px solid #cbd5e1;">ປະເພດສິນຄ້າ / ລາຍການສິນຄ້າ</th>
              <th class="text-center py-3" style="width: 140px; border-bottom: 2px solid #cbd5e1;">ຈຳນວນ</th>
              <th class="text-right py-3" style="width: 160px; border-bottom: 2px solid #cbd5e1;">ລວມເງິນ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($display_data)): ?>
              <tr>
                <td colspan="4" class="text-center py-5 text-muted font-weight-bold">
                  <i class="fas fa-layer-group fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                  ບໍ່ພົບຂໍ້ມູນປະເພດສິນຄ້າ
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($display_data as $idx => $row): ?>
                <?php 
                  $cid = $row['category_id'];
                  $prods = $category_products[$cid] ?? [];
                ?>
                <!-- Category Header Row -->
                <tr class="cat-header-row" style="background-color: #f1f5f9; border-top: 2px solid #cbd5e1; font-weight: bold;">
                  <td class="text-center align-middle text-dark" style="font-size: 0.95rem; font-weight: 700; background-color: #f1f5f9;">
                    <?php echo $offset + $idx + 1; ?>
                  </td>
                  <td class="align-middle text-dark" style="font-size: 0.96rem; font-weight: 700; background-color: #f1f5f9;">
                    <?php echo htmlspecialchars($row['category_name']); ?>
                  </td>
                  <td class="text-center align-middle text-dark" style="font-size: 0.95rem; font-weight: 700; background-color: #f1f5f9;">
                    <?php echo number_format($row['total_qty_sold']); ?>
                  </td>
                  <td class="text-right align-middle text-dark" style="font-size: 0.96rem; font-weight: 700; background-color: #f1f5f9;">
                    <?php echo number_format($row['total_category_revenue'], 0); ?>
                  </td>
                </tr>

                <!-- Product Detail Rows -->
                <?php if (!empty($prods)): ?>
                  <?php foreach ($prods as $pIdx => $p): ?>
                    <tr style="background-color: #ffffff;">
                      <td class="text-center align-middle text-muted" style="font-size: 0.88rem; padding-left: 20px;">
                        <?php echo $pIdx + 1; ?>
                      </td>
                      <td class="align-middle text-secondary" style="font-size: 0.9rem; padding-left: 28px;">
                        <?php echo htmlspecialchars($p['product_name']); ?>
                      </td>
                      <td class="text-center align-middle text-secondary" style="font-size: 0.9rem;">
                        <?php echo number_format($p['qty_sold']); ?>
                      </td>
                      <td class="text-right align-middle text-dark" style="font-size: 0.9rem;">
                        <?php echo number_format($p['item_revenue'], 0); ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr style="background-color: #ffffff;">
                    <td></td>
                    <td colspan="3" class="text-muted italic py-2" style="font-size: 0.85rem; padding-left: 28px;">
                      (ບໍ່ມີປະຫວັດການຂາຍ)
                    </td>
                  </tr>
                <?php endif; ?>

              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
          <?php if (!empty($display_data)): ?>
            <tfoot style="background: #f8fafc; border-top: 2px solid #cbd5e1;">
              <tr class="font-weight-bold" style="font-size: 0.9rem; color: #0f172a;">
                <td colspan="2" class="text-center py-3 font-weight-bold" style="background: #f8fafc; color: #0f172a;">ລວມທັງໝົດ:</td>
                <td class="text-center py-3 text-dark font-weight-bold" style="background: #f8fafc;" id="cat_tot_qty">0</td>
                <td class="text-right py-3 text-primary font-weight-bold" style="background: #f8fafc; font-size: 0.96rem;" id="cat_tot_rev">0 ₭</td>
              </tr>
            </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- Table Card Footer: Circular Blue Pagination Aligned Right -->
    <div class="card-footer bg-white border-top py-3 px-3.5 d-flex flex-column flex-md-row justify-content-between align-items-center no-print" style="row-gap: 12px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">

      <nav aria-label="Page navigation" class="ml-auto">
        <ul class="pagination report-pagination mb-0">
          <!-- Previous Page -->
          <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="category_sales.php?page=<?php echo max(1, $page - 1); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&category_id=<?php echo urlencode($category_id); ?>&search=<?php echo urlencode($search); ?>&employee_name=<?php echo urlencode($employee_name); ?>&per_page=<?php echo urlencode($per_page_raw); ?>">
              <i class="fas fa-chevron-left" style="font-size: 0.76rem;"></i>
            </a>
          </li>

          <?php
            $range = 2;
            $startP = max(1, $page - $range);
            $endP   = min($total_pages, $page + $range);

            if ($startP > 1) {
                echo '<li class="page-item"><a class="page-link" href="category_sales.php?page=1&from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&category_id=' . urlencode($category_id) . '&search=' . urlencode($search) . '&employee_name=' . urlencode($employee_name) . '&per_page=' . urlencode($per_page_raw) . '">1</a></li>';
                if ($startP > 2) {
                    echo '<li class="page-item disabled"><span class="page-link" style="border:none;">...</span></li>';
                }
            }

            for ($p = $startP; $p <= $endP; $p++) {
                $activeClass = ($p == $page) ? 'active' : '';
                echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="category_sales.php?page=' . $p . '&from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&category_id=' . urlencode($category_id) . '&search=' . urlencode($search) . '&employee_name=' . urlencode($employee_name) . '&per_page=' . urlencode($per_page_raw) . '">' . $p . '</a></li>';
            }

            if ($endP < $total_pages) {
                if ($endP < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link" style="border:none;">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="category_sales.php?page=' . $total_pages . '&from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&category_id=' . urlencode($category_id) . '&search=' . urlencode($search) . '&employee_name=' . urlencode($employee_name) . '&per_page=' . urlencode($per_page_raw) . '">' . $total_pages . '</a></li>';
            }
          ?>

          <!-- Next Page -->
          <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="category_sales.php?page=<?php echo min($total_pages, $page + 1); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&category_id=<?php echo urlencode($category_id); ?>&search=<?php echo urlencode($search); ?>&employee_name=<?php echo urlencode($employee_name); ?>&per_page=<?php echo urlencode($per_page_raw); ?>">
              <i class="fas fa-chevron-right" style="font-size: 0.76rem;"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('.table');
    const searchInput = document.getElementById('catSearchInput');
    
    function calcCatTotals() {
        if (!table) return;
        let sumQty = 0, sumRev = 0;
        const rows = table.querySelectorAll('tbody tr.cat-header-row');
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 4) {
                    sumQty += parseFloat(cells[2].textContent.replace(/[^0-9.-]+/g, '')) || 0;
                    sumRev += parseFloat(cells[3].textContent.replace(/[^0-9.-]+/g, '')) || 0;
                }
            }
        });
        const elQty = document.getElementById('cat_tot_qty');
        const elRev = document.getElementById('cat_tot_rev');
        if (elQty) elQty.textContent = Math.round(sumQty).toLocaleString();
        if (elRev) elRev.textContent = Math.round(sumRev).toLocaleString() + ' ₭';
    }

    calcCatTotals();

    const empInput = document.getElementById('catEmployeeInput');
    const targetInput = empInput || searchInput;

    if (targetInput && table) {
        targetInput.addEventListener('input', function() {
            const val = this.value.trim().toLowerCase();
            const rows = table.querySelectorAll('tbody tr:not(#noSearchResultRow)');
            let visibleCount = 0;

            if (val === '') {
                rows.forEach(row => row.style.display = '');
                const oldNoRow = document.getElementById('noSearchResultRow');
                if (oldNoRow) oldNoRow.remove();
                calcCatTotals();
                return;
            }

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(val)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            let noRow = document.getElementById('noSearchResultRow');
            if (visibleCount === 0 && rows.length > 0) {
                if (!noRow) {
                    noRow = document.createElement('tr');
                    noRow.id = 'noSearchResultRow';
                    noRow.innerHTML = `<td colspan="4" class="text-center py-5 text-muted font-weight-bold">
                        <i class="fas fa-exclamation-circle fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                        ບໍ່ພົບຂໍ້ມູນທີ່ຕົງກັບຄຳຄົ້ນຫາ
                    </td>`;
                    table.querySelector('tbody').appendChild(noRow);
                }
            } else {
                if (noRow) noRow.remove();
            }
            calcCatTotals();
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
