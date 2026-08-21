<?php
date_default_timezone_set('Asia/Vientiane');
$server = "localhost";
$username = "root";
$password = "";
$database = "minipos";

$conn = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($conn, "utf8");

if (session_status() === PHP_SESSION_NONE) {
    // ຕັ້ງຄ່າ Session Cookie ໃຫ້ເປັນ 0 (Expired ເມື່ອປິດ Browser)
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true
    ]);
    session_start();
}

// 15 Minutes Inactivity Idle Timeout (900 seconds)
$maxIdleTime = 900;
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxIdleTime)) {
        session_unset();
        session_destroy();
        $isPageDir = (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/pages/') !== false || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/home/') !== false);
        $redirectLoginPath = $isPageDir ? '../auth/login.php?expired=1' : 'auth/login.php?expired=1';
        echo "<script>if (window.top) { window.top.location.href = '{$redirectLoginPath}'; } else { window.location.href = '{$redirectLoginPath}'; }</script>";
        exit();
    }
    $_SESSION['last_activity'] = time();
}
if (!empty($_SESSION['user_id'])) {
    $session_user_id = intval($_SESSION['user_id']);
    $nowVientiane = date('Y-m-d H:i:s');
    // ອັບເດດເວລາເຄື່ອນໄຫວລ້າສຸດ (Online activity status for current logged-in user)
    try {
        if (!empty($pdo)) {
            $stmtAct = $pdo->prepare("UPDATE tbuser SET last_activity = ? WHERE Id = ?");
            $stmtAct->execute([$nowVientiane, $session_user_id]);
        } elseif ($conn) {
            @mysqli_query($conn, "UPDATE tbuser SET last_activity = '$nowVientiane' WHERE Id = '$session_user_id'");
        }
    } catch (Throwable $ex) {}
}
if ($conn && !empty($_SESSION['user_id'])) {
    $session_user_id = mysqli_real_escape_string($conn, (string)$_SESSION['user_id']);
    // ອັບເດດຂໍ້ມູນສະຖານະ ແລະ ສິດການໃຊ້ງານຈາກຕາຕະລາງ tbuser ຕາມ user_id
    $refresh_sql = "SELECT * FROM tbuser WHERE Id = '$session_user_id' LIMIT 1";
    $refresh_result = mysqli_query($conn, $refresh_sql);
    if ($refresh_result && $refresh_row = mysqli_fetch_assoc($refresh_result)) {
        $userStatusVal = $refresh_row['status'] ?? $refresh_row['userstatus'] ?? '';
        $isAdmin = (
            ($refresh_row['Id'] ?? 0) == 1 ||
            strtolower($userStatusVal) === 'admin' ||
            strtolower($userStatusVal) === 'super admin' ||
            $userStatusVal === 'ຜູ້ບໍລິຫານ'
        );
        $_SESSION['status'] = $isAdmin ? 'ຜູ້ບໍລິຫານ' : ($userStatusVal ?: 'ພະນັກງານ');
        $_SESSION['username'] = $refresh_row['username'] ?? ($_SESSION['username'] ?? 'user');
        $_SESSION['store_id'] = (int)($refresh_row['store_id'] ?? $refresh_row['branch_id'] ?? 1);
        $_SESSION['permissions'] = [
            'dashboard' => $isAdmin ? 1 : (int)($refresh_row['dashboard'] ?? 0),
            'sale' => $isAdmin ? 1 : (int)($refresh_row['sale'] ?? 0),
            'item_sales' => $isAdmin ? 1 : (int)($refresh_row['item_sales'] ?? $refresh_row['sale'] ?? 0),
            'stock' => $isAdmin ? 1 : (int)($refresh_row['stock'] ?? 0),
            'categories' => $isAdmin ? 1 : (int)($refresh_row['categories'] ?? 0),
            'products' => $isAdmin ? 1 : (int)($refresh_row['products'] ?? 0),
            'import_stock' => $isAdmin ? 1 : (int)($refresh_row['import_stock'] ?? 0),
            'import_list' => $isAdmin ? 1 : (int)($refresh_row['import_list'] ?? 0),
            'report' => $isAdmin ? 1 : (int)($refresh_row['report'] ?? 0),
            'daily_report' => $isAdmin ? 1 : (int)($refresh_row['daily_report'] ?? $refresh_row['report'] ?? 0),
            'all_sales' => $isAdmin ? 1 : (int)($refresh_row['all_sales'] ?? $refresh_row['report'] ?? 0),
            'best_seller' => $isAdmin ? 1 : (int)($refresh_row['best_seller'] ?? $refresh_row['report'] ?? 0),
            'profit_cost' => $isAdmin ? 1 : (int)($refresh_row['profit_cost'] ?? $refresh_row['report'] ?? 0),
            'financial' => $isAdmin ? 1 : (int)($refresh_row['financial'] ?? $refresh_row['report'] ?? 0),
            'category_sales' => $isAdmin ? 1 : (int)($refresh_row['category_sales'] ?? $refresh_row['report'] ?? 0),
            'delete_bills' => $isAdmin ? 1 : (int)($refresh_row['delete_bills'] ?? 0),
            'accounting' => $isAdmin ? 1 : (int)($refresh_row['accounting'] ?? 0),
            'setup' => $isAdmin ? 1 : (int)($refresh_row['setup'] ?? 0),
            'stores' => $isAdmin ? 1 : (int)($refresh_row['stores'] ?? $refresh_row['setup'] ?? 0),
            'print_barcode' => $isAdmin ? 1 : (int)($refresh_row['print_barcode'] ?? $refresh_row['setup'] ?? 0),
            'exchange_rate' => $isAdmin ? 1 : (int)($refresh_row['exchange_rate'] ?? $refresh_row['setup'] ?? 0),
            'promotions' => $isAdmin ? 1 : (int)($refresh_row['promotions'] ?? $refresh_row['setup'] ?? 0),
            'price_adjustment' => $isAdmin ? 1 : (int)($refresh_row['price_adjustment'] ?? $refresh_row['setup'] ?? 0),
            'printers' => $isAdmin ? 1 : (int)($refresh_row['printers'] ?? $refresh_row['setup'] ?? 0),
            'users' => $isAdmin ? 1 : (int)($refresh_row['users'] ?? 0),
            'permissions' => $isAdmin ? 1 : (int)($refresh_row['permissions'] ?? 0),
            'branches' => $isAdmin ? 1 : (int)($refresh_row['branches'] ?? 0),
            'edit' => $isAdmin ? 1 : (int)($refresh_row['edit'] ?? 0),
            'customers' => $isAdmin ? 1 : (int)($refresh_row['customers'] ?? 0),
            'database' => $isAdmin ? 1 : (int)($refresh_row['database'] ?? 0),
            'stock_transfer' => $isAdmin ? 1 : (int)($refresh_row['stock_transfer'] ?? 0),
            'transfer_history' => $isAdmin ? 1 : (int)($refresh_row['transfer_history'] ?? 0)
        ];

        // Refresh switch states from database table user_permission_switch_states
        $uid = intval($_SESSION['user_id']);
        $swStates = [];
        $swRes = mysqli_query($conn, "SELECT switch_key, is_enabled FROM user_permission_switch_states WHERE user_id = '$uid'");
        if ($swRes) {
            while ($swRow = mysqli_fetch_assoc($swRes)) {
                $swStates[$swRow['switch_key']] = (int)$swRow['is_enabled'];
            }
        }
        $_SESSION['switch_states'] = $swStates;

        // Enforce active branch status check (log out non-admin user if their branch is disabled/inactive)
        if (!$isAdmin) {
            $userBranchId = (int)($_SESSION['store_id'] ?? 1);
            $chkBranchRes = mysqli_query($conn, "SELECT status, store_name FROM tbstore WHERE store_id = '$userBranchId' LIMIT 1");
            if ($chkBranchRes && $bRow = mysqli_fetch_assoc($chkBranchRes)) {
                if (($bRow['status'] ?? '') !== 'active') {
                    // Destroy session and redirect to login
                    session_unset();
                    session_destroy();
                    $bName = htmlspecialchars($bRow['store_name'] ?? 'ສາຂານີ້');
                    $baseLoginPath = (basename($scriptDir) === 'pages') ? '../login.php' : '../../login.php';
                    echo "<script>alert('ສາຂາ ({$bName}) ຖືກປິດໃຊ້ງານຊົ່ວຄາວ! ລະບົບຈະອອກຈາກປະຕູເຂົ້າໃຊ້ງານ.'); window.location.href = '{$baseLoginPath}';</script>";
                    exit();
                }
            }
        }

        // Multi-Branch Store Switcher Handler for Executive / Admin Users
        if (isset($_GET['switch_store_id'])) {
            $switchId = intval($_GET['switch_store_id']);
            if ($isAdmin) {
                $_SESSION['active_store_id'] = $switchId;
            }
        }
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
            `is_main` TINYINT(1) DEFAULT 0,
            `address` TEXT NULL,
            `tel` VARCHAR(50) NULL,
            `status` VARCHAR(20) DEFAULT 'active',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $storeCount = (int)$pdo->query("SELECT COUNT(*) FROM tbstore")->fetchColumn();
        if ($storeCount === 0) {
            $pdo->exec("INSERT INTO tbstore (store_id, store_code, store_name, is_main, address, tel, status) 
                        VALUES (1, '1', 'ສາຂາໃຫຍ່ ວຽງຈັນ (HQ)', 1, 'ນະຄອນຫຼວງວຽງຈັນ', '020-55555555', 'active')");
        }

        // Auto update ST-001 to numeric '1'
        $pdo->exec("UPDATE tbstore SET store_code = '1' WHERE store_id = 1 AND store_code = 'ST-001'");

        // Ensure is_main column exists
        $hasMain = $pdo->query("SHOW COLUMNS FROM `tbstore` LIKE 'is_main'")->fetch();
        if (!$hasMain) {
            $pdo->exec("ALTER TABLE `tbstore` ADD COLUMN `is_main` TINYINT(1) DEFAULT 0 AFTER `store_name`");
        }
        $pdo->exec("UPDATE `tbstore` SET is_main = 1 WHERE store_id = 1");
    } catch (Throwable $ex) {}

    // Ensure store_id column exists in all core data tables for multi-branch isolation
    $coreTablesForStore = ['sales', 'tbsale_save', 'products', 'accounting_records', 'imports', 'customers', 'tbuser'];
    foreach ($coreTablesForStore as $tblName) {
        try {
            $hasCol = $pdo->query("SHOW COLUMNS FROM `{$tblName}` LIKE 'store_id'")->fetch();
            if (!$hasCol) {
                $pdo->exec("ALTER TABLE `{$tblName}` ADD COLUMN `store_id` INT(11) NOT NULL DEFAULT 1");
            }
        } catch (Throwable $ex) {}
    }

    // Ensure all permission columns exist in tbuser
    $allPermCols = ['dashboard', 'sale', 'stock', 'report', 'accounting', 'setup', 'users', 'permissions', 'edit', 'customers', 'branches', 'database'];
    foreach ($allPermCols as $pCol) {
        try {
            $hasCol = $pdo->query("SHOW COLUMNS FROM `tbuser` LIKE '{$pCol}'")->fetch();
            if (!$hasCol) {
                $pdo->exec("ALTER TABLE `tbuser` ADD COLUMN `{$pCol}` TINYINT(1) DEFAULT 0");
            }
        } catch (Throwable $ex) {}
    }

    // Ensure Tax/VAT columns exist in tbcompanyinfo and sales tables
    try {
        $hasVat = $pdo->query("SHOW COLUMNS FROM `tbcompanyinfo` LIKE 'vat_percent'")->fetch();
        if (!$hasVat) {
            $pdo->exec("ALTER TABLE `tbcompanyinfo` ADD COLUMN `vat_percent` DECIMAL(5,2) DEFAULT 7.00");
        }
        $hasTaxType = $pdo->query("SHOW COLUMNS FROM `tbcompanyinfo` LIKE 'tax_type'")->fetch();
        if (!$hasTaxType) {
            $pdo->exec("ALTER TABLE `tbcompanyinfo` ADD COLUMN `tax_type` VARCHAR(20) DEFAULT 'inclusive'");
        }
        $hasSalesTaxType = $pdo->query("SHOW COLUMNS FROM `sales` LIKE 'tax_type'")->fetch();
        if (!$hasSalesTaxType) {
            $pdo->exec("ALTER TABLE `sales` ADD COLUMN `tax_type` VARCHAR(20) DEFAULT 'inclusive'");
        }
        $hasSalesVatRate = $pdo->query("SHOW COLUMNS FROM `sales` LIKE 'vat_rate'")->fetch();
        if (!$hasSalesVatRate) {
            $pdo->exec("ALTER TABLE `sales` ADD COLUMN `vat_rate` DECIMAL(5,2) DEFAULT 7.00");
        }
        $hasQrImg = $pdo->query("SHOW COLUMNS FROM `tbcompanyinfo` LIKE 'qr_img'")->fetch();
        if (!$hasQrImg) {
            $pdo->exec("ALTER TABLE `tbcompanyinfo` ADD COLUMN `qr_img` VARCHAR(255) NULL AFTER `img_url`");
        }
        $hasTaxId = $pdo->query("SHOW COLUMNS FROM `tbcompanyinfo` LIKE 'tax_id'")->fetch();
        if (!$hasTaxId) {
            $pdo->exec("ALTER TABLE `tbcompanyinfo` ADD COLUMN `tax_id` VARCHAR(100) NULL AFTER `com_email`");
        }
    } catch (Throwable $ex) {}

    // Add performance indexes if missing for lightning-fast queries
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_store ON products (store_id, category_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_barcode ON products (barcode)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_sales_store_date ON sales (store_id, created_at)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_sales_invoice ON sales (invoice_number)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_customers_store ON customers (store_id, customer_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_price_adj_branch ON price_adjustments (branch_id, adjust_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_promotions_branch ON promotions (branch_id, status)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_transfers_stores ON stock_transfers (from_store_id, to_store_id, status)");
    } catch (Throwable $ex) {}

    // Clean up unused/deprecated database tables
    $unusedTables = [
        'tbbranch', 'tbunit', 'tbsale', 'tbsale_detail', 'category', 'tbsupplier', 'tbcurrency',
        'customer', 'tb_expenses', 'tb_queue_daily', 'tbdeposit_beer', 'tbfinancial_report',
        'tbreceive', 'tbreceive_detail', 'tbsale_save_data', 'stock_adjustments', 'stock_adjustment_items',
        'price_adjustment_items', 'promotion_items', 'tb_delete_bill_log'
    ];
    foreach ($unusedTables as $uTbl) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `{$uTbl}`");
        } catch (Throwable $ex) {}
    }

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

        $custCols = $pdo->query("SHOW COLUMNS FROM customers")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('member_card', $custCols)) {
            $pdo->exec("ALTER TABLE `customers` ADD COLUMN `member_card` VARCHAR(50) NULL AFTER `phone`");
        }
        if (!in_array('store_id', $custCols)) {
            $pdo->exec("ALTER TABLE `customers` ADD COLUMN `store_id` INT DEFAULT 1 AFTER `notes`");
            $pdo->exec("UPDATE `customers` SET `store_id` = 1 WHERE `store_id` IS NULL OR `store_id` = 0");
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
        'last_activity' => "DATETIME DEFAULT NULL",
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

    // Ensure tbcompanyinfo exists and has DB record
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbcompanyinfo` (
            `com_id` INT AUTO_INCREMENT PRIMARY KEY,
            `com_name_la` VARCHAR(255) DEFAULT 'Mini POS Store',
            `com_address` TEXT DEFAULT NULL,
            `com_tel` VARCHAR(50) DEFAULT NULL,
            `barcode` TEXT DEFAULT NULL,
            `img_url` VARCHAR(255) DEFAULT 'logo.png',
            `license_start_date` DATE DEFAULT '2026-01-01',
            `license_expire_date` DATE DEFAULT '2026-12-31',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $hasStartCol = $pdo->query("SHOW COLUMNS FROM `tbcompanyinfo` LIKE 'license_start_date'")->fetch();
        if (!$hasStartCol) {
            $pdo->exec("ALTER TABLE `tbcompanyinfo` ADD COLUMN `license_start_date` DATE DEFAULT '2026-01-01'");
        }
        $hasExpireCol = $pdo->query("SHOW COLUMNS FROM `tbcompanyinfo` LIKE 'license_expire_date'")->fetch();
        if (!$hasExpireCol) {
            $pdo->exec("ALTER TABLE `tbcompanyinfo` ADD COLUMN `license_expire_date` DATE DEFAULT '2026-12-31'");
        }

        $compCount = (int)$pdo->query("SELECT COUNT(*) FROM tbcompanyinfo")->fetchColumn();
        if ($compCount === 0) {
            $pdo->exec("INSERT INTO tbcompanyinfo (com_name_la, com_address, com_tel, barcode, img_url, license_start_date, license_expire_date, branch_id) VALUES 
                ('', '', '', 'ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!', 'logo.png', '2026-01-01', '2026-12-31', 1)");
        }
    } catch (Throwable $ex) {}

    // Ensure tbstore exists and has DB record
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbstore` (
            `store_id` INT AUTO_INCREMENT PRIMARY KEY,
            `store_code` VARCHAR(50) DEFAULT 'STORE01',
            `store_name` VARCHAR(255) DEFAULT 'Mini POS Store',
            `address` TEXT DEFAULT NULL,
            `tel` VARCHAR(50) DEFAULT NULL,
            `logo_path` VARCHAR(255) DEFAULT 'assets/img/logo/logo.png',
            `status` VARCHAR(20) DEFAULT 'active',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $storeCount = (int)$pdo->query("SELECT COUNT(*) FROM tbstore")->fetchColumn();
        if ($storeCount === 0) {
            $pdo->exec("INSERT INTO tbstore (store_code, store_name, address, tel, logo_path, status) VALUES 
                ('STORE01', 'Mini POS Store', 'ນະຄອນຫຼວງວຽງຈັນ', '020-55555555', 'assets/img/logo/logo.png', 'active')");
        }
    } catch (Throwable $ex) {}

    // Ensure accounting_categories exists and has DB records
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `accounting_categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `category_name` VARCHAR(100) NOT NULL,
            `record_type` VARCHAR(20) NOT NULL DEFAULT 'expense',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $accCatCount = (int)$pdo->query("SELECT COUNT(*) FROM accounting_categories")->fetchColumn();
        if ($accCatCount === 0) {
            $pdo->exec("INSERT INTO accounting_categories (category_name, record_type) VALUES 
                ('ຄ່າໄຟຟ້າ', 'expense'),
                ('ຄ່ານໍ້າປະປາ', 'expense'),
                ('ຄ່າເຊົ່າສະຖານທີ່', 'expense'),
                ('ເງິນດ່ວນ/ເງິນເດືອນ', 'expense'),
                ('ຄ່າຕົ້ນທຶນ/ເຄື່ອງໃຊ້', 'expense'),
                ('ລາຍຮັບຄ່ານາຍໜ້າ', 'income'),
                ('ລາຍຮັບບໍລິການ', 'income')");
        }
    } catch (Throwable $ex) {}

    // Ensure bank_accounts table exists and has seed bank records
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `bank_accounts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `bank_name` VARCHAR(100) NOT NULL,
            `account_number` VARCHAR(100) NOT NULL,
            `account_name` VARCHAR(150) NOT NULL,
            `bank_code` VARCHAR(50) DEFAULT 'BCEL',
            `bank_logo` VARCHAR(255) DEFAULT NULL,
            `qr_code_img` VARCHAR(255) DEFAULT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Add columns to tbsale_save & sales if missing
        $saleCols = $pdo->query("SHOW COLUMNS FROM tbsale_save")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('bank_account_id', $saleCols)) {
            $pdo->exec("ALTER TABLE tbsale_save ADD COLUMN `bank_account_id` INT DEFAULT NULL");
        }
        if (!in_array('bank_name', $saleCols)) {
            $pdo->exec("ALTER TABLE tbsale_save ADD COLUMN `bank_name` VARCHAR(100) DEFAULT NULL");
        }

        $salesCols = $pdo->query("SHOW COLUMNS FROM sales")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('bank_account_id', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `bank_account_id` INT DEFAULT NULL");
        }
        if (!in_array('bank_name', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `bank_name` VARCHAR(100) DEFAULT NULL");
        }
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
        $pdo->exec("ALTER TABLE categories DROP COLUMN IF EXISTS category_name_en");
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

    // Auto Migration for promotions table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `promotions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `promo_name` VARCHAR(255) NOT NULL,
            `promo_type` VARCHAR(50) DEFAULT 'discount',
            `discount_type` VARCHAR(20) DEFAULT 'percentage',
            `discount_value` DECIMAL(12,2) DEFAULT 0.00,
            `start_date` DATE DEFAULT NULL,
            `end_date` DATE DEFAULT NULL,
            `min_qty` INT DEFAULT 0,
            `min_amount` DECIMAL(12,2) DEFAULT 0.00,
            `status` TINYINT(1) DEFAULT 1,
            `branch_id` INT DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $hasMinQty = $pdo->query("SHOW COLUMNS FROM `promotions` LIKE 'min_qty'")->fetch();
        if (!$hasMinQty) {
            $pdo->exec("ALTER TABLE `promotions` ADD COLUMN `min_qty` INT DEFAULT 0 AFTER `end_date`");
        }
        $hasTargetType = $pdo->query("SHOW COLUMNS FROM `promotions` LIKE 'target_type'")->fetch();
        if (!$hasTargetType) {
            $pdo->exec("ALTER TABLE `promotions` ADD COLUMN `target_type` VARCHAR(50) DEFAULT 'all' AFTER `min_amount`");
            $pdo->exec("ALTER TABLE `promotions` ADD COLUMN `target_name` VARCHAR(255) DEFAULT 'ທຸກສິນຄ້າ' AFTER `target_type`");
        }

        // Auto-deactivate expired promotions
        $pdo->exec("UPDATE promotions SET status = 0 WHERE status = 1 AND end_date < CURDATE()");
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

    // Auto Migration for tbsale_save, tbsale_save_detail & sales view
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbsale_save` (
            `sale_save_id` INT AUTO_INCREMENT PRIMARY KEY,
            `sale_save_bill` VARCHAR(50) NOT NULL,
            `sale_date` DATE DEFAULT NULL,
            `sale_time` TIME DEFAULT NULL,
            `user_receive` INT DEFAULT NULL,
            `customer_id` INT DEFAULT NULL,
            `customer_name` VARCHAR(150) DEFAULT 'ລູກຄ້າທົ່ວໄປ',
            `ip_address` VARCHAR(50) DEFAULT NULL,
            `sale_qty` DECIMAL(10,2) DEFAULT 0.00,
            `sale_amount` DECIMAL(12,2) DEFAULT 0.00,
            `sale_discount_bill` DECIMAL(12,2) DEFAULT 0.00,
            `sale_barlance` DECIMAL(12,2) DEFAULT 0.00,
            `sale_pay` DECIMAL(12,2) DEFAULT 0.00,
            `sale_return` DECIMAL(12,2) DEFAULT 0.00,
            `type_pay` VARCHAR(50) DEFAULT 'ເງິນສົດ',
            `sale_status` VARCHAR(20) DEFAULT 'SUCCESS',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (`sale_save_bill`),
            INDEX (`sale_date`),
            INDEX (`user_receive`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbsale_save_detail` (
            `save_id` INT AUTO_INCREMENT PRIMARY KEY,
            `save_bill` VARCHAR(50) NOT NULL,
            `save_date` DATE DEFAULT NULL,
            `save_time` TIME DEFAULT NULL,
            `save_proid` INT DEFAULT NULL,
            `save_proname` VARCHAR(255) DEFAULT NULL,
            `save_qty` DECIMAL(10,2) DEFAULT 1.00,
            `save_price` DECIMAL(12,2) DEFAULT 0.00,
            `cost_price` DECIMAL(12,2) DEFAULT 0.00,
            `save_money` DECIMAL(12,2) DEFAULT 0.00,
            `save_net_money` DECIMAL(12,2) DEFAULT 0.00,
            `user_receives` INT DEFAULT NULL,
            INDEX (`save_bill`),
            INDEX (`save_proid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `sales` (
            `sale_id` INT AUTO_INCREMENT PRIMARY KEY,
            `invoice_number` VARCHAR(50) NOT NULL,
            `sold_by` INT DEFAULT NULL,
            `customer_id` INT DEFAULT NULL,
            `customer_name` VARCHAR(150) DEFAULT 'ລູກຄ້າທົ່ວໄປ',
            `subtotal` DECIMAL(12,2) DEFAULT 0.00,
            `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
            `vat_amount` DECIMAL(12,2) DEFAULT 0.00,
            `total_amount` DECIMAL(12,2) DEFAULT 0.00,
            `total_profit` DECIMAL(12,2) DEFAULT 0.00,
            `cash_received` DECIMAL(12,2) DEFAULT 0.00,
            `change_amount` DECIMAL(12,2) DEFAULT 0.00,
            `payment_type` VARCHAR(50) DEFAULT 'ເງິນສົດ',
            `status` VARCHAR(20) DEFAULT 'SUCCESS',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `inv_num` (`invoice_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $salesCols = $pdo->query("SHOW COLUMNS FROM sales")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('created_at', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP");
        }
        if (!in_array('sold_by', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `sold_by` INT DEFAULT NULL");
        }
        if (!in_array('subtotal', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `subtotal` DECIMAL(12,2) DEFAULT 0.00");
        }
        if (!in_array('discount_amount', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `discount_amount` DECIMAL(12,2) DEFAULT 0.00");
        }
        if (!in_array('vat_amount', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `vat_amount` DECIMAL(12,2) DEFAULT 0.00");
        }
        if (!in_array('total_amount', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `total_amount` DECIMAL(12,2) DEFAULT 0.00");
        }
        if (!in_array('total_profit', $salesCols)) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN `total_profit` DECIMAL(12,2) DEFAULT 0.00");
        }

        $pdo->exec("INSERT IGNORE INTO sales (sale_id, invoice_number, sold_by, customer_id, customer_name, subtotal, discount_amount, total_amount, cash_received, change_amount, payment_type, status, created_at)
            SELECT 
                sale_save_id,
                sale_save_bill,
                user_receive,
                customer_id,
                customer_name,
                sale_amount,
                sale_discount_bill,
                sale_barlance,
                sale_pay,
                sale_return,
                type_pay,
                sale_status,
                COALESCE(created_at, CONCAT(sale_date, ' ', COALESCE(sale_time, '00:00:00')), NOW())
            FROM tbsale_save");
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
            // Ensure table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `activity_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `action` VARCHAR(255) NOT NULL,
                `detail` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $stmt = $pdo->prepare('INSERT INTO activity_logs (action, detail, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$action, $detail]);
        } catch (Throwable $e) {
            // Ignore activity log failures safely
        }
    }
}

// === Permission Helper Functions ===
if (!function_exists('hasPermission')) {
    function hasPermission($module, $action = 'view', $checkUserId = null) {
        $userId = $checkUserId !== null ? intval($checkUserId) : intval($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) return false;
        
        // If checking current logged-in user
        if ($userId === intval($_SESSION['user_id'] ?? 0)) {
            $status = $_SESSION['status'] ?? '';
            if ($status === 'ຜູ້ບໍລິຫານ' || strtolower($status) === 'admin' || $userId === 1) {
                return true;
            }
            $switchStates = $_SESSION['switch_states'] ?? [];
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
        } else {
            // If checking another specified user ID
            global $pdo;
            $uRow = null;
            if (isset($pdo)) {
                $uStmt = $pdo->prepare("SELECT * FROM tbuser WHERE Id = ?");
                $uStmt->execute([$userId]);
                $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
            }

            if (!$uRow) return false;

            $userStatusVal = $uRow['status'] ?? $uRow['userstatus'] ?? '';
            $isAdmin = ($userId === 1 || strtolower($userStatusVal) === 'admin' || strtolower($userStatusVal) === 'super admin' || $userStatusVal === 'ຜູ້ບໍລິຫານ');
            if ($isAdmin) return true;

            // Fetch target user's switch states
            $switchStates = [];
            if (isset($pdo)) {
                $swStmt = $pdo->prepare("SELECT switch_key, is_enabled FROM user_permission_switch_states WHERE user_id = ?");
                $swStmt->execute([$userId]);
                foreach ($swStmt->fetchAll(PDO::FETCH_ASSOC) as $sRow) {
                    $switchStates[$sRow['switch_key']] = (int)$sRow['is_enabled'];
                }
            }

            $perms = $uRow;
        }

        // Action Code Mapping
        $actKeyMap = [
            'view'   => 'view',
            'add'    => 'add',
            'create' => 'add',
            'edit'   => 'edit',
            'update' => 'edit',
            'delete' => 'del',
            'del'    => 'del',
            'remove' => 'del'
        ];
        $actCode = $actKeyMap[strtolower($action)] ?? 'view';

        // Module alias mappings
        $aliasMap = [
            'dashboard'        => 'dashboard',
            'pos'              => 'sale',
            'categories'       => 'categories',
            'products'         => 'products',
            'import'           => 'import',
            'import_stock'     => 'import_stock',
            'import_list'      => 'import_list',
            'stock_check'      => 'stock_check',
            'expiry_check'     => 'expiry_check',
            'reports'          => 'report',
            'financial'        => 'financial',
            'daily_report'     => 'report',
            'all_sales'        => 'report',
            'best_seller'      => 'report',
            'profit_cost'      => 'report',
            'category_sales'   => 'report',
            'delete_bills'     => 'report',
            'item_sales'       => 'item_sales',
            'accounting'       => 'accounting',
            'bank'             => 'accounting',
            'settings'         => 'setup',
            'stores'           => 'setup',
            'print_barcode'    => 'setup',
            'exchange_rate'    => 'setup',
            'promotions'       => 'setup',
            'price_adjustment' => 'setup',
            'printers'         => 'setup',
            'branches'         => 'branches',
            'database'         => 'database',
            'permissions'      => 'permissions',
            'user_manage'      => 'users',
            'users'            => 'users',
            'customers'        => 'customers',
            'stock_transfer'   => 'stock_transfer',
            'transfer_history' => 'transfer_history'
        ];

        $targetModule = $aliasMap[$module] ?? $module;

        // Check explicit switch key for exact module or target module alias (e.g., perm_bank_edit_6 or perm_accounting_edit_6)
        $switchKey1 = 'perm_' . $module . '_' . $actCode . '_' . $userId;
        if (isset($switchStates[$switchKey1])) {
            return (int)$switchStates[$switchKey1] === 1;
        }

        $switchKey2 = 'perm_' . $targetModule . '_' . $actCode . '_' . $userId;
        if (isset($switchStates[$switchKey2])) {
            return (int)$switchStates[$switchKey2] === 1;
        }

        // Check general edit/delete permission columns in tbuser
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

        // Check view/module access flag
        $hasModuleAccess = (
            !empty($perms[$targetModule]) ||
            !empty($perms[$module])
        );

        return $hasModuleAccess;
    }
}

if (!function_exists('getPermissionLimit')) {
    function getPermissionLimit($module) {
        return 0; // Visibility limit feature removed, always return 0 (no limit)
    }
}

// Global Helper Functions for Bank Assets
if (!function_exists('resolveBankLogo')) {
    function resolveBankLogo($logoFile, $bankCode = '') {
        if (empty($logoFile) || trim($logoFile) === '') {
            return '';
        }
        $filename = basename(trim($logoFile));
        $root = dirname(__DIR__);
        $htdocsRoot = 'D:/xampp/htdocs/MiniPos';
        if (file_exists($root . '/assets/img/banks/' . $filename) || file_exists($htdocsRoot . '/assets/img/banks/' . $filename)) {
            return '../../assets/img/banks/' . $filename;
        }
        return '../../assets/img/banks/' . $filename;
    }
}

if (!function_exists('resolveBankQr')) {
    function resolveBankQr($qrFile, $bankCode = '') {
        if (empty($qrFile) || trim($qrFile) === '') {
            return '';
        }
        $filename = basename(trim($qrFile));
        $root = dirname(__DIR__);
        $htdocsRoot = 'D:/xampp/htdocs/MiniPos';
        if (file_exists($root . '/assets/img/qr/' . $filename) || file_exists($htdocsRoot . '/assets/img/qr/' . $filename)) {
            return '../../assets/img/qr/' . $filename;
        }
        return '../../assets/img/qr/' . $filename;
    }
}

// Global Branch Store Helper Functions
if (!function_exists('isMainBranch')) {
    function isMainBranch($pdo, $store_id = null) {
        if ($store_id === null) {
            $store_id = intval($_SESSION['store_id'] ?? 1);
        }
        $isAdmin = (
            ($_SESSION['status'] ?? '') === 'ຜູ້ບໍລິຫານ' ||
            strtolower($_SESSION['status'] ?? '') === 'admin' ||
            ($_SESSION['user_id'] ?? 0) == 1
        );
        if ($isAdmin && $store_id == 1) {
            return true;
        }
        if (!$pdo) return ($store_id == 1);
        try {
            $stmt = $pdo->prepare("SELECT is_main FROM tbstore WHERE store_id = ? LIMIT 1");
            $stmt->execute([$store_id]);
            $isMain = $stmt->fetchColumn();
            return (bool)$isMain;
        } catch (Exception $e) {
            return ($store_id == 1);
        }
    }
}

if (!function_exists('getActiveStoreId')) {
    function getActiveStoreId($pdo) {
        $userStoreId = intval($_SESSION['store_id'] ?? 1);
        $isAdmin = (
            ($_SESSION['status'] ?? '') === 'ຜູ້ບໍລິຫານ' ||
            strtolower($_SESSION['status'] ?? '') === 'admin' ||
            ($_SESSION['user_id'] ?? 0) == 1
        );
        $isMain = isMainBranch($pdo, $userStoreId);
        
        // If logged-in user belongs to a sub-branch (not main, not admin), enforce strict branch lock
        if (!$isMain && !$isAdmin) {
            return $userStoreId;
        }

        // If main branch / admin, check if active_store_id is set in session
        if (isset($_SESSION['active_store_id']) && $_SESSION['active_store_id'] !== '') {
            return intval($_SESSION['active_store_id']);
        }
        return $userStoreId;
    }
}

if (!function_exists('getLowStockAlerts')) {
    function getLowStockAlerts($pdo, $store_id = null) {
        if (!$pdo) return [];
        try {
            if ($store_id !== null && intval($store_id) > 0) {
                $sql = "SELECT p.*, s.store_name, s.is_main 
                        FROM products p 
                        JOIN tbstore s ON p.store_id = s.store_id 
                        WHERE (p.qty <= 10 OR (p.min_qty IS NOT NULL AND p.min_qty > 0 AND p.qty <= p.min_qty) OR p.qty <= 0) 
                          AND s.status = 'active' 
                          AND p.store_id = ?
                        ORDER BY p.qty ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([intval($store_id)]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $sql = "SELECT p.*, s.store_name, s.is_main 
                        FROM products p 
                        JOIN tbstore s ON p.store_id = s.store_id 
                        WHERE (p.qty <= 10 OR (p.min_qty IS NOT NULL AND p.min_qty > 0 AND p.qty <= p.min_qty) OR p.qty <= 0) 
                          AND s.status = 'active'
                        ORDER BY s.is_main ASC, p.qty ASC";
                return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getIncomingTransfersForStore')) {
    function getIncomingTransfersForStore($pdo, $store_id) {
        if (!$pdo || empty($store_id)) return [];
        try {
            $sql = "SELECT t.*, s.store_name as from_store_name, to_s.store_name as to_store_name
                    FROM stock_transfers t
                    LEFT JOIN tbstore s ON t.from_store_id = s.store_id
                    LEFT JOIN tbstore to_s ON t.to_store_id = to_s.store_id
                    WHERE (t.to_store_id = ? OR t.from_store_id = ?)
                      AND t.transfer_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    ORDER BY t.transfer_id DESC LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([intval($store_id), intval($store_id)]);
            $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($transfers as &$t) {
                $trfId = intval($t['transfer_id']);
                $stmtDet = $pdo->prepare("
                    SELECT COALESCE(NULLIF(TRIM(d.product_name), ''), p.product_name, 'ສິນຄ້າ') as prod_name,
                           SUM(d.qty) as total_qty
                    FROM stock_transfer_details d
                    LEFT JOIN products p ON d.product_id = p.product_id
                    WHERE d.transfer_id = ?
                    GROUP BY d.product_id, prod_name
                ");
                $stmtDet->execute([$trfId]);
                $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

                $summaryParts = [];
                foreach ($details as $d) {
                    $summaryParts[] = $d['prod_name'] . ' x' . $d['total_qty'];
                }

                $t['total_items'] = count($details);
                $t['item_summary'] = implode(', ', $summaryParts);
            }
            unset($t);

            return $transfers;
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getNewProductsForStore')) {
    function getNewProductsForStore($pdo, $store_id) {
        if (!$pdo || empty($store_id)) return [];
        try {
            $sql = "SELECT p.*, s.store_name 
                    FROM products p 
                    JOIN tbstore s ON p.store_id = s.store_id 
                    WHERE p.store_id = ? AND p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    ORDER BY p.product_id DESC LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([intval($store_id)]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getSubBranchStockImportsForMain')) {
    function getSubBranchStockImportsForMain($pdo) {
        if (!$pdo) return [];
        try {
            $sql = "SELECT id.import_detail_id, id.import_id, id.product_id, id.unit_name, id.quantity, id.cost_price, id.total_cost,
                           COALESCE(p.product_name, 'ສິນຄ້າ') as product_name, s.store_id, s.store_name, i.import_date, i.invoice_number, i.supplier_name, i.notes,
                           COALESCE(NULLIF(u.fname, ''), u.username, 'Admin') as creator_name
                    FROM import_details id
                    JOIN imports i ON id.import_id = i.import_id
                    JOIN tbstore s ON (
                        CASE 
                            WHEN i.store_id > 0 THEN i.store_id
                            ELSE (SELECT store_id FROM products WHERE product_id = id.product_id LIMIT 1)
                        END
                    ) = s.store_id
                    LEFT JOIN products p ON (id.product_id = p.product_id AND p.store_id = s.store_id)
                    LEFT JOIN tbuser u ON i.created_by = u.Id
                    WHERE (s.is_main = 0 OR s.store_id != 1)
                      AND i.import_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    ORDER BY id.import_detail_id DESC LIMIT 20";
            $stmt = $pdo->query($sql);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            return [];
        }
    }
}
?>