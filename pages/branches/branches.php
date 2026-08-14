<?php
// ============================================================
// branches.php - DEDICATED MULTI-BRANCH MANAGEMENT SYSTEM
// ໜ້າຈັດການສາຂາທັງໝົດໃນລະບົບ (ແຍກຕ່າງຫາກ)
// ============================================================
session_start();
$base_path = '../../';
require_once __DIR__ . '/../../config/db.php';

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
    $is_main    = isset($_POST['is_main']) ? 1 : 0;

    if ($store_id > 0 && $store_name !== '') {
        try {
            if ($is_main === 1) {
                $pdo->exec("UPDATE tbstore SET is_main = 0");
            }
            $stmt = $pdo->prepare("UPDATE tbstore SET store_code = ?, store_name = ?, is_main = ?, address = ?, tel = ? WHERE store_id = ?");
            $stmt->execute([$store_code, $store_name, $is_main, $address, $tel, $store_id]);
            $message = 'ແກ້ໄຂຂໍ້ມູນສາຂາ ສຳເລັດແລ້ວ!';
            $message_type = 'success';
            logActivity($pdo, "ແກ້ໄຂສາຂາ", $store_name);
        } catch (Exception $e) {
            $message = 'ຜິດພາດ: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Handle GET Toggle Branch Status
if (isset($_GET['toggle_status_id'])) {
    $tid = intval($_GET['toggle_status_id']);
    try {
        $cur = $pdo->query("SELECT status FROM tbstore WHERE store_id = {$tid}")->fetchColumn();
        $next = ($cur === 'active') ? 'inactive' : 'active';
        $pdo->exec("UPDATE tbstore SET status = '{$next}' WHERE store_id = {$tid}");
        $message = "ປ່ຽນສະຖານະສາຂາ ສຳເລັດແລ້ວ!";
        $message_type = "success";
    } catch (Exception $e) {}
}

// Fetch all branch stores
$all_branches = $pdo->query("SELECT * FROM tbstore ORDER BY is_main DESC, store_id ASC")->fetchAll();

require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid p-4">
  <?php require_once __DIR__ . '/partials/branches_header.php'; ?>
  <?php require_once __DIR__ . '/partials/branches_table.php'; ?>
</div>

<?php require_once __DIR__ . '/partials/branches_modals.php'; ?>
<?php require_once __DIR__ . '/partials/js/branches_js.php'; ?>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
