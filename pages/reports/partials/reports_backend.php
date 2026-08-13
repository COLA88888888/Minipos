<?php
// Reports Backend Processing & Data Layer - Modular Architecture
session_start();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once dirname(__DIR__, 3) . '/config/db.php';

// Check permissions
if (!hasPermission('report')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

// 1. Get Filter Input Parameters
$type         = trim($_GET['type'] ?? 'all_sales');
$default_from = ($type === 'daily') ? date('Y-m-d') : date('Y-m-01');

$from_date  = trim($_GET['from_date'] ?? $default_from); // default: today for daily, 1st of month for others
$to_date    = trim($_GET['to_date'] ?? date('Y-m-d'));   // default: today
$search     = trim($_GET['search'] ?? '');
$view_mode  = trim($_GET['view_mode'] ?? 'invoice');      // 'invoice' | 'item'

// Pagination Parameters
$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$per_page_default = '10';
$per_page_raw     = trim($_GET['per_page'] ?? $per_page_default);

if ($per_page_raw === 'all') {
    $per_page = 'all';
} else {
    $per_page = intval($per_page_raw);
    if (!in_array($per_page, [5, 10, 25, 50, 100])) {
        $per_page = 10;
    }
}

function getReportPageUrl($targetPage) {
    $queryParams = $_GET;
    $queryParams['page'] = $targetPage;
    $currentScript = basename($_SERVER['PHP_SELF'] ?? 'reports.php');
    return $currentScript . '?' . http_build_query($queryParams);
}

// Handle AJAX Request for Bill Items Modal Details
if (isset($_GET['action']) && $_GET['action'] === 'get_bill_details') {
    header('Content-Type: application/json');
    $billNo = trim($_GET['bill_no'] ?? '');

    if (empty($billNo)) {
        echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບເລກບິນ!']);
        exit();
    }

    try {
        $stmtB = $pdo->prepare("SELECT * FROM tbsale_save WHERE sale_save_bill = :bill");
        $stmtB->execute([':bill' => $billNo]);
        $billData = $stmtB->fetch(PDO::FETCH_ASSOC);

        if (!$billData) {
            echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບຂໍ້ມູນບິນຂາຍນີ້!']);
            exit();
        }

        $stmtD = $pdo->prepare("
            SELECT d.*, p.product_name, cat.category_name 
            FROM tbsale_save_detail d
            LEFT JOIN products p ON d.save_proid = p.product_id
            LEFT JOIN category cat ON p.category_id = cat.category_id
            WHERE d.save_bill = :bill
            ORDER BY d.Id ASC
        ");
        $stmtD->execute([':bill' => $billNo]);
        $details = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'bill' => $billData,
            'details' => $details
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// Handle AJAX Request for Delete Bill & Restore Product Inventory Stock
$reqAction = $_REQUEST['action'] ?? '';
if ($reqAction === 'delete_bill') {
    header('Content-Type: application/json');
    $billNo = trim($_REQUEST['bill_no'] ?? '');

    if (empty($billNo)) {
        echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບເລກບິນທີ່ຈະລົບ!']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Fetch bill header
        $stmtB = $pdo->prepare("SELECT * FROM tbsale_save WHERE sale_save_bill = :bill");
        $stmtB->execute([':bill' => $billNo]);
        $bill = $stmtB->fetch(PDO::FETCH_ASSOC);

        if (!$bill) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບຂໍ້ມູນບິນຂາຍນີ້ໃນລະບົບ!']);
            exit();
        }

        if (($bill['sale_status'] ?? '') === 'CANCEL') {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'ບິນຂາຍນີ້ຖືກລົບ/ຍົກເລີກໄປແລ້ວ!']);
            exit();
        }

        // 2. Fetch bill items
        $stmtD = $pdo->prepare("SELECT save_proid, save_qty FROM tbsale_save_detail WHERE save_bill = :bill");
        $stmtD->execute([':bill' => $billNo]);
        $details = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        // 3. Restore Product Inventory Stock (qty = qty + save_qty)
        $stmtRestore = $pdo->prepare("UPDATE products SET qty = qty + :restore_qty WHERE product_id = :proid");
        foreach ($details as $item) {
            $proid = $item['save_proid'];
            $qty   = floatval($item['save_qty']);
            if (!empty($proid) && $qty > 0) {
                $stmtRestore->execute([
                    ':restore_qty' => $qty,
                    ':proid'       => $proid
                ]);
            }
        }

        // 4. Update sale_status = 'CANCEL'
        $stmtCancel = $pdo->prepare("UPDATE tbsale_save SET sale_status = 'CANCEL' WHERE sale_save_bill = :bill");
        $stmtCancel->execute([':bill' => $billNo]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'ລົບບິນຂາຍ ' . $billNo . ' ແລະ ຄືນສະຕັອກສິນຄ້າສຳເລັດແລ້ວ!'
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage()]);
    }
    exit();
}

