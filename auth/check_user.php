<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ ແລະ ລະຫັດຜ່ານ']);
    exit();
}

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'ບໍ່ສາມາດເຊື່ອມຕໍ່ຖານຂໍ້ມູນໄດ້ (Database connection failed)']);
    exit();
}

try {
    // 1. ຄົ້ນຫາຜູ້ໃຊ້ງານຈາກຕາຕະລາງ tbuser ໂດຍກົງ
    $stmt = $pdo->prepare("SELECT * FROM tbuser WHERE username = ? OR fname = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. ກວດສອບລະຫັດຜ່ານ (ຮອງຮັບທັງ bcrypt, SHA-256, legacy PASSWORD() ແລະ plain text)
        $password_matches = false;
        $userpass = $user['password'] ?? $user['userpass'] ?? '';

        if (password_verify($password, $userpass)) {
            $password_matches = true;
        } elseif (hash('sha256', $password) === $userpass || hash('sha256', $password) === strtolower($userpass)) {
            $password_matches = true;
        } elseif ($password === $userpass) {
            $password_matches = true;
        } else {
            // ກວດສອບດ້ວຍ MySQL PASSWORD() ແບບເກົ່າ
            try {
                $hasUserpassCol = false;
                $userCols = $pdo->query("SHOW COLUMNS FROM tbuser")->fetchAll(PDO::FETCH_COLUMN);
                $hasUserpassCol = in_array('userpass', $userCols);

                if ($hasUserpassCol) {
                    $stmtLegacy = $pdo->prepare("SELECT * FROM tbuser WHERE (username = ? OR fname = ?) AND (password = PASSWORD(?) OR userpass = PASSWORD(?)) LIMIT 1");
                    $stmtLegacy->execute([$username, $username, $password, $password]);
                } else {
                    $stmtLegacy = $pdo->prepare("SELECT * FROM tbuser WHERE (username = ? OR fname = ?) AND password = PASSWORD(?) LIMIT 1");
                    $stmtLegacy->execute([$username, $username, $password]);
                }
                if ($stmtLegacy->fetch()) {
                    $password_matches = true;
                }
            } catch (Exception $ex) {}
        }

        if ($password_matches) {
            $userStatusVal = $user['status'] ?? $user['userstatus'] ?? '';
            $isAdmin = (
                strtolower($userStatusVal) === 'admin' || 
                $userStatusVal === 'ຜູ້ບໍລິຫານ' || 
                strtolower($user['username'] ?? '') === 'admin' || 
                ($user['Id'] ?? 0) == 1
            );

            // ບັນທຶກຂໍ້ມູນ Session
            $_SESSION['checked'] = 1;
            $_SESSION['user_id'] = $user['Id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_code'] = $user['user_code'] ?? '';
            $_SESSION['fname'] = $user['username'];
            $_SESSION['lname'] = '';
            $_SESSION['status'] = $isAdmin ? 'ຜູ້ບໍລິຫານ' : ($userStatusVal ?: 'ພະນັກງານ');
            $_SESSION['store_id'] = $user['store_id'] ?? $user['branch_id'] ?? 1;

            // ໂຫຼດສິດການໃຊ້ງານຕາມໂມດູນ (Module Permissions)
            $_SESSION['permissions'] = [
                'sale' => $isAdmin ? 1 : (int)($user['sale'] ?? 0),
                'stock' => $isAdmin ? 1 : (int)($user['stock'] ?? 0),
                'report' => $isAdmin ? 1 : (int)($user['report'] ?? 0),
                'accounting' => $isAdmin ? 1 : (int)($user['accounting'] ?? 0),
                'setup' => $isAdmin ? 1 : (int)($user['setup'] ?? 0),
                'users' => $isAdmin ? 1 : (int)($user['users'] ?? 0),
                'permissions' => $isAdmin ? 1 : (int)($user['users'] ?? 0),
                'edit' => $isAdmin ? 1 : (int)($user['edit'] ?? 0),
                'cafe' => $isAdmin ? 1 : (int)($user['cafe'] ?? 0),
                'order' => $isAdmin ? 1 : (int)($user['order'] ?? 0),
                'kitchen' => $isAdmin ? 1 : (int)($user['kitchen'] ?? 0),
                'tbl' => $isAdmin ? 1 : (int)($user['tbl'] ?? 0)
            ];
            
            $_SESSION['profile_img'] = 'default.png';

            // ສົ່ງທຸກຄົນໄປທີ່ໜ້າ dashboard.php (Unified Dashboard)
            $redirect = '../home/dashboard.php';
            
            logActivity($pdo, "ເຂົ້າສູ່ລະບົບ", "ຊື່ຜູ້ໃຊ້: " . $user['username']);

            echo json_encode([
                'success' => true, 
                'redirect' => $redirect
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ລະຫັດບໍ່ຖືກຕ້ອງ ກະລຸນາລອງໃໝ່']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ລະຫັດບໍ່ຖືກຕ້ອງ ກະລຸນາລອງໃໝ່']);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'ລະຫັດບໍ່ຖືກຕ້ອງ ກະລຸນາລອງໃໝ່']);
}
