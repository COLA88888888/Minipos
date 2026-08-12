<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_path = (basename($scriptDir) === 'pages') ? '../' : '../../';

require_once __DIR__ . '/../../config/db.php';

// Check if logged in and has access to permissions management
if (empty($_SESSION['user_id']) || (!hasPermission('permissions') && !hasPermission('users') && $_SESSION['status'] !== 'ຜູ້ບໍລິຫານ')) {
    echo "<script>window.top.location.href = '" . $base_path . "index.php';</script>";
    exit();
}

// === AJAX Action Handler ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    // 1. Single Permission Toggle
    if ($action === 'toggle_perm') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $perm = trim($_POST['perm'] ?? '');
        $val = intval($_POST['val'] ?? 0);

        $allowed_perms = ['sale', 'stock', 'report', 'accounting', 'setup', 'users', 'edit', 'cafe', 'order', 'kitchen', 'tbl', 'customers'];

        if ($user_id <= 0 || !in_array($perm, $allowed_perms, true)) {
            echo json_encode(['success' => false, 'message' => 'ຂໍ້ມູນບໍ່ຖືກຕ້ອງ']);
            exit();
        }

        // Prevent disabling permissions for main Admin (ID: 1)
        if ($user_id === 1 && $val === 0) {
            echo json_encode(['success' => false, 'message' => 'ບໍ່ສາມາດປິດສິດຂອງ Super Admin (ID: 1) ໄດ້!']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE tbuser SET `{$perm}` = ? WHERE Id = ?");
            $stmt->execute([$val, $user_id]);

            // Fetch updated user to calculate total permissions
            $uStmt = $pdo->prepare("SELECT username, sale, stock, report, accounting, setup, users, edit, cafe, `order`, kitchen, tbl, customers FROM tbuser WHERE Id = ?");
            $uStmt->execute([$user_id]);
            $userData = $uStmt->fetch();
            $targetUser = $userData['username'] ?? 'User';

            $permCount = 0;
            $countKeys = ['sale', 'stock', 'report', 'accounting', 'setup', 'users', 'edit', 'cafe', 'order', 'kitchen', 'tbl', 'customers'];
            foreach ($countKeys as $pKey) {
                if (!empty($userData[$pKey])) {
                    $permCount++;
                }
            }

            $perm_names_lao = [
                'sale' => 'ສິດ ຂາຍ POS',
                'stock' => 'ສິດ ຄັງສິນຄ້າ',
                'report' => 'ສິດ ລາຍງານຍອດຂາຍ',
                'accounting' => 'ສິດ ຈັດການບັນຊີ',
                'setup' => 'ສິດ ຕັ້ງຄ່າລະບົບ',
                'users' => 'ສິດ ຈັດການຜູ້ນຳໃຊ້',
                'edit' => 'ສິດ ແກ້ໄຂ & ລົບຂໍ້ມູນ',
                'cafe' => 'ສິດ ຮ້ານກາເຟ',
                'order' => 'ສິດ ສັ່ງອາຫານ',
                'kitchen' => 'ສິດ ຫ້ອງຄົວ',
                'tbl' => 'ສິດ ຈັດການໂຕະ',
                'customers' => 'ສິດ ຈັດການລູກຄ້າ'
            ];
            $perm_lao = $perm_names_lao[$perm] ?? $perm;
            $status_text = ($val === 1) ? 'ເປີດສິດ' : 'ປິດສິດ';

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

    // 2. Apply Preset Role Template
    if ($action === 'apply_preset') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $preset = trim($_POST['preset'] ?? '');

        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບຜູ້ໃຊ້']);
            exit();
        }

        $presets_map = [
            'cashier' => ['sale' => 1, 'stock' => 0, 'report' => 0, 'accounting' => 0, 'setup' => 0, 'users' => 0, 'edit' => 0, 'cafe' => 1, 'order' => 1, 'kitchen' => 0, 'tbl' => 0, 'role_name' => 'ພະນັກງານຂາຍ POS'],
            'accountant' => ['sale' => 0, 'stock' => 0, 'report' => 1, 'accounting' => 1, 'setup' => 0, 'users' => 0, 'edit' => 0, 'cafe' => 0, 'order' => 0, 'kitchen' => 0, 'tbl' => 0, 'role_name' => 'ຄົນຈັດການບັນຊີ'],
            'stock_keeper' => ['sale' => 0, 'stock' => 1, 'report' => 0, 'accounting' => 0, 'setup' => 0, 'users' => 0, 'edit' => 1, 'cafe' => 0, 'order' => 0, 'kitchen' => 0, 'tbl' => 0, 'role_name' => 'ພະນັກງານຄັງສິນຄ້າ'],
            'auditor' => ['sale' => 1, 'stock' => 1, 'report' => 1, 'accounting' => 1, 'setup' => 0, 'users' => 0, 'edit' => 0, 'cafe' => 1, 'order' => 0, 'kitchen' => 0, 'tbl' => 0, 'role_name' => 'ຜູ້ກວດສອບບັນຊີ'],
            'manager' => ['sale' => 1, 'stock' => 1, 'report' => 1, 'accounting' => 1, 'setup' => 1, 'users' => 1, 'edit' => 1, 'cafe' => 1, 'order' => 1, 'kitchen' => 1, 'tbl' => 1, 'role_name' => 'ຜູ້ບໍລິຫານ / ຈັດການທັງໝົດ'],
            'all_on' => ['sale' => 1, 'stock' => 1, 'report' => 1, 'accounting' => 1, 'setup' => 1, 'users' => 1, 'edit' => 1, 'cafe' => 1, 'order' => 1, 'kitchen' => 1, 'tbl' => 1, 'role_name' => 'ເປີດທຸກສິດ'],
            'all_off' => ['sale' => 0, 'stock' => 0, 'report' => 0, 'accounting' => 0, 'setup' => 0, 'users' => 0, 'edit' => 0, 'cafe' => 0, 'order' => 0, 'kitchen' => 0, 'tbl' => 0, 'role_name' => 'ປິດທຸກສິດ']
        ];

        if (!isset($presets_map[$preset])) {
            echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບຮູບແບບສິດທີ່ເລືອກ']);
            exit();
        }

        if ($user_id === 1 && ($preset === 'all_off' || $preset === 'cashier' || $preset === 'stock_keeper' || $preset === 'accountant')) {
            echo json_encode(['success' => false, 'message' => 'ບໍ່ສາມາດປິດສິດຂອງ Super Admin (ID: 1) ໄດ້!']);
            exit();
        }

        $p = $presets_map[$preset];
        try {
            $stmt = $pdo->prepare("UPDATE tbuser SET sale = ?, stock = ?, report = ?, accounting = ?, setup = ?, users = ?, edit = ?, cafe = ?, `order` = ?, kitchen = ?, tbl = ? WHERE Id = ?");
            $stmt->execute([$p['sale'], $p['stock'], $p['report'], $p['accounting'], $p['setup'], $p['users'], $p['edit'], $p['cafe'], $p['order'], $p['kitchen'], $p['tbl'], $user_id]);

            $uStmt = $pdo->prepare("SELECT username FROM tbuser WHERE Id = ?");
            $uStmt->execute([$user_id]);
            $targetUser = $uStmt->fetchColumn();

            $permCount = 0;
            $allKeys = ['sale', 'stock', 'report', 'accounting', 'setup', 'users', 'edit', 'cafe', 'order', 'kitchen', 'tbl'];
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
                    'sale' => $p['sale'],
                    'stock' => $p['stock'],
                    'report' => $p['report'],
                    'accounting' => $p['accounting'],
                    'setup' => $p['setup'],
                    'users' => $p['users'],
                    'edit' => $p['edit'],
                    'cafe' => $p['cafe'],
                    'order' => $p['order'],
                    'kitchen' => $p['kitchen'],
                    'tbl' => $p['tbl']
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'ຜິດພາດ: ' . $e->getMessage()]);
        }
        exit();
    }
}

