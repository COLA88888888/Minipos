<!-- Products Grid (2 on mobile, 6 on desktop screens) -->
<div class="row px-1" id="productGrid">
  <?php foreach ($products as $p): ?>
    <?php
      $imgName = $p['img_url'] ? basename($p['img_url']) : 'image.jpg';
      $imgPath = $base_path . 'assets/product_img/' . $imgName;
      $stock = floatval($p['qty'] ?? 0);
    ?>
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 pos-product-col product-item-card px-1 mb-2" 
         data-name="<?php echo htmlspecialchars(strtolower($p['product_name'])); ?>"
         data-barcode="<?php echo htmlspecialchars($p['barcode'] ?? ''); ?>"
         data-id="<?php echo $p['product_id']; ?>"
         data-category="<?php echo $p['category_id']; ?>">
         
      <div class="card product-card product-card-<?php echo $p['product_id']; ?> h-100 shadow-sm" 
           style="border-radius: 12px !important; border: <?php echo $stock <= 0 ? '2px solid #ef4444' : ($stock <= 10 ? '2px solid #f59e0b' : '1.5px solid #3b82f6'); ?> !important; background: <?php echo $stock <= 0 ? '#fff5f5' : '#ffffff'; ?>; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; <?php echo $stock <= 0 ? 'opacity: 0.82;' : ''; ?>" 
           onclick='addProductToCart(<?php echo htmlspecialchars(json_encode($p)); ?>)'>
        
        <div class="product-img-wrap" style="position: relative; width: 100%; height: 110px; max-height: 110px; overflow: hidden; background: #f8fafc; padding: 6px; display: flex; align-items: center; justify-content: center; border-radius: 10px 10px 0 0;">
          <img src="<?php echo htmlspecialchars($imgPath); ?>" 
               class="product-img-full"
               style="max-width: 100% !important; max-height: 100% !important; width: auto !important; height: auto !important; object-fit: contain !important; display: block !important; margin: 0 auto !important;"
               onerror="this.src='<?php echo $base_path; ?>assets/img/image.jpg';">
          
          <!-- Stock Status Badge Top Left -->
          <span class="stock-status-badge stock-status-badge-<?php echo $p['product_id']; ?> <?php echo $stock <= 0 ? 'out-of-stock' : ($stock <= 10 ? 'low-stock' : ''); ?>" 
                style="display: <?php echo $stock <= 10 ? 'inline-block' : 'none'; ?>;">
            <?php echo $stock <= 0 ? 'ໝົດແລ້ວ' : 'ໃກ້ໝົດ'; ?>
          </span>

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
            <span class="font-weight-bold" style="font-size: 0.96rem; color: #16a34a;">
              <?php echo number_format($p['price'], 0); ?> <small style="font-size: 0.76rem; font-weight: 700;">₭</small>
            </span>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
