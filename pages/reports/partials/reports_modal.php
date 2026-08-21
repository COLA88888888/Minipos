<!-- Modal: Bill Details Popup -->
<div class="modal fade" id="reportBillModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
      <div class="modal-header bg-primary text-white py-3 px-4" style="background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;">
        <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
          <i class="fas fa-receipt mr-2"></i> ລາຍລະອຽດໃບບິນ: <span id="modal_bill_no" class="text-warning"></span>
        </h5>
        <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <!-- Bill Header Info -->
        <div class="row mb-3 bg-light p-2.5 rounded border mx-0" style="font-size: 0.86rem; row-gap: 6px;">
          <div class="col-6">
            <span class="text-muted">ວັນທີ-ເວລາ:</span> <br>
            <strong id="modal_date" class="text-dark"></strong>
          </div>
          <div class="col-6 text-right">
            <span class="text-muted">ຜູ້ຂາຍ:</span> <br>
            <strong id="modal_cashier" class="text-dark"></strong>
          </div>
          <div class="col-12 mt-1 border-top pt-1 text-muted d-flex justify-content-between flex-wrap" style="gap: 4px;">
            <div>ລູກຄ້າ: <strong id="modal_customer" class="text-dark"></strong></div>
            <div id="modal_bank_wrapper" style="display:none;"><i class="fas fa-university text-info mr-1"></i> ໂອນຜ່ານທະນາຄານ: <strong id="modal_bank_name" class="text-primary font-weight-bold"></strong></div>
          </div>
        </div>

        <!-- Bill Items Table -->
        <div class="table-responsive mb-3 border rounded">
          <table class="table table-sm table-striped mb-0" style="font-size: 0.85rem;">
            <thead class="bg-light">
              <tr>
                <th class="text-left">ລາຍການສິນຄ້າ</th>
                <th class="text-center" style="width: 70px;">ຈຳນວນ</th>
                <th class="text-right" style="width: 90px;">ລາຄາ</th>
                <th class="text-right" style="width: 100px;">ລວມ (₭)</th>
              </tr>
            </thead>
            <tbody id="modal_items_body">
              <!-- Loaded via AJAX -->
            </tbody>
          </table>
        </div>

        <!-- Bill Financial Summary -->
        <div class="border-top pt-2" style="font-size: 0.88rem;">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">ລາຄາລວມ:</span>
            <span id="modal_subtotal" class="font-weight-bold text-dark">0 ₭</span>
          </div>
          <div class="d-flex justify-content-between mb-1 text-danger">
            <span>ສ່ວນຫຼຸດ:</span>
            <span id="modal_discount" class="font-weight-bold">0 ₭</span>
          </div>
          <div class="d-flex justify-content-between mb-2 pt-1 border-top" style="font-size: 1.05rem;">
            <strong class="text-dark">ຍອດຂາຍສຸດທິ:</strong>
            <strong id="modal_net_total" class="text-success">0 ₭</strong>
          </div>

          <div class="bg-light p-2.5 rounded border" style="font-size: 0.84rem;">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">ຮັບເງິນສົດ:</span>
              <span id="modal_cash_rec" class="font-weight-bold text-dark">0 ₭</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">ຮັບເງິນໂອນ:</span>
              <span id="modal_qr_rec" class="font-weight-bold text-primary">0 ₭</span>
            </div>
            <div class="d-flex justify-content-between mb-1 border-top pt-1">
              <span class="text-muted">ເງິນທອນ:</span>
              <span id="modal_change" class="font-weight-bold text-info">0 ₭</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light py-2 px-4">
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" data-dismiss="modal" style="border-radius: 8px;">
          ປິດ
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: POS Thermal Receipt Print (Exact POS Format) -->
<div class="modal fade" id="reportReceiptModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
      <div class="modal-header bg-success text-white py-3 px-4">
        <h6 class="modal-title font-weight-bold"><i class="fas fa-receipt mr-1"></i> ໃບບິນຮັບເງິນ</h6>
        <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3" id="reportReceiptPrintArea" style="font-family:'Noto Sans Lao Looped', monospace, sans-serif;font-size:12px;color:#000;">
        <div class="text-center mb-2">
          <?php
            $base_path = '../../';
            $logoName = !empty($company['img_url']) ? basename($company['img_url']) : 'logo.png';
            $logoPath = $base_path . 'assets/img/logo/' . $logoName;
          ?>
          <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo" class="receipt-logo"
            style="max-width:80px;height:auto;max-height:80px;object-fit:contain;margin:10px auto 2px auto;display:block;"
            onerror="this.onerror=null;this.src='<?php echo $base_path; ?>assets/img/logo/logo.png';">
          <h6 class="font-weight-bold mb-0" style="font-size:15px;color:#000;font-weight:700;"><?php echo htmlspecialchars($company['com_name_la'] ?? 'MiniPOS'); ?></h6>
          <div class="receipt-header-address" style="font-size:12px;font-weight:600;color:#000;line-height:1.4;margin-top:2px;"><?php echo htmlspecialchars($company['com_address'] ?? ''); ?></div>
          <div class="receipt-header-tel" style="font-size:12px;font-weight:600;color:#000;line-height:1.4;">ໂທ: <?php echo htmlspecialchars($company['com_tel'] ?? ''); ?></div>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ເລກບິນ:</span><span id="rc_rep_bill" style="font-weight:700;">-</span></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ວັນທີ:</span><span id="rc_rep_date">-</span></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ຜູ້ຂາຍ:</span><span id="rc_rep_cashier">-</span></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ລູກຄ້າ:</span><span id="rc_rep_customer">ລູກຄ້າທົ່ວໄປ</span></div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <table style="width:100%;font-size:11.5px;color:#000;">
          <thead>
            <tr style="border-bottom:1px dashed #000;">
              <th style="text-align:left;padding-bottom:3px;font-weight:700;color:#000;">ລາຍການ</th>
              <th style="text-align:center;padding-bottom:3px;font-weight:700;width:50px;color:#000;">ຈຳນວນ</th>
              <th style="text-align:right;padding-bottom:3px;font-weight:700;width:75px;color:#000;">ລາຄາ</th>
            </tr>
          </thead>
          <tbody id="rc_rep_items" style="color:#000;font-weight:600;"></tbody>
        </table>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div id="rc_rep_payment_rows" style="color:#000;font-weight:600;">
          <div class="d-flex justify-content-between"><span>ຮັບເງິນສົດ:</span><span id="rc_rep_cash_amt">0 ₭</span></div>
          <div class="d-flex justify-content-between"><span>ຮັບເງິນໂອນ:</span><span id="rc_rep_qr_amt">0 ₭</span></div>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ລວມ:</span><span id="rc_rep_subtotal">0 ₭</span></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ສ່ວນຫຼຸດ:</span><span id="rc_rep_discount">0 ₭</span></div>
        <div class="d-flex justify-content-between" id="rc_rep_vat_row" style="color:#000;font-weight:600;display:none;"><span id="rc_rep_vat_label">ອມພ (VAT):</span><span id="rc_rep_vat">0 ₭</span></div>
        <div class="d-flex justify-content-between font-weight-bold" style="font-size:13.5px;color:#000;font-weight:700;"><span>ຍອດສຸດທິ:</span><span id="rc_rep_total">0 ₭</span></div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div class="d-flex justify-content-between font-weight-bold" style="color:#000;font-weight:700;"><span>ເງິນທອນ:</span><span id="rc_rep_change">0 ₭</span></div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <?php 
          if (empty($company)) {
              try {
                  $company = $pdo->query("SELECT * FROM tbcompanyinfo LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
              } catch (Exception $e) {
                  $company = [];
              }
          }
          $storeQrPath = resolveBankQr($company['qr_img'] ?? '');
        ?>
        <?php if (!empty($storeQrPath)): ?>
        <div class="text-center my-2 receipt-qr-box">
          <img src="<?php echo htmlspecialchars($storeQrPath); ?>" alt="QR Code" class="receipt-qr-img"
               style="max-width:100px;max-height:100px;width:100px;height:auto;object-fit:contain;margin:6px auto 2px auto;display:block;"
               onerror="this.onerror=null;this.src='<?php echo $base_path; ?>assets/img/qr_placeholder.png';">
          <div style="font-size:10.5px;font-weight:700;color:#000;margin-top:2px;">ສະແກນ QR Code ເພື່ອຊຳລະເງິນ</div>
        </div>
        <?php endif; ?>
        <div class="text-center receipt-footer-msg" style="margin-top:10px !important; padding-top:8px; border-top:1px dashed #000; font-size:12.5px; font-weight:700; color:#000; text-align:center;"><?php echo htmlspecialchars(!empty($company['barcode']) ? $company['barcode'] : 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!'); ?></div>
      </div>
      <div class="modal-footer border-0 p-3 bg-light">
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">ປິດ</button>
        <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" onclick="doPrintReportReceipt()"><i class="fas fa-print mr-1"></i> ພິມໃບບິນ</button>
      </div>
    </div>
  </div>
</div>
