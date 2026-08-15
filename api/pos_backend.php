<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($base_path)) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';
}
require_once __DIR__ . '/../config/db.php';

// Check authorization
if (empty($_SESSION['user_id'])) {
    echo "<script>window.top.location.href = '../index.php';</script>";
    exit();
}

$vat_rate = floatval(getSetting($pdo, 'vat_rate', '0'));

// Fetch Company/Store info directly from Database
$company = $pdo->query("SELECT * FROM tbcompanyinfo LIMIT 1")->fetch();
if (!$company) {
    $company = $pdo->query("SELECT store_name as com_name_la, address as com_address, tel as com_tel, 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!' as barcode, logo_path as img_url FROM tbstore LIMIT 1")->fetch();
}

// Helper function to get client IP
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
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
    $client_ip     = getClientIP();
    
    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'ກະຕ່າສິນຄ້າຫວ່າງເປົ່າ!']);
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        $user_id = $_SESSION['username'] ?? 'Admin';
        
        // Calculate totals & Tax/VAT
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
        
        $tax_type = $company['tax_type'] ?? 'inclusive';
        $vat_percent = floatval($company['vat_percent'] ?? 7.00);

        $amount_after_discount = max(0, $subtotal - $discount_bill);
        $vat_amount = 0.00;
        $net_total = $amount_after_discount;

        if ($tax_type === 'exclusive' && $vat_percent > 0) {
            $vat_amount = round($amount_after_discount * ($vat_percent / 100), 2);
            $net_total = $amount_after_discount + $vat_amount;
        } elseif ($tax_type === 'inclusive' && $vat_percent > 0) {
            $vat_amount = round($amount_after_discount - ($amount_after_discount / (1 + ($vat_percent / 100))), 2);
            $net_total = $amount_after_discount;
        }

        $total_paid = $cash_received + $qr_received;
        $change = max(0, $total_paid - $net_total);

        // Generate daily resetting invoice number starting from 0001 (Format: YYYYMMDD-0001)
        $todayDate = date('Y-m-d');
        $todayStr  = date('Ymd');

        $stmtSeq = $pdo->prepare("SELECT COUNT(*) FROM tbsale_save WHERE sale_date = :today_date");
        $stmtSeq->execute([':today_date' => $todayDate]);
        $todayCount = intval($stmtSeq->fetchColumn() ?? 0);
        $nextSeq = $todayCount + 1;

        $invoice_no = $todayStr . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        
        $bank_account_id = isset($_POST['bank_account_id']) && intval($_POST['bank_account_id']) > 0 ? intval($_POST['bank_account_id']) : null;
        $bank_name = isset($_POST['bank_name']) && trim($_POST['bank_name']) !== '' ? trim($_POST['bank_name']) : null;

        // Auto-assign primary active bank account if payment involves transfer but bank_account_id was not passed
        if (empty($bank_account_id) && (strpos($payment_type, 'ໂອນ') !== false || strpos($payment_type, 'QR') !== false || strpos($payment_type, 'Transfer') !== false)) {
            try {
                $firstBank = $pdo->query("SELECT id, bank_name FROM bank_accounts WHERE is_active = 1 ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($firstBank) {
                    $bank_account_id = intval($firstBank['id']);
                    $bank_name = $firstBank['bank_name'];
                }
            } catch (Throwable $e) {}
        }

        if ($bank_account_id && empty($bank_name)) {
            try {
                $bStmt = $pdo->prepare("SELECT bank_name FROM bank_accounts WHERE id = ?");
                $bStmt->execute([$bank_account_id]);
                $bank_name = $bStmt->fetchColumn() ?: null;
            } catch (Throwable $e) {}
        }

        // Insert into tbsale_save
        $stmtSave = $pdo->prepare("
            INSERT INTO tbsale_save (sale_save_bill, sale_date, sale_time, user_receive, customer_id, customer_name, ip_address, sale_qty, sale_amount, sale_discount_bill, sale_barlance, sale_pay, sale_return, type_pay, bank_account_id, bank_name, sale_status)
            VALUES (:invoice_no, CURDATE(), CURTIME(), :user_id, :customer_id, :customer_name, :ip_address, :sale_qty, :sale_amount, :discount_bill, :net_total, :cash_received, :change, :payment_type, :bank_account_id, :bank_name, 'SUCCESS')
        ");
        $stmtSave->execute([
            ':invoice_no'      => $invoice_no,
            ':user_id'         => $user_id,
            ':customer_id'     => $customer_id,
            ':customer_name'   => $customer_name,
            ':ip_address'      => $client_ip,
            ':sale_qty'        => $total_qty,
            ':sale_amount'     => $subtotal,
            ':discount_bill'   => $discount_bill,
            ':net_total'       => $net_total,
            ':cash_received'   => $cash_received,
            ':change'          => $change,
            ':payment_type'    => $payment_type,
            ':bank_account_id' => $bank_account_id,
            ':bank_name'       => $bank_name
        ]);

        // Insert into physical sales table with tax details
        try {
            $stmtSalesTable = $pdo->prepare("
                INSERT INTO sales (invoice_number, sold_by, customer_id, customer_name, subtotal, discount_amount, vat_amount, tax_type, vat_rate, total_amount, cash_received, change_amount, payment_type, bank_account_id, bank_name, status, created_at)
                VALUES (:invoice_no, :user_id, :customer_id, :customer_name, :subtotal, :discount_bill, :vat_amount, :tax_type, :vat_rate, :net_total, :cash_received, :change, :payment_type, :bank_account_id, :bank_name, 'SUCCESS', NOW())
                ON DUPLICATE KEY UPDATE subtotal = :subtotal, discount_amount = :discount_bill, vat_amount = :vat_amount, tax_type = :tax_type, vat_rate = :vat_rate, total_amount = :net_total, bank_account_id = :bank_account_id, bank_name = :bank_name
            ");
            $stmtSalesTable->execute([
                ':invoice_no'      => $invoice_no,
                ':user_id'         => $user_id,
                ':customer_id'     => $customer_id,
                ':customer_name'   => $customer_name,
                ':subtotal'        => $subtotal,
                ':discount_bill'   => $discount_bill,
                ':vat_amount'      => $vat_amount,
                ':tax_type'        => $tax_type,
                ':vat_rate'        => $vat_percent,
                ':net_total'       => $net_total,
                ':cash_received'   => $cash_received,
                ':change'          => $change,
                ':payment_type'    => $payment_type,
                ':bank_account_id' => $bank_account_id,
                ':bank_name'       => $bank_name
            ]);
        } catch (Throwable $ex) {}
        
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
    $c_name        = trim($_POST['customer_name'] ?? '');
    $c_phone       = trim($_POST['phone'] ?? '');
    $c_code        = trim($_POST['customer_code'] ?? '');
    $c_member_card = trim($_POST['member_card'] ?? '');
    $c_notes       = trim($_POST['notes'] ?? '');

    if (empty($c_name)) {
        echo json_encode(['success' => false, 'message' => 'ກະລຸນາປ້ອນຊື່ລູກຄ້າ!']);
        exit();
    }

    if (empty($c_code)) {
        $stmtSeq = $pdo->query("SELECT IFNULL(MAX(customer_id), 0) + 1 FROM customers");
        $maxCusId = (int)$stmtSeq->fetchColumn();
        $c_code = 'CUST-' . str_pad($maxCusId, 3, '0', STR_PAD_LEFT);
    }

    try {
        $stmtIns = $pdo->prepare("INSERT INTO customers (customer_code, customer_name, phone, member_card, notes, created_at) VALUES (:code, :name, :phone, :card, :notes, NOW())");
        $stmtIns->execute([
            ':code'  => $c_code,
            ':name'  => $c_name,
            ':phone' => $c_phone,
            ':card'  => $c_member_card,
            ':notes' => $c_notes
        ]);
        $new_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'customer' => [
                'customer_id'   => $new_id,
                'customer_code' => $c_code,
                'customer_name' => $c_name,
                'phone'         => $c_phone,
                'member_card'   => $c_member_card,
                'notes'         => $c_notes
            ]
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'ຜິດພາດ: ' . $e->getMessage()]);
        exit();
    }
}