// Fetch all users
$stmtUsers = $pdo->query("SELECT * FROM tbuser ORDER BY Id ASC");
$users = $stmtUsers->fetchAll();

$totalUsers = count($users);
$firstUserId = !empty($users) ? $users[0]['Id'] : 0;

require_once __DIR__ . '/../../layouts/header.php';
?>


<link rel="stylesheet" href="../../themes/permissions.css?v=<?php echo filemtime(__DIR__ . '/../../themes/permissions.css'); ?>">

<div class="container-fluid p-3 p-md-4">
  
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    <div>
      <h4 class="fw-bold mb-1" style="font-family: 'Noto Sans Lao Looped'; color: #0f172a;">
        <i class="fas fa-user-shield text-warning mr-2"></i> ກຳນົດສິດການໃຊ້ງານ
      </h4>
    </div>

    <!-- Quick Link to User Management -->
    <div class="mt-2 mt-md-0">
      <a href="<?php echo $base_path; ?>pages/users_manage/users_manage.php" class="btn btn-outline-primary shadow-sm font-weight-bold" style="border-radius: 10px; padding: 8px 16px;">
        <i class="fas fa-users-cog mr-1"></i> ຈັດການຜູ້ນຳໃຊ້
      </a>
    </div>
  </div>

  <!-- Master-Detail 2-Column Grid -->
  <div class="row">
    
    <!-- LEFT COLUMN (MASTER): Users List -->
    <div class="col-lg-4 col-xl-4 mb-4">
      <div class="user-list-card">
        
        <!-- Header & Search -->
        <div class="user-list-header">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="font-weight-bold mb-0 text-dark">
              <i class="fas fa-users text-primary mr-1"></i> ລາຍຊື່ຜູ້ນຳໃຊ້
            </h6>
            <span class="badge badge-primary px-2 py-1" style="border-radius: 8px;" id="totalUsersBadge">
              <?php echo $totalUsers; ?> ບັນຊີ
            </span>
          </div>

          <!-- Live Search Input -->
          <div class="user-search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="userFilterInput" class="form-control" placeholder="ຄົ້ນຫາຊື່ຜູ້ໃຊ້ ຫຼື ບົດບາດ..." onkeyup="filterUserList()">
          </div>
        </div>

        <!-- Scrollable Users List -->
        <div class="user-items-scroll" id="userItemsList">
          <?php if (empty($users)): ?>
            <div class="empty-users-state">
              <i class="fas fa-user-slash fa-3x mb-3 text-muted"></i>
              <p class="mb-0">ບໍ່ພົບຂໍ້ມູນຜູ້ນຳໃຊ້ໃນລະບົບ</p>
            </div>
          <?php else: ?>
            <?php foreach ($users as $index => $u): ?>
              <?php 
                $uStatus = $u['status'] ?? $u['userstatus'] ?? '';
                $isAdmin = (strtolower($uStatus) === 'admin' || $uStatus === 'ຜູ້ບໍລິຫານ' || $u['Id'] == 1);
                $displayName = htmlspecialchars($u['username'] ?? 'User');
                $isActiveFirst = ($index === 0);
                
                // Calculate total enabled permissions
                $permCount = 0;
                $allPermKeys = ['sale', 'stock', 'report', 'accounting', 'setup', 'users', 'edit', 'cafe', 'order', 'kitchen', 'tbl'];
                foreach ($allPermKeys as $pk) {
                    if (!empty($u[$pk]) || $isAdmin) {
                        $permCount++;
                    }
                }
              ?>
              <div class="user-item-btn <?php echo $isActiveFirst ? 'active-user-item' : ''; ?>" 
                   id="user-item-<?php echo $u['Id']; ?>"
                   data-user-id="<?php echo $u['Id']; ?>"
                   data-username="<?php echo strtolower($displayName); ?>"
                   data-status="<?php echo strtolower($uStatus); ?>"
                   onclick="selectUser(<?php echo $u['Id']; ?>)">
                
                <div class="d-flex align-items-center overflow-hidden">
                  <!-- User Avatar -->
                  <div class="user-avatar-circle mr-3 <?php echo $isAdmin ? 'user-avatar-admin' : ''; ?>">
                    <?php if ($u['Id'] == 1): ?>
                      <i class="fas fa-crown text-white" style="font-size: 1.1rem;"></i>
                    <?php else: ?>
                      <?php echo mb_substr($displayName, 0, 1, 'UTF-8'); ?>
                    <?php endif; ?>
                  </div>

                  <!-- User Details -->
                  <div class="text-truncate">
                    <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.95rem;">
                      <?php echo $displayName; ?>
                    </div>
                    <div class="d-flex align-items-center gap-1 mt-1">
                      <?php if ($u['Id'] == 1): ?>
                        <span class="badge badge-danger" style="font-size: 0.7rem; border-radius: 4px;">Super Admin</span>
                      <?php elseif ($isAdmin): ?>
                        <span class="badge badge-primary" style="font-size: 0.7rem; border-radius: 4px;">ຜູ້ບໍລິຫານ</span>
                      <?php else: ?>
                        <span class="badge badge-secondary" style="font-size: 0.7rem; border-radius: 4px;"><?php echo htmlspecialchars($uStatus ?: 'ພະນັກງານ'); ?></span>
                      <?php endif; ?>
                      <span class="text-muted ml-1" style="font-size: 0.75rem;">ID: #<?php echo $u['Id']; ?></span>
                    </div>
                  </div>
                </div>

                <!-- Active Permissions Badge Counter -->
                <div class="ml-2">
                  <span class="perm-count-badge" id="badge-perm-count-<?php echo $u['Id']; ?>">
                    <span id="count-val-<?php echo $u['Id']; ?>"><?php echo $permCount; ?></span>/11 ສິດ
                  </span>
                </div>

              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>
    </div>

    <!-- RIGHT COLUMN (DETAIL): Menu Permissions -->
    <div class="col-lg-8 col-xl-8 mb-4">
      <div class="perm-detail-card">
        
        <?php foreach ($users as $index => $u): ?>
          <?php 
            $uStatus = $u['status'] ?? $u['userstatus'] ?? '';
            $isAdmin = (strtolower($uStatus) === 'admin' || $uStatus === 'ຜູ້ບໍລິຫານ' || $u['Id'] == 1);
            $displayName = htmlspecialchars($u['username'] ?? 'User');
            $isActiveFirst = ($index === 0);
          ?>
          <div class="user-detail-panel" id="user-detail-<?php echo $u['Id']; ?>" style="display: <?php echo $isActiveFirst ? 'flex' : 'none'; ?>; flex-direction: column; height: 100%;">
            
            <!-- Detail Header: Selected User Profile & Presets -->
            <div class="perm-detail-header" style="padding: 14px 20px; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
              <div class="d-flex justify-content-between align-items-center w-100 flex-wrap" style="gap: 12px;">
                
                <!-- User Identity (Left) -->
                <div class="d-flex align-items-center">
                  <div class="user-avatar-circle mr-3 <?php echo $isAdmin ? 'user-avatar-admin' : ''; ?>" style="width: 44px; height: 44px; font-size: 1.15rem;">
                    <?php if ($u['Id'] == 1): ?>
                      <i class="fas fa-crown text-white"></i>
                    <?php else: ?>
                      <?php echo mb_substr($displayName, 0, 1, 'UTF-8'); ?>
                    <?php endif; ?>
                  </div>
                  <div>
                    <h5 class="mb-1 font-weight-bold text-dark d-flex align-items-center" style="font-size: 1.05rem;">
                      <?php echo $displayName; ?>
                      <?php if ($u['Id'] == 1): ?>
                        <span class="badge badge-danger ml-2 px-2 py-1" style="font-size: 0.75rem; border-radius: 6px;"><i class="fas fa-shield-alt"></i> Super Admin</span>
                      <?php elseif ($isAdmin): ?>
                        <span class="badge badge-primary ml-2 px-2 py-1" style="font-size: 0.75rem; border-radius: 6px;"> ຜູ້ບໍລິຫານ</span>
                      <?php else: ?>
                        <span class="badge badge-secondary ml-2 px-2 py-1" style="font-size: 0.75rem; border-radius: 6px;"><?php echo htmlspecialchars($uStatus ?: 'ພະນັກງານ'); ?></span>
                      <?php endif; ?>
                    </h5>
                    <div class="text-muted" style="font-size: 0.85rem;">
                      <i class="fas fa-id-card mr-1"></i> ລະຫັດຜູ້ໃຊ້ ID: #<?php echo $u['Id']; ?> &nbsp;|&nbsp; 
                      <i class="fas fa-store mr-1"></i> ຮ້ານຄ້າ ID: #<?php echo htmlspecialchars($u['store_id'] ?? $u['branch_id'] ?? '1'); ?>
                    </div>
                  </div>
                </div>

                <!-- Quick Presets Dropdown (Far Right) -->
                <div class="ml-auto mt-2 mt-md-0">
                  <div class="dropdown">
                    <button class="btn btn-warning text-dark font-weight-bold shadow-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false" style="border-radius: 10px; font-size: 0.88rem; padding: 8px 16px;">
                      <i class="fas fa-magic mr-1"></i> ກຳນົດສິດດ່ວນ
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-lg border-0" style="border-radius: 12px; min-width: 220px;">
                      <h6 class="dropdown-header text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 700;">ເລືອກຕາມຕຳແໜ່ງ</h6>
                      <a class="dropdown-item py-2" href="#" onclick="applyPresetRole(<?php echo $u['Id']; ?>, 'cashier', '<?php echo $displayName; ?>'); return false;">
                        <i class="fas fa-cash-register text-success mr-2"></i> ພະນັກງານຂາຍ POS
                      </a>
                      <a class="dropdown-item py-2" href="#" onclick="applyPresetRole(<?php echo $u['Id']; ?>, 'accountant', '<?php echo $displayName; ?>'); return false;">
                        <i class="fas fa-calculator text-purple mr-2" style="color: #8b5cf6;"></i> ຄົນຈັດການບັນຊີ
                      </a>
                      <a class="dropdown-item py-2" href="#" onclick="applyPresetRole(<?php echo $u['Id']; ?>, 'stock_keeper', '<?php echo $displayName; ?>'); return false;">
                        <i class="fas fa-boxes text-primary mr-2"></i> ພະນັກງານຄັງສິນຄ້າ
                      </a>
                      <a class="dropdown-item py-2" href="#" onclick="applyPresetRole(<?php echo $u['Id']; ?>, 'manager', '<?php echo $displayName; ?>'); return false;">
                        <i class="fas fa-user-shield text-info mr-2"></i> ຜູ້ບໍລິຫານ / ຈັດການທັງໝົດ
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item py-2 text-success font-weight-bold" href="#" onclick="applyPresetRole(<?php echo $u['Id']; ?>, 'all_on', '<?php echo $displayName; ?>'); return false;">
                        <i class="fas fa-check-double mr-2"></i> ເປີດທຸກສິດ
                      </a>
                      <?php if ($u['Id'] > 1): ?>
                        <a class="dropdown-item py-2 text-danger font-weight-bold" href="#" onclick="applyPresetRole(<?php echo $u['Id']; ?>, 'all_off', '<?php echo $displayName; ?>'); return false;">
                          <i class="fas fa-ban mr-2"></i> ປິດທຸກສິດ
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- Detail Body: Permission Matrix Table Layout -->
            <div class="perm-detail-body">
              
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-uppercase font-weight-bold text-muted" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                  <i class="fas fa-th-list mr-1"></i> ຕາຕະລາງກຳນົດສິດການເຂົ້າເຖິງ
                </span>
                <span class="text-muted" style="font-size: 0.85rem;">
                  <i class="fas fa-info-circle text-primary mr-1"></i> ເລືອກເປີດ/ປິດ ຕາມແຕ່ລະແອັກຊັນ
                </span>
              </div>

              <div class="perm-matrix-table-wrapper">
                <table class="perm-matrix-table">
                  <thead>
                    <tr>
                      <th>ໂມດູນລະບົບ</th>
                      <th class="text-center" style="width: 90px;">ເບິ່ງ</th>
                      <th class="text-center" style="width: 90px;">ເພີ່ມ</th>
                      <th class="text-center" style="width: 90px;">ແກ້ໄຂ</th>
                      <th class="text-center" style="width: 90px;">ລົບ</th>
                    </tr>
                  </thead>
                  <tbody>

                    <!-- 1. ດາດຊ໌ບອດ (Dashboard) -->
                    <tr>
                      <td>
                        <div class="perm-module-info">
                          <div class="perm-module-icon icon-bg-dashboard">
                            <i class="fas fa-chart-line"></i>
                          </div>
                          <div>
                            <div class="perm-module-title">ດາດຊ໌ບອດ (Dashboard)</div>
                            <div class="perm-module-desc">ສະແດງສະຖິຕິ, ຍອດລວມອຸປະກອນ ແລະ ມູນຄ່າການສ້ອມແປງ/ຂາຍ</div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_dash_view_<?php echo $u['Id']; ?>" 
                                 data-perm="sale" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['sale']) || !empty($u['stock']) || !empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'sale', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                      <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                      <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                    </tr>

                    <!-- 2. ຂາຍສິນຄ້າ (POS Sales) -->
                    <tr>
                      <td>
                        <div class="perm-module-info">
                          <div class="perm-module-icon icon-bg-stockin">
                            <i class="fas fa-cash-register"></i>
                          </div>
                          <div>
                            <div class="perm-module-title">ໜ້າຂາຍສິນຄ້າ (POS Sales)</div>
                            <div class="perm-module-desc">ເປີດບິນຂາຍ, ຄິດໄລ່ເງິນ/ທອນ, ຮັບຊຳລະເງິນ ແລະ ພິມໃບບິນຮັບເງິນ</div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_sale_view_<?php echo $u['Id']; ?>" 
                                 data-perm="sale" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['sale']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'sale', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_sale_add_<?php echo $u['Id']; ?>" 
                                 data-perm="sale" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['sale']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'sale', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_sale_edit_<?php echo $u['Id']; ?>" 
                                 data-perm="edit" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_sale_del_<?php echo $u['Id']; ?>" 
                                 data-perm="edit" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                    </tr>

                    <!-- 3. ຂໍ້ມູນສິນຄ້າ & ຄັງສິນຄ້າ (Stock Inventory) -->
                    <tr>
                      <td>
                        <div class="perm-module-info">
                          <div class="perm-module-icon icon-bg-building">
                            <i class="fas fa-boxes"></i>
                          </div>
                          <div>
                            <div class="perm-module-title">ຂໍ້ມູນສິນຄ້າ & ຄັງສິນຄ້າ (Stock Inventory)</div>
                            <div class="perm-module-desc">ຈັດການປະເພດສິນຄ້າ, ລາຍການສິນຄ້າ, ນຳເຂົ້າສິນຄ້າ, ກວດສະຕັອກ & ວັນໝົດອາຍຸ</div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_stock_view_<?php echo $u['Id']; ?>" 
                                 data-perm="stock" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['stock']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'stock', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_stock_add_<?php echo $u['Id']; ?>" 
                                 data-perm="stock" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['stock']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'stock', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_stock_edit_<?php echo $u['Id']; ?>" 
                                 data-perm="edit" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_stock_del_<?php echo $u['Id']; ?>" 
                                 data-perm="edit" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                    </tr>

                    <!-- 4. ຈັດການບັນຊີ (Accounting) -->
                    <tr>
                      <td>
                        <div class="perm-module-info">
                          <div class="perm-module-icon icon-bg-category" style="background: #f3e8ff; color: #8b5cf6;">
                            <i class="fas fa-calculator"></i>
                          </div>
                          <div>
                            <div class="perm-module-title">ຈັດການບັນຊີຕ່າງຫາກ (Accounting)</div>
                            <div class="perm-module-desc">ບັນຊີແຍກຕາມພະນັກງານຂາຍ, ບັນທຶກລາຍຮັບ-ລາຍຈ່າຍ ແລະ ສະຫຼຸບງົບດຸນ</div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_acc_view_<?php echo $u['Id']; ?>" 
                                 data-perm="accounting" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['accounting']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'accounting', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_acc_add_<?php echo $u['Id']; ?>" 
                                 data-perm="accounting" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['accounting']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'accounting', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_acc_edit_<?php echo $u['Id']; ?>" 
                                 data-perm="accounting" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['accounting']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'accounting', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_acc_del_<?php echo $u['Id']; ?>" 
                                 data-perm="accounting" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['accounting']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'accounting', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                    </tr>

                    <!-- 5. ລາຍງານ & ການເງິນ (Reports & Finance) -->
                    <tr>
                      <td>
                        <div class="perm-module-info">
                          <div class="perm-module-icon icon-bg-asset">
                            <i class="fas fa-file-invoice-dollar"></i>
                          </div>
                          <div>
                            <div class="perm-module-title">ລາຍງານຍອດຂາຍ (Reports)</div>
                            <div class="perm-module-desc">ລາຍງານການຂາຍປະຈຳວັນ, ສິນຄ້າຂາຍດີ, ກຳໄລ-ຕົ້ນທຶນ ແລະ ປະຫວັດລົບບິນ</div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_report_view_<?php echo $u['Id']; ?>" 
                                 data-perm="report" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['report']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'report', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_report_edit_<?php echo $u['Id']; ?>" 
                                 data-perm="edit" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_report_del_<?php echo $u['Id']; ?>" 
                                 data-perm="edit" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['edit']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'edit', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                    </tr>

                    <!-- 6. ຈັດການຜູ້ນຳໃຊ້ & ກຳນົດສິດ (User Management) -->
                    <tr>
                      <td>
                        <div class="perm-module-info">
                          <div class="perm-module-icon icon-bg-users">
                            <i class="fas fa-users-cog"></i>
                          </div>
                          <div>
                            <div class="perm-module-title">ຈັດການຜູ້ນຳໃຊ້ & ກຳນົດສິດ (User Management)</div>
                            <div class="perm-module-desc">ເພີ່ມຜູ້ໃຊ້ງານໃໝ່, ແກ້ໄຂຂໍ້ມູນບັນຊີ, ປ່ຽນລະຫັດຜ່ານ ແລະ ກຳນົດສິດລະບົບ</div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_users_view_<?php echo $u['Id']; ?>" 
                                 data-perm="users" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['users']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'users', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_users_add_<?php echo $u['Id']; ?>" 
                                 data-perm="users" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['users']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'users', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_users_edit_<?php echo $u['Id']; ?>" 
                                 data-perm="users" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['users']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'users', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_users_del_<?php echo $u['Id']; ?>" 
                                 data-perm="users" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['users']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'users', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                    </tr>

                    <!-- 7. ຕັ້ງຄ່າລະບົບ (System Settings) -->
                    <tr>
                      <td>
                        <div class="perm-module-info">
                          <div class="perm-module-icon icon-bg-settings">
                            <i class="fas fa-cogs"></i>
                          </div>
                          <div>
                            <div class="perm-module-title">ຕັ້ງຄ່າລະບົບ (System Settings)</div>
                            <div class="perm-module-desc">ຕັ້ງຄ່າຂໍ້ມູນຮ້ານຄ້າ, ອັດຕາພາສີ VAT, ແຈ້ງເຕືອນສະຕັອກ & ຕັ້ງຄ່າທົ່ວໄປ</div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_setup_view_<?php echo $u['Id']; ?>" 
                                 data-perm="setup" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'setup', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                      <td class="text-center">
                        <label class="matrix-switch">
                          <input type="checkbox" 
                                 id="perm_setup_edit_<?php echo $u['Id']; ?>" 
                                 data-perm="setup" 
                                 data-user-id="<?php echo $u['Id']; ?>"
                                 <?php echo (!empty($u['setup']) || $isAdmin) ? 'checked' : ''; ?>
                                 <?php echo ($u['Id'] == 1) ? 'disabled' : ''; ?>
                                 onchange="toggleUserPerm(<?php echo $u['Id']; ?>, 'setup', this)">
                          <span class="matrix-slider"></span>
                        </label>
                      </td>
                      <td class="text-center"><span class="perm-na-badge">ບໍ່ມີ</span></td>
                    </tr>

                  </tbody>
                </table>
              </div>

            </div>
          </div>
        <?php endforeach; ?>

      </div>
    </div>

  </div>

