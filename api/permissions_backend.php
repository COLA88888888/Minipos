<?php
/**
 * --------------------------------------------------------------------------
 * ຟາຍ Backend: ປະມວນຜົນຂໍ້ມູນ ແລະ AJAX Action Handler
 * Path: pages/permissions/partials/permissions_backend.php
 * --------------------------------------------------------------------------
 * ໜ້າທີ່:
 * - ກວດເຊັກ Session ວ່າໄດ້ Login ຫຼື ບໍ່ ແລະ ມີສິດເຂົ້າເຖິງໜ້າກຳນົດສິດ
 * - ຮັບ ແລະ ປະມວນຜົນ AJAX Request ຈາກ JavaScript (ປ່ຽນສິດເອກະລາດ / ນຳໃຊ້ Preset ດ່ວນ)
 * - ດຶງຂໍ້ມູນຜູ້ນຳໃຊ້ທັງໝົດ (`tbuser`) ພ້ອມ Join ຊື່ສາຂາ (`tbstore`)
 */

// 1. ເລີ່ມ Session ຖ້າຍັງບໍ່ທັນເລີ່ມ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. ຄຳນວນ Path ຫຼັກຂອງລະບົບ
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

// 3. ໂຫຼດໄຟລ໌ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
require_once __DIR__ . '/../config/db.php';

