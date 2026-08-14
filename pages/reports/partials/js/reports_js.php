<script>
function viewBillDetails(billNo) {
  if (!billNo) return;

  $('#modal_bill_no').text(billNo);
  $('#modal_items_body').empty();
  $('#reportBillModal').modal('show');

  // Smart Slow Network Fallback: Only show loading if server takes > 600ms
  var slowNetTimer = setTimeout(function() {
    $('#modal_items_body').html('<tr><td colspan="4" class="text-center py-3 text-muted" style="font-size:0.9rem;"><i class="fas fa-wifi text-warning mr-1"></i> ກຳລັງເຊື່ອມຕໍ່ເຊີບເວີ...</td></tr>');
  }, 600);

  $.ajax({
    url: 'reports.php',
    type: 'GET',
    data: { action: 'get_bill_details', bill_no: billNo },
    dataType: 'json',
    success: function(res) {
      clearTimeout(slowNetTimer);
      if (res.success && res.bill) {
        var b = res.bill;
        $('#modal_date').text(b.sale_date + ' ' + b.sale_time);
        $('#modal_cashier').text(b.user_receive || 'Admin');
        $('#modal_customer').text(b.customer_name || 'ລູກຄ້າທົ່ວໄປ');

        if (b.bank_name) {
          $('#modal_bank_wrapper').show();
          $('#modal_bank_name').text(b.bank_name);
        } else {
          $('#modal_bank_wrapper').hide();
        }

        var subtotal = parseFloat(b.sale_amount || 0);
        var discount = parseFloat(b.sale_discount_bill || 0);
        var net      = parseFloat(b.sale_barlance || 0);
        var cash     = parseFloat(b.cash_received || (b.sale_pay || 0));
        var qr       = parseFloat(b.qr_received || 0);
        var change   = parseFloat(b.sale_return || 0);

        $('#modal_subtotal').text(subtotal.toLocaleString() + ' ₭');
        $('#modal_discount').text(discount.toLocaleString() + ' ₭');
        $('#modal_net_total').text(net.toLocaleString() + ' ₭');
        $('#modal_cash_rec').text(cash.toLocaleString() + ' ₭');
        $('#modal_qr_rec').text(qr.toLocaleString() + ' ₭');
        $('#modal_change').text(change.toLocaleString() + ' ₭');

        var tbody = $('#modal_items_body');
        tbody.empty();

        if (res.details && res.details.length > 0) {
          $.each(res.details, function(idx, item) {
            var q = floatval(item.save_qty);
            var p = floatval(item.save_price);
            var total = floatval(item.save_money || (q * p));

            tbody.append(`
              <tr>
                <td class="text-left font-weight-bold">${item.save_proname || item.product_name || 'ສິນຄ້າ'}</td>
                <td class="text-center">${q.toLocaleString()}</td>
                <td class="text-right">${p.toLocaleString()} ₭</td>
                <td class="text-right font-weight-bold">${total.toLocaleString()} ₭</td>
              </tr>
            `);
          });
        } else {
          tbody.html('<tr><td colspan="4" class="text-center text-muted">ບໍ່ມີລາຍການສິນຄ້າ</td></tr>');
        }
      } else {
        $('#modal_items_body').html('<tr><td colspan="4" class="text-center text-danger">ບໍ່ສາມາດໂຫຼດຂໍ້ມູນບິນໄດ້!</td></tr>');
      }
    },
    error: function() {
      clearTimeout(slowNetTimer);
      $('#modal_items_body').html('<tr><td colspan="4" class="text-center text-danger">ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່!</td></tr>');
    }
  });
}

