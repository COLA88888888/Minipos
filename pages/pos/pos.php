<?php
// POS Master View Page - Modular & Clean Architecture
require_once __DIR__ . '/partials/pos_backend.php';

$pos_page = true;
require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="../../themes/pos.css?v=<?php echo filemtime(__DIR__ . '/../../themes/pos.css'); ?>">

<div class="pos-wrapper">
  <!-- Mobile Navigation / Tab Switcher (Visible ONLY on Mobile ≤ 991px) -->
  <div class="pos-mobile-nav border-bottom bg-white shadow-sm mb-2 p-1.5 d-lg-none" style="position: sticky; top: 0; z-index: 1000; border-radius: 10px;">
    <div class="d-flex" style="gap: 6px;">
      <button type="button" class="btn btn-sm flex-fill font-weight-bold py-2 btn-primary active" id="mobileTabProductsBtn" onclick="switchMobilePosTab('products')" style="border-radius: 8px; font-size: 0.88rem; transition: all 0.2s;">
        <i class="fas fa-boxes mr-1.5"></i> ເລືອກສິນຄ້າ
      </button>
      <button type="button" class="btn btn-sm flex-fill font-weight-bold py-2 btn-outline-primary" id="mobileTabCartBtn" onclick="switchMobilePosTab('cart')" style="border-radius: 8px; font-size: 0.88rem; transition: all 0.2s;">
        <i class="fas fa-shopping-cart mr-1.5"></i> ລາຍການຂາຍ
        <span class="badge badge-danger ml-1 font-weight-bold" id="mobileCartCountBadge" style="display: none; font-size: 0.75rem;">0</span>
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
  <button type="button" class="btn btn-primary btn-block shadow-lg font-weight-bold py-2.5 px-3 d-flex justify-content-between align-items-center" onclick="switchMobilePosTab('cart')" style="border-radius: 12px; font-size: 0.92rem; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; box-shadow: 0 6px 20px rgba(37,99,235,0.45) !important;">
    <span><i class="fas fa-shopping-basket mr-2"></i> ເບິ່ງລາຍການຂາຍ (<span id="floatingCartItemCount">0 ລາຍການ</span>)</span>
    <span class="font-weight-bold" style="font-size: 1.02rem;" id="floatingCartTotal">0 ₭ <i class="fas fa-chevron-right ml-1"></i></span>
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
