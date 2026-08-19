<?php
// POS Master View Page - Modular & Clean Architecture
require_once __DIR__ . '/../../api/pos_backend.php';

$pos_page = true;
require_once __DIR__ . '/../../layouts/header.php';
?>

<?php
$todayDate = date('Y-m-d');
$stmtSeq = $pdo->prepare("SELECT COUNT(*) FROM tbsale_save WHERE sale_date = ?");
$stmtSeq->execute([$todayDate]);
$todaySaleCount = intval($stmtSeq->fetchColumn() ?? 0);
?>
<script>
window.POS_BACKEND_URL = '../../api/pos_backend.php';
window.CURRENT_USER_NAME = "<?php echo htmlspecialchars($_SESSION['fname'] ?? $_SESSION['username'] ?? 'Admin'); ?>";
window.POS_TODAY_SALE_COUNT = <?php echo $todaySaleCount; ?>;
</script>
<link rel="stylesheet" href="../../themes/pos.css?v=<?php echo filemtime(__DIR__ . '/../../themes/pos.css'); ?>">

<div class="pos-wrapper">
  <!-- Mobile Navigation / Tab Switcher (Visible ONLY on Mobile ≤ 991px) -->
  <div class="pos-mobile-nav border-bottom bg-white d-lg-none">
    <div class="d-flex" style="gap: 6px;">
      <button type="button" class="btn flex-fill font-weight-bold btn-primary active py-2 px-2 shadow-sm" id="mobileTabProductsBtn" onclick="switchMobilePosTab('products')" style="border-radius: 10px; font-size: 1.35rem; min-height: 40px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-weight: 800;">
        <i class="fas fa-boxes mr-2" style="font-size: 1.4rem;"></i> ເລືອກສິນຄ້າ
      </button>
      <button type="button" class="btn flex-fill font-weight-bold btn-outline-primary py-2 px-2 shadow-sm" id="mobileTabCartBtn" onclick="switchMobilePosTab('cart')" style="border-radius: 10px; font-size: 1.35rem; min-height: 40px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-weight: 800;">
        <i class="fas fa-shopping-cart mr-2" style="font-size: 1.4rem;"></i> ລາຍການຂາຍ
        <span class="badge badge-danger ml-2 font-weight-bold" id="mobileCartCountBadge" style="display: none; font-size: 0.9rem; padding: 4px 8px; border-radius: 10px;">0</span>
      </button>
    </div>
  </div>

  <!-- Left Side: Products Grid & Barcode Scanner -->
  <div class="pos-products">
    <?php require_once __DIR__ . '/partials/search_and_categories.php'; ?>
    <?php require_once __DIR__ . '/partials/product_grid.php'; ?>
  </div>

  <!-- Right Side: POS Cart Panel -->
  <?php require_once __DIR__ . '/partials/cart_section.php'; ?>
</div>

<!-- Mobile Floating Cart Bar (Visible on Mobile when on Products tab) -->
<div id="mobileFloatingCartBar" class="d-lg-none" style="display: none; position: fixed; bottom: 14px; left: 12px; right: 12px; z-index: 1050;">
  <button type="button" class="btn btn-primary btn-block shadow-lg font-weight-bold py-3 px-3.5 d-flex justify-content-between align-items-center" onclick="switchMobilePosTab('cart')" style="border-radius: 14px; font-size: 1.05rem; min-height: 52px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; box-shadow: 0 8px 24px rgba(37,99,235,0.5) !important;">
    <span><i class="fas fa-shopping-basket mr-2" style="font-size: 1.15rem;"></i> ເບິ່ງລາຍການຂາຍ (<span id="floatingCartItemCount">0 ລາຍການ</span>)</span>
    <span class="font-weight-bold" style="font-size: 1.15rem;" id="floatingCartTotal">0 ₭ <i class="fas fa-chevron-right ml-1"></i></span>
  </button>
</div>

<!-- POS Partial Modals -->
<?php require_once __DIR__ . '/partials/customer_modals.php'; ?>
<?php require_once __DIR__ . '/partials/held_orders_modal.php'; ?>
<?php require_once __DIR__ . '/partials/active_bills_modal.php'; ?>
<?php require_once __DIR__ . '/partials/checkout_modal.php'; ?>

<!-- Hidden Iframe for Thermal Receipt Printing -->
<iframe id="posPrintIframe" style="position: absolute; width: 0; height: 0; border: 0; visibility: hidden;"></iframe>

<!-- POS JavaScript Functionality -->
<?php require_once __DIR__ . '/partials/pos_js.php'; ?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
