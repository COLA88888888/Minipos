<!-- Modal: Checkout -->
<div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered checkout-modal-dialog" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; background: #ffffff;">

      <!-- Header -->
      <div class="modal-header bg-primary py-2 px-4">
        <h5 class="modal-title font-weight-bold d-flex align-items-center mb-0" style="font-size: 18px; font-family: 'Noto Sans Lao Looped';">
          ຊຳລະເງິນ
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" style="opacity:0.9;font-size:1.4rem;"><span>&times;</span></button>
      </div>

      <!-- Body: Responsive Grid -->
      <div class="modal-body p-0" style="background:#f1f5f9; max-height:85vh; overflow-y:auto; -webkit-overflow-scrolling:touch;">
        <div class="checkout-grid">

          <!-- ===== LEFT COLUMN ===== -->
          <div class="checkout-left-col">

            <!-- Total -->
            <div class="text-center p-2.5 text-white" style="border-radius:12px;background:linear-gradient(135deg,#1e293b,#0f172a);border:1px solid rgba(255,255,255,0.2);box-shadow:0 4px 12px rgba(0, 68, 255, 0.4);">
              <div style="color:#94a3b8;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">
                <h6 class="font-weight-bold mb-1" style="font-size:0.82rem;">ຍອດທີ່ຕ້ອງຊຳລະ</h6>
              </div>
              <div class="font-weight-bold" id="checkoutTotalDisplay" style="font-size:1.35rem;color:#fff;font-family:'Noto Sans Lao Looped',sans-serif;line-height:1.2;word-break:break-all;">0 ₭</div>
            </div>

            <!-- Pay Mode -->
            <div>
              <div class="font-weight-bold mb-1" style="font-size:0.80rem;color:#475569;"><i class="fas fa-credit-card text-primary mr-1"></i> ວິທີຊຳລະ</div>
              <div class="d-flex" style="gap:7px;">
                <button type="button" id="payModeSingleBtn" onclick="setPayMode('single')"
                  style="flex:1;padding:7px 4px;border-radius:9px;border:2px solid #2563eb;background:#eff6ff;color:#1d4ed8;font-weight:700;font-size:0.82rem;cursor:pointer;transition:all 0.15s;font-family:'Noto Sans Lao Looped',sans-serif;">
                  <i class="fas fa-hand-holding-usd mr-1"></i> ຊຳລະດຽວ
                </button>
                <button type="button" id="payModeSplitBtn" onclick="setPayMode('split')"
                  style="flex:1;padding:7px 4px;border-radius:9px;border:2px solid #cbd5e1;background:#fff;color:#64748b;font-weight:700;font-size:0.82rem;cursor:pointer;transition:all 0.15s;font-family:'Noto Sans Lao Looped',sans-serif;">
                  <i class="fas fa-code-branch mr-1"></i> ຊຳລະຫຼາຍຊ່ອງ
                </button>
              </div>
            </div>

            <!-- Pay Type (single only) -->
            <div id="payTypeRow">
              <div class="font-weight-bold mb-1" style="font-size:0.80rem;color:#475569;"><i class="fas fa-tag text-primary mr-1"></i> ປະເພດ</div>
              <div class="d-flex" style="gap:7px;">
                <button type="button" id="payTypeCashBtn" onclick="selectPayTypeTab('ເງິນສົດ')"
                  style="flex:1;padding:7px 4px;border-radius:9px;border:2px solid #2563eb;background:#eff6ff;color:#1d4ed8;font-weight:700;font-size:0.82rem;cursor:pointer;transition:all 0.15s;font-family:'Noto Sans Lao Looped',sans-serif;">
                  <i class="fas fa-money-bill-wave mr-1"></i> ເງິນສົດ
                </button>
                <button type="button" id="payTypeQrBtn" onclick="selectPayTypeTab('ໂອນເງິນ / QR')"
                  style="flex:1;padding:7px 4px;border-radius:9px;border:2px solid #cbd5e1;background:#fff;color:#64748b;font-weight:700;font-size:0.82rem;cursor:pointer;transition:all 0.15s;font-family:'Noto Sans Lao Looped',sans-serif;">
                  <i class="fas fa-qrcode mr-1"></i> ໂອນ / QR
                </button>
              </div>
            </div>

            <!-- SINGLE: cash input -->
            <div id="singlePaySection">
              <div class="font-weight-bold mb-1" style="font-size:0.80rem;color:#475569;"><i class="fas fa-hand-holding-usd text-primary mr-1"></i> ຈຳນວນທີ່ຮັບ <span class="text-danger">*</span></div>
              <div class="input-group" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(37,99,235,0.10);">
                <input type="text" id="cashReceived" class="form-control text-right font-weight-bold"
                  placeholder="0" oninput="formatPriceInput(this);calculateChange();"
                  style="height:42px;font-size:1.2rem;color:#1d4ed8;background:#fff;border:2px solid #2563eb;border-right:none;">
                <div class="input-group-append">
                  <span class="input-group-text font-weight-bold text-white" style="background:#2563eb;border:2px solid #2563eb;border-left:none;padding:0 14px;font-size:1rem;">₭</span>
                </div>
              </div>
              <!-- Shortcuts -->
              <div class="d-flex flex-wrap mt-2" style="gap:5px;">
                <button type="button" class="btn btn-sm font-weight-bold text-white flex-fill" onclick="setExactAmount()"
                  style="background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;border-radius:7px;padding:5px 8px;font-size:0.78rem;">
                  <i class="fas fa-check-circle mr-1"></i> ພໍດີ
                </button>
                <button type="button" class="btn btn-sm btn-quick-cash font-weight-bold flex-fill" onclick="addCashShortcut(20000)" style="font-size:0.78rem;padding:5px 6px;">+20K</button>
                <button type="button" class="btn btn-sm btn-quick-cash font-weight-bold flex-fill" onclick="addCashShortcut(50000)" style="font-size:0.78rem;padding:5px 6px;">+50K</button>
                <button type="button" class="btn btn-sm btn-quick-cash font-weight-bold flex-fill" onclick="addCashShortcut(100000)" style="font-size:0.78rem;padding:5px 6px;">+100K</button>
                <button type="button" class="btn btn-sm btn-quick-cash font-weight-bold flex-fill" onclick="addCashShortcut(500000)" style="font-size:0.78rem;padding:5px 6px;">+500K</button>
              </div>
            </div>

            <!-- SPLIT: cash + qr inputs -->
            <div id="splitPaySection" style="display:none;">
              <!-- Cash -->
              <div class="mb-2">
                <div class="font-weight-bold mb-1 d-flex align-items-center" style="font-size:0.80rem;color:#16a34a;">
                  <i class="fas fa-money-bill-wave mr-1"></i> ເງິນສົດ
                </div>
                <div class="input-group" style="border-radius:9px;overflow:hidden;">
                  <input type="text" id="splitCashAmount" class="form-control text-right font-weight-bold"
                    placeholder="0" oninput="formatPriceInput(this);calcSplitRemaining();"
                    style="height:40px;font-size:1.1rem;color:#16a34a;border:2px solid #22c55e;border-right:none;background:#f0fdf4;">
                  <div class="input-group-append">
                    <span class="input-group-text font-weight-bold text-white" style="background:#22c55e;border:2px solid #22c55e;border-left:none;padding:0 12px;font-size:0.95rem;">₭</span>
                  </div>
                </div>
                <div class="d-flex flex-wrap mt-1" style="gap:4px;">
                  <button onclick="setSplitCashExact()" style="flex:1;background:#dcfce7;color:#16a34a;border:1px solid #86efac;border-radius:6px;font-size:0.74rem;font-weight:700;padding:3px 6px;cursor:pointer;">
                    <i class="fas fa-check mr-1"></i>ພໍດີທັງໝົດ
                  </button>
                  <button onclick="addSplitCash(20000)" style="flex:1;background:#f0fdf4;color:#16a34a;border:1px solid #86efac;border-radius:6px;font-size:0.74rem;font-weight:700;padding:3px 5px;cursor:pointer;">+20K</button>
                  <button onclick="addSplitCash(50000)" style="flex:1;background:#f0fdf4;color:#16a34a;border:1px solid #86efac;border-radius:6px;font-size:0.74rem;font-weight:700;padding:3px 5px;cursor:pointer;">+50K</button>
                  <button onclick="addSplitCash(100000)" style="flex:1;background:#f0fdf4;color:#16a34a;border:1px solid #86efac;border-radius:6px;font-size:0.74rem;font-weight:700;padding:3px 5px;cursor:pointer;">+100K</button>
                  <button onclick="addSplitCash(500000)" style="flex:1;background:#f0fdf4;color:#16a34a;border:1px solid #86efac;border-radius:6px;font-size:0.74rem;font-weight:700;padding:3px 5px;cursor:pointer;">+500K</button>
                </div>
              </div>
              <!-- QR -->
              <div>
                <div class="font-weight-bold mb-1 d-flex align-items-center" style="font-size:0.80rem;color:#7c3aed;">
                  <i class="fas fa-qrcode mr-1"></i> ໂອນເງິນ / QR
                </div>
                <div class="input-group" style="border-radius:9px;overflow:hidden;">
                  <input type="text" id="splitQrAmount" class="form-control text-right font-weight-bold"
                    placeholder="0" oninput="formatPriceInput(this);calcSplitRemaining();"
                    style="height:40px;font-size:1.1rem;color:#7c3aed;border:2px solid #a78bfa;border-right:none;background:#faf5ff;">
                  <div class="input-group-append">
                    <span class="input-group-text font-weight-bold text-white" style="background:#7c3aed;border:2px solid #7c3aed;border-left:none;padding:0 12px;font-size:0.95rem;">₭</span>
                  </div>
                </div>
                <button onclick="fillSplitQrRemaining()" style="margin-top:5px;background:#f3e8ff;color:#7c3aed;border:1px solid #c4b5fd;border-radius:6px;font-size:0.74rem;font-weight:700;padding:3px 10px;cursor:pointer;">
                  <i class="fas fa-magic mr-1"></i> ຕື່ມຍອດທີ່ຍັງຄ້າງ
                </button>
              </div>
            </div>

          </div><!-- /LEFT -->

          <!-- ===== RIGHT COLUMN ===== -->
          <div class="checkout-right-col">

            <!-- SINGLE: change display -->
            <div id="singleSummary">
              <div style="background:#f8fafc;border-radius:12px;border:1.5px solid #e2e8f0;padding:12px 14px;">
                <div style="font-size:0.76rem;color:#64748b;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;">ສຳຫຼວດການຊຳລະ</div>
                <div class="d-flex justify-content-between mb-1" style="font-size:0.86rem;">
                  <span class="text-muted">ລວມ:</span>
                  <span class="font-weight-bold text-dark text-truncate ml-2" id="singleTotalLbl">0 ₭</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:0.86rem;">
                  <span class="text-muted">ຮັບມາ:</span>
                  <span class="font-weight-bold text-primary text-truncate ml-2" id="singleCashLbl">0 ₭</span>
                </div>
                <div style="border-top:1px dashed #e2e8f0;margin:6px 0;"></div>
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                  <span class="font-weight-bold" style="font-size:0.88rem;">ເງິນທອນ:</span>
                  <span class="font-weight-bold text-success" id="changeDisplay" style="font-size:1.25rem;font-family:'Noto Sans Lao Looped',sans-serif;word-break:break-all;">0 ₭</span>
                </div>
              </div>
            </div>

            <!-- SPLIT: summary -->
            <div id="splitSummary" style="display:none;">
              <div style="background:#f8fafc;border-radius:12px;border:1.5px solid #e2e8f0;padding:12px 14px;">
                <div style="font-size:0.76rem;color:#64748b;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;">ສຳຫຼວດການຊຳລະ</div>
                <div class="d-flex justify-content-between mb-1" style="font-size:0.86rem;">
                  <span class="text-muted">ຍອດທັງໝົດ:</span>
                  <span class="font-weight-bold text-dark text-truncate ml-2" id="splitTotal">0 ₭</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:0.86rem;">
                  <span class="text-muted"><i class="fas fa-money-bill-wave text-success mr-1"></i>ເງິນສົດ:</span>
                  <span class="font-weight-bold text-success text-truncate ml-2" id="splitCashDisplay">0 ₭</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:0.86rem;">
                  <span class="text-muted"><i class="fas fa-qrcode mr-1" style="color:#7c3aed;"></i>ໂອນ / QR:</span>
                  <span class="font-weight-bold text-truncate ml-2" style="color:#7c3aed;" id="splitQrDisplay">0 ₭</span>
                </div>
                <div style="border-top:1px dashed #e2e8f0;margin:6px 0;"></div>
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                  <span class="font-weight-bold" style="font-size:0.88rem;" id="splitStatusLabel">ຍັງຄ້າງ:</span>
                  <span class="font-weight-bold" style="font-size:1.2rem;font-family:'Noto Sans Lao Looped',sans-serif;word-break:break-all;" id="splitRemainingDisplay">0 ₭</span>
                </div>
              </div>
            </div>

            <!-- Numpad -->
            <div style="flex:1;">
              <div class="font-weight-bold mb-1" style="font-size:0.76rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;"></div>
              <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:5px;">
                <?php foreach(['7','8','9','4','5','6','1','2','3'] as $n): ?>
                <button onclick="numpadPress('<?= $n ?>')" class="checkout-numpad-btn"
                  style="border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;font-weight:700;color:#1e293b;cursor:pointer;transition:background 0.1s;"
                  onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';"><?= $n ?></button>
                <?php endforeach; ?>
                <button onclick="numpadPress('000')" class="checkout-numpad-btn"
                  style="border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;font-weight:700;color:#1e293b;cursor:pointer;transition:background 0.1s;"
                  onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';">000</button>
                <button onclick="numpadPress('0')" class="checkout-numpad-btn"
                  style="border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;font-weight:700;color:#1e293b;cursor:pointer;transition:background 0.1s;"
                  onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';">0</button>
                <button onclick="numpadBackspace()" class="checkout-numpad-btn"
                  style="border-radius:8px;border:1.5px solid #fca5a5;background:#fff5f5;font-weight:700;color:#ef4444;cursor:pointer;transition:background 0.1s;"
                  onmouseover="this.style.background='#fee2e2';" onmouseout="this.style.background='#fff5f5';">
                  <i class="fas fa-backspace"></i>
                </button>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex" style="gap:8px;margin-top:4px;">
              <button type="button" class="btn btn-light font-weight-bold flex-fill py-2" data-dismiss="modal"
                style="border-radius:9px;border:1.5px solid #cbd5e1;color:#475569;font-size:0.86rem;">
                <i class="fas fa-times mr-1"></i> ຍົກເລີກ
              </button>
              <button type="button" class="btn font-weight-bold flex-fill py-2 text-white" id="btnSubmitPayment"
                onclick="processCheckout()"
                style="background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;border-radius:9px;font-size:0.90rem;box-shadow:0 4px 14px rgba(37,99,235,0.35);">
                <i class="fas fa-check-circle mr-1"></i> ຢືນຢັນຊຳລະ
              </button>
            </div>

          </div><!-- /RIGHT -->

        </div><!-- /grid -->
      </div><!-- /modal-body -->

    </div>
  </div>
