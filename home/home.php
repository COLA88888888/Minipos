<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/dashboard_stats.php';

logDebug("Loading home.php. Session status: checked=" . ($_SESSION['checked'] ?? 'undefined') . ", user_id=" . ($_SESSION['user_id'] ?? 'undefined'));

if (empty($_SESSION['user_id']) || empty($_SESSION['checked'])) {
    logDebug("home.php session check failed! Redirecting window.top to auth/login.php?expired=1");
    echo "<script>window.top.location.href = '../auth/login.php?expired=1';</script>";
    exit();
}

$stats = getDashboardQuickStats($conn);

// Fetch top 5 low stock products to display as a table in dashboard
$low_stock_list = [];
try {
    $low_stock_query = @mysqli_query($conn, "
        SELECT p.product_name, p.qty AS stock_qty, 5 AS min_stock_level, COALESCE(c.category_name, 'ທົ່ວໄປ') AS category_name, '-' AS shelf_name
        FROM products p
        LEFT JOIN category c ON p.category_id = c.category_id
        WHERE p.qty <= 5
        ORDER BY p.qty ASC
        LIMIT 5
    ");
    if ($low_stock_query) {
        while ($row = mysqli_fetch_assoc($low_stock_query)) {
            $low_stock_list[] = $row;
        }
    }
} catch (Throwable $e) {}

// Fetch sales by category for chart
$category_names = [];
$category_sales = [];
try {
    $cat_query = @mysqli_query($conn, "
        SELECT COALESCE(c.category_name, 'ອື່ນໆ') AS category_name, COALESCE(SUM(sd.sale_price * sd.sale_qty), 0) AS total_sales
        FROM category c
        LEFT JOIN products p ON c.category_id = p.category_id
        LEFT JOIN tbsale_save_data sd ON p.product_id = sd.sale_proid
        GROUP BY c.category_id, c.category_name
        ORDER BY total_sales DESC
        LIMIT 6
    ");
    if ($cat_query) {
        while ($row = mysqli_fetch_assoc($cat_query)) {
            $category_names[] = $row['category_name'];
            $category_sales[] = (float)$row['total_sales'];
        }
    }
} catch (Throwable $e) {}

$base_path = '../';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="p-4">
  <!-- Content Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold" style="font-family: 'Noto Sans Lao Looped'; color: #1a252f;"><i class="fas fa-tachometer-alt mr-2 text-primary"></i> ດາດສ໌ບອດບໍລິຫານການຂາຍ</h3>
      <p class="text-muted" style="font-size: 0.9rem;">ສະຫຼຸບພາບລວມຍອດຂາຍ, ກຳໄລ, ພາສີ ແລະ ຄັງສິນຄ້າທັງໝົດໃນລະບົບ</p>
    </div>
    <div class="text-muted font-weight-bold" style="font-size: 0.95rem;">
      <i class="far fa-calendar-alt mr-1"></i> ວັນທີປັດຈຸບັນ: <?php echo date('d/m/Y'); ?>
    </div>
  </div>

  <!-- Row 1: Sales & Profits -->
  <div class="row">
    <!-- Card 1: Total Sales -->
    <div class="col-lg-3 col-6 mb-4">
      <div class="small-box bg-gradient-info p-3 shadow-sm" style="border-radius: 12px; min-height: 120px; color: white;">
        <div class="inner">
          <h4 style="font-weight: 700;"><?php echo formatCurrency($stats['total_sales']); ?></h4>
          <p class="mb-0" style="font-size: 0.9rem;">ຍອດຂາຍທັງໝົດ</p>
        </div>
        <div class="icon" style="position: absolute; right: 15px; top: 15px; font-size: 2.2rem; opacity: 0.3;">
          <i class="fas fa-cash-register"></i>
        </div>
      </div>
    </div>

    <!-- Card 2: Total Profit -->
    <div class="col-lg-3 col-6 mb-4">
      <div class="small-box bg-gradient-success p-3 shadow-sm" style="border-radius: 12px; min-height: 120px; color: white;">
        <div class="inner">
          <h4 style="font-weight: 700;"><?php echo formatCurrency($stats['total_profit']); ?></h4>
          <p class="mb-0" style="font-size: 0.9rem;">ກຳໄລລວມທັງໝົດ</p>
        </div>
        <div class="icon" style="position: absolute; right: 15px; top: 15px; font-size: 2.2rem; opacity: 0.3;">
          <i class="fas fa-hand-holding-usd"></i>
        </div>
      </div>
    </div>

    <!-- Card 3: Collected VAT -->
    <div class="col-lg-3 col-6 mb-4">
      <div class="small-box bg-gradient-secondary p-3 shadow-sm" style="border-radius: 12px; min-height: 120px; color: white; background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
        <div class="inner">
          <h4 style="font-weight: 700;"><?php echo formatCurrency($stats['vat_collected']); ?></h4>
          <p class="mb-0" style="font-size: 0.9rem;">ພາສີທີ່ເກັບໄດ້ (VAT)</p>
        </div>
        <div class="icon" style="position: absolute; right: 15px; top: 15px; font-size: 2.2rem; opacity: 0.3;">
          <i class="fas fa-percent"></i>
        </div>
      </div>
    </div>

    <!-- Card 4: Total Products -->
    <div class="col-lg-3 col-6 mb-4">
      <div class="small-box bg-gradient-primary p-3 shadow-sm" style="border-radius: 12px; min-height: 120px; color: white;">
        <div class="inner">
          <h4 style="font-weight: 700;"><?php echo $stats['total_products']; ?> <span style="font-size: 0.9rem;">ລາຍການ</span></h4>
          <p class="mb-0" style="font-size: 0.9rem;">ສິນຄ້າທັງໝົດ</p>
        </div>
        <div class="icon" style="position: absolute; right: 15px; top: 15px; font-size: 2.2rem; opacity: 0.3;">
          <i class="fas fa-box"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 2: Warnings & Alerts -->
  <div class="row">
    <!-- Card 5: Low Stock Warning -->
    <div class="col-lg-4 col-12 mb-4">
      <a href="../pages/stock_check/stock_check.php" style="text-decoration: none; color: inherit;">
        <div class="small-box bg-gradient-danger p-3 shadow-sm text-white" style="border-radius: 12px; min-height: 110px;">
          <div class="inner">
            <h4 style="font-weight: 700;"><?php echo $stats['low_stock_count']; ?> <span style="font-size:0.9rem;">ລາຍການ</span></h4>
            <p class="mb-0" style="font-size:0.9rem;"><i class="fas fa-cubes"></i> ສິນຄ້າໃກ້ໝົດສາງ (ເຕືອນແດງ)</p>
          </div>
          <div class="icon" style="position: absolute; right: 15px; top: 10px; font-size: 2rem; opacity: 0.3;">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
        </div>
      </a>
    </div>

    <!-- Card 6: Near Expired Warning -->
    <div class="col-lg-4 col-6 mb-4">
      <a href="../pages/expiry_check/expiry_check.php" style="text-decoration: none; color: inherit;">
        <div class="small-box p-3 shadow-sm text-dark" style="border-radius: 12px; min-height: 110px; background: linear-gradient(135deg, #ffe066 0%, #fcc419 100%);">
          <div class="inner">
            <h4 style="font-weight: 700;"><?php echo $stats['near_expired_count']; ?> <span style="font-size:0.9rem;">ລາຍການ</span></h4>
            <p class="mb-0" style="font-size:0.9rem;"><i class="fas fa-hourglass-half"></i> ສິນຄ້າໃກ້ໝົດອາຍຸ (ເຕືອນສົ້ມ)</p>
          </div>
          <div class="icon" style="position: absolute; right: 15px; top: 10px; font-size: 2rem; opacity: 0.2;">
            <i class="fas fa-clock"></i>
          </div>
        </div>
      </a>
    </div>

    <!-- Card 7: Expired Alert -->
    <div class="col-lg-4 col-6 mb-4">
      <a href="../pages/expiry_check/expiry_check.php" style="text-decoration: none; color: inherit;">
        <div class="small-box p-3 shadow-sm text-white" style="border-radius: 12px; min-height: 110px; background: linear-gradient(135deg, #c92a2a 0%, #a61c1c 100%);">
          <div class="inner">
            <h4 style="font-weight: 700;"><?php echo $stats['expired_count']; ?> <span style="font-size:0.9rem;">ລາຍການ</span></h4>
            <p class="mb-0" style="font-size:0.9rem;"><i class="fas fa-skull-crossbones"></i> ສິນຄ້າໝົດອາຍຸແລ້ວ (ແດງ/ດຳ)</p>
          </div>
          <div class="icon" style="position: absolute; right: 15px; top: 10px; font-size: 2rem; opacity: 0.3;">
            <i class="fas fa-calendar-times"></i>
          </div>
        </div>
      </a>
    </div>
  </div>

  <div class="row">
    <!-- Chart Column -->
    <div class="col-lg-7 mb-4">
      <div class="card h-100 shadow-sm" style="border-radius: 12px; border: none; background: white;">
        <div class="card-header bg-white border-0 py-3">
          <h5 class="card-title fw-bold" style="font-family: 'Noto Sans Lao Looped'; color: #2c3e50;">
            <i class="fas fa-chart-bar text-info mr-2"></i> ຍອດຂາຍແຍກຕາມປະເພດສິນຄ້າ
          </h5>
        </div>
        <div class="card-body">
          <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="categorySalesChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Low Stock Table Column -->
    <div class="col-lg-5 mb-4">
      <div class="card h-100 shadow-sm" style="border-radius: 12px; border: none; background: white;">
        <div class="card-header bg-white border-0 py-3">
          <h5 class="card-title fw-bold text-danger" style="font-family: 'Noto Sans Lao Looped';">
            <i class="fas fa-bell mr-2"></i> ສິນຄ້າເຫຼືອໜ້ອຍທີ່ສຸດ 5 ອັນດັບ
          </h5>
        </div>
        <div class="card-body p-0">
          <?php if (empty($low_stock_list)): ?>
            <div class="text-center py-5 text-muted">
              <i class="far fa-check-circle text-success" style="font-size: 3rem; margin-bottom: 10px;"></i>
              <p>ບໍ່ມີສິນຄ້າໃກ້ໝົດສາງ</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0" style="font-size: 0.9rem;">
                <thead class="thead-light">
                  <tr>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th>ປະເພດ</th>
                    <th class="text-center">ຍອດເຫຼືອ</th>
                    <th class="text-center">ເກນເຕືອນ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($low_stock_list as $item): ?>
                    <tr class="table-danger">
                      <td class="font-weight-bold"><?php echo htmlspecialchars($item['product_name']); ?></td>
                      <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                      <td class="text-center text-danger font-weight-bold"><?php echo $item['stock_qty']; ?></td>
                      <td class="text-center text-muted"><?php echo $item['min_stock_level']; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- Chart.js -->
<script src="../plugins/chart.js/Chart.min.js"></script>
<script>
  $(function () {
    var ctx = document.getElementById('categorySalesChart').getContext('2d');
    var categoryNames = <?php echo json_encode($category_names); ?>;
    var categorySales = <?php echo json_encode($category_sales); ?>;
    
    if (categoryNames.length === 0) {
      categoryNames = ["ບໍ່ມີຂໍ້ມູນ"];
      categorySales = [0];
    }

    var chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: categoryNames,
        datasets: [{
          label: 'ຍອດຂາຍ (ກີບ)',
          backgroundColor: '#17a2b8',
          borderColor: '#17a2b8',
          data: categorySales,
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true,
              callback: function(value) {
                return value.toLocaleString() + ' ₭';
              }
            }
          }]
        },
        tooltips: {
          callbacks: {
            label: function(tooltipItem, data) {
              return tooltipItem.yLabel.toLocaleString() + ' ₭';
            }
          }
        }
      }
    });
  });
</script>
