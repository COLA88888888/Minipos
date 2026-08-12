<script>
var allProducts = <?php echo json_encode($products); ?>;
var allCustomers = <?php echo json_encode($customersList); ?>;
var cart = [];
var selectedPayType = 'ເງິນສົດ';
var selectedCustomer = { customer_id: null, customer_name: 'ລູກຄ້າທົ່ວໄປ', phone: '' };
var heldOrders = [];
var activeBills = [];
var currentBillId = '';
var isBillOpened = localStorage.getItem('pos_bill_opened') === '1';

function checkBillOpenedOrAlert() {
  if (!isBillOpened) {
    Swal.fire({
      icon: 'warning',
      title: 'ກະລຸນາເປີດບິນກ່ອນ!',
      text: 'ທ່ານຕ້ອງກົດປຸ່ມ "ເປີດບິນໃໝ່" ກ່ອນ ຈຶ່ງສາມາດເລືອກຂາຍສິນຄ້າໄດ້.',
      confirmButtonText: '<i class="fas fa-plus-circle mr-1"></i> ເປີດບິນໃໝ່ດຽວນີ້',
      confirmButtonColor: '#2563eb',
      showCancelButton: true,
      cancelButtonText: 'ຍົກເລີກ',
      cancelButtonColor: '#64748b'
    }).then(function(result) {
      if (result.isConfirmed) {
        createNewBillModal();
      }
    });
    return false;
  }
  return true;
}

try {
  heldOrders = JSON.parse(localStorage.getItem('pos_held_orders') || '[]');
} catch(e) { heldOrders = []; }

$(document).ready(function() {
  // ເຊື່ອງ sidebar ໃນ parent window (dashboard.php) ຕອນໂຫຼດໜ້າ POS
  try {
    window.parent.document.body.classList.add('sidebar-collapse');
  } catch(e) {}

  // ຄືນ sidebar ຕອນອອກຈາກໜ້າ POS
  window.addEventListener('beforeunload', function() {
    try {
      window.parent.document.body.classList.remove('sidebar-collapse');
    } catch(e) {}
  });

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

<?php
// POS Modular JS Includes
require_once __DIR__ . '/js/pos_bills_js.php';
require_once __DIR__ . '/js/pos_customer_js.php';
require_once __DIR__ . '/js/pos_cart_js.php';
require_once __DIR__ . '/js/pos_barcode_js.php';
require_once __DIR__ . '/js/pos_checkout_js.php';
?>
