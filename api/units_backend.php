<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../config/db.php';

// Units now has its own permission module (wired the same way as categories in
// pages/permissions), separate from hasPermission('categories', ...).
if (empty($_SESSION['user_id']) || (!hasPermission('units') && !hasPermission('stock') && ($_SESSION['status'] ?? '') !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

$unit_message = '';
$unit_message_type = '';

// Handle Unit Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $unitActiveStoreId = getActiveStoreId($pdo);

        if ($_POST['action'] === 'add_unit') {
            $name = trim($_POST['unit_name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $unit_id = isset($_POST['unit_id']) && $_POST['unit_id'] !== '' ? intval($_POST['unit_id']) : null;
            if ($name !== '' && $unit_id !== null && $unit_id > 0) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO units (unit_id, unit_name, description, store_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$unit_id, $name, $desc, $unitActiveStoreId]);

                    // A unit added from the main branch is also created in every sub-branch
                    // (own auto-generated unit_id per branch), same propagation rule as
                    // categories in api/categories_backend.php.
                    if (isMainBranch($pdo, $unitActiveStoreId)) {
                        $subStoresStmt = $pdo->prepare("SELECT store_id FROM tbstore WHERE store_id != ?");
                        $subStoresStmt->execute([$unitActiveStoreId]);
                        $subInsertStmt = $pdo->prepare("INSERT INTO units (unit_name, description, store_id) VALUES (?, ?, ?)");
                        foreach ($subStoresStmt->fetchAll(PDO::FETCH_COLUMN) as $subStoreId) {
                            $subInsertStmt->execute([$name, $desc, $subStoreId]);
                        }
                    }

                    $unit_message = 'ເພີ່ມຫົວໜ່ວຍສຳເລັດ!';
                    $unit_message_type = 'success';
                    logActivity($pdo, "ເພີ່ມຫົວໜ່ວຍ", "ຊື່: $name");
                } catch (Exception $e) {
                    $unit_message = 'ຜິດພາດ: ລະຫັດນີ້ອາດຊ້ຳກັນ ຫຼື ' . $e->getMessage();
                    $unit_message_type = 'danger';
                }
            } else {
                $unit_message = 'ກະລຸນາປ້ອນລະຫັດ ແລະ ຊື່ຫົວໜ່ວຍໃຫ້ຄົບ!';
                $unit_message_type = 'danger';
            }
        }
        elseif ($_POST['action'] === 'edit_unit') {
            $id = intval($_POST['unit_id'] ?? 0);
            $name = trim($_POST['unit_name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            if ($id > 0 && $name !== '') {
                try {
                    // Grab the pre-edit name so a main-branch edit can find the matching
                    // rows in sub-branches (each branch holds its own unit_id, so the only
                    // thing linking "the same" unit across branches is its name).
                    $oldNameStmt = $pdo->prepare("SELECT unit_name FROM units WHERE unit_id = ? AND store_id = ?");
                    $oldNameStmt->execute([$id, $unitActiveStoreId]);
                    $oldName = $oldNameStmt->fetchColumn();

                    // Scoped to this branch's own unit — a sub-branch user can't edit another branch's row
                    $stmt = $pdo->prepare("UPDATE units SET unit_name = ?, description = ? WHERE unit_id = ? AND store_id = ?");
                    $stmt->execute([$name, $desc, $id, $unitActiveStoreId]);

                    // A unit edited from the main branch also renames the matching unit in
                    // every sub-branch, so products there keep showing the updated name.
                    if ($oldName !== false && $oldName !== '' && isMainBranch($pdo, $unitActiveStoreId)) {
                        $subUpdateStmt = $pdo->prepare("UPDATE units SET unit_name = ?, description = ? WHERE unit_name = ? AND store_id != ?");
                        $subUpdateStmt->execute([$name, $desc, $oldName, $unitActiveStoreId]);
                    }

                    $unit_message = 'ແກ້ໄຂຫົວໜ່ວຍສຳເລັດ!';
                    $unit_message_type = 'success';
                    logActivity($pdo, "ແກ້ໄຂຫົວໜ່ວຍ", "ID: $id, ຊື່ໃໝ່: $name");
                } catch (Exception $e) {
                    $unit_message = 'ຜິດພາດ: ' . $e->getMessage();
                    $unit_message_type = 'danger';
                }
            }
        }
        elseif ($_POST['action'] === 'delete_unit') {
            $id = intval($_POST['unit_id'] ?? 0);
            if ($id > 0) {
                // products.unit is a free-text field (not an FK), so "in use" is matched by name
                $nameStmt = $pdo->prepare("SELECT unit_name FROM units WHERE unit_id = ? AND store_id = ?");
                $nameStmt->execute([$id, $unitActiveStoreId]);
                $unitName = $nameStmt->fetchColumn();

                $count = 0;
                if ($unitName !== false && $unitName !== '') {
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE unit = ?");
                    $checkStmt->execute([$unitName]);
                    $count = (int)$checkStmt->fetchColumn();
                }

                if ($count > 0) {
                    $unit_message = 'ບໍ່ສາມາດລົບຫົວໜ່ວຍນີ້ໄດ້! ເພາະມີລາຍການສິນຄ້າໃຊ້ຫົວໜ່ວຍນີ້ ' . $count . ' ລາຍການ.';
                    $unit_message_type = 'danger';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM units WHERE unit_id = ? AND store_id = ?");
                        $stmt->execute([$id, $unitActiveStoreId]);
                        $unit_message = 'ລົບຫົວໜ່ວຍສຳເລັດ!';
                        $unit_message_type = 'success';
                        logActivity($pdo, "ລົບຫົວໜ່ວຍ", "ID: $id");
                    } catch (Exception $e) {
                        $unit_message = 'ຜິດພາດ: ' . $e->getMessage();
                        $unit_message_type = 'danger';
                    }
                }
            }
        }
    }
}

// Fetch Units scoped to the active branch only — same shape as api/categories_backend.php.
// product_count matches by name (products.unit is free-text, not an FK to units.unit_id) —
// explicit COLLATE needed since products.unit (utf8mb4_unicode_ci) and units.unit_name
// (utf8mb4_general_ci, the table's default collation) don't match.
$activeStoreIdForUnit = getActiveStoreId($pdo);
$stmtUnit = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM products p WHERE p.unit = u.unit_name COLLATE utf8mb4_unicode_ci) AS product_count FROM units u WHERE u.store_id = ? ORDER BY u.unit_id DESC");
$stmtUnit->execute([$activeStoreIdForUnit]);
$allUnits = $stmtUnit->fetchAll();

// Next available unit_id (MAX + 1 across all branches) — unit_id is a shared primary key,
// so this must stay global to avoid two branches generating the same new ID.
$next_unit_id = (int)$pdo->query("SELECT IFNULL(MAX(unit_id), 0) + 1 FROM units")->fetchColumn();
