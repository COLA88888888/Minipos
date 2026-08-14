<!-- Products Grid (2 on mobile, 6 on desktop screens) -->
<div class="row px-1" id="productGrid">
  <?php foreach ($products as $p): ?>
    <?php
      $imgName = $p['img_url'] ? basename($p['img_url']) : 'image.jpg';
      $imgPath = $base_path . 'assets/product_img/' . $imgName;
      $stock = floatval($p['qty'] ?? 0);
      $hasPromo = !empty($p['has_promo']);
      $isGiftPromo = ($p['promo_type'] ?? '') === 'buy_x_get_y' || !empty($p['gift_product_name']);
      $borderColor = $hasPromo ? ($isGiftPromo ? '#10b981' : '#ef4444') : ($stock <= 0 ? '#ef4444' : ($stock <= 10 ? '#f59e0b' : '#3b82f6'));
    ?>
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 pos-product-col product-item-card px-1 mb-2" 
         data-name="<?php echo htmlspecialchars(strtolower($p['product_name'])); ?>"
         data-barcode="<?php echo htmlspecialchars($p['barcode'] ?? ''); ?>"
         data-id="<?php echo $p['product_id']; ?>"
         data-category="<?php echo $p['category_id']; ?>">
         
      <div class="card product-card product-card-<?php echo $p['product_id']; ?> h-100 shadow-sm" 
           data-id="<?php echo $p['product_id']; ?>"
           style="border-radius: 12px !important; border: 2px solid <?php echo $borderColor; ?> !important; background: <?php echo $stock <= 0 ? '#fff5f5' : '#ffffff'; ?>; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; <?php echo $stock <= 0 ? 'opacity: 0.82;' : ''; ?>">
        
        <div class="product-img-wrap" style="position: relative; width: 100%; height: 110px; max-height: 110px; overflow: hidden; background: #f8fafc; padding: 6px; display: flex; align-items: center; justify-content: center; border-radius: 10px 10px 0 0;">
          <img src="<?php echo htmlspecialchars($imgPath); ?>" 
               class="product-img-full"
               style="max-width: 100% !important; max-height: 100% !important; width: auto !important; height: auto !important; object-fit: contain !important; display: block !important; margin: 0 auto !important;"
               onerror="this.src='<?php echo $base_path; ?>assets/img/image.jpg';">
          
          <!-- Stock Status Badge Top Left -->
          <span class="stock-status-badge stock-status-badge-<?php echo $p['product_id']; ?> <?php echo $stock <= 0 ? 'out-of-stock' : ($stock <= 10 ? 'low-stock' : ''); ?>" 
                style="display: <?php echo $stock <= 10 ? 'inline-block' : 'none'; ?>; position: absolute; top: 5px; left: 5px; z-index: 10;">
            <?php echo $stock <= 0 ? 'ໝົດແລ້ວ' : 'ໃກ້ໝົດ'; ?>
          </span>

          <!-- Promo Badge (Discount or Free Gift - RED Theme, Full Text Wrapping) -->
          <?php if ($hasPromo): ?>
            <?php 
              $isGiftPromo = ($p['promo_type'] ?? '') === 'buy_x_get_y' || !empty($p['gift_product_name']);
              $badgeBg = 'linear-gradient(135deg, #ef4444, #dc2626)';
              $badgeShadow = 'rgba(239,68,68,0.45)';
              $badgeIcon = $isGiftPromo ? 'fa-gift' : 'fa-fire';
              $topPos = ($stock <= 10) ? '28px' : '6px';
            ?>
            <span class="badge badge-danger font-weight-bold" 
                  style="position: absolute; top: <?php echo $topPos; ?>; left: 6px; right: 6px; z-index: 11; font-size: 0.68rem; padding: 3px 6px; border-radius: 6px; box-shadow: 0 2px 6px <?php echo $badgeShadow; ?>; background: <?php echo $badgeBg; ?>; color: #ffffff; white-space: normal; word-break: break-word; line-height: 1.25; text-align: center;">
              <i class="fas <?php echo $badgeIcon; ?> mr-1" style="font-size: 0.64rem;"></i> <?php echo htmlspecialchars($p['promo_badge']); ?>
            </span>
          <?php endif; ?>

          <span class="cart-qty-badge product-qty-badge-<?php echo $p['product_id']; ?>" 
                style="position: absolute; top: 5px; right: 5px; z-index: 10; display: none; background: #ef4444; color: #ffffff; font-size: 0.75rem; font-weight: 800; min-width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 50%; border: 2px solid #ffffff; box-shadow: 0 3px 8px rgba(239,68,68,0.45); padding: 0 4px; white-space: nowrap;">0</span>
        </div>
        
        <div class="card-body d-flex flex-column text-left" style="padding: 7px 8px !important;">
          <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.84rem; line-height: 1.2; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($p['product_name']); ?>">
            <?php echo htmlspecialchars($p['product_name']); ?>
          </h6>

          <!-- Prominent Stock Info Pill -->
          <div class="stock-info-wrap stock-info-wrap-<?php echo $p['product_id']; ?> <?php echo $stock <= 0 ? 'out-of-stock' : ($stock <= 10 ? 'low-stock' : 'in-stock'); ?>">
            <span>ເຫຼືອ: <span class="font-weight-bold product-stock-val product-stock-val-<?php echo $p['product_id']; ?>" data-initial-stock="<?php echo $p['qty']; ?>" data-cut-qty="<?php echo intval($p['cut_qty'] ?? 1); ?>"><?php echo number_format($p['qty']); ?></span> <?php echo htmlspecialchars($p['unit'] ?? ''); ?></span>
          </div>
          
          <div class="mt-auto pt-1 border-top text-left">
            <?php if ($hasPromo && floatval($p['original_price'] ?? 0) > floatval($p['price'] ?? 0)): ?>
              <div class="d-flex align-items-baseline flex-wrap justify-content-start" style="gap: 3px;">
                <small class="font-weight-bold text-muted" style="font-size: 0.74rem; text-decoration: line-through; color: #94a3b8 !important;">
                  <?php echo number_format($p['original_price'], 0); ?> ₭
                </small>
                <span class="font-weight-bold text-danger" style="font-size: 0.94rem; color: #ef4444 !important;">
                  <?php echo number_format($p['price'], 0); ?> <small style="font-size: 0.72rem; font-weight: 700;">₭</small>
                </span>
              </div>
            <?php else: ?>
              <span class="font-weight-bold" style="font-size: 0.96rem; color: #16a34a;">
                <?php echo number_format($p['price'], 0); ?> <small style="font-size: 0.76rem; font-weight: 700;">₭</small>
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
