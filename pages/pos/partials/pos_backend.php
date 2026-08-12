<?php
session_start();
$base_path = '../../';
require_once __DIR__ . '/../../../config/db.php';

// Check authorization
if (empty($_SESSION['user_id'])) {
    echo "<script>window.top.location.href = '../../index.php';</script>";
    exit();
}

// Auto-ensure customer columns exist in tbsale_save
try {
    $pdo->exec("ALTER TABLE tbsale_save ADD COLUMN customer_id INT NULL AFTER user_receive");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE tbsale_save ADD COLUMN customer_name VARCHAR(255) NULL AFTER customer_id");
} catch (Exception $e) {}

$vat_rate = floatval(getSetting($pdo, 'vat_rate', '0'));

// Fetch Company/Store info for receipts
$company = $pdo->query("SELECT * FROM tbcompanyinfo LIMIT 1")->fetch();
if (!$company) {
    $company = [
        'com_name_la' => 'ຮ້ານ Corner Retail',
        'com_address' => 'ນະຄອນຫຼວງວຽງຈັນ',
        'com_tel'     => '020 55555555',
        'barcode'     => 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!',
        'img_url'     => 'logo.png'
    ];
}

// 1. AJAX handler for Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    header('Content-Type: application/json');
    $cart = json_decode($_POST['cart'] ?? '[]', true);
    $cash_received = floatval($_POST['cash_received'] ?? 0.00);
    $qr_received   = floatval($_POST['qr_received']   ?? 0.00);
    $payment_type  = $_POST['payment_type'] ?? 'ເງິນສົດ';
    $pay_mode      = $_POST['pay_mode']     ?? 'single';
    $discount_bill = floatval($_POST['discount_amount'] ?? 0.00);
    $customer_id   = !empty($_POST['customer_id']) ? strval($_POST['customer_id']) : '';
    $customer_name = !empty($_POST['customer_name']) ? trim($_POST['customer_name']) : 'ລູກຄ້າທົ່ວໄປ';
    
    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'ກະຕ່າສິນຄ້າຫວ່າງເປົ່າ!']);
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        $user_id = $_SESSION['username'] ?? 'Admin';
        
        // Calculate totals
        $subtotal = 0;
        $total_cost = 0;
        $total_qty = 0;
        
        foreach ($cart as $item) {
            $qty = floatval($item['quantity']);
            $price = floatval($item['unit_price']);
            $cost = floatval($item['cost_price']);
            $subtotal += ($qty * $price);
            $total_cost += ($qty * $cost);
            $total_qty += $qty;
        }
        
        $net_total = max(0, $subtotal - $discount_bill);
        if ($pay_mode === 'split') {
            $total_paid = $cash_received + $qr_received;
            $change = max(0, $total_paid - $net_total);
        } else {
            $change = max(0, $cash_received - $net_total);
        }

        // Generate invoice number
        $stmtSeq = $pdo->query("SELECT MAX(Id) as max_id FROM tbsale_save");
        $maxId = $stmtSeq->fetch()['max_id'] ?? 0;
        $invoice_no = 'INV-' . date('Ymd') . '-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
        
        // Insert into tbsale_save
        $stmtSave = $pdo->prepare("
            INSERT INTO tbsale_save (sale_save_bill, sale_date, sale_time, user_receive, customer_id, customer_name, sale_qty, sale_amount, sale_discount_bill, sale_barlance, sale_pay, sale_return, type_pay, sale_status)
            VALUES (:invoice_no, CURDATE(), CURTIME(), :user_id, :customer_id, :customer_name, :sale_qty, :sale_amount, :discount_bill, :net_total, :cash_received, :change, :payment_type, 'SUCCESS')
        ");
        $stmtSave->execute([
            ':invoice_no'    => $invoice_no,
            ':user_id'       => $user_id,
            ':customer_id'   => $customer_id,
            ':customer_name' => $customer_name,
            ':sale_qty'      => $total_qty,
            ':sale_amount'   => $subtotal,
            ':discount_bill' => $discount_bill,
            ':net_total'     => $net_total,
            ':cash_received' => $cash_received,
            ':change'        => $change,
            ':payment_type'  => $payment_type
        ]);
        
        // Insert into tbsale_save_detail and update inventory
        $stmtDetail = $pdo->prepare("
            INSERT INTO tbsale_save_detail (save_bill, save_date, save_time, save_proid, save_proname, save_qty, save_price, cost_price, save_money, save_net_money, user_receives)
            VALUES (:save_bill, CURDATE(), CURTIME(), :save_proid, :save_proname, :save_qty, :save_price, :cost_price, :save_money, :save_net_money, :user_receives)
        ");
        
        $stmtUpdateStock = $pdo->prepare("
            UPDATE products SET qty = qty - :deduct_qty WHERE product_id = :product_id
        ");

        $stmtCheckStock = $pdo->prepare("
            SELECT product_name, qty, cut_qty FROM products WHERE product_id = :pid FOR UPDATE
        ");
        
        $details_summary = [];
        $updated_stocks = [];
        
        foreach ($cart as $item) {
            $pid = intval($item['product_id']);
            $proname = $item['product_name'] . ' (' . $item['unit_name'] . ')';
            $qty = floatval($item['quantity']);
            $price = floatval($item['unit_price']);
            $cost = floatval($item['cost_price']);
            $multiplier = intval($item['multiplier'] ?? 1);
            $total_item = $qty * $price;
            $deduct_stock_qty = $qty * $multiplier;

            // ກວດສອບຍອດເຫຼືອໃນຖານຂໍ້ມູນ
            $stmtCheckStock->execute([':pid' => $pid]);
            $pData = $stmtCheckStock->fetch();
            if (!$pData) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => "ບໍ່ພົບຂໍ້ມູນສິນຄ້າ ID: {$pid} ໃນລະບົບ!"]);
                exit();
            }

            $shouldCutQty = intval($pData['cut_qty'] ?? 1); // 1=ຕັດ qty, 0=ບໍ່ຕັດ qty
            $currentDbStock = floatval($pData['qty']);

            if ($shouldCutQty) {
                if ($currentDbStock <= 0) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => "ສິນຄ້າ \"{$pData['product_name']}\" ໝົດແລ້ວ! ບໍ່ສາມາດຂາຍໄດ້"]);
                    exit();
                }

                if ($currentDbStock < $deduct_stock_qty) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => "ສິນຄ້າ \"{$pData['product_name']}\" ເຫຼືອພຽງ {$currentDbStock} ອັນ! ບໍ່ພໍສຳລັບການຂາຍ"]);
                    exit();
                }
            }
            
            $stmtDetail->execute([
                ':save_bill'    => $invoice_no,
                ':save_proid'   => $pid,
                ':save_proname' => $proname,
                ':save_qty'     => $qty,
                ':save_price'   => $price,
                ':cost_price'   => $cost,
                ':save_money'   => $total_item,
                ':save_net_money'=> $total_item,
                ':user_receives'=> $user_id
            ]);
            
            $newStock = $currentDbStock;
            // ຕັດ qty ສະເພາະສິນຄ້າທີ່ cut_qty = 1 ເທົ່ານັ້ນ
            if ($shouldCutQty) {
                $stmtUpdateStock->execute([
                    ':deduct_qty' => $deduct_stock_qty,
                    ':product_id' => $pid
                ]);
                $newStock = max(0, $currentDbStock - $deduct_stock_qty);
            }

            $updated_stocks[] = [
                'product_id' => $pid,
                'new_qty'    => $newStock,
                'cut_qty'    => $shouldCutQty
            ];

            $details_summary[] = [
                'proname' => $proname,
                'qty'     => $qty,
                'price'   => $price,
                'total'   => $total_item
            ];
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success'        => true,
            'invoice_number' => $invoice_no,
            'date'           => date('d/m/Y H:i'),
            'cashier'        => $_SESSION['username'] ?? 'Admin',
            'customer_name'  => $customer_name,
            'subtotal'       => $subtotal,
            'discount_amount'=> $discount_bill,
            'total_amount'   => $net_total,
            'cash_received'  => $cash_received,
            'qr_received'    => $qr_received,
            'pay_mode'       => $pay_mode,
            'change'         => $change,
            'payment_type'   => $payment_type,
            'details'        => $details_summary,
            'updated_stocks' => $updated_stocks
        ]);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage()]);
        exit();
    }
}

