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
  
  <!-- Store Favicon Icon (Replaces XAMPP icon in browser tab title) -->
  <link rel="shortcut icon" href="<?php echo htmlspecialchars($company['com_logo']); ?>" type="image/x-icon">
  <link rel="icon" href="<?php echo htmlspecialchars($company['com_logo']); ?>" type="image/png">
  
  <!-- Font Awesome & System Styles -->
  <link rel="stylesheet" href="<?php echo $base_path; ?>plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/local-font.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/main.min.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/global-custom.css">
  
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      background: #f1f5f9;
      color: #1e293b;
      font-family: 'Noto Sans Lao Looped', 'Noto Sans Lao', sans-serif;
      overflow: hidden;
      user-select: none;
    }
    .display-wrapper {
      display: flex;
      flex-direction: column;
      height: 100vh;
    }
    
    /* ===== TOP HEADER BAR (MATCHES POS PRIMARY BLUE THEME) ===== */
    .display-header {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      border-bottom: 3px solid #1e40af;
      padding: 10px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 15px rgba(37,99,235,0.3);
      z-index: 100;
    }
    .brand-logo-wrap {
      background: #ffffff;
      padding: 5px 12px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #93c5fd;
      box-shadow: 0 3px 8px rgba(0,0,0,0.12);
      max-height: 52px;
    }
    .brand-logo-img {
      height: 38px;
      max-width: 140px;
      object-fit: contain;
    }
    .fallback-logo-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
    }

    /* Customer Badge */
    .customer-info-badge {
      background: rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(8px);
      padding: 6px 18px;
      border-radius: 30px;
      border: 1.5px solid rgba(255, 255, 255, 0.4);
      display: flex;
      align-items: center;
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* ===== MAIN DUAL LAYOUT ===== */
    .display-main {
      flex: 1;
      display: flex;
      overflow: hidden;
      padding: 14px;
      gap: 14px;
    }

    /* ===== LEFT PANEL: MEDIA & PROMO MOTION (50%) ===== */
    .left-media-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .media-card {
      flex: 1;
      background: #ffffff;
      border-radius: 18px;
      border: 2px solid #cbd5e1;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    }
    
    /* Animated Gradient Background */
    .animated-bg {
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      background: linear-gradient(-45deg, #eff6ff, #dbeafe, #e0e7ff, #eff6ff);
      background-size: 400% 400%;
      animation: gradientBG 14s ease infinite;
      z-index: 1;
    }
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Promo Video Player */
    .promo-video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      position: absolute;
      top: 0; left: 0;
      z-index: 2;
    }

    /* Motion Graphic Content */
    .media-content {
      position: relative;
      z-index: 3;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 24px;
    }

    .glow-circle {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(37,99,235,0.85) 0%, rgba(37,99,235,0) 70%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 22px;
      animation: pulseGlow 2.5s infinite alternate;
    }
    @keyframes pulseGlow {
      0% { transform: scale(0.96); box-shadow: 0 0 25px rgba(37,99,235,0.3); }
      100% { transform: scale(1.10); box-shadow: 0 0 45px rgba(37,99,235,0.6); }
    }

    .promo-box {
      background: #ffffff;
      border: 2px solid #2563eb;
      border-radius: 16px;
      padding: 20px 28px;
      max-width: 88%;
      box-shadow: 0 8px 20px rgba(37,99,235,0.15);
    }

    /* ===== RIGHT PANEL: CUSTOMER ORDER (50%) ===== */
    .right-order-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 12px;
      overflow: hidden;
    }
    
    .cart-card {
      flex: 1;
      background: #ffffff;
      border-radius: 18px;
      border: 2px solid #cbd5e1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    }

    .display-table-head {
      background: #2563eb;
      color: #ffffff;
      font-weight: 700;
      font-size: 0.92rem;
      border-bottom: 2px solid #1d4ed8;
    }
    .display-table-head th {
      padding: 13px 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .display-table-body {
      overflow-y: auto;
      flex: 1;
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
    .display-table-body::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }
    .display-table-body td {
      padding: 13px 14px;
      vertical-align: middle;
      border-bottom: 1px solid #e2e8f0;
      font-size: 1.02rem;
      color: #1e293b;
    }
    .display-table-body tr:nth-child(even) {
      background: #f8fafc;
    }

    .summary-card {
      background: #ffffff;
      border-radius: 16px;
      border: 2px solid #cbd5e1;
      padding: 14px 18px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }

    .grand-total-card {
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      border: 2.5px solid #166534;
      border-radius: 16px;
      padding: 16px;
      text-align: center;
      box-shadow: 0 8px 25px rgba(22, 163, 74, 0.30);
      position: relative;
      overflow: hidden;
    }

    .payment-status-card {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      border: 2px solid #1d4ed8;
      border-radius: 16px;
      padding: 14px;
      text-align: center;
      box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
    }

    .badge-qty {
      background: #2563eb;
      color: #ffffff;
      font-size: 0.95rem;
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 700;
    }
  </style>
</head>
<body>

<div class="display-wrapper">
  <!-- TOP HEADER BAR (POS PRIMARY BLUE THEME) -->
  <div class="display-header">
    <!-- Store Logo & Name -->
    <div class="d-flex align-items-center">
      <div class="brand-logo-wrap mr-3">
        <img src="<?php echo htmlspecialchars($company['com_logo']); ?>" alt="Logo" class="brand-logo-img"
             onerror="this.style.display='none'; document.getElementById('fallbackLogoIcon').style.display='flex';">
        <div id="fallbackLogoIcon" class="fallback-logo-icon" style="display:none;">
          <i class="fas fa-store"></i>
        </div>
      </div>
      <div>
        <h4 class="mb-0 font-weight-bold text-white"><?php echo htmlspecialchars($company['com_name']); ?></h4>
        <small class="text-white-50 font-weight-bold"><i class="fas fa-phone-alt mr-1"></i><?php echo htmlspecialchars($company['com_tel']); ?> <?php if(!empty($company['com_address'])) echo ' | ' . htmlspecialchars($company['com_address']); ?></small>
      </div>
    </div>

    <!-- Customer Badge (Text Only) -->
    <div class="customer-info-badge">
      <div>
        <small class="text-white-50 d-block font-weight-bold" style="font-size: 0.70rem;">ລູກຄ້າ / ສະມາຊິກ:</small>
        <span class="font-weight-bold text-white" id="displayCustomerName" style="font-size: 1.05rem;">ລູກຄ້າທົ່ວໄປ</span>
      </div>
    </div>

    <!-- Realtime Clock -->
    <div class="text-right">
      <h4 class="mb-0 font-weight-bold text-warning" id="liveClock" style="font-size: 1.35rem;">00:00:00</h4>
      <small class="text-white-50 font-weight-bold" id="liveDate">--/--/----</small>
    </div>
  </div>

  <!-- MAIN DUAL PANEL (Left = Video/Motion, Right = Order List) -->
  <div class="display-main">
    
    <!-- LEFT PANEL: MEDIA & MOTION GRAPHICS (50%) -->
    <div class="left-media-panel">
      <div class="media-card">
        <div class="animated-bg"></div>

        <!-- Video Player (Auto-plays promo video if provided) -->
        <video id="promoVideoPlayer" class="promo-video" autoplay loop muted playsinline style="display:none;">
          <source src="<?php echo $base_path; ?>assets/media/promo.mp4" type="video/mp4">
        </video>

        <!-- Motion Graphic & Store Welcome Display -->
        <div class="media-content" id="motionGraphicContent">
          <div class="glow-circle">
            <i class="fas fa-shopping-bag fa-3x text-white"></i>
          </div>
          <h2 class="font-weight-bold text-dark mb-2" style="font-size: 2.2rem;">
            <?php echo htmlspecialchars($company['com_name']); ?>
          </h2>
          <p class="text-primary font-weight-bold mb-4" style="font-size: 1.2rem; letter-spacing: 0.5px;">
            <i class="fas fa-star text-warning mr-1"></i> ຍິນດີຕ້ອນຮັບສູ່ຮ້ານເຮົາ <i class="fas fa-star text-warning ml-1"></i>
          </p>
          
          <div class="promo-box shadow-sm">
            <i class="fas fa-heart text-danger fa-2x mb-2 animated pulse infinite"></i>
            <h4 class="font-weight-bold text-dark mb-1">ຂໍຂອບໃຈທີ່ມາອຸດໜູນ</h4>
            <p class="text-secondary mb-0" style="font-size: 0.98rem;">ຂໍຂອບໃຈທີ່ມາອຸດໜູນຮ້ານເຮົາ! ຂໍໃຫ້ທ່ານມີຄວາມສຸກໃນການຊື້ສິນຄ້າ</p>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL: CUSTOMER ORDER LIST & TOTALS (50%) -->
    <div class="right-order-panel">
      <!-- Order Items Table -->
      <div class="cart-card">
        <table class="w-100 border-0 mb-0">
          <thead class="display-table-head">
            <tr>
              <th class="text-center" style="width: 45px;">ລຳດັບ</th>
              <th class="text-center" style="width: 55px;">ຮູບ</th>
              <th>ລາຍການສິນຄ້າ</th>
              <th class="text-center" style="width: 80px;">ຈຳນວນ</th>
              <th class="text-right" style="width: 115px;">ລາຄາ/ໜ່ວຍ</th>
              <th class="text-right" style="width: 130px;">ລວມ (₭)</th>
            </tr>
          </thead>
        </table>
        <div class="display-table-body">
          <table class="w-100 border-0 mb-0">
            <tbody id="displayCartBody">
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="fas fa-shopping-basket fa-3x mb-3 text-secondary opacity-40"></i>
                  <h4 class="font-weight-bold text-secondary">ຍັງບໍ່ມີລາຍການສິນຄ້າ</h4>
                  <p class="mb-0 text-muted">ກະລຸນາລໍຖ້າພະນັກງານເລືອກສິນຄ້າ...</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Item Count & Subtotal Card -->
      <div class="summary-card">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted font-weight-bold" style="font-size: 0.95rem;">ລາຍການສິນຄ້າ:</span>
          <span class="font-weight-bold text-dark" id="displayItemCount" style="font-size: 1.05rem;">0 ລາຍການ (0 ຈຳນວນ)</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted font-weight-bold" style="font-size: 0.95rem;">ລວມມູນຄ່າ:</span>
          <span class="font-weight-bold text-dark" id="displaySubtotal" style="font-size: 1.1rem;">0 ₭</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted font-weight-bold" style="font-size: 0.95rem;">ສ່ວນຫຼຸດ:</span>
          <span class="font-weight-bold text-danger" id="displayDiscount" style="font-size: 1.1rem;">0 ₭</span>
        </div>
      </div>

      <!-- Grand Total Card -->
      <div class="grand-total-card">
        <div class="text-uppercase font-weight-bold mb-1" style="color: #dcfce7; letter-spacing: 1px; font-size: 1rem;">ຍອດຊຳລະສຸດທິ</div>
        <h1 class="font-weight-bold mb-0 text-white" id="displayGrandTotal" style="font-size: 3.2rem; font-family: 'Noto Sans Lao Looped', sans-serif;">0 ₭</h1>
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

// Video Check
var videoEl = document.getElementById('promoVideoPlayer');
if (videoEl) {
  videoEl.addEventListener('error', function() {
    this.style.display = 'none';
    $('#motionGraphicContent').show();
  });
  videoEl.addEventListener('canplay', function() {
    this.style.display = 'block';
    $('#motionGraphicContent').hide();
  });
}

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

  // 2. Cart Table
  var tbody = $('#displayCartBody');
  tbody.empty();

  var cart = data.cart || [];
  if (cart.length === 0) {
    tbody.append(`
      <tr>
        <td colspan="6" class="text-center py-5 text-muted">
          <i class="fas fa-shopping-basket fa-3x mb-3 text-secondary opacity-40"></i>
          <h4 class="font-weight-bold text-secondary">ຍັງບໍ່ມີລາຍການສິນຄ້າ</h4>
          <p class="mb-0 text-muted">ກະລຸນາລໍຖ້າພະນັກງານເລືອກສິນຄ້າ...</p>
        </td>
      </tr>
    `);
  } else {
    cart.forEach(function(item, idx) {
      var itemTotal = (item.quantity * item.unit_price).toLocaleString();
      var unitStr = item.unit_name ? (' ' + item.unit_name) : '';
      
      var imgSrc = item.image || item.img_url || '';
      if (!imgSrc || imgSrc === '') {
        imgSrc = '../../assets/img/product_img/default.png';
      } else if (!imgSrc.startsWith('http') && !imgSrc.startsWith('../') && !imgSrc.startsWith('/')) {
        imgSrc = '../../assets/img/product_img/' + imgSrc;
      }

      tbody.append(`
        <tr>
          <td class="text-center text-muted font-weight-bold">${idx + 1}</td>
          <td class="text-center" style="width: 55px;">
            <img src="${imgSrc}" alt="${item.product_name}" 
                 style="width: 42px; height: 42px; object-fit: cover; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff;" 
                 onerror="this.onerror=null; this.src='../../assets/img/product_img/default.png';">
          </td>
          <td class="font-weight-bold text-dark">
            ${item.product_name}
            ${item.barcode ? `<small class="text-muted d-block font-weight-normal">${item.barcode}</small>` : ''}
          </td>
          <td class="text-center"><span class="badge-qty">x${item.quantity}${unitStr}</span></td>
          <td class="text-right text-primary font-weight-bold">${parseFloat(item.unit_price).toLocaleString()} ₭</td>
          <td class="text-right text-success font-weight-bold" style="font-size: 1.05rem;">${itemTotal} ₭</td>
        </tr>
      `);
    });
  }

  // 3. Summary Totals
  var lineCount = cart.length;
  var totalQty = 0;
  var subtotal = 0;
  cart.forEach(function(i) {
    totalQty += i.quantity;
    subtotal += i.quantity * i.unit_price;
  });

  var discountVal = parseFloat((data.discount || '0').toString().replace(/[^\d]/g, '')) || 0;
  var grandTotal = Math.max(0, subtotal - discountVal);

  $('#displayItemCount').text(lineCount + ' ລາຍການ (' + totalQty + ' ຈຳນວນ)');
  $('#displaySubtotal').text(subtotal.toLocaleString() + ' ₭');
  $('#displayDiscount').text(discountVal.toLocaleString() + ' ₭');
  $('#displayGrandTotal').text(grandTotal.toLocaleString() + ' ₭');

  // 4. Payment Status Card
  var statusCard = $('#displayPaymentCard');
  var statusContent = $('#paymentStatusContent');

  if (data.checkoutState === 'checkout_modal_open') {
    statusCard.css({ 'background': 'linear-gradient(135deg, #2563eb, #1d4ed8)', 'border-color': '#1d4ed8' });
    statusContent.html(`
      <i class="fas fa-credit-card fa-lg mb-1 text-warning"></i>
      <h5 class="font-weight-bold text-warning mb-1">ກຳລັງຊຳລະເງິນ...</h5>
      <div style="font-size: 1.1rem;" class="text-white font-weight-bold">ວິທີຊຳລະ: ${data.paymentType || 'ເງິນສົດ'}</div>
    `);
  } else if (data.checkoutState === 'payment_success') {
    statusCard.css({ 'background': 'linear-gradient(135deg, #16a34a, #15803d)', 'border-color': '#166534' });
    var changeVal = parseFloat(data.changeAmount || 0).toLocaleString();
    var cashVal = parseFloat(data.cashReceived || 0).toLocaleString();
    statusContent.html(`
      <i class="fas fa-check-circle fa-lg mb-1 text-white"></i>
      <h4 class="font-weight-bold text-white mb-1">ຊຳລະເງິນສຳເລັດ!</h4>
      <div class="font-weight-bold text-white mb-1" style="font-size: 1rem;">ຮັບເງິນ: ${cashVal} ₭</div>
      <div class="font-weight-bold text-warning" style="font-size: 1.3rem;">💵 ເງິນທອນ: ${changeVal} ₭</div>
    `);
  } else {
    statusCard.css({ 'background': 'linear-gradient(135deg, #2563eb, #1d4ed8)', 'border-color': '#1d4ed8' });
    statusContent.html(`
      <i class="fas fa-shopping-cart fa-lg mb-1 text-white opacity-80"></i>
      <h5 class="font-weight-bold text-white mb-0">ກຳລັງເລືອກສິນຄ້າ...</h5>
      <small class="text-white-50">ຍິນດີຕ້ອນຮັບ</small>
    `);
  }
}
</script>

</body>
</html>
