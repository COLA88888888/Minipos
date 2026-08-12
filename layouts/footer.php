<?php
if (!isset($base_path)) {
    $base_path = '';
}
?>
    <script src="<?php echo $base_path; ?>plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="<?php echo $base_path; ?>dist/js/adminlte.js"></script>
    <!-- Flatpickr Lao Datepicker JS -->
    <script src="<?php echo $base_path; ?>assets/js/flatpickr.min.js"></script>
    
    <script>
      // Preloader Fade Out with delay so spin icon is clearly visible
      function hidePreloader() {
          var p = document.getElementById('global-preloader');
          if (p && !p.classList.contains('fade-out')) {
              p.classList.add('fade-out');
          }
      }
      if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', function() {
              setTimeout(hidePreloader, 500);
          });
      } else {
          setTimeout(hidePreloader, 500);
      }
      setTimeout(hidePreloader, 800);

      document.addEventListener('DOMContentLoaded', function() {
          // ===== SIDEBAR SMART SCROLL =====
          var sidebarEl = document.querySelector('.main-sidebar .sidebar');
          if (sidebarEl) {
              if (typeof $ !== 'undefined' && $.fn.overlayScrollbars) {
                  try {
                      var osInstance = $(sidebarEl).overlayScrollbars();
                      if (osInstance) osInstance.destroy();
                  } catch(e) {}
              }

              function checkSidebarScroll() {
                  if (sidebarEl.scrollHeight > sidebarEl.clientHeight) {
                      sidebarEl.style.overflowY = 'auto';
                  } else {
                      sidebarEl.style.overflowY = 'hidden';
                  }
              }

              checkSidebarScroll();
              var observer = new MutationObserver(function() {
                  setTimeout(checkSidebarScroll, 200);
              });
              observer.observe(sidebarEl, { childList: true, subtree: true, attributes: true });

              window.addEventListener('resize', checkSidebarScroll);
          }
      });
    </script>
  </body>
</html>
