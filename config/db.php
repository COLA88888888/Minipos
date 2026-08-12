<?php
date_default_timezone_set('Asia/Vientiane');
$server = "localhost";
$username = "root";
$password = "";
$database = "minipos";

$conn = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($conn, "utf8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($conn && isset($_SESSION['user_id'])) {
    $session_user_id = mysqli_real_escape_string($conn, (string)$_SESSION['user_id']);
    // ອັບເດດຂໍ້ມູນສະຖານະ ແລະ ສິດການໃຊ້ງານຈາກຕາຕະລາງ tbuser ຕາມ user_id
    $refresh_sql = "SELECT * FROM tbuser WHERE Id = '$session_user_id' LIMIT 1";
    $refresh_result = mysqli_query($conn, $refresh_sql);
    if ($refresh_result && $refresh_row = mysqli_fetch_assoc($refresh_result)) {
        $userStatusVal = $refresh_row['status'] ?? $refresh_row['userstatus'] ?? '';
        $isAdmin = (
            strtolower($userStatusVal) === 'admin' ||
            $userStatusVal === 'ຜູ້ບໍລິຫານ' ||
            strtolower($refresh_row['username'] ?? '') === 'admin' ||
            ($refresh_row['Id'] ?? 0) == 1
        );
        $_SESSION['status'] = $isAdmin ? 'ຜູ້ບໍລິຫານ' : ($userStatusVal ?: 'ພະນັກງານ');
        $_SESSION['username'] = $refresh_row['username'] ?? ($_SESSION['username'] ?? 'user');
        $_SESSION['store_id'] = (int)($refresh_row['store_id'] ?? $refresh_row['branch_id'] ?? 1);
        $_SESSION['permissions'] = [
            'sale' => $isAdmin ? 1 : (int)($refresh_row['sale'] ?? 0),
            'stock' => $isAdmin ? 1 : (int)($refresh_row['stock'] ?? 0),
            'report' => $isAdmin ? 1 : (int)($refresh_row['report'] ?? 0),
            'accounting' => $isAdmin ? 1 : (int)($refresh_row['accounting'] ?? 0),
            'setup' => $isAdmin ? 1 : (int)($refresh_row['setup'] ?? 0),
            'users' => $isAdmin ? 1 : (int)($refresh_row['users'] ?? 0),
            'permissions' => $isAdmin ? 1 : (int)($refresh_row['users'] ?? 0),
            'edit' => $isAdmin ? 1 : (int)($refresh_row['edit'] ?? 0),
            'cafe' => $isAdmin ? 1 : (int)($refresh_row['cafe'] ?? 0),
            'order' => $isAdmin ? 1 : (int)($refresh_row['order'] ?? 0),
            'kitchen' => $isAdmin ? 1 : (int)($refresh_row['kitchen'] ?? 0),
            'tbl' => $isAdmin ? 1 : (int)($refresh_row['tbl'] ?? 0)
        ];
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$server};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Migration for tbstore table & store_id
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbstore` (
            `store_id` INT AUTO_INCREMENT PRIMARY KEY,
            `store_code` VARCHAR(50) NOT NULL UNIQUE,
            `store_name` VARCHAR(150) NOT NULL,
            `address` TEXT NULL,
            `tel` VARCHAR(50) NULL,
            `status` VARCHAR(20) DEFAULT 'active',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $storeCount = (int)$pdo->query("SELECT COUNT(*) FROM tbstore")->fetchColumn();
        if ($storeCount === 0) {
            $pdo->exec("INSERT INTO tbstore (store_id, store_code, store_name, address, tel, status) 
                        VALUES (1, 'ST-001', 'ຮ້ານຕົ້ນແບບ / Main Store', 'ນະຄອນຫຼວງວຽງຈັນ', '020-00000000', 'active')");
        }
    } catch (Throwable $ex) {}

    try {
        $pdo->exec("DROP TABLE IF EXISTS `tbbranch`");
    } catch (Throwable $ex) {}

    try {
        $hasBranchId = $pdo->query("SHOW COLUMNS FROM tbuser LIKE 'branch_id'")->fetch();
        if ($hasBranchId) {
            $pdo->exec("ALTER TABLE tbuser CHANGE COLUMN `branch_id` `store_id` INT(11) DEFAULT 1");
        }
    } catch (Throwable $ex) {}

    // Migration for tbuser columns: drop userid, rename userpass->password, userstatus->status
    try {
        $hasUserid = $pdo->query("SHOW COLUMNS FROM tbuser LIKE 'userid'")->fetch();
        if ($hasUserid) {
            $pdo->exec("ALTER TABLE tbuser DROP COLUMN `userid`");
        }
    } catch (Throwable $ex) {}

    try {
        $hasUserpass = $pdo->query("SHOW COLUMNS FROM tbuser LIKE 'userpass'")->fetch();
        if ($hasUserpass) {
            $pdo->exec("ALTER TABLE tbuser CHANGE COLUMN `userpass` `password` VARCHAR(255) DEFAULT NULL");
        }
    } catch (Throwable $ex) {}

    try {
        $hasUserstatus = $pdo->query("SHOW COLUMNS FROM tbuser LIKE 'userstatus'")->fetch();
        if ($hasUserstatus) {
            $pdo->exec("ALTER TABLE tbuser CHANGE COLUMN `userstatus` `status` VARCHAR(50) DEFAULT NULL");
        }
    } catch (Throwable $ex) {}

    // Migration for customers table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `customers` (
            `customer_id` INT AUTO_INCREMENT PRIMARY KEY,
            `customer_code` VARCHAR(50) NOT NULL UNIQUE,
            `customer_name` VARCHAR(150) NOT NULL,
            `phone` VARCHAR(50) NULL,
            `email` VARCHAR(100) NULL,
            `address` TEXT NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $custCount = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        if ($custCount === 0) {
            $pdo->exec("INSERT INTO customers (customer_code, customer_name, phone, address, notes) 
                        VALUES ('CUST-001', 'ລູກຄ້າທົ່ວໄປ / General Customer', '020-00000000', 'ນະຄອນຫຼວງວຽງຈັນ', 'ລູກຄ້າທົ່ວໄປເລີ່ມຕົ້ນ')");
        }
    } catch (Throwable $ex) {}

    // Auto Migration for User Detail Columns
    $userCols = [
        'user_code' => "VARCHAR(50) DEFAULT NULL",
        'fname' => "VARCHAR(100) DEFAULT NULL",
        'lname' => "VARCHAR(100) DEFAULT NULL",
        'gender' => "VARCHAR(20) DEFAULT NULL",
        'dob' => "DATE DEFAULT NULL",
        'tel' => "VARCHAR(30) DEFAULT NULL",
        'password' => "VARCHAR(255) DEFAULT NULL",
        'status' => "VARCHAR(50) DEFAULT 'ພະນັກງານ'",
        'store_id' => "INT(11) DEFAULT 1",
        'address' => "TEXT DEFAULT NULL",
        'notes' => "TEXT DEFAULT NULL",
        'profile_img' => "VARCHAR(255) DEFAULT 'default.png'",
        'accounting' => "TINYINT(1) DEFAULT 0",
        'customers' => "TINYINT(1) DEFAULT 1"
    ];
    foreach ($userCols as $col => $def) {
        try {
            $colCheck = $pdo->query("SHOW COLUMNS FROM tbuser LIKE '{$col}'")->fetch();
            if (!$colCheck) {
                $pdo->exec("ALTER TABLE tbuser ADD COLUMN `{$col}` {$def}");
            }
        } catch (Throwable $ex) {}
    }

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
            `category_id` INT AUTO_INCREMENT PRIMARY KEY,
            `category_name` VARCHAR(150) NOT NULL,
            `description` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $ex) {}

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `shelves` (
            `shelf_id` INT AUTO_INCREMENT PRIMARY KEY,
            `shelf_name` VARCHAR(150) NOT NULL,
            `category_id` INT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $shelfCount = (int)$pdo->query("SELECT COUNT(*) FROM shelves")->fetchColumn();
        if ($shelfCount === 0) {
            $pdo->exec("INSERT INTO shelves (shelf_name, category_id) VALUES 
                ('ຕູ້ແຊ່ເຄື່ອງດື່ມ A1', 1),
                ('ຊັ້ນວາງຂະໜົມ B1', 2)");
        }
    } catch (Throwable $ex) {}

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS accounting_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            record_type VARCHAR(20) NOT NULL DEFAULT 'expense',
            category VARCHAR(100) NOT NULL,
            amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            record_date DATE NOT NULL,
            note TEXT NULL,
            created_by INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $ex) {}

    try {
        $hasSize = $pdo->query("SHOW COLUMNS FROM products LIKE 'size'")->fetch();
        if ($hasSize) {
            $pdo->exec("ALTER TABLE products CHANGE COLUMN `size` `unit` VARCHAR(100) DEFAULT NULL");
        }
        $hasUnit = $pdo->query("SHOW COLUMNS FROM products LIKE 'unit'")->fetch();
        if (!$hasUnit) {
            $pdo->exec("ALTER TABLE products ADD COLUMN `unit` VARCHAR(100) DEFAULT NULL");
        }

        // ລົບຟິວພາສາອັງກິດ (_en) ທີ່ບໍ່ໄດ້ໃຊ້ງານ
        $pdo->exec("ALTER TABLE category DROP COLUMN IF EXISTS category_name_en");
        $pdo->exec("ALTER TABLE products DROP COLUMN IF EXISTS product_name_en");
        $pdo->exec("ALTER TABLE tbcompanyinfo DROP COLUMN IF EXISTS com_name_en");

        // ສ້າງຕາຕະລາງ product_units ສຳລັບສິນຄ້າທີ່ມີຫຼາຍລາຄາ/ຫຼາຍຫົວໜ່ວຍ
        $pdo->exec("CREATE TABLE IF NOT EXISTS product_units (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            unit_name VARCHAR(50) NOT NULL,
            multiplier INT NOT NULL DEFAULT 1,
            bprice DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            barcode VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // ສ້າງຕາຕະລາງ imports ສຳລັບໃບບິນຮັບສິນຄ້າເຂົ້າ
        $pdo->exec("CREATE TABLE IF NOT EXISTS imports (
            import_id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_number VARCHAR(100) NOT NULL,
            supplier_name VARCHAR(150) DEFAULT NULL,
            total_cost DECIMAL(15,2) DEFAULT 0.00,
            created_by INT DEFAULT NULL,
            import_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            notes TEXT DEFAULT NULL,
            INDEX (invoice_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // ສ້າງຕາຕະລາງ import_details ສຳລັບລາຍລະອຽດສິນຄ້ານຳເຂົ້າ
        $pdo->exec("CREATE TABLE IF NOT EXISTS import_details (
            import_detail_id INT AUTO_INCREMENT PRIMARY KEY,
            import_id INT NOT NULL,
            product_id INT NOT NULL,
            unit_name VARCHAR(50) DEFAULT NULL,
            multiplier INT DEFAULT 1,
            quantity INT NOT NULL DEFAULT 1,
            total_base_qty INT NOT NULL DEFAULT 1,
            cost_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            total_cost DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            expiry_date DATE DEFAULT NULL,
            INDEX (import_id),
            INDEX (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // ສ້າງຕາຕະລາງ product_batches ສຳລັບຕິດຕາມລັອດສິນຄ້າ ແລະ ວັນໝົດອາຍຸ
        $pdo->exec("CREATE TABLE IF NOT EXISTS product_batches (
            batch_id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            import_detail_id INT DEFAULT NULL,
            expiry_date DATE DEFAULT NULL,
            initial_qty INT NOT NULL DEFAULT 0,
            quantity INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (product_id),
            INDEX (expiry_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $ex) {}

    // Auto Migration for system_settings table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `system_settings` (
            `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $setCount = (int)$pdo->query("SELECT COUNT(*) FROM system_settings")->fetchColumn();
        if ($setCount === 0) {
            $pdo->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES 
                ('vat_rate', '0'),
                ('expiry_warning_days', '30')");
        }
    } catch (Throwable $ex) {}

} catch (PDOException $e) {
    $pdo = null;
}

