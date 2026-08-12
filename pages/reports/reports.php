<?php
session_start();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../../config/db.php';

// Check permissions
if (!hasPermission('report')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$type = trim($_GET['type'] ?? 'daily');
if (!in_array($type, ['daily', 'all_sales', 'best_seller', 'profit_cost', 'financial', 'category', 'delete_bills'])) {
    $type = 'daily';
}

// Titles and icons mapping
$report_meta = [
    'daily' => [
        'title' => 'ລາຍງານປະຈຳວັນ',
        'icon' => 'fas fa-calendar-day text-info'
    ],
    'all_sales' => [
        'title' => 'ລາຍງານການຂາຍທັງໝົດ',
        'icon' => 'fas fa-file-invoice-dollar text-success'
    ],
    'best_seller' => [
        'title' => 'ລາຍງານສິນຄ້າຂາຍດີ',
        'icon' => 'fas fa-fire text-danger'
    ],
    'profit_cost' => [
        'title' => 'ລາຍງານກຳໄລ-ຕົ້ນທຶນ',
        'icon' => 'fas fa-chart-line text-warning'
    ],
    'financial' => [
        'title' => 'ລາຍງານການເງິນ',
        'icon' => 'fas fa-wallet text-info'
    ],
    'category' => [
        'title' => 'ລາຍງານຕາມ/ປະເພດສິນຄ້າ',
        'icon' => 'fas fa-layer-group text-primary'
    ],
    'delete_bills' => [
        'title' => 'ປະຫວັດການລົບບິນຂາຍ',
        'icon' => 'fas fa-trash-alt text-danger'
    ]
];

$current_meta = $report_meta[$type] ?? [
    'title' => 'ລາຍງານ',
    'icon' => 'fas fa-file-invoice-dollar text-orange'
];

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/reports.css?v=<?php echo filemtime(__DIR__ . '/../../themes/reports.css'); ?>">

<div class="container-fluid p-4">
  <!-- Page Header: Specific Title Only -->
  <div class="row mb-3 align-items-center">
    <div class="col-12">
      <h3 style="font-family: 'Noto Sans Lao Looped'; color: #1a252f; font-weight: 700;">
        <i class="<?php echo $current_meta['icon']; ?> mr-2"></i> <?php echo htmlspecialchars($current_meta['title']); ?>
      </h3>
    </div>
  </div>

  <!-- Clean Blank Container -->
  <div class="card shadow-sm border-0" style="border-radius: 12px; background: white; min-height: 420px; display: flex; align-items: center; justify-content: center;">
    <div class="card-body text-center d-flex flex-column align-items-center justify-content-center py-5">
      <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
        <i class="<?php echo $current_meta['icon']; ?> fa-3x" style="opacity: 0.7;"></i>
      </div>
      <h4 class="font-weight-bold text-dark mb-2" style="font-family: 'Noto Sans Lao Looped';"><?php echo htmlspecialchars($current_meta['title']); ?></h4>
      <p class="text-muted mb-0" style="font-size: 0.95rem;">ໜ້າຈໍນີ້ກຳລັງຢູ່ໃນຂັ້ນຕອນການພັດທະນາ</p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