function printBill(billNo) {
  if (!billNo) return;

  $.ajax({
    url: 'reports.php',
    type: 'GET',
    data: { action: 'get_bill_details', bill_no: billNo },
    dataType: 'json',
    success: function(res) {
      if (res.success && res.bill) {
        var b = res.bill;
        $('#rc_rep_bill').text(b.sale_save_bill || billNo);
        $('#rc_rep_date').text((b.sale_date || '') + ' ' + (b.sale_time || ''));
        $('#rc_rep_cashier').text(b.user_receive || 'Admin');
        $('#rc_rep_customer').text(b.customer_name || 'ລູກຄ້າທົ່ວໄປ');

        var subtotal = parseFloat(b.sale_amount || 0);
        var discount = parseFloat(b.sale_discount_bill || 0);
        var net      = parseFloat(b.sale_barlance || 0);
        var cash     = parseFloat(b.cash_received || 0);
        var qr       = parseFloat(b.qr_received || 0);
        var change   = parseFloat(b.sale_return || 0);
        var typePay  = b.type_pay || b.payment_type || '';

        if (cash === 0 && qr === 0) {
          if (typePay.indexOf('ເງິນສົດ') !== -1) {
            cash = parseFloat(b.sale_pay || b.sale_barlance || 0);
          }
          if (typePay.indexOf('ໂອນ') !== -1 || typePay.indexOf('QR') !== -1) {
            qr = parseFloat(b.sale_barlance || 0);
          }
        }

        $('#rc_rep_subtotal').text(subtotal.toLocaleString() + ' ₭');
        $('#rc_rep_discount').text(discount.toLocaleString() + ' ₭');
        $('#rc_rep_total').text(net.toLocaleString() + ' ₭');
        $('#rc_rep_change').text(change.toLocaleString() + ' ₭');

        var payHtml = '';
        payHtml += '<div class="d-flex justify-content-between"><span>ຮັບເງິນສົດ:</span><span>' + cash.toLocaleString() + ' ₭</span></div>';
        payHtml += '<div class="d-flex justify-content-between"><span>ຮັບເງິນໂອນ:</span><span>' + qr.toLocaleString() + ' ₭</span></div>';
        if (b.bank_name && qr > 0) {
          payHtml += '<div class="d-flex justify-content-between" style="font-weight:700;"><span>ທະນາຄານໂອນ:</span><span>' + b.bank_name + '</span></div>';
        }
        $('#rc_rep_payment_rows').html(payHtml);

        var tbody = $('#rc_rep_items');
        tbody.empty();

        if (res.details && res.details.length > 0) {
          $.each(res.details, function(idx, item) {
            var q = floatval(item.save_qty);
            var p = floatval(item.save_price);
            var total = floatval(item.save_money || (q * p));

            tbody.append(`
              <tr>
                <td style="text-align:left;padding:3px 0;">${item.save_proname || item.product_name || 'ສິນຄ້າ'}</td>
                <td style="text-align:center;padding:3px 0;">${q.toLocaleString()}</td>
                <td style="text-align:right;padding:3px 0;">${total.toLocaleString()} ₭</td>
              </tr>
            `);
          });
        }

        doPrintReportReceipt();
      } else {
        alert('ບໍ່ສາມາດໂຫຼດຂໍ້ມູນບິນໄດ້!');
      }
    },
    error: function() {
      alert('ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່!');
    }
  });
}

