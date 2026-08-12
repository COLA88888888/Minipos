<?php
session_start();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../../config/db.php';

// Check permissions
if (empty($_SESSION['user_id']) || (!hasPermission('accounting') && !hasPermission('report') && $_SESSION['status'] !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// === Handle POST: Income & Expense Recording ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. ADD INC/EXP RECORD
    if ($action === 'add_record') {
        $record_type = $_POST['record_type'] ?? 'expense';
        $category = trim($_POST['category'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0.00);
        $record_date = $_POST['record_date'] ?? date('Y-m-d');
        $note = trim($_POST['note'] ?? '');
        $created_by = $_SESSION['user_id'];

        if ($category !== '' && $amount > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO accounting_records (record_type, category, amount, record_date, note, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$record_type, $category, $amount, $record_date, $note, $created_by]);
                $message = ($record_type === 'income') ? "ບັນທຶກລາຍຮັບອື່ນໆ ສໍາເລັດແລ້ວ!" : "ບັນທຶກລາຍຈ່າຍ ສໍາເລັດແລ້ວ!";
                $message_type = "success";
                logActivity($pdo, "ບັນທຶກບັນຊີ", "ປະເພດ: $record_type, ໝວດ: $category, ຈຳນວນ: $amount ₭");
            } catch (Exception $e) {
                $message = "ຜິດພາດ: " . $e->getMessage();
                $message_type = "danger";
            }
        } else {
            $message = "ກະລຸນາປ້ອນຂໍ້ມູນໝວດ ແລະ ຈຳນວນເງິນໃຫ້ຖືກຕ້ອງ";
            $message_type = "warning";
        }
    }

    // 2. DELETE RECORD
    if ($action === 'delete_record') {
        $record_id = intval($_POST['record_id'] ?? 0);
        if ($record_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM accounting_records WHERE id = ?");
                $stmt->execute([$record_id]);
                $message = "ລົບລາຍການບັນຊີ ສໍາເລັດແລ້ວ!";
                $message_type = "success";
                logActivity($pdo, "ລົບລາຍການບັນຊີ", "ID: $record_id");
            } catch (Exception $e) {
                $message = "ຜິດພາດ: " . $e->getMessage();
                $message_type = "danger";
            }
        }
    }
}

// === Filters & Date Parameters ===
$tab = $_GET['tab'] ?? 'employee_sales';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$seller_id = isset($_GET['seller_id']) && $_GET['seller_id'] !== '' ? intval($_GET['seller_id']) : 0;

// Fetch all employees/sellers for dropdown
$sellers_stmt = $pdo->query("SELECT Id, username, status FROM tbuser ORDER BY Id ASC");
$all_sellers = $sellers_stmt->fetchAll();

// === Query 1: Employee Sales Breakdown ===
$sales_where = ["DATE(sales.created_at) BETWEEN ? AND ?"];
$sales_params = [$start_date, $end_date];

if ($seller_id > 0) {
    $sales_where[] = "sales.sold_by = ?";
    $sales_params[] = $seller_id;
}

$sales_where_sql = implode(' AND ', $sales_where);

// Summary Stats per Employee or Overall
$emp_summary_sql = "
    SELECT 
        u.Id as seller_id,
        u.username as seller_name,
        u.status as seller_role,
        COUNT(s.sale_id) as total_bills,
        COALESCE(SUM(s.subtotal), 0) as raw_subtotal,
        COALESCE(SUM(s.discount_amount), 0) as total_discount,
        COALESCE(SUM(s.vat_amount), 0) as total_vat,
        COALESCE(SUM(s.total_amount), 0) as net_sales,
        COALESCE(SUM(s.total_profit), 0) as total_profit
    FROM tbuser u
    LEFT JOIN sales s ON u.Id = s.sold_by AND DATE(s.created_at) BETWEEN ? AND ?
    GROUP BY u.Id, u.username, u.status
    ORDER BY net_sales DESC
";
$emp_summary_stmt = $pdo->prepare($emp_summary_sql);
$emp_summary_stmt->execute([$start_date, $end_date]);
$employee_stats = $emp_summary_stmt->fetchAll();

