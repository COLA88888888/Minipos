<style>
.kpi-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 16px;
}
.kpi-card-item {
  border-radius: 10px !important;
  padding: 18px 20px !important;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
  transform: none !important;
}
.kpi-card-item:hover {
  transform: none !important;
}
.kpi-card-title {
  font-size: 0.88rem !important;
  font-weight: 700 !important;
  color: rgba(255, 255, 255, 0.9) !important;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}
.kpi-card-val {
  font-size: clamp(1.2rem, 1.3vw, 1.5rem) !important;
  font-weight: 800 !important;
  white-space: nowrap !important;
  overflow: visible !important;
  text-overflow: clip !important;
}
.kpi-card-unit {
  font-size: 0.88rem !important;
  font-weight: 600 !important;
  color: rgba(255, 255, 255, 0.88) !important;
}
.kpi-card-icon {
  width: 46px !important;
  height: 46px !important;
  border-radius: 10px !important;
  background: rgba(255, 255, 255, 0.22) !important;
  color: #ffffff !important;
  backdrop-filter: blur(6px);
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex-shrink: 0 !important;
  font-size: 1.3rem !important;
  margin-left: 6px !important;
}

/* Mobile Phone Screens: 2 Columns side-by-side with fit font */
@media (max-width: 767px) {
  .kpi-cards-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
  }
  .kpi-card-item {
    padding: 14px 13px !important;
  }
  .kpi-card-title {
    font-size: 0.78rem !important;
    letter-spacing: 0.2px !important;
  }
  .kpi-card-val {
    font-size: 1.05rem !important;
    white-space: nowrap !important;
  }
  .kpi-card-unit {
    font-size: 0.76rem !important;
  }
  .kpi-card-icon {
    width: 38px !important;
    height: 38px !important;
    font-size: 1rem !important;
    margin-left: 4px !important;
    border-radius: 8px !important;
  }
}
</style>

