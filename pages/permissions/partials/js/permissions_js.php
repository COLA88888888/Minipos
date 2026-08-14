<?php
/**
 * --------------------------------------------------------------------------
 * ຟາຍ Partials JavaScript: ຟັງຊັນຄວບຄຸມ UI ແລະ AJAX Requests
 * Path: pages/permissions/partials/js/permissions_js.php
 * --------------------------------------------------------------------------
 * ໜ້າທີ່:
 * - selectUser(userId): ສະຫຼັບສະແດງຂໍ້ມູນຕາຕະລາງສິດຂອງຜູ້ນຳໃຊ້ທີ່ເລືອກຢູ່ທາງຊ້າຍ
 * - filterUserList(): ຟັງຊັນ Live Search ຄົ້ນຫາຊື່ຜູ້ໃຊ້ ຫຼື ບົດບາດ ໃນລາຍຊື່ທາງຊ້າຍ
 * - toggleUserPerm(userId, perm, checkbox): ສົ່ງ AJAX ໄປອັບເດດສິດເອກະລາດ (ເປີດ/ປິດ ສະວິດ)
 * - applyRolePreset(userId, presetKey): ສົ່ງ AJAX ນຳໃຊ້ຮູບແບບສິດດ່ວນ (Cashier, Accountant, etc.)
 */
?>

<!-- JavaScript Logic ສຳລັບ Master-Detail & AJAX Interactions -->
<script>
/**
 * 1. ຟັງຊັນເລືອກຜູ້ນຳໃຊ້ (Master-Detail Selection)
 * ເມື່ອຜູ້ໃຊ້ຄລິກເລືອກບັນຊີຜູ້ໃຊ້ຢູ່ທາງຊ້າຍ:
 * - ໄຮໄລ້ (Active) Card ຜູ້ໃຊ້ນັ້ນ
 * - ສະແດງ Panel ຕາຕະລາງສິດ (Matrix Table) ຂອງຜູ້ໃຊ້ນັ້ນຢູ່ທາງຂວາ
 */
function selectUser(userId) {
  // ລົບ class active ຈາກທຸກບັນຊີ
  document.querySelectorAll('.user-item-btn').forEach(function(btn) {
    btn.classList.remove('active-user-item');
  });
  
  // ເພີ່ມ class active ໃຫ້ບັນຊີທີ່ຖືກເລືອກ
  var activeBtn = document.getElementById('user-item-' + userId);
  if (activeBtn) {
    activeBtn.classList.add('active-user-item');
  }

  // ຊ່ອນ Panel ຕາຕະລາງສິດຂອງທຸກຄົນ
  document.querySelectorAll('.user-detail-panel').forEach(function(panel) {
    panel.style.display = 'none';
  });
  
  // ສະແດງ Panel ຕາຕະລາງສິດຂອງຄົນທີ່ເລືອກ
  var targetPanel = document.getElementById('user-detail-' + userId);
  if (targetPanel) {
    targetPanel.style.display = 'flex';
  }
}

/**
 * 2. ຟັງຊັນ Live Search (ຄົ້ນຫາຊື່ຜູ້ໃຊ້ ຫຼື ບົດບາດ)
 * ທຳງານເມື່ອມີການພິມຂໍ້ຄວາມໃນຊ່ອງ Input ຄົ້ນຫາ
 */
function filterUserList() {
  var input = document.getElementById('userFilterInput');
  var filter = input.value.toLowerCase().trim();
  var items = document.querySelectorAll('.user-item-btn');
  var visibleCount = 0;

  items.forEach(function(item) {
    var username = item.getAttribute('data-username') || '';
    var status = item.getAttribute('data-status') || '';
    if (username.indexOf(filter) > -1 || status.indexOf(filter) > -1) {
      item.style.display = 'flex';
      visibleCount++;
    } else {
      item.style.display = 'none';
    }
  });

  // ອັບເດດ Badge ຈຳນວນບັນຊີທີ່ພົບ
  var badge = document.getElementById('totalUsersBadge');
  if (badge) {
    badge.innerText = visibleCount + ' ບັນຊີ';
  }
}

/**
 * 3. Helper SweetAlert Toast Notification
 */
var Toast = null;
function getToast() {
  if (!Toast && typeof Swal !== 'undefined') {
    Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true,
      didOpen: function(toast) {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });
  }
  return Toast;
}