// 4. ກວດເຊັກສິດການເຂົ້າເຖິງ (ສະເພາະ ຜູ້ບໍລິຫານ ຫຼື ຜູ້ທີ່ມີສິດ permissions/users ເທົ່ານັ້ນ)
if (empty($_SESSION['user_id']) || (!hasPermission('permissions') && !hasPermission('users') && $_SESSION['status'] !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

// =========================================================================
// 5. ຈັດການ AJAX Actions (ເມື່ອມີການສົ່ງ POST ajax_action ຈາກ JavaScript)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    // ---------------------------------------------------------------------
    // Action 1: ປ່ຽນສິດເອກະລາດ single toggle (toggle_perm)
    // ---------------------------------------------------------------------
    if ($action === 'toggle_perm') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $perm    = trim($_POST['perm'] ?? '');
        $val     = intval($_POST['val'] ?? 0);

        // ລາຍຊື່ຄໍລຳສິດທີ່ອະນຸຍາດໃຫ້ປັບປ່ຽນ
        $allowed_perms = ['dashboard', 'sale', 'stock', 'report', 'accounting', 'setup', 'users', 'permissions', 'edit', 'customers'];

        if ($user_id <= 0 || !in_array($perm, $allowed_perms, true)) {
            echo json_encode(['success' => false, 'message' => 'ຂໍ້ມູນບໍ່ຖືກຕ້ອງ']);
            exit();
        }

        // ປ້ອງກັນບໍ່ໃຫ້ປິດສິດຂອງຜູ້ບໍລິຫານ (Admin / Executive / Super Admin ID: 1)
        $uRole = $pdo->query("SELECT status FROM tbuser WHERE Id = {$user_id}")->fetchColumn();
        if ($user_id === 1 || strtolower($uRole) === 'admin' || $uRole === 'ຜູ້ບໍລິຫານ') {
            echo json_encode(['success' => false, 'message' => 'ຜູ້ບໍລິຫານ (Admin) ມີສິດເຕັມ 100% ບໍ່ສາມາດປັບປ່ຽນສິດໄດ້!']);
            exit();
        }

        try {
            // ເພີ່ມຄໍລຳອໍໂຕ້ໃນຖານຂໍ້ມູນຖ້າຍັງບໍ່ມີ
            try {
                $pdo->exec("ALTER TABLE tbuser ADD COLUMN `{$perm}` TINYINT(1) DEFAULT 0");
            } catch (Throwable $ex) {}

            // ອັບເດດສິດໃນຕາຕະລາງ tbuser
            $stmt = $pdo->prepare("UPDATE tbuser SET `{$perm}` = ? WHERE Id = ?");
            $stmt->execute([$val, $user_id]);

            // ດຶງຂໍ້ມູນຜູ້ໃຊ້ທີ່ຖືກອັບເດດ ເພື່ອຄຳນວນຈຳນວນສິດທີ່ເປີດຢູ່
            $uStmt = $pdo->prepare("SELECT username, dashboard, sale, stock, report, accounting, setup, users, edit, customers FROM tbuser WHERE Id = ?");
            $uStmt->execute([$user_id]);
            $userData = $uStmt->fetch();
            $targetUser = $userData['username'] ?? 'User';

            $permCount = 0;
            $countKeys = ['dashboard', 'sale', 'stock', 'report', 'accounting', 'setup', 'users', 'edit', 'customers'];
            foreach ($countKeys as $pKey) {
                if (!empty($userData[$pKey])) {
                    $permCount++;
                }
            }

            // ຊື່ສິດພາສາລາວສຳລັບບັນທຶກ Log
            $perm_names_lao = [
                'dashboard' => 'ສິດ ດາດຊ໌ບອດ',
                'sale' => 'ສິດ ຂາຍສິນຄ້າ POS',
                'customers' => 'ສິດ ຈັດການລູກຄ້າ',
                'stock' => 'ສິດ ຂໍ້ມູນສິນຄ້າ & ຄັງສິນຄ້າ',
                'accounting' => 'ສິດ ຈັດການບັນຊີ',
                'report' => 'ສິດ ລາຍງານ & ການເງິນ',
                'users' => 'ສິດ ຈັດການຜູ້ນຳໃຊ້',
                'permissions' => 'ສິດ ກຳນົດສິດ',
                'setup' => 'ສິດ ຕັ້ງຄ່າລະບົບ & ຈັດການສາຂາ',
                'edit' => 'ສິດ ແກ້ໄຂ & ລົບຂໍ້ມູນ'
            ];
            $perm_lao = $perm_names_lao[$perm] ?? $perm;
            $status_text = ($val === 1) ? 'ເປີດສິດ' : 'ປິດສິດ';

            // ບັນທຶກປະຫວັດການເຮັດວຽກ (Activity Log)
            logActivity($pdo, "ປ່ຽນສິດການໃຊ້ງານ", "ຜູ້ໃຊ້: {$targetUser} (ID: {$user_id}), {$perm_lao}: {$status_text}");

            echo json_encode([
                'success' => true, 
                'message' => "{$status_text} \"{$perm_lao}\" ໃຫ້ {$targetUser} ສຳເລັດ!",
                'user_id' => $user_id,
                'perm' => $perm,
                'val' => $val,
                'perm_count' => $permCount
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'ຜິດພາດ: ' . $e->getMessage()]);
        }
        exit();
    }

    // ---------------------------------------------------------------------
    // Action 2: ກຳນົດສິດແບບດ່ວນ (apply_preset)
    // ---------------------------------------------------------------------
    if ($action === 'apply_preset') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $preset  = trim($_POST['preset'] ?? '');

        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບຜູ້ໃຊ້']);
            exit();
        }

        // ແຜນຜັງຮູບແບບສິດ (Preset Template Mapping)
        $presets_map = [
            'cashier'      => ['dashboard' => 0, 'sale' => 1, 'stock' => 0, 'report' => 0, 'accounting' => 0, 'setup' => 0, 'users' => 0, 'edit' => 0, 'customers' => 1, 'role_name' => 'ພະນັກງານຂາຍ POS'],
            'accountant'   => ['dashboard' => 1, 'sale' => 0, 'stock' => 0, 'report' => 1, 'accounting' => 1, 'setup' => 0, 'users' => 0, 'edit' => 0, 'customers' => 0, 'role_name' => 'ຄົນຈັດການບັນຊີ'],
            'stock_keeper' => ['dashboard' => 1, 'sale' => 0, 'stock' => 1, 'report' => 0, 'accounting' => 0, 'setup' => 0, 'users' => 0, 'edit' => 1, 'customers' => 0, 'role_name' => 'ພະນັກງານຄັງສິນຄ້າ'],
            'auditor'      => ['dashboard' => 1, 'sale' => 1, 'stock' => 1, 'report' => 1, 'accounting' => 1, 'setup' => 0, 'users' => 0, 'edit' => 0, 'customers' => 1, 'role_name' => 'ຜູ້ກວດສອບບັນຊີ'],
            'manager'      => ['dashboard' => 1, 'sale' => 1, 'stock' => 1, 'report' => 1, 'accounting' => 1, 'setup' => 1, 'users' => 1, 'edit' => 1, 'customers' => 1, 'permissions' => 1, 'role_name' => 'ຜູ້ບໍລິຫານ / ຈັດການທັງໝົດ'],
            'all_on'       => ['dashboard' => 1, 'sale' => 1, 'stock' => 1, 'report' => 1, 'accounting' => 1, 'setup' => 1, 'users' => 1, 'edit' => 1, 'customers' => 1, 'permissions' => 1, 'role_name' => 'ເປີດທຸກສິດ'],
            'all_off'      => ['dashboard' => 0, 'sale' => 0, 'stock' => 0, 'report' => 0, 'accounting' => 0, 'setup' => 0, 'users' => 0, 'edit' => 0, 'customers' => 0, 'permissions' => 0, 'role_name' => 'ປິດທຸກສິດ']
        ];

        if (!isset($presets_map[$preset])) {
            echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບຮູບແບບສິດທີ່ເລືອກ']);
            exit();
        }

        $uRole = $pdo->query("SELECT status FROM tbuser WHERE Id = {$user_id}")->fetchColumn();
        if ($user_id === 1 || strtolower($uRole) === 'admin' || $uRole === 'ຜູ້ບໍລິຫານ') {
            echo json_encode(['success' => false, 'message' => 'ຜູ້ບໍລິຫານ (Admin) ບໍ່ສາມາດປັບປ່ຽນສິດໄດ້!']);
            exit();
        }

        $p = $presets_map[$preset];
        try {
            $stmt = $pdo->prepare("UPDATE tbuser SET dashboard = ?, sale = ?, stock = ?, report = ?, accounting = ?, setup = ?, users = ?, permissions = ?, edit = ?, customers = ? WHERE Id = ?");
            $stmt->execute([$p['dashboard'] ?? 0, $p['sale'] ?? 0, $p['stock'] ?? 0, $p['report'] ?? 0, $p['accounting'] ?? 0, $p['setup'] ?? 0, $p['users'] ?? 0, $p['permissions'] ?? 0, $p['edit'] ?? 0, $p['customers'] ?? 0, $user_id]);

            $uStmt = $pdo->prepare("SELECT username FROM tbuser WHERE Id = ?");
            $uStmt->execute([$user_id]);
            $targetUser = $uStmt->fetchColumn();

            $permCount = 0;
            $allKeys = ['dashboard', 'sale', 'stock', 'report', 'accounting', 'setup', 'users', 'permissions', 'edit', 'customers'];
            foreach ($allKeys as $pk) {
                if (!empty($p[$pk])) {
                    $permCount++;
                }
            }

            logActivity($pdo, "ກຳນົດສິດແບບດ່ວນ", "ຜູ້ໃຊ້: {$targetUser} (ID: {$user_id}), ຮູບແບບ: {$p['role_name']}");

            echo json_encode([
                'success' => true, 
                'message' => "ນຳໃຊ້ຮູບແບບ \"{$p['role_name']}\" ໃຫ້ {$targetUser} ສຳເລັດ!",
                'user_id' => $user_id,
                'perm_count' => $permCount,
                'permissions' => [
                    'dashboard' => $p['dashboard'] ?? 0,
                    'sale' => $p['sale'] ?? 0,
                    'stock' => $p['stock'] ?? 0,
                    'report' => $p['report'] ?? 0,
                    'accounting' => $p['accounting'] ?? 0,
                    'setup' => $p['setup'] ?? 0,
                    'users' => $p['users'] ?? 0,
                    'permissions' => $p['permissions'] ?? 0,
                    'edit' => $p['edit'] ?? 0,
                    'customers' => $p['customers'] ?? 0
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'ຜິດພາດ: ' . $e->getMessage()]);
        }
        exit();
    }
}

// =========================================================================
// 6. ດຶງຂໍ້ມູນຜູ້ນຳໃຊ້ທັງໝົດ ພ້ອມ Join ຊື່ສາຂາ (tbstore)
// =========================================================================
$stmtUsers = $pdo->query("SELECT u.*, s.store_name FROM tbuser u LEFT JOIN tbstore s ON u.store_id = s.store_id ORDER BY u.Id ASC");
$users = $stmtUsers->fetchAll();

$totalUsers = count($users);
$firstUserId = !empty($users) ? $users[0]['Id'] : 0;
