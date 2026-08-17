<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../config/db.php';

// Check permissions
if (empty($_SESSION['user_id']) || (!hasPermission('categories') && !hasPermission('stock') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$message = '';
$message_type = '';

// Handle Category Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_category') {
            $name = trim($_POST['category_name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $cat_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? intval($_POST['category_id']) : null;
            if ($name !== '' && $cat_id !== null && $cat_id > 0) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO categories (category_id, category_name, description) VALUES (?, ?, ?)");
                    $stmt->execute([$cat_id, $name, $desc]);
                    $message = 'ເພີ່ມປະເພດສິນຄ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ເພີ່ມປະເພດສິນຄ້າ", "ຊື່: $name");
                } catch (Exception $e) {
                    $message = 'ຜິດພາດ: ລະຫັດນີ້ອາດຊ້ຳກັນ ຫຼື ' . $e->getMessage();
                    $message_type = 'danger';
                }
            } else {
                $message = 'ກະລຸນາປ້ອນລະຫັດ ແລະ ຊື່ປະເພດສິນຄ້າໃຫ້ຄົບ!';
                $message_type = 'danger';
            }
        }
        elseif ($_POST['action'] === 'edit_category') {
            $id = intval($_POST['category_id'] ?? 0);
            $name = trim($_POST['category_name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            if ($id > 0 && $name !== '') {
                try {
                    $stmt = $pdo->prepare("UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?");
                    $stmt->execute([$name, $desc, $id]);
                    $message = 'ແກ້ໄຂປະເພດສິນຄ້າສຳເລັດ!';
                    $message_type = 'success';
                    logActivity($pdo, "ແກ້ໄຂປະເພດສິນຄ້າ", "ID: $id, ຊື່ໃໝ່: $name");
                } catch (Exception $e) {
                    $message = 'ຜິດພາດ: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        }
        elseif ($_POST['action'] === 'delete_category') {
            $id = intval($_POST['category_id'] ?? 0);
            if ($id > 0) {
                // Check if category has products inside
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $checkStmt->execute([$id]);
                $count = (int)$checkStmt->fetchColumn();

                if ($count > 0) {
                    $message = 'ບໍ່ສາມາດລົບປະເພດສິນຄ້ານີ້ໄດ້! ເພາະມີລາຍການສິນຄ້າໃນໝວດໝູ່ນີ້ ' . $count . ' ລາຍການ.';
                    $message_type = 'danger';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
                        $stmt->execute([$id]);
                        $message = 'ລົບປະເພດສິນຄ້າສຳເລັດ!';
                        $message_type = 'success';
                        logActivity($pdo, "ລົບປະເພດສິນຄ້າ", "ID: $id");
                    } catch (Exception $e) {
                        $message = 'ຜິດພາດ: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
            }
        }
    }
}

// Fetch All Categories With Product Count
$stmtCat = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) AS product_count FROM categories c ORDER BY c.category_id DESC");
$allCategories = $stmtCat->fetchAll();
$total_records = count($allCategories);

// Next available ID (MAX + 1, or 1 if empty)
$next_cat_id = (int)$pdo->query("SELECT IFNULL(MAX(category_id), 0) + 1 FROM categories")->fetchColumn();
