<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($base_path)) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';
}
require_once __DIR__ . '/../config/db.php';

// Check permissions
if (empty($_SESSION['user_id']) || (!hasPermission('customers') && !hasPermission('sale') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// Auto-ensure member_card column exists
try {
    $custCols = $pdo->query("SHOW COLUMNS FROM customers")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('member_card', $custCols)) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `member_card` VARCHAR(50) NULL AFTER `phone`");
    }
} catch (Throwable $ex) {}

// Handle Customer Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_customer') {
            $code        = trim($_POST['customer_code'] ?? '');
            $name        = trim($_POST['customer_name'] ?? '');
            $phone       = trim($_POST['phone'] ?? '');
            $member_card = trim($_POST['member_card'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $address     = trim($_POST['address'] ?? '');
            $notes       = trim($_POST['notes'] ?? '');

            if (empty($code)) {
                $maxId = (int)$pdo->query("SELECT IFNULL(MAX(customer_id), 0) + 1 FROM customers")->fetchColumn();
                $code = 'CUST-' . str_pad($maxId, 3, '0', STR_PAD_LEFT);
            }

            if ($name !== '') {
                try {
                    $stmt = $pdo->prepare("INSERT INTO customers (customer_code, customer_name, phone, member_card, email, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$code, $name, $phone, $member_card, $email, $address, $notes]);
                    $message = 'ເພີ່ມຂໍ້ມູນລູກຄ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ເພີ່ມຂໍ້ມູນລູກຄ້າ", "ລະຫັດ: $code, ຊື່: $name");
                } catch (Exception $e) {
                    $message = 'ຜິດພາດ: ລະຫັດລູກຄ້ານີ້ອາດມີໃນລະບົບແລ້ວ ຫຼື ' . $e->getMessage();
                    $message_type = 'danger';
                }
            } else {
                $message = 'ກະລຸນາປ້ອນລະຫັດ ແລະ ຊື່ລູກຄ້າໃຫ້ຄົບຖ້ວນ!';
                $message_type = 'danger';
            }
        }
        elseif ($_POST['action'] === 'edit_customer') {
            $id          = intval($_POST['customer_id'] ?? 0);
            $name        = trim($_POST['customer_name'] ?? '');
            $phone       = trim($_POST['phone'] ?? '');
            $member_card = trim($_POST['member_card'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $address     = trim($_POST['address'] ?? '');
            $notes       = trim($_POST['notes'] ?? '');

            if ($id > 0 && $name !== '') {
                try {
                    $stmt = $pdo->prepare("UPDATE customers SET customer_name = ?, phone = ?, member_card = ?, email = ?, address = ?, notes = ? WHERE customer_id = ?");
                    $stmt->execute([$name, $phone, $member_card, $email, $address, $notes, $id]);
                    $message = 'ແກ້ໄຂຂໍ້ມູນລູກຄ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ແກ້ໄຂຂໍ້ມູນລູກຄ້າ", "ID: $id, ຊື່: $name");
                } catch (Exception $e) {
                    $message = 'ຜິດພາດ: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            } else {
                $message = 'ກະລຸນາປ້ອນຊື່ລູກຄ້າໃຫ້ຄົບຖ້ວນ!';
                $message_type = 'danger';
            }
        }
        elseif ($_POST['action'] === 'delete_customer') {
            $id = intval($_POST['customer_id'] ?? 0);
            if ($id > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id = ?");
                    $stmt->execute([$id]);
                    $message = 'ລົບຂໍ້ມູນລູກຄ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ລົບຂໍ້ມູນລູກຄ້າ", "ID: $id");
                } catch (Exception $e) {
                    $message = 'ຜິດພາດ: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        }
    }
}

// Fetch All Customers
$stmtCust = $pdo->query("SELECT * FROM customers ORDER BY customer_id DESC");
$allCustomers = $stmtCust->fetchAll();
$total_records = count($allCustomers);

// Generate Next Customer Code
$maxId = (int)$pdo->query("SELECT IFNULL(MAX(customer_id), 0) + 1 FROM customers")->fetchColumn();
$next_cust_code = 'CUST-' . str_pad($maxId, 3, '0', STR_PAD_LEFT);
