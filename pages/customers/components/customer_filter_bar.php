<!-- Component: Customer Filter Bar (Page Size, Date Range, Member Search) -->
<div class="customer-card-header p-3 bg-white border-bottom" style="border-radius: 14px 14px 0 0;">
  <div class="row align-items-center" style="row-gap: 12px;">

    <!-- Left: Page Size Selector -->
    <div class="col-auto">
      <div class="d-flex align-items-center" style="gap: 6px;">
        <select id="pageSizeSelect" class="form-control" style="width: 80px; height: 38px; font-size: 0.9rem; border-radius: 8px; border: 1px solid #cbd5e1;" onchange="changePageSize()">
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
          <option value="all">ທັງໝົດ</option>
        </select>
      </div>
    </div>

    <!-- Middle: Date Range Search (ບັອກຄົ້ນຫາ ວັນທີ ຫາ ວັນທີ) -->
    <div class="col-lg-5 col-md-6 col-12">
      <div class="d-flex align-items-center flex-wrap flex-sm-nowrap" style="gap: 8px;">
        <div class="input-group" style="height: 38px;">
          <div class="input-group-prepend">
            <span class="input-group-text bg-light text-muted px-2.5" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1; border-right: none;"><i class="fas fa-calendar-alt text-primary"></i></span>
          </div>
          <input type="date" id="fromDateInput" class="form-control font-weight-bold" title="ຕັ້ງແຕ່ວັນທີ" value="<?php echo date('Y-m-01'); ?>" oninput="filterCustomerTable()" onchange="filterCustomerTable()" style="height: 38px; font-size: 0.88rem; border-radius: 0 8px 8px 0; border: 1px solid #cbd5e1;">
        </div>
        <span class="font-weight-bold text-secondary px-1" style="font-size: 0.88rem; white-space: nowrap;">ຫາ</span>
        <div class="input-group" style="height: 38px;">
          <div class="input-group-prepend">
            <span class="input-group-text bg-light text-muted px-2.5" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1; border-right: none;"><i class="fas fa-calendar-check text-success"></i></span>
          </div>
          <input type="date" id="toDateInput" class="form-control font-weight-bold" title="ຫາວັນທີ" value="<?php echo date('Y-m-d'); ?>" oninput="filterCustomerTable()" onchange="filterCustomerTable()" style="height: 38px; font-size: 0.88rem; border-radius: 0 8px 8px 0; border: 1px solid #cbd5e1;">
        </div>
        <button type="button" class="btn btn-primary font-weight-bold px-3 shadow-sm" onclick="filterCustomerTable()" title="ຄົ້ນຫາ" style="border-radius: 8px; height: 38px; font-size: 0.88rem; white-space: nowrap; display: flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
          <i class="fas fa-search"></i> ຄົ້ນຫາ
        </button>
      </div>
    </div>

    <!-- Right: Member Name Search Box (ບັອກຄົ້ນຫາ ລາຍຊື່ສະມາຊິກ - ເຣວທາມ Real-time) -->
    <div class="col-lg-5 col-md-5 col-12 ml-auto">
      <div class="input-group" style="height: 38px;">
        <div class="input-group-prepend">
          <span class="input-group-text bg-light text-primary px-3" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1; border-right: none;"><i class="fas fa-search" style="font-size: 0.95rem;"></i></span>
        </div>
        <input type="search" id="customerSearchInput" class="form-control" placeholder="ຄົ້ນຫາ ລາຍຊື່ສະມາຊິກ, ລະຫັດ, ເບີໂທ, ເລກບັດ..." oninput="filterCustomerTable()" onkeyup="filterCustomerTable()" onsearch="filterCustomerTable()" style="height: 38px; font-size: 0.9rem; border-radius: 0 8px 8px 0; border: 1px solid #cbd5e1;">
      </div>
    </div>

  </div>
</div>