function doPrintReportReceipt() {
  var printContent = document.getElementById("reportReceiptPrintArea").innerHTML;
  var iframe = document.getElementById("reportPrintIframe");
  if (!iframe) {
    iframe = document.createElement('iframe');
    iframe.id = 'reportPrintIframe';
    iframe.style.cssText = 'position:absolute;width:0px;height:0px;left:-9999px;top:-9999px;border:none;';
    document.body.appendChild(iframe);
  }
  var iframeDoc = iframe.contentWindow || iframe.contentDocument;
  if (iframeDoc.document) iframeDoc = iframeDoc.document;

  iframeDoc.open();
  iframeDoc.write(`<!DOCTYPE html><html><head>
    <meta charset="utf-8">
    <title>ໃບບິນຮັບເງິນ (POS)</title>
    <link rel="stylesheet" href="../../assets/css/local-font.css">
    <style>
      @page { size: 80mm auto; margin: 0mm; }
      * { box-sizing: border-box; font-family: 'Noto Sans Lao Looped', 'Phetsarath OT', Arial, sans-serif !important; color: #000 !important; }
      html, body { width: 80mm; margin: 0 auto; padding: 8px 6px; background: #fff; color: #000 !important; font-size: 12px; line-height: 1.4; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .text-center { text-align: center !important; } .text-right { text-align: right !important; }
      .font-weight-bold { font-weight: 700 !important; color: #000 !important; } 
      .small { font-size: 11.5px !important; color: #000 !important; font-weight: 600 !important; }
      .d-flex { display: flex !important; } .justify-content-between { justify-content: space-between !important; }
      .mb-0{margin-bottom:0!important}.mb-1{margin-bottom:4px!important}.mb-2{margin-bottom:8px!important}
      .mt-2{margin-top:8px!important} 
      .text-muted { color: #000 !important; font-weight: 600 !important; }
      .receipt-header-address, .receipt-header-tel { font-size: 12px !important; font-weight: 600 !important; color: #000 !important; line-height: 1.4 !important; }
      .receipt-footer-msg { font-size: 12.5px !important; font-weight: 700 !important; color: #000 !important; border-top: 1px dashed #000 !important; margin-top: 10px !important; padding-top: 8px !important; text-align: center !important; }
      img.receipt-logo { max-width:80px!important; max-height:80px!important; height:auto!important; display:block!important; margin:10px auto 2px auto!important; object-fit:contain!important; }
      img.receipt-qr-img { max-width:100px!important; max-height:100px!important; height:auto!important; display:block!important; margin:6px auto 2px auto!important; object-fit:contain!important; }
      table { width:100%; border-collapse:collapse; margin:4px 0; font-size:11.5px; color: #000 !important; }
      td,th { padding:3px 0; vertical-align:top; color: #000 !important; font-weight: 600 !important; }
      th { font-weight: 700 !important; }
      @media print { 
        html,body { width:100%; margin:0; padding:2mm; color: #000 !important; } 
        * { color: #000 !important; }
      }
    </style>
  </head><body>${printContent}</body></html>`);
  iframeDoc.close();

  var images = iframeDoc.getElementsByTagName('img');
  var totalImages = images.length;
  var loadedCount = 0;
  var printTriggered = false;

  function doTriggerPrint() {
    if (printTriggered) return;
    printTriggered = true;
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
  }

  if (totalImages === 0) {
    setTimeout(doTriggerPrint, 150);
  } else {
    for (var i = 0; i < totalImages; i++) {
      if (images[i].complete && images[i].naturalWidth !== 0) {
        loadedCount++;
      } else {
        images[i].onload = images[i].onerror = function() {
          loadedCount++;
          if (loadedCount >= totalImages) {
            setTimeout(doTriggerPrint, 100);
          }
        };
      }
    }
    if (loadedCount >= totalImages) {
      setTimeout(doTriggerPrint, 150);
    } else {
      setTimeout(doTriggerPrint, 500);
    }
  }
}

function floatval(val) {
  var n = parseFloat(val);
  return isNaN(n) ? 0 : n;
}

function changePerPage(val) {
  var url = new URL(window.location.href);
  url.searchParams.set('per_page', val);
  url.searchParams.set('page', 1);
  window.location.href = url.toString();
}

