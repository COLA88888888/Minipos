<script>
$(document).ready(function() {
  if ($.fn.select2) {
    $('#category_id').select2({
      theme: 'bootstrap4',
      placeholder: '-- ເລືອກ ຫຼື ພິມຄົ້ນຫາໝວດໝູ່ --',
      allowClear: true,
      dropdownParent: $('#priceAdjModal')
    });
  }
  
  // Focus quick search input on modal open
  $('#priceAdjModal').on('shown.bs.modal', function() {
    $('#quick_search_code').val('').focus();
  });
});

function quickFindProduct(val) {
  val = $.trim(val).toLowerCase();
  if (!val) {
    $('#product_id_hidden').val('');
    $('#product_name_display').val('-- ລໍຖ້າສະແກນບາໂຄ້ດ/ພິມລະຫັດ --');
    $('#cur_bprice').val('');
    $('#cur_price').val('');
    return;
  }

  var foundOpt = null;
  $('#product_id option').each(function() {
    var pCode = String($(this).data('code') || '').toLowerCase();
    var pBarcode = String($(this).data('barcode') || '').toLowerCase();
    var pId = String($(this).data('id') || '').toLowerCase();

    if (pCode === val || pBarcode === val || pId === val) {
      foundOpt = this;
      return false; // Break loop
    }
  });

  if (foundOpt) {
    var pId = $(foundOpt).val();
    var pName = $(foundOpt).data('name') || '';
    var bprice = $(foundOpt).data('bprice') || '0';
    var price = $(foundOpt).data('price') || '0';

    $('#product_id_hidden').val(pId);
    $('#product_name_display').val(pName);
    $('#cur_bprice').val(Number(bprice).toLocaleString() + ' ₭');
    $('#cur_price').val(Number(price).toLocaleString() + ' ₭');
  } else {
    $('#product_id_hidden').val('');
    $('#product_name_display').val('❌ ບໍ່ພົບສິນຄ້ານີ້ໃນລະບົບ');
    $('#cur_bprice').val('');
    $('#cur_price').val('');
  }
}

function onTargetModeChange(mode) {
  if (mode === 'category') {
    $('#category_select_wrap').show();
    $('#product_scan_wrap').hide();
    $('#product_name_wrap').hide();
    $('#old_prices_display').hide();
    $('#category_id').prop('required', true);
    $('#product_id_hidden').prop('required', false);
  } else {
    $('#category_select_wrap').hide();
    $('#product_scan_wrap').show();
    $('#product_name_wrap').show();
    $('#old_prices_display').css('display', 'flex').show();
    $('#category_id').prop('required', false);
    $('#product_id_hidden').prop('required', true);
  }
}

function formatNumberInput(input) {
  var val = input.value.replace(/[^0-9.]/g, '');
  if (val === '') {
    input.value = '';
    return;
  }
  var parts = val.split('.');
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  if (parts.length > 2) {
    parts = [parts[0], parts.slice(1).join('')];
  }
  input.value = parts.join('.');
}

function deletePriceAdjLog(id, btn) {
  Swal.fire({
    title: 'ຢືນຢັນການລົບ?',
    text: 'ຕ້ອງການລົບປະຫວັດການປັບລາຄານີ້ແທ້ຫຼືບໍ່?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'ລົບເລີຍ',
    cancelButtonText: 'ຍົກເລີກ'
  }).then(function(r) {
    if (r.isConfirmed) {
      $.post('price_adjustment.php', { action: 'delete_log', adjust_id: id, is_ajax: 1 }, function(res) {
        if (res.success) {
          $(btn).closest('tr').fadeOut(300, function() { $(this).remove(); });
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'ລຶບປະຫວັດສຳເລັດ!',
            showConfirmButton: false,
            timer: 1500
          });
        } else {
          Swal.fire({ icon: 'error', title: 'ຜິດພາດ', text: res.message || 'ຜິດພາດ!' });
        }
      }, 'json');
    }
  });
}

function refreshPriceAdjTable() {
  var url = 'price_adjustment.php?fetch_table=1';
  var storeVal = $('select[name="store_id"]').val();
  if (storeVal) url += '&store_id=' + storeVal;
  
  $.get(url, function(html) {
    var $newContent = $(html);
    var $tableCard = $('.card.border-0.shadow-sm');
    if ($tableCard.length) {
      $tableCard.replaceWith($newContent);
    }
  });
}

$(document).ready(function() {
  $('#priceAdjForm').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var formData = $form.serialize() + '&is_ajax=1';
    $.post('price_adjustment.php', formData, function(res) {
      if (res.success) {
        $('#priceAdjModal').modal('hide');
        $form[0].reset();
        refreshPriceAdjTable();
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: res.message || 'ດຳເນີນການສຳເລັດ!',
          showConfirmButton: false,
          timer: 1500
        });
      } else {
        Swal.fire({ icon: 'error', title: 'ຜິດພາດ', text: res.message || 'ຜິດພາດ!' });
      }
    }, 'json');
  });
});
</script>