// Auto-deactivate expired promotions & Fetch active promotions
$activePromos = [];
try {
    $pdo->exec("UPDATE promotions SET status = 0 WHERE status = 1 AND end_date < CURDATE()");
    $activePromos = $pdo->query("
        SELECT * FROM promotions 
        WHERE status = 1 
          AND CURDATE() BETWEEN start_date AND end_date 
        ORDER BY id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

function getProductPromotion($product, $activePromos) {
    if (empty($activePromos)) return null;
    
    $pName = trim($product['product_name'] ?? '');
    $cName = trim($product['category_name'] ?? '');

    $matchedDiscountPromo = null;
    $matchedGiftPromo = null;
    
    foreach ($activePromos as $promo) {
        $targetType = $promo['target_type'] ?? 'all';
        $targetName = trim($promo['target_name'] ?? '');
        $targetUnit = mb_strtolower(trim($promo['target_unit_name'] ?? 'all'));
        $baseUnit   = mb_strtolower(trim($product['unit'] ?? ''));

        $isMatch = false;
        
        if ($targetType === 'all' || empty($targetName) || $targetName === 'ທຸກສິນຄ້າ') {
            $isMatch = true;
        } elseif ($targetType === 'category') {
            if (!empty($cName) && (mb_strtolower($cName) === mb_strtolower($targetName) || mb_stripos($cName, $targetName) !== false || mb_stripos($targetName, $cName) !== false)) {
                $isMatch = true;
            }
        } elseif ($targetType === 'product') {
            if (!empty($pName) && (mb_strtolower($pName) === mb_strtolower($targetName) || mb_stripos($pName, $targetName) !== false || mb_stripos($targetName, $pName) !== false)) {
                $isMatch = true;
            }
        }

        // Check if unit matches target_unit_name
        $isUnitMatch = ($targetUnit === 'all' || empty($targetUnit) || $baseUnit === $targetUnit);
        if (!$isUnitMatch && !empty($baseUnit) && !empty($targetUnit)) {
            $n1 = preg_replace('/ເເກັດ|ເກັດ|ແກັດ|ແກັດ/u', 'ເກັດ', $baseUnit);
            $n2 = preg_replace('/ເເກັດ|ເກັດ|ແກັດ|ແກັດ/u', 'ເກັດ', $targetUnit);
            if ($n1 === $n2 || mb_stripos($baseUnit, $targetUnit) !== false || mb_stripos($targetUnit, $baseUnit) !== false) {
                $isUnitMatch = true;
            }
        }
        
        if ($isMatch) {
            $discVal = floatval($promo['discount_value'] ?? 0);
            $giftName = trim($promo['gift_product_name'] ?? '');
            
            // Apply discount if unit matches OR if target_unit_name is all
            if ($discVal > 0 && !$matchedDiscountPromo && $isUnitMatch) {
                $matchedDiscountPromo = $promo;
            }
            if (!empty($giftName) && !$matchedGiftPromo && $isUnitMatch) {
                $matchedGiftPromo = $promo;
            }
            if ($matchedDiscountPromo && $matchedGiftPromo) {
                break;
            }
        }
    }

    $promoToUse = $matchedDiscountPromo ?? $matchedGiftPromo;
    if (!$promoToUse) return null;

    $origPrice = floatval($product['price'] ?? 0);
    $promoType = $promoToUse['promo_type'] ?? 'discount';
    $discType  = $promoToUse['discount_type'] ?? 'percentage';
    $discVal   = floatval($promoToUse['discount_value'] ?? 0);
    $giftName  = trim($matchedGiftPromo['gift_product_name'] ?? $promoToUse['gift_product_name'] ?? '');
    $giftQty   = intval($matchedGiftPromo['gift_qty'] ?? $promoToUse['gift_qty'] ?? 1);
    if ($giftQty < 1) $giftQty = 1;
    
    $tunit = trim($promoToUse['target_unit_name'] ?? 'all');
    if (empty($tunit)) $tunit = 'all';

    $unitSuffix = ($tunit !== 'all') ? ' (ສະເພາະ ' . $tunit . ')' : '';

    $discountAmount = 0;
    $promoPrice = $origPrice;

    $discountText = '';
    if ($discType === 'percentage' && $discVal > 0) {
        $discountAmount = $origPrice * ($discVal / 100);
        $promoPrice = max(0, $origPrice - $discountAmount);
        $discountText = 'ຫຼຸດ: ' . (floor($discVal) == $discVal ? intval($discVal) : number_format($discVal, 1)) . '% (-' . number_format($discountAmount) . '₭)';
    } elseif ($discVal > 0) {
        $discountAmount = $discVal;
        $promoPrice = max(0, $origPrice - $discountAmount);
        $discountText = 'ຫຼຸດ: ' . number_format($discVal) . '₭';
    }

    $baseUnit = mb_strtolower(trim($product['unit'] ?? ''));
    $tunitLower = mb_strtolower($tunit);
    
    // Check if base product unit matches target unit
    $isUnitMatch = ($tunitLower === 'all' || empty($tunitLower) || $baseUnit === $tunitLower);
    if (!$isUnitMatch) {
        // Normalize Lao spelling variations (ເຊັ່ນ: ເເກັດ vs ເກັດ vs ແກັດ)
        $n1 = preg_replace('/ເເກັດ|ເກັດ|ແກັດ|ແກັດ/u', 'ເກັດ', $baseUnit);
        $n2 = preg_replace('/ເເກັດ|ເກັດ|ແກັດ|ແກັດ/u', 'ເກັດ', $tunitLower);
        if ($n1 === $n2 || mb_stripos($baseUnit, $tunitLower) !== false || mb_stripos($tunitLower, $baseUnit) !== false) {
            $isUnitMatch = true;
        }
    }

    $giftText = '';
    // Only show Free Gift text on the product card badge if the product's unit matches the target unit!
    if (!empty($giftName) && $isUnitMatch) {
        if ($tunit === 'all' || empty($tunit)) {
            $giftText = 'ແຖມ: ' . $giftName . ($giftQty > 1 ? ' x' . $giftQty : '');
        } else {
            $giftText = 'ແຖມ: ' . $giftName . ($giftQty > 1 ? ' x' . $giftQty : '') . ' (ສະເພາະ ' . $tunit . ')';
        }
    }

    if (!empty($discountText) && !empty($giftText)) {
        $badge = $discountText . ' + ' . $giftText;
    } elseif (!empty($discountText)) {
        $badge = $discountText;
    } elseif (!empty($giftText)) {
        $badge = $giftText;
    } else {
        $badge = $promoToUse['promo_name'];
    }

    return [
        'promo_name'        => $promoToUse['promo_name'],
        'promo_type'        => $promoType,
        'discount_type'     => $discType,
        'discount_value'    => $discVal,
        'discount_amount'   => $discountAmount,
        'original_price'    => $origPrice,
        'promo_price'       => $promoPrice,
        'badge'             => $badge,
        'min_qty'           => intval($promoToUse['min_qty'] ?? 0),
        'gift_product_name' => $giftName,
        'gift_qty'          => $giftQty,
        'target_unit_name'  => $tunit
    ];
}

// Fetch categories, products, and customers list
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
$productsRaw = $pdo->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.product_name ASC
")->fetchAll();

$customersList = $pdo->query("SELECT customer_id, customer_code, customer_name, phone, member_card, notes, created_at FROM customers ORDER BY customer_id DESC")->fetchAll();

// Fetch extra units
$extraUnits = $pdo->query("SELECT * FROM product_units ORDER BY multiplier ASC, id ASC")->fetchAll();
$unitsMap = [];
foreach ($extraUnits as $u) {
    $unitsMap[$u['product_id']][] = $u;
}

// Map products with complete unit structures and promotions
$products = [];
foreach ($productsRaw as $p) {
    $pid = $p['product_id'];
    $p['extra_units'] = $unitsMap[$pid] ?? [];
    
    $promo = getProductPromotion($p, $activePromos);
    if ($promo) {
        $p['has_promo']         = true;
        $p['original_price']    = floatval($p['price']);
        $p['promo_price']       = $promo['promo_price'];
        $p['promo_badge']       = $promo['badge'];
        $p['promo_name']        = $promo['promo_name'];
        $p['promo_type']        = $promo['promo_type'] ?? 'discount';
        $p['gift_product_name'] = $promo['gift_product_name'] ?? '';
        $p['gift_qty']          = $promo['gift_qty'] ?? 1;
        $p['target_unit_name']  = $promo['target_unit_name'] ?? 'all';
        $p['price']             = $promo['promo_price']; // Set active price to promotional price
        
        if (!empty($p['extra_units']) && $promo['discount_type'] === 'percentage') {
            foreach ($p['extra_units'] as &$eu) {
                $eu['original_price'] = floatval($eu['price']);
                $eu['price'] = round($eu['price'] * (1 - ($promo['discount_value'] / 100)));
            }
        }
    } else {
        $p['has_promo']       = false;
        $p['original_price']  = floatval($p['price']);
    }
    
    $products[] = $p;
}
