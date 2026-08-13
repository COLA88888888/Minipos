<?php
// Standalone Page: ລາຍງານສິນຄ້າຂາຍດີ (Best Seller Products Report)
session_start();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once dirname(__DIR__, 2) . '/config/db.php';

if (!hasPermission('report')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$from_date = trim($_GET['from_date'] ?? date('Y-m-01'));
$to_date   = trim($_GET['to_date'] ?? date('Y-m-d'));
$search    = trim($_GET['search'] ?? '');
$page      = max(1, intval($_GET['page'] ?? 1));
$per_page_raw = trim($_GET['per_page'] ?? '10');
if ($per_page_raw === 'all') {
    $per_page = 999999;
} else {
    $per_page = max(1, intval($per_page_raw));
}

// SQL Filter Conditions
$where   = ["(s.sale_status IS NULL OR s.sale_status != 'CANCEL')"];
$params  = [];

if (!empty($from_date)) {
    $where[] = "s.sale_date >= :from_date";
    $params[':from_date'] = $from_date;
}
if (!empty($to_date)) {
    $where[] = "s.sale_date <= :to_date";
    $params[':to_date'] = $to_date;
}
if (!empty($search)) {
    $where[] = "(d.save_proname LIKE :search OR d.save_proid LIKE :search OR p.product_name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$whereClause = implode(" AND ", $where);

// Aggregate Query Grouped by Product
$sql = "
    SELECT 
        d.save_proid,
        COALESCE(d.save_proname, p.product_name, 'ສິນຄ້າ') AS product_name,
        cat.category_name,
        SUM(d.save_qty) AS total_qty_sold,
        AVG(d.save_price) AS avg_price,
        SUM(d.save_money) AS total_revenue
    FROM tbsale_save_detail d
    INNER JOIN tbsale_save s ON d.save_bill = s.sale_save_bill
    LEFT JOIN products p ON d.save_proid = p.product_id
    LEFT JOIN category cat ON p.category_id = cat.category_id
    WHERE {$whereClause}
    GROUP BY d.save_proid, product_name, cat.category_name
    ORDER BY total_qty_sold DESC, total_revenue DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$best_sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_records = count($best_sellers);
$total_pages   = max(1, ceil($total_records / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$display_data = array_slice($best_sellers, $offset, $per_page);

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/reports.css?v=<?php echo filemtime(__DIR__ . '/../../themes/reports.css'); ?>">

<div class="container-fluid p-3 p-md-4">
  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="row-gap: 10px;">
    <div>
      <h5 class="font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
        <i class="fas fa-fire text-danger mr-2"></i> ລາຍງານສິນຄ້າຂາຍດີ
      </h5>
    </div>

    <!-- Export & Print Buttons (Excel, PDF, Print) -->
    <div class="d-flex align-items-center justify-content-end ml-auto no-print" style="gap: 5px;">
      <button type="button" onclick="exportReportExcel('all')" class="btn btn-xs btn-success font-weight-bold px-2 d-inline-flex align-items-center shadow-sm" style="border-radius: 6px; height: 30px; font-size: 0.78rem; background: linear-gradient(135deg, #16a34a, #15803d); border: none;">
        <i class="fas fa-file-excel mr-1" style="font-size: 0.8rem;"></i> Excel
      </button>
      <button type="button" onclick="exportReportPDF('all')" class="btn btn-xs btn-danger font-weight-bold px-2 d-inline-flex align-items-center shadow-sm" style="border-radius: 6px; height: 30px; font-size: 0.78rem; background: linear-gradient(135deg, #dc2626, #b91c1c); border: none;">
        <i class="fas fa-file-pdf mr-1" style="font-size: 0.8rem;"></i> PDF
      </button>
      <button type="button" onclick="printReportTable('all')" class="btn btn-xs btn-primary font-weight-bold px-2 d-inline-flex align-items-center shadow-sm" style="border-radius: 6px; height: 30px; font-size: 0.78rem; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
        <i class="fas fa-print mr-1" style="font-size: 0.8rem;"></i> ພິມ
      </button>
    </div>
  </div>

  <!-- Search & Filter Bar -->
  <div class="report-filter-box no-print mb-3.5" style="padding: 12px 16px;">
    <form method="GET" action="best_seller.php" class="d-flex flex-column flex-md-row align-items-md-end flex-wrap" style="gap: 12px;">
      <!-- Per Page Dropdown (ໂຊລາຍການ) -->
      <div style="flex: 1 1 140px; width: 100%;">
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
      <div style="flex: 1 1 140px; width: 100%;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-alt text-primary mr-1"></i> ຕັ້ງແຕ່ວັນທີ:
        </label>
        <input type="date" name="from_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($from_date); ?>" style="border-radius: 8px; height: 38px; width: 100%;">
      </div>

      <!-- To Date (ຫາວັນທີ) -->
      <div style="flex: 1 1 140px; width: 100%;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-check text-primary mr-1"></i> ຫາວັນທີ:
        </label>
        <input type="date" name="to_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($to_date); ?>" style="border-radius: 8px; height: 38px; width: 100%;">
      </div>

      <!-- Action Buttons (ຄົ້ນຫາ & ຣີໂຫລດ) -->
      <div style="flex: 1 1 140px; width: 100%;">
        <div class="d-flex align-items-center" style="gap: 8px; width: 100%;">
          <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3 d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, #2563eb, #1d4ed8); flex: 1;">
            <i class="fas fa-search mr-1.5"></i> ຄົ້ນຫາ
          </button>
          <a href="best_seller.php" class="btn btn-light btn-sm border font-weight-bold px-3 d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px;" title="ລ້າງຄ່າ">
            <i class="fas fa-redo"></i>
          </a>
        </div>
      </div>
    </form>
  </div>

  <!-- Best Sellers Table Card -->
  <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-0">
      <div class="table-responsive" style="overflow: visible;">
        <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 0.88rem;">
          <thead style="background-color: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">
            <tr style="background: #ffffff; color: #1e293b;">
              <th class="text-center py-3" style="width: 60px; background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ລຳດັບ</th>
              <th class="py-3" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ລະຫັດສິນຄ້າ</th>
              <th class="py-3" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ລາຍການສິນຄ້າ</th>
              <th class="text-center py-3" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ຈໍານວນ</th>
              <th class="text-right py-3" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ລາຄາ</th>
              <th class="text-right py-3" style="background: #ffffff; color: #1e293b; border-bottom: 2px solid #e2e8f0;">ລວມເປັນເງິນ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($display_data)): ?>
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="fas fa-fire fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                  ບໍ່ພົບຂໍ້ມູນສິນຄ້າຂາຍດີ
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($display_data as $idx => $row): ?>
                <?php
                  $rank = $offset + $idx + 1;
                ?>
                <tr>
                  <td class="text-center font-weight-bold text-muted"><?php echo $rank; ?></td>
                  <td class="font-weight-bold text-secondary"><?php echo htmlspecialchars($row['save_proid'] ?: '-'); ?></td>
                  <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($row['product_name']); ?></td>
                  <td class="text-center font-weight-bold text-dark" style="font-size: 0.92rem;"><?php echo number_format($row['total_qty_sold']); ?></td>
                  <td class="text-right text-dark"><?php echo number_format($row['avg_price']); ?> ₭</td>
                  <td class="text-right font-weight-bold text-primary" style="font-size: 0.95rem;">
                    <?php echo number_format($row['total_revenue']); ?> ₭
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

    <!-- Table Card Footer: Circular Blue Pagination Aligned Right -->
    <div class="card-footer bg-white border-top py-3 px-3.5 d-flex flex-column flex-md-row justify-content-between align-items-center no-print" style="row-gap: 12px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
      <div class="d-flex align-items-center">
        <!-- <small class="text-muted font-weight-bold" style="font-size: 0.85rem;">
          ສະແດງ <?php echo number_format($offset + 1); ?> ຫາ <?php echo number_format(min($offset + $per_page, $total_records)); ?> ຈາກທັງໝົດ <?php echo number_format($total_records); ?> ລາຍການ
        </small> -->
      </div>

      <nav aria-label="Page navigation" class="ml-auto">
        <ul class="pagination report-pagination mb-0">
          <!-- Previous Page -->
          <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="best_seller.php?page=<?php echo max(1, $page - 1); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&search=<?php echo urlencode($search); ?>">
              <i class="fas fa-chevron-left" style="font-size: 0.76rem;"></i>
            </a>
          </li>

          <?php
            $range = 2;
            $startP = max(1, $page - $range);
            $endP   = min($total_pages, $page + $range);

            if ($startP > 1) {
                echo '<li class="page-item"><a class="page-link" href="best_seller.php?page=1&from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&search=' . urlencode($search) . '">1</a></li>';
                if ($startP > 2) {
                    echo '<li class="page-item disabled"><span class="page-link" style="border:none;">...</span></li>';
                }
            }

            for ($p = $startP; $p <= $endP; $p++) {
                $activeClass = ($p == $page) ? 'active' : '';
                echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="best_seller.php?page=' . $p . '&from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&search=' . urlencode($search) . '">' . $p . '</a></li>';
            }

            if ($endP < $total_pages) {
                if ($endP < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link" style="border:none;">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="best_seller.php?page=' . $total_pages . '&from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&search=' . urlencode($search) . '">' . $total_pages . '</a></li>';
            }
          ?>

          <!-- Next Page -->
          <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="best_seller.php?page=<?php echo min($total_pages, $page + 1); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&search=<?php echo urlencode($search); ?>">
              <i class="fas fa-chevron-right" style="font-size: 0.76rem;"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
</div>

<!-- html2pdf & Shared Report JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<?php require_once __DIR__ . '/partials/js/reports_js.php'; ?>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
