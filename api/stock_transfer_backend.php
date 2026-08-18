<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/db.php';

// Authorization check
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied: Please log in']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get_transfer_details') {
    $transfer_id = intval($_GET['transfer_id'] ?? 0);
    if ($transfer_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid transfer ID']);
        exit();
    }

    try {
        // Fetch transfer header details
        $stmtTrf = $pdo->prepare("
            SELECT t.*, f.store_name as from_store_name, to_s.store_name as to_store_name, u.username as creator_name
            FROM stock_transfers t
            LEFT JOIN tbstore f ON t.from_store_id = f.store_id
            LEFT JOIN tbstore to_s ON t.to_store_id = to_s.store_id
            LEFT JOIN tbuser u ON t.created_by = u.Id
            WHERE t.transfer_id = ?
        ");
        $stmtTrf->execute([$transfer_id]);
        $transfer = $stmtTrf->fetch(PDO::FETCH_ASSOC);

        if (!$transfer) {
            echo json_encode(['success' => false, 'message' => 'Transfer record not found']);
            exit();
        }

        // Format date
        $transfer['transfer_date'] = date('d/m/Y H:i', strtotime($transfer['transfer_date']));

        // Fetch transfer details directly from stock_transfer_details
        $stmtDet = $pdo->prepare("
            SELECT d.*, COALESCE(d.barcode, p.barcode) as prod_barcode
            FROM stock_transfer_details d
            LEFT JOIN products p ON d.product_id = p.product_id
            WHERE d.transfer_id = ?
            ORDER BY d.id ASC
        ");
        $stmtDet->execute([$transfer_id]);
        $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'transfer' => $transfer,
            'details' => $details
        ]);
        exit();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}
