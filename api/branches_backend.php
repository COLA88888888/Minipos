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
if (empty($_SESSION['user_id']) || (!hasPermission('setup') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// Auto-generate next numeric Branch Code (1, 2, 3...)
$max_id = (int)$pdo->query("SELECT COALESCE(MAX(store_id), 0) FROM tbstore")->fetchColumn();
$next_store_code = (string)($max_id + 1);

// Handle POST Add Branch Store
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_branch') {
    $store_code = trim($_POST['store_code'] ?? '');
    if ($store_code === '') {
        $store_code = $next_store_code;
    }
    $store_name = trim($_POST['store_name'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $tel        = trim($_POST['tel'] ?? '');
    $is_main    = isset($_POST['is_main']) ? 1 : 0;

    if ($store_code !== '' && $store_name !== '') {
        try {
            if ($is_main === 1) {
                $pdo->exec("UPDATE tbstore SET is_main = 0");
            }
            $stmt = $pdo->prepare("INSERT INTO tbstore (store_code, store_name, is_main, address, tel, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$store_code, $store_name, $is_main, $address, $tel]);
            $message = 'ເພີ່ມສາຂາໃໝ່ ສຳເລັດແລ້ວ!';
            $message_type = 'success';
            logActivity($pdo, "ເພີ່ມສາຂາໃໝ່", $store_name);
        } catch (Exception $e) {
            $message = 'ຜິດພາດ: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = 'ກະລຸນາປ້ອນລະຫັດສາຂາ ແລະ ຊື່ສາຂາ!';
        $message_type = 'warning';
    }
}

// Handle POST Edit Branch Store
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_branch') {
    $store_id   = intval($_POST['store_id'] ?? 0);
    $store_code = trim($_POST['store_code'] ?? '');
    $store_name = trim($_POST['store_name'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $tel        = trim($_POST['tel'] ?? '');
    $status     = trim($_POST['status'] ?? 'active');
    $is_main    = isset($_POST['is_main']) ? 1 : 0;

    if ($store_id > 0 && $store_name !== '') {
        try {
            if ($is_main === 1) {
                $pdo->exec("UPDATE tbstore SET is_main = 0");
            }
            $stmt = $pdo->prepare("UPDATE tbstore SET store_code = ?, store_name = ?, is_main = ?, address = ?, tel = ?, status = ? WHERE store_id = ?");
            $stmt->execute([$store_code, $store_name, $is_main, $address, $tel, $status, $store_id]);
            $message = 'ແກ້ໄຂຂໍ້ມູນສາຂາ ສຳເລັດແລ້ວ!';
            $message_type = 'success';
            logActivity($pdo, "ແກ້ໄຂຂໍ້ມູນສາຂາ", $store_name);
        } catch (Exception $e) {
            $message = 'ຜິດພາດ: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Fetch all branch stores
$all_branches = $pdo->query("SELECT * FROM tbstore ORDER BY is_main DESC, store_id ASC")->fetchAll();
$branches = $all_branches;