function deleteBill(billNo) {
  if (!billNo) return;

  var doDelete = function() {
    $.ajax({
      url: 'reports.php',
      type: 'POST',
      data: { action: 'delete_bill', bill_no: billNo },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: 'ລົບບິນສຳເລັດ!',
              text: res.message,
              timer: 1800,
              showConfirmButton: false
            }).then(function() {
              window.location.reload();
            });
          } else {
            alert(res.message);
            window.location.reload();
          }
        } else {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'ເກີດຂໍ້ຜິດພາດ!', text: res.message });
          } else {
            alert('ເກີດຂໍ້ຜິດພາດ: ' + res.message);
          }
        }
      },
      error: function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'ເກີດຂໍ້ຜິດພາດ!', text: 'ບໍ່ສາມາດເຊື່ອມຕໍ່ກັບເຊີເວີໄດ້!' });
        } else {
          alert('ບໍ່ສາມາດເຊື່ອມຕໍ່ກັບເຊີເວີໄດ້!');
        }
      }
    });
  };

  if (typeof Swal !== 'undefined') {
    Swal.fire({
      title: 'ຢືນຢັນການລົບບິນຂາຍ?',
      html: 'ທ່ານຕ້ອງການລົບບິນເລກທີ <strong class="text-danger">' + billNo + '</strong> ແທ້ບໍ?<br><small class="text-muted">ສະຕັອກສິນຄ້າທັງໝົດໃນບິນນີ້ຈະຖືກຄືນເຂົ້າຄັງອັດໂຕໂນມັດ!</small>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#64748b',
      confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> ຢືນຢັນລົບ',
      cancelButtonText: 'ຍົກເລີກ'
    }).then(function(result) {
      if (result.isConfirmed) {
        doDelete();
      }
    });
  } else {
    if (confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລົບບິນເລກທີ ' + billNo + ' ?\nສະຕັອກສິນຄ້າທັງໝົດໃນບິນນີ້ຈະຖືກຄືນເຂົ້າຄັງອັດໂຕໂນມັດ!')) {
      doDelete();
    }
  }
}

$(document).ready(function() {
  $('#reportSearchInput').on('keyup input search', function() {
    var val = $.trim($(this).val()).toLowerCase();
    var $rows = $('.report-table tbody tr:not(#noSearchResultRow)');

    if (val === '') {
      $rows.show();
      $('#noSearchResultRow').remove();
      return;
    }

    var visibleCount = 0;
    $rows.each(function() {
      var text = $(this).text().toLowerCase();
      if (text.indexOf(val) > -1) {
        $(this).show();
        visibleCount++;
      } else {
        $(this).hide();
      }
    });

    $('#noSearchResultRow').remove();
    if (visibleCount === 0 && $rows.length > 0) {
      var colCount = $('.report-table thead th').length || 12;
      $('.report-table tbody').append(
        '<tr id="noSearchResultRow"><td colspan="' + colCount + '" class="text-center py-4 text-muted font-weight-bold">ບໍ່ພົບຂໍ້ມູນທີ່ຕົງກັບຄຳຄົ້ນຫາ "' + $('<div>').text(val).html() + '"</td></tr>'
      );
    }
  });
});

// Helper to auto-trigger export after loading all records
function checkAutoExport() {
  var urlParams = new URLSearchParams(window.location.search);
  var autoAction = urlParams.get('auto_export');
  if (autoAction === 'excel') {
    window.history.replaceState({}, document.title, window.location.pathname + '?' + removeParam(window.location.search, 'auto_export'));
    setTimeout(function() { doExportExcel(); }, 300);
  } else if (autoAction === 'pdf') {
    window.history.replaceState({}, document.title, window.location.pathname + '?' + removeParam(window.location.search, 'auto_export'));
    setTimeout(function() { doExportPDF(); }, 300);
  } else if (autoAction === 'print') {
    window.history.replaceState({}, document.title, window.location.pathname + '?' + removeParam(window.location.search, 'auto_export'));
    setTimeout(function() { window.print(); }, 300);
  }
}

function removeParam(queryString, param) {
  var params = new URLSearchParams(queryString);
  params.delete(param);
  return params.toString();
}

function printReportTable(mode) {
  var urlParams = new URLSearchParams(window.location.search);
  if (mode === 'all' && urlParams.get('per_page') !== 'all') {
    urlParams.set('per_page', 'all');
    urlParams.set('auto_export', 'print');
    window.location.href = window.location.pathname + '?' + urlParams.toString();
    return;
  }
  $('#global-preloader').hide();
  window.print();
}

function exportReportExcel(mode) {
  var urlParams = new URLSearchParams(window.location.search);
  if (mode === 'all' && urlParams.get('per_page') !== 'all') {
    urlParams.set('per_page', 'all');
    urlParams.set('auto_export', 'excel');
    window.location.href = window.location.pathname + '?' + urlParams.toString();
    return;
  }
  doExportExcel();
}

