<!-- Table Card Footer: Record Counter & Circular Blue Pagination Aligned Right -->
<div class="card-footer bg-white border-top py-3 px-3.5 d-flex flex-column flex-md-row justify-content-between align-items-center no-print" style="row-gap: 12px;">
  
  <!-- Left: Record Counter -->
  <!-- <div class="d-flex align-items-center">
    <small class="text-muted font-weight-bold" style="font-size: 0.85rem;">
      ສະແດງ <?php echo number_format($start_record); ?> ຫາ <?php echo number_format($end_record); ?> ຈາກທັງໝົດ <?php echo number_format($total_records); ?> ລາຍການ
    </small>
  </div> -->

  <!-- Right: Circular Blue Pagination (pagination ວົງມົນສີຟ້າ ຊິດຂວາມືທາງລຸ່ມ) -->
  <?php if ($total_pages > 1 && $per_page !== 'all'): ?>
    <nav aria-label="Page navigation" class="ml-auto">
      <ul class="pagination report-pagination mb-0">
        <!-- Previous Page -->
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
          <a class="page-link" href="<?php echo getReportPageUrl(max(1, $page - 1)); ?>" aria-label="Previous">
            <i class="fas fa-chevron-left" style="font-size: 0.76rem;"></i>
          </a>
        </li>

        <?php
          $range = 2;
          $startP = max(1, $page - $range);
          $endP   = min($total_pages, $page + $range);

          if ($startP > 1) {
              echo '<li class="page-item"><a class="page-link" href="' . getReportPageUrl(1) . '">1</a></li>';
              if ($startP > 2) {
                  echo '<li class="page-item disabled"><span class="page-link" style="border:none;">...</span></li>';
              }
          }

          for ($p = $startP; $p <= $endP; $p++) {
              $activeClass = ($p == $page) ? 'active' : '';
              echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . getReportPageUrl($p) . '">' . $p . '</a></li>';
          }

          if ($endP < $total_pages) {
              if ($endP < $total_pages - 1) {
                  echo '<li class="page-item disabled"><span class="page-link" style="border:none;">...</span></li>';
              }
              echo '<li class="page-item"><a class="page-link" href="' . getReportPageUrl($total_pages) . '">' . $total_pages . '</a></li>';
          }
        ?>

        <!-- Next Page -->
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
          <a class="page-link" href="<?php echo getReportPageUrl(min($total_pages, $page + 1)); ?>" aria-label="Next">
            <i class="fas fa-chevron-right" style="font-size: 0.76rem;"></i>
          </a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>

</div>
</div>
