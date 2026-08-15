<script>
var allProducts = <?php echo json_encode($products); ?>;
var allCustomers = <?php echo json_encode($customersList); ?>;
var cart = [];
var selectedPayType = 'ເງິນສົດ';
var selectedCustomer = { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' };
var heldOrders = [];
var activeBills = [];
var currentBillId = '';
var isBillOpened = false;

function checkBillOpenedOrAlert() {
  if (!isBillOpened || !activeBills || activeBills.length === 0) {
    var newId = 'BILL-' + Date.now();
    activeBills = [{
      id: newId,
      name: 'ບິນທີ 1',
      time: new Date().toLocaleTimeString('lo-LA', { hour: '2-digit', minute: '2-digit' }),
      customer: { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' },
      cart: [],
      discount: '0'
    }];
    currentBillId = newId;
    isBillOpened = true;
    localStorage.setItem('pos_bill_opened', '1');
    localStorage.setItem('pos_active_bills', JSON.stringify(activeBills));
    if (typeof renderBillTabs === 'function') renderBillTabs();
    if (typeof updateCartUI === 'function') updateCartUI();
  }
  return true;
}

try {
  heldOrders = JSON.parse(localStorage.getItem('pos_held_orders') || '[]');
} catch(e) { heldOrders = []; }
</script>

<?php
// POS Modular JS Includes
require_once __DIR__ . '/js/pos_bills_js.php';
require_once __DIR__ . '/js/pos_customer_js.php';
require_once __DIR__ . '/js/pos_cart_js.php';
require_once __DIR__ . '/js/pos_barcode_js.php';
require_once __DIR__ . '/js/pos_checkout_js.php';
?>

<script>
$(document).ready(function() {
  updateHeldOrdersBadge();
  initActiveBills();

  // ຕອນໂຫຼດໜ້າໃນ Mobile (≤ 991px): ສະແດງໜ້າ "ເລືອກສິນຄ້າ" ເປັນ Default ເລີຍ
  if (window.innerWidth < 992) {
    if (typeof switchMobilePosTab === 'function') {
      switchMobilePosTab('products');
    }
  }

  $(window).on('resize', function() {
    if (window.innerWidth >= 992) {
      $('.pos-products').removeClass('mobile-hidden').show();
      $('.pos-cart').removeClass('mobile-active').show();
      $('#mobileFloatingCartBar').hide();
    } else {
      if (!$('#mobileTabCartBtn').hasClass('active')) {
        switchMobilePosTab('products');
      }
    }
  });
});
</script>
