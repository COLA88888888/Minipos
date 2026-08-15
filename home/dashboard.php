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
      try {
        var savedSrc = sessionStorage.getItem('currentIframeSrc') || '';
        var isPos = savedSrc.indexOf('pos.php') !== -1 || savedSrc.indexOf('pos/') !== -1;
        if (isPos) {
          document.documentElement.classList.add('sidebar-collapse');
        }
      } catch(e) {}
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
  <nav class="main-header navbar navbar-expand navbar-white navbar-light justify-content-between">
    <!-- Left side: Menu toggle -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" id="topNavbarPushMenuBtn" href="#" role="button" style="color: #ffffff; font-size: 1.2rem;" title="ເມນູ">
          <i class="fas fa-bars"></i>
        </a>
      </li>
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

      // Badge style depending on remaining days
      $badgeBg = 'background: linear-gradient(135deg, #10b981, #059669); color: #ffffff;';
      if ($daysRemaining <= 15) {
        $badgeBg = 'background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff;';
      } elseif ($daysRemaining <= 30) {
        $badgeBg = 'background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff;';
      }
    ?>

    <!-- Right side: Subscription Info + Live Date/Time + Logout button -->
    <ul class="navbar-nav ml-auto align-items-center" style="gap: 12px; font-family: 'Noto Sans Lao Looped', sans-serif;">
      <!-- Real-Time Current Date & Time Display (Clean text without box or icon) -->
      <li class="nav-item d-none d-sm-flex align-items-center text-white mr-1" style="font-size: 0.85rem; font-weight: 600;">
        <span id="live_datetime_clock"><?php echo date('d/m/Y H:i:s'); ?></span>
      </li>

      <!-- Clean text subscription info on right side -->
      <li class="nav-item d-flex align-items-center text-white" style="font-size: 0.85rem; font-weight: 600; gap: 8px;">
        <!-- Always visible on all screens (Mobile & PC): ເຫຼືອ ... ວັນ -->
        <span class="d-inline-flex align-items-center">
          ເຫຼືອ <span class="mx-1 text-warning" style="font-size: 0.95rem; font-weight: 800; text-decoration: underline;"><?php echo $daysRemaining; ?></span> ວັນ
        </span>
        <!-- Hidden on Mobile / Tablet (Only visible on Desktop) -->
        <span class="opacity-50 d-none d-xl-inline" style="color: rgba(255,255,255,0.6);">|</span>
        <span class="d-none d-xl-inline opacity-90">
          <i class="fas fa-play-circle mr-1 text-light"></i>ເລີ່ມ: <strong><?php echo $formattedStart; ?></strong>
        </span>
        <span class="opacity-50 d-none d-lg-inline" style="color: rgba(255,255,255,0.6);">|</span>
        <span class="d-none d-lg-inline opacity-90">
          <i class="fas fa-flag-checkered mr-1 text-light"></i>ສິ້ນສຸດ: <strong><?php echo $formattedExpire; ?></strong>
        </span>
      </li>

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
      // 1. Expand the saved active menu item synchronously during initial parse
      var savedSrc = sessionStorage.getItem('currentIframeSrc') || 'home.php';
      if (savedSrc) {
        var links = document.querySelectorAll('.nav-sidebar a.nav-link');
        for (var i = 0; i < links.length; i++) {
          links[i].classList.remove('active');
        }
        for (var i = 0; i < links.length; i++) {
          var href = links[i].getAttribute('href');
          if (href && href !== '#' && (href === savedSrc || savedSrc.endsWith(href))) {
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
  <div class="content-wrapper" style="height: calc(100vh - 64px - 42px) !important; background-color: #f4f6f9;">
    <iframe width="100%" height="100%" frameborder="0" name="frame" src="home.php" style="background-color: #f4f6f9;"></iframe>
    <script>
      (function() {
        var savedSrc = sessionStorage.getItem('currentIframeSrc');
        if (savedSrc && savedSrc !== 'home.php') {
          document.getElementsByName('frame')[0].src = savedSrc;
        }
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
    // Handle manual PushMenu toggle click to keep POS sidebar state independent from Settings/Dashboard state
    $('[data-widget="pushmenu"]').on('click', function() {
      setTimeout(function() {
        var frame = document.getElementsByName('frame')[0];
        var isPos = false;
        try {
          var p = frame.contentWindow.location.pathname;
          isPos = (p.indexOf('pos.php') !== -1 || p.indexOf('pos') !== -1);
        } catch(e) {}

        var isCollapsed = $('body').hasClass('sidebar-collapse');
        var newState = isCollapsed ? 'collapse' : 'expand';

        if (isPos) {
          sessionStorage.setItem('sidebar_user_state_pos', newState);
        } else {
          sessionStorage.setItem('sidebar_user_state_other', newState);
        }
      }, 100);
    });

    // Disable hover expansion on sidebar
    $('.main-sidebar').off('mouseenter mouseleave');

    // Restore sidebar scroll position if available
    var sidebarEl = document.querySelector('.sidebar');
    if (sidebarEl && window.jQuery && jQuery.fn.overlayScrollbars) {
      var os = $(sidebarEl).overlayScrollbars();
      var savedScroll = sessionStorage.getItem('sidebarScrollTop');
      if (os && savedScroll) {
        os.scroll({ y: parseInt(savedScroll, 10) }, 0);
      }
    }

    // ===== Exclusive Active Menu Highlight =====
    var $sidebar = $('.nav-sidebar');
    
    $sidebar.on('click', '.nav-link', function() {
      var href = $(this).attr('href');
      
      if (!href || href === '#' || $(this).closest('.nav-item').hasClass('has-treeview')) {
        return;
      }

      $('.nav-sidebar .nav-link').removeClass('active');
      $(this).addClass('active');

      sessionStorage.setItem('currentIframeSrc', href);

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
      try {
        var path = this.contentWindow.location.pathname;
        var search = this.contentWindow.location.search || '';
        var fullTarget = path.substring(path.lastIndexOf('/') + 1) + search;
        var page = path.substring(path.lastIndexOf('/') + 1);

        var isMobile = window.innerWidth <= 992;

        // Strict rule requested by user:
        // ONLY collapse sidebar when on POS sales page (pos.php).
        // On ALL other pages (Stores/Settings, Reports, Products, Users, Dashboard), ALWAYS expand sidebar completely!
        var isPosPage = (path.indexOf('pos.php') !== -1 || path.indexOf('/pos/') !== -1);

        if (isPosPage) {
          if (!isMobile && window.jQuery && $.fn.PushMenu) {
            $('[data-widget="pushmenu"]').PushMenu('collapse');
          }
          $('html, body').addClass('sidebar-collapse').removeClass('sidebar-closed sidebar-open');
        } else {
          if (!isMobile && window.jQuery && $.fn.PushMenu) {
            $('[data-widget="pushmenu"]').PushMenu('expand');
          }
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

    // ===== Collapsed Sidebar Treeview Dropdown Toggle =====
    // ຕອນເຊື່ອງ sidebar ຖ້າກົດໄອຄອນທີ່ມີ ດັອບດາວ ໃຫ້ toggle ສະແດງໄອຄອນຍ່ອຍລົງມາເລີຍ
    $sidebar.on('click', '.has-treeview > .nav-link', function(e) {
      if ($('body').hasClass('sidebar-collapse')) {
        e.preventDefault();
        e.stopPropagation();
        var $parent = $(this).closest('.has-treeview');
        var $tree = $parent.children('.nav-treeview');
        var isOpen = $parent.hasClass('menu-open');

        if (isOpen) {
          $parent.removeClass('menu-open menu-is-opening');
          $tree.stop(true, true).slideUp(200);
        } else {
          $parent.addClass('menu-open menu-is-opening');
          $tree.stop(true, true).slideDown(200);
        }
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
</script>
</body>
</html>
