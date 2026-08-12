<script>
// --- BARCODE SCANNER, SEARCH & CATEGORY FILTERING ---
var selectedCategoryPillId = '';

function playPosBeep() {
  try {
    var ctx = new (window.AudioContext || window.webkitAudioContext)();
    var osc = ctx.createOscillator();
    var gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(1200, ctx.currentTime);
    gain.gain.setValueAtTime(0.15, ctx.currentTime);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start();
    osc.stop(ctx.currentTime + 0.08);
  } catch(e) {}
}

$('#barcodeInput').on('keypress', function(e) {
  if (e.which === 13) {
    e.preventDefault();
    handleBarcodeSubmit();
  }
});

function handleBarcodeSubmit() {
  var rawCode = $('#barcodeInput').val().trim();
  $('#barcodeInput').val('').focus();
  if (!rawCode) return;
  
  findAndAddByBarcode(rawCode);
  filterGrid();
}

function findAndAddByBarcode(code) {
  var cleanCode = code.toLowerCase();
  var matchedProduct = null;
  var matchedUnit = null;

  for (var i = 0; i < allProducts.length; i++) {
    var p = allProducts[i];
    if (p.barcode && p.barcode.toLowerCase() === cleanCode) {
      matchedProduct = p;
      matchedUnit = { unit_name: p.unit || 'ອັນ', multiplier: 1, price: p.price, bprice: p.bprice };
      break;
    }
    if (String(p.product_id) === cleanCode) {
      matchedProduct = p;
      matchedUnit = { unit_name: p.unit || 'ອັນ', multiplier: 1, price: p.price, bprice: p.bprice };
      break;
    }
    if (p.extra_units && p.extra_units.length > 0) {
      for (var j = 0; j < p.extra_units.length; j++) {
        var eu = p.extra_units[j];
        if (eu.barcode && eu.barcode.toLowerCase() === cleanCode) {
          matchedProduct = p;
          matchedUnit = eu;
          break;
        }
      }
      if (matchedProduct) break;
    }
  }

  if (!matchedProduct) {
    for (var i = 0; i < allProducts.length; i++) {
      var p = allProducts[i];
      if (p.product_name && p.product_name.toLowerCase().indexOf(cleanCode) !== -1) {
        matchedProduct = p;
        matchedUnit = { unit_name: p.unit || 'ອັນ', multiplier: 1, price: p.price, bprice: p.bprice };
        break;
      }
    }
  }

  if (matchedProduct) {
    playPosBeep();
    addProductToCart(matchedProduct, matchedUnit);
  } else {
    Swal.fire({
      icon: 'warning',
      title: 'ບໍ່ພົບສິນຄ້າ!',
      text: 'ລະຫັດບາໂຄ້ດ: ' + code,
      timer: 1500,
      showConfirmButton: false
    });
  }
}

function addProductToCart(product, specificUnit) {
  // 1. ກວດ stock ກ່ອນທຸກຢ່າງ — ຖ້າໝົດແລ້ວ ສະແດງ alert "ສິນຄ້າໝົດແລ້ວ!" ທັນທີ (ບໍ່ໃຫ້ເຕືອນເປີດບິນ)
  var cutQty = getProductCutQty(product.product_id);
  var remaining = getProductRemainingStock(product.product_id);
  var isOutOfStock = (cutQty !== 0 && remaining <= 0) || (parseFloat(product.qty || 0) <= 0 && cutQty !== 0);

  if (isOutOfStock) {
    Swal.fire({
      icon: 'error',
      title: 'ສິນຄ້າໝົດແລ້ວ!',
      html: '<div style="font-size:1.05rem; font-weight:700; color:#ef4444; margin-bottom:6px;">ສິນຄ້າ "' + product.product_name + '"</div>' +
            '<div class="text-dark font-weight-bold" style="font-size:0.92rem;">ບໍ່ສາມາດກົດຂາຍໄດ້ ເນື່ອງຈາກຈຳນວນສິນຄ້າໃນຄັງເປັນ 0</div>' +
            '<small class="text-muted d-block mt-2">ກະລຸນາເພີ່ມສະຕັອກ ຫຼື ເລືອກສິນຄ້າອື່ນ</small>',
      confirmButtonText: '<i class="fas fa-check mr-1"></i> ຕົກລົງ',
      confirmButtonColor: '#ef4444',
      customClass: {
        confirmButton: 'btn btn-danger font-weight-bold px-4'
      }
    });
    return;
  }

  // 2. ຖ້າສິນຄ້າມີໃນສາງ — ກວດສອບວ່າເປີດບິນແລ້ວຫຼືບໍ່
  if (!checkBillOpenedOrAlert()) return;

  var allUnits = buildAvailableUnits(product);

  // ຖ້າລະບຸ specificUnit ມາແລ້ວ ຫຼື ມີຫົວໜ່ວຍດຽວ — ເພີ່ມເລີຍ
  if (specificUnit || allUnits.length <= 1) {
    _doAddToCart(product, specificUnit || null);
    return;
  }

  // ຖ້າມີຫຼາຍຫົວໜ່ວຍ — popup ໃຫ້ເລືອກ
  var htmlOptions = allUnits.map(function(u) {
    return `<button class="unit-pick-btn" data-unit='${JSON.stringify(u)}'
      style="display:flex; align-items:center; justify-content:space-between; width:100%; margin-bottom:8px; padding:10px 16px; border:1.5px solid #e2e8f0; border-radius:10px; background:#fff; cursor:pointer; transition:all 0.15s ease; font-family:inherit;"
      onmouseover="this.style.borderColor='#2563eb'; this.style.background='#eff6ff';"
      onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#fff';">
      <span style="font-size:0.95rem; font-weight:700; color:#1e293b;">${u.unit_name}</span>
      <span style="font-size:1.0rem; font-weight:800; color:#16a34a;">${Number(u.price).toLocaleString()} ₭</span>
    </button>`;
  }).join('');

  Swal.fire({
    title: '<span style="font-size:1.05rem; color:#1e293b;">ເລືອກຫົວໜ່ວຍ</span>',
    html: `<div style="text-align:left; margin-bottom:6px; font-size:0.88rem; color:#64748b; font-weight:600;">${product.product_name}</div>
           <div style="margin-top:10px;">${htmlOptions}</div>`,
    showConfirmButton: false,
    showCloseButton: true,
    didOpen: function() {
      $('.unit-pick-btn').on('click', function() {
        var uObj = $(this).data('unit');
        Swal.close();
        _doAddToCart(product, uObj);
      });
    }
  });
}


function selectCategoryPill(btn, catId) {
  $('.category-pill-btn').removeClass('active');
  $(btn).addClass('active');
  selectedCategoryPillId = String(catId);
  filterGrid();
}

function filterGrid() {
  var q = ($('#barcodeInput').val() || '').toLowerCase().trim();
  var catId = selectedCategoryPillId;

  $('.product-item-card').each(function() {
    var name = $(this).attr('data-name') || '';
    var barcode = $(this).attr('data-barcode') || '';
    var pid = $(this).attr('data-id') || '';
    var cat = $(this).attr('data-category') || '';

    var matchText = (name.indexOf(q) !== -1) || (barcode.indexOf(q) !== -1) || (pid.indexOf(q) !== -1);
    var matchCat = (!catId) || (cat == catId);

    if (matchText && matchCat) {
      $(this).show();
    } else {
      $(this).hide();
    }
  });
}
</script>
