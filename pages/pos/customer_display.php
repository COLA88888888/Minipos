<?php
// ============================================================
// customer_display.php - DUAL-SCREEN POS CUSTOMER DISPLAY
// ໜ້າຈໍສະແດງຜົນລູກຄ້າ Real-Time (POS System Primary Blue Theme)
// ============================================================
require_once __DIR__ . '/../../config/db.php';

// Store Info Retrieval directly from Database
$company = [
    'com_name'    => '',
    'com_tel'     => '',
    'com_address' => '',
    'com_logo'    => ''
];

try {
    if (isset($pdo)) {
        $cinfo = $pdo->query("SELECT * FROM tbcompanyinfo LIMIT 1")->fetch();
        if ($cinfo) {
            $company['com_name']    = !empty($cinfo['com_name_la']) ? $cinfo['com_name_la'] : 'POS Retail Store';
            $company['com_tel']     = $cinfo['com_tel'] ?? '';
            $company['com_address'] = $cinfo['com_address'] ?? '';
            $company['qr_img']      = !empty($cinfo['qr_img']) ? ('../../assets/img/qr/' . $cinfo['qr_img']) : '';
            if (!empty($cinfo['img_url'])) {
                $company['com_logo'] = '../../assets/img/logo/' . $cinfo['img_url'];
            }
        } else {
            $company['com_name'] = 'POS Retail Store';
        }
    }
} catch (Throwable $e) {}

