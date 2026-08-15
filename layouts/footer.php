<?php
if (!isset($base_path)) {
    $base_path = '';
}
?>
    <script src="<?php echo $base_path; ?>plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="<?php echo $base_path; ?>dist/js/adminlte.js"></script>
    <!-- Select2 JS -->
    <script src="<?php echo $base_path; ?>plugins/select2/js/select2.full.min.js"></script>
    <!-- Flatpickr Lao Datepicker JS -->
    <script src="<?php echo $base_path; ?>assets/js/flatpickr.min.js"></script>
    
    <script>
      // Smart Preloader: Immediately cancel timer & hide preloader on fast page load
      function hidePreloader() {
          if (window._slowNetPreloaderTimer) clearTimeout(window._slowNetPreloaderTimer);
          var p = document.getElementById('global-preloader');
          if (p) {
              p.classList.remove('show-slow-net');
              p.style.display = 'none';
          }
      }
      hidePreloader();
      document.addEventListener('DOMContentLoaded', hidePreloader);
      window.addEventListener('load', hidePreloader);

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