</div>

<!-- JavaScript for Master-Detail & AJAX Interactions -->
<script>
function selectUser(userId) {
  document.querySelectorAll('.user-item-btn').forEach(function(btn) {
    btn.classList.remove('active-user-item');
  });
  var activeBtn = document.getElementById('user-item-' + userId);
  if (activeBtn) {
    activeBtn.classList.add('active-user-item');
  }

  document.querySelectorAll('.user-detail-panel').forEach(function(panel) {
    panel.style.display = 'none';
  });
  var targetPanel = document.getElementById('user-detail-' + userId);
  if (targetPanel) {
    targetPanel.style.display = 'flex';
  }
}

function filterUserList() {
  var input = document.getElementById('userFilterInput');
  var filter = input.value.toLowerCase().trim();
  var items = document.querySelectorAll('.user-item-btn');
  var visibleCount = 0;

  items.forEach(function(item) {
    var username = item.getAttribute('data-username') || '';
    var status = item.getAttribute('data-status') || '';
    if (username.indexOf(filter) > -1 || status.indexOf(filter) > -1) {
      item.style.display = 'flex';
      visibleCount++;
    } else {
      item.style.display = 'none';
    }
  });

  var badge = document.getElementById('totalUsersBadge');
  if (badge) {
    badge.innerText = visibleCount + ' ບັນຊີ';
  }
}