/**
 * 4. ຟັງຊັນປ່ຽນສິດເອກະລາດ (Single Permission Toggle via AJAX)
 * ເມື່ອມີການກົດ ເປີດ/ປິດ ສະວິດໃນຕາຕະລາງ:
 * - ສົ່ງ AJAX POST ajax_action: 'toggle_perm' ໄປຫາ backend
 * - ອັບເດດ Badge ຈຳນວນສິດ (Perm Count Counter)
 */
function toggleUserPerm(userId, perm, checkbox) {
  var val = checkbox.checked ? 1 : 0;
  
  // Sync ທຸກສະວິດຂອງສິດດຽວກັນສຳລັບຜູ້ໃຊ້ນີ້ໃນຕາຕະລາງ
  document.querySelectorAll('[data-user-id="' + userId + '"][data-perm="' + perm + '"]').forEach(function(cb) {
    cb.checked = checkbox.checked;
  });

  $.ajax({
    url: window.location.href,
    type: 'POST',
    data: {
      ajax_action: 'toggle_perm',
      user_id: userId,
      perm: perm,
      val: val
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        var toast = getToast();
        if (toast) {
          toast.fire({
            icon: 'success',
            title: res.message
          });
        }
        // ອັບເດດປ້າຍນັບສິດ (Badge Counter)
        var countVal = document.getElementById('count-val-' + userId);
        if (countVal && res.perm_count !== undefined) {
          countVal.innerText = res.perm_count;
        }
      } else {
        // ຄືນຄ່າສະວິດ ຖ້າອັບເດດບໍ່ສຳເລັດ
        checkbox.checked = !checkbox.checked;
        document.querySelectorAll('[data-user-id="' + userId + '"][data-perm="' + perm + '"]').forEach(function(cb) {
          cb.checked = checkbox.checked;
        });
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'ແຈ້ງເຕືອນ',
            text: res.message || 'ບໍ່ສາມາດອັບເດດສິດໄດ້!'
          });
        }
      }
    },
    error: function() {
      checkbox.checked = !checkbox.checked;
      document.querySelectorAll('[data-user-id="' + userId + '"][data-perm="' + perm + '"]').forEach(function(cb) {
        cb.checked = checkbox.checked;
      });
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'ຜິດພາດ',
          text: 'ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່ກັບເຊີເວີ!'
        });
      }
    }
  });
}

/**
 * 5. ຟັງຊັນນຳໃຊ້ຮູບແບບສິດດ່ວນ (Apply Role Preset Template via AJAX)
 */
function applyRolePreset(userId, presetKey) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      title: 'ຢືນຢັນການກຳນົດສິດດ່ວນ?',
      text: "ລະບົບຈະອັບເດດສິດທຸກໂມດູນຂອງຜູ້ນຳໃຊ້ນີ້ຕາມຮູບແບບທີ່ເລືອກ!",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'ຢືນຢັນນຳໃຊ້',
      cancelButtonText: 'ຍົກເລີກ'
    }).then((result) => {
      if (result.isConfirmed) {
        executePreset(userId, presetKey);
      }
    });
  } else {
    if (confirm("ຢືນຢັນການກຳນົດສິດດ່ວນ?")) {
      executePreset(userId, presetKey);
    }
  }
}

/**
 * 6. Execute Preset AJAX Request
 */
function executePreset(userId, presetKey) {
  $.ajax({
    url: window.location.href,
    type: 'POST',
    data: {
      ajax_action: 'apply_preset',
      user_id: userId,
      preset: presetKey
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        var toast = getToast();
        if (toast) {
          toast.fire({
            icon: 'success',
            title: res.message
          });
        }
        var countVal = document.getElementById('count-val-' + userId);
        if (countVal && res.perm_count !== undefined) {
          countVal.innerText = res.perm_count;
        }

        // ອັບເດດສະຖານະສະວິດທຸກອັນໃນຕາຕະລາງຕາມຄ່າ Preset ໃໝ່
        if (res.permissions) {
          var p = res.permissions;
          for (var key in p) {
            var val = p[key];
            document.querySelectorAll('[data-user-id="' + userId + '"][data-perm="' + key + '"]').forEach(function(cb) {
              cb.checked = (val == 1);
            });
          }
        }
      } else {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'ແຈ້ງເຕືອນ',
            text: res.message || 'ບໍ່ສາມາດກຳນົດສິດດ່ວນໄດ້!'
          });
        }
      }
    },
    error: function() {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'ຜິດພາດ',
          text: 'ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່ກັບເຊີເວີ!'
        });
      }
    }
  });
}
</script>
