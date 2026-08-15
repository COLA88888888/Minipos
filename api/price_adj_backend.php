<?php
// ຮັບປະກັນໂຄງສ້າງຕາຕະລາງ price_adjustments ມີຟີວຄົບຖ້ວນ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `price_adjustments` (
        `adjust_id` INT AUTO_INCREMENT PRIMARY KEY,
        `adjust_date` DATE DEFAULT NULL,
        `adjust_time` TIME DEFAULT NULL,
        `target_mode` VARCHAR(20) DEFAULT 'product',
        `category_id` INT DEFAULT NULL,
        `product_id` INT DEFAULT NULL,
        `product_name` VARCHAR(255) DEFAULT NULL,
        `target_price_type` VARCHAR(20) DEFAULT 'price',
        `calc_type` VARCHAR(20) DEFAULT 'set',
        `adjust_value` DECIMAL(12,2) DEFAULT 0.00,
        `old_bprice` DECIMAL(12,2) DEFAULT 0.00,
        `old_price` DECIMAL(12,2) DEFAULT 0.00,
        `new_bprice` DECIMAL(12,2) DEFAULT 0.00,
        `new_price` DECIMAL(12,2) DEFAULT 0.00,
        `diff_amount` DECIMAL(12,2) DEFAULT 0.00,
        `remark` TEXT DEFAULT NULL,
        `username` VARCHAR(100) DEFAULT NULL,
        `user_id` INT DEFAULT NULL,
        `branch_id` INT DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Add missing columns if table existed previously with old columns
    $cols = $pdo->query("SHOW COLUMNS FROM `price_adjustments`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('target_mode', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `target_mode` VARCHAR(20) DEFAULT 'product' AFTER `adjust_time`");
    if (!in_array('category_id', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `category_id` INT DEFAULT NULL AFTER `target_mode`");
    if (!in_array('product_id', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `product_id` INT DEFAULT NULL AFTER `category_id`");
    if (!in_array('product_name', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `product_name` VARCHAR(255) DEFAULT NULL AFTER `product_id`");
    if (!in_array('target_price_type', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `target_price_type` VARCHAR(20) DEFAULT 'price' AFTER `product_name`");
    if (!in_array('calc_type', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `calc_type` VARCHAR(20) DEFAULT 'set' AFTER `target_price_type`");
    if (!in_array('adjust_value', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `adjust_value` DECIMAL(12,2) DEFAULT 0.00 AFTER `calc_type`");
    if (!in_array('old_bprice', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `old_bprice` DECIMAL(12,2) DEFAULT 0.00 AFTER `adjust_value`");
    if (!in_array('old_price', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `old_price` DECIMAL(12,2) DEFAULT 0.00 AFTER `old_bprice`");
    if (!in_array('new_bprice', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `new_bprice` DECIMAL(12,2) DEFAULT 0.00 AFTER `old_price`");
    if (!in_array('new_price', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `new_price` DECIMAL(12,2) DEFAULT 0.00 AFTER `new_bprice`");
    if (!in_array('diff_amount', $cols)) $pdo->exec("ALTER TABLE `price_adjustments` ADD COLUMN `diff_amount` DECIMAL(12,2) DEFAULT 0.00 AFTER `new_price`");
} catch (Exception $e) {}

$message = '';
$message_type = '';

// Handle POST Actions (Add Adjustment & Delete Log)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'adjust_price') {
        $target_mode       = $_POST['target_mode'] ?? 'product'; // 'product' ຫຼື 'category'
        $category_id       = intval($_POST['category_id'] ?? 0);
        $product_id        = intval($_POST['product_id'] ?? 0);
        $target_price_type = $_POST['target_price_type'] ?? 'price'; // 'price', 'bprice', 'both'
        $calc_type         = $_POST['calc_type'] ?? 'set'; // 'increase', 'decrease', 'set'
        $adjust_value      = floatval(str_replace(',', '', $_POST['adjust_value'] ?? '0'));
        $remark            = trim($_POST['remark'] ?? '');
        $username          = $_SESSION['username'] ?? 'admin';
        $user_id           = $_SESSION['user_id'] ?? 1;

        if ($target_mode === 'product' && $product_id <= 0) {
            $message = 'ກະລຸນາເລືອກສິນຄ້າທີ່ຕ້ອງການປັບລາຄາ!';
            $message_type = 'danger';
        } elseif ($target_mode === 'category' && $category_id <= 0) {
            $message = 'ກະລຸນາເລືອກໝວດໝູ່ສິນຄ້າ!';
            $message_type = 'danger';
        } else {
            try {
                $pdo->beginTransaction();

                // ດຶງລາຍການສິນຄ້າທີ່ຈະປັບ
                if ($target_mode === 'product') {
                    $stmt = $pdo->prepare("SELECT product_id, product_name, bprice, price FROM products WHERE product_id = ?");
                    $stmt->execute([$product_id]);
                    $targetProducts = $stmt->fetchAll();
                } else {
                    $stmt = $pdo->prepare("SELECT product_id, product_name, bprice, price FROM products WHERE category_id = ?");
                    $stmt->execute([$category_id]);
                    $targetProducts = $stmt->fetchAll();
                }

                if (empty($targetProducts)) {
                    $pdo->rollBack();
                    $message = 'ບໍ່ພົບສິນຄ້າໃນເງື່ອນໄຂທີ່ເລືອກ!';
                    $message_type = 'warning';
                } else {
                    $count = 0;
                    $updStmt = $pdo->prepare("UPDATE products SET bprice = ?, price = ? WHERE product_id = ?");
                    $logStmt = $pdo->prepare("INSERT INTO price_adjustments (adjust_date, adjust_time, target_mode, category_id, product_id, product_name, target_price_type, calc_type, adjust_value, old_bprice, old_price, new_bprice, new_price, diff_amount, remark, username, user_id, branch_id) VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

                    foreach ($targetProducts as $prod) {
                        $pid       = $prod['product_id'];
                        $pname     = $prod['product_name'];
                        $oldBprice = floatval($prod['bprice']);
                        $oldPrice  = floatval($prod['price']);

                        $newBprice = $oldBprice;
                        $newPrice  = $oldPrice;

                        // ຄິດໄລ່ລາຄາຂາຍໃໝ່
                        if ($target_price_type === 'price' || $target_price_type === 'both') {
                            if ($calc_type === 'increase') {
                                $newPrice = $oldPrice + $adjust_value;
                            } elseif ($calc_type === 'decrease') {
                                $newPrice = max(0, $oldPrice - $adjust_value);
                            } else { // 'set'
                                $newPrice = max(0, $adjust_value);
                            }
                        }

                        // ຄິດໄລ່ລາຄາຊື້ໃໝ່
                        if ($target_price_type === 'bprice' || $target_price_type === 'both') {
                            if ($calc_type === 'increase') {
                                $newBprice = $oldBprice + $adjust_value;
                            } elseif ($calc_type === 'decrease') {
                                $newBprice = max(0, $oldBprice - $adjust_value);
                            } else { // 'set'
                                $newBprice = max(0, $adjust_value);
                            }
                        }

                        // ຜົນຕ່າງ (Diff amount relative to sales price or bprice)
                        $diffAmount = ($target_price_type === 'bprice') ? ($newBprice - $oldBprice) : ($newPrice - $oldPrice);

                        // ອັບເດດລາຄາໃນຕາຕະລາງ products
                        $updStmt->execute([$newBprice, $newPrice, $pid]);

                        // ບັນທຶກປະຫວັດລາຍລະອຽດ
                        $logStmt->execute([
                            $target_mode,
                            ($target_mode === 'category' ? $category_id : null),
                            $pid,
                            $pname,
                            $target_price_type,
                            $calc_type,
                            $adjust_value,
                            $oldBprice,
                            $oldPrice,
                            $newBprice,
                            $newPrice,
                            $diffAmount,
                            $remark,
                            $username,
                            $user_id
                        ]);

                        $count++;
                    }

                    $pdo->commit();
                    $message = "ປັບລາຄາສິນຄ້າສຳເລັດແລ້ວ ທັງໝົດ {$count} ລາຍການ!";
                    $message_type = 'success';
                    logActivity($pdo, "ປັບລາຄາສິນຄ້າ", "ໂໝດ: {$target_mode}, ປັບທັງໝົດ: {$count} ລາຍການ");
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif ($action === 'delete_log') {
        $adjust_id = intval($_POST['adjust_id'] ?? 0);
        if ($adjust_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM price_adjustments WHERE adjust_id = ?");
                $stmt->execute([$adjust_id]);
                $message = 'ລຶບປະຫວັດການປັບລາຄາສຳເລັດ!';
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'ຜິດພາດ: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// Fetch categories, products, and price adjustments history
$categoriesList = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC")->fetchAll();
$productsList   = $pdo->query("SELECT p.product_id, p.product_name, p.barcode, p.category_id, p.bprice, p.price, p.unit, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_name ASC")->fetchAll();

$adjustments = $pdo->query("
    SELECT pa.*, p.barcode as prod_barcode, c.category_name 
    FROM price_adjustments pa
    LEFT JOIN products p ON pa.product_id = p.product_id
    LEFT JOIN categories c ON pa.category_id = c.category_id
    ORDER BY pa.adjust_id DESC 
    LIMIT 100
")->fetchAll();
