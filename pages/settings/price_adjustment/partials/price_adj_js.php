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
</script>