// 2. AJAX handler for Quick Add Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_customer_ajax') {
    header('Content-Type: application/json');
    $c_name    = trim($_POST['customer_name'] ?? '');
    $c_phone   = trim($_POST['phone'] ?? '');
    $c_code    = trim($_POST['customer_code'] ?? '');
    $c_address = trim($_POST['address'] ?? '');

    if (empty($c_name)) {
        echo json_encode(['success' => false, 'message' => 'ກະລຸນາປ້ອນຊື່ລູກຄ້າ!']);
        exit();
    }

    if (empty($c_code)) {
        $stmtSeq = $pdo->query("SELECT MAX(customer_id) as max_id FROM customers");
        $maxCusId = $stmtSeq->fetch()['max_id'] ?? 0;
        $c_code = 'CUS-' . str_pad($maxCusId + 1, 4, '0', STR_PAD_LEFT);
    }

    try {
        $stmtIns = $pdo->prepare("INSERT INTO customers (customer_code, customer_name, phone, address, created_at) VALUES (:code, :name, :phone, :address, NOW())");
        $stmtIns->execute([
            ':code'    => $c_code,
            ':name'    => $c_name,
            ':phone'   => $c_phone,
            ':address' => $c_address
        ]);
        $new_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'customer' => [
                'customer_id'   => $new_id,
                'customer_code' => $c_code,
                'customer_name' => $c_name,
                'phone'         => $c_phone,
                'address'       => $c_address
            ]
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage()]);
        exit();
    }
}

// Fetch categories, products, and customers list
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
$productsRaw = $pdo->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.product_name ASC
")->fetchAll();

$customersList = $pdo->query("SELECT customer_id, customer_code, customer_name, phone, address FROM customers ORDER BY customer_name ASC")->fetchAll();

// Fetch extra units
$extraUnits = $pdo->query("SELECT * FROM product_units ORDER BY multiplier ASC, id ASC")->fetchAll();
$unitsMap = [];
foreach ($extraUnits as $u) {
    $unitsMap[$u['product_id']][] = $u;
}

// Map products with complete unit structures
$products = [];
foreach ($productsRaw as $p) {
    $pid = $p['product_id'];
    $p['extra_units'] = $unitsMap[$pid] ?? [];
    $products[] = $p;
}