var Toast = null;
function getToast() {
  if (!Toast && typeof Swal !== 'undefined') {
    Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true,
      didOpen: function(toast) {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });
  }
  return Toast;
}

function showToast(icon, title) {
  var t = getToast();
  if (t) {
    t.fire({ icon: icon, title: title });
  }
}

function toggleUserPerm(userId, permName, inputEl) {
  var isChecked = inputEl.checked ? 1 : 0;

  $.ajax({
    url: '',
    type: 'POST',
    data: {
      ajax_action: 'toggle_perm',
      user_id: userId,
      perm: permName,
      val: isChecked
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        document.querySelectorAll('input[data-perm="' + permName + '"][data-user-id="' + userId + '"]').forEach(function(el) {
          el.checked = (isChecked === 1);
        });

        if (res.perm_count !== undefined) {
          var countVal = document.getElementById('count-val-' + userId);
          if (countVal) {
            countVal.innerText = res.perm_count;
          }
        }

        showToast('success', res.message);
      } else {
        inputEl.checked = !inputEl.checked;
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'warning',
            title: 'ແຈ້ງເຕືອນ',
            text: res.message,
            confirmButtonColor: '#2563eb'
          });
        } else {
          alert(res.message);
        }
      }
    },
    error: function() {
      inputEl.checked = !inputEl.checked;
      showToast('error', 'ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່ລະບົບ');
    }
  });
}

