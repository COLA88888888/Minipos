<?php
// ============================================================
// dashboard.php - UNIFIED MASTER LAYOUT (ລະບົບດາດສ໌ບອດຫຼັກ)
// ============================================================
session_start();

// 1. ກວດສອບຄວາມປອດໄພ ແລະ ການເຂົ້າສູ່ລະບົບ
if (!isset($_SESSION['checked']) || $_SESSION['checked'] !== 1 || !isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?expired=1');
    exit();
}

require_once __DIR__ . '/../config/db.php';

$base_path = '../';
$site_name = 'POS System - Corner Retail';
$site_logo = '../assets/img/logo/logo.png';

$display_name = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
if ($display_name === '') {
    $display_name = $_SESSION['username'] ?? 'admin';
}

$profile_img = $_SESSION['profile_img'] ?? 'default.png';
if (empty($profile_img) || !file_exists(__DIR__ . '/../assets/img/users/' . $profile_img)) {
    $profile_img = 'default.png';
}
$profile_img_path = '../assets/img/users/' . $profile_img;

// ດຶງໜ້າເລີ່ມຕົ້ນຕາມສິດ (Default Page Route based on permissions)
$default_iframe_src = 'home.php';
if (!hasPermission('dashboard')) {
    if (hasPermission('sale')) {
        $default_iframe_src = '../pages/pos/pos.php';
    } elseif (hasPermission('customers')) {
        $default_iframe_src = '../pages/customers/customers.php';
    } elseif (hasPermission('categories')) {
        $default_iframe_src = '../pages/categories/categories.php';
    } elseif (hasPermission('products') || hasPermission('stock')) {
        $default_iframe_src = '../pages/products/products.php';
    } elseif (hasPermission('import_stock')) {
        $default_iframe_src = '../pages/import_stock/import_stock.php';
    } elseif (hasPermission('import_list')) {
        $default_iframe_src = '../pages/import_stock/import_list.php';
    } elseif (hasPermission('stock_transfer')) {
        $default_iframe_src = '../pages/import_stock/stock_transfer.php';
    } elseif (hasPermission('transfer_history')) {
        $default_iframe_src = '../pages/import_stock/transfer_history.php';
    } elseif (hasPermission('accounting')) {
        $default_iframe_src = '../pages/bank/bank.php';
    } elseif (hasPermission('report') || hasPermission('daily_report') || hasPermission('all_sales') || hasPermission('best_seller') || hasPermission('profit_cost') || hasPermission('financial') || hasPermission('category_sales') || hasPermission('delete_bills')) {
        $default_iframe_src = '../pages/reports/daily_report.php';
    } elseif (hasPermission('users')) {
        $default_iframe_src = '../pages/users_manage/users_manage.php';
    } elseif (hasPermission('permissions')) {
        $default_iframe_src = '../pages/permissions/permissions.php';
    } elseif (hasPermission('setup') || hasPermission('branches')) {
        $default_iframe_src = '../pages/branches/branches.php';
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($site_name); ?></title>
  <link rel="shortcut icon" href="<?php echo $site_logo; ?>" type="image/x-icon">
  
  <script>
    (function() {
      // ກວດສອບວ່າ Tab ນີ້ໄດ້ຜ່ານການ Login ໂດຍກົງຫຼືບໍ່ (ຖ້າກັອບປີ້ Link ມາວາງຢູ່ Tab ໃໝ່ -> ເດັ້ງໄປ Login ທັນທີ)
      try {
        var activeTabToken = sessionStorage.getItem('pos_tab_active');
        if (!activeTabToken || activeTabToken.indexOf('TOKEN_') !== 0) {
          window.location.href = '../auth/logout.php?expired=1';
          return;
        }
        var savedSrc = sessionStorage.getItem('currentIframeSrc') || '';
        var isPos = savedSrc.indexOf('pos.php') !== -1 || savedSrc.indexOf('pos/') !== -1;
        if (isPos) {
          document.documentElement.classList.add('sidebar-collapse');
        }
      } catch(e) {
        window.location.href = '../auth/logout.php?expired=1';
      }
    })();
  </script>

  <!-- Local Fonts & Styles -->
  <link rel="stylesheet" href="../assets/css/local-font.css">
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="../sweetalert/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="../assets/css/pages/menu-sidebar.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../assets/css/global-custom.css?v=<?php echo time(); ?>">

  <style>
    /* Critical anti-flash scrollbar rules (Prevent native large scrollbar on refresh) */
    html, body, .wrapper, .main-sidebar, .sidebar, .os-viewport, .os-content {
      scrollbar-width: none !important;
      -ms-overflow-style: none !important;
    }
    html::-webkit-scrollbar, body::-webkit-scrollbar, .main-sidebar::-webkit-scrollbar, .sidebar::-webkit-scrollbar, .os-viewport::-webkit-scrollbar, .os-content::-webkit-scrollbar {
      display: none !important;
      width: 0 !important;
      height: 0 !important;
    }
  </style>


  <!-- Scripts -->
  <script src="../sweetalert/dist/sweetalert2.all.min.js"></script>
  <script src="../plugins/jquery/jquery.min.js"></script>
  <script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
</head>
<body class="hold-transition sidebar-mini sidebar-no-expand layout-fixed">
<script>
  if (document.documentElement.classList.contains('sidebar-collapse')) {
    document.body.classList.add('sidebar-collapse');
  }
</script>

<div class="wrapper">

  <!-- ແຖບເມນູດ້ານເທິງ (Top Navigation Bar) -->
  <nav class="main-header navbar navbar-expand navbar-dark justify-content-between" style="background-color: rgb(2, 99, 255) !important; border: none !important; border-bottom: none !important; box-shadow: none !important; height: 64px;">
    <!-- Left side: Menu toggle & Branch Switcher -->
    <ul class="navbar-nav align-items-center">
      <li class="nav-item">
        <a class="nav-link" id="topNavbarPushMenuBtn" href="#" role="button" style="color: #ffffff; font-size: 1.2rem; cursor: pointer;" title="ເມນູ">
          <i class="fas fa-bars"></i>
        </a>
      </li>
      <?php
        $userStoreId = intval($_SESSION['store_id'] ?? 1);
        $userIsAdmin = ($_SESSION['status'] ?? '') === 'ຜູ້ບໍລິຫານ' || strtolower($_SESSION['status'] ?? '') === 'admin' || ($_SESSION['user_id'] ?? 0) == 1;
        $userIsMain = isMainBranch($pdo, $userStoreId);
        $allStores = $pdo->query("SELECT * FROM tbstore WHERE status = 'active' ORDER BY is_main DESC, store_id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $activeStoreId = getActiveStoreId($pdo);
        $currentStoreName = '';
        foreach ($allStores as $s) {
            if ($s['store_id'] == $activeStoreId) {
                $currentStoreName = $s['store_name'];
                break;
            }
        }
      ?>
      <!-- Branch Switcher Removed -->
    </ul>

    <?php
      // ດຶງຂໍ້ມູນແພັກເກັດການນຳໃຊ້ (Subscription License Info)
      $license_start = '2026-01-01';
      $license_expire = '2026-12-31';
      try {
        $cLic = $pdo->query("SELECT license_start_date, license_expire_date FROM tbcompanyinfo LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($cLic) {
          if (!empty($cLic['license_start_date'])) $license_start = $cLic['license_start_date'];
          if (!empty($cLic['license_expire_date'])) $license_expire = $cLic['license_expire_date'];
        }
      } catch (Exception $ex) {}

      $todayObj = new DateTime(date('Y-m-d'));
      $expireObj = new DateTime($license_expire);
      $daysRemaining = 0;
      if ($expireObj >= $todayObj) {
        $diff = $todayObj->diff($expireObj);
        $daysRemaining = (int)$diff->format('%a');
      }

      $formattedStart = date('d/m/Y', strtotime($license_start));
      $formattedExpire = date('d/m/Y', strtotime($license_expire));

      // Low Stock Alerts for Main Branch (Grouped by store/branch)
      $lowStockList = getLowStockAlerts($pdo);
      $lowStockCount = count($lowStockList);

      $groupedLowStock = [];
      foreach ($lowStockList as $alert) {
          $stId = $alert['store_id'];
          if (!isset($groupedLowStock[$stId])) {
              $groupedLowStock[$stId] = [
                  'store_id'   => $alert['store_id'],
                  'store_name' => $alert['store_name'],
                  'is_main'    => $alert['is_main'] ?? 0,
                  'items'      => []
              ];
          }
          $groupedLowStock[$stId]['items'][] = $alert;
      }
    ?>

    <!-- Right side: Subscription Days Remaining + Notifications Bell + Logout -->
    <ul class="navbar-nav ml-auto align-items-center" style="gap: 12px; font-family: 'Noto Sans Lao Looped', sans-serif;">
      
      <!-- Notifications Dropdown (Low Stock Warning Grouped by Branch - Only for Main Branch / Admin) -->
      <?php if ($userIsMain || $userIsAdmin): ?>
        <li class="nav-item dropdown">
          <a class="nav-link text-white position-relative px-2 d-flex align-items-center" data-toggle="dropdown" href="#" title="ແຈ້ງເຕືອນສິນຄ້າໃກ້ສິນສຸດ/ສິນຄ້າໝົດ" style="font-size: 1.25rem; cursor: pointer;">
            <i class="fas fa-bell <?php echo ($lowStockCount > 0) ? 'text-warning fa-bounce' : ''; ?>"></i>
            <?php if ($lowStockCount > 0): ?>
              <span class="badge badge-danger font-weight-bold position-absolute" style="top: 2px; right: -2px; font-size: 0.68rem; border-radius: 10px; padding: 2px 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                <?php echo $lowStockCount; ?>
              </span>
            <?php endif; ?>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow-lg border-0" style="border-radius: 12px; min-width: 340px; padding: 0; overflow: hidden;">
            <div class="dropdown-header font-weight-bold d-flex bg-primary text-white justify-content-between align-items-center py-2.5 px-3">
              <span><i class="fas fa-exclamation-triangle text-warning mr-1.5"></i> ແຈ້ງເຕືອນສິນຄ້າໃກ້ສິນສຸດ (<?php echo $lowStockCount; ?>)</span>
            </div>
            <div class="dropdown-divider m-0"></div>
            
            <div style="max-height: 320px; overflow-y: auto;">
              <?php if ($lowStockCount > 0 && !empty($groupedLowStock)): ?>
                <?php foreach ($groupedLowStock as $stId => $branchGroup): ?>
                  <!-- ຫົວຂໍ້ແຍກຕາມສາຂາ (Branch Section Header) -->
                  <div class="px-3 py-1.5 font-weight-bold text-dark border-bottom d-flex align-items-center justify-content-between" style="background-color: #f1f5f9; font-size: 0.82rem;">
                    <span>
                      <i class="fas fa-store text-primary mr-1"></i> <?php echo htmlspecialchars($branchGroup['store_name']); ?>
                      <?php if (!empty($branchGroup['is_main'])): ?>
                        <small class="text-muted">(ສາງຫຼັກ)</small>
                      <?php endif; ?>
                    </span>
                    <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 0.7rem; border-radius: 6px;">
                      <?php echo count($branchGroup['items']); ?> ລາຍການ
                    </span>
                  </div>

                  <!-- ລາຍການສິນຄ້າໃນສາຂານັ້ນ (Items in Branch Section) -->
                  <?php foreach ($branchGroup['items'] as $alert): ?>
                    <a href="#" onclick="openStockTransferModal(<?php echo $alert['store_id']; ?>, '<?php echo addslashes($alert['product_name']); ?>', <?php echo intval($alert['product_id']); ?>)" class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between border-bottom text-wrap" style="transition: background 0.15s; background-color: #ffffff;">
                      <div style="max-width: 60%;">
                        <strong class="d-block text-dark text-truncate" style="font-size: 0.84rem;" title="<?php echo htmlspecialchars($alert['product_name']); ?>"><?php echo htmlspecialchars($alert['product_name']); ?></strong>
                      </div>
                      <div class="text-right">
                        <span class="badge badge-danger font-weight-bold px-2 py-1" style="font-size: 0.75rem;">ເຫຼືອ <?php echo $alert['qty']; ?> <?php echo htmlspecialchars($alert['unit'] ?: 'ອັນ'); ?></span>
                        <span class="d-block text-primary font-weight-bold mt-1" style="font-size: 0.75rem;"><i class="fas fa-shipping-fast mr-1"></i>ໂອນໃຫ້</span>
                      </div>
                    </a>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="text-center py-4 text-muted">
                  <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                  <span style="font-size: 0.85rem;">ບໍ່ມີສິນຄ້າໃກ້ສິນສຸດໃນສາຂາ</span>
                </div>
              <?php endif; ?>
            </div>

            <?php if ($lowStockCount > 0): ?>
              <div class="dropdown-footer p-2 text-center bg-light">
                <a href="<?php echo $base_path; ?>pages/import_stock/stock_transfer.php" target="frame" class="btn btn-sm btn-primary font-weight-bold btn-block" style="border-radius: 8px;">
                  <i class="fas fa-exchange-alt mr-1"></i> ໜ້າຈັດການໂອນສິນຄ້າທັງໝົດ
                </a>
              </div>
            <?php endif; ?>
          </div>
        </li>
      <?php endif; ?>

      <!-- Subscription info (Only for Main Branch / Admin) -->
      <?php if ($userIsMain || $userIsAdmin): ?>
        <li class="nav-item d-none d-md-flex align-items-center text-white mr-1" style="font-size: 0.85rem; font-weight: 600;">
          <span class="d-inline-flex align-items-center">
            ເເພັກເກັດ:<span class="mx-1 text-white" style="font-size: 0.95rem; font-weight: 800"><?php echo $daysRemaining; ?></span> ວັນ
          </span>
        </li>
      <?php endif; ?>

      <!-- Logout button -->
      <li class="nav-item">
        <a class="nav-link logout-nav-btn font-weight-bold" href="javascript:void(0);" onclick="confirmLogout()" title="ອອກຈາກລະບົບ">
          <i class="fas fa-power-off"></i>
          <span class="d-none d-md-inline">ອອກຈາກລະບົບ</span>
        </a>
      </li>
    </ul>
  </nav>

  <script>
    function switchDashboardBranch(storeId) {
      $.ajax({
        url: '../api/branches_backend.php',
        type: 'POST',
        data: { action: 'switch_branch', store_id: storeId },
        success: function(res) {
          window.location.reload();
        }
      });
    }

    function openStockTransferModal(targetStoreId, productName, productId) {
      var iframe = document.getElementById('mainContentFrame');
      var transferUrl = '../pages/import_stock/stock_transfer.php?target_store=' + targetStoreId + '&search=' + encodeURIComponent(productName || '') + (productId ? '&product_id=' + productId : '') + '&auto_add=1';
      if (iframe) {
        iframe.src = transferUrl;
        sessionStorage.setItem('currentIframeSrc', transferUrl);
      } else {
        window.location.href = transferUrl;
      }
    }
  </script>

  <script>
    // Real-Time Clock Function
    function updateLiveClock() {
      var now = new Date();
      var d = String(now.getDate()).padStart(2, '0');
      var m = String(now.getMonth() + 1).padStart(2, '0');
      var y = now.getFullYear();
      var hh = String(now.getHours()).padStart(2, '0');
      var mm = String(now.getMinutes()).padStart(2, '0');
      var ss = String(now.getSeconds()).padStart(2, '0');
      var el = document.getElementById('live_datetime_clock');
      if (el) {
        el.innerText = d + '/' + m + '/' + y + ' ' + hh + ':' + mm + ':' + ss;
      }
    }
    setInterval(updateLiveClock, 1000);
  </script>
  <!-- /.navbar -->

  <!-- ແຖບເມນູທາງຊ້າຍ (Unified Dynamic Sidebar) -->
  <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
  
  <script>
    (function() {
      var defaultSrc = '<?php echo $default_iframe_src; ?>';
      var hasDashboardPerm = <?php echo hasPermission('dashboard') ? 'true' : 'false'; ?>;
      var savedSrc = sessionStorage.getItem('currentIframeSrc');
      
      // If user does not have dashboard permission and savedSrc is home.php, clear it
      if (!hasDashboardPerm && (!savedSrc || savedSrc === 'home.php')) {
        savedSrc = defaultSrc;
        sessionStorage.setItem('currentIframeSrc', defaultSrc);
      }
      
      var targetSrc = savedSrc || defaultSrc;
      if (targetSrc) {
        var links = document.querySelectorAll('.nav-sidebar a.nav-link');
        for (var i = 0; i < links.length; i++) {
          links[i].classList.remove('active');
        }
        for (var i = 0; i < links.length; i++) {
          var href = links[i].getAttribute('href');
          if (href && href !== '#' && (href === targetSrc || targetSrc.endsWith(href))) {
            links[i].classList.add('active');
            var treeview = links[i].closest('.nav-treeview');
            if (treeview) {
              var parentLiGroup = treeview.closest('.nav-item');
              if (parentLiGroup) {
                parentLiGroup.classList.add('menu-open');
              }
            }
            break;
          }
        }
      }
    })();
  </script>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="background-color: #f4f6f9; position: relative;">
    <!-- Page Loading Spinner Overlay (12-Dot Pulse Circle Spinner from Screenshot) -->
    <style>
      .loader-dots-spinner {
        color: #0263ff;
        display: inline-block;
        position: relative;
        width: 56px;
        height: 56px;
      }
      .loader-dots-spinner div {
        transform-origin: 28px 28px;
        animation: loader-dots-spinner-anim 1.2s linear infinite;
      }
      .loader-dots-spinner div:after {
        content: " ";
        display: block;
        position: absolute;
        top: 4px;
        left: 25px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #0263ff;
      }
      .loader-dots-spinner div:nth-child(1) { transform: rotate(0deg); animation-delay: -1.1s; }
      .loader-dots-spinner div:nth-child(2) { transform: rotate(30deg); animation-delay: -1s; }
      .loader-dots-spinner div:nth-child(3) { transform: rotate(60deg); animation-delay: -0.9s; }
      .loader-dots-spinner div:nth-child(4) { transform: rotate(90deg); animation-delay: -0.8s; }
      .loader-dots-spinner div:nth-child(5) { transform: rotate(120deg); animation-delay: -0.7s; }
      .loader-dots-spinner div:nth-child(6) { transform: rotate(150deg); animation-delay: -0.6s; }
      .loader-dots-spinner div:nth-child(7) { transform: rotate(180deg); animation-delay: -0.5s; }
      .loader-dots-spinner div:nth-child(8) { transform: rotate(210deg); animation-delay: -0.4s; }
      .loader-dots-spinner div:nth-child(9) { transform: rotate(240deg); animation-delay: -0.3s; }
      .loader-dots-spinner div:nth-child(10) { transform: rotate(270deg); animation-delay: -0.2s; }
      .loader-dots-spinner div:nth-child(11) { transform: rotate(300deg); animation-delay: -0.1s; }
      .loader-dots-spinner div:nth-child(12) { transform: rotate(330deg); animation-delay: 0s; }
      @keyframes loader-dots-spinner-anim {
        0% { opacity: 1; }
        100% { opacity: 0.15; }
      }
    </style>
    <div id="iframeLoaderOverlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.94); z-index: 999; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
      <div class="loader-dots-spinner">
        <div></div><div></div><div></div><div></div>
        <div></div><div></div><div></div><div></div>
        <div></div><div></div><div></div><div></div>
      </div>
      <div style="margin-top: 18px; font-weight: 700; color: #1e293b; font-size: 1.05rem; letter-spacing: 0.5px;">
        ກຳລັງໂຫຼດຂໍ້ມູນ...
      </div>
    </div>

    <iframe width="100%" height="100%" frameborder="0" name="frame" src="<?php echo $default_iframe_src; ?>" style="background-color: #f4f6f9;"></iframe>
    <style>
      .content-wrapper {
        height: calc(100vh - 64px - 42px) !important;
      }
      @media (max-width: 991.98px) {
        .content-wrapper {
          height: calc(100vh - 56px - 42px) !important;
        }
      }
    </style>
    <script>
      function hideFastLoader() {
        var loader = document.getElementById('iframeLoaderOverlay');
        if (loader) {
          loader.style.opacity = '0';
          loader.style.transition = 'opacity 0.25s ease';
          setTimeout(function() { loader.style.display = 'none'; }, 250);
        }
      }
      function showFastLoader() {
        var loader = document.getElementById('iframeLoaderOverlay');
        if (loader) {
          loader.style.display = 'flex';
          loader.style.opacity = '1';
        }
      }
      (function() {
        var defaultSrc = '<?php echo $default_iframe_src; ?>';
        var hasDashboardPerm = <?php echo hasPermission('dashboard') ? 'true' : 'false'; ?>;
        var savedSrc = sessionStorage.getItem('currentIframeSrc');
        if (!hasDashboardPerm && (!savedSrc || savedSrc === 'home.php' || savedSrc.endsWith('/home.php'))) {
          savedSrc = defaultSrc;
          sessionStorage.setItem('currentIframeSrc', defaultSrc);
        }
        if (savedSrc) {
          document.getElementsByName('frame')[0].src = savedSrc;
        }
        // Safety timeout: automatically hide loading spinner after 600ms
        setTimeout(hideFastLoader, 600);
      })();
    </script>
  </div>

  <!-- Main Footer -->
  <footer class="main-footer" style="background: #ffffff; border-top: 1px solid #cbd5e1; color: #475569; padding: 0 20px; font-size: 0.88rem; height: 42px; display: flex; align-items: center; justify-content: space-between; box-sizing: border-box;">
    <div style="font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
      <span>ລະບົບຂາຍ POS</span>
    </div>
    <div style="font-weight: 700; color: #64748b;">
      <span>Version 3.8.26</span>
    </div>
  </footer>

</div>
<!-- ./wrapper -->

<!-- Bootstrap 4 -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>

<script>
  $(function() {
    var posUserToggledManual = false;

    // Handle manual Hamburger PushMenu toggle click
    $(document).on('click', '#topNavbarPushMenuBtn, [data-widget="pushmenu"]', function(e) {
      e.preventDefault();
      e.stopPropagation();

      posUserToggledManual = true;

      var isMobile = window.innerWidth < 768;
      var isCollapsed = $('body').hasClass('sidebar-collapse') || $('html').hasClass('sidebar-collapse');
      
      if (isMobile) {
        if ($('body').hasClass('sidebar-open')) {
          $('html, body').removeClass('sidebar-open sidebar-is-opening');
        } else {
          $('html, body').addClass('sidebar-open').removeClass('sidebar-collapse sidebar-closed');
        }
      } else {
        if (isCollapsed) {
          $('html, body').removeClass('sidebar-collapse sidebar-closed');
        } else {
          $('html, body').addClass('sidebar-collapse');
        }
      }
    });

    // Disable hover expansion on sidebar completely
    $('.main-sidebar, .brand-link').off('mouseenter mouseleave mouseover mouseout');
    $(document).off('mouseenter mouseleave mouseover mouseout', '.main-sidebar, .brand-link');

    // Restore sidebar scroll position if available
    var sidebarEl = document.querySelector('.sidebar');
    if (sidebarEl && window.jQuery && jQuery.fn.overlayScrollbars) {
      var os = $(sidebarEl).overlayScrollbars();
      var savedScroll = sessionStorage.getItem('sidebarScrollTop');
      if (os && savedScroll) {
        os.scroll({ y: parseInt(savedScroll, 10) }, 0);
      }
    }

    var loaderSafetyTimer = null;

    function showFastLoader() {
      clearTimeout(loaderSafetyTimer);
      $('#iframeLoaderOverlay').stop(true, true).fadeIn(60);
      loaderSafetyTimer = setTimeout(function() {
        $('#iframeLoaderOverlay').stop(true, true).fadeOut(120);
      }, 1200);
    }

    function hideFastLoader() {
      clearTimeout(loaderSafetyTimer);
      $('#iframeLoaderOverlay').stop(true, true).fadeOut(120);
    }

    // ===== Exclusive Active Menu Highlight =====
    var $sidebar = $('.nav-sidebar');
    
    $sidebar.on('click', '.nav-link', function() {
      var href = $(this).attr('href');
      
      if (!href || href === '#' || $(this).closest('.nav-item').hasClass('has-treeview')) {
        return;
      }

      // Reset manual toggle flag on new menu navigation
      posUserToggledManual = false;

      // Show high-speed 12-dot pulse loader overlay on menu click
      showFastLoader();

      $('.nav-sidebar .nav-link').removeClass('active');
      sessionStorage.setItem('currentIframeSrc', href);

      if (href && (href.indexOf('pos.php') !== -1 || href.indexOf('/pos/') !== -1)) {
        $('html, body').addClass('sidebar-collapse').removeClass('sidebar-closed sidebar-open');
      } else {
        $('html, body').removeClass('sidebar-collapse sidebar-closed sidebar-open');
      }

      if (sidebarEl && window.jQuery && jQuery.fn.overlayScrollbars) {
        var osInstance = $(sidebarEl).overlayScrollbars();
        if (osInstance && typeof osInstance.scroll === 'function') {
          var scrollObj = osInstance.scroll();
          var scrollTop = 0;
          if (scrollObj) {
            if (scrollObj.position && typeof scrollObj.position.y !== 'undefined') {
              scrollTop = scrollObj.position.y;
            } else if (typeof scrollObj.y !== 'undefined') {
              scrollTop = scrollObj.y;
            }
          }
          sessionStorage.setItem('sidebarScrollTop', scrollTop);
        }
      }
    });

    $('iframe[name="frame"]').on('load', function() {
      // Hide high-speed 12-dot pulse loader overlay smoothly on page load
      hideFastLoader();

      // Bind click/touchstart event inside iframe to close sidebar
      try {
        var iframeWin = this.contentWindow;
        var iframeDoc = this.contentDocument || iframeWin.document;
        if (iframeDoc && window.jQuery) {
          $(iframeDoc).off('click.sidebarClose touchstart.sidebarClose').on('click.sidebarClose touchstart.sidebarClose', function() {
            if (window.innerWidth <= 992) {
              var isSidebarOpen = $('body').hasClass('sidebar-open') || $('html').hasClass('sidebar-open');
              if (isSidebarOpen) {
                $('#topNavbarPushMenuBtn').click();
              }
            }
          });
        }
      } catch(e) {
        // Suppress cross-origin warnings if any
      }

      try {
        var path = this.contentWindow.location.pathname;
        var search = this.contentWindow.location.search || '';
        var fullTarget = path.substring(path.lastIndexOf('/') + 1) + search;
        var page = path.substring(path.lastIndexOf('/') + 1);

        var isPosPage = (path.indexOf('pos.php') !== -1 || path.indexOf('/pos/') !== -1);

        if (isPosPage) {
          // Auto-collapse on POS page initial load unless user manually toggled hamburger
          if (!posUserToggledManual) {
            $('html, body').addClass('sidebar-collapse').removeClass('sidebar-closed sidebar-open');
          }
        } else {
          // Auto-expand sidebar for ALL other pages
          posUserToggledManual = false;
          $('html, body').removeClass('sidebar-collapse sidebar-closed sidebar-open');
        }

        if (page && page !== 'blank') {
          var matched = false;
          $('.nav-sidebar .nav-link[target="frame"]').each(function() {
            var href = $(this).attr('href');
            if (href && (href === fullTarget || href.endsWith(fullTarget))) {
              $('.nav-sidebar .nav-link').removeClass('active');
              $(this).addClass('active');
              sessionStorage.setItem('currentIframeSrc', href);
              matched = true;
              return false;
            }
          });

          if (!matched) {
            $('.nav-sidebar .nav-link[target="frame"]').each(function() {
              var href = $(this).attr('href');
              if (href && (href === page || href.endsWith(page) || href.indexOf(page) !== -1)) {
                $('.nav-sidebar .nav-link').removeClass('active');
                $(this).addClass('active');
                sessionStorage.setItem('currentIframeSrc', href);
                return false;
              }
            });
          }
        }
      } catch(e) {}
    });

    // ===== Universal Accordion Dropdown Toggle (Only 1 dropdown open at a time) =====
    $(document).on('click', '.nav-sidebar .has-treeview > .nav-link', function(e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      var $parent = $(this).closest('.has-treeview');
      var $tree = $parent.children('.nav-treeview');
      var isOpen = $parent.hasClass('menu-open');

      // Accordion: Close ALL OTHER open dropdown menus first with balanced animation
      $('.nav-sidebar .has-treeview').not($parent).each(function() {
        var $otherParent = $(this);
        var $otherTree = $otherParent.children('.nav-treeview');
        $otherTree.stop(true, false).slideUp(300, function() {
          $otherParent.removeClass('menu-open menu-is-opening');
        });
      });

      // Toggle current clicked dropdown menu with balanced 350ms animation
      if (isOpen) {
        $tree.stop(true, false).slideUp(300, function() {
          $parent.removeClass('menu-open menu-is-opening');
        });
      } else {
        $parent.addClass('menu-is-opening');
        $tree.stop(true, false).slideDown(350, function() {
          $parent.addClass('menu-open').removeClass('menu-is-opening');
        });
      }
    });

    // PushMenu event sync
    $(document).on('collapsed.lte.pushmenu', function() {
      $('html, body').addClass('sidebar-collapse');
    });
    $(document).on('shown.lte.pushmenu', function() {
      $('html, body').removeClass('sidebar-collapse');
    });

    // ===== Auto-close sidebar on mobile when menu item clicked =====
    // ເຊື່ອງ sidebar ອັດຕະໂນມັດ ເມື່ອກົດ menu ໃນໜ້າຈໍ mobile (≤992px)
    $(document).on('click', '.nav-sidebar .nav-link', function() {
      var isMobile = window.innerWidth <= 992;
      var href = $(this).attr('href');
      var isLeafItem = href && href !== '#' && !$(this).closest('.nav-item').hasClass('has-treeview');

      if (isMobile && isLeafItem) {
        $('body').removeClass('sidebar-open sidebar-is-opening');
        if (window.jQuery && $.fn.PushMenu) {
          try { $('[data-widget="pushmenu"]').PushMenu('collapse'); } catch(err) {}
          try { $('[data-widget="pushmenu"]').PushMenu('close'); } catch(err) {}
        }
      }
    });

    // ===== Close Sidebar on Mobile when clicking/tapping on blank area outside sidebar =====
    // ກົດ ຫຼື ແຕະບ່ອນຫວ່າງເປົ່າ (Outside Sidebar) ເພື່ອເຊື່ອງ sidebar ໃນໜ້າຈໍ mobile (≤992px)
    $(document).on('click touchstart', function(e) {
      if (window.innerWidth <= 992) {
        var $target = $(e.target);
        var isSidebarOpen = $('body').hasClass('sidebar-open') || $('html').hasClass('sidebar-open');
        
        if (isSidebarOpen) {
          var isInsideSidebar = $target.closest('.main-sidebar').length > 0;
          var isPushMenuBtn = $target.closest('#topNavbarPushMenuBtn').length > 0 || $target.closest('[data-widget="pushmenu"]').length > 0;
          
          if (!isInsideSidebar && !isPushMenuBtn) {
            $('#topNavbarPushMenuBtn').click();
          }
        }
      }
    });

  });

  function confirmLogout() {
    Swal.fire({
      title: '<span style="font-size:1.15rem; font-weight:700; color:#1e293b;">ຢືນຢັນການອອກຈາກລະບົບ</span>',
      html: '<div style="font-size:0.90rem; color:#475569; font-weight:600; margin-top:4px;">ທ່ານຕ້ອງການອອກຈາກລະບົບແທ້ຫຼືບໍ່?</div>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: '<i class="fas fa-power-off mr-1"></i> ອອກຈາກລະບົບ',
      cancelButtonText: 'ຍົກເລີກ',
      heightAuto: false
    }).then(function(result) {
      if (result.isConfirmed) {
        sessionStorage.clear();
        window.location.href = '../auth/logout.php';
      }
    });
  }

  // ===== Client-Side Auto Idle Inactivity Timeout (15 ນາທີ = 900,000ms) =====
  (function() {
    var idleTime = 0;
    var maxIdleTime = 15 * 60 * 1000; // 15 ນາທີ
    var lastPingTime = Date.now();

    function resetIdleTimer() {
      idleTime = 0;
      // ຖ້າຜູ້ໃຊ້ນຳໃຊ້ຢູ່ — ສົ່ງ Heartbeat ໄປອັບເດດ Session ຢູ່ Server ເປັນໄລຍະ (ທຸກໆ 2 ນາທີ)
      if (Date.now() - lastPingTime > 120000) {
        lastPingTime = Date.now();
        fetch('../config/db.php', { method: 'HEAD' }).catch(function(){});
      }
    }

    // Reset idle timer ຢ່າງຕໍ່ເນື່ອງເມື່ອມີການເຄື່ອນໄຫວ ຫຼື ນຳໃຊ້ລະບົບ
    window.onload = resetIdleTimer;
    window.onmousemove = resetIdleTimer;
    window.onmousedown = resetIdleTimer;
    window.ontouchstart = resetIdleTimer;
    window.onclick = resetIdleTimer;
    window.onkeydown = resetIdleTimer;
    window.onscroll = resetIdleTimer;
    window.addEventListener('scroll', resetIdleTimer, true);
    window.addEventListener('message', resetIdleTimer);

    setInterval(function() {
      idleTime += 5000; // ກວດສອບທຸກໆ 5 ວິນາທີ
      if (idleTime >= maxIdleTime) {
        sessionStorage.clear();
        window.location.href = '../auth/logout.php?expired=1';
      }
    }, 5000);
  })();
</script>
</body>
</html>