<?php if ($type === 'daily'): ?>

  <!-- DAILY REPORT: 10 SPECIFIC SUMMARY KPI CARDS BOX -->
  <div class="mb-4 kpi-cards-grid">

    <!-- 1. ລວມຍອດ (Blue Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ລວມຍອດ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_gross_sales ?? 0); ?>" data-suffix=" ₭">0 ₭</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-file-invoice-dollar"></i>
      </div>
    </div>

    <!-- 2. ສ່ວນຫຼຸດສິນຄ້າ (Rose Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ສ່ວນຫຼຸດສິນຄ້າ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_item_discounts ?? 0); ?>" data-suffix=" ₭">0 ₭</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-tag"></i>
      </div>
    </div>

    <!-- 3. ສ່ວນຫຼຸດໃບບິນ (Pink/Purple Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ສ່ວນຫຼຸດໃບບິນ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_bill_discounts ?? 0); ?>" data-suffix=" ₭">0 ₭</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-receipt"></i>
      </div>
    </div>

    <!-- 4. ສ່ວນຫຼຸດທັງໝົດ (Crimson Red Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ສ່ວນຫຼຸດທັງໝົດ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_total_discounts ?? 0); ?>" data-suffix=" ₭">0 ₭</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-tags"></i>
      </div>
    </div>

    <!-- 5. ຍອດຂາຍສຸດທິ (Emerald Green Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ຍອດຂາຍສຸດທິ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_net_sales ?? 0); ?>" data-suffix=" ₭">0 ₭</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-coins"></i>
      </div>
    </div>

    <!-- 6. ເງິນສົດ (Indigo Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ເງິນສົດ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_cash_payments ?? 0); ?>" data-suffix=" ₭">0 ₭</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-money-bill-wave"></i>
      </div>
    </div>

    <!-- 7. ເງິນໂອນ (Cyan/Teal Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #06b6d4 0%, #0e7490 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ເງິນໂອນ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_qr_payments ?? 0); ?>" data-suffix=" ₭">0 ₭</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-qrcode"></i>
      </div>
    </div>

    <!-- 9. ບິນ (Royal Blue Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ບິນ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_bills_count ?? 0); ?>" data-suffix=" ບິນ">0 ບິນ</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-file-invoice"></i>
      </div>
    </div>

    <!-- 10. ລາຍການ (Teal Gradient) -->
    <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
         style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
      <div style="z-index: 2; min-width: 0;">
        <div class="kpi-card-title">ລາຍການ</div>
        <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
          <span class="counter-num" data-target="<?php echo (int)($daily_items_qty ?? 0); ?>" data-suffix=" ອັນ">0 ອັນ</span>
        </div>
      </div>
      <div class="kpi-card-icon">
        <i class="fas fa-boxes"></i>
      </div>
    </div>

  </div>

<?php else: ?>

  <?php if ($view_mode !== 'item'): ?>
    <?php
      $net_val = (float)$total_net;
      $disc_val = (float)$total_disc;
      $profit_val = (float)($total_profit > 0 ? $total_profit : max(0, $total_net - ($total_gross * 0.7)));
    ?>

    <!-- REGULAR SUMMARY KPI CARDS (5 CARDS) -->
    <div class="kpi-cards-grid" style="margin-bottom: 20px;">

      <!-- 1. ຈຳນວນບິນ -->
      <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
           style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
        <div style="z-index: 2; min-width: 0;">
          <div class="kpi-card-title">ຈຳນວນບິນ</div>
          <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <span class="counter-num" data-target="<?php echo (int)$total_bills_count; ?>" data-suffix=" ບິນ">0 ບິນ</span>
          </div>
        </div>
        <div class="kpi-card-icon">
          <i class="fas fa-file-invoice"></i>
        </div>
      </div>

      <!-- 2. ຈຳນວນສິນຄ້າ -->
      <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
           style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
        <div style="z-index: 2; min-width: 0;">
          <div class="kpi-card-title">ຈຳນວນສິນຄ້າ</div>
          <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <span class="counter-num" data-target="<?php echo (int)$total_qty; ?>" data-suffix=" ອັນ">0 ອັນ</span>
          </div>
        </div>
        <div class="kpi-card-icon">
          <i class="fas fa-boxes"></i>
        </div>
      </div>

      <!-- 3. ຍອດຂາຍ -->
      <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
           style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
        <div style="z-index: 2; min-width: 0;">
          <div class="kpi-card-title">ຍອດຂາຍ</div>
          <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <span class="counter-num" data-target="<?php echo (int)$net_val; ?>" data-suffix=" ₭">0 ₭</span>
          </div>
        </div>
        <div class="kpi-card-icon">
          <i class="fas fa-coins"></i>
        </div>
      </div>

      <!-- 4. ສ່ວນຫຼຸດ -->
      <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
           style="background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%);">
        <div style="z-index: 2; min-width: 0;">
          <div class="kpi-card-title">ສ່ວນຫຼຸດ</div>
          <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <span class="counter-num" data-target="<?php echo (int)$disc_val; ?>" data-suffix=" ₭">0 ₭</span>
          </div>
        </div>
        <div class="kpi-card-icon">
          <i class="fas fa-tags"></i>
        </div>
      </div>

      <!-- 5. ກຳໄລ -->
      <div class="kpi-card-item d-flex align-items-center justify-content-between text-white" 
           style="background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);">
        <div style="z-index: 2; min-width: 0;">
          <div class="kpi-card-title">ກຳໄລ</div>
          <div class="font-weight-bold mt-1 text-white kpi-card-val" style="font-family: 'Noto Sans Lao Looped', sans-serif;">
            <span class="counter-num" data-target="<?php echo (int)$profit_val; ?>" data-suffix=" ₭">0 ₭</span>
          </div>
        </div>
        <div class="kpi-card-icon">
          <i class="fas fa-chart-line"></i>
        </div>
      </div>

    </div>
  <?php endif; ?>

<?php endif; ?>

<!-- Animated Counter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  var counters = document.querySelectorAll('.counter-num');
  counters.forEach(function(el) {
    var target = parseInt(el.getAttribute('data-target') || '0', 10);
    var suffix = el.getAttribute('data-suffix') || '';
    if (isNaN(target)) target = 0;

    var startTime = null;
    var duration = 1800; // 1.8 seconds smooth elegant count-up

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      // Smooth ease out cubic
      var easeProgress = 1 - Math.pow(1 - progress, 3);
      var currentVal = Math.floor(easeProgress * target);
      
      el.textContent = currentVal.toLocaleString() + suffix;
      
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        el.textContent = target.toLocaleString() + suffix;
      }
    }
    
    window.requestAnimationFrame(step);
  });
});
</script>
