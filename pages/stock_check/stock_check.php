<?php
session_start();
$base_path = '../../';
require_once __DIR__ . '/../../config/db.php';

// Check if logged in and has access to stock
if (empty($_SESSION['user_id']) || (!hasPermission('stock') && $_SESSION['status'] !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '../../index.php';</script>";
    exit();
}

// Search and Filter variables
$search_query = trim($_GET['search_query'] ?? '');
$category_filter = !empty($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$shelf_filter = !empty($_GET['shelf_id']) ? intval($_GET['shelf_id']) : 0;

$where_clauses = ["1=1"];
$params = [];

if ($search_query !== '') {
    $where_clauses[] = "(p.product_name LIKE ?)";
    $params[] = '%' . $search_query . '%';
}
if ($category_filter > 0) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_filter;
}
if ($shelf_filter > 0) {
    $where_clauses[] = "p.shelf_id = ?";
    $params[] = $shelf_filter;
}

$where_sql = implode(" AND ", $where_clauses);

// Fetch filtered products
$query = "
    SELECT p.*, c.category_name, s.shelf_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN shelves s ON p.shelf_id = s.shelf_id
    WHERE {$where_sql}
    ORDER BY p.stock_qty ASC, p.product_name ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch categories and shelves for filters
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
$shelves = $pdo->query("SELECT * FROM shelves ORDER BY shelf_name ASC")->fetchAll();

require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/stock_check.css?v=<?php echo filemtime(__DIR__ . '/../../themes/stock_check.css'); ?>">

<div class="container-fluid p-4">
  <div class="row mb-3">
    <div class="col-md-8">
      <h3 style="font-family: 'Noto Sans Lao Looped'; color: #1a252f;"><i class="fas fa-clipboard-list mr-2 text-danger"></i> ກວດສອບຍອດເຫຼືອສິນຄ້າ (Stock Audit)</h3>
      <p class="text-muted">ກວດສອບຈຳນວນສິນຄ້າໃນຄັງປະຈຳວັນ, ແຍກຕາມປະເພດ ແລະ ບ່ອນວາງ</p>
    </div>
    <div class="col-md-4 text-right">
      <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print mr-1"></i> ພິມລາຍການກວດສອບ</button>
    </div>
  </div>

  <!-- Search & Filter Card -->
  <div class="card shadow-sm mb-4" style="border-radius: 12px; border: none; background: white;">
    <div class="card-body">
      <form action="" method="GET" class="row">
        <div class="col-md-4 mb-2">
          <label class="form-label" style="font-size:0.85rem; font-weight:600;">ຊອກຫາຊື່ສິນຄ້າ</label>
          <input type="text" name="search_query" class="form-control" placeholder="ປ້ອນຊື່ສິນຄ້າ..." value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        <div class="col-md-3 mb-2">
          <label class="form-label" style="font-size:0.85rem; font-weight:600;">ປະເພດສິນຄ້າ</label>
          <select name="category_id" class="form-control" onchange="filterShelves(this.value)">
            <option value="">-- ທັງໝົດ --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['category_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 mb-2">
          <label class="form-label" style="font-size:0.85rem; font-weight:600;">ບ່ອນວາງ (ຊັ້ນວາງ)</label>
          <select name="shelf_id" id="filter_shelf_id" class="form-control">
            <option value="">-- ທັງໝົດ --</option>
            <?php foreach ($shelves as $sh): ?>
              <option value="<?php echo $sh['shelf_id']; ?>" <?php echo $shelf_filter == $sh['shelf_id'] ? 'selected' : ''; ?> data-cat="<?php echo $sh['category_id']; ?>">
                <?php echo htmlspecialchars($sh['shelf_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 mb-2 d-flex align-items-end">
          <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search mr-1"></i> ຄົ້ນຫາ</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Inventory Table Card -->
  <div class="card shadow-sm" style="border-radius: 12px; border: none; background: white;">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
          <thead class="thead-light">
            <tr>
              <th width="80">ລະຫັດ</th>
              <th>ຊື່ສິນຄ້າ</th>
              <th>ປະເພດສິນຄ້າ</th>
              <th>ບ່ອນວາງ (ຊັ້ນວາງ)</th>
              <th class="text-right">ຈຳນວນໃນຄັງ (ຍ່ອຍ)</th>
              <th>ລາຍລະອຽດຍອດເຫຼືອ (ແຍກຫົວໜ່ວຍ)</th>
              <th class="text-center">ເກນເຕືອນ</th>
              <th class="text-center">ສະຖານະຄັງ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)): ?>
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">ບໍ່ພົບຂໍ້ມູນສິນຄ້າທີ່ຄົ້ນຫາ</td>
              </tr>
            <?php else: ?>
              <?php foreach ($products as $p): ?>
                <?php
                // Math breakdown for remaining stock
                $rem = $p['stock_qty'];
                $breakdown_parts = [];
                
                if ($p['has_case'] && $p['case_multiplier'] > 1) {
                    $cases = floor($rem / $p['case_multiplier']);
                    $rem = $rem % $p['case_multiplier'];
                    if ($cases > 0) {
                        $breakdown_parts[] = "<strong>$cases</strong> " . htmlspecialchars($p['case_unit']);
                    }
                }
                
                if ($p['has_pack'] && $p['pack_multiplier'] > 1) {
                    $packs = floor($rem / $p['pack_multiplier']);
                    $rem = $rem % $p['pack_multiplier'];
                    if ($packs > 0) {
                        $breakdown_parts[] = "<strong>$packs</strong> " . htmlspecialchars($p['pack_unit']);
                    }
                }
                
                if ($rem > 0 || empty($breakdown_parts)) {
                    $breakdown_parts[] = "<strong>$rem</strong> " . htmlspecialchars($p['base_unit']);
                }
                $stock_breakdown_html = implode(' + ', $breakdown_parts);
                
                // Low stock condition
                $is_low = ($p['stock_qty'] <= $p['min_stock_level']);
                $row_class = $is_low ? 'table-danger' : '';
                ?>
                <tr class="<?php echo $row_class; ?>">
                  <td><?php echo $p['product_id']; ?></td>
                  <td class="font-weight-bold"><?php echo htmlspecialchars($p['product_name']); ?></td>
                  <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                  <td class="font-weight-bold text-success"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($p['shelf_name'] ?? 'ບໍ່ໄດ້ກຳນົດ'); ?></td>
                  <td class="text-right font-weight-bold" style="font-size:1.1rem;"><?php echo $p['stock_qty']; ?> <?php echo htmlspecialchars($p['base_unit']); ?></td>
                  <td><?php echo $stock_breakdown_html; ?></td>
                  <td class="text-center text-muted"><?php echo $p['min_stock_level']; ?> <?php echo htmlspecialchars($p['base_unit']); ?></td>
                  <td class="text-center">
                    <?php if ($is_low): ?>
                      <span class="badge badge-danger p-2"><i class="fas fa-exclamation-triangle mr-1"></i> ສິນຄ້າເຫຼືອໜ້ອຍ (ແດງ)</span>
                    <?php else: ?>
                      <span class="badge badge-success p-2"><i class="fas fa-check-circle mr-1"></i> ປົກກະຕິ</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<script>
  // All shelves data as JS object
  var shelves = <?php echo json_encode($shelves); ?>;

  function filterShelves(catId) {
    var select = $('#filter_shelf_id');
    select.empty();
    select.append('<option value="">-- ທັງໝົດ --</option>');
    
    if (catId) {
      var filtered = shelves.filter(function(sh) {
        return sh.category_id == catId;
      });
      
      filtered.forEach(function(sh) {
        select.append('<option value="' + sh.shelf_id + '">' + sh.shelf_name + '</option>');
      });
    } else {
      shelves.forEach(function(sh) {
        select.append('<option value="' + sh.shelf_id + '">' + sh.shelf_name + '</option>');
      });
    }
  }
</script>