function doExportExcel() {
  var table = document.querySelector('.report-table') || document.querySelector('table');
  if (!table) return;

  var cloneTable = table.cloneNode(true);
  
  var noPrints = cloneTable.querySelectorAll('.no-print');
  noPrints.forEach(function(el) { el.remove(); });

  var links = cloneTable.querySelectorAll('a');
  links.forEach(function(a) {
    var textNode = document.createTextNode(a.textContent);
    a.parentNode.replaceChild(textNode, a);
  });

  var images = cloneTable.querySelectorAll('img');
  images.forEach(function(img) {
    var title = img.getAttribute('title') || img.getAttribute('alt') || '';
    if (title && img.parentNode) {
      var textNode = document.createTextNode(title);
      img.parentNode.replaceChild(textNode, img);
    } else if (img.parentNode) {
      img.remove();
    }
  });

  var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
  html += '<head><meta charset="utf-8"><style>';
  html += '* { font-family: "Phetsarath OT", "Noto Sans Lao", "Saysettha OT", sans-serif !important; }';
  html += 'table { border-collapse: collapse; width: 100%; font-family: "Phetsarath OT", "Noto Sans Lao", sans-serif; }';
  html += 'th, td { border: 1px solid #cbd5e1; padding: 10px 12px; font-family: "Phetsarath OT", "Noto Sans Lao", sans-serif; vertical-align: middle; }';
  html += 'th { background-color: #f1f5f9; font-weight: bold; text-align: center !important; }';
  html += '.text-right { text-align: right !important; }';
  html += '.text-center { text-align: center !important; }';
  html += '.text-left { text-align: left !important; }';
  html += 'h2 { text-align: center !important; font-family: "Phetsarath OT", "Noto Sans Lao", sans-serif; font-size: 18px; margin: 15px 0; }';
  html += '</style></head><body>';
  html += '<h2>ລາຍງານການຂາຍທັງໝົດ</h2>';
  html += cloneTable.outerHTML;
  html += '</body></html>';

  var blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel;charset=utf-8' });
  var url = URL.createObjectURL(blob);
  var a = document.createElement('a');
  a.href = url;
  a.download = 'sales_report_' + new Date().toISOString().slice(0, 10) + '.xls';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function exportReportPDF(mode) {
  var urlParams = new URLSearchParams(window.location.search);
  if (mode === 'all' && urlParams.get('per_page') !== 'all') {
    urlParams.set('per_page', 'all');
    urlParams.set('auto_export', 'pdf');
    window.location.href = window.location.pathname + '?' + urlParams.toString();
    return;
  }
  doExportPDF();
}

function doExportPDF() {
  var table = document.querySelector('.report-table') || document.querySelector('table');
  if (!table) return;

  var fileName = 'sales_report_' + new Date().toISOString().slice(0, 10) + '.pdf';

  if (typeof html2pdf !== 'undefined') {
    var wrapper = document.createElement('div');
    wrapper.style.padding = '15px';
    wrapper.style.fontFamily = "'Noto Sans Lao Looped', 'Phetsarath OT', sans-serif";
    
    var title = document.createElement('h3');
    title.innerText = 'ລາຍງານການຂາຍທັງໝົດ';
    title.style.textAlign = 'center';
    title.style.marginBottom = '15px';
    title.style.fontFamily = "'Noto Sans Lao Looped', 'Phetsarath OT', sans-serif";
    
    var cloneTable = table.cloneNode(true);
    cloneTable.style.width = '100%';
    cloneTable.style.borderCollapse = 'collapse';
    cloneTable.style.fontSize = '11px';

    var noPrints = cloneTable.querySelectorAll('.no-print');
    noPrints.forEach(function(el) { el.remove(); });

    wrapper.appendChild(title);
    wrapper.appendChild(cloneTable);

    var opt = {
      margin:       10,
      filename:     fileName,
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(wrapper).save();
  } else {
    var originalTitle = document.title;
    document.title = fileName;
    window.print();
    setTimeout(function() {
      document.title = originalTitle;
    }, 1000);
  }
}

$(document).ready(function() {
  checkAutoExport();
});
</script>