if (!function_exists('getSetting')) {
    function getSetting($pdo, $key, $default = '') {
        if (!$pdo) return $default;
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null) ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        return number_format((float)$amount, 0, '.', ',') . ' ₭';
    }
}

if (!function_exists('logDebug')) {
    function logDebug($message)
    {
        // debug logging helper
    }
}

if (!function_exists('logActivity')) {
    function logActivity($pdo, $action, $detail = '')
    {
        if (!$pdo) {
            return;
        }
        try {
            // ຄຳສັ່ງ SQL: ບັນທຶກປະຫວັດການເຮັດວຽກຂອງຜູ້ໃຊ້ (ເຊັ່ນ: ການເຂົ້າສູ່ລະບົບ, ການເພີ່ມ/ລົບ/ແກ້ໄຂຂໍ້ມູນ) ລົງໃນຕາຕະລາງ activity_logs
            $stmt = $pdo->prepare('INSERT INTO activity_logs (action, detail, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$action, $detail]);
        } catch (Exception $e) {
            // activity_logs table may not exist in all deployments
        }
    }
}

// === Permission Helper Functions ===
if (!function_exists('hasPermission')) {
    function hasPermission($module, $action = 'view') {
        if (!isset($_SESSION['checked']) || empty($_SESSION['checked'])) return false;
        
        $status = $_SESSION['status'] ?? '';
        
        // 1. Admin (ຜູ້ບໍລິຫານ): Full access to all modules and actions
        if ($status === 'ຜູ້ບໍລິຫານ' || strtolower($status) === 'admin' || strtolower($_SESSION['username'] ?? '') === 'admin' || ($_SESSION['user_id'] ?? null) == 1) {
            return true;
        }
        
        // 2. Module alias mappings
        $aliasMap = [
            'dashboard' => ['sale', 'stock', 'report', 'setup', 'users'], // Any access grants dashboard
            'pos' => 'sale',
            'categories' => 'stock',
            'products' => 'stock',
            'import' => 'stock',
            'import_stock' => 'stock',
            'stock_check' => 'stock',
            'expiry_check' => 'stock',
            'reports' => 'report',
            'financial' => 'report',
            'accounting' => ['accounting', 'report'],
            'settings' => 'setup',
            'permissions' => 'users',
            'user_manage' => 'users',
            'customers' => ['customers', 'sale', 'users']
        ];
        
        $perms = $_SESSION['permissions'] ?? [];
        if (is_string($perms)) {
            $decoded = json_decode($perms, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $perms = $decoded;
            }
        }
        
        if (!is_array($perms)) {
            $perms = [];
        }
        
        // Check direct flag in array (e.g. ['sale' => 1])
        if (isset($perms[$module]) && ($perms[$module] === 1 || $perms[$module] === '1' || $perms[$module] === true)) {
            return true;
        }
        
        // Check mapped alias
        if (isset($aliasMap[$module])) {
            $target = $aliasMap[$module];
            if (is_array($target)) {
                foreach ($target as $t) {
                    if (!empty($perms[$t])) {
                        return true;
                    }
                }
            } elseif (!empty($perms[$target])) {
                return true;
            }
        }
        
        // Check if $perms is a list of module strings: e.g. ["pos", "products"]
        if (in_array($module, $perms, true)) {
            return true;
        }
        
        // Check granular array format: ['stock' => ['view' => 1, 'add' => 1]]
        if (isset($perms[$module]) && is_array($perms[$module])) {
            return !empty($perms[$module][$action]);
        }
        
        return false;
    }
}

if (!function_exists('getPermissionLimit')) {
    function getPermissionLimit($module) {
        return 0; // Visibility limit feature removed, always return 0 (no limit)
    }
}
?>