$base_path = '../../';
?>
<!DOCTYPE html>
<html lang="lo">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer Display - <?php echo htmlspecialchars($company['com_name']); ?></title>
  
  <link rel="shortcut icon" href="<?php echo htmlspecialchars($company['com_logo']); ?>" type="image/x-icon">
  <link rel="icon" href="<?php echo htmlspecialchars($company['com_logo']); ?>" type="image/png">
  
  <link rel="stylesheet" href="<?php echo $base_path; ?>plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/local-font.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/main.min.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/global-custom.css">
  
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      background: #f8fafc;
      color: #0f172a;
      font-family: 'Noto Sans Lao Looped', 'Noto Sans Lao', sans-serif;
      overflow: hidden;
      user-select: none;
    }

    .display-wrapper {
      display: flex;
      flex-direction: column;
      height: 100vh;
      background: #f8fafc;
    }
    
    /* ===== 1. TOP HEADER BAR: BLUE THEME & SYSTEM TITLE ===== */
    .display-header {
      background: linear-gradient(135deg, #0263ff 0%, #004ed6 100%);
      padding: 14px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 20px rgba(2, 99, 255, 0.35);
      z-index: 100;
    }
    .header-system-title {
      color: #ffffff;
      font-size: 1.45rem;
      font-weight: 800;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .customer-info-badge {
      background: rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(10px);
      padding: 8px 20px;
      border-radius: 40px;
      border: 1.5px solid rgba(255, 255, 255, 0.4);
      display: flex;
      align-items: center;
      color: #ffffff;
      gap: 10px;
    }

    .clock-badge {
      background: rgba(0, 0, 0, 0.22);
      padding: 6px 18px;
      border-radius: 30px;
      border: 1px solid rgba(255, 255, 255, 0.25);
    }

    /* ===== MAIN DUAL PANEL (Left = Large Logo & Store Info, Right = Order List & Payment) ===== */
    .display-main {
      flex: 1;
      display: flex;
      overflow: hidden;
      padding: 16px;
      gap: 16px;
    }

    /* ===== LEFT PANEL: LARGE STORE LOGO & STORE INFO (40%) ===== */
    .left-store-panel {
      flex: 0.85;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .store-card {
      flex: 1;
      background: #ffffff;
      border-radius: 8px;
      border: 2px solid #cbd5e1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 36px 28px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.05);
      position: relative;
    }

    .large-logo-box {
      width: 220px;
      height: 220px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
    }
    .large-logo-img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 6px 12px rgba(0,0,0,0.12));
    }
    .large-fallback-icon {
      font-size: 5.5rem;
      color: #0263ff;
    }

    .store-name-title {
      font-size: 2.2rem;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 8px;
      line-height: 1.2;
    }
    .store-info-text {
      font-size: 1.1rem;
      color: #475569;
      font-weight: 600;
      max-width: 90%;
      margin-bottom: 16px;
      line-height: 1.5;
    }

    .welcome-banner-box {
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      border: 2px solid #bfdbfe;
      border-radius: 8px;
      padding: 20px 24px;
      width: 92%;
      margin-top: 15px;
      box-shadow: 0 6px 20px rgba(2, 99, 255, 0.08);
    }

    /* ===== RIGHT PANEL: ORDER LIST & PAYMENT SUMMARY (60%) ===== */
    .right-order-panel {
      flex: 1.15;
      display: flex;
      flex-direction: column;
      gap: 12px;
      overflow: hidden;
    }
    
    .cart-card {
      flex: 1;
      background: #ffffff;
      border-radius: 8px;
      border: 2px solid #cbd5e1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    }

    .display-table-head {
      background: linear-gradient(135deg, #0263ff 0%, #004ed6 100%);
      color: #ffffff;
      font-weight: 700;
      font-size: 1.0rem;
    }
    .display-table-head th {
      padding: 12px 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .display-table-body {
      overflow-y: auto;
      flex: 1;
      scrollbar-width: none;
    }
    .display-table-body::-webkit-scrollbar {
      display: none;
    }
    .display-table-body td {
      padding: 12px 14px;
      vertical-align: middle;
      border-bottom: 1px solid #e2e8f0;
      font-size: 1.1rem;
      color: #0f172a;
    }
    .display-table-body tr:nth-child(even) {
      background: #f8fafc;
    }

    /* ENLARGED PRODUCT IMAGE & DETAILS */
    .display-pro-img {
      width: 62px;
      height: 62px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #cbd5e1;
      background: #ffffff;
      padding: 2px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.08);
    }

    .badge-qty {
      background: #0263ff;
      color: #ffffff;
      font-size: 1.05rem;
      padding: 4px 14px;
      border-radius: 8px;
      font-weight: 800;
      box-shadow: 0 3px 8px rgba(2, 99, 255, 0.3);
    }

    /* PAYMENT SUMMARY & BREAKDOWN CARD */
    .summary-card {
      background: #ffffff;
      border-radius: 8px;
      border: 2px solid #cbd5e1;
      padding: 14px 20px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    }

    .summary-item-label {
      color: #334155;
      font-weight: 700;
      font-size: 1.02rem;
    }

    .grand-total-banner {
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      border-radius: 8px;
      padding: 10px 20px;
      text-align: center;
      color: #ffffff;
      box-shadow: 0 6px 20px rgba(5, 150, 105, 0.30);
      margin-top: 8px;
    }
  </style>
</head>
<body>

<div class="display-wrapper">
  <!-- 1. TOP HEADER BAR: BLUE THEME & SYSTEM TITLE -->
  <div class="display-header">
    <div class="header-system-title">
      <i class="fas fa-desktop text-warning"></i>
      <span><?php echo htmlspecialchars($company['com_name']); ?> - ໜ້າຈໍສະແດງຜົນລູກຄ້າ</span>
    </div>

    <!-- Customer Badge -->
    <div class="customer-info-badge">
      <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; color: #0263ff; font-size: 1.1rem;">
        <i class="fas fa-user font-weight-bold"></i>
      </div>
      <div>
        <small class="text-white-50 d-block font-weight-bold" style="font-size: 0.72rem; line-height: 1;">ລູກຄ້າ / ສະມາຊິກ:</small>
        <span class="font-weight-bold text-white" id="displayCustomerName" style="font-size: 1.1rem;">ລູກຄ້າທົ່ວໄປ</span>
      </div>
    </div>

    <!-- Realtime Clock -->
    <div class="clock-badge text-right">
      <h4 class="mb-0 font-weight-bold text-warning" id="liveClock" style="font-size: 1.45rem; letter-spacing: 1px;">00:00:00</h4>
      <small class="text-white-50 font-weight-bold" id="liveDate">--/--/----</small>
    </div>
  </div>

  <!-- MAIN DUAL PANEL -->
  <div class="display-main">
    
    <!-- LEFT PANEL: LARGE STORE LOGO & ADDRESS INFO (40%) -->
    <div class="left-store-panel">
      <div class="store-card">
        <!-- Large Store Logo -->
        <div class="large-logo-box">
          <img src="<?php echo htmlspecialchars($company['com_logo']); ?>" alt="Store Logo" class="large-logo-img"
               onerror="this.style.display='none'; document.getElementById('largeFallbackIcon').style.display='block';">
          <i class="fas fa-store large-fallback-icon" id="largeFallbackIcon" style="display:none;"></i>
        </div>

        <!-- Store Name & Info -->
        <div class="store-name-title"><?php echo htmlspecialchars($company['com_name']); ?></div>
        
        <div class="store-info-text">
          <?php if(!empty($company['com_address'])): ?>
            <div class="mb-1"><i class="fas fa-map-marker-alt text-danger mr-2"></i><?php echo htmlspecialchars($company['com_address']); ?></div>
          <?php endif; ?>
          <?php if(!empty($company['com_tel'])): ?>
            <div><i class="fas fa-phone-alt text-primary mr-2"></i>ເບີໂທ: <strong><?php echo htmlspecialchars($company['com_tel']); ?></strong></div>
          <?php endif; ?>
        </div>

        <div class="welcome-banner-box">
          <i class="fas fa-heart text-danger fa-2x mb-2 animated pulse infinite"></i>
          <h4 class="font-weight-bold text-dark mb-1" style="font-size: 1.35rem;">ຂໍຂອບໃຈທີ່ມາອຸດໜູນ</h4>
          <p class="text-secondary mb-0" style="font-size: 1.02rem; font-weight: 600;">ຍິນດີຕ້ອນຮັບ! ຂໍໃຫ້ທ່ານມີຄວາມສຸກໃນການຊື້ສິນຄ້າ</p>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL: CUSTOMER ORDER LIST & COMPLETE PAYMENT DETAILS (60%) -->
    <div class="right-order-panel">
      <!-- Order Items Table -->
      <div class="cart-card">
        <table class="w-100 border-0 mb-0">
          <thead class="display-table-head">
            <tr>
              <th class="text-center" style="width: 50px;">ລຳດັບ</th>
              <th class="text-center" style="width: 75px;">ຮູບສິນຄ້າ</th>
              <th>ຊື່ສິນຄ້າ (Product Name)</th>
              <th class="text-center" style="width: 100px;">ຫົວໜ່ວຍ</th>
              <th class="text-center" style="width: 100px;">ຈຳນວນ</th>
              <th class="text-right" style="width: 130px;">ລາຄາ</th>
              <th class="text-right" style="width: 145px;">ລວມ (₭)</th>
            </tr>
          </thead>
        </table>
        <div class="display-table-body">
          <table class="w-100 border-0 mb-0">
            <tbody id="displayCartBody">
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="fas fa-shopping-basket fa-3x mb-3 text-secondary opacity-40"></i>
                  <h4 class="font-weight-bold text-secondary">ຍັງບໍ່ມີລາຍການສິນຄ້າ</h4>
                  <p class="mb-0 text-muted">ກະລຸນາລໍຖ້າພະນັກງານສະແກນ ຫຼື ເລືອກສິນຄ້າ...</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FULL PAYMENT BREAKDOWN SUMMARY CARD -->
      <div class="summary-card">
        <div class="row align-items-center">
          <div class="col-6 pr-3 border-right" style="border-color: #cbd5e1 !important;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="summary-item-label"><i class="fas fa-boxes mr-1 text-primary"></i> ລາຍການສິນຄ້າ:</span>
              <span class="font-weight-bold text-dark" id="displayItemCount" style="font-size: 1.1rem;">0 ລາຍການ (0 ຈຳນວນ)</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="summary-item-label"><i class="fas fa-calculator mr-1 text-primary"></i> ຍອດລວມ:</span>
              <span class="font-weight-bold text-dark" id="displaySubtotal" style="font-size: 1.18rem;">0 ₭</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="summary-item-label"><i class="fas fa-percent mr-1 text-danger"></i> ສ່ວນຫຼຸດ:</span>
              <span class="font-weight-bold text-danger" id="displayDiscount" style="font-size: 1.18rem;">0 ₭</span>
            </div>
          </div>

          <div class="col-6 pl-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="summary-item-label"><i class="fas fa-money-bill-wave mr-1 text-success"></i> ຮັບເງິນສົດ:</span>
              <span class="font-weight-bold text-success" id="displayCashReceived" style="font-size: 1.18rem;">0 ₭</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="summary-item-label"><i class="fas fa-qrcode mr-1 text-info"></i> ຮັບເງິນໂອນ:</span>
              <span class="font-weight-bold text-info" id="displayQrReceived" style="font-size: 1.18rem;">0 ₭</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="summary-item-label"><i class="fas fa-hand-holding-usd mr-1 text-warning"></i> ເງິນທອນ:</span>
              <span class="font-weight-bold text-warning" id="displayChangeAmount" style="font-size: 1.3rem; font-weight: 800;">0 ₭</span>
            </div>
          </div>
        </div>

        <!-- Grand Total Net Amount Banner -->
        <div class="grand-total-banner">
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-uppercase font-weight-bold" style="color: #dcfce7; letter-spacing: 0.5px; font-size: 1.05rem;">ຍອດຊຳລະ (ຍອດລວມ - ສ່ວນຫຼຸດ):</span>
            <h2 class="font-weight-bold mb-0 text-white" id="displayGrandTotal" style="font-size: 2.3rem; font-family: 'Noto Sans Lao Looped', sans-serif; text-shadow: 0 3px 6px rgba(0,0,0,0.2);">0 ₭</h2>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<script src="<?php echo $base_path; ?>plugins/jquery/jquery.min.js"></script>
<script>
// Live Clock
function updateClock() {
  var now = new Date();
  $('#liveClock').text(now.toLocaleTimeString('lo-LA'));
  $('#liveDate').text(now.toLocaleDateString('lo-LA'));
}
setInterval(updateClock, 1000);
updateClock();

// Broadcast Channel & Storage Sync
var channel = null;
try {
  channel = new BroadcastChannel('pos_customer_display_channel');
  channel.onmessage = function(e) {
    if (e.data) renderDisplayData(e.data);
  };
} catch(e) {}

window.addEventListener('storage', function(e) {
  if (e.key === 'pos_customer_display_data') {
    try {
      renderDisplayData(JSON.parse(e.newValue));
    } catch(err) {}
  }
});

function loadInitialData() {
  var raw = localStorage.getItem('pos_customer_display_data');
  if (raw) {
    try {
      renderDisplayData(JSON.parse(raw));
    } catch(e) {}
  }
}
loadInitialData();

function renderDisplayData(data) {
  if (!data) return;

  // 1. Customer Name
  var cusName = (data.customer && data.customer.customer_name) ? data.customer.customer_name : 'ລູກຄ້າທົ່ວໄປ';
  if (data.customer && data.customer.member_card) {
    cusName += ' (💳 ' + data.customer.member_card + ')';
  }
  $('#displayCustomerName').text(cusName);

  // 2. Cart Table (Enlarged Image, Name, Unit, Qty, Price, Total)
  var tbody = $('#displayCartBody');
  tbody.empty();

  var cart = data.cart || [];
  if (cart.length === 0) {
    tbody.append(`
      <tr>
        <td colspan="7" class="text-center py-5 text-muted">
          <i class="fas fa-shopping-basket fa-3x mb-3 text-secondary opacity-40"></i>
          <h4 class="font-weight-bold text-secondary">ຍັງບໍ່ມີລາຍການສິນຄ້າ</h4>
          <p class="mb-0 text-muted">ກະລຸນາລໍຖ້າພະນັກງານສະແກນ ຫຼື ເລືອກສິນຄ້າ...</p>
        </td>
      </tr>
    `);
  } else {
    cart.forEach(function(item, idx) {
      var isGift = !!item.is_free_gift;
      var itemTotal = isGift ? '0 ₭' : (item.quantity * item.unit_price).toLocaleString() + ' ₭';
      var unitPriceStr = isGift ? '0 ₭' : parseFloat(item.unit_price).toLocaleString() + ' ₭';
      var unitNameStr = item.unit_name ? item.unit_name : 'ອັນ';
      var giftBadgeHtml = isGift ? `<span class="badge badge-success font-weight-bold ml-1" style="font-size:0.75rem; padding:2px 6px; border-radius:4px;"><i class="fas fa-gift mr-1"></i>ແຖມຟຣີ</span>` : '';
      
      var rawImg = item.image || item.img_url || '';
      var imgName = rawImg ? rawImg.split('/').pop().split('\\').pop() : 'image.jpg';
      var imgSrc = (!imgName || imgName === 'image.jpg') ? '../../assets/img/image.jpg' : '../../assets/product_img/' + imgName;

      tbody.append(`
        <tr style="${isGift ? 'background:#f0fdf4;' : ''}">
          <td class="text-center text-muted font-weight-bold">${idx + 1}</td>
          <td class="text-center">
            <img src="${imgSrc}" alt="${item.product_name}" class="display-pro-img" onerror="this.src='../../assets/img/image.jpg';">
          </td>
          <td class="font-weight-bold text-dark" style="font-size: 1.1rem;">
            ${item.product_name} ${giftBadgeHtml}
            ${item.barcode ? `<small class="text-muted d-block font-weight-normal" style="font-size:0.80rem;">${item.barcode}</small>` : ''}
          </td>
          <td class="text-center font-weight-bold text-primary">${unitNameStr}</td>
          <td class="text-center"><span class="badge-qty">x${item.quantity}</span></td>
          <td class="text-right ${isGift ? 'text-success' : 'text-primary'} font-weight-bold">${unitPriceStr}</td>
          <td class="text-right text-success font-weight-bold" style="font-size: 1.15rem;">${itemTotal}</td>
        </tr>
      `);
    });
  }

  // 3. Summary Totals Calculation
  var lineCount = cart.length;
  var totalQty = 0;
  var subtotal = 0;
  cart.forEach(function(i) {
    totalQty += i.quantity;
    subtotal += i.quantity * i.unit_price;
  });

  var discountVal = parseFloat((data.discount || '0').toString().replace(/[^\d]/g, '')) || 0;
  var netGrandTotal = Math.max(0, subtotal - discountVal);

  // Animated number counter helper
  function animateNumber(elementId, targetValue, suffix) {
    var $el = $('#' + elementId);
    var currentText = $el.text().replace(/[^\d]/g, '');
    var startValue = parseFloat(currentText) || 0;
    if (startValue === targetValue) {
      $el.text(targetValue.toLocaleString() + (suffix || ''));
      return;
    }
    var duration = 400;
    var startTime = null;

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var easeProgress = 1 - Math.pow(1 - progress, 3); // easeOutCubic
      var val = Math.floor(startValue + (targetValue - startValue) * easeProgress);
      $el.text(val.toLocaleString() + (suffix || ''));
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        $el.text(targetValue.toLocaleString() + (suffix || ''));
      }
    }
    window.requestAnimationFrame(step);
  }

  $('#displayItemCount').text(lineCount + ' ລາຍການ (' + totalQty + ' ຈຳນວນ)');
  animateNumber('displaySubtotal', subtotal, ' ₭');
  animateNumber('displayDiscount', discountVal, ' ₭');
  animateNumber('displayGrandTotal', netGrandTotal, ' ₭');

  // 4. Cash, QR, and Change Breakdown
  var cashRec = parseFloat(data.cashReceived || 0);
  var qrRec = parseFloat(data.qrReceived || 0);
  var changeAmt = parseFloat(data.changeAmount || 0);

  animateNumber('displayCashReceived', cashRec, ' ₭');
  animateNumber('displayQrReceived', qrRec, ' ₭');
  animateNumber('displayChangeAmount', changeAmt, ' ₭');
}
</script>

</body>
</html>
