<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['checked'])) {
    echo "<script>window.top.location.href = '../auth/login.php?expired=1';</script>";
    exit();
}

// 1. Get Years starting from 2021 up to current year
$current_year_real = (int)date('Y');
$start_year = 2021;
$available_years = [];

for ($y = $current_year_real; $y >= $start_year; $y--) {
    $available_years[] = $y;
}

try {
    $yrStmt = $pdo->query("
        SELECT DISTINCT YEAR(sale_date) AS yr 
        FROM tbsale_save 
        WHERE sale_date IS NOT NULL 
          AND sale_date != '0000-00-00' 
          AND (sale_status IS NULL OR sale_status != 'CANCEL')
        ORDER BY yr DESC
    ");
    while ($row = $yrStmt->fetch(PDO::FETCH_ASSOC)) {
        $dbYr = (int)($row['yr'] ?? 0);
        if ($dbYr >= 2021 && !in_array($dbYr, $available_years)) {
            $available_years[] = $dbYr;
        }
    }
} catch (Exception $e) {}

rsort($available_years);

// 2. Filter Inputs & Automatic Year Date Bounds Sync
$selected_year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : $current_year_real;

if (isset($_GET['year'])) {
    if (isset($_GET['from_date']) && !empty($_GET['from_date']) && date('Y', strtotime($_GET['from_date'])) == $selected_year) {
        $from_date = trim($_GET['from_date']);
    } else {
        $from_date = ($selected_year === $current_year_real) ? date('Y-m-01') : "{$selected_year}-01-01";
    }

    if (isset($_GET['to_date']) && !empty($_GET['to_date']) && date('Y', strtotime($_GET['to_date'])) == $selected_year) {
        $to_date = trim($_GET['to_date']);
    } else {
        $to_date = ($selected_year === $current_year_real) ? date('Y-m-d') : "{$selected_year}-12-31";
    }
} else {
    $from_date = trim($_GET['from_date'] ?? date('Y-m-01'));
    $to_date   = trim($_GET['to_date'] ?? date('Y-m-d'));
}

// 3. Build SQL Where for Main Metrics
$where   = ["(s.sale_status IS NULL OR s.sale_status != 'CANCEL')"];
$params  = [];

if (!empty($from_date)) {
    $where[] = "DATE(s.sale_date) >= :from_date";
    $params[':from_date'] = $from_date;
}
if (!empty($to_date)) {
    $where[] = "DATE(s.sale_date) <= :to_date";
    $params[':to_date'] = $to_date;
}
$whereClause = implode(" AND ", $where);

// 4. Fetch 4 Main Cards Stats: Total Sales, Total Cost, Total Profit, Profit %
$total_sales  = 0.00;
$total_cost   = 0.00;
$total_profit = 0.00;
$profit_pct   = 0.00;

try {
    $stmtCards = $pdo->prepare("
        SELECT 
            COALESCE(SUM(s.sale_barlance), SUM(s.sale_amount - COALESCE(s.sale_discount_bill, 0)), 0) AS total_sales,
            COALESCE(SUM(
                (SELECT SUM(COALESCE(d.cost_price, p.bprice, d.save_price * 0.7) * d.save_qty) 
                 FROM tbsale_save_detail d 
                 LEFT JOIN products p ON d.save_proid = p.product_id 
                 WHERE d.save_bill = s.sale_save_bill)
            ), 0) AS total_cost
        FROM tbsale_save s
        WHERE {$whereClause}
    ");
    $stmtCards->execute($params);
    $cardRes = $stmtCards->fetch(PDO::FETCH_ASSOC);

    $total_sales = floatval($cardRes['total_sales'] ?? 0);
    $total_cost  = floatval($cardRes['total_cost'] ?? 0);

    if ($total_sales <= 0) {
        $total_sales  = 0.00;
        $total_cost   = 0.00;
        $total_profit = 0.00;
        $profit_pct   = 0.0;
    } else {
        $total_profit = $total_sales - $total_cost;
        if ($total_profit < 0) {
            $total_profit = 0.00;
        }
        $profit_pct = round(($total_profit / $total_sales) * 100, 1);
    }
} catch (Exception $e) {}

// 5. Monthly Revenue & Profit % Chart Data for Selected Year
$monthly_sales  = array_fill(1, 12, 0.00);
$monthly_cost   = array_fill(1, 12, 0.00);
$monthly_profit = array_fill(1, 12, 0.00);
$monthly_margin = array_fill(1, 12, 0.0);

if ($total_sales > 0) {
    try {
        $stmtMonthly = $pdo->prepare("
            SELECT 
                MONTH(s.sale_date) AS m,
                COALESCE(SUM(s.sale_barlance), 0) AS sales,
                COALESCE(SUM(
                    (SELECT SUM(COALESCE(d.cost_price, p.bprice, d.save_price * 0.7) * d.save_qty) 
                     FROM tbsale_save_detail d 
                     LEFT JOIN products p ON d.save_proid = p.product_id 
                     WHERE d.save_bill = s.sale_save_bill)
                ), 0) AS cost
            FROM tbsale_save s
            WHERE YEAR(s.sale_date) = :yr AND (s.sale_status IS NULL OR s.sale_status != 'CANCEL')
            GROUP BY MONTH(s.sale_date)
        ");
        $stmtMonthly->execute([':yr' => $selected_year]);
        while ($mRow = $stmtMonthly->fetch(PDO::FETCH_ASSOC)) {
            $m = (int)$mRow['m'];
            $s = floatval($mRow['sales']);
            $c = floatval($mRow['cost']);
            $p = $s - $c;
            $monthly_sales[$m]  = $s;
            $monthly_cost[$m]   = $c;
            $monthly_profit[$m] = $p;
            $monthly_margin[$m] = ($s > 0) ? round(($p / $s) * 100, 1) : 0;
        }
    } catch (Exception $e) {}
}

// 6. Yearly Sales Comparison Chart (Include selected year with 0 if no sales)
$yearly_sales_map = [];
foreach ($available_years as $yrItem) {
    $yearly_sales_map[$yrItem] = 0.00;
}

try {
    $stmtYearly = $pdo->query("
        SELECT YEAR(sale_date) AS yr, COALESCE(SUM(sale_barlance), 0) AS total_sales
        FROM tbsale_save
        WHERE sale_date IS NOT NULL AND sale_date != '0000-00-00' AND (sale_status IS NULL OR sale_status != 'CANCEL')
        GROUP BY YEAR(sale_date)
        ORDER BY yr ASC
    ");
    while ($yRow = $stmtYearly->fetch(PDO::FETCH_ASSOC)) {
        $yrVal = (int)$yRow['yr'];
        $yearly_sales_map[$yrVal] = floatval($yRow['total_sales']);
    }
} catch (Exception $e) {}

ksort($yearly_sales_map);

$yearly_labels = [];
$yearly_sales  = [];
foreach ($yearly_sales_map as $yrKey => $yrSalesVal) {
    $yearly_labels[] = 'ປີ ' . $yrKey;
    $yearly_sales[]  = $yrSalesVal;
}

// 7. Top 10 Best Selling Products Chart
$top10_names = [];
$top10_qty   = [];
if ($total_sales > 0) {
    try {
        $stmtTop10 = $pdo->prepare("
            SELECT 
                COALESCE(d.save_proname, p.product_name, 'ສິນຄ້າ') AS pro_name,
                SUM(d.save_qty) AS total_qty
            FROM tbsale_save_detail d
            INNER JOIN tbsale_save s ON d.save_bill = s.sale_save_bill
            LEFT JOIN products p ON d.save_proid = p.product_id
            WHERE {$whereClause}
            GROUP BY d.save_proid, pro_name
            ORDER BY total_qty DESC
            LIMIT 10
        ");
        $stmtTop10->execute($params);
        while ($tRow = $stmtTop10->fetch(PDO::FETCH_ASSOC)) {
            $top10_names[] = $tRow['pro_name'];
            $top10_qty[]   = floatval($tRow['total_qty']);
        }
    } catch (Exception $e) {}
}

$base_path = '../';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="../themes/reports.css?v=<?php echo filemtime(__DIR__ . '/../themes/reports.css'); ?>">

<div class="container-fluid p-3 p-md-4">
  <!-- Header Title & Controls -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3.5 gap-2">
    <div>
      <h4 class="font-weight-bold text-dark mb-1" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
        <i class="fas fa-chart-pie text-primary mr-2"></i> ດາດສ໌ບອດບໍລິຫານ & ວິເຄາະການຂາຍ
      </h4>
      <p class="text-muted mb-0" style="font-size: 0.85rem;">ສະຫຼຸບພາບລວມຍອດຂາຍ, ຕົ້ນທຶນ, ກຳໄລ ແລະ ສະຖິຕິການຂາຍປະຈຳປີ</p>
    </div>
  </div>

  <!-- Modern Filter Box: Date Range + Dynamic Auto Year Selector -->
  <div class="report-filter-box no-print mb-4" style="padding: 14px 18px; border-radius: 12px; background: #ffffff; border: 1.5px solid #e2e8f0; box-shadow: 0 3px 12px rgba(0,0,0,0.03);">
    <form method="GET" action="home.php" class="d-flex align-items-end flex-wrap" style="gap: 12px;">
      <!-- Year Select (Auto-Generated) -->
      <div style="flex: 1 1 140px; min-width: 130px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-check text-primary mr-1"></i> ເລືອກປີ:
        </label>
        <select name="year" class="form-control form-control-sm font-weight-bold" onchange="this.form.from_date.value=''; this.form.to_date.value=''; this.form.submit();" style="border-radius: 8px; height: 38px; font-size: 0.85rem; border: 1.5px solid #2563eb; color: #1e40af; background: #eff6ff;">
          <?php foreach ($available_years as $yr): ?>
            <option value="<?php echo $yr; ?>" <?php echo ($selected_year == $yr) ? 'selected' : ''; ?>>ປີ <?php echo $yr; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- From Date -->
      <div style="flex: 1 1 150px; min-width: 140px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-alt text-primary mr-1"></i> ຕັ້ງແຕ່ວັນທີ:
        </label>
        <input type="date" name="from_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($from_date); ?>" style="border-radius: 8px; height: 38px; font-size: 0.85rem; border: 1.5px solid #cbd5e1;">
      </div>

      <!-- To Date -->
      <div style="flex: 1 1 150px; min-width: 140px;">
        <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 0.82rem; white-space: nowrap;">
          <i class="fas fa-calendar-day text-primary mr-1"></i> ຫາວັນທີ:
        </label>
        <input type="date" name="to_date" class="form-control form-control-sm font-weight-bold" value="<?php echo htmlspecialchars($to_date); ?>" style="border-radius: 8px; height: 38px; font-size: 0.85rem; border: 1.5px solid #cbd5e1;">
      </div>

      <!-- Action Buttons -->
      <div class="d-flex align-items-center" style="flex: 0 0 auto; gap: 6px;">
        <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3.5 d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, #2563eb, #1d4ed8); white-space: nowrap; font-size: 0.85rem;">
          <i class="fas fa-search mr-1.5"></i> ຄົ້ນຫາ
        </button>
        <a href="home.php" class="btn btn-light btn-sm border font-weight-bold d-inline-flex align-items-center justify-content-center px-2.5" title="ລ້າງຄ່າ" style="border-radius: 8px; height: 38px;">
          <i class="fas fa-redo"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Row 1: 4 Key Metrics Cards (Modern Gradient & Animated CountUp) -->
  <div class="row mb-4">
    <!-- Card 1: Total Sales -->
    <div class="col-lg-3 col-md-6 col-6 mb-3 mb-lg-0">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #ffffff;">
        <div class="card-body p-3 p-lg-3.5 position-relative overflow-hidden">
          <div class="d-flex justify-content-between align-items-start mb-1.5">
            <span class="text-white-50 font-weight-bold metric-title">ຍອດຂາຍທັງໝົດ</span>
            <div class="rounded-circle d-flex align-items-center justify-content-center metric-icon-box" style="background: rgba(255,255,255,0.22);">
              <i class="fas fa-cash-register text-white metric-icon"></i>
            </div>
          </div>
          <h4 class="font-weight-bold text-white mb-0" style="font-family: sans-serif; word-break: break-word; white-space: normal; line-height: 1.2;">
            <span class="metric-num counter-num" data-target="<?php echo (float)$total_sales; ?>" data-decimals="0">0</span> <small class="metric-currency">₭</small>
          </h4>
        </div>
      </div>
    </div>

    <!-- Card 2: Total Cost -->
    <div class="col-lg-3 col-md-6 col-6 mb-3 mb-lg-0">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); color: #ffffff;">
        <div class="card-body p-3 p-lg-3.5 position-relative overflow-hidden">
          <div class="d-flex justify-content-between align-items-start mb-1.5">
            <span class="text-white-50 font-weight-bold metric-title">ຕົ້ນທຶນທັງໝົດ</span>
            <div class="rounded-circle d-flex align-items-center justify-content-center metric-icon-box" style="background: rgba(255,255,255,0.22);">
              <i class="fas fa-boxes text-white metric-icon"></i>
            </div>
          </div>
          <h4 class="font-weight-bold text-white mb-0" style="font-family: sans-serif; word-break: break-word; white-space: normal; line-height: 1.2;">
            <span class="metric-num counter-num" data-target="<?php echo (float)$total_cost; ?>" data-decimals="0">0</span> <small class="metric-currency">₭</small>
          </h4>
        </div>
      </div>
    </div>

    <!-- Card 3: Total Profit -->
    <div class="col-lg-3 col-md-6 col-6 mb-3 mb-lg-0">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: #ffffff;">
        <div class="card-body p-3 p-lg-3.5 position-relative overflow-hidden">
          <div class="d-flex justify-content-between align-items-start mb-1.5">
            <span class="text-white-50 font-weight-bold metric-title">ກຳໄລທັງໝົດ</span>
            <div class="rounded-circle d-flex align-items-center justify-content-center metric-icon-box" style="background: rgba(255,255,255,0.22);">
              <i class="fas fa-chart-line text-white metric-icon"></i>
            </div>
          </div>
          <h4 class="font-weight-bold text-white mb-0" style="font-family: sans-serif; word-break: break-word; white-space: normal; line-height: 1.2;">
            <span class="metric-num counter-num" data-target="<?php echo (float)$total_profit; ?>" data-decimals="0">0</span> <small class="metric-currency">₭</small>
          </h4>
        </div>
      </div>
    </div>

    <!-- Card 4: Profit Margin % -->
    <div class="col-lg-3 col-md-6 col-6 mb-3 mb-lg-0">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); color: #ffffff;">
        <div class="card-body p-3 p-lg-3.5 position-relative overflow-hidden">
          <div class="d-flex justify-content-between align-items-start mb-1.5">
            <span class="text-white-50 font-weight-bold metric-title">ກຳໄລ %</span>
            <div class="rounded-circle d-flex align-items-center justify-content-center metric-icon-box" style="background: rgba(255,255,255,0.22);">
              <i class="fas fa-percentage text-white metric-icon"></i>
            </div>
          </div>
          <h4 class="font-weight-bold text-white mb-0" style="font-family: sans-serif; word-break: break-word; white-space: normal; line-height: 1.2;">
            <span class="metric-num counter-num" data-target="<?php echo (float)$profit_pct; ?>" data-decimals="1">0</span>%
          </h4>
        </div>
      </div>
    </div>
  </div>

  <style>
    .metric-title { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px; }
    .metric-num { font-size: 0.95rem; }
    .metric-currency { font-size: 0.7rem; }
    .metric-icon-box { width: 30px; height: 30px; }
    .metric-icon { font-size: 0.9rem; }

    @media (min-width: 992px) {
      .metric-title { font-size: 0.85rem; letter-spacing: 0.5px; }
      .metric-num { font-size: 1.4rem; }
      .metric-currency { font-size: 0.85rem; }
      .metric-icon-box { width: 38px; height: 38px; }
      .metric-icon { font-size: 1.1rem; }
    }
  </style>

  <!-- Row 2: 2 Primary Charts (Monthly Revenue for Selected Year & Yearly Sales Comparison) -->
  <div class="row mb-4">
    <!-- Chart 1: Monthly Revenue for Selected Year -->
    <div class="col-lg-8 mb-4 mb-lg-0">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
        <div class="card-header bg-white border-0 py-3 px-3.5 d-flex justify-content-between align-items-center">
          <h6 class="font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <i class="fas fa-chart-area text-primary mr-2"></i> ກາຟລາຍຮັບປະຈຳເດືອນ (ປີ <?php echo $selected_year; ?>)
          </h6>
          <span class="badge badge-light border text-primary font-weight-bold px-2.5 py-1">12 ເດືອນ</span>
        </div>
        <div class="card-body p-3">
          <div style="position: relative; height: 380px; width: 100%;">
            <canvas id="monthlyRevenueChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Chart 2: Yearly Sales Comparison -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
        <div class="card-header bg-white border-0 py-3 px-3.5 d-flex justify-content-between align-items-center">
          <h6 class="font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <i class="fas fa-calendar-alt text-info mr-2"></i> ກາຟປຽບທຽບຍອດຂາຍປະຈຳປີ
          </h6>
        </div>
        <div class="card-body p-3">
          <div style="position: relative; height: 320px; width: 100%;">
            <canvas id="yearlySalesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 3: 2 Secondary Charts (Profit Margin % & Top 10 Best Sellers) -->
  <div class="row mb-4">
    <!-- Chart 3: Monthly Profit Margin % -->
    <div class="col-lg-5 mb-4 mb-lg-0">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
        <div class="card-header bg-white border-0 py-3 px-3.5 d-flex justify-content-between align-items-center">
          <h6 class="font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <i class="fas fa-percentage text-warning mr-2"></i> ກາຟອັດຕາກຳໄລ % (ປີ <?php echo $selected_year; ?>)
          </h6>
        </div>
        <div class="card-body p-3">
          <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="profitMarginChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Chart 4: Top 10 Best Sellers -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
        <div class="card-header bg-white border-0 py-3 px-3.5 d-flex justify-content-between align-items-center">
          <h6 class="font-weight-bold text-dark mb-0" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <i class="fas fa-fire text-danger mr-2"></i> ກາຟ 10 ລາຍການສິນຄ້າທີ່ຂາຍດີ
          </h6>
          <span class="badge badge-light border text-danger font-weight-bold px-2.5 py-1">Top 10</span>
        </div>
        <div class="card-body p-3">
          <div style="position: relative; min-height: 360px; height: 360px; width: 100%;">
            <canvas id="topSellersChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js Library -->
<script src="../plugins/chart.js/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
$(document).ready(function() {
  // CountUp Number Animation for Metrics Cards
  $('.counter-num').each(function() {
    var $el = $(this);
    var target = parseFloat($el.attr('data-target') || 0);
    var decimals = parseInt($el.attr('data-decimals') || 0, 10);
    if (isNaN(target)) target = 0;
    
    var startTime = null;
    var duration = 1200;
    
    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var ease = 1 - Math.pow(1 - progress, 3);
      var current = ease * target;
      
      if (decimals > 0) {
        $el.text(current.toFixed(decimals));
      } else {
        $el.text(Math.floor(current).toLocaleString());
      }
      
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        if (decimals > 0) {
          $el.text(target.toFixed(decimals));
        } else {
          $el.text(Math.round(target).toLocaleString());
        }
      }
    }
    requestAnimationFrame(step);
  });

  if (typeof Chart === 'undefined') {
    return;
  }

  try {
    Chart.defaults.font = Chart.defaults.font || {};
    Chart.defaults.font.family = "'Noto Sans Lao Looped', 'Phetsarath OT', sans-serif";
  } catch(e) {}

  // Function to format long labels into multi-line arrays
  function formatChartLabel(str, maxLen) {
    maxLen = maxLen || 16;
    if (!str) return '';
    if (typeof str !== 'string') return str;
    if (str.length <= maxLen) return str;
    
    if (str.indexOf(' ') !== -1) {
      var words = str.split(' ');
      var lines = [];
      var currentLine = '';
      for (var i = 0; i < words.length; i++) {
        var w = words[i];
        if ((currentLine ? currentLine + ' ' + w : w).length <= maxLen) {
          currentLine = currentLine ? currentLine + ' ' + w : w;
        } else {
          if (currentLine) lines.push(currentLine);
          currentLine = w;
        }
      }
      if (currentLine) lines.push(currentLine);
      if (lines.length > 1) return lines;
    }
    
    var chunks = [];
    for (var i = 0; i < str.length; i += maxLen) {
      chunks.push(str.substring(i, i + maxLen));
    }
    return chunks;
  }

  // Data variables from PHP
  var monthLabels   = ['ມ.ກ', 'ກ.ພ', 'ມີ.ນາ', 'ເມ.ສາ', 'ພຶ.ພາ', 'ມິ.ຖຸ', 'ກໍ.ກົດ', 'ສ.ຫ', 'ກ.ຍ', 'ຕ.ລ', 'ພ.ຈ', 'ທ.ວ'];
  var monthlySales  = <?php echo json_encode(array_values($monthly_sales)); ?>;
  var monthlyCost   = <?php echo json_encode(array_values($monthly_cost)); ?>;
  var monthlyProfit = <?php echo json_encode(array_values($monthly_profit)); ?>;
  var monthlyMargin = <?php echo json_encode(array_values($monthly_margin)); ?>;

  var yearlyLabels  = <?php echo json_encode($yearly_labels); ?>;
  var yearlySales   = <?php echo json_encode($yearly_sales); ?>;

  var top10Names    = <?php echo json_encode($top10_names); ?>;
  var top10Qty      = <?php echo json_encode($top10_qty); ?>;

  if (!yearlyLabels.length) {
    yearlyLabels = ['ປີ <?php echo $selected_year; ?>'];
    yearlySales  = [0];
  }

  if (!top10Names.length) {
    top10Names = ['ບໍ່ມີຂໍ້ມູນການຂາຍ'];
    top10Qty   = [0];
  }

  // ===== 1. Monthly Revenue & Profit Chart =====
  var ctxMonthly = document.getElementById('monthlyRevenueChart');
  if (ctxMonthly) {
    new Chart(ctxMonthly, {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: [
          {
            label: 'ຍອດຂາຍ (₭)',
            backgroundColor: '#2563eb',
            borderColor: '#1d4ed8',
            data: monthlySales,
            borderRadius: 6
          },
          {
            label: 'ຕົ້ນທຶນ (₭)',
            backgroundColor: '#38bdf8',
            borderColor: '#0284c7',
            data: monthlyCost,
            borderRadius: 6
          },
          {
            label: 'ກຳໄລ (₭)',
            backgroundColor: '#22c55e',
            borderColor: '#16a34a',
            data: monthlyProfit,
            borderRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(val) {
                if (val >= 1000000) {
                  return (val / 1000000).toLocaleString() + 'M';
                } else if (val >= 1000) {
                  return (val / 1000).toLocaleString() + 'k';
                }
                return val;
              }
            }
          }
        }
      }
    });
  }

  // ===== 2. Yearly Sales Comparison Chart =====
  var ctxYearly = document.getElementById('yearlySalesChart');
  if (ctxYearly) {
    new Chart(ctxYearly, {
      type: 'bar',
      data: {
        labels: yearlyLabels,
        datasets: [{
          label: 'ຍອດຂາຍ (₭)',
          backgroundColor: ['#2563eb', '#0284c7', '#16a34a', '#d97706', '#8b5cf6'],
          data: yearlySales,
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(val) { return Number(val).toLocaleString() + ' ₭'; }
            }
          }
        }
      }
    });
  }

  // ===== 3. Profit Margin % Chart =====
  var ctxMargin = document.getElementById('profitMarginChart');
  if (ctxMargin) {
    new Chart(ctxMargin, {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [{
          label: 'ອັດຕາກຳໄລ (%)',
          backgroundColor: 'rgba(217, 119, 6, 0.15)',
          borderColor: '#d97706',
          pointBackgroundColor: '#b45309',
          borderWidth: 3,
          data: monthlyMargin,
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            suggestedMax: 100,
            ticks: {
              callback: function(val) { return val + '%'; }
            }
          }
        }
      }
    });
  }

  // ===== 4. Top 10 Best Sellers Chart =====
  var ctxTop = document.getElementById('topSellersChart');
  if (ctxTop) {
    var formattedTop10Names = top10Names.map(function(name) {
      return formatChartLabel(name, 16);
    });

    new Chart(ctxTop, {
      type: 'bar',
      data: {
        labels: formattedTop10Names,
        datasets: [{
          label: 'ຈຳນວນຂາຍ',
          backgroundColor: '#dc2626',
          borderColor: '#b91c1c',
          data: top10Qty,
          borderRadius: 6
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        layout: {
          padding: {
            left: 5,
            right: 15,
            top: 5,
            bottom: 5
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              title: function(tooltipItems) {
                if (!tooltipItems.length) return '';
                var idx = tooltipItems[0].dataIndex;
                return top10Names[idx] || '';
              }
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              font: {
                family: "'Noto Sans Lao Looped', 'Phetsarath OT', sans-serif"
              }
            }
          },
          y: {
            ticks: {
              autoSkip: false,
              font: {
                family: "'Noto Sans Lao Looped', 'Phetsarath OT', sans-serif",
                size: 11
              }
            }
          }
        }
      }
    });
  }
});
</script>