// Detailed Sales List for Table
$detail_sales_sql = "
    SELECT s.*, u.username as seller_name, u.status as seller_role
    FROM sales s
    LEFT JOIN tbuser u ON s.sold_by = u.Id
    WHERE {$sales_where_sql}
    ORDER BY s.created_at DESC
    LIMIT 100
";
$detail_sales_stmt = $pdo->prepare($detail_sales_sql);
$detail_sales_stmt->execute($sales_params);
$detailed_sales = $detail_sales_stmt->fetchAll();

// Aggregate totals for the active sales filter
$total_filtered_sales = 0.00;
$total_filtered_bills = count($detailed_sales);
$total_filtered_profit = 0.00;
$total_filtered_discount = 0.00;
foreach ($detailed_sales as $ds) {
    $total_filtered_sales += (float)$ds['total_amount'];
    $total_filtered_profit += (float)$ds['total_profit'];
    $total_filtered_discount += (float)$ds['discount_amount'];
}

// === Query 2: Accounting Records (Income & Expenses) ===
$acc_stmt = $pdo->prepare("
    SELECT r.*, u.username as creator_name 
    FROM accounting_records r
    LEFT JOIN tbuser u ON r.created_by = u.Id
    WHERE r.record_date BETWEEN ? AND ?
    ORDER BY r.record_date DESC, r.id DESC
");
$acc_stmt->execute([$start_date, $end_date]);
$accounting_records = $acc_stmt->fetchAll();

$total_expenses = 0.00;
$total_other_income = 0.00;
foreach ($accounting_records as $ar) {
    if ($ar['record_type'] === 'income') {
        $total_other_income += (float)$ar['amount'];
    } else {
        $total_expenses += (float)$ar['amount'];
    }
}

// Net Operating Cash Flow
$grand_total_revenue = $total_filtered_sales + $total_other_income;
$net_operating_balance = $grand_total_revenue - $total_expenses;

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/accounting.css?v=<?php echo filemtime(__DIR__ . '/../../themes/accounting.css'); ?>">

<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    <div>
      <h3 class="font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped';">
        <i class="fas fa-calculator text-primary mr-2"></i> ຈັດການບັນຊີຕ່າງຫາກ & ບັນຊີແຍກຕາມພະນັກງານ
      </h3>
      <p class="text-muted mb-0 style-sub" style="font-size: 0.9rem;">
        ລະບົບຄິດໄລ່ບັນຊີ, ບັນທຶກລາຍຮັບ-ລາຍຈ່າຍ, ແລະ ແຍກຍອດຂາຍຂອງພະນັກງານແຕ່ລະຄົນ
      </p>
    </div>
    <div class="d-flex align-items-center mt-2 mt-md-0">
      <button onclick="window.print();" class="btn btn-outline-secondary font-weight-bold mr-2" style="border-radius: 8px;">
        <i class="fas fa-print mr-1"></i> ພິມລາຍງານບັນຊີ
      </button>
      <button class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#addRecordModal" style="border-radius: 8px;">
        <i class="fas fa-plus-circle mr-1"></i> ບັນທຶກລາຍຮັບ - ລາຍຈ່າຍ
      </button>
    </div>
  </div>

  <?php if ($message !== ''): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert" style="border-radius: 8px;">
      <?php echo htmlspecialchars($message); ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- Global Filter Bar -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: white;">
    <div class="card-body py-3">
      <form method="GET" class="row align-items-center">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
        <div class="col-md-3 mb-2 mb-md-0">
          <label class="font-weight-bold text-muted mb-1" style="font-size: 0.85rem;"><i class="fas fa-calendar-alt mr-1"></i> ຕັ້ງແຕ່ວັນທີ:</label>
          <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($start_date); ?>">
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
          <label class="font-weight-bold text-muted mb-1" style="font-size: 0.85rem;"><i class="fas fa-calendar-check mr-1"></i> ຫາວັນທີ:</label>
          <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($end_date); ?>">
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
          <label class="font-weight-bold text-muted mb-1" style="font-size: 0.85rem;"><i class="fas fa-user-tag mr-1"></i> ພະນັກງານຂາຍ:</label>
          <select name="seller_id" class="form-control form-control-sm">
            <option value="">-- ທຸກພະນັກງານຂາຍ --</option>
            <?php foreach ($all_sellers as $s): ?>
              <option value="<?php echo $s['Id']; ?>" <?php echo ($seller_id == $s['Id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($s['username']); ?> (<?php echo htmlspecialchars($s['status'] ?? $s['userstatus'] ?? 'ພະນັກງານ'); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-info btn-sm btn-block font-weight-bold" style="border-radius: 6px; margin-top: 24px;">
            <i class="fas fa-filter mr-1"></i> ດຶງຂໍ້ມູນ
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Accounting Overview Metric Cards -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
      <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
          <div>
            <span class="text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px; opacity: 0.9;">ຍອດຂາຍ POS ລວມ</span>
            <h3 class="font-weight-bold mb-0 mt-1" style="font-size: 1.4rem;"><?php echo formatCurrency($total_filtered_sales); ?></h3>
            <small style="opacity: 0.85;"><?php echo $total_filtered_bills; ?> ໃບບິນ</small>
          </div>
          <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; opacity: 0.9;">
            <i class="fas fa-cash-register fa-lg"></i>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
      <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
          <div>
            <span class="text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px; opacity: 0.9;">ລາຍຮັບອື່ນໆ</span>
            <h3 class="font-weight-bold mb-0 mt-1" style="font-size: 1.4rem;"><?php echo formatCurrency($total_other_income); ?></h3>
            <small style="opacity: 0.85;">ລາຍຮັບນອກ POS</small>
          </div>
          <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; opacity: 0.9;">
            <i class="fas fa-hand-holding-usd fa-lg"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
      <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
          <div>
            <span class="text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px; opacity: 0.9;">ລາຍຈ່າຍທັງໝົດ</span>
            <h3 class="font-weight-bold mb-0 mt-1" style="font-size: 1.4rem;"><?php echo formatCurrency($total_expenses); ?></h3>
            <small style="opacity: 0.85;">ຄ່າໃຊ້ຈ່າຍບໍລິຫານ/ຄັງ</small>
          </div>
          <div class="bg-white text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; opacity: 0.9;">
            <i class="fas fa-receipt fa-lg"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
          <div>
            <span class="text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px; opacity: 0.9;">ຍອດເຫຼືອບັນຊີສຸດທິ</span>
            <h3 class="font-weight-bold mb-0 mt-1" style="font-size: 1.4rem;"><?php echo formatCurrency($net_operating_balance); ?></h3>
            <small style="opacity: 0.85;">ກະແສເງິນສົດສຸດທິ</small>
          </div>
          <div class="bg-white text-purple rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; opacity: 0.9; color: #8b5cf6;">
            <i class="fas fa-wallet fa-lg"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Accounting Tabs Navigation -->
  <ul class="nav nav-pills mb-3 bg-white p-2 shadow-sm rounded-lg border-0" id="accountingTab" role="tablist">
    <li class="nav-item">
      <a class="nav-link font-weight-bold <?php echo ($tab === 'employee_sales') ? 'active' : ''; ?>" href="?tab=employee_sales&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&seller_id=<?php echo $seller_id; ?>">
        <i class="fas fa-users-cog mr-1"></i> 1. ບັນຊີແຍກຕາມພະນັກງານຂາຍ
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link font-weight-bold <?php echo ($tab === 'income_expense') ? 'active' : ''; ?>" href="?tab=income_expense&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&seller_id=<?php echo $seller_id; ?>">
        <i class="fas fa-file-invoice-dollar mr-1"></i> 2. ຈັດການລາຍຮັບ - ລາຍຈ່າຍ
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link font-weight-bold <?php echo ($tab === 'ledger_summary') ? 'active' : ''; ?>" href="?tab=ledger_summary&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&seller_id=<?php echo $seller_id; ?>">
        <i class="fas fa-chart-pie mr-1"></i> 3. ສະຫຼຸບບັນຊີ & ງົບດຸນ
      </a>
    </li>
  </ul>

  <!-- TAB CONTENT 1: Employee Sales Breakdown -->
  <?php if ($tab === 'employee_sales'): ?>
    <div class="row">
      <!-- Employee Summary Cards -->
      <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
          <div class="card-header bg-white font-weight-bold py-3 border-0">
            <i class="fas fa-user-check text-info mr-2"></i> ສັງລວມຍອດຂາຍແຍກຕາມແຕ່ລະພະນັກງານ
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <?php foreach ($employee_stats as $es): ?>
                <a href="?tab=employee_sales&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&seller_id=<?php echo $es['seller_id']; ?>" 
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 <?php echo ($seller_id == $es['seller_id']) ? 'bg-light' : ''; ?>">
                  <div>
                    <span class="font-weight-bold text-dark d-block"><?php echo htmlspecialchars($es['seller_name']); ?></span>
                    <small class="text-muted">
                      <?php echo htmlspecialchars($es['seller_role'] ?? 'ພະນັກງານ'); ?> | <?php echo $es['total_bills']; ?> ບິນ
                    </small>
                  </div>
                  <div class="text-right">
                    <span class="font-weight-bold text-success d-block"><?php echo formatCurrency($es['net_sales']); ?></span>
                    <small class="text-muted">ກຳໄລ: <?php echo formatCurrency($es['total_profit']); ?></small>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Sales Bills for Selected Employee -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
          <div class="card-header bg-white font-weight-bold py-3 border-0 d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list-alt text-primary mr-2"></i> ປະຫວັດການຂາຍ & ບິນຂອງພະນັກງານ</span>
            <span class="badge badge-primary px-3 py-2" style="font-size: 0.85rem;">
              ລວມ: <?php echo formatCurrency($total_filtered_sales); ?>
            </span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.88rem;">
                <thead style="background: #f8fafc; color: #475569;">
                  <tr>
                    <th>ເລກທີໃບບິນ</th>
                    <th>ວັນທີ-ເວລາ</th>
                    <th>ພະນັກງານຂາຍ</th>
                    <th class="text-right">ຍອດລວມ</th>
                    <th class="text-right">ສ່ວນຫຼຸດ</th>
                    <th class="text-right">ຍອດສຸດທິ</th>
                    <th class="text-right">ກຳໄລ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($detailed_sales)): ?>
                    <tr>
                      <td colspan="7" class="text-center py-4 text-muted">ບໍ່ພົບຂໍ້ມູນການຂາຍໃນຊ່ວງເວລານີ້</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($detailed_sales as $ds): ?>
                      <tr>
                        <td class="font-weight-bold text-primary"><?php echo htmlspecialchars($ds['invoice_number']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($ds['created_at'])); ?></td>
                        <td>
                          <span class="badge badge-light border px-2 py-1">
                            <i class="fas fa-user-circle mr-1 text-info"></i><?php echo htmlspecialchars($ds['seller_name']); ?>
                          </span>
                        </td>
                        <td class="text-right font-weight-bold"><?php echo formatCurrency($ds['subtotal']); ?></td>
                        <td class="text-right text-danger"><?php echo formatCurrency($ds['discount_amount']); ?></td>
                        <td class="text-right font-weight-bold text-success"><?php echo formatCurrency($ds['total_amount']); ?></td>
                        <td class="text-right text-info font-weight-bold"><?php echo formatCurrency($ds['total_profit']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- TAB CONTENT 2: Income & Expense Management -->
  <?php if ($tab === 'income_expense'): ?>
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
      <div class="card-header bg-white font-weight-bold py-3 border-0 d-flex justify-content-between align-items-center">
        <span><i class="fas fa-exchange-alt text-warning mr-2"></i> ລາຍການລາຍຮັບ - ລາຍຈ່າຍ ປະຈຳວັນ</span>
        <div>
          <span class="badge badge-success mr-2 px-3 py-2" style="font-size: 0.85rem;">ລາຍຮັບອື່ນໆ: <?php echo formatCurrency($total_other_income); ?></span>
          <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">ລາຍຈ່າຍ: <?php echo formatCurrency($total_expenses); ?></span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.88rem;">
            <thead style="background: #f8fafc; color: #475569;">
              <tr>
                <th class="text-center" style="width: 60px;">ID</th>
                <th>ວັນທີ</th>
                <th>ປະເພດ</th>
                <th>ໝວດ/ລາຍລະອຽດ</th>
                <th>ໝາຍເຫດ</th>
                <th class="text-right">ຈຳນວນເງິນ (₭)</th>
                <th>ຜູ້ບັນທຶກ</th>
                <th class="text-center" style="width: 80px;">ຈັດການ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($accounting_records)): ?>
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">ບໍ່ມີລາຍການລາຍຮັບ - ລາຍຈ່າຍ ໃນໄລຍະເວລານີ້</td>
                </tr>
              <?php else: ?>
                <?php foreach ($accounting_records as $ar): ?>
                  <tr>
                    <td class="text-center font-weight-bold text-muted"><?php echo $ar['id']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($ar['record_date'])); ?></td>
                    <td>
                      <?php if ($ar['record_type'] === 'income'): ?>
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-arrow-down mr-1"></i> ລາຍຮັບ</span>
                      <?php else: ?>
                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-arrow-up mr-1"></i> ລາຍຈ່າຍ</span>
                      <?php endif; ?>
                    </td>
                    <td class="font-weight-bold text-dark"><?php echo htmlspecialchars($ar['category']); ?></td>
                    <td class="text-muted"><?php echo htmlspecialchars($ar['note'] ?? '-'); ?></td>
                    <td class="text-right font-weight-bold <?php echo ($ar['record_type'] === 'income') ? 'text-success' : 'text-danger'; ?>">
                      <?php echo ($ar['record_type'] === 'income' ? '+' : '-') . formatCurrency($ar['amount']); ?>
                    </td>
                    <td><small class="text-muted"><?php echo htmlspecialchars($ar['creator_name'] ?? 'Admin'); ?></small></td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteRecord(<?php echo $ar['id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- TAB CONTENT 3: Ledger Summary -->
  <?php if ($tab === 'ledger_summary'): ?>
    <div class="row">
      <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
          <div class="card-header bg-white font-weight-bold py-3 border-0">
            <i class="fas fa-balance-scale text-primary mr-2"></i> ໃບສະຫຼຸບງົບດຸນການເງິນ & ຜົນຮ່ວມ (Financial Ledger)
          </div>
          <div class="card-body p-4">
            <table class="table table-bordered align-middle">
              <tbody>
                <tr class="bg-light">
                  <th colspan="2" class="text-dark font-weight-bold"><i class="fas fa-coins text-success mr-2"></i> 1. ລາຍຮັບທັງໝົດ (Total Revenues)</th>
                </tr>
                <tr>
                  <td>- ຍອດຂາຍຈາກ POS (Sales Revenue)</td>
                  <td class="text-right font-weight-bold text-success"><?php echo formatCurrency($total_filtered_sales); ?></td>
                </tr>
                <tr>
                  <td>- ລາຍຮັບອື່ນໆ (Other Operating Incomes)</td>
                  <td class="text-right font-weight-bold text-success"><?php echo formatCurrency($total_other_income); ?></td>
                </tr>
                <tr class="font-weight-bold" style="background: #f0fdf4;">
                  <td>ລວມລາຍຮັບທັງໝົດ (Gross Revenue)</td>
                  <td class="text-right text-success font-weight-bold" style="font-size: 1.1rem;"><?php echo formatCurrency($grand_total_revenue); ?></td>
                </tr>

                <tr class="bg-light">
                  <th colspan="2" class="text-dark font-weight-bold"><i class="fas fa-minus-circle text-danger mr-2"></i> 2. ລາຍຈ່າຍ & ຕົ້ນທຶນ (Total Expenses & Deductions)</th>
                </tr>
                <tr>
                  <td>- ສ່ວນຫຼຸດບິນຂາຍ (Sales Discounts)</td>
                  <td class="text-right text-danger"><?php echo formatCurrency($total_filtered_discount); ?></td>
                </tr>
                <tr>
                  <td>- ລາຍຈ່າຍບໍລິຫານ & ຄັງ (Operating Expenses)</td>
                  <td class="text-right text-danger"><?php echo formatCurrency($total_expenses); ?></td>
                </tr>
                <tr class="font-weight-bold" style="background: #fef2f2;">
                  <td>ລວມລາຍຈ່າຍທັງໝົດ (Total Expenses)</td>
                  <td class="text-right text-danger font-weight-bold" style="font-size: 1.1rem;"><?php echo formatCurrency($total_expenses + $total_filtered_discount); ?></td>
                </tr>

                <tr style="background: #f8fafc;">
                  <th class="font-weight-bold text-dark" style="font-size: 1.1rem;"><i class="fas fa-trophy text-warning mr-2"></i> 3. ຍອດເຫຼືອບັນຊີສຸດທິ (Net Operating Balance)</th>
                  <th class="text-right font-weight-bold text-primary" style="font-size: 1.3rem;">
                    <?php echo formatCurrency($net_operating_balance); ?>
                  </th>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: white;">
          <div class="card-header bg-white font-weight-bold py-3 border-0">
            <i class="fas fa-user-shield text-info mr-2"></i> ຜູ້ຈັດການບັນຊີ / ການກວດສອບ
          </div>
          <div class="card-body p-4 text-center">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
              <i class="fas fa-check-double text-success fa-2x"></i>
            </div>
            <h5 class="font-weight-bold text-dark">ກວດສອບບັນຊີຮຽບຮ້ອຍ</h5>
            <p class="text-muted" style="font-size: 0.88rem;">
              ລາຍງານບັນຊີນີ້ຖືກຄິດໄລ່ໂດຍອັດໂນມັດຈາກຖານຂໍ້ມູນ POS ແລະ ລະບົບບັນທຶກລາຍຮັບ-ລາຍຈ່າຍ.
            </p>
            <hr>
            <div class="text-left font-weight-bold text-muted mb-1" style="font-size: 0.85rem;">ລາຍຊື່ຜູ້ກວດສອບ:</div>
            <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded mb-2">
              <span class="font-weight-bold text-dark"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Accountant'); ?></span>
              <span class="badge badge-purple px-2 py-1" style="background: #8b5cf6; color: white;">
                <?php echo htmlspecialchars($_SESSION['status'] ?? 'ຄົນຈັດການບັນຊີ'); ?>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- ADD RECORD MODAL -->
<div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
      <form method="POST">
        <input type="hidden" name="action" value="add_record">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> ບັນທຶກລາຍຮັບ - ລາຍຈ່າຍ ໃໝ່</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group">
            <label class="font-weight-bold">ປະເພດລາຍການ:<span class="text-danger">*</span></label>
            <select name="record_type" class="form-control" required>
              <option value="expense">ລາຍຈ່າຍ (Expense)</option>
              <option value="income">ລາຍຮັບອື່ນໆ (Income)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">ໝວດ/ລາຍລະອຽດ:<span class="text-danger">*</span></label>
            <input type="text" name="category" class="form-control" placeholder="ເຊັ່ນ: ຄ່າໄຟຟ້າ, ຄ່ານໍ້າ, ເງິນດ່ວນ, ຄ່າຕົ້ນທຶນ..." required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">ຈຳນວນເງິນ (ກີບ ₭):<span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">ວັນທີ:<span class="text-danger">*</span></label>
            <input type="date" name="record_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">ໝາຍເຫດເພີ່ມເຕີມ:</label>
            <textarea name="note" class="form-control" rows="2" placeholder="ໃສ່ລາຍລະອຽດ ຫຼື ອ້າງອີງໃບບິນ..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">ຍົກເລີກ</button>
          <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> ບັນທຶກລາຍການ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- DELETE RECORD FORM -->
<form id="deleteRecordForm" method="POST" style="display:none;">
  <input type="hidden" name="action" value="delete_record">
  <input type="hidden" name="record_id" id="delete_record_id">
</form>

<script>
function confirmDeleteRecord(id) {
  Swal.fire({
    title: 'ຢືນຢັນການລົບລາຍການ',
    text: 'ທ່ານຕ້ອງການລົບລາຍການບັນຊີນີ້ແທ້ຫຼືບໍ່?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'ລົບເລີຍ',
    cancelButtonText: 'ຍົກເລີກ'
  }).then(function(res) {
    if (res.isConfirmed) {
      document.getElementById('delete_record_id').value = id;
      document.getElementById('deleteRecordForm').submit();
    }
  });
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
