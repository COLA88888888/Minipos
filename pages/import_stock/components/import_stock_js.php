<?php
// Component: JavaScript Logic for Import Stock Entry (import_stock.php)
?>
<script>
  var PRODUCTS_LIST = <?php echo json_encode($products); ?>;
  var PRODUCTS_UNITS = <?php echo json_encode($product_units_map); ?>;
  var selectedProduct = null;
  var cartItems = [];

  // ====== DIRECT ENTRY FORM JS LOGIC ======
  $(document).ready(function() {
    $('#direct_barcode_input').focus();
  });

  function onDirectBarcodeKeyDown(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      searchDirectProduct(true);
    }
  }

  function onDirectBarcodeChange() {
    searchDirectProduct(false);
  }

  function searchDirectProduct(isEnter) {
    var query = $('#direct_barcode_input').val().trim().toLowerCase();
    if (!query) {
      resetDirectSelection();
      return;
    }

    var exactMatch = null;
    var partialMatches = [];

    PRODUCTS_LIST.forEach(function(p) {
      var pBarcode = (p.barcode || "").toLowerCase();
      var pId = p.product_id.toString();
      var pName = (p.product_name || "").toLowerCase();

      var extraBarcodes = [];
      if (PRODUCTS_UNITS[p.product_id]) {
        PRODUCTS_UNITS[p.product_id].forEach(function(u) {
          if (u.barcode) extraBarcodes.push(u.barcode.toLowerCase());
        });
      }

      if (pBarcode === query || extraBarcodes.includes(query) || pId === query) {
        exactMatch = p;
      } else if (pBarcode.includes(query) || pName.includes(query)) {
        partialMatches.push(p);
      }
    });

    var targetProduct = exactMatch;
    if (!targetProduct && isEnter && partialMatches.length > 0) {
      targetProduct = partialMatches[0];
    }

    if (targetProduct) {
      setDirectSelectedProduct(targetProduct);
      if (isEnter) {
        $('#direct_qty').focus().select();
      }
    }
  }

  function selectProductFromModal(productId) {
    var p = PRODUCTS_LIST.find(function(item) { return item.product_id == productId; });
    if (p) {
      setDirectSelectedProduct(p);
      $('#productSelectModal').modal('hide');
      $('#direct_qty').focus().select();
    }
  }

  function setDirectSelectedProduct(p) {
    selectedProduct = p;
    $('#direct_matched_name').text(p.product_name);
    $('#direct_matched_barcode').text(p.barcode || '-');
    $('#direct_matched_bprice').text(parseFloat(p.bprice || 0).toLocaleString() + ' ₭');
    $('#direct_matched_stock').text(parseInt(p.qty || 0).toLocaleString());
    $('#direct_matched_banner').removeClass('d-none').addClass('d-flex');

    // Build units dropdown
    var unitSelect = document.getElementById("direct_unit_key");
    unitSelect.options.length = 0;
    var baseUnit = p.unit || "ອັນ";

    unitSelect.options[unitSelect.options.length] = new Option("ຍ່ອຍ (" + baseUnit + ")", "base");

    if (PRODUCTS_UNITS[p.product_id]) {
      PRODUCTS_UNITS[p.product_id].forEach(function(u) {
        var optText = u.unit_name + " (1 " + u.unit_name + " = " + u.multiplier + " " + baseUnit + ")";
        unitSelect.options[unitSelect.options.length] = new Option(optText, u.id);
      });
    }
  }

  function resetDirectSelection() {
    selectedProduct = null;
    $('#direct_barcode_input').val('');
    $('#direct_qty').val(1);
    $('#direct_expiry_date').val('');
    $('#direct_matched_banner').addClass('d-none').removeClass('d-flex');
    var unitSelect = document.getElementById("direct_unit_key");
    unitSelect.options.length = 0;
    unitSelect.options[unitSelect.options.length] = new Option("ຫົວໜ່ວຍຍ່ອຍ", "base");
  }

  function onQtyKeyDown(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      addCurrentItemToCart();
    }
  }

  function addCurrentItemToCart() {
    if (!selectedProduct) {
      Swal.fire({ icon: 'warning', title: 'ກະລຸນາເລືອກສິນຄ້າ', text: 'ກະລຸນາສະແກນບາໂຄ້ດ ຫຼື ກົດປຸ່ມ ເລືອກສິນຄ້າ ກ່ອນ!', confirmButtonColor: '#2563eb' });
      return;
    }

    var qty = intval($('#direct_qty').val() || 1);
    if (qty <= 0) {
      Swal.fire({ icon: 'warning', title: 'ຈຳນວນບໍ່ຖືກຕ້ອງ', text: 'ຈຳນວນຮັບເຂົ້າຕ້ອງຫຼາຍກວ່າ 0!', confirmButtonColor: '#2563eb' });
      return;
    }

    var unitKey = $('#direct_unit_key').val();
    var expiryDate = $('#direct_expiry_date').val();
    var baseUnit = selectedProduct.unit || 'ອັນ';
    var unitName = baseUnit;
    var multiplier = 1;
    var costPrice = parseFloat(selectedProduct.bprice || 0);

    if (unitKey !== 'base' && PRODUCTS_UNITS[selectedProduct.product_id]) {
      var foundU = PRODUCTS_UNITS[selectedProduct.product_id].find(function(u) { return u.id == unitKey; });
      if (foundU) {
        unitName = foundU.unit_name;
        multiplier = parseInt(foundU.multiplier || 1);
        if (foundU.bprice && parseFloat(foundU.bprice) > 0) {
          costPrice = parseFloat(foundU.bprice);
        }
      }
    }

    // Check if item already exists in cart
    var existingIdx = cartItems.findIndex(function(item) {
      return item.product_id == selectedProduct.product_id && item.unit_key == unitKey && item.expiry_date == expiryDate;
    });

    if (existingIdx >= 0) {
      cartItems[existingIdx].quantity += qty;
    } else {
      cartItems.push({
        product_id: selectedProduct.product_id,
        product_name: selectedProduct.product_name,
        barcode: selectedProduct.barcode || '-',
        base_unit: baseUnit,
        unit_key: unitKey,
        unit_name: unitName,
        multiplier: multiplier,
        quantity: qty,
        cost_price: costPrice,
        expiry_date: expiryDate
      });
    }

    resetDirectSelection();
    renderCartTable();
    $('#direct_barcode_input').focus();
  }

  // ====== LIVE CART TABLE PAGINATION ======
  var cartCurrentPage = 1;
  var cartPageSize = 10;

  function renderCartTable() {
    var tbody = $('#cart_table_body');
    tbody.empty();

    if (cartItems.length === 0) {
      tbody.html(`
        <tr id="empty_cart_row">
          <td colspan="6" class="text-center text-muted py-5">
            <i class="fas fa-box-open fa-3x d-block mb-2 text-muted" style="opacity: 0.4;"></i>
            <span class="font-weight-bold">ຍັງບໍ່ມີລາຍການສິນຄ້າໃນໃບບິນ</span><br>
            <small>ກະລຸນາສີດບາໂຄ້ດ ຫຼື ກົດປຸ່ມ "ເລືອກສິນຄ້າ" ເພື່ອເພີ່ມສິນຄ້າຮັບເຂົ້າ</small>
          </td>
        </tr>
      `);
      $('#cart_total_items').text(0);
      $('#cart_total_base').text(0);
      $('#cart_grand_total').text('0 ₭');
      $('#cart_page_start').text(0);
      $('#cart_page_end').text(0);
      $('#cart_page_total').text(0);
      $('#cart_json_input').val('[]');
      renderCartPagination(1);
      return;
    }

    var totalItems = cartItems.length;
    var grandTotalBase = 0;
    var grandTotalCost = 0;

    cartItems.forEach(function(item) {
      var itemBaseQty = item.quantity * item.multiplier;
      var itemTotalCost = item.quantity * item.cost_price;
      grandTotalBase += itemBaseQty;
      grandTotalCost += itemTotalCost;
    });

    $('#cart_total_items').text(totalItems);
    $('#cart_total_base').text(grandTotalBase.toLocaleString());
    $('#cart_grand_total').text(grandTotalCost.toLocaleString() + ' ₭');
    $('#cart_json_input').val(JSON.stringify(cartItems));

    // Pagination for cart items table
    var totalPages = Math.ceil(totalItems / cartPageSize) || 1;
    if (cartCurrentPage > totalPages) cartCurrentPage = totalPages;
    if (cartCurrentPage < 1) cartCurrentPage = 1;

    var startIdx = (cartCurrentPage - 1) * cartPageSize;
    var endIdx = Math.min(startIdx + cartPageSize, totalItems);

    for (var idx = startIdx; idx < endIdx; idx++) {
      var item = cartItems[idx];
      var itemTotalCost = item.quantity * item.cost_price;

      var rowHtml = `
        <tr>
          <td class="text-center align-middle font-weight-bold text-muted">${idx + 1}</td>
          <td class="align-middle">
            <span class="font-weight-bold text-dark d-block" style="font-size: 0.98rem;">${escapeHtml(item.product_name)}</span>
            <small class="text-muted"><i class="fas fa-barcode mr-1"></i> ${escapeHtml(item.barcode)} ${item.expiry_date ? '| ໝົດອາຍຸ: ' + item.expiry_date : ''}</small>
          </td>
          <td class="text-center align-middle">
            <div class="input-group input-group-sm mx-auto" style="max-width: 130px;">
              <input type="number" class="form-control text-center font-weight-bold" min="1" value="${item.quantity}" onchange="updateCartQty(${idx}, this.value)">
              <div class="input-group-append">
                <span class="input-group-text bg-light font-weight-bold text-dark">${escapeHtml(item.unit_name)}</span>
              </div>
            </div>
          </td>
          <td class="text-right align-middle font-weight-bold text-dark" style="font-size: 0.95rem;">
            ${item.cost_price.toLocaleString()} ₭
          </td>
          <td class="text-right align-middle font-weight-bold text-primary" style="font-size: 1.05rem;">
            ${itemTotalCost.toLocaleString()} ₭
          </td>
          <td class="text-center align-middle">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCartItem(${idx})" title="ລົບອອກ"><i class="fas fa-trash-alt"></i></button>
          </td>
        </tr>
      `;
      tbody.append(rowHtml);
    }

    $('#cart_page_start').text(startIdx + 1);
    $('#cart_page_end').text(endIdx);
    $('#cart_page_total').text(totalItems);

    renderCartPagination(totalPages);
  }

  function renderCartPagination(totalPages) {
    var container = $('#cartTablePagination');
    container.empty();

    if (totalPages < 1) totalPages = 1;

    var prevDisabled = (cartCurrentPage === 1) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToCartPage(${cartCurrentPage - 1})">
          <i class="fas fa-chevron-left"></i>
        </a>
      </li>
    `);

    var maxButtons = 5;
    var startPage = Math.max(1, cartCurrentPage - 2);
    var endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) {
      startPage = Math.max(1, endPage - maxButtons + 1);
    }

    if (startPage > 1) {
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToCartPage(1)">1</a></li>`);
      if (startPage > 2) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
    }

    for (var p = startPage; p <= endPage; p++) {
      var activeClass = (p === cartCurrentPage) ? 'active' : '';
      container.append(`
        <li class="page-item ${activeClass}">
          <a class="page-link" href="javascript:void(0)" onclick="goToCartPage(${p})">${p}</a>
        </li>
      `);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToCartPage(${totalPages})">${totalPages}</a></li>`);
    }

    var nextDisabled = (cartCurrentPage === totalPages) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToCartPage(${cartCurrentPage + 1})">
          <i class="fas fa-chevron-right"></i>
        </a>
      </li>
    `);
  }

  function goToCartPage(page) {
    cartCurrentPage = page;
    renderCartTable();
  }

  function updateCartQty(idx, val) {
    var q = intval(val || 1);
    if (q <= 0) q = 1;
    cartItems[idx].quantity = q;
    renderCartTable();
  }

  function removeCartItem(idx) {
    cartItems.splice(idx, 1);
    renderCartTable();
  }

  function submitDirectImportBill() {
    if (cartItems.length === 0) {
      Swal.fire({ icon: 'warning', title: 'ໃບບິນຫວ່າງເປົ່າ', text: 'ກະລຸນາເພີ່ມສິນຄ້າຮັບເຂົ້າຢ່າງນ້ອຍ 1 ລາຍການ!', confirmButtonColor: '#2563eb' });
      return;
    }

    Swal.fire({
      title: 'ຢືນຢັນການບັນທຶກຮັບເຂົ້າ?',
      text: "ລາຍການຮັບເຂົ້າທັງໝົດ " + cartItems.length + " ລາຍການ ຈະຖືກເພີ່ມເຂົ້າສະຕັອກຄັງສິນຄ້າ!",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#10b981',
      cancelButtonColor: '#64748b',
      confirmButtonText: '<i class="fas fa-save mr-1"></i> ບັນທຶກຮັບເຂົ້າ',
      cancelButtonText: 'ຍົກເລີກ'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#directImportForm').submit();
      }
    });
  }

  // ====== MODAL PAGINATION & SEARCH ======
  var modalCurrentPage = 1;
  var modalPageSize = 10;
  var modalFilteredRows = [];

  function filterModalProducts() {
    var q = $('#modal_product_search').val().trim().toLowerCase();
    var cat = $('#modal_category_filter').val();
    var rows = document.querySelectorAll('.modal-product-row');
    modalFilteredRows = [];

    rows.forEach(function(r) {
      var sData = r.getAttribute('data-search') || '';
      var rCat = r.getAttribute('data-category') || '';

      var matchSearch = !q || sData.includes(q);
      var matchCat = !cat || rCat === cat;

      if (matchSearch && matchCat) {
        modalFilteredRows.push(r);
      } else {
        r.style.display = 'none';
      }
    });

    modalCurrentPage = 1;
    renderModalProductTable();
  }

  function changeModalPageSize(val) {
    modalPageSize = parseInt(val, 10) || 10;
    modalCurrentPage = 1;
    renderModalProductTable();
  }

  function renderModalProductTable() {
    var totalRows = modalFilteredRows.length;
    var totalPages = Math.ceil(totalRows / modalPageSize) || 1;

    if (modalCurrentPage > totalPages) modalCurrentPage = totalPages;
    if (modalCurrentPage < 1) modalCurrentPage = 1;

    var startIdx = (modalCurrentPage - 1) * modalPageSize;
    var endIdx = startIdx + modalPageSize;

    document.querySelectorAll('.modal-product-row').forEach(function(r) {
      r.style.display = 'none';
    });

    for (var i = startIdx; i < endIdx && i < totalRows; i++) {
      var r = modalFilteredRows[i];
      r.style.display = '';
    }

    $('#modal_page_start').text(totalRows > 0 ? startIdx + 1 : 0);
    $('#modal_page_end').text(Math.min(endIdx, totalRows));
    $('#modal_page_total').text(totalRows);

    renderModalPagination(totalPages);
  }

  function renderModalPagination(totalPages) {
    var container = $('#modalProductPagination');
    container.empty();

    if (totalPages < 1) totalPages = 1;

    var prevDisabled = (modalCurrentPage === 1) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${prevDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToModalPage(${modalCurrentPage - 1})">
          <i class="fas fa-chevron-left"></i>
        </a>
      </li>
    `);

    var maxButtons = 5;
    var startPage = Math.max(1, modalCurrentPage - 2);
    var endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) {
      startPage = Math.max(1, endPage - maxButtons + 1);
    }

    if (startPage > 1) {
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToModalPage(1)">1</a></li>`);
      if (startPage > 2) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
    }

    for (var p = startPage; p <= endPage; p++) {
      var activeClass = (p === modalCurrentPage) ? 'active' : '';
      container.append(`
        <li class="page-item ${activeClass}">
          <a class="page-link" href="javascript:void(0)" onclick="goToModalPage(${p})">${p}</a>
        </li>
      `);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
      }
      container.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToModalPage(${totalPages})">${totalPages}</a></li>`);
    }

    var nextDisabled = (modalCurrentPage === totalPages) ? 'disabled' : '';
    container.append(`
      <li class="page-item ${nextDisabled}">
        <a class="page-link" href="javascript:void(0)" onclick="goToModalPage(${modalCurrentPage + 1})">
          <i class="fas fa-chevron-right"></i>
        </a>
      </li>
    `);
  }

  function goToModalPage(page) {
    modalCurrentPage = page;
    renderModalProductTable();
  }

  $('#productSelectModal').on('shown.bs.modal', function () {
    filterModalProducts();
    $('#modal_product_search').focus();
  });

  function intval(val) {
    var parsed = parseInt(val, 10);
    return isNaN(parsed) ? 0 : parsed;
  }

  function escapeHtml(text) {
    return String(text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
</script>
