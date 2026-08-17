<?php
// Component: Stock Transfer History JavaScript Logic
?>
<script>
var currentPage = 1;
var pageSize = 10;
var filteredRows = [];

$(document).ready(function() {
  // Run initial filter and pagination setup on page load
  filterTransferHistory();

  // Instant client-side search key binding
  $('input[name="search"]').on('keyup', function() {
    filterTransferHistory();
  });
});

function filterTransferHistory() {
  var query = $('input[name="search"]').val().trim().toLowerCase();
  var rows = document.querySelectorAll('#historyTableBody .transfer-row');
  filteredRows = [];

  rows.forEach(function(row) {
    var searchStr = row.getAttribute('data-search') || '';
    if (!query || searchStr.indexOf(query) !== -1) {
      filteredRows.push(row);
    } else {
      row.style.display = 'none';
    }
  });

  currentPage = 1;
  renderTransferTable();
}

function renderTransferTable() {
  var totalRows = filteredRows.length;
  var totalPages = Math.ceil(totalRows / pageSize) || 1;

  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  var startIdx = (currentPage - 1) * pageSize;
  var endIdx = startIdx + pageSize;

  // Hide all rows first
  document.querySelectorAll('#historyTableBody .transfer-row').forEach(function(r) {
    r.style.display = 'none';
  });

  // Show only paged rows
  for (var i = startIdx; i < endIdx && i < totalRows; i++) {
    var r = filteredRows[i];
    r.style.display = '';
    var cellIndex = r.querySelector('.row-index');
    if (cellIndex) cellIndex.textContent = i + 1;
  }

  // Update Page info description
  $('#page_info_start').text(totalRows > 0 ? startIdx + 1 : 0);
  $('#page_info_end').text(Math.min(endIdx, totalRows));
  $('#page_info_total').text(totalRows);

  renderTransferPagination(totalPages);
}

function renderTransferPagination(totalPages) {
  var container = $('#transferPagination');
  container.empty();

  if (totalPages < 1) totalPages = 1;

  var prevDisabled = (currentPage === 1) ? 'disabled' : '';
  container.append(
    '<li class="page-item ' + prevDisabled + '">' +
      '<a class="page-link" href="javascript:void(0)" onclick="goToTransferPage(' + (currentPage - 1) + ')">' +
        '<i class="fas fa-chevron-left"></i>' +
      '</a>' +
    '</li>'
  );

  var maxButtons = 5;
  var startPage = Math.max(1, currentPage - 2);
  var endPage = Math.min(totalPages, startPage + maxButtons - 1);
  if (endPage - startPage < maxButtons - 1) {
    startPage = Math.max(1, endPage - maxButtons + 1);
  }

  if (startPage > 1) {
    container.append('<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToTransferPage(1)">1</a></li>');
    if (startPage > 2) {
      container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
    }
  }

  for (var p = startPage; p <= endPage; p++) {
    var activeClass = (p === currentPage) ? 'active' : '';
    container.append(
      '<li class="page-item ' + activeClass + '">' +
        '<a class="page-link" href="javascript:void(0)" onclick="goToTransferPage(' + p + ')">' + p + '</a>' +
      '</li>'
    );
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
    }
    container.append('<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToTransferPage(' + totalPages + ')">' + totalPages + '</a></li>');
  }

  var nextDisabled = (currentPage === totalPages) ? 'disabled' : '';
  container.append(
    '<li class="page-item ' + nextDisabled + '">' +
      '<a class="page-link" href="javascript:void(0)" onclick="goToTransferPage(' + (currentPage + 1) + ')">' +
        '<i class="fas fa-chevron-right"></i>' +
      '</a>' +
    '</li>'
  );
}

function goToTransferPage(p) {
  currentPage = p;
  renderTransferTable();
}

function viewTransferDetails(transferId) {
  $.ajax({
    url: '../../api/stock_transfer_backend.php',
    type: 'GET',
    data: { action: 'get_transfer_details', transfer_id: transferId },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        var trf = res.transfer;
        $('#modalTransferCode').text(trf.transfer_code);
        $('#modalFromStore').text(trf.from_store_name);
        $('#modalToStore').text(trf.to_store_name);
        $('#modalDate').text(trf.transfer_date);
        $('#modalCreator').text(trf.creator_name || 'Admin');
        
        var statusHtml = '';
        if (trf.status === 'completed') {
          statusHtml = '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>ສຳເລັດ</span>';
        } else {
          statusHtml = '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>ຍົກເລີກ</span>';
        }
        $('#modalStatus').html(statusHtml);

        if (trf.notes && trf.notes.trim() !== '') {
          $('#modalNotesText').text(trf.notes);
          $('#modalNotesBox').removeClass('d-none');
        } else {
          $('#modalNotesBox').addClass('d-none');
        }

        // Render detail table
        var tbody = $('#modalDetailsTableBody');
        tbody.empty();
        res.details.forEach(function(item, idx) {
          tbody.append(
            '<tr>' +
              '<td class="text-center align-middle font-weight-bold text-muted">' + (idx + 1) + '</td>' +
              '<td class="align-middle"><strong>' + escapeHtml(item.product_name) + '</strong></td>' +
              '<td class="text-center align-middle font-weight-bold text-secondary">' + escapeHtml(item.barcode || '-') + '</td>' +
              '<td class="text-center align-middle font-weight-bold text-dark">' + item.qty + '</td>' +
              '<td class="text-center align-middle">' + escapeHtml(item.unit || 'ອັນ') + '</td>' +
            '</tr>'
          );
        });

        $('#transferDetailsModal').modal('show');
      } else {
        Swal.fire('ຜິດພາດ', res.message || 'ບໍ່ສາມາດໂຫຼດຂໍ້ມູນໄດ້', 'error');
      }
    },
    error: function() {
      Swal.fire('ຜິດພາດ', 'ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່', 'error');
    }
  });
}

function confirmCancelTransfer(transferId, transferCode) {
  Swal.fire({
    title: 'ຢືນຢັນການຍົກເລີກໃບໂອນ?',
    html: '<div style="font-size:0.9rem; color:#ef4444; font-weight:bold;">ເລກທີໃບໂອນ: ' + transferCode + '</div><div style="font-size:0.85rem; color:#475569; margin-top:5px;">ລະບົບຈະຫັກສະຕັອກຄືນຈາກສາຂາປາຍທາງ ແລະ ເພີ່ມຄືນໃຫ້ສາຂາຕົ້ນທາງ.</div>',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'ຢືນຢັນຍົກເລີກ',
    cancelButtonText: 'ປິດ'
  }).then(function(result) {
    if (result.isConfirmed) {
      $('#cancel_transfer_id_input').val(transferId);
      $('#cancelTransferForm').submit();
    }
  });
}

function escapeHtml(text) {
  if (!text) return '';
  return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