// Titles and icons mapping
$report_meta = [
    'daily' => [
        'title' => 'ລາຍງານປະຈຳວັນ',
        'icon' => 'fas fa-calendar-day text-info'
    ],
    'all_sales' => [
        'title' => ($view_mode === 'item') ? 'ລາຍການຂາຍສິນຄ້າ' : 'ລາຍງານການຂາຍທັງໝົດ',
        'icon' => 'fas fa-file-invoice-dollar text-success'
    ],
    'best_seller' => [
        'title' => 'ລາຍງານສິນຄ້າຂາຍດີ',
        'icon' => 'fas fa-fire text-danger'
    ],
    'profit_cost' => [
        'title' => 'ລາຍງານກຳໄລ-ຕົ້ນທຶນ',
        'icon' => 'fas fa-chart-line text-warning'
    ],
    'financial' => [
        'title' => 'ລາຍງານການເງິນ',
        'icon' => 'fas fa-wallet text-info'
    ],
    'category' => [
        'title' => 'ລາຍງານຕາມ/ປະເພດສິນຄ້າ',
        'icon' => 'fas fa-layer-group text-primary'
    ],
    'delete_bills' => [
        'title' => 'ປະຫວັດການລົບບິນຂາຍ',
        'icon' => 'fas fa-trash-alt text-danger'
    ]
];

$current_meta = $report_meta[$type] ?? $report_meta['all_sales'];

// Inspect columns in tbsale_save to handle cash, qr, and tip columns dynamically
$hasCashCol = false;
$hasQrCol   = false;
$hasTipCol  = false;
try {
    $cols = $pdo->query("SHOW COLUMNS FROM tbsale_save")->fetchAll(PDO::FETCH_COLUMN);
    $hasCashCol = in_array('cash_received', $cols);
    $hasQrCol   = in_array('qr_received', $cols);
    $hasTipCol  = in_array('tip_amount', $cols) || in_array('tip', $cols);
} catch (Exception $e) {}

// Build SQL Query & Fetch Data
$where  = [];
$params = [];

if ($type === 'delete_bills') {
    $where[] = "s.sale_status = 'CANCEL'";
} else {
    $where[] = "(s.sale_status IS NULL OR s.sale_status != 'CANCEL')";
}
$params = [];

// Always apply date range filter
if (!empty($from_date)) {
    $where[] = "DATE(s.sale_date) >= :from_date";
    $params[':from_date'] = $from_date;
}

if (!empty($to_date)) {
    $where[] = "DATE(s.sale_date) <= :to_date";
    $params[':to_date'] = $to_date;
}