function applyPresetRole(userId, presetKey, username) {
  if (typeof Swal === 'undefined') {
    alert('ກະລຸນາລໍຖ້າລະບົບໂຫຼດຈັກຄູ່...');
    return;
  }

  Swal.fire({
    title: 'ຢືນຢັນການກຳນົດສິດ',
    text: 'ທ່ານຕ້ອງການນຳໃຊ້ຮູບແບບສິດນີ້ໃຫ້ກັບ "' + username + '" ແທ້ຫຼືບໍ່?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#64748b',
    confirmButtonText: '<i class="fas fa-check mr-1"></i> ຢືນຢັນ',
    cancelButtonText: 'ຍົກເລີກ'
  }).then(function(result) {
    if (result.isConfirmed) {
      $.ajax({
        url: '',
        type: 'POST',
        data: {
          ajax_action: 'apply_preset',
          user_id: userId,
          preset: presetKey
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            var perms = res.permissions;

            Object.keys(perms).forEach(function(k) {
              var isVal = (parseInt(perms[k]) === 1);
              document.querySelectorAll('input[data-perm="' + k + '"][data-user-id="' + userId + '"]').forEach(function(el) {
                el.checked = isVal;
              });
            });

            if (res.perm_count !== undefined) {
              var countVal = document.getElementById('count-val-' + userId);
              if (countVal) {
                countVal.innerText = res.perm_count;
              }
            }

            showToast('success', res.message);
          } else {
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'warning',
                title: 'ແຈ້ງເຕືອນ',
                text: res.message,
                confirmButtonColor: '#2563eb'
              });
            } else {
              alert(res.message);
            }
          }
        },
        error: function() {
          showToast('error', 'ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່');
        }
      });
    }
  });
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