</div>

<!-- Modal: Receipt Print -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
      <div class="modal-header bg-success text-white py-3 px-4">
        <h6 class="modal-title font-weight-bold"><i class="fas fa-receipt mr-1"></i> ໃບບິນຮັບເງິນສຳເລັດ</h6>
        <button type="button" class="close text-white" onclick="resetPOS()"><span>&times;</span></button>
      </div>
      <div class="modal-body p-3" id="receiptPrintArea" style="font-family:'Noto Sans Lao Looped', monospace, sans-serif;font-size:12px;color:#000;">
        <div class="text-center mb-2">
          <?php
            $logoName = !empty($company['img_url']) ? basename($company['img_url']) : 'logo.png';
            $logoPath = $base_path . 'assets/img/logo/' . $logoName;
          ?>
          <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo" class="receipt-logo"
            style="max-width:80px;height:auto;max-height:80px;object-fit:contain;margin:10px auto 2px auto;display:block;"
            onerror="this.onerror=null;this.src='<?php echo $base_path; ?>assets/img/logo/logo.png';">
          <h6 class="font-weight-bold mb-0" style="font-size:15px;color:#000;font-weight:700;"><?php echo htmlspecialchars($company['com_name_la']); ?></h6>
          <div class="receipt-header-address" style="font-size:12px;font-weight:600;color:#000;line-height:1.4;margin-top:2px;"><?php echo htmlspecialchars($company['com_address']); ?></div>
          <div class="receipt-header-tel" style="font-size:12px;font-weight:600;color:#000;line-height:1.4;">ໂທ: <?php echo htmlspecialchars($company['com_tel']); ?></div>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ເລກບິນ:</span><span id="rc_bill" style="font-weight:700;">-</span></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ວັນທີ:</span><span id="rc_date">-</span></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ຜູ້ຂາຍ:</span><span id="rc_cashier">-</span></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ລູກຄ້າ:</span><span id="rc_customer">ລູກຄ້າທົ່ວໄປ</span></div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <table style="width:100%;font-size:11.5px;color:#000;">
          <thead>
            <tr style="border-bottom:1px dashed #000;">
              <th style="text-align:left;padding-bottom:3px;font-weight:700;color:#000;">ລາຍການ</th>
              <th style="text-align:center;padding-bottom:3px;font-weight:700;width:50px;color:#000;">ຈຳນວນ</th>
              <th style="text-align:right;padding-bottom:3px;font-weight:700;width:75px;color:#000;">ລາຄາ</th>
            </tr>
          </thead>
          <tbody id="rc_items" style="color:#000;font-weight:600;"></tbody>
        </table>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ລວມ:</span><span id="rc_subtotal">0 ₭</span></div>
        <div class="d-flex justify-content-between" style="color:#000;font-weight:600;"><span>ສ່ວນຫຼຸດ:</span><span id="rc_discount">0 ₭</span></div>
        <div class="d-flex justify-content-between font-weight-bold" style="font-size:13.5px;color:#000;font-weight:700;"><span>ຍອດສຸດທິ:</span><span id="rc_total">0 ₭</span></div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div id="rc_payment_rows" style="color:#000;font-weight:600;"></div>
        <div class="d-flex justify-content-between font-weight-bold" style="color:#000;font-weight:700;"><span>ເງິນທອນ:</span><span id="rc_change">0 ₭</span></div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div class="text-center receipt-footer-msg" style="margin-top:20px !important; padding-top:10px; border-top:1px dashed #000; font-size:12.5px; font-weight:700; color:#000; text-align:center;"><?php echo htmlspecialchars(!empty($company['barcode']) ? $company['barcode'] : 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!'); ?></div>
      </div>
      <div class="modal-footer border-0 p-3 bg-light">
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold" onclick="resetPOS()">ປິດ</button>
        <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" onclick="printReceipt()"><i class="fas fa-print mr-1"></i> ພິມໃບບິນ</button>
      </div>
    </div>
  </div>
</div>