if (!empty($search)) {
    $where[] = "(s.sale_save_bill LIKE :search OR s.user_receive LIKE :search OR s.customer_name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$whereClause = implode(" AND ", $where);

// Data variables
$sales_data        = [];
$total_qty         = 0;
$total_gross       = 0;
$total_disc        = 0;
$total_net         = 0;
$total_cash        = 0;
$total_qr          = 0;
$total_change      = 0;
$total_tip         = 0;
$total_profit      = 0;
$total_bills_count = 0;

// Daily Report 10 Metrics Variables
$daily_gross_sales    = 0;
$daily_item_discounts = 0;
$daily_bill_discounts = 0;
$daily_total_discounts= 0;
$daily_net_sales      = 0;
$daily_cash_payments  = 0;
$daily_qr_payments    = 0;
$daily_tips_sum       = 0;
$daily_bills_count    = 0;
$daily_items_qty      = 0;

if ($type === 'daily') {
    try {
        $cashSql = $hasCashCol ? "WHEN s.cash_received > 0 THEN s.cash_received" : "";
        $qrSql   = $hasQrCol   ? "WHEN s.qr_received > 0 THEN s.qr_received" : "";

        $stmtDaily = $pdo->prepare("
            SELECT 
                COUNT(s.Id) AS total_bills,
                COALESCE(SUM(s.sale_qty), 0) AS total_items_qty,
                COALESCE(SUM(s.sale_amount), 0) AS total_gross_sales,
                COALESCE(SUM(s.sale_discount_bill), 0) AS total_bill_discounts,
                COALESCE(SUM(s.sale_barlance), 0) AS total_net_sales,
                COALESCE(SUM(CASE 
                    {$cashSql}
                    WHEN s.type_pay LIKE '%ເງິນສົດ%' AND (s.type_pay NOT LIKE '%ໂອນ%' AND s.type_pay NOT LIKE '%QR%') THEN s.sale_barlance
                    WHEN s.type_pay LIKE '%ເງິນສົດ%' THEN GREATEST(0, s.sale_pay - s.sale_return)
                    ELSE 0 
                END), 0) AS total_cash_received,
                COALESCE(SUM(CASE 
                    {$qrSql}
                    WHEN (s.type_pay LIKE '%ໂອນ%' OR s.type_pay LIKE '%QR%') AND s.type_pay NOT LIKE '%ເງິນສົດ%' THEN s.sale_barlance
                    WHEN s.sale_transfer > 0 THEN s.sale_transfer
                    ELSE 0 
                END), 0) AS total_qr_received,
                COALESCE(SUM(GREATEST(0, (s.sale_pay - s.sale_return) - s.sale_barlance)), 0) AS total_tips_sum
            FROM tbsale_save s
            WHERE {$whereClause}
        ");
        $stmtDaily->execute($params);
        $dailyStats = $stmtDaily->fetch(PDO::FETCH_ASSOC);

        $hasItemDiscCol = false;
        try {
            $colsD = $pdo->query("SHOW COLUMNS FROM tbsale_save_detail")->fetchAll(PDO::FETCH_COLUMN);
            $hasItemDiscCol = in_array('save_discount_item', $colsD) ? 'save_discount_item' : (in_array('save_discount', $colsD) ? 'save_discount' : '');
        } catch (Exception $e) {}

        if ($hasItemDiscCol) {
            $stmtItemDisc = $pdo->prepare("
                SELECT COALESCE(SUM(d.{$hasItemDiscCol}), 0) AS total_item_disc
                FROM tbsale_save_detail d
                INNER JOIN tbsale_save s ON d.save_bill = s.sale_save_bill
                WHERE {$whereClause}
            ");
            $stmtItemDisc->execute($params);
            $itemDiscRes = $stmtItemDisc->fetch(PDO::FETCH_ASSOC);
            $daily_item_discounts = (float)($itemDiscRes['total_item_disc'] ?? 0);
        } else {
            $daily_item_discounts = 0;
        }

        $daily_bills_count     = (int)($dailyStats['total_bills'] ?? 0);
        $daily_items_qty       = (int)($dailyStats['total_items_qty'] ?? 0);
        $daily_gross_sales     = (float)($dailyStats['total_gross_sales'] ?? 0);
        $daily_bill_discounts  = (float)($dailyStats['total_bill_discounts'] ?? 0);
        $daily_total_discounts = $daily_item_discounts + $daily_bill_discounts;
        $daily_net_sales       = (float)($dailyStats['total_net_sales'] ?? 0);
        $daily_cash_payments   = (float)($dailyStats['total_cash_received'] ?? 0);
        $daily_qr_payments     = (float)($dailyStats['total_qr_received'] ?? 0);
        $daily_tips_sum        = (float)($dailyStats['total_tips_sum'] ?? 0);
    } catch (Exception $e) {}
}

if ($view_mode === 'item') {
    // VIEW MODE 2: ສະແດງຕາມລາຍການສິນຄ້າ (ITEM DETAIL VIEW)
    $sql = "
        SELECT d.*, s.sale_date, s.sale_time, s.user_receive, s.customer_name, s.type_pay, s.sale_status,
               s.sale_amount AS bill_gross, s.sale_discount_bill AS bill_discount, s.sale_barlance AS bill_net,
               s.sale_pay, s.sale_return,
               " . ($hasCashCol ? "s.cash_received," : "") . "
               " . ($hasQrCol ? "s.qr_received," : "") . "
               p.product_name, cat.category_name
        FROM tbsale_save_detail d
        INNER JOIN tbsale_save s ON d.save_bill = s.sale_save_bill
        LEFT JOIN products p ON d.save_proid = p.product_id
        LEFT JOIN category cat ON p.category_id = cat.category_id
        WHERE {$whereClause}
        ORDER BY s.sale_date DESC, s.sale_time DESC, d.Id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $qty   = floatval($r['save_qty']);
        $price = floatval($r['save_price']);
        $gross = floatval($r['save_money'] ?? ($qty * $price));

        $billGross = floatval($r['bill_gross'] ?? 0);
        $billDisc  = floatval($r['bill_discount'] ?? 0);
        $itemDisc  = ($billGross > 0) ? round(($gross / $billGross) * $billDisc, 2) : 0;
        $net       = max(0, $gross - $itemDisc);

        $typePay  = $r['type_pay'] ?? '';
        $billNet  = floatval($r['bill_net'] ?? 0);
        $billPay  = floatval($r['sale_pay'] ?? 0);
        $billRet  = floatval($r['sale_return'] ?? 0);
        $billPaid = max(0, $billPay - $billRet);

        if (isset($r['cash_received']) && isset($r['qr_received']) && ($r['cash_received'] > 0 || $r['qr_received'] > 0)) {
            $billCash = floatval($r['cash_received']);
            $billQr   = floatval($r['qr_received']);
        } else {
            if (mb_strpos($typePay, 'ໂອນ') !== false || mb_strpos($typePay, 'QR') !== false) {
                if (mb_strpos($typePay, 'ເງິນສົດ') !== false) {
                    $billCash = max(0, $billPaid);
                    $billQr   = max(0, $billNet - $billCash);
                } else {
                    $billCash = 0;
                    $billQr   = $billNet;
                }
            } else {
                $billCash = $billNet;
                $billQr   = 0;
            }
        }

        $billTip = max(0, $billPaid - $billNet);
        $ratio   = ($billNet > 0) ? ($net / $billNet) : 0;
        $itemCash = round($billCash * $ratio, 2);
        $itemQr   = round($billQr * $ratio, 2);
        $itemTip  = round($billTip * $ratio, 2);

        $buyPrice = floatval($r['price_buy'] ?? ($price * 0.7));
        $itemCost = $qty * $buyPrice;
        $profit   = max(0, $net - $itemCost);

        $sales_data[] = [
            'bill_no'   => $r['save_bill'],
            'date_time' => date('d/m/Y', strtotime($r['sale_date'])) . ' ' . substr($r['sale_time'], 0, 5),
            'pro_code'  => $r['save_proid'] ?: ($r['product_id'] ?? '-'),
            'pro_name'  => $r['save_proname'] ?: ($r['product_name'] ?? 'ສິນຄ້າ'),
            'qty'       => $qty,
            'price'     => $price,
            'gross'     => $gross,
            'discount'  => $itemDisc,
            'net'       => $net,
            'profit'    => $profit,
            'cash'      => $itemCash,
            'qr'        => $itemQr,
            'tip'       => $itemTip,
            'status'    => $r['sale_status'] ?? 'SUCCESS',
            'cashier'   => $r['user_receive'] ?? 'Admin'
        ];

        $total_qty    += $qty;
        $total_gross  += $gross;
        $total_disc   += $itemDisc;
        $total_net    += $net;
        $total_profit += $profit;
        $total_cash  += $itemCash;
        $total_qr    += $itemQr;
        $total_tip   += $itemTip;
    }

    $total_bills_count = count($sales_data);



} else {
    // VIEW MODE 1: ສະແດງຕາມບິນ (INVOICE SUMMARY VIEW)
    $sql = "
        SELECT s.*
        FROM tbsale_save s
        WHERE {$whereClause}
        ORDER BY s.sale_date DESC, s.sale_time DESC, s.Id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $qty   = floatval($r['sale_qty'] ?? 0);
        $gross = floatval($r['sale_amount'] ?? 0);
        $disc  = floatval($r['sale_discount_bill'] ?? 0);
        $net   = floatval($r['sale_barlance'] ?? max(0, $gross - $disc));

        $typePay = $r['type_pay'] ?? '';
        $pay     = floatval($r['sale_pay'] ?? 0);
        $ret     = floatval($r['sale_return'] ?? 0);
        $paid    = max(0, $pay - $ret);

        if (isset($r['cash_received']) && isset($r['qr_received']) && ($r['cash_received'] > 0 || $r['qr_received'] > 0)) {
            $cash = floatval($r['cash_received']);
            $qr   = floatval($r['qr_received']);
        } else {
            if (mb_strpos($typePay, 'ໂອນ') !== false || mb_strpos($typePay, 'QR') !== false) {
                if (mb_strpos($typePay, 'ເງິນສົດ') !== false) {
                    $cash = max(0, $paid);
                    $qr   = max(0, $net - $cash);
                } else {
                    $cash = 0;
                    $qr   = $net;
                }
            } else {
                $cash = $net;
                $qr   = 0;
            }
        }

        if (isset($r['tip_amount']) && $r['tip_amount'] > 0) {
            $tip = floatval($r['tip_amount']);
        } else if (isset($r['tip']) && $r['tip'] > 0) {
            $tip = floatval($r['tip']);
        } else {
            $tip = max(0, $paid - $net);
        }

        $change = floatval($r['sale_return'] ?? 0);

        $sales_data[] = [
            'bill_no'   => $r['sale_save_bill'],
            'date_time' => date('d/m/Y', strtotime($r['sale_date'])) . ' ' . substr($r['sale_time'], 0, 5),
            'qty'       => $qty,
            'gross'     => $gross,
            'discount'  => $disc,
            'net'       => $net,
            'cash'      => $cash,
            'qr'        => $qr,
            'change'    => $change,
            'tip'       => $tip,
            'status'    => $r['sale_status'] ?? 'SUCCESS',
            'cashier'   => $r['user_receive'] ?? 'Admin',
            'customer'  => $r['customer_name'] ?? 'ລູກຄ້າທົ່ວໄປ'
        ];

        $total_qty    += $qty;
        $total_gross  += $gross;
        $total_disc   += $disc;
        $total_net    += $net;
        $total_cash   += $cash;
        $total_qr     += $qr;
        $total_change += $change;
        $total_tip    += $tip;
    }

    $total_bills_count = count($sales_data);
}

// Slice $sales_data into $display_data for pagination
$total_records = count($sales_data);

if ($per_page === 'all') {
    $total_pages = 1;
    $page = 1;
    $display_data = $sales_data;
    $start_record = ($total_records > 0) ? 1 : 0;
    $end_record = $total_records;
} else {
    $total_pages = max(1, intval(ceil($total_records / $per_page)));
    if ($page > $total_pages) $page = $total_pages;

    $offset = ($page - 1) * $per_page;
    $display_data = array_slice($sales_data, $offset, $per_page);
    $start_record = ($total_records > 0) ? ($offset + 1) : 0;
    $end_record = min($offset + $per_page, $total_records);
}

$grouped_bills = [];
if ($view_mode === 'item') {
    foreach ($display_data as $row) {
        $bno = $row['bill_no'];
        if (!isset($grouped_bills[$bno])) {
            $grouped_bills[$bno] = [
                'bill_no'   => $bno,
                'date_time' => $row['date_time'],
                'cashier'   => $row['cashier'],
                'items'     => [],
                'bill_net'  => 0,
            ];
        }
        $grouped_bills[$bno]['items'][] = $row;
        $grouped_bills[$bno]['bill_net'] += $row['net'];
    }
}
